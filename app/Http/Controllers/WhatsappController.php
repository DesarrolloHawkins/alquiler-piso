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
use App\Models\LimpiadoraGuardia;
use App\Models\WhatsappTemplate;
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

            // 2. Clasificar el mensaje y notificar si procede
            try {
                $categoria = $this->clasificarMensaje($contenido);
                if ($categoria === 'averia') {
                    $this->gestionarAveria($waId, $contenido);
                } elseif ($categoria === 'limpieza') {
                    $this->gestionarLimpieza($waId, $contenido);
                }
            } catch (\Throwable $e) {
                Log::warning('Fallo clasificando o notificando: ' . $e->getMessage());
            }

            // 3. Intentar obtener respuesta de ChatGPT
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
        // Registrar la avería en la base de datos
        $this->registrarAveria($phone, $mensaje);
        
        // Enviar mensaje al técnico
        $this->enviarMensajeTecnico($phone, $mensaje);
        
        return "Hemos registrado tu avería. Nuestro equipo técnico ha sido notificado y te contactará pronto.";
    }

    public function gestionarLimpieza($phone, $mensaje)
    {
        // Registrar la solicitud de limpieza en la base de datos
        $this->registrarLimpieza($phone, $mensaje);
        
        // Enviar mensaje a la limpiadora
        $this->enviarMensajeLimpiadora($phone, $mensaje);
        
        return "Hemos programado el servicio de limpieza. Nuestro equipo de limpieza ha sido notificado y te avisaremos cuando esté confirmado.";
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

    /**
     * Registrar una avería en la base de datos
     */
    private function registrarAveria($phone, $mensaje)
    {
        // Aquí puedes implementar el registro en la base de datos
        // Por ejemplo, crear un registro en una tabla de averías
        Log::info("Avería registrada - Teléfono: {$phone}, Mensaje: {$mensaje}");
    }

    /**
     * Registrar una solicitud de limpieza en la base de datos
     */
    private function registrarLimpieza($phone, $mensaje)
    {
        // Aquí puedes implementar el registro en la base de datos
        // Por ejemplo, crear un registro en una tabla de solicitudes de limpieza
        Log::info("Limpieza registrada - Teléfono: {$phone}, Mensaje: {$mensaje}");
    }

    /**
     * Enviar mensaje al técnico usando template de WhatsApp
     */
    private function enviarMensajeTecnico($phone, $mensaje)
    {
        try {
            // Obtener técnico disponible según horario actual
            $tecnico = $this->obtenerTecnicoDisponible();
            
            if (!$tecnico) {
                Log::warning("No hay técnicos disponibles para notificar");
                return;
            }

            // Buscar template para averías
            $template = \App\Models\WhatsappTemplate::where('name', 'like', '%reparaciones%')
                ->first();

            if ($template) {
                // Enviar mensaje usando template con parámetros en el orden correcto
                $this->enviarMensajeTemplate($tecnico->telefono, $template->name, [
                    '1' => $tecnico->nombre ?? 'Técnico', // Nombre del técnico
                    '2' => $this->obtenerApartamentoCliente($phone) ?? 'Apartamento', // Apartamento del cliente
                    '3' => $this->obtenerEdificioCliente($phone) ?? 'Edificio', // Edificio del cliente
                    '4' => $mensaje, // Información del cliente
                    '5' => $phone // Número del cliente
                ]);
            } else {
                // Enviar mensaje simple si no hay template
                $texto = "🚨 NUEVA AVERÍA REPORTADA\n\n📱 Cliente: {$phone}\n💬 Mensaje: {$mensaje}\n📅 Fecha: " . now()->format('d/m/Y H:i');
                $this->contestarWhatsapp3($tecnico->telefono, $texto);
            }

            Log::info("Mensaje enviado al técnico: {$tecnico->telefono}");
        } catch (\Exception $e) {
            Log::error("Error enviando mensaje al técnico: " . $e->getMessage());
        }
    }

    /**
     * Enviar mensaje a la limpiadora usando template de WhatsApp
     */
    private function enviarMensajeLimpiadora($phone, $mensaje)
    {
        try {
            // Obtener limpiadora disponible según horario actual
            $limpiadora = $this->obtenerLimpiadoraDisponible();
            
            if (!$limpiadora) {
                Log::warning("No hay limpiadoras disponibles para notificar");
                return;
            }

            // Buscar template para limpieza
            $template = \App\Models\WhatsappTemplate::where('name', 'like', '%limpieza%')
                ->orWhere('name', 'like', '%limpiadora%')
                ->orWhere('name', 'like', '%cleaning%')
                ->first();

            if ($template) {
                // Enviar mensaje usando template
                $this->enviarMensajeTemplate($limpiadora->telefono, $template->name, [
                    'cliente_telefono' => $phone,
                    'mensaje' => $mensaje,
                    'fecha' => now()->format('d/m/Y H:i')
                ]);
            } else {
                // Enviar mensaje simple si no hay template
                $texto = "🧹 NUEVA SOLICITUD DE LIMPIEZA\n\n📱 Cliente: {$phone}\n💬 Mensaje: {$mensaje}\n📅 Fecha: " . now()->format('d/m/Y H:i');
                $this->contestarWhatsapp3($limpiadora->telefono, $texto);
            }

            Log::info("Mensaje enviado a la limpiadora: {$limpiadora->telefono}");
        } catch (\Exception $e) {
            Log::error("Error enviando mensaje a la limpiadora: " . $e->getMessage());
        }
    }

    /**
     * Enviar mensaje usando template de WhatsApp
     */
    private function enviarMensajeTemplate($phone, $templateName, $parameters = [])
    {
        $token = env('TOKEN_WHATSAPP', 'valorPorDefecto');
        
        $mensajeTemplate = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "template",
            "template" => [
                "name" => $templateName,
                "language" => [
                    "code" => "es"
                ]
            ]
        ];

        // Agregar parámetros si existen
        if (!empty($parameters)) {
            $mensajeTemplate["template"]["components"] = [
                [
                    "type" => "body",
                    "parameters" => array_map(function($key, $value) {
                        return [
                            "type" => "text",
                            "text" => $value
                        ];
                    }, array_keys($parameters), $parameters)
                ]
            ];
        }

        $urlMensajes = 'https://graph.facebook.com/v16.0/102360642838173/messages';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ])->post($urlMensajes, $mensajeTemplate);

        if ($response->failed()) {
            Log::error("❌ Error enviando template de WhatsApp: " . $response->body());
            return ['error' => 'Error enviando template'];
        }

        $responseJson = $response->json();
        Storage::disk('local')->put("Respuesta_Template_Whatsapp-{$phone}.txt", json_encode($responseJson, JSON_PRETTY_PRINT));

        return $responseJson;
    }

    /**
     * Obtener técnico disponible según horario actual
     */
    private function obtenerTecnicoDisponible()
    {
        $horaActual = now()->format('H:i');
        $diaSemana = now()->dayOfWeek; // 0 = domingo, 1 = lunes, etc.
        
        // Mapear día de la semana a columnas de la base de datos
        $diasColumnas = [
            1 => 'lunes',
            2 => 'martes', 
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
            0 => 'domingo'
        ];
        
        $columnaDia = $diasColumnas[$diaSemana] ?? 'lunes';
        
        // Buscar técnico disponible en el día y horario actual
        $tecnico = Reparaciones::where($columnaDia, true)
            ->where('hora_inicio', '<=', $horaActual)
            ->where('hora_fin', '>=', $horaActual)
            ->first();
            
        // Si no hay técnico en horario, buscar cualquier técnico
        if (!$tecnico) {
            $tecnico = Reparaciones::first();
        }
        
        return $tecnico;
    }

    /**
     * Obtener limpiadora disponible según horario actual
     */
    private function obtenerLimpiadoraDisponible()
    {
        $horaActual = now()->format('H:i');
        $diaSemana = now()->dayOfWeek; // 0 = domingo, 1 = lunes, etc.
        
        // Mapear día de la semana a columnas de la base de datos
        $diasColumnas = [
            1 => 'lunes',
            2 => 'martes', 
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
            0 => 'domingo'
        ];
        
        $columnaDia = $diasColumnas[$diaSemana] ?? 'lunes';
        
        // Buscar limpiadora disponible en el día y horario actual
        $limpiadora = LimpiadoraGuardia::where($columnaDia, true)
            ->where('hora_inicio', '<=', $horaActual)
            ->where('hora_fin', '>=', $horaActual)
            ->first();
            
        // Si no hay limpiadora en horario, buscar cualquier limpiadora
        if (!$limpiadora) {
            $limpiadora = LimpiadoraGuardia::first();
        }
        
        return $limpiadora;
    }

    /**
     * Obtener el apartamento del cliente según su teléfono
     */
    private function obtenerApartamentoCliente($phone)
    {
        try {
            // Buscar cliente por teléfono
            $cliente = Cliente::where('telefono', $phone)->first();
            
            if ($cliente) {
                // Buscar reserva activa del cliente
                $reserva = Reserva::where('cliente_id', $cliente->id)
                    ->where('estado_id', '!=', 4) // No cancelada
                    ->where('fecha_entrada', '<=', now())
                    ->where('fecha_salida', '>=', now())
                    ->first();
                
                if ($reserva && $reserva->apartamento) {
                    return $reserva->apartamento->nombre;
                }
            }
            
            return 'Apartamento no identificado';
        } catch (\Exception $e) {
            Log::error("Error obteniendo apartamento del cliente: " . $e->getMessage());
            return 'Apartamento no identificado';
        }
    }

    /**
     * Obtener el edificio del cliente según su teléfono
     */
    private function obtenerEdificioCliente($phone)
    {
        try {
            // Buscar cliente por teléfono
            $cliente = Cliente::where('telefono', $phone)->first();
            
            if ($cliente) {
                // Buscar reserva activa del cliente
                $reserva = Reserva::where('cliente_id', $cliente->id)
                    ->where('estado_id', '!=', 4) // No cancelada
                    ->where('fecha_entrada', '<=', now())
                    ->where('fecha_salida', '>=', now())
                    ->first();
                
                if ($reserva && $reserva->apartamento && $reserva->apartamento->edificioRelacion) {
                    return $reserva->apartamento->edificioRelacion->nombre;
                }
            }
            
            return 'Edificio no identificado';
        } catch (\Exception $e) {
            Log::error("Error obteniendo edificio del cliente: " . $e->getMessage());
            return 'Edificio no identificado';
        }
    }

    public function chatGpt($mensaje, $id, $phone = null, $idMensaje = null)
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
        // Obtener los IDs del último mensaje por cada remitente (excepto "guest")
        $ids = ChatGpt::where('remitente', '!=', 'guest')
            ->selectRaw('MAX(id) as id')
            ->groupBy('remitente')
            ->pluck('id');

        // Cargar solo esos mensajes
        $mensajes = ChatGpt::whereIn('id', $ids)
            ->orderBy('created_at', 'desc')
            ->get();

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
