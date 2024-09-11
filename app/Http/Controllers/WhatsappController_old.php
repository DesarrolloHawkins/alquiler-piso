<?php

namespace App\Http\Controllers;

use App\Models\ChatGpt;
use App\Models\Cliente;
use App\Models\Mensaje;
use App\Models\MensajeAuto;
use App\Models\Reserva;
use App\Models\Whatsapp;
use App\Services\ClienteService;
use Carbon\Carbon;
use CURLFile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberToCarrierMapper;
use libphonenumber\geocoding\PhoneNumberOfflineGeocoder;
use libphonenumber\PhoneNumberFormat;

class WhatsappController extends Controller
{
    protected $clienteService;

    public function __construct(ClienteService $clienteService)
    {
        $this->clienteService = $clienteService;
    }
   
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
        // e&[Q/A(fJC:95rF#S)b*V(=zwJ98R[ /%%&Ff;_*AB:T./i9WB!PPSg.nT+D[ jTjr)M,]Gu9iEdpbz)GKZX/)r[Gx/K 8#Y?x3DdLRP#PrzfR]-q}!Cm#}2Dqn @w!jEAy[)DS3//i[j2_RJ;-&_PQ.@T Gp_GB_=fu7YL.4ySCX5hA)9EtAW{m] [wgWzq8z+A!(KCiyfrgGy)avyp5NJj

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
        // Storage::disk('local')->put('comprobar-'.$id.'.txt', json_encode($data) );
        
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

            Storage::disk('local')->put('transcripcion-'.$idMedia.'.txt', $SpeechToText );

            $reponseChatGPT = $this->chatGpt($SpeechToText);
            Storage::disk('local')->put('reponseChatGPT-'.$idMedia.'.txt', $reponseChatGPT );

            $respuestaWhatsapp = $this->contestarWhatsapp($phone, $reponseChatGPT['messages']);
            Storage::disk('local')->put('respuestaWhatsapp-'.$idMedia.'.txt', $respuestaWhatsapp );

            $dataRegistrarChat = [
                'id_mensaje' => $data['entry'][0]['changes'][0]['value']['messages'][0]['id'],
                'remitente' => $data['entry'][0]['changes'][0]['value']['contacts'][0]['wa_id'],
                'mensaje' => $SpeechToText,
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

                    Mensaje::create($dataRegistrar);

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

    }

    // Funcion para obtener el Audio del Whatsapp
    public function obtenerAudio($id) {
        $token = env('TOKEN_WHATSAPP', 'valorPorDefecto');

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

        $token = env('TOKEN_WHATSAPP', 'valorPorDefecto');

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
        $headers[]  = "Authorization: Bearer ". $token;
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

        // $token = $this->tokenAzure();
        // Storage::disk('local')->put('AudioFileToken.txt', $token );
        
        // $curl = curl_init();

        // curl_setopt_array($curl, array(
        // CURLOPT_URL => 'https://westeurope.stt.speech.microsoft.com/speech/recognition/conversation/cognitiveservices/v1?language=es-ES&format=detailed',
        // CURLOPT_RETURNTRANSFER => true,
        // CURLOPT_ENCODING => '',
        // CURLOPT_MAXREDIRS => 10,
        // CURLOPT_TIMEOUT => 0,
        // CURLOPT_FOLLOWLOCATION => true,
        // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        // CURLOPT_CUSTOMREQUEST => 'POST',
        // CURLOPT_POSTFIELDS => $audio,
        // CURLOPT_HTTPHEADER => array(
        //     'Content-Type: audio/ogg; codecs=opus',
        //     'Authorization: Bearer '.$token
        // ),
        // ));

        // $response = curl_exec($curl);
        
        // curl_close($curl);
        // Storage::disk('local')->put('AudioFile22.txt', $response );

        // return json_decode($response);
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.openai.com/v1/audio/transcriptions',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('file'=> new CURLFile($audio),'model' => 'whisper-1'),
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '. $token
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response['text'];
    }

    public function chatGpt($texto) {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        // Configurar los parámetros de la solicitud
     $url = 'https://api.openai.com/v1/completions';
     $headers = array(
         'Content-Type: application/json',
         'Authorization: Bearer '. $token
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
         Storage::disk('local')->put('errorChapt.txt', $error['messages'] );

         return response()->json( $error );

     } else {
         $response_data = json_decode($response, true);
         $responseReturn = [
           'status' => 'ok',
           'messages' => $response_data['choices'][0]['text']
         ];
         Storage::disk('local')->put('respuestaFuncionChapt.txt', $responseReturn['messages'] );

         return $responseReturn;
     }
    }

    public function contestarWhatsapp($phone, $texto){
        $token = env('TOKEN_WHATSAPP', 'valorPorDefecto');
        // return $texto;
        $mensajePersonalizado = '{
            "messaging_product": "whatsapp",
            "recipient_type": "individual",
            "to": "'.$phone.'",
            "type": "text", 
            "text": { 
                "body": "'.$texto.'"
            }
        }';
        // return $mensajePersonalizado;

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
                'Authorization: Bearer ' . $token
            ),
        
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        // $responseJson = json_decode($response);
        Storage::disk('local')->put('response0001.txt', json_encode($response) . json_encode($mensajePersonalizado) );
        return $response;

    }  

    public function chatGptPruebas($texto) {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        // Configurar los parámetros de la solicitud
     $url = 'https://api.openai.com/v1/completions';
     $headers = array(
         'Content-Type: application/json',
         'Authorization: Bearer '. $token
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
         Storage::disk('local')->put('errorChapt.txt', $error['messages'] );

         return response()->json( $error );

     } else {
         $response_data = json_decode($response, true);
         $responseReturn = [
           'status' => 'ok',
           'messages' => $response_data['choices'][0]['text']
         ];
         Storage::disk('local')->put('respuestaFuncionChapt.txt', $responseReturn['messages'] );

         return $response_data;
     }
    }

    function limpiarNumeroTelefono($numero) {
        // Eliminar el signo más y cualquier espacio
        $numeroLimpio = preg_replace('/\+|\s+/', '', $numero);
    
        return $numeroLimpio;
    }

    

   

    // Cron 1 minuto
    public function cron(){
        // Obtener la fecha de hoy
        $hoy = Carbon::now();
        // Obtenemos la reservas que sean igual o superior a la fecha de entrada de hoy y no tengan el DNI Enrtegado.
        $reservasEntrada = Reserva::where('dni_entregado', null)
        ->where('estado_id', 1)
        ->where('fecha_entrada', '>=', $hoy->toDateString())
        ->get();

        foreach($reservasEntrada as $reserva){
            $resultado = $this->clienteService->getIdiomaClienteID($reserva->cliente_id);

            // $cliente = Cliente::find($reserva->cliente_id);
            // $reponseNacionalidad =  $this->getIdiomaClienteID($reserva->cliente_id);
            // $reserva['return'] = $reponseNacionalidad;
            // $reserva['cliente'] = $cliente;
        }
        dd($reservasEntrada);


       
        // Obtener la fecha de dos días después
        $dosDiasDespues = Carbon::now()->addDays(2)->format('Y-m-d');

        // Modificar la consulta para obtener reservas desde hoy hasta dentro de dos días
        $reservasEntrada = Reserva::where('dni_entregado', null)
        ->where('estado_id', 1)
        ->where('cliente_id',133)
        ->get();
        // $reservasEntrada = Reserva::whereBetween('fecha_entrada', [date('Y-m-d'), $dosDiasDespues])
        // ->where('estado_id', 1)
        // ->get();

        // Validamos si hay reservas pendiente del DNI
        if(count($reservasEntrada) != 0){
            // Recorremos las reservas
            foreach($reservasEntrada as $reserva){

                // Obtenemos el mensaje del DNI si existe
                $mensajeDNI = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 1)->first();
                // Validamos si existe mensaje de DNI enviado
                if ($mensajeDNI == null) {
                    $token = bin2hex(random_bytes(16)); // Genera un token de 32 caracteres
                    $reserva->token = $token;
                    $reserva->save();
                    $mensaje = 'Desde hawkins le solicitamos que rellenes sus datos para poder continuar con la reserva, entre en el siguiente enlace para completarla: https://crm.apartamentosalgeciras.com/dni-user/'.$token;
                    $phoneCliente =  $this->limpiarNumeroTelefono($reserva->cliente->telefono);
                    $enviarMensaje = $this->contestarWhatsapp($phoneCliente, $mensaje);
                    // return $enviarMensaje;

                    // Data para guardar Mensaje enviado
                    $dataMensaje = [
                        'reserva_id' => $reserva->id,
                        'cliente_id' => $reserva->cliente_id,
                        'categoria_id' => 1,
                        'fecha_envio' => Carbon::now()
                    ];

                    MensajeAuto::create($dataMensaje);                    
                    
                }
            }
            
            return $reservasEntrada;
        } else {
            return 'No hay reservas';
        }
    }
    
    public function cron2(){

        // Obtener la fecha de hoy
        $hoy = Carbon::now();
        // Obtener la fecha de dos días después
        $dosDiasDespues = Carbon::now()->addDays(2)->format('Y-m-d');

        // Modificar la consulta para obtener reservas desde hoy hasta dentro de dos días
        $reservasEntrada = Reserva::where('dni_entregado', true)
        ->where('estado_id', 1)
        ->get();
        $reservasSalida = Reserva::whereDate('fecha_salida', '=', date('Y-m-d'))->get();

        $fechaHoy = $hoy->format('Y-m-d');

        foreach($reservasEntrada as $reserva){
            $mensajeFotos = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 2)->first();
            $mensajeClaves = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 3)->first();
            $mensajeBienvenida = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 4)->first();
            $mensajeConsulta = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 5)->first();
            $mensajeOcio = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 6)->first();
            $mensajeDespedida = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 7)->first();

            // Suponiendo que $fechaHoy es una fecha en formato 'Y-m-d' hay que pensar que es una hora menos
            $fechaInicio = date_create($fechaHoy . ' 12:41:00'); // Establece los segundos a 00
            $fechaActualSinSegundos = date_create(date('Y-m-d H:i:00')); // Hora actual sin segundos
            $diferencia = date_diff($fechaActualSinSegundos, $fechaInicio);

            $diferenciasHoraBienvenida = $diferencia->format('%R%H:%I');


            // Comprobamos la diferencia en dias de la reserva Entrada y Salida
            $dias = date_diff($hoy, date_create($reservasEntrada[0]['fecha_entrada']))->format('%R%a');
            $diasSalida = date_diff($hoy, date_create($reservasEntrada[0]['fecha_salida']))->format('%R%a');

            if ($diferenciasHoraBienvenida == '+00:00') {
                return $reserva;
                // Bienvenida a los apartamentos
                //$idioma = $this->idiomaUser($reserva->nacionalidad);
                // $data = $this->mensajesAutomaticos('despedida', 'Ivan', '+34605621704', 'es' );
                //$data = $this->mensajesAutomaticos('bienvenido', $reserva->nombre, $reserva->telefono, $idioma );
                // $actualizarReserva = Reserva::where('id', $reserva->id)->first();
                // $actualizarReserva->send_bienvenido = $hoy;
                // $actualizarReserva->save();
            }

            if($dias == 0 ){
                

                // Suponiendo que $fechaHoy es una fecha en formato 'Y-m-d' hay que pensar que es una hora menos
                $fechaInicio = date_create($fechaHoy . ' 11:22:00'); // Establece los segundos a 00
                $fechaActualSinSegundos = date_create(date('Y-m-d H:i:00')); // Hora actual sin segundos
                $diferencia = date_diff($fechaActualSinSegundos, $fechaInicio);

                $diferenciasHoraBienvenida = $diferencia->format('%R%H:%I');


                // $diferenciasHoraBienvenida = date_diff($hoy, date_create($fechaHoy .' 12:12:30'))->format('%R%H%I');
                if ($diferenciasHoraBienvenida == '+00:00') {
                    return $reserva;
                    // Bienvenida a los apartamentos
                    //$idioma = $this->idiomaUser($reserva->nacionalidad);
                    // $data = $this->mensajesAutomaticos('despedida', 'Ivan', '+34605621704', 'es' );
                    //$data = $this->mensajesAutomaticos('bienvenido', $reserva->nombre, $reserva->telefono, $idioma );
                    // $actualizarReserva = Reserva::where('id', $reserva->id)->first();
                    // $actualizarReserva->send_bienvenido = $hoy;
                    // $actualizarReserva->save();
                }
                if ($diferenciasHoraBienvenida  == 0 && $reserva->send_bienvenido == null) {
                    return $reserva;
                    // Bienvenida a los apartamentos
                    //$idioma = $this->idiomaUser($reserva->nacionalidad);
                    // $data = $this->mensajesAutomaticos('despedida', 'Ivan', '+34605621704', 'es' );
                    //$data = $this->mensajesAutomaticos('bienvenido', $reserva->nombre, $reserva->telefono, $idioma );
                    // $actualizarReserva = Reserva::where('id', $reserva->id)->first();
                    // $actualizarReserva->send_bienvenido = $hoy;
                    // $actualizarReserva->save();
                }

                $diferenciasHoraCodigos = date_diff($hoy, date_create($fechaHoy .' 12:09:00'))->format('%R%H%I');
                if ($diferenciasHoraCodigos  == 0 && $reserva->send_codigos == null) {
                    return $reserva;

                    // Bienvenida a los apartamentos
                    //$code = $this->codigoApartamento(strtoupper($reserva->habitacion));
                    //$idioma = $this->idiomaUser($reserva->nacionalidad);
                    // $data = $this->mensajesAutomaticos('despedida', 'Ivan', '+34605621704', 'es' );
                    // //$data = $this->mensajesAutomaticos('codigos', $reserva->nombre, $reserva->telefono, $reserva->telefono, $idioma, strtoupper($reserva->habitacion), '2191#', $code );
                    // $actualizarReserva = Reserva::where('id', $reserva->id)->first();
                    // $actualizarReserva->send_codigos = $hoy;
                    // $actualizarReserva->save();
                }
                $diferenciasHoraConsulta = date_diff($hoy, date_create($fechaHoy .' 16:01:00'))->format('%R%H%I');
                if ($diferenciasHoraConsulta  == 0 && $reserva->send_consulta == null) {
                    return $reserva;

                    // Bienvenida a los apartamentos
                    //$idioma = $this->idiomaUser($reserva->nacionalidad);
                    // $data = $this->mensajesAutomaticos('despedida', 'Ivan', '+34605621704', 'es' );
                    // $data = $this->mensajesAutomaticos('consulta', $reserva->nombre, $reserva->telefono, $idioma );
                    // $actualizarReserva = Reserva::where('id', $reserva->id)->first();
                    // $actualizarReserva->send_consulta = $hoy;
                    // $actualizarReserva->save();
                }

                $diferenciasHoraOcio = date_diff($hoy, date_create($fechaHoy .' 18:01:00'))->format('%R%H%I');
                if ($diferenciasHoraOcio  == 0 && $reserva->send_ocio == null) {
                    return $reserva;

                    // Bienvenida a los apartamentos
                    //$idioma = $this->idiomaUser($reserva->nacionalidad);
                    // // $data = $this->mensajesAutomaticos('despedida', 'Ivan', '+34605621704', 'es' );
                    // $data = $this->mensajesAutomaticos('ocio', $reserva->nombre, $reserva->telefono, $idioma );
                    // $actualizarReserva = Reserva::where('id', $reserva->id)->first();
                    // $actualizarReserva->send_ocio = $hoy;
                    // $actualizarReserva->save();
                }
                    
                // $data = [
                //     'prueba' => $diferenciasHora  == 0 ? $diferenciasHora : 'false'
                // ];

            }
            if ($diasSalida == 0 && $reserva->send_despedida == null) {

                $diferenciasHoraDespedida = date_diff($hoy, date_create($fechaHoy .' 12:01:00'))->format('%R%H%I');
                if ($diferenciasHoraDespedida  == 0 && $reserva->send_despedida == null) {
                    return $reserva;

                    // Bienvenida a los apartamentos
                    //$idioma = $this->idiomaUser($reserva->nacionalidad);
                    // $data = $this->mensajesAutomaticos('despedida', 'Ivan', '+34605621704', 'es' );
                    // $data = $this->mensajesAutomaticos('despedida', $reserva->nombre, $reserva->telefono, $idioma );
                    // $actualizarReserva = Reserva::where('id', $reserva->id)->first();
                    // $actualizarReserva->send_despedida = $hoy;
                    // $actualizarReserva->save();
                }

            }
        }
         
        /*  MENSAJES TEMPLATE:
                - bienvenido
                - consulta
                - ocio
                - despedida

            IDIOMAS:
                - es
                - en
                - de
                - fr
                - it
                - ar
                - pt_PT

            MENSAJES:

                - BIENVENIDO (Dia de entrada a las 10:00 h.):
                Hola {{1}}!
                Recuerda que puedes entrar al alojamiento a partir de las 14:00h, 
                pero antes debes de enviarnos  una foto de tu Documento nacional 
                de identidad o pasaporte y del de todos los ocupantes mayores de edad. 
                Si ya nos lo has enviado olvida este mensaje.
                Gracias y Felíz Estancia!

                - CONSULTA (Dia de entrada a las 18:00 h.):
                Hola {{1}}!
                Espero que todo sea de tu agrado. 
                En Hawkins trabajamos mucho para ofrecerte el mejor servicio, 
                pero ¡Somos humanos! Si ves algo que no está a tu gusto, 
                no dudes en decírmelo. 
                Lo que queremos es que pases una estancia genial.

                - OCIO (Dia de entrada a las 16:00 h.):
                Hola {{1}}!
                Estás en el lugar mas céntrico de Algeciras! 
                Te dejo algunos enlaces de interés:

                *SALUD*
                Farmacia más cercana  - https://goo.gl/maps/UnNd9ZUPXG9bH7cHA
                Farmacia 24h - https://goo.gl/maps/64WTSYmoVi2YVYx26
                Centro de salud cercano - https://goo.gl/maps/TDyoPx2qZzb5bknm9
                Hospital - https://goo.gl/maps/YZ9C66LyNrUjTbzc8
                Telefono de asistencia - 061

                *OCIO y CULTURA*
                Bar Restaurante cercano-https://goo.gl/maps/kSjqXLHXfUWX6rgc6
                Zona de bares - https://goo.gl/maps/bQ1WGSgXERYkLYve9
                Murallas Merinies https://goo.gl/maps/j5qHXZ2Q9HgANSFNA
                Teatro florida https://goo.gl/maps/43rrcsiT4kpNarxX9
                Playa del rinconcillo https://goo.gl/maps/3sHCqFZHRUHDnTiY7

                *COMPRAS*
                Supermercado más cercano https://goo.gl/maps/d7hrnxxfYUpBhHSVA
                Centro comercial https://goo.gl/maps/FtRsqgt6bShv4pTLA
                Taller de automovil https://goo.gl/maps/psnE5D3PdYX2P76C9

                - DESPEDIDA (Dia de salida a las 12:00 h.):
                Te vamos a echar de menos {{1}}!
                Para nosotros ha sido un placer que nos hayas elegido para alojarte y esperamos que vuelvas a venir. 
                Para poder seguir mejorando y que nuestro alojamiento sea cada día mejor, 
                es muy importante que nos dejes una valoración positiva. 
                Como agradecimiento, te damos un bono de descuento por si vuelves a alojarte con nosotros.
                Reserva en www.apartamentoshawkins.com y usa el cupón #SpecialClient
        */
        // $dias = 'no';

        // foreach($reservas as $reserva){
        //     $dias = date_diff($hoy, date_create($reservas[0]['fecha_entrada']))->format('%R%a');
        //     if($dias == 0){
        //         $diferenciasHora = date_diff($hoy, date_create($fechaHoy .' 16:00:00'))->format('%R%H%I');
        //             if ($diferenciasHora  == 0) {

        //                 // Bienvenida a los apartamentos
        //                 $idioma = $this->idiomaUser($reserva->nacionalidad);
        //                 // $data = $this->mensajesAutomaticos('despedida', 'Ivan', '+34605621704', 'es' );
        //                 $data = $this->mensajesAutomaticos('despedida', $reserva->nombre, $reserva->telefono, $idioma );
        //             }
               
        //         // $data = [
        //         //     'prueba' => $diferenciasHora  == 0 ? $diferenciasHora : 'false'
        //         // ];
        //     }
        // }

        // return view('site.cron', compact('reservas','hoy', 'dias','data','hora'));
    }

    public function idiomaUser($idioma){
        switch ($idioma) {
            case 'español':
                return 'es';
                break;
            case 'aleman':
                return 'de';
                break;
            case 'otros':
                return 'en';
                break;
            case 'paisesdeleste':
                return 'en';
                break;
            case 'marroqui':
                return 'ar';
                break;
            case 'frances':
                return 'fr';
                break;
            case 'ingles':
                return 'en';
                break;
            case 'italiano':
                return 'it';
                break;
            case 'hispanos':
                return 'es';
                break;
            case 'nordicos':
                return 'en';
                break;
            case 'portugues':
                return 'pt_PT';
                break;
            default:
                return 'es';
                break;
        }
    }

    public function mensajesAutomaticos($template, $nombre, $telefono, $idioma = 'es'){
        $token = 'EAAKn6tggu1UBAMqGlFOg5DarUwE9isj74UU0C6XnsftooIUAdgiIjJZAdqnnntw0Kg7gaYmfCxFqVrDl5gtNGXENKHACfsrC59z723xNbtxyoZAhTtDYpDAFN4eE598iZCmMfdXRNmA7rlat7JfWR6YOavmiDPH2WX2wquJ0YWzzxzYo96TLC4Sb7rfpwVF78UlZBmYMPQZDZD';


        $mensajePersonalizado = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $telefono,
            "type" => "template",
            "template" => [
                "name" => $template,
                "language" => ["code" => $idioma],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            ["type" => "text", "text" => $nombre],
                        ],
                    ],
                ],
            ],
        ];

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
            CURLOPT_POSTFIELDS => json_encode($mensajePersonalizado),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$token
            ),
        
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        // $responseJson = json_decode($response);
        return $response;

    }

    // Añadir idioma del cliente por coleccion
    public function getIdiomaCliente(){
        // Obtener la fecha de hoy
        $hoy = Carbon::now();

        // Obtenemos la reservas que sean igual o superior a la fecha de entrada de hoy y no tengan el DNI Enrtegado.
        $reservasEntrada = Reserva::where('dni_entregado', null)
        ->where('estado_id', 1)
        ->where('fecha_entrada', '>=', $hoy->toDateString())
        ->get();

        // Recorremos el array obtenido
        foreach($reservasEntrada as $reserva){
            // Obtenemos los datos de cliente de su modelo
            $cliente = Cliente::find($reserva->cliente_id);

            // Validamos si la nacionalidad de cliente es NULL
            if ($cliente->nacionalidad == null) {
                // Generamos la instancia del Package de Phone
                $phoneUtil = PhoneNumberUtil::getInstance();
                // Hacemos la conversion con el codigo del telefono a codigo ISO del Pais
                try {

                    $phoneNumber = $phoneUtil->parse($cliente->telefono, "ZZ");
                    $codigoPaisISO = $phoneUtil->getRegionCodeForNumber($phoneNumber);

                } catch (\libphonenumber\NumberParseException $e) {
                // Devolvemos la operacion con un status 500 con el mensaje de error.
                    return [
                        'status' => '500',
                        'mensaje' => $e
                    ];
                }

                // Luego, realizas una solicitud a una API para obtener el idioma
                // Por ejemplo, usando REST Countries API
                $url = "https://restcountries.com/v3.1/alpha/".$codigoPaisISO;
                $datosPais = file_get_contents($url);
                $infoPais = json_decode($datosPais, true);

                // Obtienes del array de idioma el codigo del pais y se lo enviamos a ChatGPT para que nos devuelva el idioma.
                $reponseNacionalidad =  $this->addIdiomaCliente($infoPais[0]['cioc']);
                // Establecemos la nacionalidad y guardamos el cliente
                $cliente->nacionalidad = $reponseNacionalidad;
                $cliente->save();
                // Devolvemos la operacion con un status 200 todo fue correctamente.
                return [
                    'status' => '200',
                ];
            }
        }
    }

    // Añadir idioma del cliente por ID
    public function getIdiomaClienteID($id){
        // Obtener la fecha de hoy
        $hoy = Carbon::now();

        // Obtenemos el cliente por el ID
        $cliente = Cliente::find($id);
         
        // Validamos si la nacionalidad del cliente es NULL
        if ($cliente->nacionalidad == null) {
            // Generamos la instancia del Package de Phone
            $phoneUtil = PhoneNumberUtil::getInstance();
            // Hacemos la conversion con el codigo del telefono a codigo ISO del Pais
            try {
                $phoneNumber = $phoneUtil->parse($cliente->telefono, "ZZ");
                $codigoPaisISO = $phoneUtil->getRegionCodeForNumber($phoneNumber);
            } catch (\libphonenumber\NumberParseException $e) {
                // Devolvemos la operacion con un status 500 con el mensaje de error.
                return [
                    'status' => '500',
                    'mensaje' => $e
                ];
            }

            // Luego, realizas una solicitud a una API para obtener el idioma
            // Por ejemplo, usando REST Countries API
            $url = "https://restcountries.com/v3.1/alpha/".$codigoPaisISO;
            $datosPais = file_get_contents($url);
            $infoPais = json_decode($datosPais, true);

            // Obtienes del array de idioma el codigo del pais y se lo enviamos a ChatGPT para que nos devuelva el idioma.
            $reponseNacionalidad =  $this->addIdiomaCliente($infoPais[0]['cioc']);
            // Establecemos la nacionalidad y guardamos el cliente
            $cliente->nacionalidad = $reponseNacionalidad;
            $cliente->save();

            // Devolvemos la operacion con un status 200 todo fue correctamente.
            return [
                'status' => '200',
            ];
        }
    }

    function addIdiomaCliente($codigo){
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        // Configurar los parámetros de la solicitud
        $url = 'https://api.openai.com/v1/chat/completions';
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer '. $token
        );

        // $cliente = Cliente::find($id);
        $data = array(
        "messages" => [
            ["role" => "user", "content" => 'podrias decirme en una palabra el idioma de este codigo de pais, no me digas nada mas que el idioma y no pongas punto final: '. $codigo,]
        ],
        // "model" => "davinci:ft-personal:apartamentos-hawkins-2023-04-27-09-45-29",
        // "model" => "davinci:ft-personal:modeloapartamentos-2023-05-24-16-36-49",
        // "model" => "davinci:ft-personal:apartamentosjunionew-2023-06-14-21-19-15",
        // "model" => "davinci:ft-personal:apartamento-junio-2023-07-26-23-23-07",
        // "model" => "davinci:ft-personal:apartamentosoctubre-2023-10-03-16-01-24",
        "model" => "gpt-4",
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
        if ($response === false) {
            return $response;
        } else {
            $response_data = json_decode($response, true);            
            return $response_data['choices'][0]['message']['content'];
        }
    }

    public function whatsapp()
    {    
        // $mensajes = Mensaje::all();      
        $mensajes = ChatGpt::orderBy('created_at', 'desc')->get();      
        $resultado = [];
        foreach ($mensajes as $elemento) {
            $resultado[$elemento['remitente']][] = $elemento;


        }
        // dd($resultado);

        // var_dump(var_export($result, true));
        return view('whatsapp.index', compact('resultado'));
    }

}
