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
