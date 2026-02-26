<?php

namespace App\Services;

use App\Models\EmailNotificaciones;
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
            Log::info('WhatsappNotificationService: preparando envío', [
                'destinatario_id' => $destinatario->id,
                'telefono' => $destinatario->telefono,
                'nombre' => $destinatario->nombre,
            ]);

            if (!empty($templateVariables)) {
                $this->sendTemplate($destinatario->telefono, $templateVariables);
            } else {
                $this->sendText($destinatario->telefono, $message);
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

        if ($response->failed()) {
            Log::error('WhatsappNotificationService: error enviando template', [
                'telefono' => $phone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return;
        }

        $responseJson = $response->json();
        Storage::disk('local')->put(
            "overlap-whatsapp-template-{$phone}.json",
            json_encode($responseJson, JSON_PRETTY_PRINT)
        );

        Log::info('WhatsappNotificationService: template enviado correctamente', [
            'telefono' => $phone,
            'response_id' => $responseJson['messages'][0]['id'] ?? null,
        ]);
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

        if ($response->failed()) {
            Log::error('WhatsappNotificationService: error enviando mensaje simple', [
                'telefono' => $phone,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return;
        }

        $responseJson = $response->json();
        Storage::disk('local')->put(
            "overlap-whatsapp-text-{$phone}.json",
            json_encode($responseJson, JSON_PRETTY_PRINT)
        );

        Log::info('WhatsappNotificationService: mensaje simple enviado correctamente', [
            'telefono' => $phone,
            'response_id' => $responseJson['messages'][0]['id'] ?? null,
        ]);
    }
}


