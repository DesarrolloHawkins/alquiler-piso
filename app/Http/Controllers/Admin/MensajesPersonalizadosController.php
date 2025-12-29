<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\WebhookController;
use App\Services\ClienteService;

class MensajesPersonalizadosController extends Controller
{
    protected $clienteService;

    public function __construct(ClienteService $clienteService)
    {
        $this->clienteService = $clienteService;
    }

    /**
     * Mostrar el formulario para enviar mensajes personalizados
     */
    public function index()
    {
        $clientes = Cliente::orderBy('nombre')->get();
        $reservas = Reserva::with(['cliente', 'apartamento'])
            ->orderBy('fecha_entrada', 'desc')
            ->limit(100)
            ->get();

        return view('admin.mensajes-personalizados.index', compact('clientes', 'reservas'));
    }

    /**
     * Enviar mensaje personalizado
     */
    public function enviar(Request $request)
    {
        $tipoDestinatario = $request->input('tipo_destinatario');
        
        // Reglas base
        $rules = [
            'canal' => 'required|in:whatsapp,channex',
            'mensaje' => 'required|string|max:5000',
            'tipo_destinatario' => 'required|in:cliente,reserva,telefono_directo,booking_id',
        ];
        
        // Mensajes base
        $messages = [
            'canal.required' => 'Debe seleccionar un canal de envío.',
            'canal.in' => 'El canal seleccionado no es válido.',
            'mensaje.required' => 'El mensaje es obligatorio.',
            'mensaje.max' => 'El mensaje no puede exceder 5000 caracteres.',
            'tipo_destinatario.required' => 'Debe seleccionar un tipo de destinatario.',
        ];
        
        // Agregar reglas según el tipo de destinatario
        switch ($tipoDestinatario) {
            case 'cliente':
                $rules['cliente_id'] = 'required|exists:clientes,id';
                $messages['cliente_id.required'] = 'Debe seleccionar un cliente.';
                $messages['cliente_id.exists'] = 'El cliente seleccionado no es válido.';
                break;
                
            case 'reserva':
                $rules['reserva_id'] = 'required|exists:reservas,id';
                $messages['reserva_id.required'] = 'Debe seleccionar una reserva.';
                $messages['reserva_id.exists'] = 'La reserva seleccionada no es válida.';
                break;
                
            case 'telefono_directo':
                $rules['telefono'] = 'required|string|max:20';
                $rules['nombre_cliente_telefono'] = 'required|string|max:255';
                $messages['telefono.required'] = 'Debe proporcionar un número de teléfono.';
                $messages['telefono.max'] = 'El teléfono no puede exceder 20 caracteres.';
                $messages['nombre_cliente_telefono.required'] = 'Debe proporcionar el nombre del cliente.';
                $messages['nombre_cliente_telefono.max'] = 'El nombre no puede exceder 255 caracteres.';
                break;
                
            case 'booking_id':
                $rules['booking_id'] = 'required|string|max:100';
                $messages['booking_id.required'] = 'Debe proporcionar un Booking ID de Channex.';
                $messages['booking_id.string'] = 'El Booking ID debe ser una cadena de caracteres.';
                $messages['booking_id.max'] = 'El Booking ID no puede exceder 100 caracteres.';
                break;
        }
        
        $request->validate($rules, $messages);

        try {
            $canal = $request->input('canal');
            $mensaje = $request->input('mensaje');
            $tipoDestinatario = $request->input('tipo_destinatario');

            // Determinar destinatario según el tipo
            $destinatario = $this->obtenerDestinatario($tipoDestinatario, $request);

            if (!$destinatario) {
                return back()->withErrors(['error' => 'No se pudo determinar el destinatario.'])->withInput();
            }

            // Enviar según el canal
            if ($canal === 'whatsapp') {
                if (!$destinatario['telefono']) {
                    return back()->withErrors(['error' => 'El destinatario seleccionado no tiene número de teléfono.'])->withInput();
                }
                
                // Obtener nombre del cliente y idioma
                $nombreCliente = $this->obtenerNombreCliente($destinatario, $tipoDestinatario, $request);
                $idiomaCliente = $this->obtenerIdiomaCliente($destinatario, $tipoDestinatario, $request);
                
                // Enviar usando template de WhatsApp
                // Variable 1: nombre del cliente
                // Variable 2: mensaje personalizado
                $resultado = $this->enviarWhatsAppTemplate(
                    $destinatario['telefono'], 
                    $nombreCliente,
                    $mensaje, // El mensaje personalizado va como variable 2
                    $idiomaCliente
                );
            } else {
                // Para Channex necesitamos booking_id
                if (!$destinatario['booking_id']) {
                    return back()->withErrors(['error' => 'Para enviar mensajes por Channex se requiere un Booking ID. Seleccione una reserva o proporcione un Booking ID.'])->withInput();
                }
                $resultado = $this->enviarChannex($destinatario['booking_id'], $mensaje);
            }

            if ($resultado['success']) {
                Log::info('Mensaje personalizado enviado exitosamente', [
                    'canal' => $canal,
                    'tipo_destinatario' => $tipoDestinatario,
                    'destinatario' => $destinatario
                ]);

                return back()->with('success', 'Mensaje enviado exitosamente.');
            } else {
                return back()->withErrors(['error' => $resultado['message'] ?? 'Error al enviar el mensaje.'])->withInput();
            }

        } catch (\Exception $e) {
            Log::error('Error al enviar mensaje personalizado', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Error inesperado: ' . $e->getMessage()])->withInput();
        }
    }


    /**
     * Obtener información del destinatario
     */
    private function obtenerDestinatario($tipoDestinatario, $request)
    {
        switch ($tipoDestinatario) {
            case 'cliente':
                $cliente = Cliente::find($request->input('cliente_id'));
                if (!$cliente || !$cliente->telefono) {
                    return null;
                }
                return [
                    'telefono' => $cliente->telefono,
                    'booking_id' => null,
                    'cliente_id' => $cliente->id
                ];

            case 'reserva':
                $reserva = Reserva::with(['cliente', 'apartamento'])->find($request->input('reserva_id'));
                if (!$reserva || !$reserva->cliente || !$reserva->cliente->telefono) {
                    return null;
                }
                return [
                    'telefono' => $reserva->cliente->telefono,
                    'booking_id' => $reserva->id_channex,
                    'cliente_id' => $reserva->cliente_id,
                    'reserva_id' => $reserva->id
                ];

            case 'telefono_directo':
                return [
                    'telefono' => $request->input('telefono'),
                    'booking_id' => null,
                    'cliente_id' => null
                ];

            case 'booking_id':
                $bookingId = $request->input('booking_id');
                $reserva = Reserva::where('id_channex', $bookingId)
                    ->with(['cliente', 'apartamento'])
                    ->first();
                
                return [
                    'telefono' => $reserva?->cliente?->telefono ?? null,
                    'booking_id' => $bookingId,
                    'cliente_id' => $reserva?->cliente_id ?? null,
                    'reserva_id' => $reserva?->id ?? null
                ];

            default:
                return null;
        }
    }

    /**
     * Obtener nombre del cliente
     */
    private function obtenerNombreCliente($destinatario, $tipoDestinatario, $request)
    {
        // Si es teléfono directo, usar el nombre proporcionado
        if ($tipoDestinatario === 'telefono_directo') {
            return $request->input('nombre_cliente_telefono', 'Cliente');
        }
        
        // Si hay cliente_id, obtener el nombre del cliente
        if ($destinatario['cliente_id']) {
            $cliente = Cliente::find($destinatario['cliente_id']);
            if ($cliente) {
                $nombreCompleto = trim(($cliente->nombre ?? '') . ' ' . ($cliente->apellido1 ?? '') . ' ' . ($cliente->apellido2 ?? ''));
                if ($nombreCompleto) {
                    return $nombreCompleto;
                }
                // Si no tiene nombre, usar alias
                if ($cliente->alias) {
                    return $cliente->alias;
                }
            }
        }
        
        return 'Cliente';
    }

    /**
     * Obtener idioma del cliente y mapearlo a código de WhatsApp
     */
    private function obtenerIdiomaCliente($destinatario, $tipoDestinatario, $request)
    {
        $idioma = 'es'; // Por defecto español
        
        // Si es teléfono directo, no hay forma de saber el idioma, usar español por defecto
        if ($tipoDestinatario === 'telefono_directo') {
            return 'es';
        }
        
        if ($destinatario['cliente_id']) {
            $cliente = Cliente::find($destinatario['cliente_id']);
            if ($cliente && $cliente->idioma) {
                // Mapear el idioma del cliente a código de WhatsApp
                $idioma = $this->mapearIdiomaWhatsApp($cliente->idioma);
            }
        }
        
        return $idioma;
    }

    /**
     * Mapear idioma del cliente a código de WhatsApp
     */
    private function mapearIdiomaWhatsApp($idiomaCliente)
    {
        // Normalizar el idioma
        $idioma = strtoupper(trim($idiomaCliente));
        
        // Mapeo de idiomas a códigos de WhatsApp
        // Los códigos de WhatsApp son: es, en, fr, de, it, ar, pt
        $mapeo = [
            'ES' => 'es',
            'ESPAÑA' => 'es',
            'ESPAÑOL' => 'es',
            'EN' => 'en',
            'ENGLISH' => 'en',
            'INGLÉS' => 'en',
            'FR' => 'fr',
            'FRANCIA' => 'fr',
            'FRENCH' => 'fr',
            'FRANCÉS' => 'fr',
            'DE' => 'de',
            'ALEMANIA' => 'de',
            'GERMAN' => 'de',
            'ALEMÁN' => 'de',
            'IT' => 'it',
            'ITALIA' => 'it',
            'ITALIAN' => 'it',
            'ITALIANO' => 'it',
            'AR' => 'ar',
            'MARRUECOS' => 'ar',
            'ARABIC' => 'ar',
            'ÁRABE' => 'ar',
            'PT' => 'pt',
            'PT_PT' => 'pt',
            'PORTUGAL' => 'pt',
            'PORTUGUESE' => 'pt',
            'PORTUGUÉS' => 'pt',
        ];
        
        return $mapeo[$idioma] ?? 'es'; // Por defecto español
    }

    /**
     * Enviar mensaje por WhatsApp usando template
     */
    private function enviarWhatsAppTemplate($telefono, $nombreCliente, $mensajePersonalizado, $idioma)
    {
        try {
            $token = env('TOKEN_WHATSAPP');
            
            if (!$token) {
                return ['success' => false, 'message' => 'Token de WhatsApp no configurado.'];
            }

            // Limpiar teléfono (quitar espacios, guiones, etc.)
            $telefono = preg_replace('/[^0-9+]/', '', $telefono);

            // Template: informacion_al_cliente
            // Variable 1: nombre del cliente
            // Variable 2: mensaje personalizado
            $templateName = 'informacion_al_cliente';
            
            $mensajeTemplate = [
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $telefono,
                "type" => "template",
                "template" => [
                    "name" => $templateName,
                    "language" => [
                        "code" => $idioma
                    ],
                    "components" => [
                        [
                            "type" => "body",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => $nombreCliente
                                ],
                                [
                                    "type" => "text",
                                    "text" => $mensajePersonalizado
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $urlMensajes = 'https://graph.facebook.com/v16.0/102360642838173/messages';

            Log::info("Enviando template WhatsApp", [
                'telefono' => $telefono,
                'template' => $templateName,
                'idioma' => $idioma,
                'nombre_cliente' => $nombreCliente
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ])->post($urlMensajes, $mensajeTemplate);

            if ($response->failed()) {
                Log::error("Error enviando template WhatsApp", [
                    'telefono' => $telefono,
                    'response' => $response->body(),
                    'status' => $response->status()
                ]);
                return ['success' => false, 'message' => 'Error al enviar mensaje por WhatsApp: ' . $response->body()];
            }

            $responseJson = $response->json();
            Log::info("Template WhatsApp enviado exitosamente", [
                'telefono' => $telefono,
                'message_id' => $responseJson['messages'][0]['id'] ?? null,
                'idioma' => $idioma
            ]);

            return ['success' => true, 'data' => $responseJson];

        } catch (\Exception $e) {
            Log::error("Excepción al enviar template WhatsApp", [
                'telefono' => $telefono,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'message' => 'Error inesperado: ' . $e->getMessage()];
        }
    }

    /**
     * Enviar mensaje por Channex
     */
    private function enviarChannex($bookingId, $mensaje)
    {
        try {
            if (!$bookingId) {
                return ['success' => false, 'message' => 'Booking ID es requerido para enviar mensajes por Channex.'];
            }

            $resultado = WebhookController::enviarMensajeAutomaticoAChannex($mensaje, $bookingId);

            if ($resultado === true) {
                Log::info("Mensaje Channex enviado exitosamente", [
                    'booking_id' => $bookingId
                ]);
                return ['success' => true];
            } else {
                Log::error("Error al enviar mensaje Channex", [
                    'booking_id' => $bookingId,
                    'resultado' => $resultado
                ]);
                return ['success' => false, 'message' => 'Error al enviar mensaje por Channex. Verifique el Booking ID y las credenciales.'];
            }

        } catch (\Exception $e) {
            Log::error("Excepción al enviar mensaje Channex", [
                'booking_id' => $bookingId,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Error inesperado: ' . $e->getMessage()];
        }
    }
}

