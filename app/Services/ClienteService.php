<?php

namespace App\Services;

use App\Models\Cliente; // Asegúrate de importar tu modelo Cliente
use Carbon\Carbon;
use libphonenumber\PhoneNumberUtil;
use Exception;

class ClienteService
{
    /**
     * Añadir idioma del cliente por ID
     *
     * @param int $id
     * @return array
     */
    public function getIdiomaClienteID($id)
    {
        // Obtener la fecha de hoy
        $hoy = Carbon::now();

        // Obtener el cliente por el ID
        $cliente = Cliente::find($id);

        // Validar si la nacionalidad del cliente es NULL
        if ($cliente && $cliente->nacionalidad == null) {
            // Generar la instancia del Package de Phone
            $phoneUtil = PhoneNumberUtil::getInstance();

            // Convertir el código del teléfono a código ISO del país
            try {
                $phoneNumber = $phoneUtil->parse($cliente->telefono, "ZZ");
                $codigoPaisISO = $phoneUtil->getRegionCodeForNumber($phoneNumber);
            } catch (\libphonenumber\NumberParseException $e) {
                // Devolver la operación con un status 500 y mensaje de error
                return [
                    'status' => '500',
                    'mensaje' => $e->getMessage()
                ];
            }

            // Realizar una solicitud a una API para obtener el idioma
            $url = "https://restcountries.com/v3.1/alpha/" . $codigoPaisISO;
            $datosPais = file_get_contents($url);
            $infoPais = json_decode($datosPais, true);

            // Obtener del array de idioma el código del país y enviarlo a ChatGPT
            $reponseNacionalidad = $this->addIdiomaCliente($infoPais[0]['cioc']);

            // Establecer la nacionalidad y guardar el cliente
            $cliente->nacionalidad = $reponseNacionalidad;
            $cliente->save();

            // Devolver la operación con un status 200
            return [
                'status' => '200',
            ];
        }

        return [
            'status' => '400',
            'mensaje' => 'Cliente no encontrado o ya tiene nacionalidad definida.'
        ];
    }

    /**
     * Consultar a ChatGPT el idioma basado en el código de país
     *
     * @param string $codigo
     * @return string
     * @throws Exception
     */
    public function addIdiomaCliente($codigo)
    {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');

        // Configurar los parámetros de la solicitud
        $url = 'https://api.openai.com/v1/chat/completions';
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ];

        $data = [
            "messages" => [
                ["role" => "user", "content" => 'podrias decirme en una palabra el idioma de este codigo de pais, no me digas nada mas que el idioma y no pongas punto final: ' . $codigo,]
            ],
            "model" => "gpt-4",
            "temperature" => 0,
            "max_tokens" => 200,
            "top_p" => 1,
            "frequency_penalty" => 0,
            "presence_penalty" => 0,
            "stop" => ["_END"]
        ];

        // Inicializar cURL y configurar las opciones
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // Ejecutar la solicitud y obtener la respuesta
        $response = curl_exec($curl);

        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new Exception("Error en la solicitud cURL: " . $error);
        }

        curl_close($curl);

        // Procesar la respuesta
        $response_data = json_decode($response, true);
        return $response_data['choices'][0]['message']['content'];
    }
}
