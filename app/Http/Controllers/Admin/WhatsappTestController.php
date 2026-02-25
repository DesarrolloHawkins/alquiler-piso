<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappTemplate;
use App\Models\WhatsappMensaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappTestController extends Controller
{
    public function index()
    {
        $templates = WhatsappTemplate::where('status', 'APPROVED')
            ->orWhere('status', 'PENDING')
            ->orderBy('name')
            ->get();

        return view('admin.whatsapp.test', compact('templates'));
    }

    public function sendTest(Request $request)
    {
        $request->validate([
            'template_name' => 'required|string|exists:whatsapp_templates,name',
            'phone' => 'required|string|regex:/^[0-9+\s\-()]+$/',
            'parameters' => 'nullable|array',
        ]);

        $template = WhatsappTemplate::where('name', $request->template_name)->first();
        
        // Normalizar número de teléfono: solo dígitos, sin espacios, guiones, paréntesis o +
        $phone = preg_replace('/[\s\-\(\)\+]/', '', trim($request->phone));
        
        // Validar formato: debe tener entre 7 y 15 dígitos (formato internacional E.164)
        if (!preg_match('/^\d{7,15}$/', $phone)) {
            return back()->with('error', 
                "Formato de teléfono inválido. Debe contener solo dígitos (7-15 dígitos) con código de país. " .
                "Ejemplo: 34612345678 (España) o 12025551234 (EEUU)"
            );
        }
        
        $token = env('TOKEN_WHATSAPP', 'valorPorDefecto');
        
        // Log detallado antes de enviar
        Log::info('WhatsappTestController: Iniciando envío de test', [
            'template_name' => $template->name,
            'template_status' => $template->status,
            'phone_original' => $request->phone,
            'phone_normalized' => $phone,
            'phone_length' => strlen($phone),
            'template_language' => $template->language ?? 'es',
        ]);

        // Construir payload
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phone,
            'type' => 'template',
            'template' => [
                'name' => $template->name,
                'language' => [
                    'code' => $template->language ?? 'es',
                ],
            ],
        ];

        // Obtener el número de parámetros requeridos del template
        $components = $template->components ?? [];
        $bodyComponent = collect($components)->firstWhere(function ($comp) {
            return strtolower($comp['type'] ?? '') === 'body';
        });
        
        $requiredParamsCount = 0;
        
        if ($bodyComponent) {
            // Método 1: Contar variables en el texto del body ({{1}}, {{2}}, etc.)
            $bodyText = $bodyComponent['text'] ?? $bodyComponent['body'] ?? '';
            if (!empty($bodyText)) {
                preg_match_all('/\{\{(\d+)\}\}/', $bodyText, $matches);
                if (!empty($matches[1])) {
                    $requiredParamsCount = max(array_map('intval', $matches[1]));
                }
            }
            
            // Método 2: Si tiene parámetros definidos en example
            if ($requiredParamsCount === 0 && isset($bodyComponent['example'])) {
                $exampleBody = $bodyComponent['example']['body_text'] ?? [];
                if (is_array($exampleBody) && !empty($exampleBody)) {
                    $requiredParamsCount = count($exampleBody[0] ?? []);
                }
            }
        }

        // Construir parámetros - enviar TODOS los requeridos
        $bodyParameters = [];
        $receivedParams = array_filter($request->parameters ?? [], function($p) {
            return !empty(trim($p));
        });
        
        // Validar que tenemos todos los parámetros requeridos
        if ($requiredParamsCount > 0 && count($receivedParams) < $requiredParamsCount) {
            return back()->with('error', 
                "El template requiere {$requiredParamsCount} parámetros, pero solo se proporcionaron " . count($receivedParams) . ". " .
                "Por favor, completa todos los campos de parámetros."
            );
        }
        
        // Construir array de parámetros
        for ($i = 0; $i < $requiredParamsCount; $i++) {
            $paramValue = trim($receivedParams[$i] ?? '');
            
            if (empty($paramValue)) {
                return back()->with('error', 
                    "El parámetro " . ($i + 1) . " es requerido. Por favor, completa todos los campos."
                );
            }
            
            $bodyParameters[] = [
                'type' => 'text',
                'text' => $paramValue,
            ];
        }

        // Solo agregar componentes si hay parámetros requeridos
        if ($requiredParamsCount > 0 && !empty($bodyParameters)) {
            $payload['template']['components'] = [
                [
                    'type' => 'body',
                    'parameters' => $bodyParameters,
                ],
            ];
        }

        $url = 'https://graph.facebook.com/v16.0/102360642838173/messages';

        // Log del payload completo (sin el token)
        $payloadForLog = $payload;
        Log::info('WhatsappTestController: Payload a enviar', [
            'payload' => $payloadForLog,
            'url' => $url,
        ]);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])->post($url, $payload);

            $statusCode = $response->status();
            $responseBody = $response->body();
            $responseJson = $response->json();

            // Log completo de la respuesta
            Log::info('WhatsappTestController: Respuesta de WhatsApp API', [
                'status_code' => $statusCode,
                'response_body' => $responseBody,
                'response_json' => $responseJson,
            ]);

            // Verificar errores (incluso si el status code es 200)
            if ($response->failed() || isset($responseJson['error'])) {
                $errorMessage = $responseJson['error']['message'] ?? 'Error desconocido';
                $errorCode = $responseJson['error']['code'] ?? $statusCode;
                $errorType = $responseJson['error']['type'] ?? 'unknown';
                $errorSubcode = $responseJson['error']['error_subcode'] ?? null;
                $errorFbtraceId = $responseJson['error']['fbtrace_id'] ?? null;

                Log::error('WhatsappTestController: Error enviando test', [
                    'template' => $template->name,
                    'phone' => $phone,
                    'status_code' => $statusCode,
                    'error_code' => $errorCode,
                    'error_type' => $errorType,
                    'error_subcode' => $errorSubcode,
                    'error_message' => $errorMessage,
                    'fbtrace_id' => $errorFbtraceId,
                    'full_response' => $responseJson,
                ]);

                // Mensaje de error más descriptivo
                $userMessage = "Error al enviar: {$errorMessage}";
                if ($errorCode) {
                    $userMessage .= " (Código: {$errorCode}";
                    if ($errorSubcode) {
                        $userMessage .= ", Subcódigo: {$errorSubcode}";
                    }
                    $userMessage .= ")";
                }
                
                // Sugerencias según el código de error
                if ($errorCode == 131047) {
                    $userMessage .= "\n\nEl número de teléfono no está registrado en WhatsApp o no es válido.";
                } elseif ($errorCode == 131051) {
                    $userMessage .= "\n\nEl template no está aprobado o no existe.";
                } elseif ($errorCode == 132000) {
                    $userMessage .= "\n\nEl número de parámetros no coincide con el template.";
                }

                return back()->with('error', $userMessage);
            }

            // Verificar que tenemos un message ID
            $messageId = $responseJson['messages'][0]['id'] ?? null;
            
            if (!$messageId) {
                Log::warning('WhatsappTestController: Respuesta exitosa pero sin message ID', [
                    'response' => $responseJson,
                ]);
                return back()->with('error', 
                    "La API respondió correctamente pero no se recibió un Message ID. " .
                    "Revisa los logs para más detalles."
                );
            }

            // Guardar el mensaje en la BD para poder rastrear su estado
            try {
                WhatsappMensaje::firstOrCreate(
                    ['mensaje_id' => $messageId],
                    [
                        'tipo' => 'template',
                        'contenido' => json_encode([
                            'template_name' => $template->name,
                            'parameters' => $bodyParameters ?? [],
                        ]),
                        'remitente' => 'system_test', // Identificar como mensaje de test
                        'estado' => 'sent', // Estado inicial
                        'recipient_id' => $messageId, // El recipient_id es el mismo que el message_id
                        'fecha_mensaje' => now(),
                        'metadata' => [
                            'payload' => $payload,
                            'response' => $responseJson,
                            'test' => true,
                            'phone' => $phone,
                        ],
                    ]
                );
                
                Log::info('WhatsappTestController: Mensaje guardado en BD', [
                    'message_id' => $messageId,
                    'recipient_id' => $messageId,
                ]);
            } catch (\Exception $e) {
                Log::error('WhatsappTestController: Error guardando mensaje en BD', [
                    'message_id' => $messageId,
                    'error' => $e->getMessage(),
                ]);
                // No fallar el envío si hay error al guardar
            }

            Log::info('WhatsappTestController: Test enviado correctamente', [
                'template' => $template->name,
                'phone' => $phone,
                'message_id' => $messageId,
                'status_code' => $statusCode,
            ]);

            return back()->with('success', 
                "Mensaje enviado correctamente. Message ID: {$messageId}\n\n" .
                "Nota: El mensaje puede tardar unos segundos en llegar. " .
                "Si no llega, verifica que el número esté registrado en WhatsApp y que el template esté aprobado."
            );
        } catch (\Exception $e) {
            Log::error('WhatsappTestController: Excepción al enviar test', [
                'template' => $template->name,
                'phone' => $phone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', "Error inesperado: {$e->getMessage()}");
        }
    }
}
