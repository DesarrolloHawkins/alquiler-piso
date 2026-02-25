<?php

namespace App\Services;

use App\Models\EmailNotificaciones;
use App\Models\WhatsappMensaje;
use App\Services\AlertService;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class WhatsappNotificationService
{
    /**
     * Nombre del template de alerta de doble reserva en Meta.
     */
    private string $templateName = 'alerta_doble_reserva';

    /**
     * Normalizar número de teléfono para WhatsApp.
     * Elimina espacios, guiones, paréntesis y el prefijo +.
     */
    private function normalizarTelefono(string $telefono): ?string
    {
        // Eliminar espacios, guiones, paréntesis y el prefijo +
        $normalizado = preg_replace('/[\s\-\(\)\+]/', '', trim($telefono));
        
        // Si está vacío, retornar null
        if (empty($normalizado)) {
            return null;
        }
        
        // Si no empieza con código de país, asumir que es español (34)
        if (!preg_match('/^[1-9]\d{1,14}$/', $normalizado)) {
            Log::warning('WhatsappNotificationService: formato de teléfono sospechoso', [
                'telefono_original' => $telefono,
                'telefono_normalizado' => $normalizado,
            ]);
        }
        
        return $normalizado;
    }

    /**
     * Enviar notificación usando plantilla de WhatsApp (si está disponible).
     */
    public function sendToConfiguredRecipients(string $message, array $templateVariables = []): void
    {
        $destinatarios = EmailNotificaciones::whereNotNull('telefono')
            ->where('telefono', '!=', '')
            ->get();

        if ($destinatarios->isEmpty()) {
            Log::info('WhatsappNotificationService: sin destinatarios configurados');
            return;
        }

        Log::info('WhatsappNotificationService: iniciando envío a responsables', [
            'total_destinatarios' => $destinatarios->count(),
            'ids' => $destinatarios->pluck('id'),
        ]);

        foreach ($destinatarios as $destinatario) {
            // Normalizar número de teléfono (quitar espacios, +, etc.)
            $telefonoNormalizado = $this->normalizarTelefono($destinatario->telefono);
            
            if (!$telefonoNormalizado) {
                Log::warning('WhatsappNotificationService: número de teléfono inválido', [
                    'destinatario_id' => $destinatario->id,
                    'telefono_original' => $destinatario->telefono,
                ]);
                continue;
            }

            Log::info('WhatsappNotificationService: preparando envío', [
                'destinatario_id' => $destinatario->id,
                'telefono_original' => $destinatario->telefono,
                'telefono_normalizado' => $telefonoNormalizado,
                'nombre' => $destinatario->nombre,
            ]);

            if (!empty($templateVariables)) {
                $this->sendTemplate($telefonoNormalizado, $templateVariables);
            } else {
                $this->sendText($telefonoNormalizado, $message);
            }
        }
    }

    /**
     * Enviar mensaje usando template de WhatsApp.
     *
     * @param string $phone
     * @param array{0:string,1:string,2:string,3:string} $variables
     */
    private function sendTemplate(string $phone, array $variables): void
    {
        $token = env('TOKEN_WHATSAPP', 'valorPorDefecto');

        Log::info('WhatsappNotificationService: enviando template', [
            'telefono' => $phone,
            'template' => $this->templateName,
            'variables' => $variables,
        ]);

        $bodyParameters = [];
        foreach ($variables as $value) {
            $bodyParameters[] = [
                'type' => 'text',
                'text' => $value,
            ];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phone,
            'type' => 'template',
            'template' => [
                'name' => $this->templateName,
                'language' => [
                    'code' => 'es',
                ],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => $bodyParameters,
                    ],
                ],
            ],
        ];

        $url = 'https://graph.facebook.com/v16.0/102360642838173/messages';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->post($url, $payload);

        $responseJson = $response->json();
        Storage::disk('local')->put(
            "overlap-whatsapp-template-{$phone}.json",
            json_encode($responseJson, JSON_PRETTY_PRINT)
        );

        // Verificar errores incluso si el HTTP status es 200
        if ($response->failed() || isset($responseJson['error'])) {
            $errorMessage = $responseJson['error']['message'] ?? $response->body();
            $errorCode = $responseJson['error']['code'] ?? $response->status();
            $errorType = $responseJson['error']['type'] ?? 'unknown';

            Log::error('WhatsappNotificationService: error enviando template', [
                'telefono' => $phone,
                'status' => $response->status(),
                'error_code' => $errorCode,
                'error_type' => $errorType,
                'error_message' => $errorMessage,
                'response' => $responseJson,
            ]);

            // Crear alerta para administradores
            $this->crearAlertaErrorWhatsApp(
                'Error al enviar template de WhatsApp',
                "No se pudo enviar el template '{$this->templateName}' al número {$phone}.\n\n" .
                "Código de error: {$errorCode}\n" .
                "Tipo: {$errorType}\n" .
                "Mensaje: {$errorMessage}",
                [
                    'telefono' => $phone,
                    'template' => $this->templateName,
                    'error_code' => $errorCode,
                    'error_type' => $errorType,
                    'error_message' => $errorMessage,
                ]
            );

            // Si el template no existe o hay error, intentar enviar mensaje de texto como fallback
            if (isset($responseJson['error']['code']) && in_array($responseJson['error']['code'], [131047, 131051, 132000])) {
                Log::warning('WhatsappNotificationService: template no disponible, usando fallback a texto', [
                    'telefono' => $phone,
                    'error_code' => $responseJson['error']['code'],
                ]);
                
                $fallbackMessage = $this->construirMensajeFallback($variables);
                $this->sendText($phone, $fallbackMessage);
            }
            
            return;
        }

        $responseId = $responseJson['messages'][0]['id'] ?? null;

        if (!$responseId) {
            Log::error('WhatsappNotificationService: respuesta sin message ID', [
                'telefono' => $phone,
                'response' => $responseJson,
            ]);

            // Crear alerta para administradores
            $this->crearAlertaErrorWhatsApp(
                'Error al enviar template de WhatsApp',
                "El template '{$this->templateName}' se envió pero no se recibió un ID de mensaje.\n\n" .
                "Número: {$phone}\n" .
                "La respuesta de la API no contiene un message ID válido.",
                [
                    'telefono' => $phone,
                    'template' => $this->templateName,
                    'response' => $responseJson,
                ]
            );
            return;
        }

        Log::info('WhatsappNotificationService: template enviado correctamente', [
            'telefono' => $phone,
            'response_id' => $responseId,
        ]);

        // Guardar el mensaje en la base de datos para que el webhook pueda actualizar su estado
        if ($responseId) {
            $contenido = sprintf(
                'Alerta de doble reserva - Template: %s - Variables: %s',
                $this->templateName,
                implode(', ', $variables)
            );

            WhatsappMensaje::create([
                'mensaje_id' => $responseId,
                'tipo' => 'template',
                'contenido' => $contenido,
                'remitente' => null, // Mensaje saliente
                'estado' => 'sent', // Estado inicial
                'recipient_id' => $responseId, // ID que el webhook buscará
                'fecha_mensaje' => now(),
                'metadata' => [
                    'payload' => $payload,
                    'response' => $responseJson,
                    'template' => $this->templateName,
                    'variables' => $variables,
                ],
            ]);

            Log::info('WhatsappNotificationService: mensaje guardado en base de datos', [
                'telefono' => $phone,
                'recipient_id' => $responseId,
            ]);
        }
    }

    /**
     * Construir mensaje de texto fallback desde las variables del template.
     */
    private function construirMensajeFallback(array $variables): string
    {
        // Variables: [0] Apartamento, [1] Fecha inicio, [2] Fecha fin, [3] IDs reservas
        return sprintf(
            "⚠️ ALERTA: Doble Reserva Detectada\n\n" .
            "🏠 Apartamento: %s\n" .
            "📅 Fechas: %s - %s\n" .
            "🔗 Reservas: %s\n\n" .
            "Por favor, revisa y resuelve en el ERP.",
            $variables[0] ?? 'N/A',
            $variables[1] ?? 'N/A',
            $variables[2] ?? 'N/A',
            $variables[3] ?? 'N/A'
        );
    }

    /**
     * Fallback: mensaje de texto simple (si el template no está disponible).
     */
    private function sendText(string $phone, string $message): void
    {
        $token = env('TOKEN_WHATSAPP', 'valorPorDefecto');

        Log::info('WhatsappNotificationService: enviando mensaje simple', [
            'telefono' => $phone,
            'preview' => mb_substr($message, 0, 120),
        ]);

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phone,
            'type' => 'text',
            'text' => [
                'body' => $message,
            ],
        ];

        $url = 'https://graph.facebook.com/v16.0/102360642838173/messages';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->post($url, $payload);

        $responseJson = $response->json();
        Storage::disk('local')->put(
            "overlap-whatsapp-text-{$phone}.json",
            json_encode($responseJson, JSON_PRETTY_PRINT)
        );

        // Verificar errores incluso si el HTTP status es 200
        if ($response->failed() || isset($responseJson['error'])) {
            $errorMessage = $responseJson['error']['message'] ?? $response->body();
            $errorCode = $responseJson['error']['code'] ?? $response->status();
            $errorType = $responseJson['error']['type'] ?? 'unknown';

            Log::error('WhatsappNotificationService: error enviando mensaje simple', [
                'telefono' => $phone,
                'status' => $response->status(),
                'error_code' => $errorCode,
                'error_type' => $errorType,
                'error_message' => $errorMessage,
                'response' => $responseJson,
            ]);

            // Crear alerta para administradores
            $this->crearAlertaErrorWhatsApp(
                'Error al enviar mensaje de WhatsApp',
                "No se pudo enviar el mensaje al número {$phone}.\n\n" .
                "Código de error: {$errorCode}\n" .
                "Tipo: {$errorType}\n" .
                "Mensaje: {$errorMessage}",
                [
                    'telefono' => $phone,
                    'error_code' => $errorCode,
                    'error_type' => $errorType,
                    'error_message' => $errorMessage,
                ]
            );
            return;
        }

        $responseId = $responseJson['messages'][0]['id'] ?? null;

        if (!$responseId) {
            Log::error('WhatsappNotificationService: respuesta sin message ID', [
                'telefono' => $phone,
                'response' => $responseJson,
            ]);

            // Crear alerta para administradores
            $this->crearAlertaErrorWhatsApp(
                'Error al enviar mensaje de WhatsApp',
                "El mensaje se envió pero no se recibió un ID de mensaje.\n\n" .
                "Número: {$phone}\n" .
                "La respuesta de la API no contiene un message ID válido.",
                [
                    'telefono' => $phone,
                    'response' => $responseJson,
                ]
            );
            return;
        }

        Log::info('WhatsappNotificationService: mensaje simple enviado correctamente', [
            'telefono' => $phone,
            'response_id' => $responseId,
        ]);

        // Guardar el mensaje en la base de datos para que el webhook pueda actualizar su estado
        if ($responseId) {
            WhatsappMensaje::create([
                'mensaje_id' => $responseId,
                'tipo' => 'text',
                'contenido' => $message,
                'remitente' => null, // Mensaje saliente
                'estado' => 'sent', // Estado inicial
                'recipient_id' => $responseId, // ID que el webhook buscará
                'fecha_mensaje' => now(),
                'metadata' => [
                    'payload' => $payload,
                    'response' => $responseJson,
                ],
            ]);

            Log::info('WhatsappNotificationService: mensaje guardado en base de datos', [
                'telefono' => $phone,
                'recipient_id' => $responseId,
            ]);
        }
    }

    /**
     * Crear alerta de error de WhatsApp para administradores.
     */
    private function crearAlertaErrorWhatsApp(string $titulo, string $contenido, array $metadata = []): void
    {
        try {
            $admins = User::where('role', 'ADMIN')->get();
            
            foreach ($admins as $admin) {
                AlertService::createForUser($admin->id, [
                    'type' => 'error',
                    'scenario' => 'whatsapp_error',
                    'title' => $titulo,
                    'content' => $contenido,
                    'action_url' => '/admin/whatsapp/test',
                    'action_text' => 'Ir a test de WhatsApp',
                    'is_dismissible' => true,
                    'metadata' => $metadata,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WhatsappNotificationService: error creando alerta', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}


