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

        Log::info('[ReservaWeb] Stripe webhook: recibido', [
            'payload_length' => strlen($payload),
            'signature_present' => !empty($sigHeader),
        ]);

        if (!$webhookSecret) {
            Log::warning('[ReservaWeb] Stripe webhook: secret no configurado');
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
                Log::warning('[ReservaWeb] Stripe webhook: SDK no disponible, procesando básico');
                $event = json_decode($payload, true);
            }
        } catch (\Exception $e) {
            Log::error('[ReservaWeb] Stripe webhook: firma inválida', [
                'message' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Firma inválida'], 400);
        }

        $eventType = $event['type'] ?? $event->type ?? null;
        Log::info('[ReservaWeb] Stripe webhook: evento', ['type' => $eventType]);

        // Procesar el evento
        switch ($eventType) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event['data']['object'] ?? $event->data->object ?? null);
                break;
            
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event['data']['object'] ?? $event->data->object ?? null);
                break;
            
            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event['data']['object'] ?? $event->data->object ?? null);
                break;
            
            default:
                Log::info('[ReservaWeb] Stripe webhook: evento no manejado', [
                    'type' => $eventType ?? 'unknown',
                ]);
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

            Log::info('[ReservaWeb] Stripe checkout.session.completed: inicio', [
                'session_id' => $sessionId ? substr($sessionId, 0, 24) . '...' : null,
            ]);

            if (!$sessionId) {
                Log::warning('[ReservaWeb] Stripe checkout.session.completed: sin session_id');
                return;
            }

            $pago = Pago::where('stripe_checkout_session_id', $sessionId)->first();

            if (!$pago) {
                Log::warning('[ReservaWeb] Stripe checkout.session.completed: pago no encontrado', [
                    'session_id_prefijo' => substr($sessionId, 0, 24),
                ]);
                return;
            }

            $paymentIntentId = $session['payment_intent'] ?? $session->payment_intent ?? null;
            $paymentStatus = $session['payment_status'] ?? $session->payment_status ?? 'unknown';

            Log::info('[ReservaWeb] Stripe checkout.session.completed: pago encontrado', [
                'pago_id' => $pago->id,
                'reserva_id' => $pago->reserva_id,
                'payment_status' => $paymentStatus,
                'pago_estado_actual' => $pago->estado,
            ]);

            if ($paymentStatus === 'paid' && $pago->estado !== 'completado') {
                $pago->update([
                    'estado' => 'completado',
                    'stripe_payment_intent_id' => $paymentIntentId,
                    'fecha_pago' => now(),
                ]);

                Log::info('[ReservaWeb] Stripe checkout.session.completed: pago actualizado a completado', [
                    'pago_id' => $pago->id,
                    'payment_intent_id' => $paymentIntentId,
                ]);

                // Actualizar reserva a confirmada
                if ($pago->reserva) {
                    $pago->reserva->update(['estado_id' => 1]); // Confirmada

                    Log::info('[ReservaWeb] Stripe checkout.session.completed: reserva confirmada', [
                        'reserva_id' => $pago->reserva->id,
                        'codigo_reserva' => $pago->reserva->codigo_reserva,
                        'apartamento_id' => $pago->reserva->apartamento_id,
                    ]);

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
                $updated = IntentoPago::where('stripe_checkout_session_id', $sessionId)
                    ->update([
                        'estado' => 'exitoso',
                        'stripe_payment_intent_id' => $paymentIntentId,
                        'respuesta_stripe' => is_array($session) ? $session : (array)$session,
                    ]);

                Log::info('[ReservaWeb] Stripe checkout.session.completed: intentos pago actualizados', [
                    'session_id_prefijo' => substr($sessionId, 0, 24),
                    'intentos_actualizados' => $updated,
                ]);

                // Si es un pago de extras, actualizar reserva_servicios
                if (isset($pago->metadata['tipo']) && $pago->metadata['tipo'] === 'extras') {
                    \App\Models\ReservaServicio::where('pago_id', $pago->id)
                        ->update([
                            'estado' => 'pagado',
                            'fecha_pago' => now(),
                            'stripe_payment_intent_id' => $paymentIntentId,
                        ]);

                    Log::info('[ReservaWeb] Stripe checkout.session.completed: extras marcados como pagados', [
                        'reserva_id' => $pago->reserva_id,
                    ]);
                }

                Log::info('[ReservaWeb] Stripe checkout.session.completed: flujo completado', [
                    'pago_id' => $pago->id,
                    'reserva_id' => $pago->reserva_id,
                ]);
            } else {
                Log::info('[ReservaWeb] Stripe checkout.session.completed: sin cambios (ya completado o status no paid)', [
                    'payment_status' => $paymentStatus,
                    'pago_estado' => $pago->estado,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('[ReservaWeb] Stripe checkout.session.completed: excepción', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
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
            }
        } catch (\Exception $e) {
            Log::error('Error al procesar payment_intent.payment_failed: ' . $e->getMessage());
        }
    }
}
