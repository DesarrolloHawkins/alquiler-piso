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

    /**
     * Maneja webhooks de reservas de Channex
     * 
     * Según la documentación de Channex:
     * - Cada modificación de reserva genera un nuevo revision_id
     * - El booking_id permanece igual para la misma reserva
     * - Se debe verificar si la reserva ya existe antes de crear una nueva
     * - Las modificaciones incluyen cambios en fechas, habitaciones, precios, etc.
     * 
     * LÓGICA IMPLEMENTADA:
     * 1. Si la reserva existe (modificación): ACTUALIZAR la existente
     * 2. Si la reserva NO existe (nueva): CREAR una nueva
     * 3. NUNCA crear nueva reserva después de actualizar una existente
     * 
     * @param Request $request
     * @param int $id ID del apartamento
     * @return \Illuminate\Http\JsonResponse
     */
    public function bookingAny(Request $request, $id)
    {
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

                // === NUEVO BLOQUE: RESTABLECER DISPONIBILIDAD COMPLETA ===
                $roomType = RoomType::find($reserva->room_type_id);
                if ($roomType && $roomType->id_channex) {
                    $start = Carbon::parse($reserva->fecha_entrada);
                    $end = Carbon::parse($reserva->fecha_salida)->subDay(); // No incluir el checkout

                    $values = [];
                    for ($date = $start; $date->lte($end); $date->addDay()) {
                        $values[] = [
                            'property_id'   => $apartamento->id_channex,
                            'room_type_id'  => $roomType->id_channex,
                            'date'          => $date->toDateString(),
                            'availability'  => 1,
                        ];
                    }

                    Http::withHeaders([
                        'user-api-key' => $this->apiToken,
                    ])->post("{$this->apiUrl}/availability", [
                        'values' => $values
                    ]);
                }
                // Llamar a la función fullSync
                return response()->json(['status' => true, 'message' => 'Reserva cancelada actualizada en el sistema']);
            } else {
                return response()->json(['status' => false, 'message' => 'Reserva cancelada no encontrada en la base de datos']);
            }
        }

        // Verificar si es una reserva nueva o una modificación
        $codigoReserva = $bookingData['ota_reservation_code'] ?? $bookingData['booking_id'];
        $reservaExistente = Reserva::where('codigo_reserva', $codigoReserva)
            ->orWhere('id_channex', $bookingId)
            ->first();

        Log::info('Procesando webhook de reserva', [
            'evento' => $evento,
            'booking_id' => $bookingId,
            'revision_id' => $revisionId,
            'codigo_reserva' => $codigoReserva,
            'es_modificacion' => $reservaExistente ? true : false,
            'estado_channex' => $estadoReserva
        ]);

        $customer = $bookingData['customer'];

        // Normaliza el teléfono eliminando espacios, guiones, etc.
        $telefono = preg_replace('/\D/', '', $customer['phone']);

        if (!empty($customer['mail'])) {
            $cliente = Cliente::firstOrCreate(
                ['email' => $customer['mail']],
                [
                    'alias' => $customer['name'] . ' ' . $customer['surname'],
                    'nombre' => $customer['name'],
                    'apellido1' => $customer['surname'],
                    'telefono' => $telefono,
                    'direccion' => $customer['address'],
                    'nacionalidad' => $customer['country'],
                ]
            );
        } else {
            $cliente = Cliente::where('telefono', $telefono)->first();

            if (!$cliente) {
                $cliente = Cliente::create([
                    'alias' => $customer['name'] . ' ' . $customer['surname'],
                    'nombre' => $customer['name'],
                    'apellido1' => $customer['surname'],
                    'telefono' => $telefono,
                    'direccion' => $customer['address'],
                    'nacionalidad' => $customer['country'],
                    'email' => null,
                ]);
            }
        }

        // === LÓGICA DE MODIFICACIÓN vs NUEVA RESERVA ===
        // Si es una modificación, actualizar la reserva existente
        if ($reservaExistente) {
            Log::info('Modificando reserva existente', [
                'codigo_reserva' => $codigoReserva,
                'id_channex' => $bookingId,
                'revision_id' => $revisionId,
                'reserva_id' => $reservaExistente->id,
                'datos_actuales' => [
                    'fecha_entrada' => $reservaExistente->fecha_entrada,
                    'fecha_salida' => $reservaExistente->fecha_salida,
                    'precio' => $reservaExistente->precio,
                    'numero_personas' => $reservaExistente->numero_personas,
                    'neto' => $reservaExistente->neto
                ]
            ]);

            // Actualizar datos del cliente si han cambiado
            $reservaExistente->cliente_id = $cliente->id;
            
            // Actualizar datos generales de la reserva
            $reservaExistente->update([
                'cliente_id' => $cliente->id,
                'origen' => $bookingData['ota_name'],
                'neto' => floatval(str_replace(',', '.', $bookingData['amount'])),
                'comision' => floatval(str_replace(',', '.', $bookingData['ota_commission'])),
                'updated_at' => now(),
            ]);
            
            // Actualizar las habitaciones con los nuevos datos (fechas y precios)
            // Estos son los campos más importantes que suelen cambiar en las modificaciones:
            // - fecha_entrada: Cambio de fecha de llegada
            // - fecha_salida: Cambio de fecha de salida  
            // - precio: Cambio de tarifa/precio
            // - numero_personas: Cambio en el número de huéspedes
            // - room_type_id: Cambio de tipo de habitación
            foreach ($bookingData['rooms'] as $room) {
                $ratePlanId = $room['rate_plan_id'] ?? null;
                if (!$ratePlanId) {
                    Log::error('Rate Plan ID no encontrado en la reserva', ['room' => $room]);
                    continue;
                }

                $ratePlan = RatePlan::where('id_channex', $ratePlanId)->first();
                if (!$ratePlan) {
                    Log::error('RatePlan no encontrado en la base de datos', ['rate_plan_id' => $ratePlanId]);
                    continue;
                }

                $roomTypeId = $ratePlan->room_type_id;

                // Actualizar la reserva existente con los nuevos datos
                $reservaExistente->update([
                    'fecha_entrada' => $room['checkin_date'],
                    'fecha_salida' => Carbon::parse($room['checkout_date'])->toDateString(),
                    'precio' => floatval(str_replace(',', '.', $room['amount'])),
                    'numero_personas' => $room['occupancy']['adults'],
                    'room_type_id' => $roomTypeId,
                ]);
                
                // Detectar cambios importantes
                $cambios = [];
                if ($reservaExistente->fecha_entrada != $room['checkin_date']) {
                    $cambios['fecha_entrada'] = [
                        'anterior' => $reservaExistente->fecha_entrada,
                        'nuevo' => $room['checkin_date']
                    ];
                }
                if ($reservaExistente->fecha_salida != Carbon::parse($room['checkout_date'])->toDateString()) {
                    $cambios['fecha_salida'] = [
                        'anterior' => $reservaExistente->fecha_salida,
                        'nuevo' => Carbon::parse($room['checkout_date'])->toDateString()
                    ];
                }
                if ($reservaExistente->precio != floatval(str_replace(',', '.', $room['amount']))) {
                    $cambios['precio'] = [
                        'anterior' => $reservaExistente->precio,
                        'nuevo' => floatval(str_replace(',', '.', $room['amount']))
                    ];
                }
                if ($reservaExistente->numero_personas != $room['occupancy']['adults']) {
                    $cambios['numero_personas'] = [
                        'anterior' => $reservaExistente->numero_personas,
                        'nuevo' => $room['occupancy']['adults']
                    ];
                }
                
                Log::info('Reserva actualizada con nuevos datos', [
                    'reserva_id' => $reservaExistente->id,
                    'fecha_entrada' => $room['checkin_date'],
                    'fecha_salida' => Carbon::parse($room['checkout_date'])->toDateString(),
                    'precio' => floatval(str_replace(',', '.', $room['amount'])),
                    'numero_personas' => $room['occupancy']['adults'],
                    'cambios_detectados' => $cambios
                ]);
            }
            
            Log::info('Reserva existente actualizada', [
                'reserva_id' => $reservaExistente->id,
                'codigo_reserva' => $codigoReserva
            ]);
            
            // IMPORTANTE: No crear nueva reserva, solo actualizar la existente
            Log::info('Modificación completada - NO se creará nueva reserva');
        } else {
            Log::info('Creando nueva reserva', [
                'codigo_reserva' => $codigoReserva,
                'booking_id' => $bookingId
            ]);
        }

        // Solo crear nueva reserva si NO es una modificación
        if (!$reservaExistente) {
            foreach ($bookingData['rooms'] as $room) {
                $ratePlanId = $room['rate_plan_id'] ?? null;
                if (!$ratePlanId) {
                    Log::error('Rate Plan ID no encontrado en la reserva', ['room' => $room]);
                    continue;
                }

                $ratePlan = RatePlan::where('id_channex', $ratePlanId)->first();
                if (!$ratePlan) {
                    Log::error('RatePlan no encontrado en la base de datos', ['rate_plan_id' => $ratePlanId]);
                    continue;
                }

                $roomTypeId = $ratePlan->room_type_id;

                Reserva::create([
                    'cliente_id' => $cliente->id,
                    'apartamento_id' => $apartamento->id,
                    'room_type_id' => $roomTypeId,
                    'origen' => $bookingData['ota_name'],
                    'fecha_entrada' => $room['checkin_date'],
                    'fecha_salida' => Carbon::parse($room['checkout_date'])->toDateString(),
                    'codigo_reserva' => $codigoReserva,
                    'precio' => floatval(str_replace(',', '.', $room['amount'])),
                    'numero_personas' => $room['occupancy']['adults'],
                    'neto' => floatval(str_replace(',', '.', $bookingData['amount'])),
                    'comision' => floatval(str_replace(',', '.', $bookingData['ota_commission'])),
                    'estado_id' => 1, // Nueva reserva
                    'id_channex' => $bookingId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                Log::info('Nueva reserva creada', [
                    'codigo_reserva' => $codigoReserva,
                    'booking_id' => $bookingId,
                    'fecha_entrada' => $room['checkin_date'],
                    'fecha_salida' => Carbon::parse($room['checkout_date'])->toDateString(),
                    'precio' => floatval(str_replace(',', '.', $room['amount']))
                ]);
            }
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

        $mensaje = $reservaExistente 
            ? 'Reserva modificada y marcada como revisada' 
            : 'Nueva reserva guardada y marcada como revisada';

        $response = [
            'status' => true, 
            'message' => $mensaje,
            'tipo' => $reservaExistente ? 'modificacion' : 'nueva',
            'codigo_reserva' => $codigoReserva,
            'revision_id' => $revisionId
        ];

        // Si es una modificación, incluir información sobre los cambios
        if ($reservaExistente && isset($cambios) && !empty($cambios)) {
            $response['cambios'] = $cambios;
            $response['message'] .= ' - Campos actualizados: ' . implode(', ', array_keys($cambios));
        }

        return response()->json($response);
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
            Log::error('Error al enviar a OpenAI: ' . $response->body());
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

        /**
     * Envía un mensaje automático a Channex usando el bookingId
     * @param string $mensaje El mensaje a enviar
     * @param string $bookingId El ID de la reserva en Channex
     * @return mixed La respuesta de la API
     */
    public static function enviarMensajeAutomaticoAChannex($mensaje, $bookingId)
    {
        $apiToken = env('CHANNEX_TOKEN');

        if (!$apiToken || !$bookingId) {
            Log::error('Faltan credenciales o bookingId para enviar mensaje a Channex', [
                'apiToken' => $apiToken ? 'presente' : 'ausente',
                'bookingId' => $bookingId
            ]);
            return false;
        }

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
                'user-api-key: ' . $apiToken,
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode === 200 || $httpCode === 201) {
            Log::info('Mensaje automático enviado exitosamente a Channex', [
                'booking_id' => $bookingId,
                'http_code' => $httpCode
            ]);
            return true;
        } else {
            Log::error('Error al enviar mensaje automático a Channex', [
                'booking_id' => $bookingId,
                'http_code' => $httpCode,
                'response' => $response
            ]);
            return false;
        }
    }

    /**
     * Crea un mensaje de texto plano para el chat basado en el tipo de mensaje
     * @param string $tipo Tipo de mensaje: 'dni', 'claves', 'bienvenida', 'consulta', 'ocio', 'despedida'
     * @param array $datos Datos necesarios para el mensaje
     * @param string $idioma Idioma del mensaje
     * @return string Mensaje formateado para el chat
     */
    public static function crearMensajeChat($tipo, $datos, $idioma = 'en')
    {
        switch ($tipo) {
            case 'dni':
                $token = $datos['token'];
                $url = "https://crm.apartamentosalgeciras.com/dni-user/{$token}";

                switch ($idioma) {
                    case 'es':
                        return "¡Gracias por reservar en los apartamentos Hawkins!\n\nLa legislación española nos obliga a solicitar su Documento Nacional de Identidad o pasaporte. Es obligatorio que nos lo facilite o no podrá alojarse en el apartamento.\n\nPuede rellenar sus datos aquí: {$url}\n\nLas claves de acceso se le enviarán el día de su llegada por WhatsApp y correo electrónico.";
                    case 'fr':
                        return "Merci de réserver chez les appartements Hawkins!\n\nLa législation espagnole nous oblige à vous demander votre carte d'identité nationale ou votre passeport. Il est obligatoire que vous nous le fournissiez, sinon vous ne pourrez pas séjourner dans l'appartement.\n\nVous pouvez remplir vos informations ici: {$url}\n\nLes codes d'accès vous seront envoyés le jour de votre arrivée par WhatsApp et e-mail.";
                    case 'de':
                        return "Danke, dass Sie sich für die Hawkins Apartments entschieden haben!\n\nDie spanische Gesetzgebung verpflichtet uns, Ihren Personalausweis oder Ihren Reisepass anzufordern. Es ist obligatorisch, dass Sie uns diesen zur Verfügung stellen, ansonsten können Sie nicht in der Wohnung übernachten.\n\nSie können Ihre Informationen hier ausfüllen: {$url}\n\nDie Zugangscodes werden Ihnen am Tag Ihrer Ankunft per WhatsApp und E-Mail zugesendet.";
                    default: // en
                        return "Thank you for booking at Hawkins Apartments!\n\nThe Spanish legislation requires us to request your National Identity Document or your passport. It is mandatory that you provide it to us or you will not be able to stay in the apartment.\n\nYou can fill out your information here: {$url}\n\nThe access codes will be sent to you on the day of your arrival by WhatsApp and email.";
                }
                break;

            case 'claves':
                $nombre = $datos['nombre'];
                $apartamento = $datos['apartamento'];
                $claveEntrada = $datos['claveEntrada'];
                $clavePiso = $datos['clavePiso'];
                $url = $datos['url'] ?? 'https://goo.gl/maps/qb7AxP1JAxx5yg3N9';

                switch ($idioma) {
                    case 'es':
                        return "¡Hola {$nombre}!\n\nLa ubicación de los apartamentos es: {$url}\n\nTu apartamento es el {$apartamento}. Los códigos para entrar son:\n• Puerta principal: {$claveEntrada}\n• Puerta de tu apartamento: {$clavePiso}\n\n¡Espero que pases una estancia maravillosa!";
                    case 'fr':
                        return "Bonjour {$nombre}!\n\nL'emplacement des appartements est: {$url}\n\nVotre appartement est le {$apartamento}. Les codes pour entrer sont:\n• Porte principale: {$claveEntrada}\n• Porte de votre appartement: {$clavePiso}\n\nJ'espère que vous passerez un séjour merveilleux!";
                    case 'de':
                        return "Hallo {$nombre}!\n\nDie Lage der Apartments ist: {$url}\n\nIhr Apartment ist das {$apartamento}. Die Codes zum Betreten sind:\n• Haupteingangstür: {$claveEntrada}\n• Tür Ihrer Wohnung: {$clavePiso}\n\nIch hoffe, Sie haben einen wunderbaren Aufenthalt!";
                    default: // en
                        return "Hello {$nombre}!\n\nThe location of the apartments is: {$url}\n\nYour apartment is {$apartamento}. The codes to enter are:\n• Main door: {$claveEntrada}\n• Your apartment door: {$clavePiso}\n\nI hope you have a wonderful stay!";
                }
                break;

            case 'bienvenida':
                $nombre = $datos['nombre'];

                switch ($idioma) {
                    case 'es':
                        return "¡Hola {$nombre}! ¡Bienvenido a los apartamentos Hawkins! Esperamos que disfrutes de tu estancia.";
                    case 'fr':
                        return "Bonjour {$nombre}! Bienvenue aux appartements Hawkins! Nous espérons que vous apprécierez votre séjour.";
                    case 'de':
                        return "Hallo {$nombre}! Willkommen in den Hawkins Apartments! Wir hoffen, Sie genießen Ihren Aufenthalt.";
                    default: // en
                        return "Hello {$nombre}! Welcome to Hawkins Apartments! We hope you enjoy your stay.";
                }
                break;

            case 'consulta':
                $nombre = $datos['nombre'];

                switch ($idioma) {
                    case 'es':
                        return "¡Hola {$nombre}! ¿Tienes alguna consulta o necesitas ayuda con algo durante tu estancia? Estamos aquí para ayudarte.";
                    case 'fr':
                        return "Bonjour {$nombre}! Avez-vous des questions ou avez-vous besoin d'aide pour quelque chose pendant votre séjour? Nous sommes là pour vous aider.";
                    case 'de':
                        return "Hallo {$nombre}! Haben Sie Fragen oder brauchen Sie Hilfe bei etwas während Ihres Aufenthalts? Wir sind hier, um Ihnen zu helfen.";
                    default: // en
                        return "Hello {$nombre}! Do you have any questions or need help with anything during your stay? We are here to help you.";
                }
                break;

            case 'ocio':
                $nombre = $datos['nombre'];

                switch ($idioma) {
                    case 'es':
                        return "¡Hola {$nombre}! ¿Te gustaría conocer algunos lugares interesantes para visitar o actividades para hacer en la zona? ¡Estamos encantados de recomendarte!";
                    case 'fr':
                        return "Bonjour {$nombre}! Souhaitez-vous connaître quelques endroits intéressants à visiter ou des activités à faire dans la région? Nous serions ravis de vous recommander!";
                    case 'de':
                        return "Hallo {$nombre}! Möchten Sie einige interessante Orte zum Besuchen oder Aktivitäten in der Gegend kennenlernen? Wir würden uns freuen, Ihnen zu empfehlen!";
                    default: // en
                        return "Hello {$nombre}! Would you like to know some interesting places to visit or activities to do in the area? We'd be happy to recommend!";
                }
                break;

            case 'despedida':
                $nombre = $datos['nombre'];

                switch ($idioma) {
                    case 'es':
                        return "¡Hola {$nombre}! Esperamos que hayas disfrutado de tu estancia en los apartamentos Hawkins. ¡Que tengas un buen viaje de regreso!";
                    case 'fr':
                        return "Bonjour {$nombre}! Nous espérons que vous avez apprécié votre séjour aux appartements Hawkins. Bon voyage de retour!";
                    case 'de':
                        return "Hallo {$nombre}! Wir hoffen, Sie haben Ihren Aufenthalt in den Hawkins Apartments genossen. Gute Heimreise!";
                    default: // en
                        return "Hello {$nombre}! We hope you enjoyed your stay at Hawkins Apartments. Have a good trip back!";
                }
                break;

            default:
                return "Mensaje no reconocido";
        }
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

