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
use App\Models\Incidencia;
use App\Models\User;
use App\Services\AlertService;
use App\Services\NotificationService;

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

        // Verificar si el mensaje ya existe para evitar duplicados
        $whatsappMensaje = WhatsappMensaje::firstOrCreate(
            ['mensaje_id' => $id],
            [
                'tipo' => $tipo,
                'contenido' => $contenido,
                'remitente' => $waId,
                'fecha_mensaje' => $timestamp ? Carbon::createFromTimestamp($timestamp) : now(),
                'metadata' => $mensaje
            ]
        );

        // Si el mensaje ya existía, no procesar de nuevo
        if ($whatsappMensaje->wasRecentlyCreated === false) {
            Log::info("🔄 Mensaje duplicado detectado - Ya procesado anteriormente", [
                'mensaje_id' => $id,
                'remitente' => $waId
            ]);
            return response()->json(['status' => 'duplicate', 'message' => 'Mensaje ya procesado']);
        }

        // Solo si es texto, responde con ChatGPT
        if ($tipo === 'text') {
            // VALIDACIÓN: Verificar si es un mensaje repetido de un contestador automático
            // Buscar mensajes idénticos del mismo remitente en los últimos 10 minutos
            $mensajeRepetido = $this->verificarMensajeRepetido($waId, $contenido);
            
            if ($mensajeRepetido) {
                Log::info("🔄 Mensaje repetido detectado - No se responderá para evitar bucle con contestador automático", [
                    'remitente' => $waId,
                    'mensaje' => substr($contenido, 0, 100),
                    'mensaje_anterior_id' => $mensajeRepetido->id,
                    'fecha_mensaje_anterior' => $mensajeRepetido->date
                ]);
                
                // Crear registro pero sin responder
                $chat = ChatGpt::create([
                    'id_mensaje' => $id,
                    'whatsapp_mensaje_id' => $whatsappMensaje->id,
                    'remitente' => $waId,
                    'mensaje' => $contenido,
                    'respuesta' => null,
                    'status' => 2, // 2 = mensaje repetido, no responder
                    'type' => 'text',
                    'date' => now(),
                ]);
                
                return response()->json([
                    'status' => 'ignored',
                    'reason' => 'Mensaje repetido detectado - No se responde para evitar bucle'
                ]);
            }
            
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
                return response()->json(['status' => 'ok', 'respuesta' => $respuestaTexto]);
            } else {
                Log::warning("❌ Error de IA local. No se contestó a {$waId}.");
                return response()->json(['status' => 'failed', 'message' => 'No se obtuvo respuesta de la IA']);

                // Se mantiene la fila con status = 0 y respuesta = null
            }
        }

    }

    function enviarMensajeOpenAiChatCompletions($nuevoMensaje, $remitente)
    {
        // Configuración de la IA local Hawkins
        $config = config('services.hawkins_ai');
        $endpoint = $config['base_url'];
        
        // Asegurar que la URL termine en /chat/chat
        if (!str_ends_with($endpoint, '/chat/chat')) {
            // Si termina en /chat, agregar /chat
            if (str_ends_with($endpoint, '/chat')) {
                $endpoint = rtrim($endpoint, '/chat') . '/chat/chat';
            } else {
                // Si termina en /, agregar chat/chat
                $endpoint = rtrim($endpoint, '/') . '/chat/chat';
            }
        }
        
        $apiKey = $config['api_key'];
        $modelo = $config['model'];
        
        $promptAsistente = PromptAsistente::first();
        $promptBase = $promptAsistente ? $promptAsistente->prompt : "Eres un asistente de apartamentos turísticos.";

        // Construir instrucciones sobre funciones disponibles
        $instruccionesFunciones = "\n\nFUNCIONES DISPONIBLES:\n" .
            "Cuando necesites ejecutar una función, responde SOLO con el formato exacto:\n" .
            "- Para obtener claves: [FUNCION:obtener_claves:codigo_reserva=CODIGO]\n" .
            "- Para notificar técnico: [FUNCION:notificar_tecnico:descripcion=DESCRIPCION:urgencia=alta|media|baja]\n" .
            "- Para notificar limpieza: [FUNCION:notificar_limpieza:tipo_limpieza=TIPO:observaciones=OBS]\n\n" .
            "Si NO necesitas ejecutar ninguna función, responde normalmente al usuario.";

        $promptSystem = $promptBase . $instruccionesFunciones;

        // Obtener historial de conversación
        $historial = ChatGpt::where('remitente', $remitente)
            ->orderBy('date', 'desc')
            ->limit(20)
            ->get()
            ->reverse()
            ->map(function ($chat) {
                $texto = "";
                if (!empty($chat->mensaje)) {
                    $texto .= "Usuario: " . $chat->mensaje . "\n";
                }
                if (!empty($chat->respuesta)) {
                    $texto .= "Asistente: " . $chat->respuesta . "\n";
                }
                return $texto;
            })
            ->implode("\n");

        // Construir prompt completo con historial y nuevo mensaje
        $promptCompleto = $promptSystem . "\n\n--- HISTORIAL DE CONVERSACIÓN ---\n" . 
            ($historial ? $historial . "\n" : "") .
            "--- MENSAJE ACTUAL ---\n" .
            "Usuario: " . $nuevoMensaje . "\n" .
            "Asistente:";

        Log::info("🤖 Enviando mensaje a IA local Hawkins", [
            'endpoint' => $endpoint,
            'modelo' => $modelo,
            'remitente' => $remitente
        ]);

        // Llamar a la API local
        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'Content-Type' => 'application/json'
        ])->post($endpoint, [
            'prompt' => $promptCompleto,
            'modelo' => $modelo
        ]);

        if ($response->failed()) {
            Log::error("❌ Error llamando a IA local Hawkins: " . $response->body());
            return null;
        }

        $data = $response->json();

        if (!isset($data['success']) || !$data['success']) {
            Log::error("❌ Error en respuesta de IA local: " . json_encode($data));
            return null;
        }

        $respuestaTexto = $data['respuesta'] ?? null;

        if (!$respuestaTexto) {
            Log::warning("⚠️ Respuesta vacía de IA local");
            return null;
        }

        // Detectar si la respuesta contiene una llamada a función
        if (preg_match('/\[FUNCION:([^:]+):(.+)\]/', $respuestaTexto, $matches)) {
            $nombreFuncion = trim($matches[1]);
            $parametrosStr = $matches[2];
            
            // Parsear parámetros
            $parametros = [];
            foreach (explode(':', $parametrosStr) as $param) {
                if (strpos($param, '=') !== false) {
                    list($key, $value) = explode('=', $param, 2);
                    $parametros[trim($key)] = trim($value);
                }
            }

            Log::info("🔧 Función detectada: {$nombreFuncion}", ['parametros' => $parametros]);

            // Ejecutar función correspondiente
            if ($nombreFuncion === 'obtener_claves') {
                $codigoReserva = $parametros['codigo_reserva'] ?? null;
                $resultadoFuncion = $this->ejecutarObtenerClaves($codigoReserva, $remitente, $promptSystem, $historial, $nuevoMensaje, $endpoint, $apiKey, $modelo);
                return $resultadoFuncion;
                
            } elseif ($nombreFuncion === 'notificar_tecnico') {
                $descripcion = $parametros['descripcion_problema'] ?? ($parametros['descripcion'] ?? '');
                $urgencia = $parametros['urgencia'] ?? 'media';
                $resultadoFuncion = $this->ejecutarNotificarTecnico($remitente, $descripcion, $urgencia, $promptSystem, $historial, $nuevoMensaje, $endpoint, $apiKey, $modelo);
                return $resultadoFuncion;
                
            } elseif ($nombreFuncion === 'notificar_limpieza') {
                $tipoLimpieza = $parametros['tipo_limpieza'] ?? '';
                $observaciones = $parametros['observaciones'] ?? '';
                $resultadoFuncion = $this->ejecutarNotificarLimpieza($remitente, $tipoLimpieza, $observaciones, $promptSystem, $historial, $nuevoMensaje, $endpoint, $apiKey, $modelo);
                return $resultadoFuncion;
            }
        }

        return $respuestaTexto;
    }

    /**
     * Ejecutar función obtener_claves
     */
    private function ejecutarObtenerClaves($codigoReserva, $remitente, $promptSystem, $historial, $nuevoMensaje, $endpoint, $apiKey, $modelo)
    {
        $reserva = Reserva::where('codigo_reserva', $codigoReserva)->first();

        if (!$reserva) {
            $mensajeError = "No se encontró ninguna reserva con el código: {$codigoReserva}";
            return $this->llamarIALocalConContexto($promptSystem, $historial, $nuevoMensaje, $mensajeError, $endpoint, $apiKey, $modelo);
        }

        $fechaEntrada = Carbon::parse($reserva->fecha_entrada);
        $horaActual = now()->format('H:i');

        if (empty($reserva->dni_entregado)) {
            $url = 'https://crm.apartamentosalgeciras.com/dni-user/' . $reserva->token;
            $mensajeFuncion = "Para poder darte la clave de acceso, necesitamos que completes el formulario con tus datos de identificación aquí: {$url}";
            return $this->llamarIALocalConContexto($promptSystem, $historial, $nuevoMensaje, $mensajeFuncion, $endpoint, $apiKey, $modelo);
        }

        if ($fechaEntrada->isToday()) {
            if ($horaActual < '14:00') {
                $mensajeFuncion = "Las claves estarán disponibles a partir de las 14:00 del día de entrada.";
                return $this->llamarIALocalConContexto($promptSystem, $historial, $nuevoMensaje, $mensajeFuncion, $endpoint, $apiKey, $modelo);
            }

            $clave = $reserva->apartamento->claves ?? 'No asignada aún';
            $clave2 = $reserva->apartamento->edificioName->clave ?? 'No asignada aún';
            $mensajeFuncion = "Clave de acceso para tu apartamento reservado (#{$codigoReserva}): *{$clave}*\n\nClave de la puerta del edificio: *{$clave2}*\nApartamento: *{$reserva->apartamento->nombre}*, Entrada: *{$reserva->fecha_entrada}* - Salida: *{$reserva->fecha_salida}*, hora actual: *{$horaActual}*";
            return $this->llamarIALocalConContexto($promptSystem, $historial, $nuevoMensaje, $mensajeFuncion, $endpoint, $apiKey, $modelo);
        } else {
            $mensajeFuncion = "Las claves solo se entregan el día de entrada. Tu reserva es para el *{$fechaEntrada->format('d/m/Y')}*.";
            return $this->llamarIALocalConContexto($promptSystem, $historial, $nuevoMensaje, $mensajeFuncion, $endpoint, $apiKey, $modelo);
        }
    }

    /**
     * Ejecutar función notificar_tecnico
     */
    private function ejecutarNotificarTecnico($remitente, $descripcion, $urgencia, $promptSystem, $historial, $nuevoMensaje, $endpoint, $apiKey, $modelo)
    {
        $this->gestionarAveria($remitente, $descripcion);
        $mensajeFuncion = "He notificado al técnico sobre el problema reportado. Te contactarán pronto para resolver la situación.";
        return $this->llamarIALocalConContexto($promptSystem, $historial, $nuevoMensaje, $mensajeFuncion, $endpoint, $apiKey, $modelo);
    }

    /**
     * Ejecutar función notificar_limpieza
     */
    private function ejecutarNotificarLimpieza($remitente, $tipoLimpieza, $observaciones, $promptSystem, $historial, $nuevoMensaje, $endpoint, $apiKey, $modelo)
    {
        $mensajeCompleto = $tipoLimpieza . ($observaciones ? " - " . $observaciones : "");
        $this->gestionarLimpieza($remitente, $mensajeCompleto);
        $mensajeFuncion = "He notificado al equipo de limpieza sobre tu solicitud. Te avisaremos cuando esté confirmado.";
        return $this->llamarIALocalConContexto($promptSystem, $historial, $nuevoMensaje, $mensajeFuncion, $endpoint, $apiKey, $modelo);
    }

    /**
     * Llamar a la IA local con contexto actualizado después de ejecutar una función
     */
    private function llamarIALocalConContexto($promptSystem, $historial, $nuevoMensaje, $resultadoFuncion, $endpoint, $apiKey, $modelo)
    {
        $promptCompleto = $promptSystem . "\n\n--- HISTORIAL DE CONVERSACIÓN ---\n" . 
            ($historial ? $historial . "\n" : "") .
            "--- MENSAJE ACTUAL ---\n" .
            "Usuario: " . $nuevoMensaje . "\n" .
            "Asistente: [FUNCION ejecutada]\n" .
            "Resultado de la función: " . $resultadoFuncion . "\n" .
            "Ahora responde al usuario de forma natural integrando esta información:";

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'Content-Type' => 'application/json'
        ])->post($endpoint, [
            'prompt' => $promptCompleto,
            'modelo' => $modelo
        ]);

        if ($response->failed()) {
            Log::error("❌ Error en segunda llamada a IA local: " . $response->body());
            return $resultadoFuncion; // Devolver resultado directo si falla
        }

        $data = $response->json();
        return $data['respuesta'] ?? $resultadoFuncion;
    }

    public function clasificarMensaje($mensaje)
    {
        Log::info("🤖 CLASIFICAR MENSAJE - Iniciando para: {$mensaje}");
        
        // Configuración de la IA local Hawkins
        $config = config('services.hawkins_ai');
        $endpoint = $config['base_url'];
        
        // Asegurar que la URL termine en /chat/chat
        if (!str_ends_with($endpoint, '/chat/chat')) {
            if (str_ends_with($endpoint, '/chat')) {
                $endpoint = rtrim($endpoint, '/chat') . '/chat/chat';
            } else {
                $endpoint = rtrim($endpoint, '/') . '/chat/chat';
            }
        }
        
        $apiKey = $config['api_key'];
        $modelo = $config['model'];

        $prompt = "Eres un asistente que clasifica mensajes. Responde ÚNICAMENTE con una de estas palabras: \"averia\", \"limpieza\", \"reserva_apartamento\", o \"otro\". No agregues explicaciones ni texto adicional.\n\nMensaje a clasificar: {$mensaje}\n\nCategoría:";

        Log::info("🌐 Enviando petición a IA local para clasificación...");
        
        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'Content-Type' => 'application/json'
        ])->post($endpoint, [
            'prompt' => $prompt,
            'modelo' => $modelo
        ]);

        if ($response->failed()) {
            Log::error("❌ Error llamando a IA local para clasificación: " . $response->body());
            return 'otro';
        }

        $data = $response->json();
        
        if (isset($data['respuesta'])) {
            $categoria = trim(strtolower($data['respuesta']));
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
        
        // Registrar la avería en la base de datos como incidencia
        Log::info("📝 Registrando avería como incidencia...");
        $registrada = $this->registrarAveria($phone, $mensaje);
        
        if (!$registrada) {
            Log::info("⚠️ La incidencia ya fue registrada anteriormente");
            return "La incidencia ya fue registrada anteriormente. Nuestro equipo técnico ya ha sido notificado y te contactará pronto.";
        }
        
        // Enviar mensaje al técnico
        Log::info("👨‍🔧 Enviando mensaje al técnico...");
        $this->enviarMensajeTecnico($phone, $mensaje);
        
        Log::info("✅ GESTIONAR AVERÍA - Completado");
        return "Hemos registrado tu avería. Nuestro equipo técnico ha sido notificado y te contactará pronto.";
    }

    public function gestionarLimpieza($phone, $mensaje)
    {
        Log::info("🧹 GESTIONAR LIMPIEZA - Iniciando para teléfono: {$phone}");
        
        // Registrar la solicitud de limpieza en la base de datos como incidencia
        Log::info("📝 Registrando solicitud de limpieza como incidencia...");
        $registrada = $this->registrarLimpieza($phone, $mensaje);
        
        if (!$registrada) {
            Log::info("⚠️ La incidencia ya fue registrada anteriormente");
            return "La solicitud de limpieza ya fue registrada anteriormente. Nuestro equipo de limpieza ya ha sido notificado y te avisaremos cuando esté confirmado.";
        }
        
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
     * Obtener reserva activa del cliente por teléfono
     * Busca tanto en el cliente principal como en los huéspedes (acompañantes)
     */
    private function obtenerReservaActivaCliente($phone)
    {
        Log::info("🔍 OBTENER RESERVA ACTIVA - Buscando para teléfono: {$phone}");
        
        try {
            // 1. Buscar cliente principal por teléfono (telefono o telefono_movil)
            $cliente = Cliente::where(function($query) use ($phone) {
                $query->where('telefono', $phone)
                      ->orWhere('telefono_movil', $phone);
            })->first();
            
            if ($cliente) {
                Log::info("✅ Cliente principal encontrado: {$cliente->nombre} {$cliente->apellido1}");
                
                // Buscar reserva activa del cliente principal
                $reserva = Reserva::with(['cliente', 'apartamento'])
                    ->where('cliente_id', $cliente->id)
                    ->where('estado_id', '!=', 4) // No cancelada
                    ->where('fecha_entrada', '<=', now())
                    ->where('fecha_salida', '>=', now())
                    ->first();
                
                if ($reserva) {
                    Log::info("✅ Reserva activa encontrada por cliente principal: ID {$reserva->id}");
                    return $reserva;
                }
            }
            
            // 2. Si no se encontró, buscar en huéspedes (acompañantes)
            Log::info("🔍 Buscando en huéspedes (acompañantes)...");
            $huesped = \App\Models\Huesped::where(function($query) use ($phone) {
                $query->where('telefono_movil', $phone)
                      ->orWhere('telefono2', $phone);
            })->first();
            
            if ($huesped && $huesped->reserva_id) {
                Log::info("✅ Huésped encontrado: {$huesped->nombre} {$huesped->primer_apellido}");
                
                // Buscar reserva activa del huésped
                $reserva = Reserva::with(['cliente', 'apartamento'])
                    ->where('id', $huesped->reserva_id)
                    ->where('estado_id', '!=', 4) // No cancelada
                    ->where('fecha_entrada', '<=', now())
                    ->where('fecha_salida', '>=', now())
                    ->first();
                
                if ($reserva) {
                    Log::info("✅ Reserva activa encontrada por huésped: ID {$reserva->id}");
                    return $reserva;
                }
            }
            
            Log::warning("⚠️ No se encontró reserva activa para el teléfono: {$phone}");
            return null;
        } catch (\Exception $e) {
            Log::error("❌ Error obteniendo reserva activa: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verificar si existe una incidencia duplicada
     */
    private function verificarIncidenciaDuplicada($hash)
    {
        Log::info("🔍 VERIFICAR DUPLICADO - Hash: {$hash}");
        
        try {
            $existe = Incidencia::where('hash_identificador', $hash)
                ->where('created_at', '>=', now()->subHours(24))
                ->exists();
            
            if ($existe) {
                Log::warning("⚠️ Incidencia duplicada encontrada con hash: {$hash}");
                return true;
            }
            
            Log::info("✅ No se encontró incidencia duplicada");
            return false;
        } catch (\Exception $e) {
            Log::error("❌ Error verificando duplicado: " . $e->getMessage());
            return false; // En caso de error, permitir crear la incidencia
        }
    }

    /**
     * Obtener o crear usuario sistema para incidencias de WhatsApp
     */
    private function obtenerUsuarioSistema()
    {
        Log::info("🔍 OBTENER USUARIO SISTEMA - Buscando usuario 'Sistema WhatsApp'");
        
        try {
            $usuario = User::where('name', 'Sistema WhatsApp')->first();
            
            if ($usuario) {
                Log::info("✅ Usuario sistema encontrado: ID {$usuario->id}");
                return $usuario;
            }
            
            // Crear usuario sistema si no existe
            Log::info("📝 Creando usuario sistema...");
            $usuario = User::create([
                'name' => 'Sistema WhatsApp',
                'email' => 'sistema.whatsapp@apartamentosalgeciras.com',
                'password' => bcrypt(uniqid()), // Password aleatorio, no se usará
                'role' => 'ADMIN',
                'inactive' => false
            ]);
            
            Log::info("✅ Usuario sistema creado: ID {$usuario->id}");
            return $usuario;
        } catch (\Exception $e) {
            Log::error("❌ Error obteniendo/creando usuario sistema: " . $e->getMessage());
            // Retornar null si hay error, la incidencia se creará sin empleada_id
            return null;
        }
    }

    /**
     * Detectar prioridad basada en palabras clave del mensaje
     */
    private function detectarPrioridad($mensaje, $tipoIncidencia = 'averia')
    {
        $mensajeLower = strtolower($mensaje);
        $palabrasUrgentes = ['urgente', 'roto', 'no funciona', 'no hay', 'sin', 'emergencia', 'grave', 'importante'];
        
        foreach ($palabrasUrgentes as $palabra) {
            if (strpos($mensajeLower, $palabra) !== false) {
                Log::info("🚨 Palabra clave '{$palabra}' detectada - Prioridad: urgente");
                return 'urgente';
            }
        }
        
        // Para averías, prioridad alta por defecto
        if ($tipoIncidencia === 'averia') {
            return 'alta';
        }
        
        // Para limpieza, prioridad media por defecto
        return 'media';
    }

    /**
     * Registrar una avería en la base de datos como incidencia
     */
    private function registrarAveria($phone, $mensaje)
    {
        Log::info("🚨 REGISTRAR AVERÍA - Iniciando para teléfono: {$phone}");
        
        try {
            // 1. Obtener cliente y reserva activa
            // Esta función busca tanto en cliente principal como en huéspedes
            $reserva = $this->obtenerReservaActivaCliente($phone);
            
            // Variables para cliente y huésped
            $cliente = null;
            $huesped = null;
            
            // Obtener cliente: si hay reserva, usar el cliente de la reserva
            // Si no hay reserva, buscar en clientes o huéspedes
            if ($reserva) {
                $cliente = $reserva->cliente;
            } else {
                $cliente = Cliente::where(function($query) use ($phone) {
                    $query->where('telefono', $phone)
                          ->orWhere('telefono_movil', $phone);
                })->first();
                
                // Si no se encuentra cliente, buscar en huéspedes
                if (!$cliente) {
                    $huesped = \App\Models\Huesped::where(function($query) use ($phone) {
                        $query->where('telefono_movil', $phone)
                              ->orWhere('telefono2', $phone);
                    })->first();
                    
                    // Si encontramos huésped pero no hay reserva, no podemos crear incidencia con apartamento
                    // pero al menos tenemos información del huésped
                }
            }
            
            // 2. Generar hash único basado en reserva (no en teléfono)
            // Esto permite detectar duplicados aunque escriba el acompañante desde otro teléfono
            $mensajeCorto = substr($mensaje, 0, 50);
            if ($reserva) {
                // Si hay reserva, usar reserva_id para que funcione aunque cambie el teléfono
                $hash = md5($reserva->id . 'averia' . $mensajeCorto . date('Y-m-d'));
                Log::info("🔑 Hash generado basado en reserva ID {$reserva->id}: {$hash}");
            } else {
                // Si no hay reserva, usar teléfono como fallback
                $hash = md5($phone . 'averia' . $mensajeCorto . date('Y-m-d'));
                Log::info("🔑 Hash generado basado en teléfono (sin reserva): {$hash}");
            }
            
            // 3. Verificar duplicado
            if ($this->verificarIncidenciaDuplicada($hash)) {
                Log::warning("⚠️ Incidencia duplicada detectada - No se creará");
                return false;
            }
            
            // 4. Obtener información del apartamento
            $apartamentoNombre = 'Apartamento no identificado';
            
            if ($reserva && $reserva->apartamento) {
                $apartamentoNombre = $reserva->apartamento->nombre;
                // NO usamos apartamento_id para evitar problemas
            }
            
            // 5. Obtener usuario sistema
            $usuarioSistema = $this->obtenerUsuarioSistema();
            
            // 6. Detectar prioridad
            $prioridad = $this->detectarPrioridad($mensaje, 'averia');
            
            // 7. Crear descripción completa
            $descripcionCompleta = $mensaje;
            if ($apartamentoNombre !== 'Apartamento no identificado') {
                $descripcionCompleta .= "\n\nApartamento: {$apartamentoNombre}";
            }
            if ($cliente) {
                $descripcionCompleta .= "\nCliente: {$cliente->nombre} {$cliente->apellido1}";
            } elseif (isset($huesped) && $huesped) {
                // Si no hay cliente pero sí huésped, incluir información del huésped
                $descripcionCompleta .= "\nReportado por: {$huesped->nombre} {$huesped->primer_apellido} (Huésped)";
            }
            if ($reserva) {
                $descripcionCompleta .= "\nReserva ID: {$reserva->id}";
            }
            
            // 8. Crear la incidencia
            $incidencia = Incidencia::create([
                'titulo' => 'Avería reportada vía WhatsApp',
                'descripcion' => $descripcionCompleta,
                'tipo' => 'apartamento',
                'apartamento_id' => null, // NO usar apartamento_id
                'zona_comun_id' => null,
                'apartamento_limpieza_id' => null,
                'empleada_id' => $usuarioSistema ? $usuarioSistema->id : null,
                'prioridad' => $prioridad,
                'estado' => 'pendiente',
                'fotos' => null,
                'telefono_cliente' => $phone,
                'origen' => 'whatsapp',
                'hash_identificador' => $hash,
                'apartamento_nombre' => $apartamentoNombre,
                'reserva_id' => $reserva ? $reserva->id : null
            ]);
            
            Log::info("✅ Incidencia creada: ID {$incidencia->id}");
            
            // 9. Crear alerta para administradores
            AlertService::createIncidentAlert(
                $incidencia->id,
                $incidencia->titulo,
                'Apartamento',
                $apartamentoNombre,
                $prioridad,
                $usuarioSistema ? $usuarioSistema->name : 'Sistema WhatsApp'
            );
            
            // 10. Crear notificación
            NotificationService::notifyNewIncident($incidencia);
            
            Log::info("✅ AVERÍA REGISTRADA EXITOSAMENTE - ID: {$incidencia->id}");
            return true;
            
        } catch (\Exception $e) {
            Log::error("❌ Error registrando avería: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Registrar una solicitud de limpieza en la base de datos como incidencia
     */
    private function registrarLimpieza($phone, $mensaje)
    {
        Log::info("🧹 REGISTRAR LIMPIEZA - Iniciando para teléfono: {$phone}");
        
        try {
            // 1. Obtener cliente y reserva activa
            // Esta función busca tanto en cliente principal como en huéspedes
            $reserva = $this->obtenerReservaActivaCliente($phone);
            
            // Variables para cliente y huésped
            $cliente = null;
            $huesped = null;
            
            // Obtener cliente: si hay reserva, usar el cliente de la reserva
            // Si no hay reserva, buscar en clientes o huéspedes
            if ($reserva) {
                $cliente = $reserva->cliente;
            } else {
                $cliente = Cliente::where(function($query) use ($phone) {
                    $query->where('telefono', $phone)
                          ->orWhere('telefono_movil', $phone);
                })->first();
                
                // Si no se encuentra cliente, buscar en huéspedes
                if (!$cliente) {
                    $huesped = \App\Models\Huesped::where(function($query) use ($phone) {
                        $query->where('telefono_movil', $phone)
                              ->orWhere('telefono2', $phone);
                    })->first();
                    
                    // Si encontramos huésped pero no hay reserva, no podemos crear incidencia con apartamento
                    // pero al menos tenemos información del huésped
                }
            }
            
            // 2. Generar hash único basado en reserva (no en teléfono)
            // Esto permite detectar duplicados aunque escriba el acompañante desde otro teléfono
            $mensajeCorto = substr($mensaje, 0, 50);
            if ($reserva) {
                // Si hay reserva, usar reserva_id para que funcione aunque cambie el teléfono
                $hash = md5($reserva->id . 'limpieza' . $mensajeCorto . date('Y-m-d'));
                Log::info("🔑 Hash generado basado en reserva ID {$reserva->id}: {$hash}");
            } else {
                // Si no hay reserva, usar teléfono como fallback
                $hash = md5($phone . 'limpieza' . $mensajeCorto . date('Y-m-d'));
                Log::info("🔑 Hash generado basado en teléfono (sin reserva): {$hash}");
            }
            
            // 3. Verificar duplicado
            if ($this->verificarIncidenciaDuplicada($hash)) {
                Log::warning("⚠️ Incidencia duplicada detectada - No se creará");
                return false;
            }
            
            // 4. Obtener información del apartamento
            $apartamentoNombre = 'Apartamento no identificado';
            
            if ($reserva && $reserva->apartamento) {
                $apartamentoNombre = $reserva->apartamento->nombre;
                // NO usamos apartamento_id para evitar problemas
            }
            
            // 5. Obtener usuario sistema
            $usuarioSistema = $this->obtenerUsuarioSistema();
            
            // 6. Detectar prioridad (limpieza siempre media, a menos que tenga palabras urgentes)
            $prioridad = $this->detectarPrioridad($mensaje, 'limpieza');
            
            // 7. Crear descripción completa
            $descripcionCompleta = $mensaje;
            if ($apartamentoNombre !== 'Apartamento no identificado') {
                $descripcionCompleta .= "\n\nApartamento: {$apartamentoNombre}";
            }
            if ($cliente) {
                $descripcionCompleta .= "\nCliente: {$cliente->nombre} {$cliente->apellido1}";
            } elseif (isset($huesped) && $huesped) {
                // Si no hay cliente pero sí huésped, incluir información del huésped
                $descripcionCompleta .= "\nReportado por: {$huesped->nombre} {$huesped->primer_apellido} (Huésped)";
            }
            if ($reserva) {
                $descripcionCompleta .= "\nReserva ID: {$reserva->id}";
            }
            
            // 8. Crear la incidencia
            $incidencia = Incidencia::create([
                'titulo' => 'Solicitud de limpieza vía WhatsApp',
                'descripcion' => $descripcionCompleta,
                'tipo' => 'apartamento',
                'apartamento_id' => null, // NO usar apartamento_id
                'zona_comun_id' => null,
                'apartamento_limpieza_id' => null,
                'empleada_id' => $usuarioSistema ? $usuarioSistema->id : null,
                'prioridad' => $prioridad,
                'estado' => 'pendiente',
                'fotos' => null,
                'telefono_cliente' => $phone,
                'origen' => 'whatsapp',
                'hash_identificador' => $hash,
                'apartamento_nombre' => $apartamentoNombre,
                'reserva_id' => $reserva ? $reserva->id : null
            ]);
            
            Log::info("✅ Incidencia creada: ID {$incidencia->id}");
            
            // 9. Crear alerta para administradores
            AlertService::createIncidentAlert(
                $incidencia->id,
                $incidencia->titulo,
                'Apartamento',
                $apartamentoNombre,
                $prioridad,
                $usuarioSistema ? $usuarioSistema->name : 'Sistema WhatsApp'
            );
            
            // 10. Crear notificación
            NotificationService::notifyNewIncident($incidencia);
            
            Log::info("✅ LIMPIEZA REGISTRADA EXITOSAMENTE - ID: {$incidencia->id}");
            return true;
            
        } catch (\Exception $e) {
            Log::error("❌ Error registrando limpieza: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return false;
        }
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

    /**
     * Verifica si un mensaje es repetido (contestador automático)
     * Busca mensajes idénticos del mismo remitente en los últimos 10 minutos
     * que ya hayan sido respondidos
     * 
     * @param string $remitente Número de teléfono del remitente
     * @param string $contenido Contenido del mensaje
     * @return ChatGpt|null Mensaje repetido encontrado o null
     */
    private function verificarMensajeRepetido($remitente, $contenido)
    {
        try {
            // Normalizar el contenido para comparación (eliminar espacios extra, convertir a minúsculas)
            $contenidoNormalizado = trim(strtolower($contenido));
            
            // Buscar mensajes idénticos del mismo remitente en los últimos 10 minutos
            $fechaLimite = Carbon::now()->subMinutes(10);
            
            $mensajeAnterior = ChatGpt::where('remitente', $remitente)
                ->where('mensaje', $contenido) // Comparación exacta primero (más rápida)
                ->where('date', '>=', $fechaLimite)
                ->where('status', '!=', 2) // Excluir otros mensajes repetidos
                ->orderBy('date', 'desc')
                ->first();
            
            // Si no se encuentra con comparación exacta, intentar con normalización
            if (!$mensajeAnterior) {
                $mensajesRecientes = ChatGpt::where('remitente', $remitente)
                    ->where('date', '>=', $fechaLimite)
                    ->where('status', '!=', 2)
                    ->orderBy('date', 'desc')
                    ->limit(5) // Solo revisar los últimos 5 mensajes para optimizar
                    ->get();
                
                foreach ($mensajesRecientes as $mensaje) {
                    $mensajeNormalizado = trim(strtolower($mensaje->mensaje ?? ''));
                    
                    // Comparar mensajes normalizados (ignorar diferencias de mayúsculas/minúsculas y espacios)
                    if ($mensajeNormalizado === $contenidoNormalizado) {
                        $mensajeAnterior = $mensaje;
                        break;
                    }
                    
                    // También verificar similitud alta (más del 95% de similitud)
                    // para capturar variaciones menores del contestador automático
                    if (strlen($contenidoNormalizado) > 10 && strlen($mensajeNormalizado) > 10) {
                        $similitud = similar_text($contenidoNormalizado, $mensajeNormalizado, $percent);
                        if ($percent > 95) {
                            $mensajeAnterior = $mensaje;
                            break;
                        }
                    }
                }
            }
            
            // Si encontramos un mensaje anterior, verificar que ya se haya respondido
            if ($mensajeAnterior && $mensajeAnterior->status == 1 && !empty($mensajeAnterior->respuesta)) {
                Log::info("✅ Mensaje repetido encontrado y ya respondido", [
                    'remitente' => $remitente,
                    'mensaje_anterior_id' => $mensajeAnterior->id,
                    'fecha_anterior' => $mensajeAnterior->date,
                    'tiempo_transcurrido' => Carbon::now()->diffInSeconds($mensajeAnterior->date) . ' segundos'
                ]);
                return $mensajeAnterior;
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error("❌ Error verificando mensaje repetido: " . $e->getMessage());
            // En caso de error, no bloquear el mensaje (mejor responder que no responder)
            return null;
        }
    }

    /**
     * Método de prueba para la IA local Hawkins
     * Permite probar la integración sin necesidad de WhatsApp
     * 
     * Uso: GET /chatgpt/{texto} o GET /test-ia-local?mensaje=Hola&remitente=34612345678
     */
    public function chatGptPruebas($texto = null)
    {
        $mensaje = request()->get('mensaje', $texto);
        $remitente = request()->get('remitente', '34600000000'); // Remitente de prueba por defecto
        
        if (!$mensaje) {
            return response()->json([
                'error' => 'Debes proporcionar un mensaje',
                'uso' => 'GET /chatgpt/{texto} o GET /test-ia-local?mensaje=Hola&remitente=34612345678'
            ], 400);
        }

        Log::info("🧪 PRUEBA IA LOCAL - Mensaje: {$mensaje}, Remitente: {$remitente}");

        try {
            // Llamar al método principal que usa la IA local
            $respuesta = $this->enviarMensajeOpenAiChatCompletions($mensaje, $remitente);

            if (!$respuesta) {
                return response()->json([
                    'error' => 'No se obtuvo respuesta de la IA local',
                    'mensaje_enviado' => $mensaje,
                    'remitente' => $remitente
                ], 500);
            }

            return response()->json([
                'success' => true,
                'mensaje_enviado' => $mensaje,
                'remitente' => $remitente,
                'respuesta_ia' => $respuesta,
                'timestamp' => now()->toIso8601String()
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error en prueba de IA local: " . $e->getMessage());
            
            return response()->json([
                'error' => 'Error al procesar la petición',
                'mensaje' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Método de prueba directa a la API de IA local (sin historial)
     * Útil para verificar la conexión básica
     */
    public function testIALocalDirecta()
    {
        $mensaje = request()->get('mensaje', 'Hola, ¿cómo estás?');
        
        $config = config('services.hawkins_ai');
        $endpoint = $config['base_url'];
        
        // Asegurar que la URL termine en /chat/chat
        if (!str_ends_with($endpoint, '/chat/chat')) {
            if (str_ends_with($endpoint, '/chat')) {
                $endpoint = rtrim($endpoint, '/chat') . '/chat/chat';
            } else {
                $endpoint = rtrim($endpoint, '/') . '/chat/chat';
            }
        }
        
        $apiKey = $config['api_key'];
        $modelo = $config['model'];

        Log::info("🧪 PRUEBA DIRECTA IA LOCAL", [
            'endpoint' => $endpoint,
            'modelo' => $modelo,
            'mensaje' => $mensaje
        ]);

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Content-Type' => 'application/json'
            ])->timeout(30)->post($endpoint, [
                'prompt' => $mensaje,
                'modelo' => $modelo
            ]);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Error en la petición HTTP',
                    'status_code' => $response->status(),
                    'body' => $response->body(),
                    'config' => [
                        'endpoint' => $endpoint,
                        'modelo' => $modelo,
                        'api_key_set' => !empty($apiKey)
                    ]
                ], 500);
            }

            $data = $response->json();

            return response()->json([
                'success' => true,
                'mensaje_enviado' => $mensaje,
                'respuesta_completa' => $data,
                'respuesta_texto' => $data['respuesta'] ?? null,
                'config' => [
                    'endpoint' => $endpoint,
                    'modelo' => $modelo
                ],
                'timestamp' => now()->toIso8601String()
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error en prueba directa: " . $e->getMessage());
            
            return response()->json([
                'error' => 'Excepción al procesar la petición',
                'mensaje' => $e->getMessage(),
                'config' => [
                    'endpoint' => $endpoint,
                    'modelo' => $modelo,
                    'api_key_set' => !empty($apiKey)
                ],
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

}
