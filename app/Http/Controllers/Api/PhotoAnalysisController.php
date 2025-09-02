<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PhotoAnalysisController extends Controller
{
    public function analyzePhoto(Request $request)
    {
        try {
            Log::info('Iniciando análisis de foto', $request->all());
            
            $request->validate([
                'image_url' => 'required|string',
                'categoria' => 'required|string',
                'limpieza_id' => 'required|integer',
                'categoria_id' => 'required|integer'
            ]);

            $imageUrl = $request->image_url;
            $categoria = $request->categoria;
            $limpiezaId = $request->limpieza_id;
            $categoriaId = $request->categoria_id;
            
            Log::info('Datos validados', [
                'image_url' => $imageUrl,
                'categoria' => $categoria,
                'limpieza_id' => $limpiezaId,
                'categoria_id' => $categoriaId
            ]);

            // Obtener la imagen desde la URL
            $imageContent = $this->getImageFromUrl($imageUrl);
            
            if (!$imageContent) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo obtener la imagen'
                ], 400);
            }

            // Convertir imagen a base64
            $base64Image = base64_encode($imageContent);

            // Preparar prompt para OpenAI
            $prompt = $this->generatePrompt($categoria);

            // Llamar a OpenAI
            Log::info('Enviando prompt a OpenAI:', ['prompt' => $prompt]);
            $openaiResponse = $this->callOpenAI($base64Image, $prompt);

            if (!$openaiResponse) {
                Log::error('OpenAI no respondió');
                return response()->json([
                    'success' => false,
                    'message' => 'Error al comunicarse con OpenAI'
                ], 500);
            }
            
            Log::info('Respuesta recibida de OpenAI:', ['response' => $openaiResponse]);

            // Analizar respuesta de OpenAI
            $analysis = $this->parseOpenAIResponse($openaiResponse);
            
            // Determinar si pasa el control de calidad
            $passesQuality = $this->determineQualityPass($analysis);

            // Guardar análisis en la base de datos
            $photoAnalysis = $this->saveAnalysis($limpiezaId, $categoriaId, $analysis, $passesQuality, $imageUrl, $categoria);
            
            // Si es el primer análisis de esta limpieza, asignar empleada
            if ($photoAnalysis) {
                $this->asignarEmpleadaALimpieza($limpiezaId);
            }

            return response()->json([
                'success' => true,
                'analysis' => $analysis,
                'passes_quality' => $passesQuality,
                'raw_response' => $openaiResponse
            ]);

        } catch (\Exception $e) {
            Log::error('Error en análisis de foto: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getImageFromUrl($imageUrl)
    {
        try {
            Log::info('Obteniendo imagen desde URL', ['imageUrl' => $imageUrl]);
            
            // Si es una URL local, convertir a path
            if (strpos($imageUrl, 'http') === 0) {
                // Extraer el path de la URL, independientemente del dominio
                $parsedUrl = parse_url($imageUrl);
                $path = $parsedUrl['path'] ?? '';
                
                // Si el path empieza con /images/, usarlo directamente
                if (strpos($path, '/images/') === 0) {
                    $fullPath = public_path($path);
                    
                    Log::info('URL local detectada', [
                        'original_url' => $imageUrl,
                        'path_extraido' => $path,
                        'full_path' => $fullPath,
                        'file_exists' => file_exists($fullPath)
                    ]);
                    
                    if (file_exists($fullPath)) {
                        $content = file_get_contents($fullPath);
                        Log::info('Imagen obtenida exitosamente', [
                            'file_size' => strlen($content),
                            'path' => $fullPath
                        ]);
                        return $content;
                    } else {
                        Log::warning('Archivo no encontrado en path', ['full_path' => $fullPath]);
                    }
                }
            }
            
            // Si es un path relativo
            $fullPath = public_path($imageUrl);
            Log::info('Probando path relativo', [
                'imageUrl' => $imageUrl,
                'full_path' => $fullPath,
                'file_exists' => file_exists($fullPath)
            ]);
            
            if (file_exists($fullPath)) {
                $content = file_get_contents($fullPath);
                Log::info('Imagen obtenida exitosamente (path relativo)', [
                    'file_size' => strlen($content),
                    'path' => $fullPath
                ]);
                return $content;
            }

            Log::error('No se pudo obtener la imagen desde ninguna ruta');
            return null;
        } catch (\Exception $e) {
            Log::error('Error obteniendo imagen: ' . $e->getMessage(), [
                'imageUrl' => $imageUrl,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    private function generatePrompt($categoria)
    {
        return "Eres un inspector de calidad de limpieza para apartamentos turísticos. Analiza esta imagen de la categoría: {$categoria}.

        INSTRUCCIONES CRÍTICAS:
        1. Evalúa la calidad de limpieza general (SOLO: excelente/buena/regular/mala)
        2. Identifica deficiencias específicas y visibles
        3. Verifica si cumple estándares de apartamento turístico (true/false)
        4. Busca manchas, suciedad, desorden, elementos mal colocados
        5. Considera presentación visual y profesional
        6. Asigna puntuación del 1 al 10
        7. Escribe observaciones descriptivas y específicas
        8. Proporciona recomendaciones prácticas y accionables

        REGLAS ABSOLUTAS:
        - Responde ÚNICAMENTE en formato JSON válido
        - NO incluyas texto explicativo antes o después del JSON
        - NO uses comillas extra o caracteres adicionales
        - SIEMPRE incluye todos los campos requeridos
        - Las deficiencias y recomendaciones deben ser arrays con elementos reales

        FORMATO JSON EXACTO (responde SOLO esto):
        {
            \"calidad_general\": \"buena\",
            \"deficiencias\": [\"mancha en la pared\", \"toalla mal doblada\"],
            \"cumple_estandares\": false,
            \"observaciones\": \"La limpieza general es aceptable, pero se observan manchas en la pared y la toalla no está correctamente doblada según estándares turísticos\",
            \"puntuacion\": 6,
            \"recomendaciones\": [\"Limpiar mancha en pared con producto específico\", \"Doblar toalla siguiendo estándares de hotel\"]
        }";
    }

    private function callOpenAI($base64Image, $prompt)
    {
        try {
            Log::info('Iniciando llamada a OpenAI', [
                'base64_length' => strlen($base64Image),
                'prompt_length' => strlen($prompt)
            ]);
            
            $apiKey = config('services.openai.api_key');
            
            if (!$apiKey) {
                Log::error('API key de OpenAI no configurada');
                return null;
            }
            
            Log::info('API key de OpenAI configurada', [
                'api_key_length' => strlen($apiKey),
                'api_key_preview' => substr($apiKey, 0, 10) . '...'
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json'
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $prompt
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => 'data:image/jpeg;base64,' . $base64Image
                                ]
                            ]
                        ]
                    ]
                ],
                'max_tokens' => 1000
            ]);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'];
            } else {
                Log::error('Error en respuesta de OpenAI: ' . $response->body());
                return null;
            }

        } catch (\Exception $e) {
            Log::error('Error llamando a OpenAI: ' . $e->getMessage());
            return null;
        }
    }

    private function parseOpenAIResponse($response)
    {
        try {
            Log::info('Respuesta raw de OpenAI:', ['response' => $response]);
            
            // Limpiar la respuesta de OpenAI
            $cleanResponse = trim($response);
            
            // Intentar parsear JSON
            $parsed = json_decode($cleanResponse, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                Log::info('JSON parseado correctamente:', $parsed);
                
                // Validar y completar campos obligatorios
                $parsed = $this->validateAndCompleteAnalysis($parsed);
                
                return $parsed;
            }
            
            Log::warning('JSON no válido, creando respuesta estructurada', [
                'json_error' => json_last_error_msg(),
                'clean_response' => $cleanResponse
            ]);
            
            // Si no es JSON válido, crear respuesta estructurada mejorada
            return $this->createFallbackAnalysis($cleanResponse);

        } catch (\Exception $e) {
            Log::error('Error parseando respuesta de OpenAI: ' . $e->getMessage());
            
            return $this->createFallbackAnalysis($response);
        }
    }
    
    private function validateAndCompleteAnalysis($analysis)
    {
        // Asegurar que todos los campos obligatorios existan
        $defaults = [
            'calidad_general' => 'regular',
            'deficiencias' => [],
            'cumple_estandares' => false,
            'observaciones' => 'Análisis realizado correctamente',
            'puntuacion' => 5,
            'recomendaciones' => ['Revisar manualmente la imagen', 'Verificar estándares de limpieza']
        ];
        
        foreach ($defaults as $key => $defaultValue) {
            if (!isset($analysis[$key]) || empty($analysis[$key])) {
                $analysis[$key] = $defaultValue;
            }
        }
        
        // Validar calidad_general
        $validQualities = ['excelente', 'buena', 'regular', 'mala'];
        if (!in_array($analysis['calidad_general'], $validQualities)) {
            $analysis['calidad_general'] = 'regular';
        }
        
        // Validar puntuación
        if (!is_numeric($analysis['puntuacion']) || $analysis['puntuacion'] < 1 || $analysis['puntuacion'] > 10) {
            $analysis['puntuacion'] = 5;
        }
        
        // Asegurar que deficiencias y recomendaciones sean arrays
        if (!is_array($analysis['deficiencias'])) {
            $analysis['deficiencias'] = [];
        }
        if (!is_array($analysis['recomendaciones'])) {
            $analysis['recomendaciones'] = ['Revisar manualmente la imagen'];
        }
        
        return $analysis;
    }
    
    private function createFallbackAnalysis($rawResponse)
    {
        return [
            'calidad_general' => 'regular',
            'deficiencias' => ['Análisis automático no disponible'],
            'cumple_estandares' => false,
            'observaciones' => 'Se requiere revisión manual de la imagen',
            'puntuacion' => 5,
            'recomendaciones' => [
                'Revisar manualmente la imagen',
                'Verificar estándares de limpieza',
                'Documentar estado actual'
            ],
            'raw_response' => $rawResponse
        ];
    }

    private function determineQualityPass($analysis)
    {
        // Lógica para determinar si pasa el control de calidad
        if (!isset($analysis['cumple_estandares'])) {
            return false;
        }

        // Si OpenAI dice que no cumple estándares
        if (!$analysis['cumple_estandares']) {
            return false;
        }

        // Verificar puntuación si existe
        if (isset($analysis['puntuacion']) && $analysis['puntuacion'] < 7) {
            return false;
        }

        // Verificar si hay deficiencias críticas
        if (isset($analysis['deficiencias']) && count($analysis['deficiencias']) > 2) {
            return false;
        }

        return true;
    }

    private function saveAnalysis($limpiezaId, $categoriaId, $analysis, $passesQuality, $imageUrl, $categoria)
    {
        try {
            // Obtener el usuario autenticado (empleada)
            $empleadaId = auth()->id();
            
            // Crear el análisis en la base de datos
            $photoAnalysis = \App\Models\PhotoAnalysis::create([
                'limpieza_id' => $limpiezaId,
                'categoria_id' => $categoriaId,
                'empleada_id' => $empleadaId,
                'image_url' => $imageUrl,
                'categoria_nombre' => $categoria,
                'calidad_general' => $analysis['calidad_general'],
                'puntuacion' => $analysis['puntuacion'],
                'cumple_estandares' => $analysis['cumple_estandares'],
                'deficiencias' => $analysis['deficiencias'],
                'observaciones' => $analysis['observaciones'],
                'recomendaciones' => $analysis['recomendaciones'],
                'fecha_analisis' => now(),
                'raw_openai_response' => $analysis
            ]);
            
            Log::info('Análisis guardado en base de datos', [
                'id' => $photoAnalysis->id,
                'limpieza_id' => $limpiezaId,
                'categoria_id' => $categoriaId,
                'empleada_id' => $empleadaId
            ]);
            
            return $photoAnalysis;
            
        } catch (\Exception $e) {
            Log::error('Error guardando análisis en base de datos: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Asignar empleada a la limpieza si no está asignada
     */
    private function asignarEmpleadaALimpieza($limpiezaId)
    {
        try {
            // Verificar si ya tiene empleada asignada
            $limpieza = \App\Models\ApartamentoLimpieza::find($limpiezaId);
            
            if ($limpieza && !$limpieza->empleada_id) {
                // Asignar la empleada actual
                $limpieza->update(['empleada_id' => auth()->id()]);
                
                Log::info('Empleada asignada a limpieza', [
                    'limpieza_id' => $limpiezaId,
                    'empleada_id' => auth()->id()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error asignando empleada a limpieza: ' . $e->getMessage());
        }
    }
}
