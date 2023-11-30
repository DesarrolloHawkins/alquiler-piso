<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GraciasController extends Controller
{
    //
    public function index($idioma){

        $textos = [
            'title' => 'Gracias por reservar con nosotros',
            'subtitle' => 'Gracias por reservar un apartamento en nuestras estancias.',
            'tenemos' => 'Ya tenemos los datos necesario para poder acceder a nuestras instalaciones, el mismo dia de la fecha de entrada recibira las indicaciones para acceder al apartamento.',
            'info' => 'Para cualquier informacion o reclamacion puede realizarla atraves de nuestro formulario de',
            'telefono' => 'o en el telefono:',
            'horario' => 'en horario de 09:00 a 14:00 horas.',
            'horaario2' => 'Para cualquier horario a traves de whatsapp:',
            'ir' => 'Ir al Whatsapp',
            'ia' => 'sera atendido por nuestra Inteligencia Artificial.',
            'contacto' => 'contacto',
        ];

        $nombreArchivo = 'traducciones_gracias_' . $idioma . '.json';
        $path = storage_path('app/public/' . $nombreArchivo);

        if (file_exists($path)) {
            // Leer el contenido del archivo si ya existe
            $textosTraducidos = json_decode(file_get_contents($path), true);
        } else {
            // Si no existe el archivo, hacer la petición a chatGpt
            $traduccion = $this->chatGpt('Puedes traducirme este array al idioma '. $idioma.', manteniendo la propiedad y traduciendo solo el valor. contestame solo con el array traducido, no me expliques nada devuelve solo el json en formato texto donde no se envie como code, te adjunto el array: ' . json_encode($textos));
            $textosTraducidos = json_decode($traduccion['messages']['choices'][0]['message']['content'], true);

            // Guardar la traducción en un nuevo archivo
            file_put_contents($path, json_encode($textosTraducidos));
        }

        $textos = $textosTraducidos;
        // dd($textos);

        return view('gracias', compact('textos'));
    }
    public function contacto(){
        return view('contacto');
    }

    public function chatGpt($texto) 
    {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        // Configurar los parámetros de la solicitud
        $url = 'https://api.openai.com/v1/chat/completions';
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer '. $token
        );


        $data = array(
            "messages" => [
                [
                    "role" => "user",
                    'content' => $texto
                ]
            ], 
            "model" => "gpt-4-1106-preview",
            "temperature" => 0,
            "max_tokens" => 1000,
            "top_p" => 1,
            "frequency_penalty" => 0,
            "presence_penalty" => 0,
            "stop" => ["_END"]
        );

        // Inicializar cURL y configurar las opciones
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // Ejecutar la solicitud y obtener la respuesta
        $response = curl_exec($curl);
        curl_close($curl);

        // Procesar la respuesta
        if ($response === false) {
            $error = [
            'status' => 'error',
            'messages' => 'Error al realizar la solicitud'
            ];
            Storage::disk('local')->put('errorChapt.txt', $error['messages'] );

            return response()->json( $error );

        } else {
            $response_data = json_decode($response, true);
            $responseReturn = [
            'status' => 'ok',
            //    'messages' => $response_data['choices'][0]['text']
            'messages' => $response_data
            ];
            //  Storage::disk('local')->put('respuestaFuncionChapt.txt', $responseReturn );

            return $responseReturn;
        }
    }
}
