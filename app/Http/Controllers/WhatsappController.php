<?php

namespace App\Http\Controllers;

use App\Models\ChatGpt;
use App\Models\Mensaje;
use App\Models\Whatsapp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WhatsappController extends Controller
{
   
    public function hookWhatsapp(Request $request)
    {
        
        $responseJson = 'e&[Q/A(fJC:95rF#S)b*V(=zwJ98R[ /%%&Ff;_*AB:T./i9WB!PPSg.nT+D[ jTjr)M,]Gu9iEdpbz)GKZX/)r[Gx/K 8#Y?x3DdLRP#PrzfR]-q}!Cm#}2Dqn @w!jEAy[)DS3//i[j2_RJ;-&_PQ.@T Gp_GB_=fu7YL.4ySCX5hA)9EtAW{m] [wgWzq8z+A!(KCiyfrgGy)avyp5NJj';

        $query = $request->all();
        $mode = $query['hub_mode'];
        $token = $query['hub_verify_token'];
        $challenge = $query['hub_challenge'];

        // Formatear la fecha y hora actual
        $dateTime = Carbon::now()->format('Y-m-d_H-i-s'); // Ejemplo de formato: 2023-11-13_15-30-25

        // Crear un nombre de archivo con la fecha y hora actual
        $filename = "hookWhatsapp_{$dateTime}.txt";

        Storage::disk('local')->put($filename, json_encode($request->all()));

        return response($challenge, 200)->header('Content-Type', 'text/plain');
        
    }

    
    public function processHookWhatsapp(Request $request)
    {
        // https://thwork.crmhawkins.com/movil
        // e&[Q/A(fJC:95rF#S)b*V(=zwJ98R[ /%%&Ff;_*AB:T./i9WB!PPSg.nT+D[ jTjr)M,]Gu9iEdpbz)GKZX/)r[Gx/K 8#Y?x3DdLRP#PrzfR]-q}!Cm#}2Dqn @w!jEAy[)DS3//i[j2_RJ;-&_PQ.@T Gp_GB_=fu7YL.4ySCX5hA)9EtAW{m] [wgWzq8z+A!(KCiyfrgGy)avyp5NJj
        // $responseJson = 'e&[Q/A(fJC:95rF#S)b*V(=zwJ98R[ /%%&Ff;_*AB:T./i9WB!PPSg.nT+D[ jTjr)M,]Gu9iEdpbz)GKZX/)r[Gx/K 8#Y?x3DdLRP#PrzfR]-q}!Cm#}2Dqn @w!jEAy[)DS3//i[j2_RJ;-&_PQ.@T Gp_GB_=fu7YL.4ySCX5hA)9EtAW{m] [wgWzq8z+A!(KCiyfrgGy)avyp5NJj';

        // $query = $request->all();
        // $mode = $query['hub_mode'];
        // $token = $query['hub_verify_token'];
        // $challenge = $query['hub_challenge'];
        // $challenge = '{"prueba": "Soy tonto"}';
        $ejemplo = '{
            "messaging_product":"whatsapp",
            "metadata":{
                "display_phone_number":"34605379329",
                "phone_number_id":"102360642838173"
            },
            "contacts":[
                {
                    "profile":
                    {
                        "name":"Ivan Hawkins"
                    },
                    "wa_id":"34605621704"
                }
            ],
                    "messages":[
                        {
                            "from":"34605621704",
                            "id":"wamid.HBgLMzQ2MDU2MjE3MDQVAgASGBQzQTg2RkUxRjM0QzMyNTBERkRCMAA=",
                            "timestamp":"1681401419",
                            "text":{
                                "body":"Comenabo"
                            },
                            "type":"text"
                        }
                    ]
        }';

        $data = json_decode($request->getContent(), true);
        $id = $data['entry'][0]['changes'][0]['value']['messages'][0]['id'];

        $tipo = $data['entry'][0]['changes'][0]['value']['messages'][0]['type'];

        if ($tipo == 'audio') {

            $idMedia = $data['entry'][0]['changes'][0]['value']['messages'][0]['audio']['id'];
            $phone = $data['entry'][0]['changes'][0]['value']['messages'][0]['from'];

            Storage::disk('local')->put('audio-'.$idMedia.'.txt', json_encode($data) );

            $url = str_replace('/\/', '/', $this->obtenerAudio($idMedia));

            Storage::disk('local')->put('url-'.$idMedia.'.txt', $url );

            $fileAudio = $this->obtenerAudioMedia($url,$idMedia);

            // Storage::disk('local')->put('Conversion-'.$idMedia.'.txt', $fileAudio  );
            $file = Storage::disk('public')->get( $idMedia.'.ogg');

            $SpeechToText = $this->audioToText($file);


            // if (isset(json_decode($SpeechToText)[0]['DisplayText'])) {
            //     # code...
            // }
            Storage::disk('local')->put('phone-'.$idMedia.'.txt', $phone );

            Storage::disk('local')->put('transcripcion-'.$idMedia.'.txt', $SpeechToText->DisplayText );

            $reponseChatGPT = $this->chatGpt($SpeechToText->DisplayText);
            Storage::disk('local')->put('reponseChatGPT-'.$idMedia.'.txt', $reponseChatGPT );

            $respuestaWhatsapp = $this->contestarWhatsapp($phone, $reponseChatGPT['messages']);
            Storage::disk('local')->put('respuestaWhatsapp-'.$idMedia.'.txt', $respuestaWhatsapp );

            $dataRegistrarChat = [
                'id_mensaje' => $data['entry'][0]['changes'][0]['value']['messages'][0]['id'],
                'remitente' => $data['entry'][0]['changes'][0]['value']['contacts'][0]['wa_id'],
                'mensaje' => $SpeechToText->DisplayText,
                'respuesta' => str_replace('"','',$reponseChatGPT['messages'] ),
                'status' => 1,
                'type' => 'audio'
            ];
            ChatGpt::create( $dataRegistrarChat );

            return response('ok', 200);
        }

        else if ($tipo == 'image') {
            $mensajeExiste = ChatGpt::where('id_mensaje', $data['entry'][0]['changes'][0]['value']['messages'][0]['id'])->first();
            $phone = $data['entry'][0]['changes'][0]['value']['messages'][0]['from'];

            if ($mensajeExiste == null) {

                $idMedia = $data['entry'][0]['changes'][0]['value']['messages'][0]['image']['id'];

                Storage::disk('local')->put('image-'.$idMedia.'.txt', json_encode($data) );

                $url = $this->obtenerImage($idMedia);
                
                $urlMedia = str_replace('\/', '/', $url );

                Storage::disk('local')->put('image-response-url-'.$idMedia.'.txt', $urlMedia );
                // $url = str_replace('/\/', '/', $this->obtenerAudio($idMedia));

                $descargarImage = $this->descargarImage($urlMedia,$idMedia );

                if ($descargarImage == true) {
                    
                }

                $responseImage = 'Gracias!! recuerda que soy una inteligencia artificial y que no puedo ver lo que me has enviado pero mi supervisora María lo verá en el horario de 09:00 a 18:00 de Lunes a viernes. Si es tu DNI o Pasaporte es suficiente con enviármelo a mi. Mi supervisora lo recibirá. Muchas gracias!!';

                $respuestaWhatsapp = $this->contestarWhatsapp($phone, $responseImage);

                $dataRegistrarChat = [
                    'id_mensaje' => $data['entry'][0]['changes'][0]['value']['messages'][0]['id'],
                    'remitente' => $data['entry'][0]['changes'][0]['value']['contacts'][0]['wa_id'],
                    'mensaje' => $data['entry'][0]['changes'][0]['value']['messages'][0]['image']['id'],
                    'respuesta' => $responseImage,
                    'status' => 1,
                    'type' => 'image'
                ];
                ChatGpt::create( $dataRegistrarChat );
                
            }
            

        } 
        else {

            // Storage::disk('local')->put('data-'.$id.'.txt', json_encode($data) );

            Whatsapp::create(['mensaje' => json_encode($data)]);
            $phone = $data['entry'][0]['changes'][0]['value']['messages'][0]['from'];
            $mensaje = $data['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'];
            Storage::disk('local')->put('comprobar-'.$id.'.txt', json_encode($data) );
            
            if (str_word_count($data['entry'][0]['changes'][0]['value']['messages'][0]['text']['body']) > 1) {
                Storage::disk('local')->put('example-'.$id.'.txt', json_encode($data) );

                $mensajeExiste = Mensaje::where('id_mensaje', $data['entry'][0]['changes'][0]['value']['messages'][0]['id'] )->get();

                if (count($mensajeExiste) > 0) {
                    # code...
                }else{
                    $dataRegistrar = [
                        'id_mensaje' => $data['entry'][0]['changes'][0]['value']['messages'][0]['id'],
                        'remitente' => $data['entry'][0]['changes'][0]['value']['contacts'][0]['wa_id'],
                        'mensaje' => $data['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'],
                        'status' => 1
                    ];

                    $mensajess = Mensaje::create($dataRegistrar);

                    
                    
                    $value = $data['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'];
                    $reponseChatGPT = $this->chatGpt($value);
                    Storage::disk('local')->put('response'.$id.'.txt', $reponseChatGPT['messages'] );

                    $dataRegistrarChat = [
                        'id_mensaje' => $data['entry'][0]['changes'][0]['value']['messages'][0]['id'],
                        'remitente' => $data['entry'][0]['changes'][0]['value']['contacts'][0]['wa_id'],
                        'mensaje' => $data['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'],
                        'respuesta' => str_replace('"','',$reponseChatGPT['messages'] ),
                        'status' => 1
                    ];
                    ChatGpt::create( $dataRegistrarChat );

                    $respuestaWhatsapp = $this->contestarWhatsapp($phone, $reponseChatGPT['messages']);

                    return response(200)->header('Content-Type', 'text/plain');

                }
            }

            return response(200)->header('Content-Type', 'text/plain');
        }

        
        // return view('admin.estadisticas.enviar', compact('responseJson'));
    }

    public function obtenerAudio($id) {
        $token = 'EAAKn6tggu1UBAMqGlFOg5DarUwE9isj74UU0C6XnsftooIUAdgiIjJZAdqnnntw0Kg7gaYmfCxFqVrDl5gtNGXENKHACfsrC59z723xNbtxyoZAhTtDYpDAFN4eE598iZCmMfdXRNmA7rlat7JfWR6YOavmiDPH2WX2wquJ0YWzzxzYo96TLC4Sb7rfpwVF78UlZBmYMPQZDZD';

        $urlMensajes = 'https://graph.facebook.com/v16.0/'.$id;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $urlMensajes,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => '',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$token
            ),
        
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $responseJson = json_decode($response);
        Storage::disk('local')->put('response_Audio'.$id.'.txt', json_encode($response) );
        return $responseJson->url;
    }

    public function obtenerAudioMedia($url, $id) {

        $token = 'EAAKn6tggu1UBAMqGlFOg5DarUwE9isj74UU0C6XnsftooIUAdgiIjJZAdqnnntw0Kg7gaYmfCxFqVrDl5gtNGXENKHACfsrC59z723xNbtxyoZAhTtDYpDAFN4eE598iZCmMfdXRNmA7rlat7JfWR6YOavmiDPH2WX2wquJ0YWzzxzYo96TLC4Sb7rfpwVF78UlZBmYMPQZDZD';

        // $urlMensajes = str_replace('/\/', '/', $url);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 400);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_CUSTOMREQUEST , "GET");
        curl_setopt($ch,CURLOPT_ENCODING , "");

        $headers    = [];
        $headers[]  = "Authorization: Bearer EAAKn6tggu1UBAMqGlFOg5DarUwE9isj74UU0C6XnsftooIUAdgiIjJZAdqnnntw0Kg7gaYmfCxFqVrDl5gtNGXENKHACfsrC59z723xNbtxyoZAhTtDYpDAFN4eE598iZCmMfdXRNmA7rlat7JfWR6YOavmiDPH2WX2wquJ0YWzzxzYo96TLC4Sb7rfpwVF78UlZBmYMPQZDZD";
        $headers[]  = "Accept-Language:en-US,en;q=0.5";
        $headers[]  = "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $raw = curl_exec($ch);
        
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if((int)$httpcode == 200){
            // here save the $row content of file 
            Storage::disk('public')->put( $id.'.ogg', $raw );
            return true;
        }
        
        return false;
    }

    public function audioToText($audio){

        $token = $this->tokenAzure();
        Storage::disk('local')->put('AudioFileToken.txt', $token );
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://westeurope.stt.speech.microsoft.com/speech/recognition/conversation/cognitiveservices/v1?language=es-ES&format=detailed',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $audio,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: audio/ogg; codecs=opus',
            'Authorization: Bearer '.$token
        ),
        ));

        $response = curl_exec($curl);
        
        curl_close($curl);
        Storage::disk('local')->put('AudioFile22.txt', $response );

        return json_decode($response);
    }
    public function chatGpt($texto) {
        // Configurar los parámetros de la solicitud
     $url = 'https://api.openai.com/v1/completions';
     $headers = array(
         'Content-Type: application/json',
         'Authorization: Bearer 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'
     );


     $data = array(
       "prompt" => $texto .' ->', 
       // "model" => "davinci:ft-personal:apartamentos-hawkins-2023-04-27-09-45-29",
       // "model" => "davinci:ft-personal:modeloapartamentos-2023-05-24-16-36-49",
       // "model" => "davinci:ft-personal:apartamentosjunionew-2023-06-14-21-19-15",
       // "model" => "davinci:ft-personal:apartamento-junio-2023-07-26-23-23-07",
       // "model" => "davinci:ft-personal:apartamentosoctubre-2023-10-03-16-01-24",
       "model" => "davinci:ft-personal:apartamentos20octubre-2023-10-20-13-53-04",
       "temperature" => 0,
       "max_tokens"=> 200,
       "top_p"=> 1,
       "frequency_penalty"=> 0,
       "presence_penalty"=> 0,
       "stop"=> ["_END"]
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
         return response()->json( $error );

     } else {
         $response_data = json_decode($response, true);
         $responseReturn = [
           'status' => 'ok',
           'messages' => $response_data['choices'][0]['text']
         ];
         return $responseReturn;
     }
   }
   public function contestarWhatsapp($phone, $texto){
        $token = env('TOKEN_WHATSAPP', 'valorPorDefecto');
        
        $mensajePersonalizado = '{
            "messaging_product": "whatsapp",
            "recipient_type": "individual",
            "to": "'.str_replace('"','',$phone ).'",
            "type": "text", 
            "text": { 
                "body": "'.str_replace('"','',$texto ).'"
            }
        }';

        $urlMensajes = 'https://graph.facebook.com/v16.0/102360642838173/messages';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $urlMensajes,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $mensajePersonalizado,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$token
            ),
        
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        // $responseJson = json_decode($response);
        Storage::disk('local')->put('response000.txt', json_encode($response) );
        return $response;

    }   

}
