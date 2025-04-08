<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\Cliente;
use App\Models\MensajeChat;
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

            if (!MensajeChat::where('channex_message_id', $messageId)->exists()) {
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

                // Verificar si ya existe un hilo de OpenAI asociado o crearlo
                $openaiThreadId = $this->getOrCreateOpenAIThread($payload['message_thread_id']);  // Obtén o crea el hilo de OpenAI

                // Actualizar el mensaje con el ID del hilo de OpenAI
                $mensajeChat->update(['openai_thread_id' => $openaiThreadId]);

                // Procesar el mensaje con OpenAI y obtener la respuesta
                $responseMessage = $this->procesarMensajeConAsistente($payload['message'], $openaiThreadId);

                // Enviar la respuesta a Channex
                $this->enviarRespuestaAChannex($responseMessage, $payload['booking_id']);
            }

            return response()->json(['status' => true, 'message' => 'Mensaje registrado']);
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
// Función para obtener el estado de la ejecución del hilo en OpenAI
private function ejecutarHiloStatus($id_thread, $id_runs)
{
    $token = env('TOKEN_OPENAI', 'valorPorDefecto');
    $url = 'https://api.openai.com/v1/threads/' . $id_thread . '/runs/' . $id_runs;

    $headers = [
        'Authorization: Bearer ' . $token,
        "OpenAI-Beta: assistants=v2"
    ];

    // Inicializar cURL y configurar las opciones
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // Ejecutar la solicitud y obtener la respuesta
    $response = curl_exec($curl);
    curl_close($curl);

    // Verificar si hubo un error
    if ($response === false) {
        Log::error("Error al obtener el estado del hilo de OpenAI: " . curl_error($curl));
        return [
            'status' => 'error',
            'message' => 'Error al obtener el estado del hilo de OpenAI',
        ];
    }

    // Procesar la respuesta
    $response_data = json_decode($response, true);

    // Si hay un error en la respuesta, lo manejamos
    if (isset($response_data['error'])) {
        Log::error("Error en la respuesta de OpenAI: " . $response_data['error']['message']);
        return [
            'status' => 'error',
            'message' => 'Error en la respuesta de OpenAI: ' . $response_data['error']['message'],
        ];
    }

    return $response_data;
}
// Función para obtener los pasos de la ejecución de un hilo en OpenAI
private function ejecutarHiloISteeps($id_thread, $id_runs)
{
    $token = env('TOKEN_OPENAI', 'valorPorDefecto');
    $url = 'https://api.openai.com/v1/threads/'.$id_thread.'/runs/'.$id_runs.'/steps';

    $headers = [
        'Authorization: Bearer ' . $token,
        "OpenAI-Beta: assistants=v2"
    ];

    // Inicializar cURL y configurar las opciones
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // Ejecutar la solicitud y obtener la respuesta
    $response = curl_exec($curl);
    curl_close($curl);

    // Verificar si hubo un error
    if ($response === false) {
        Log::error("Error al obtener los pasos del hilo de OpenAI: " . curl_error($curl));
        return [
            'status' => 'error',
            'message' => 'Error al obtener los pasos del hilo de OpenAI',
        ];
    }

    // Procesar la respuesta
    $response_data = json_decode($response, true);

    // Si hay un error en la respuesta, lo manejamos
    if (isset($response_data['error'])) {
        Log::error("Error en la respuesta de OpenAI: " . $response_data['error']['message']);
        return [
            'status' => 'error',
            'message' => 'Error en la respuesta de OpenAI: ' . $response_data['error']['message'],
        ];
    }

    return $response_data;
}
// Función para listar los mensajes de un hilo en OpenAI
private function listarMensajes($id_thread)
{
    $token = env('TOKEN_OPENAI', 'valorPorDefecto');
    $url = 'https://api.openai.com/v1/threads/' . $id_thread . '/messages';

    $headers = [
        'Authorization: Bearer ' . $token,
        "OpenAI-Beta: assistants=v2"
    ];

    // Inicializar cURL y configurar las opciones
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // Ejecutar la solicitud y obtener la respuesta
    $response = curl_exec($curl);
    curl_close($curl);

    // Verificar si hubo un error
    if ($response === false) {
        Log::error("Error al listar los mensajes del hilo de OpenAI: " . curl_error($curl));
        return [
            'status' => 'error',
            'message' => 'Error al listar los mensajes del hilo de OpenAI',
        ];
    }

    // Procesar la respuesta
    $response_data = json_decode($response, true);

    // Si hay un error en la respuesta, lo manejamos
    if (isset($response_data['error'])) {
        Log::error("Error en la respuesta de OpenAI: " . $response_data['error']['message']);
        return [
            'status' => 'error',
            'message' => 'Error en la respuesta de OpenAI: ' . $response_data['error']['message'],
        ];
    }

    // Retorna los mensajes del hilo
    return $response_data;
}


    // Obtener o crear el hilo de OpenAI
    private function getOrCreateOpenAIThread($channexThreadId)
    {
        // Verifica si existe un hilo de OpenAI para este mensaje de Channex
        $mensajeChat = MensajeChat::where('channex_message_id', $channexThreadId)->first();

        if ($mensajeChat && $mensajeChat->openai_thread_id) {
            return $mensajeChat->openai_thread_id;  // Ya existe un hilo de OpenAI
        }

        // Si no existe, creamos un nuevo hilo de OpenAI
        $hilo = $this->crearHilo();
        // Guardamos el ID del hilo de OpenAI
        return $hilo['id'];
    }

    private function procesarMensajeConAsistente($mensaje, $threadId)
    {
        // Enviar el mensaje al asistente dentro del hilo de OpenAI
        $this->mensajeHilo($threadId, $mensaje);

        // Ejecutar el hilo y esperar la respuesta
        $ejecucion = $this->ejecutarHilo($threadId);
        $ejecucionStatus = $this->ejecutarHiloStatus($threadId, $ejecucion['id']);

        // Esperar hasta que el hilo se complete
        while ($ejecucionStatus['status'] === 'in_progress') {
            sleep(2); // Espera activa antes de verificar el estado nuevamente

            // Verificar el estado del paso actual del hilo
            $pasosHilo = $this->ejecutarHiloISteeps($threadId, $ejecucion['id']);
            if ($pasosHilo['data'][0]['status'] === 'completed') {
                $ejecucionStatus = $this->ejecutarHiloStatus($threadId, $ejecucion['id']);
            }
        }

        if ($ejecucionStatus['status'] === 'completed') {
            // El hilo ha completado su ejecución, obtener la respuesta final
            $mensajes = $this->listarMensajes($threadId);
            return $mensajes['data'][0]['content'][0]['text']['value'] ?? "No se pudo obtener una respuesta válida.";
        }

        return "Hubo un error en la ejecución del asistente.";
    }

    // Función para crear un nuevo hilo de OpenAI
    private function crearHilo()
    {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        $url = 'https://api.openai.com/v1/threads';

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            "OpenAI-Beta: assistants=v2"
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }

    // Función para enviar un mensaje dentro de un hilo de OpenAI
    private function mensajeHilo($id_thread, $pregunta)
    {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        $url = 'https://api.openai.com/v1/threads/' . $id_thread . '/messages';

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            "OpenAI-Beta: assistants=v2"
        ];

        $body = [
            "role" => "user",
            "content" => $pregunta
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
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

    // Función para ejecutar el hilo de OpenAI
    private function ejecutarHilo($id_thread)
    {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        $url = 'https://api.openai.com/v1/threads/' . $id_thread . '/runs';

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            "OpenAI-Beta: assistants=v2"
        ];

        // Prepara el cuerpo de la solicitud
        $body = [
            "assistant_id" => 'asst_KfPsIM26MjS662Vlq6h9WnuH' // Tu ID de asistente de OpenAI
        ];

        // Inicializar cURL y configurar las opciones
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));

        // Ejecutar la solicitud y obtener la respuesta
        $response = curl_exec($curl);
        curl_close($curl);

        // Verificar si hubo un error
        if ($response === false) {
            Log::error("Error al ejecutar el hilo de OpenAI: " . curl_error($curl));
            return [
                'status' => 'error',
                'message' => 'Error al ejecutar el hilo de OpenAI',
            ];
        }

        // Procesar la respuesta
        $response_data = json_decode($response, true);

        // Si hay un error en la respuesta, lo manejamos
        if (isset($response_data['error'])) {
            Log::error("Error en la respuesta de OpenAI: " . $response_data['error']['message']);
            return [
                'status' => 'error',
                'message' => 'Error en la respuesta de OpenAI: ' . $response_data['error']['message'],
            ];
        }

        return $response_data;
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

