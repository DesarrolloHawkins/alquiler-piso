<?php

namespace App\Services;

use App\Models\EmailNotificaciones;
use App\Http\Controllers\WhatsappController;
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

            $this->sendText($destinatario->telefono, $message);
        }
    }

    private function sendText(string $phone, string $message): void
    {
        Log::info('WhatsappNotificationService: enviando mensaje', [
            'telefono' => $phone,
            'preview' => mb_substr($message, 0, 120),
        ]);

        /** @var WhatsappController $whatsapp */
        $whatsapp = app(WhatsappController::class);

        $responseJson = $whatsapp->contestarWhatsapp3($phone, $message);

        Storage::disk('local')->put(
            "overlap-whatsapp-{$phone}.json",
            json_encode($responseJson, JSON_PRETTY_PRINT)
        );

        Log::info('WhatsappNotificationService: mensaje enviado correctamente', [
            'telefono' => $phone,
            'response_id' => $responseJson['messages'][0]['id'] ?? null,
        ]);
    }
}


