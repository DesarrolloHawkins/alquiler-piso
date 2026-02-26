<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappTemplate;
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
        $phone = preg_replace('/[\s\-\(\)\+]/', '', trim($request->phone));
        $token = env('TOKEN_WHATSAPP', 'valorPorDefecto');

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

        // Agregar parámetros si existen
        if (!empty($request->parameters)) {
            $bodyParameters = [];
            foreach ($request->parameters as $param) {
                if (!empty($param)) {
                    $bodyParameters[] = [
                        'type' => 'text',
                        'text' => $param,
                    ];
                }
            }

            if (!empty($bodyParameters)) {
                $payload['template']['components'] = [
                    [
                        'type' => 'body',
                        'parameters' => $bodyParameters,
                    ],
                ];
            }
        }

        $url = 'https://graph.facebook.com/v16.0/102360642838173/messages';

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])->post($url, $payload);

            $responseJson = $response->json();

            if ($response->failed() || isset($responseJson['error'])) {
                $errorMessage = $responseJson['error']['message'] ?? $response->body();
                $errorCode = $responseJson['error']['code'] ?? $response->status();

                Log::error('WhatsappTestController: error enviando test', [
                    'template' => $template->name,
                    'phone' => $phone,
                    'error_code' => $errorCode,
                    'error_message' => $errorMessage,
                ]);

                return back()->with('error', "Error al enviar: {$errorMessage} (Código: {$errorCode})");
            }

            $messageId = $responseJson['messages'][0]['id'] ?? null;

            Log::info('WhatsappTestController: test enviado correctamente', [
                'template' => $template->name,
                'phone' => $phone,
                'message_id' => $messageId,
            ]);

            return back()->with('success', "Mensaje enviado correctamente. Message ID: {$messageId}");
        } catch (\Exception $e) {
            Log::error('WhatsappTestController: excepción al enviar test', [
                'template' => $template->name,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', "Error: {$e->getMessage()}");
        }
    }
}
