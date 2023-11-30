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
        if ($cliente && $cliente->idioma == null) {
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
            $reponseIdioma = $this->addIdiomaCliente($infoPais[0]['cioc']);

            // Obtener del array de idioma el código del país y enviarlo a ChatGPT
            $reponsePais = $this->addPaisCliente($infoPais[0]['cioc']);

            // Establecer la nacionalidad y guardar el cliente
            $cliente->nacionalidad = $reponsePais;
            $cliente->idioma = $reponseIdioma;
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

    public function addPaisCliente($codigo)
    {
        $paises = array("Afganistán","Albania","Alemania","Andorra","Angola","Antigua y Barbuda","Arabia Saudita","Argelia","Argentina","Armenia","Australia","Austria","Azerbaiyán","Bahamas","Bangladés","Barbados","Baréin","Bélgica","Belice","Benín","Bielorrusia","Birmania","Bolivia","Bosnia y Herzegovina","Botsuana","Brasil","Brunéi","Bulgaria","Burkina Faso","Burundi","Bután","Cabo Verde","Camboya","Camerún","Canadá","Catar","Chad","Chile","China","Chipre","Ciudad del Vaticano","Colombia","Comoras","Corea del Norte","Corea del Sur","Costa de Marfil","Costa Rica","Croacia","Cuba","Dinamarca","Dominica","Ecuador","Egipto","El Salvador","Emiratos Árabes Unidos","Eritrea","Eslovaquia","Eslovenia","España","Estados Unidos","Estonia","Etiopía","Filipinas","Finlandia","Fiyi","Francia","Gabón","Gambia","Georgia","Ghana","Granada","Grecia","Guatemala","Guyana","Guinea","Guinea ecuatorial","Guinea-Bisáu","Haití","Honduras","Hungría","India","Indonesia","Irak","Irán","Irlanda","Islandia","Islas Marshall","Islas Salomón","Israel","Italia","Jamaica","Japón","Jordania","Kazajistán","Kenia","Kirguistán","Kiribati","Kuwait","Laos","Lesoto","Letonia","Líbano","Liberia","Libia","Liechtenstein","Lituania","Luxemburgo","Madagascar","Malasia","Malaui","Maldivas","Malí","Malta","Marruecos","Mauricio","Mauritania","México","Micronesia","Moldavia","Mónaco","Mongolia","Montenegro","Mozambique","Namibia","Nauru","Nepal","Nicaragua","Níger","Nigeria","Noruega","Nueva Zelanda","Omán","Países Bajos","Pakistán","Palaos","Palestina","Panamá","Papúa Nueva Guinea","Paraguay","Perú","Polonia","Portugal","Reino Unido","República Centroafricana","República Checa","República de Macedonia","República del Congo","República Democrática del Congo","República Dominicana","República Sudafricana","Ruanda","Rumanía","Rusia","Samoa","San Cristóbal y Nieves","San Marino","San Vicente y las Granadinas","Santa Lucía","Santo Tomé y Príncipe","Senegal","Serbia","Seychelles","Sierra Leona","Singapur","Siria","Somalia","Sri Lanka","Suazilandia","Sudán","Sudán del Sur","Suecia","Suiza","Surinam","Tailandia","Tanzania","Tayikistán","Timor Oriental","Togo","Tonga","Trinidad y Tobago","Túnez","Turkmenistán","Turquía","Tuvalu","Ucrania","Uganda","Uruguay","Uzbekistán","Vanuatu","Venezuela","Vietnam","Yemen","Yibuti","Zambia","Zimbabue");
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');

        // Configurar los parámetros de la solicitud
        $url = 'https://api.openai.com/v1/chat/completions';
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ];

        $data = [
            "messages" => [
                ["role" => "user", "content" => 'podrias decirme en una palabra el pais de este array '. json_encode($paises) .', de este codigo de pais, no me digas nada mas que el string donde coincida el codigo con el pais del array que te envie y no pongas punto final: ' . $codigo,]
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
