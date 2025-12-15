<?php

namespace App\Services;

use App\Models\EmailNotificaciones;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WhatsappNotificationService
{
    private string $apiUrl = 'https://graph.facebook.com/v16.0/102360642838173/messages';

    public function sendToConfiguredRecipients(string $message): void
    {
        $destinatarios = EmailNotificaciones::whereNotNull('telefono')
            ->where('telefono', '!=', '')
            ->get();

        if ($destinatarios->isEmpty()) {
            Log::info('WhatsappNotificationService: sin destinatarios configurados');
            return;
        }

        foreach ($destinatarios as $destinatario) {
            $this->sendText($destinatario->telefono, $message);
        }
    }

    private function sendText(string $phone, string $message): void
    {
        $token = env('TOKEN_WHATSAPP');

        if (!$token) {
            Log::warning('WhatsappNotificationService: TOKEN_WHATSAPP no configurado');
            return;
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phone,
            'type' => 'text',
            'text' => [
                'body' => $message,
            ],
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->post($this->apiUrl, $payload);

        if ($response->failed()) {
            Log::error("WhatsappNotificationService: error enviando a {$phone}: {$response->body()}");
            return;
        }

        $responseJson = $response->json();
        Storage::disk('local')->put(
            "overlap-whatsapp-{$phone}.json",
            json_encode($responseJson, JSON_PRETTY_PRINT)
        );
    }
}


