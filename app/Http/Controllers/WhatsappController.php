<?php

namespace App\Http\Controllers;

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

}
