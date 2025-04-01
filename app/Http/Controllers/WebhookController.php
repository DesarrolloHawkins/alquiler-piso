<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\Cliente;
use App\Models\RatePlan;
use App\Models\Reserva;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    private $apiUrl;
    private $apiToken;
    public function __construct()
    {
        $this->apiUrl = env('CHANNEX_URL');
        $this->apiToken = env('CHANNEX_TOKEN');
    }

    private function saveToWebhooksFolder($filename, $data)
    {
        // Ruta completa para guardar en la carpeta "webhooks"
        $path = "webhooks/{$filename}";
        Storage::disk('publico')->put($path, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function ariChanges(Request $request, $id)
    {
        $apartamento = Apartamento::find($id);

        $fecha = Carbon::now()->format('Y-m-d_H-i-s'); // Formato para el nombre del archivo
        $filename = "ariChanges_{$fecha}.txt";

        $this->saveToWebhooksFolder($filename, $request->all());

        return response()->json(['status' => true]);
    }

    // public function bookingAny(Request $request, $id)
    // {
    //     $apartamento = Apartamento::find($id);

    //     $fecha = Carbon::now()->format('Y-m-d_H-i-s');
    //     $filename = "bookingAny_{$fecha}.txt";

    //     $this->saveToWebhooksFolder($filename, $request->all());
    //     // Extraer revision_id del payload
    //     $revisionId = $request->input('payload.revision_id');

    //     if (!$revisionId) {
    //         return response()->json(['status' => false, 'message' => 'No revision_id found'], 400);
    //     }
    //     $url = "https://staging.channex.io/api/v1/booking_revisions/{$revisionId}/ack";

    //     // Hacer la petición POST
    //     $response = Http::withHeaders([
    //         'user-api-key' => $this->apiToken, // Asegúrate de que $this->apiToken está definido
    //     ])->post($url, ['values' => []]); // Enviar datos vacíos si no hay updates

    //     // Verificar la respuesta
    //     if ($response->successful()) {
    //         return response()->json(['status' => true, 'message' => 'Acknowledged successfully']);
    //     } else {
    //         return response()->json(['status' => false, 'message' => 'Error in acknowledgment', 'error' => $response->body()], $response->status());
    //     }
    // }

    public function bookingAny(Request $request, $id)
    {
        // Guardar la request entrante como archivo para depuración
        $fileName = 'booking_' . now()->format('Ymd_His') . '_' . Str::random(6) . '.json';
        Storage::disk('local')->put('logs/bookings/' . $fileName, json_encode($request->all(), JSON_PRETTY_PRINT));

        // Buscar el apartamento
        $apartamento = Apartamento::find($id);
        if (!$apartamento) {
            return response()->json(['status' => false, 'message' => 'Apartamento no encontrado'], 404);
        }

        $revisionId = $request['revision_id'];
        $bookingId = $request['booking_id'];

        if (!$revisionId || !$bookingId) {
            return response()->json(['status' => true, 'message' => 'No revision_id or booking_id found']);
        }

        // Obtener la reserva desde Channex
        $bookingResponse = Http::withHeaders([
            'user-api-key' => $this->apiToken,
        ])->get("https://app.channex.io/api/v1/bookings/{$bookingId}");

        if (!$bookingResponse->successful()) {
            return response()->json([
                'status' => false,
                'message' => 'Error al obtener la información de la reserva',
                'error' => $bookingResponse->body()
            ], $bookingResponse->status());
        }

        $bookingData = $bookingResponse->json()['data']['attributes'];
        $estadoReserva = $bookingData['status']; // Ej: "new", "cancelled"

        // Si la reserva ha sido cancelada
        if ($estadoReserva === 'cancelled') {
            $codigoReserva = $bookingData['ota_reservation_code'] ?? $bookingData['booking_id'];

            $reserva = Reserva::where('codigo_reserva', $codigoReserva)->first();
            if ($reserva) {
                $reserva->estado_id = 4; // ID 4 es "Cancelado"
                $reserva->save();

                return response()->json(['status' => true, 'message' => 'Reserva cancelada actualizada en el sistema']);
            } else {
                return response()->json(['status' => false, 'message' => 'Reserva cancelada no encontrada en la base de datos']);
            }
        }

        // Si la reserva es nueva o confirmada, continuar con el flujo normal
        $cliente = Cliente::firstOrCreate(
            ['email' => $bookingData['customer']['mail']],
            [
                'alias' => $bookingData['customer']['name'] . ' ' . $bookingData['customer']['surname'],
                'nombre' => $bookingData['customer']['name'],
                'apellido1' => $bookingData['customer']['surname'],
                'telefono' => $bookingData['customer']['phone'],
                'direccion' => $bookingData['customer']['address'],
                'nacionalidad' => $bookingData['customer']['country'],
            ]
        );

        foreach ($bookingData['rooms'] as $room) {
            $ratePlanId = $room['rate_plan_id'] ?? null;
            if (!$ratePlanId) {
                \Log::error('Rate Plan ID no encontrado en la reserva', ['room' => $room]);
                continue;
            }

            $ratePlan = RatePlan::where('id_channex', $ratePlanId)->first();
            if (!$ratePlan) {
                \Log::error('RatePlan no encontrado en la base de datos', ['rate_plan_id' => $ratePlanId]);
                continue;
            }

            $roomTypeId = $ratePlan->room_type_id;

            Reserva::create([
                'cliente_id' => $cliente->id,
                'apartamento_id' => $apartamento->id,
                'room_type_id' => $roomTypeId,
                'origen' => 'Booking',
                'fecha_entrada' => $room['checkin_date'],
                'fecha_salida' => Carbon::parse($room['checkout_date'])->toDateString(),
                'codigo_reserva' => $bookingData['ota_reservation_code'] ?? $bookingData['booking_id'],
                'precio' => $room['amount'],
                'numero_personas' => $room['occupancy']['adults'],
                'neto' => $bookingData['amount'],
                'comision' => $bookingData['ota_commission'],
                'estado_id' => 1, // Nueva reserva
            ]);
        }

        // Marcar la revisión como revisada en Channex
        $ackResponse = Http::withHeaders([
            'user-api-key' => $this->apiToken,
        ])->post("https://app.channex.io/api/v1/booking_revisions/{$revisionId}/ack", ['values' => []]);

        if (!$ackResponse->successful()) {
            return response()->json([
                'status' => false,
                'message' => 'Error al marcar la reserva como revisada',
                'error' => $ackResponse->body()
            ], $ackResponse->status());
        }

        return response()->json(['status' => true, 'message' => 'Reserva guardada y marcada como revisada']);
    }





    public function bookingUnmappedRoom(Request $request, $id)
    {
        $apartamento = Apartamento::find($id);

        $fecha = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "bookingUnmappedRoom_{$fecha}.txt";

        $this->saveToWebhooksFolder($filename, $request->all());

        return response()->json(['status' => true]);
    }

    public function bookingUnmappedRate(Request $request, $id)
    {
        $apartamento = Apartamento::find($id);

        $fecha = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "bookingUnmappedRate_{$fecha}.txt";

        $this->saveToWebhooksFolder($filename, $request->all());

        return response()->json(['status' => true]);
    }

    public function message(Request $request, $id)
    {
        $apartamento = Apartamento::find($id);

        $fecha = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "message_{$fecha}.txt";

        $this->saveToWebhooksFolder($filename, $request->all());

        return response()->json(['status' => true]);
    }

    public function review(Request $request, $id)
    {
        $apartamento = Apartamento::find($id);

        $fecha = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "review_{$fecha}.txt";

        $this->saveToWebhooksFolder($filename, $request->all());

        return response()->json(['status' => true]);
    }

    public function reservationRequest(Request $request, $id)
    {
        $apartamento = Apartamento::find($id);

        $fecha = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "reservationRequest_{$fecha}.txt";

        $this->saveToWebhooksFolder($filename, $request->all());

        return response()->json(['status' => true]);
    }

    public function syncError(Request $request, $id)
    {
        $apartamento = Apartamento::find($id);

        $fecha = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "syncError_{$fecha}.txt";

        $this->saveToWebhooksFolder($filename, $request->all());

        return response()->json(['status' => true]);
    }

    public function alterationRequest(Request $request, $id)
    {
        $apartamento = Apartamento::find($id);

        $fecha = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "alterationRequest_{$fecha}.txt";

        $this->saveToWebhooksFolder($filename, $request->all());

        return response()->json(['status' => true]);
    }

























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
                $this->processAriEvent($validated['property_id'], $validated['payload']);
                break;
            case 'booking':
                $this->processBookingEvent($validated['property_id'], $validated['payload']);
                break;
            case 'booking_unmapped_room':
                $this->processUnmappedRoomEvent($validated['property_id'], $validated['payload']);
                break;
            default:
                Log::warning("Evento desconocido recibido: {$validated['event']}");
        }

        return response()->json(['message' => 'Webhook recibido con éxito'], 200);
    }

    private function processAriEvent($propertyId, $data)
    {
        $fecha = Carbon::now()->format('Y-m-d_H-i-s'); // Puedes cambiar el formato según lo que necesites

        Log::info("Procesando evento ARI para la propiedad {$propertyId}", $data);
        Storage::disk('publico')->put("accepted-reservation{$fecha}.txt", json_encode($data));

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

