<?php

namespace App\Http\Controllers;

use App\Models\ChatGpt;
use App\Models\Cliente;
use App\Models\Configuraciones;
use App\Models\Mensaje;
use App\Models\MensajeAuto;
use App\Models\PromptAsistente;
use App\Models\Reparaciones;
use App\Models\Reserva;
use App\Models\Whatsapp;
use App\Services\ClienteService;
use CURLFile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberToCarrierMapper;
use libphonenumber\geocoding\PhoneNumberOfflineGeocoder;
use libphonenumber\PhoneNumberFormat;
use Illuminate\Support\Facades\Log;
use Laravel\Prompts\Prompt;
use PhpOption\None;
use App\Models\WhatsappLog;
use App\Models\WhatsappMensaje;
use Carbon\Carbon;
use App\Models\WhatsappEstadoMensaje;

class WhatsappController extends Controller
{
    protected $clienteService;

    public function __construct(ClienteService $clienteService)
    {
        $this->clienteService = $clienteService;
    }

    public function hookWhatsapp(Request $request)
    {
        $responseJson = env('WHATSAPP_KEY', 'valorPorDefecto');

        $query = $request->all();
        $mode = $query['hub_mode'];
        $token = $query['hub_verify_token'];
        $challenge = $query['hub_challenge'];

        // Formatear la fecha y hora actual
        $dateTime = Carbon::now()->format('Y-m-d_H-i-s'); // Ejemplo de formato: 2023-11-13_15-30-25

        // Crear un nombre de archivo con la fecha y hora actual
        $filename = "hookWhatsapp_{$dateTime}.txt";

        Storage::disk('local')->put($filename, json_encode($request->all()));

        return response($challenge, 200)->header('Content-Type', 'text/plain');

    }

    public function processHookWhatsapp(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        // 1. Guardar el JSON original
        WhatsappLog::create(['contenido' => $data]);

        // 2. Guardar el archivo en disco
        if (!Storage::exists('whatsapp/json')) {
            Storage::makeDirectory('whatsapp/json');
        }

        $timestamp = now()->format('Ymd_His_u');
        Storage::put("whatsapp/json/{$timestamp}.json", json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // 3. Extraer los datos
        $entry = $data['entry'][0]['changes'][0]['value'] ?? [];

        // 4. Procesar mensajes entrantes
        if (isset($entry['messages'])) {
            foreach ($entry['messages'] as $mensaje) {
                $this->procesarMensajeYResponder($mensaje, $entry);
            }
        }

        // 5. Procesar estados de mensajes enviados
        if (isset($entry['statuses'])) {
            foreach ($entry['statuses'] as $status) {
                $this->procesarStatus($status);
            }
        }

        return response(200)->header('Content-Type', 'text/plain');
    }

    public function procesarStatus(array $status)
    {
        $mensaje = WhatsappMensaje::where('recipient_id', $status['id'])->first(); // CAMBIO AQUÍ

        if ($mensaje) {
            // Guardar último estado
            $mensaje->estado = $status['status'];
            $mensaje->conversacion_id = $status['conversation']['id'] ?? null;
            $mensaje->origen_conversacion = $status['conversation']['origin']['type'] ?? null;
            $mensaje->expiracion_conversacion = isset($status['conversation']['expiration_timestamp'])
                ? Carbon::createFromTimestamp($status['conversation']['expiration_timestamp'])
                : null;
            $mensaje->billable = $status['pricing']['billable'] ?? null;
            $mensaje->categoria_precio = $status['pricing']['category'] ?? null;
            $mensaje->modelo_precio = $status['pricing']['pricing_model'] ?? null;
            $mensaje->errores = $status['errors'] ?? null;
            $mensaje->save();

            // Guardar en histórico
            WhatsappEstadoMensaje::create([
                'whatsapp_mensaje_id' => $mensaje->id,
                'estado' => $status['status'],
                'recipient_id' => $status['recipient_id'] ?? null,
                'fecha_estado' => isset($status['timestamp']) ? Carbon::createFromTimestamp($status['timestamp']) : now(),
            ]);
            return response()->json(['status' => 'ok', 'mensaje' => $mensaje]);
        } else {
            Log::warning("⚠️ No se encontró mensaje con recipient_id = {$status['id']} para guardar estado.");
        }
        return response()->json(['status' => 'faile']);

    }



    public function procesarMensajeYResponder(array $mensaje, array $entry)
    {
        $waId = $mensaje['from'];
        $tipo = $mensaje['type'];
        $id = $mensaje['id'];
        $timestamp = $mensaje['timestamp'] ?? null;

        $contenido = null;
        if ($tipo === 'text') {
            $contenido = $mensaje['text']['body'];
        } elseif ($tipo === 'image' && isset($mensaje['image']['id'])) {
            $contenido = '[Imagen] ' . $mensaje['image']['id'];
        } elseif ($tipo === 'audio' && isset($mensaje['audio']['id'])) {
            $contenido = '[Audio] ' . $mensaje['audio']['id'];
        } elseif ($tipo === 'document') {
            $contenido = '[Documento] ' . ($mensaje['document']['filename'] ?? 'sin nombre');
        }

        $whatsappMensaje = WhatsappMensaje::create([
            'mensaje_id' => $id,
            'tipo' => $tipo,
            'contenido' => $contenido,
            'remitente' => $waId,
            'fecha_mensaje' => $timestamp ? Carbon::createFromTimestamp($timestamp) : now(),
            'metadata' => $mensaje
        ]);

        // Solo si es texto, responde con ChatGPT
        if ($tipo === 'text') {
            // 1. Siempre crear el registro de entrada
            $chat = ChatGpt::create([
                'id_mensaje' => $id,
                'whatsapp_mensaje_id' => $whatsappMensaje->id,
                'remitente' => $waId,
                'mensaje' => $contenido,
                'respuesta' => null, // respuesta aún no disponible
                'status' => 0, // pendiente de respuesta
                'type' => 'text',
                'date' => now(),
            ]);

            // 2. Intentar obtener respuesta de ChatGPT
            $respuestaTexto = $this->enviarMensajeOpenAiChatCompletions($contenido, $waId);

            if ($respuestaTexto) {
                // 3. Solo si hay respuesta, actualizar la fila y contestar
                $chat->update([
                    'respuesta' => $respuestaTexto,
                    'status' => 1,
                ]);

                $response = $this->contestarWhatsapp($waId, $respuestaTexto, $whatsappMensaje);
                // dd($response);
                return response()->json(['status' => 'ok', 'respuesta' => $respuestaTexto]);
            } else {
                dd($respuestaTexto);

                Log::warning("❌ Error de ChatGPT. No se contestó a {$waId}.");
                return response()->json(['status' => 'faile']);

                // Se mantiene la fila con status = 0 y respuesta = null
            }
        }

    }

    function enviarMensajeOpenAiChatCompletions($nuevoMensaje, $remitente)
    {
        $apiKey = env('OPENAI_API_KEY');
        $modelo = 'gpt-4o';
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $promptAsistente = PromptAsistente::first();

        $tools = [
            [
                "type" => "function",
                "function" => [
                    "name" => "obtener_claves",
                    "description" => "Devuelve la clave de acceso al apartamento según el código de reserva, solo si es la fecha de entrada, ha pasado la hora de entrada y el cliente ha entregado el DNI.",
                    "parameters" => [
                        "type" => "object",
                        "properties" => [
                            "codigo_reserva" => [
                                "type" => "string",
                                "description" => "Código de la reserva del cliente"
                            ]
                        ],
                        "required" => ["codigo_reserva"]
                    ]
                ]
            ]
        ];

        $promptSystem = [
            "role" => "system",
            "content" => $promptAsistente ? $promptAsistente->prompt : "Eres un asistente de apartamentos turísticos."
        ];

        $historial = ChatGpt::where('remitente', $remitente)
            ->orderBy('date', 'desc')
            ->limit(20)
            ->get()
            ->reverse()
            ->flatMap(function ($chat) {
                $mensajes = [];
                if (!empty($chat->mensaje)) {
                    $mensajes[] = ["role" => "user", "content" => $chat->mensaje];
                }
                if (!empty($chat->respuesta)) {
                    $mensajes[] = ["role" => "assistant", "content" => $chat->respuesta];
                }
                return $mensajes;
            })
            ->toArray();

        $historial[] = ["role" => "user", "content" => $nuevoMensaje];

        $response = Http::withToken($apiKey)->post($endpoint, [
            'model' => $modelo,
            'messages' => array_merge([$promptSystem], $historial),
            'tools' => $tools,
            'tool_choice' => "auto",
        ]);

        if ($response->failed()) {
            Log::error("❌ Error llamando a ChatGPT: " . $response->body());
            return null;
        }

        $data = $response->json();

        if (isset($data['choices'][0]['message']['tool_calls'])) {
            $toolCall = $data['choices'][0]['message']['tool_calls'][0];
            if ($toolCall['function']['name'] === 'obtener_claves') {
                $args = json_decode($toolCall['function']['arguments'], true);
                $codigoReserva = $args['codigo_reserva'] ?? null;

                $reserva = Reserva::where('codigo_reserva', $codigoReserva)->first();

                if (!$reserva) {
                    return "❌ No se encontró ninguna reserva con ese código.";
                }

                // Verificaciones
                $hoy = now();
                $fechaEntrada = Carbon::parse($reserva->fecha_entrada);
                $horaActual = now()->format('H:i');

                if (empty($reserva->dni_entregado)) {
                    $url = 'https://crm.apartamentosalgeciras.com/dni-user/' . $reserva->token;
                    return "🪪 Para poder darte la clave de acceso, necesitamos que completes el formulario con tus datos de identificación aquí: $url";
                }

                if ($fechaEntrada->isToday()) {
                    if ($horaActual < '13:00') {
                        return "🔒 Las claves estarán disponibles a partir de las 13:00 del día de entrada.";
                    }



                    $clave = $reserva->apartamento->claves ?? 'No asignada aún';
                    $clave2 = $reserva->apartamento->edificioRelacion->clave ?? 'No asignada aún';

                    $respuestaFinal = "🔐 Clave de acceso para tu apartamento reservado (#{$codigoReserva}): *{$clave}*\n\n🚪 Clave de la puerta del edificio: *{$clave2}*\n📅 Entrada: *{$reserva->fecha_entrada}* - Salida: *{$reserva->fecha_salida}*";

                    // Segunda llamada a OpenAI para integrar en la conversación
                    $responseFinal = Http::withToken($apiKey)->post($endpoint, [
                        'model' => $modelo,
                        'messages' => [
                            $promptSystem,
                            ...$historial,
                            ["role" => "assistant", "tool_calls" => [$toolCall]],
                            [
                                "role" => "tool",
                                "tool_call_id" => $toolCall['id'],
                                "content" => $respuestaFinal
                            ]
                        ]
                    ]);

                    return $responseFinal->json('choices.0.message.content');
                } else {
                    return "📅 Las claves solo se entregan el día de entrada. Tu reserva es para el *{$fechaEntrada->format('d/m/Y')}*.";
                }
            }
        }

        return $data['choices'][0]['message']['content'] ?? null;
    }

    public function clasificarMensaje($mensaje)
    {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        $url = 'https://api.openai.com/v1/chat/completions';

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ];

        $body = json_encode([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'Eres un asistente que clasifica mensajes en: "averia", "limpieza", "reserva_apartamento", o "otro".'],
                ['role' => 'user', 'content' => $mensaje]
            ],
            'max_tokens' => 10
        ]);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        $response = curl_exec($curl);
        curl_close($curl);

        $response_data = json_decode($response, true);

        if (isset($response_data['choices'][0]['message']['content'])) {
            return trim(strtolower($response_data['choices'][0]['message']['content']));
        }

        return 'otro';
    }

    public function gestionarAveria($phone, $mensaje)
    {
        // Aquí podrías registrar la avería en la base de datos
        return "Hemos registrado tu avería. Nuestro equipo te contactará pronto.";
    }

    public function gestionarLimpieza($phone, $mensaje)
    {
        // Aquí podrías programar una limpieza en el sistema
        return "Hemos programado el servicio de limpieza. Te avisaremos cuando esté confirmado.";
    }

    public function gestionarReserva($phone, $mensaje)
    {
        // Aquí podrías consultar la disponibilidad y responder al usuario
        return "Por favor, indícanos la fecha y el apartamento que deseas reservar.";
    }

    public function procesarMensajeGeneral($mensaje, $id, $phone, $idMensaje)
    {
        return "Procesamiento del mensaje general";
        // Aquí iría tu código original para procesar la conversación con el asistente
    }

    public function chatGpt($mensaje, $id, $phone = null, $idMensaje)
    {
        $categoria = $this->clasificarMensaje($mensaje);

        switch ($categoria) {
            case 'averia':
                return $this->gestionarAveria($phone, $mensaje);
            case 'limpieza':
                return $this->gestionarLimpieza($phone, $mensaje);
            case 'reserva_apartamento':
                return $this->gestionarReserva($phone, $mensaje);
            default:
                return $this->procesarMensajeGeneral($mensaje, $id, $phone, $idMensaje);
        }

    }

    public function contestarWhatsapp2($phone, $texto) {
        $token = env('TOKEN_WHATSAPP', 'valorPorDefecto');

        // Construir la carga útil como un array en lugar de un string JSON
        $mensajePersonalizado = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "text",
            "text" => [
                "body" => $texto
            ]
        ];

        $urlMensajes = 'https://graph.facebook.com/v16.0/102360642838173/messages';

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $urlMensajes,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($mensajePersonalizado),  // Asegúrate de que mensajePersonalizado sea un array
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
        ]);

        $response = curl_exec($curl);
        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            Log::error("Error en cURL al enviar mensaje de WhatsApp: " . $error);
            return ['error' => $error];
        }
        curl_close($curl);

        try {
            $responseJson = json_decode($response, true);
            Storage::disk('local')->put("Respuesta_Envio_Whatsapp-{$phone}.txt", $response);
            return $responseJson;
        } catch (\Exception $e) {
            Log::error("Error al guardar la respuesta de WhatsApp: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }


    public function contestarWhatsapp3($phone, $texto, $chatGptId = null)
    {
        $token = env('TOKEN_WHATSAPP', 'valorPorDefecto');

        $mensajePersonalizado = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "text",
            "text" => [
                "body" => $texto
            ]
        ];

        $urlMensajes = 'https://graph.facebook.com/v16.0/102360642838173/messages';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ])->post($urlMensajes, $mensajePersonalizado);

        if ($response->failed()) {
            Log::error("❌ Error en cURL al enviar mensaje de WhatsApp: " . $response->body());
            return ['error' => 'Error enviando mensaje'];
        }

        $responseJson = $response->json();
        Storage::disk('local')->put("Respuesta_Envio_Whatsapp-{$phone}.txt", json_encode($responseJson, JSON_PRETTY_PRINT));

        // ⏺️ Guardar ID del mensaje enviado
        if (isset($responseJson['messages'][0]['id'])) {
            $whatsappMessageId = $responseJson['messages'][0]['id'];

            WhatsappMensaje::create([
                'mensaje_id' => $whatsappMessageId,
                'tipo' => 'text',
                'contenido' => $texto,
                'remitente' => null, // este es un mensaje saliente, puedes usar un valor especial
                'fecha_mensaje' => now(),
                'metadata' => $mensajePersonalizado,
            ]);

            if ($chatGptId) {
                ChatGpt::where('id', $chatGptId)->update([
                    'respuesta_id' => $whatsappMessageId
                ]);
            }
        }

        return $responseJson;
    }

    public function contestarWhatsapp($phone, $texto, $mensajeOriginal = null)
    {
        $token = env('TOKEN_WHATSAPP', 'valorPorDefecto');

        $mensajePersonalizado = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "text",
            "text" => [
                "body" => $texto
            ]
        ];

        $urlMensajes = 'https://graph.facebook.com/v16.0/102360642838173/messages';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ])->post($urlMensajes, $mensajePersonalizado);

        if ($response->failed()) {
            Log::error("❌ Error al enviar mensaje: " . $response->body());
            return ['error' => 'Error enviando mensaje'];
        }

        $responseJson = $response->json();

        if (isset($responseJson['messages'][0]['id']) && $mensajeOriginal instanceof WhatsappMensaje) {
            $mensajeOriginal->recipient_id = $responseJson['messages'][0]['id'];
            $mensajeOriginal->save();

            Log::info("✅ Guardado recipient_id en mensaje original: " . $mensajeOriginal->id);
        }

        return $responseJson;
    }




    // Vista de los mensajes
   public function whatsapp()
{
    // Obtener el ID del último mensaje por remitente
    $ids = ChatGpt::selectRaw('MAX(id) as id')
        ->groupBy('remitente')
        ->pluck('id');

    // Cargar solo esos mensajes
    $mensajes = ChatGpt::whereIn('id', $ids)->orderBy('created_at', 'desc')->get();

    $resultado = [];
    foreach ($mensajes as $mensaje) {
        $mensaje['whatsapp_mensaje'] = $mensaje->whatsappMensaje;

        $cliente = Cliente::where('telefono', '+'.$mensaje->remitente)->first();
        $mensaje['nombre_remitente'] = $cliente
            ? ($cliente->nombre !== '' ? $cliente->nombre . ' ' . $cliente->apellido1 : $cliente->alias)
            : 'Desconocido';

        $resultado[$mensaje->remitente][] = $mensaje;
    }

    return view('whatsapp.index', compact('resultado'));
}


    // En el mismo controlador
    public function mensajes($remitente)
    {
        $limit = request()->get('limit', 20); // Cantidad a cargar
        $offset = request()->get('offset', 0); // Desde dónde empezar

        $mensajes = ChatGpt::where('remitente', $remitente)
            ->orderBy('created_at', 'asc')
            ->skip($offset)
            ->take($limit)
            ->get();

        foreach ($mensajes as $mensaje) {
            $mensaje['whatsapp_mensaje'] = $mensaje->whatsappMensaje;
        }

        return response()->json($mensajes);
    }

}
