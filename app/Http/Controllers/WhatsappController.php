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
use App\Models\EmailNotificaciones;
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
           /*  try {
                Log::info("🔍 Iniciando clasificación del mensaje: {$contenido}");
                $categoria = $this->clasificarMensaje($contenido);
                Log::info("📋 Mensaje clasificado como: {$categoria}");
                
                if ($categoria === 'averia') {
                    Log::info("🚨 Mensaje clasificado como AVERÍA - Iniciando gestión");
                    $this->gestionarAveria($waId, $contenido);
                } elseif ($categoria === 'limpieza') {
                    Log::info("🧹 Mensaje clasificado como LIMPIEZA - Iniciando gestión");
                    $this->gestionarLimpieza($waId, $contenido);
                } else {
                    Log::info("📝 Mensaje clasificado como: {$categoria} - No requiere notificación");
                }
            } catch (\Throwable $e) {
                Log::error('❌ Error en clasificación o notificación: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
            } */

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
            ],
            [
                "type" => "function",
                "function" => [
                    "name" => "notificar_tecnico",
                    "description" => "Notifica al técnico cuando hay una avería real que requiere intervención inmediata. Solo usar cuando el problema no se puede resolver con información general o cuando despues de intentar resolver el problema con la información general no se ha resuelto el problema.",
                    "parameters" => [
                        "type" => "object",
                        "properties" => [
                            "descripcion_problema" => [
                                "type" => "string",
                                "description" => "Descripción detallada del problema reportado por el cliente"
                            ],
                            "urgencia" => [
                                "type" => "string",
                                "enum" => ["baja", "media", "alta"],
                                "description" => "Nivel de urgencia del problema"
                            ]
                        ],
                        "required" => ["descripcion_problema", "urgencia"]
                    ]
                ]
            ],
            [
                "type" => "function",
                "function" => [
                    "name" => "notificar_limpieza",
                    "description" => "Notifica al equipo de limpieza cuando hay una solicitud de limpieza que requiere intervención. Solo usar cuando el cliente solicita limpieza específica o cuando despues de intentar resolver el problema con la información general no se ha resuelto el problema.",
                    "parameters" => [
                        "type" => "object",
                        "properties" => [
                            "tipo_limpieza" => [
                                "type" => "string",
                                "description" => "Tipo de limpieza solicitada (ej: limpieza general, cambio de ropa, etc.)"
                            ],
                            "observaciones" => [
                                "type" => "string",
                                "description" => "Observaciones adicionales del cliente"
                            ]
                        ],
                        "required" => ["tipo_limpieza"]
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
                                "content" => "Para poder darte la clave de acceso, necesitamos que completes el formulario con tus datos de identificación aquí: $url"
                            ]
                        ]
                    ]);

                    return $responseFinal->json('choices.0.message.content');
                    //return ;
                }
                

                if ($fechaEntrada->isToday()) {
                    if ($horaActual < '14:00') {
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
                                    "content" => "Las claves estarán disponibles a partir de las 14:00 del día de entrada."
                                ]
                            ]
                        ]);

                        return $responseFinal->json('choices.0.message.content');
                        //return "🔒 Las claves estarán disponibles a partir de las 13:00 del día de entrada.";
                    }



                    $clave = $reserva->apartamento->claves ?? 'No asignada aún';
                    $clave2 = $reserva->apartamento->edificioName->clave ?? 'No asignada aún';
                    $respuestaFinal = "🔐 Clave de acceso para tu apartamento reservado (#{$codigoReserva}): *{$clave}*\n\n🚪 Clave de la puerta del edificio: *{$clave2}*\n📅, Apartamento: *{$reserva->apartamento->nombre}*, Entrada: *{$reserva->fecha_entrada}* - Salida: *{$reserva->fecha_salida}*, hora actual: *{$horaActual}*";

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
                    $responseFinal = Http::withToken($apiKey)->post($endpoint, [
                        'model' => $modelo,
                        'messages' => [
                            $promptSystem,
                            ...$historial,
                            ["role" => "assistant", "tool_calls" => [$toolCall]],
                            [
                                "role" => "tool",
                                "tool_call_id" => $toolCall['id'],
                                "content" => "Las claves solo se entregan el día de entrada. Tu reserva es para el *{$fechaEntrada->format('d/m/Y')}*."
                            ]
                        ]
                    ]);

                    return $responseFinal->json('choices.0.message.content');
                    //return "📅 Las claves solo se entregan el día de entrada. Tu reserva es para el *{$fechaEntrada->format('d/m/Y')}*.";
                } 
            } elseif ($toolCall['function']['name'] === 'notificar_tecnico') {
                $args = json_decode($toolCall['function']['arguments'], true);
                $descripcion = $args['descripcion_problema'] ?? '';
                $urgencia = $args['urgencia'] ?? 'media';
                
                // Ejecutar la notificación al técnico
                $this->gestionarAveria($remitente, $descripcion);
                
                // Respuesta a ChatGPT confirmando la notificación
                $responseFinal = Http::withToken($apiKey)->post($endpoint, [
                    'model' => $modelo,
                    'messages' => [
                        $promptSystem,
                        ...$historial,
                        ["role" => "assistant", "tool_calls" => [$toolCall]],
                        [
                            "role" => "tool",
                            "tool_call_id" => $toolCall['id'],
                            "content" => "He notificado al técnico sobre el problema reportado. Te contactarán pronto para resolver la situación."
                        ]
                    ]
                ]);
                
                return $responseFinal->json('choices.0.message.content');
                
            } elseif ($toolCall['function']['name'] === 'notificar_limpieza') {
                $args = json_decode($toolCall['function']['arguments'], true);
                $tipoLimpieza = $args['tipo_limpieza'] ?? '';
                $observaciones = $args['observaciones'] ?? '';
                
                // Ejecutar la notificación a limpieza
                $this->gestionarLimpieza($remitente, $tipoLimpieza . ($observaciones ? " - " . $observaciones : ""));
                
                // Respuesta a ChatGPT confirmando la notificación
                $responseFinal = Http::withToken($apiKey)->post($endpoint, [
                    'model' => $modelo,
                    'messages' => [
                        $promptSystem,
                        ...$historial,
                        ["role" => "assistant", "tool_calls" => [$toolCall]],
                        [
                            "role" => "tool",
                            "tool_call_id" => $toolCall['id'],
                            "content" => "He notificado al equipo de limpieza sobre tu solicitud. Te avisaremos cuando esté confirmado."
                        ]
                    ]
                ]);
                
                return $responseFinal->json('choices.0.message.content');
            }
        }

        return $data['choices'][0]['message']['content'] ?? null;
    }

    public function clasificarMensaje($mensaje)
    {
        Log::info("🤖 CLASIFICAR MENSAJE - Iniciando para: {$mensaje}");
        
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        $url = 'https://api.openai.com/v1/chat/completions';

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ];

        $body = json_encode([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'Eres un asistente que clasifica mensajes. Responde ÚNICAMENTE con una de estas palabras: "averia", "limpieza", "reserva_apartamento", o "otro". No agregues explicaciones ni texto adicional.'],
                ['role' => 'user', 'content' => $mensaje]
            ],
            'max_tokens' => 5
        ]);

        Log::info("🌐 Enviando petición a OpenAI para clasificación...");
        
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
            $categoria = trim(strtolower($response_data['choices'][0]['message']['content']));
            Log::info("✅ Clasificación exitosa: {$categoria}");
            
            // Extraer solo la categoría relevante
            if (strpos($categoria, 'averia') !== false) {
                return 'averia';
            } elseif (strpos($categoria, 'limpieza') !== false) {
                return 'limpieza';
            } elseif (strpos($categoria, 'reserva') !== false) {
                return 'reserva_apartamento';
            } else {
                return 'otro';
            }
        }

        Log::warning("⚠️ Error en clasificación, retornando 'otro'");
        return 'otro';
    }

    public function gestionarAveria($phone, $mensaje)
    {
        Log::info("🚨 GESTIONAR AVERÍA - Iniciando para teléfono: {$phone}");
        
        // Registrar la avería en la base de datos
        Log::info("📝 Registrando avería en logs...");
        $this->registrarAveria($phone, $mensaje);
        
        // Enviar mensaje al técnico
        Log::info("👨‍🔧 Enviando mensaje al técnico...");
        $this->enviarMensajeTecnico($phone, $mensaje);
        
        Log::info("✅ GESTIONAR AVERÍA - Completado");
        return "Hemos registrado tu avería. Nuestro equipo técnico ha sido notificado y te contactará pronto.";
    }

    public function gestionarLimpieza($phone, $mensaje)
    {
        Log::info("🧹 GESTIONAR LIMPIEZA - Iniciando para teléfono: {$phone}");
        
        // Registrar la solicitud de limpieza en la base de datos
        Log::info("📝 Registrando solicitud de limpieza en logs...");
        $this->registrarLimpieza($phone, $mensaje);
        
        // Enviar mensaje a la limpiadora
        Log::info("👩‍🔧 Enviando mensaje a la limpiadora...");
        $this->enviarMensajeLimpiadora($phone, $mensaje);
        
        Log::info("✅ GESTIONAR LIMPIEZA - Completado");
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
        Log::info("👨‍🔧 ENVIAR MENSAJE TÉCNICO - Iniciando para cliente: {$phone}");
        
        try {
            // Obtener todos los técnicos
            Log::info("🔍 Buscando todos los técnicos...");
            $tecnicos = $this->obtenerTecnicoDisponible();
            
            if ($tecnicos->isEmpty()) {
                Log::warning("⚠️ No hay técnicos disponibles para notificar");
                return;
            }
            
            Log::info("✅ Técnicos encontrados: " . $tecnicos->count() . " técnicos");

            // Buscar template para averías
            Log::info("🔍 Buscando template para averías...");
            $template = \App\Models\WhatsappTemplate::where('name', 'reparaciones')
                ->where('name', 'not like', '%_null%')
                ->first();

            // Obtener información del cliente (una sola vez)
            $apartamento = $this->obtenerApartamentoCliente($phone);
            $edificio = $this->obtenerEdificioCliente($phone);

            // Enviar mensaje a cada técnico
            foreach ($tecnicos as $tecnico) {
                Log::info("📱 Enviando mensaje al técnico: {$tecnico->nombre} - {$tecnico->telefono}");
                
                if ($template) {
                    Log::info("✅ Template encontrado: {$template->name} (ID: {$template->id})");
                    
                    // Enviar mensaje usando template con los 5 parámetros que espera
                    $this->enviarMensajeTemplate($tecnico->telefono, $template->name, [
                        '1' => $tecnico->nombre ?? 'Técnico', // Nombre del técnico
                        '2' => $apartamento, // Apartamento del cliente
                        '3' => $edificio, // Edificio del cliente
                        '4' => $mensaje, // Información del cliente
                        '5' => $phone // Número del cliente
                    ]);
                } else {
                    Log::warning("⚠️ No se encontró template para averías, enviando mensaje simple");
                    
                    // Enviar mensaje simple si no hay template
                    $texto = "🚨 NUEVA AVERÍA REPORTADA\n\n👨‍🔧 Técnico: {$tecnico->nombre}\n📱 Cliente: {$phone}\n🏠 Apartamento: {$apartamento}\n🏢 Edificio: {$edificio}\n💬 Mensaje: {$mensaje}\n📅 Fecha: " . now()->format('d/m/Y H:i');
                    $this->contestarWhatsapp3($tecnico->telefono, $texto);
                }

                Log::info("✅ Mensaje enviado al técnico: {$tecnico->telefono}");
            }
            
            // Enviar notificación a todos los responsables configurados (solo una vez)
            $primerTecnico = $tecnicos->first();
            $this->enviarNotificacionResponsables($phone, $mensaje, 'averia', $primerTecnico->nombre, $apartamento, $edificio);
            
        } catch (\Exception $e) {
            Log::error("Error enviando mensaje a los técnicos: " . $e->getMessage());
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
            Log::info("🔍 Buscando template para limpieza...");
            $template = \App\Models\WhatsappTemplate::where('name', 'limpieza')
                ->where('name', 'not like', '%_null%')
                ->first();

            if ($template) {
                Log::info("✅ Template encontrado: {$template->name} (ID: {$template->id})");
                Log::info("📱 Enviando mensaje usando template...");
                
                // Obtener información del cliente
                $apartamento = $this->obtenerApartamentoCliente($phone);
                $edificio = $this->obtenerEdificioCliente($phone);
                
                // Enviar mensaje usando template con los 4 parámetros que espera
                $this->enviarMensajeTemplate($limpiadora->telefono, $template->name, [
                    '1' => $apartamento, // Apartamento del cliente
                    '2' => $edificio, // Edificio del cliente
                    '3' => $mensaje, // Información del cliente
                    '4' => $phone // Número del cliente
                ]);
            } else {
                Log::warning("⚠️ No se encontró template para limpieza, enviando mensaje simple");
                // Enviar mensaje simple si no hay template
                $apartamento = $this->obtenerApartamentoCliente($phone);
                $edificio = $this->obtenerEdificioCliente($phone);
                
                $texto = "🧹 NUEVA SOLICITUD DE LIMPIEZA\n\n👩‍🔧 Limpiadora: " . ($limpiadora->usuario->name ?? 'Limpiadora') . "\n📱 Cliente: {$phone}\n🏠 Apartamento: {$apartamento}\n🏢 Edificio: {$edificio}\n💬 Mensaje: {$mensaje}\n📅 Fecha: " . now()->format('d/m/Y H:i');
                $this->contestarWhatsapp3($limpiadora->telefono, $texto);
            }

            Log::info("Mensaje enviado a la limpiadora: {$limpiadora->telefono}");
            
            // Enviar notificación a todos los responsables configurados
            $this->enviarNotificacionResponsables($phone, $mensaje, 'limpieza', $limpiadora->usuario->name ?? 'Limpiadora', $apartamento, $edificio);
            
        } catch (\Exception $e) {
            Log::error("Error enviando mensaje a la limpiadora: " . $e->getMessage());
        }
    }

    /**
     * Enviar mensaje usando template de WhatsApp
     */
    private function enviarMensajeTemplate($phone, $templateName, $parameters = [])
    {
        Log::info("📱 ENVIAR MENSAJE TEMPLATE - Iniciando para: {$phone}");
        Log::info("🔧 Template: {$templateName}");
        Log::info("📋 Parámetros: " . json_encode($parameters));
        
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
                    "parameters" => array_values(array_map(function($value) {
                        return [
                            "type" => "text",
                            "text" => $value
                        ];
                    }, $parameters))
                ]
            ];
        }

        $urlMensajes = 'https://graph.facebook.com/v16.0/102360642838173/messages';

        Log::info("🌐 Enviando petición a WhatsApp API...");
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ])->post($urlMensajes, $mensajeTemplate);

        if ($response->failed()) {
            Log::error("❌ Error enviando template de WhatsApp: " . $response->body());
            return ['error' => 'Error enviando template'];
        }

        $responseJson = $response->json();
        Log::info("✅ Respuesta exitosa de WhatsApp API: " . json_encode($responseJson));
        Storage::disk('local')->put("Respuesta_Template_Whatsapp-{$phone}.txt", json_encode($responseJson, JSON_PRETTY_PRINT));

        return $responseJson;
    }

    /**
     * Obtener técnico disponible según horario actual
     */
    private function obtenerTecnicoDisponible()
    {
        // NUEVO: Enviar a todos los técnicos
        $todosTecnicos = Reparaciones::all();
        return $todosTecnicos;
        
        // CÓDIGO ORIGINAL COMENTADO - Selección por horario
        /*
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
        */
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
     * Enviar notificación a todos los responsables configurados
     */
    private function enviarNotificacionResponsables($phone, $mensaje, $tipo, $personalAsignado, $apartamento, $edificio)
    {
        Log::info("📢 ENVIAR NOTIFICACIÓN RESPONSABLES - Iniciando para tipo: {$tipo}");
        
        try {
            // Obtener todos los responsables configurados
            $responsables = EmailNotificaciones::all();
            
            if ($responsables->isEmpty()) {
                Log::info("ℹ️ No hay responsables configurados para notificar");
                return;
            }
            
            Log::info("📋 Encontrados {$responsables->count()} responsables para notificar");
            
            foreach ($responsables as $responsable) {
                try {
                    if (!empty($responsable->telefono)) {
                        // Enviar mensaje de WhatsApp al responsable
                        $texto = $this->generarMensajeResponsable($phone, $mensaje, $tipo, $personalAsignado, $apartamento, $edificio);
                        
                        Log::info("📱 Enviando notificación a responsable: {$responsable->nombre} - {$responsable->telefono}");
                        $this->contestarWhatsapp3($responsable->telefono, $texto);
                        
                        Log::info("✅ Notificación enviada exitosamente a: {$responsable->nombre}");
                    } else {
                        Log::warning("⚠️ Responsable {$responsable->nombre} no tiene teléfono configurado");
                    }
                } catch (\Exception $e) {
                    Log::error("❌ Error enviando notificación a {$responsable->nombre}: " . $e->getMessage());
                }
            }
            
            Log::info("✅ ENVIAR NOTIFICACIÓN RESPONSABLES - Completado");
            
        } catch (\Exception $e) {
            Log::error("❌ Error general enviando notificaciones a responsables: " . $e->getMessage());
        }
    }

    /**
     * Generar mensaje para responsables
     */
    private function generarMensajeResponsable($phone, $mensaje, $tipo, $personalAsignado, $apartamento, $edificio)
    {
        $emoji = ($tipo === 'averia') ? '🚨' : '🧹';
        $tipoTexto = ($tipo === 'averia') ? 'AVERÍA' : 'LIMPIEZA';
        
        return "{$emoji} NOTIFICACIÓN DE {$tipoTexto}\n\n" .
               "📱 Cliente: {$phone}\n" .
               "🏠 Apartamento: {$apartamento}\n" .
               "🏢 Edificio: {$edificio}\n" .
               "💬 Mensaje: {$mensaje}\n" .
               "👨‍🔧 Personal Asignado: {$personalAsignado}\n" .
               "📅 Fecha: " . now()->format('d/m/Y H:i') . "\n\n" .
               "ℹ️ Esta notificación se ha enviado automáticamente al personal correspondiente.";
    }

    /**
     * Obtener el apartamento del cliente según su teléfono
     */
    private function obtenerApartamentoCliente($phone)
    {
        Log::info("🏠 OBTENER APARTAMENTO CLIENTE - Buscando para teléfono: {$phone}");
        
        try {
            // Buscar cliente por teléfono
            $cliente = Cliente::where('telefono', $phone)->first();
            
            if ($cliente) {
                Log::info("✅ Cliente encontrado: {$cliente->nombre} {$cliente->apellido1}");
                
                // Buscar reserva activa del cliente
                $reserva = Reserva::where('cliente_id', $cliente->id)
                    ->where('estado_id', '!=', 4) // No cancelada
                    ->where('fecha_entrada', '<=', now())
                    ->where('fecha_salida', '>=', now())
                    ->first();
                
                if ($reserva && $reserva->apartamento) {
                    Log::info("✅ Apartamento encontrado: {$reserva->apartamento->nombre}");
                    return $reserva->apartamento->nombre;
                } else {
                    Log::warning("⚠️ No se encontró reserva activa para el cliente");
                }
            } else {
                Log::warning("⚠️ Cliente no encontrado con teléfono: {$phone}");
            }
            
            Log::info("🏠 Retornando: Apartamento no identificado");
            return 'Apartamento no identificado';
        } catch (\Exception $e) {
            Log::error("❌ Error obteniendo apartamento del cliente: " . $e->getMessage());
            return 'Apartamento no identificado';
        }
    }

    /**
     * Obtener el edificio del cliente según su teléfono
     */
    private function obtenerEdificioCliente($phone)
    {
        Log::info("🏢 OBTENER EDIFICIO CLIENTE - Buscando para teléfono: {$phone}");
        
        try {
            // Buscar cliente por teléfono
            $cliente = Cliente::where('telefono', $phone)->first();
            
            if ($cliente) {
                Log::info("✅ Cliente encontrado: {$cliente->nombre} {$cliente->apellido1}");
                
                // Buscar reserva activa del cliente
                $reserva = Reserva::where('cliente_id', $cliente->id)
                    ->where('estado_id', '!=', 4) // No cancelada
                    ->where('fecha_entrada', '<=', now())
                    ->where('fecha_salida', '>=', now())
                    ->first();
                
                if ($reserva && $reserva->apartamento && $reserva->apartamento->edificioName) {
                    Log::info("✅ Edificio encontrado: {$reserva->apartamento->edificioName->nombre}");
                    return $reserva->apartamento->edificioName->nombre;
                } else {
                    Log::warning("⚠️ No se encontró edificio para la reserva");
                }
            } else {
                Log::warning("⚠️ Cliente no encontrado con teléfono: {$phone}");
            }
            
            Log::info("🏢 Retornando: Edificio no identificado");
            return 'Edificio no identificado';
        } catch (\Exception $e) {
            Log::error("❌ Error obteniendo edificio del cliente: " . $e->getMessage());
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
