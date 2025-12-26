<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Reserva;
use App\Models\IntentoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    /**
     * Manejar webhooks de Stripe
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        if (!$webhookSecret) {
            Log::warning('Stripe webhook secret no configurado');
            return response()->json(['error' => 'Webhook secret no configurado'], 400);
        }

        try {
            if (class_exists('\Stripe\Webhook')) {
                $event = \Stripe\Webhook::constructEvent(
                    $payload,
                    $sigHeader,
                    $webhookSecret
                );
            } else {
                // Si Stripe no está instalado, registrar y continuar
                Log::warning('Stripe SDK no disponible, procesando webhook básico');
                $event = json_decode($payload, true);
            }
        } catch (\Exception $e) {
            Log::error('Error al verificar webhook de Stripe: ' . $e->getMessage());
            return response()->json(['error' => 'Firma inválida'], 400);
        }

        // Procesar el evento
        switch ($event['type'] ?? $event->type ?? null) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event['data']['object'] ?? $event->data->object ?? null);
                break;
            
            case 'checkout.session.expired':
                $this->handleCheckoutSessionExpired($event['data']['object'] ?? $event->data->object ?? null);
                break;
            
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event['data']['object'] ?? $event->data->object ?? null);
                break;
            
            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event['data']['object'] ?? $event->data->object ?? null);
                break;
            
            default:
                Log::info('Evento de Stripe no manejado: ' . ($event['type'] ?? $event->type ?? 'unknown'));
        }

        return response()->json(['received' => true]);
    }

    /**
     * Manejar sesión de checkout completada
     */
    private function handleCheckoutSessionCompleted($session)
    {
        try {
            $sessionId = $session['id'] ?? $session->id ?? null;
            
            if (!$sessionId) {
                return;
            }

            $pago = Pago::where('stripe_checkout_session_id', $sessionId)->first();
            
            if (!$pago) {
                Log::warning("Pago no encontrado para sesión: {$sessionId}");
                return;
            }

            $paymentIntentId = $session['payment_intent'] ?? $session->payment_intent ?? null;
            $paymentStatus = $session['payment_status'] ?? $session->payment_status ?? 'unknown';

            if ($paymentStatus === 'paid' && $pago->estado !== 'completado') {
                $pago->update([
                    'estado' => 'completado',
                    'stripe_payment_intent_id' => $paymentIntentId,
                    'fecha_pago' => now(),
                ]);

                // Actualizar reserva a confirmada
                if ($pago->reserva) {
                    $pago->reserva->update(['estado_id' => 1]); // Confirmada
                    
                    // Crear notificación
                    \App\Models\Notification::createForAdmins(
                        \App\Models\Notification::TYPE_RESERVA,
                        'Nueva Reserva Confirmada',
                        "Reserva {$pago->reserva->codigo_reserva} confirmada y pagada",
                        [
                            'reserva_id' => $pago->reserva->id,
                            'pago_id' => $pago->id,
                        ],
                        \App\Models\Notification::PRIORITY_HIGH,
                        \App\Models\Notification::CATEGORY_SUCCESS,
                        route('admin.reservas.show', $pago->reserva->id)
                    );
                }

                // Actualizar intento de pago
                IntentoPago::where('stripe_checkout_session_id', $sessionId)
                    ->update([
                        'estado' => 'exitoso',
                        'stripe_payment_intent_id' => $paymentIntentId,
                        'respuesta_stripe' => is_array($session) ? $session : (array)$session,
                    ]);

                // Si es un pago de extras, actualizar reserva_servicios
                if (isset($pago->metadata['tipo']) && $pago->metadata['tipo'] === 'extras') {
                    \App\Models\ReservaServicio::where('pago_id', $pago->id)
                        ->update([
                            'estado' => 'pagado',
                            'fecha_pago' => now(),
                            'stripe_payment_intent_id' => $paymentIntentId,
                        ]);
                    
                    Log::info("Extras pagados para reserva: {$pago->reserva_id}");
                }

                Log::info("Pago completado: {$pago->id} - Reserva: {$pago->reserva_id}");
            }
        } catch (\Exception $e) {
            Log::error('Error al procesar checkout.session.completed: ' . $e->getMessage());
        }
    }

    /**
     * Manejar sesión de checkout expirada
     */
    private function handleCheckoutSessionExpired($session)
    {
        try {
            $sessionId = $session['id'] ?? $session->id ?? null;
            
            if (!$sessionId) {
                return;
            }

            $pago = Pago::where('stripe_checkout_session_id', $sessionId)->first();
            
            if ($pago && $pago->reserva && $pago->reserva->estado_id == 2) { // Solo si está pendiente
                // Cancelar la reserva
                $pago->reserva->estado_id = 4; // Cancelada
                $pago->reserva->save();
                
                // Marcar el pago como expirado
                $pago->update(['estado' => 'expirado']);
                
                // Liberar disponibilidad en Channex
                $this->liberarDisponibilidadChannex($pago->reserva);
                
                Log::info('Reserva cancelada por sesión de Stripe expirada', [
                    'reserva_id' => $pago->reserva->id,
                    'codigo_reserva' => $pago->reserva->codigo_reserva,
                    'pago_id' => $pago->id,
                    'session_id' => $sessionId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error al procesar checkout.session.expired: ' . $e->getMessage());
        }
    }

    /**
     * Manejar payment intent exitoso
     */
    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        try {
            $paymentIntentId = $paymentIntent['id'] ?? $paymentIntent->id ?? null;
            
            if (!$paymentIntentId) {
                return;
            }

            $pago = Pago::where('stripe_payment_intent_id', $paymentIntentId)->first();
            
            if ($pago && $pago->estado !== 'completado') {
                $pago->update([
                    'estado' => 'completado',
                    'fecha_pago' => now(),
                ]);

                if ($pago->reserva) {
                    $pago->reserva->update(['estado_id' => 1]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al procesar payment_intent.succeeded: ' . $e->getMessage());
        }
    }

    /**
     * Manejar payment intent fallido
     */
    private function handlePaymentIntentFailed($paymentIntent)
    {
        try {
            $paymentIntentId = $paymentIntent['id'] ?? $paymentIntent->id ?? null;
            
            if (!$paymentIntentId) {
                return;
            }

            $pago = Pago::where('stripe_payment_intent_id', $paymentIntentId)->first();
            
            if ($pago) {
                $pago->update(['estado' => 'fallido']);

                // Registrar intento fallido
                IntentoPago::create([
                    'pago_id' => $pago->id,
                    'reserva_id' => $pago->reserva_id,
                    'stripe_payment_intent_id' => $paymentIntentId,
                    'estado' => 'fallido',
                    'monto' => $pago->monto,
                    'moneda' => $pago->moneda,
                    'mensaje_error' => $paymentIntent['last_payment_error']['message'] ?? 'Pago fallido',
                    'respuesta_stripe' => is_array($paymentIntent) ? $paymentIntent : (array)$paymentIntent,
                    'fecha_intento' => now(),
                ]);

                // Cancelar la reserva si está pendiente y liberar Channex
                if ($pago->reserva && $pago->reserva->estado_id == 2) { // Solo si está pendiente
                    $pago->reserva->estado_id = 4; // Cancelada
                    $pago->reserva->save();
                    
                    // Liberar disponibilidad en Channex
                    $this->liberarDisponibilidadChannex($pago->reserva);
                    
                    Log::info('Reserva cancelada por pago fallido en Stripe', [
                        'reserva_id' => $pago->reserva->id,
                        'codigo_reserva' => $pago->reserva->codigo_reserva,
                        'pago_id' => $pago->id,
                        'payment_intent_id' => $paymentIntentId,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al procesar payment_intent.payment_failed: ' . $e->getMessage());
        }
    }

    /**
     * Liberar disponibilidad en Channex para una reserva cancelada
     */
    private function liberarDisponibilidadChannex(Reserva $reserva)
    {
        try {
            $apartamento = $reserva->apartamento;
            $roomType = \App\Models\RoomType::find($reserva->room_type_id);
            
            if (!$apartamento || !$apartamento->id_channex || !$roomType || !$roomType->id_channex) {
                Log::warning('No se puede liberar disponibilidad en Channex: faltan datos', [
                    'reserva_id' => $reserva->id,
                ]);
                return;
            }
            
            $startDate = \Carbon\Carbon::parse($reserva->fecha_entrada);
            $endDate = \Carbon\Carbon::parse($reserva->fecha_salida)->subDay();
            
            $update = [
                'property_id' => $apartamento->id_channex,
                'room_type_id' => $roomType->id_channex,
                'date_from' => $startDate->toDateString(),
                'date_to' => $endDate->toDateString(),
                'update_type' => 'availability',
                'availability' => 1, // Habilitar disponibilidad (liberar)
            ];
            
            $apiUrl = config('services.channex.url', env('CHANNEX_URL', 'https://app.channex.io/api/v1'));
            $apiToken = config('services.channex.token', env('CHANNEX_TOKEN'));
            
            if (!$apiToken) {
                Log::error('CHANNEX_TOKEN no configurado para liberar disponibilidad');
                return;
            }
            
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders([
                    'user-api-key' => $apiToken,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$apiUrl}/availability", ['values' => [$update]]);
            
            if ($response->successful()) {
                Log::info('Disponibilidad liberada en Channex - reserva cancelada por pago fallido', [
                    'reserva_id' => $reserva->id,
                    'codigo_reserva' => $reserva->codigo_reserva,
                    'apartamento_id' => $apartamento->id,
                    'fecha_entrada' => $startDate->toDateString(),
                    'fecha_salida' => $endDate->toDateString(),
                ]);
            } else {
                Log::error('Error al liberar disponibilidad en Channex', [
                    'reserva_id' => $reserva->id,
                    'http_status' => $response->status(),
                    'error_body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Excepción al liberar disponibilidad en Channex', [
                'reserva_id' => $reserva->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
