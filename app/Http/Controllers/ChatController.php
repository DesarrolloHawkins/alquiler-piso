<?php

namespace App\Http\Controllers;

use App\Services\OpenAIService;
use Illuminate\Http\Request;
use App\Models\Apartamento;

class ChatController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function sendMessage(Request $request)
    {
        $message = "¿Cuáles son los apartamentos disponibles hoy?";

        // Definir las funciones API
        $functions = [
            [
                "name" => "GetAllApartments",
                "description" => "Retrieve a list of all apartments.",
                "parameters" => [
                    "type" => "object",
                    "properties" => [
                        "apartments" => [
                            "type" => "array",
                            "description" => "List of apartments available.",
                            "items" => [
                                "type" => "object",
                                "properties" => [
                                    "apartment_id" => ["type" => "string", "description" => "Unique identifier for the apartment"],
                                    "name" => ["type" => "string", "description" => "Name of the apartment"],
                                    "capacity" => ["type" => "integer", "description" => "Maximum occupancy of the apartment"]
                                ]
                            ]
                        ]
                    ],
                    "required" => ["apartments"]
                ]
            ],
            [
                "name" => "ReportTechnicalIssue",
                "description" => "Report a technical issue that requires attention.",
                "parameters" => [
                    "type" => "object",
                    "properties" => [
                        "description" => ["type" => "string", "description" => "Description of the technical issue."],
                        "apartment_id" => ["type" => "string", "description" => "ID of the apartment with the issue."]
                    ],
                    "required" => ["description", "apartment_id"]
                ]
            ]
        ];

        $response = $this->openAIService->sendMessage($message, $functions);


        // Verificar si OpenAI solicita una llamada a función
        if (isset($response['choices'][0]['finish_reason']) && $response['choices'][0]['finish_reason'] === 'function_call') {
            $function_call = $response['choices'][0]['message']['function_call'];

            // Determinar cuál función se solicitó
            if ($function_call['name'] === 'GetAllApartments') {
                // Llamar a la función para obtener apartamentos (ejemplo de datos locales o llamada a API)
                $apartments = $this->getAllApartments();

                // Enviar la respuesta de la función a OpenAI para que la use en el mensaje final
                return response()->json($apartments);
            } elseif ($function_call['name'] === 'ReportTechnicalIssue') {
                // Extraer parámetros
                $params = json_decode($function_call['arguments'], true);
                $description = $params['description'] ?? '';
                $apartment_id = $params['apartment_id'] ?? '';

                // Llamar a la función para reportar un problema técnico
                $issueReport = $this->reportTechnicalIssue($apartment_id, $description);

                return response()->json($issueReport);
            }
        }
        return response()->json($response);
    }

    public function reportTechnicalIssue($apartment_id, $description)
    {
        // Lógica para registrar el problema técnico, por ejemplo, guardarlo en la base de datos
        // Aquí estamos simulando la respuesta
        return [
            "status" => "Issue reported successfully for apartment ID $apartment_id: $description"
        ];
    }

    public function getAllApartments()
    {
        // Obtén todos los apartamentos desde la base de datos usando el modelo `Apartamento`
        $apartments = Apartamento::all();

        // Devuelve la lista de apartamentos en formato JSON
        return response()->json([
            "apartments" => $apartments
        ]);
    }



}
