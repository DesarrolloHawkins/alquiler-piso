<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\ChatGpt;
use App\Models\Cliente;
use App\Models\MensajeChat;
use App\Models\PromptAsistente;
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
    private $openaiApiKey;

    public function __construct()
    {
        $this->apiUrl = env('CHANNEX_URL');
        $this->apiToken = env('CHANNEX_TOKEN');
        $this->openaiApiKey = env('OPENAI_API_KEY'); // Asegúrate de tener tu API key de OpenAI en .env

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

    public function bookingAny(Request $request, $id)
    {
        // dd($request->all());
        // Guardar la request entrante como archivo para depuración
        $fileName = 'booking_' . now()->format('Ymd_His') . '_' . Str::random(6) . '.json';
        Storage::disk('local')->put('logs/bookings/' . $fileName, json_encode($request->all(), JSON_PRETTY_PRINT));

        $evento = $request->input('event');

        // === GESTIÓN DE MENSAJES ===
        if ($evento === 'message') {
            $payload = $request->input('payload');
            $messageId = $payload['ota_message_id'];

            if (!MensajeChat::where('channex_message_id', $messageId)->exists() && $payload['sender'] != 'property') {
                // Guardamos el mensaje en la base de datos
                $mensajeChat = MensajeChat::create([
                    'channex_message_id' => $messageId,
                    'booking_id' => $payload['booking_id'],
                    'thread_id' => $payload['message_thread_id'], // Channex thread_id
                    'property_id' => $payload['property_id'],
                    'sender' => $payload['sender'],
                    'message' => $payload['message'],
                    'attachments' => $payload['attachments'] ?? [],
                    'have_attachment' => $payload['have_attachment'] ?? false,
                    'received_at' => Carbon::parse($request->input('timestamp')),
                    'openai_thread_id' => null, // Inicialmente vacío, se llenará después
                ]);

                // // Verificar si ya existe un hilo de OpenAI asociado o crearlo
                // $openaiThreadId = $this->getOrCreateOpenAIThread($payload['message_thread_id']);  // Obtén o crea el hilo de OpenAI

                // // Actualizar el mensaje con el ID del hilo de OpenAI
                // $mensajeChat->update(['openai_thread_id' => $openaiThreadId]);

                // // Procesar el mensaje con OpenAI y obtener la respuesta
                // $responseMessage = $this->procesarMensajeConAsistente($payload['message'], $openaiThreadId);
                //function enviarMensajeOpenAiChatCompletions($id, $nuevoMensaje, $remitente)

                $enviaChatGPT = $this->enviarMensajeOpenAiChatCompletions($mensajeChat->id, $payload['message'], $payload['sender']);
                // Enviar la respuesta a Channex
                $this->enviarRespuestaAChannex($enviaChatGPT, $payload['booking_id']);

                return response()->json(['status' => true, 'message' => 'Mensaje registrado', 'content' => $enviaChatGPT]);
            }
            return response()->json(['status' => true, 'message' => 'El Mensaje ya estaba registrado']);
        }


        // Buscar el apartamento
        $apartamento = Apartamento::find($id);
        if (!$apartamento) {
            return response()->json(['status' => false, 'message' => 'Apartamento no encontrado'], 404);
        }

        $revisionId = $request->input('payload.revision_id');
        $bookingId = $request->input('payload.booking_id');


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

    function enviarMensajeOpenAiChatCompletions($id, $nuevoMensaje, $remitente)
    {
        $apiKey = env('OPENAI_API_KEY');
        $modelo = 'gpt-4o';
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $promptAsistente = PromptAsistente::first(); // o all()->first() si usas all()
    
        $promptSystem = [
            "role" => "system",
            "content" => $promptAsistente ? $promptAsistente->prompt : "No hay prompt configurado aún."
        ];
    
        // Guardar el mensaje del usuario
        ChatGpt::create([
            'id_mensaje' => $id,
            'remitente' => $remitente,
            'mensaje' => $nuevoMensaje,
            'respuesta' => null,
            'status' => 0, // por 'respondido'
            'date' => now()
        ]);
    
        // Historial: últimos 20 mensajes válidos (mensaje + respuesta)
        $historial = ChatGpt::where('remitente', $remitente)
            ->orderBy('date', 'desc')
            ->limit(20)
            ->get()
            ->reverse()
            ->flatMap(function ($chat) {
                $mensajes = [];
    
                if (!empty($chat->mensaje)) {
                    $mensajes[] = [
                        "role" => "user",
                        "content" => $chat->mensaje,
                    ];
                }
    
                if (!empty($chat->respuesta)) {
                    $mensajes[] = [
                        "role" => "assistant",
                        "content" => $chat->respuesta,
                    ];
                }
    
                return $mensajes;
            })
            ->toArray();
    
        // Añadir nuevo mensaje del usuario
        $historial[] = [
            "role" => "user",
            "content" => $nuevoMensaje,
        ];
    
        // Unir con prompt
        $mensajes = array_merge([$promptSystem], $historial);
        // dd($nuevoMensaje);
        // Llamar a OpenAI
        $response = Http::withToken($apiKey)
            ->post($endpoint, [
                'model' => $modelo,
                'messages' => $mensajes,
                'temperature' => 0.7,
            ]);
    
        if ($response->failed()) {
            \Log::error('Error al enviar a OpenAI: ' . $response->body());
            return null;
        }
    
        $respuestaTexto = $response->json('choices.0.message.content');
    
        // Guardar la respuesta generada
        ChatGpt::where('remitente', $remitente)
            ->whereNull('respuesta')
            ->orderByDesc('created_at')
            ->limit(1)
            ->update([
                'respuesta' => $respuestaTexto,
                'status' => 1, // por 'respondido'
            ]);
    
        return $respuestaTexto;
    }
    private function enviarRespuestaAChannex($mensaje, $bookingId)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://app.channex.io/api/v1/bookings/{$bookingId}/messages",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                'message' => [
                    'message' => $mensaje
                ],
            ]),
            CURLOPT_HTTPHEADER => [
                'user-api-key: ' . $this->apiToken,
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        Log::info('Respuesta enviada a Channex:', ['booking_id' => $bookingId, 'response' => $response]);

        return $response;
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

