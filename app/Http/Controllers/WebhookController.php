<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Log de ejemplo para depuración
        Log::info('Webhook recibido', $request->all());

        // Verifica si la estructura del evento es válida
        $validated = $request->validate([
            'event' => 'required|string',
            'property_id' => 'required|string',
            'payload' => 'nullable|array',
        ]);

        // Procesa el evento según el tipo
        switch ($validated['event']) {
            case 'ari':
                $this->processAriEvent($validated['property_id'], $validated['data']);
                break;
            case 'booking':
                $this->processBookingEvent($validated['property_id'], $validated['data']);
                break;
            case 'booking_unmapped_room':
                $this->processUnmappedRoomEvent($validated['property_id'], $validated['data']);
                break;
            default:
                Log::warning("Evento desconocido recibido: {$validated['event']}");
        }

        return response()->json(['message' => 'Webhook recibido con éxito'], 200);
    }

    private function processAriEvent($propertyId, $data)
    {
        Log::info("Procesando evento ARI para la propiedad {$propertyId}", $data);
        // Lógica para manejar los cambios en ARI
    }

    private function processBookingEvent($propertyId, $data)
    {
        // Encuentra la propiedad en la base de datos
        $apartamento = Apartamento::where('id_channex', $propertyId)->first();

        if (!$apartamento) {
            Log::error("Propiedad no encontrada para ID: {$propertyId}");
            return;
        }

        // Guarda o actualiza la información de la reserva
        foreach ($data['bookings'] ?? [] as $booking) {
            Reserva::updateOrCreate(
                ['booking_id' => $booking['id']],
                [
                    'apartamento_id' => $apartamento->id,
                    'fecha_inicio' => $booking['start_date'],
                    'fecha_fin' => $booking['end_date'],
                    'huespedes' => $booking['guests'],
                    'estado' => $booking['status'],
                ]
            );
        }
    }

    private function processUnmappedRoomEvent($propertyId, $data)
    {
        Log::info("Procesando evento de habitación no mapeada para la propiedad {$propertyId}", $data);
        // Lógica para manejar habitaciones no mapeadas
    }
}

