<?php

namespace App\Http\Controllers;

use App\Models\ChatGpt;
use App\Models\Cliente;
use App\Models\Configuraciones;
use App\Models\Mensaje;
use App\Models\MensajeAuto;
use App\Models\Reparaciones;
use App\Models\Reserva;
use App\Models\Whatsapp;
use App\Services\ClienteService;
use Carbon\Carbon;
use CURLFile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberToCarrierMapper;
use libphonenumber\geocoding\PhoneNumberOfflineGeocoder;
use libphonenumber\PhoneNumberFormat;
use Illuminate\Support\Facades\Log;
use PhpOption\None;

class WhatsappController extends Controller
{
    protected $clienteService;

    public function __construct(ClienteService $clienteService)
    {
        $this->clienteService = $clienteService;
    }

    public function hookWhatsapp(Request $request)
    {
        $responseJson = env('WHATSAPP_KEY', 'valorPorDefecto');

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
        $data = json_decode($request->getContent(), true);
        $tipo = $data['entry'][0]['changes'][0]['value']['messages'][0]['type'];

        if ($tipo == 'audio') {
            $this->audioMensaje($data);
        }elseif($tipo == 'image') {
            $this->imageMensaje($data);
        }else {
            $this->textMensaje($data);
        }

        return response(200)->header('Content-Type', 'text/plain');

    }

    public function audioMensaje( $data ){
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
    }

    public function imageMensaje( $data )
    {
        
        // Comprobamos si el Mensaje existe ya
        $mensajeExiste = ChatGpt::where('id_mensaje', $data['entry'][0]['changes'][0]['value']['messages'][0]['id'])->first();
        
        // Obetenemos el numero de Telefono
        $phone = $data['entry'][0]['changes'][0]['value']['messages'][0]['from'];
        Storage::disk('publico')->put('data-'.$phone.'.txt', json_encode($data) );

        // Comprobamos si existe algun cliente con ese telefono
        $cliente = Cliente::where('telefono', $phone)->get();
        // Si el cliente existe vamos a buscar una reserva que tenga
        if (count($cliente) > 0 ) {
            // Reservas del cliente que nos ha escrito
            $reservas = Reserva::where('cliente_id', $cliente->id)->get();

            // Comprobamos si existen reservas
            if (count($reservas) > 0) {
                foreach ($reservas as $reserva) {
                    $hoy = Carbon::now()->toDateString(); // Obtener solo la fecha de hoy (YYYY-MM-DD)
                    if ($reserva->fecha_entrada->toDateString() >= $hoy) {
                        $idImg = $data['entry'][0]['changes'][0]['value']['messages'][0]['image']['id'];
                        $fileName = $this->descargarImage($idImg); // obtenemos el nombre de la imagen

                        $respuestaImageChatGPT = $this->chatGptPruebasConImagen($fileName);
                        
                        Storage::disk('publico')->put('RespuestaChatSobreImagen-'.$idImg.'.txt', $respuestaImageChatGPT );
                        return true;
                        //    'nombre': nombre,
                        //    'apellido1': apellido1,
                        //    'apellido2': apellido2,
                        //    'nacionalidad': data['cliente']['nacionalidadCode'],
                        //    'nacionalidadStr': data['cliente']['nacionalidadStr'],
                        //    'tipoDocumento': data['cliente']['tipo_documento'],
                        //    'tipoDocumentoStr': data['cliente']['tipo_documento_str'],
                        //    'numIdentificacion': data['cliente']['num_identificacion'],
                        //    'fechaExpedicionDoc': datetime.strptime(data['cliente']['fecha_expedicion_doc'], '%Y-%m-%d').strftime('%d/%m/%Y'),
                        //    'dia': datetime.strptime(data['cliente']['fecha_nacimiento'], '%Y-%m-%d').day,
                        //    'mes': datetime.strptime(data['cliente']['fecha_nacimiento'], '%Y-%m-%d').month,
                        //    'ano': datetime.strptime(data['cliente']['fecha_nacimiento'], '%Y-%m-%d').year,
                        //    'fechaNacimiento': datetime.strptime(data['cliente']['fecha_nacimiento'], '%Y-%m-%d').strftime('%d/%m/%Y'),
                        //    'sexo': data['cliente']['sexo_str'],
                        //    'sexoStr': data['cliente']['sexo'],
                        //    'fechaEntrada': datetime.strptime(data['fecha_entrada'], '%Y-%m-%d').strftime('%d/%m/%Y'),

                    /*    {
                            "id":"chatcmpl-ABjhmK5e9ctr1oPPgo6EPvb5k0SUN",
                            "object":"chat.completion",
                            "created":1727360662,
                            "model":"gpt-4o-2024-05-13",
                            "choices":[
                                {
                                    "index":0,
                                    "message":{
                                        "role":"assistant",
                                        "content":
                                            "isDni": true,
                                            "isPasaporte": false,
                                            "informacion": {
                                                "nombre": "FILIPE ANDR\u00c9\",
                                                "apellido1": "JESUS",
                                                "apellido2": "CASTANHA",
                                                "fechaNacimiento": "15 06 1988",
                                                "fechaExpedicionDoc": "03 08 2031",
                                                "pais": "PORTUGAL",
                                                "numIdentificacion": "13379841",
                                                "value": "A9125AAAAA",
                                                "isEuropean": true,
                                                "sexo": "Masculino o Femenino",
                                                "nacionalidadStr": ,
                                                "nacionalidad": ,
                                                "tipoDocumento": ,
                                                "tipoDocumentoStr": ,
                                                "sexoStr": ,
                                                "dia": esto es sobre la fecha de nacimiento ,
                                                "mes": esto es sobre la fecha de nacimiento ,
                                                "ano": esto es sobre la fecha de nacimiento ,
                                            }
                                    },
                                    "refusal":null
                                },
                                    "logprobs":null,
                                    "finish_reason":"stop"
                                }
                            ],
                            "usage":
                                {
                                    "prompt_tokens":4866,
                                    "completion_tokens":135,
                                    "total_tokens":5001,
                                    "completion_tokens_details":{
                                        "reasoning_tokens":0
                                    }
                                },
                            "system_fingerprint":"fp_3537616b13"
                        } */

                        // if($respuestaImageChatGPT['isDni'] == true){
                        //     if($cliente->nombre == $respuestaImageChatGPT){
                        //         $cliente->nombre == null ? $cliente->nombre = $respuestaImageChatGPT['informacion']->nombre : '';
                        //         $cliente->apellido1 == null ? $cliente->apellido1 = $respuestaImageChatGPT['informacion']->apellido1 : '';
                        //         $cliente->apellido2 == null ? $cliente->apellido2 = $respuestaImageChatGPT['informacion']->apellido2 : '';
                        //         $cliente->nacionalidad == null ? $cliente->nacionalidad = $respuestaImageChatGPT['informacion']->nacionalidad : '';
                        //         $cliente->nombre == null ? $cliente->nombre = $respuestaImageChatGPT['informacion']->nombre : '';
                        //         $cliente->nombre == null ? $cliente->nombre = $respuestaImageChatGPT['informacion']->nombre : '';
                        //         $cliente->nombre == null ? $cliente->nombre = $respuestaImageChatGPT['informacion']->nombre : '';
                        //     }
                        // }elseif($respuestaImageChatGPT['isPasaporte'] == true){

                        // }
                        

                        // $responseImage = '!';

                        // $dataRegistrarChat = [
                        //     'id_mensaje' => $data['entry'][0]['changes'][0]['value']['messages'][0]['id'],
                        //     'remitente' => $data['entry'][0]['changes'][0]['value']['contacts'][0]['wa_id'],
                        //     'mensaje' => $data['entry'][0]['changes'][0]['value']['messages'][0]['image']['id'],
                        //     'respuesta' => $responseImage,
                        //     'status' => 1,
                        //     'type' => 'image'
                        // ];
                        // ChatGpt::create( $dataRegistrarChat );
                        //Storage::disk('local')->put( 'image-'.$fileName.'.txt', json_encode($data) );
                        
                    }
                }
            }
        }else {
            
            $fileName = $this->descargarImageTemporal(null); // temporalWhatsapp/fileName.[jpg,png] obtenemos la ruta completa que esta en public
        }
        
        Storage::disk('local')->put('phone-Prueba.txt', json_encode($phone) );
        Storage::disk('local')->put('phone-mensaje.txt', json_encode($mensajeExiste) );

        if ($mensajeExiste == null) {

            $idMedia = $data['entry'][0]['changes'][0]['value']['messages'][0]['image']['id'];

            Storage::disk('local')->put('image-'.$idMedia.'.txt', json_encode($data) );

            $descargarImage = $this->descargarImage($idMedia);
            Storage::disk('publico')->put('nombreImagen.txt', $descargarImage );

            $responseImage = 'Gracias!! recuerda que soy una inteligencia artificial y que no puedo ver lo que me has enviado pero mi supervisora María lo verá en el horario de 09:00 a 18:00 de Lunes a viernes. Si es tu DNI o Pasaporte es suficiente con enviármelo a mi. Mi supervisora lo recibirá. Muchas gracias!!';
            $respuestaImage = $this->chatGptPruebasConImagen($descargarImage);
            Storage::disk('publico')->put('lecturaImagen-'.$idMedia.'.txt', $respuestaImage );

            // $respuestaWhatsapp = $this->contestarWhatsapp($phone, $responseImage);

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

    public function obtenerImage($imageId)
    {
        // Suponiendo que tienes una URL base para obtener imágenes
        // $url = "https://api.whatsapp.com/v1/media/{$imageId}";
        $url = "https://graph.facebook.com/v20.0/{$imageId}/";
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('TOKEN_WHATSAPP')
        ])->get($url);

        if ($response->successful()) {
            Storage::disk('local')->put('image-response-url-response.txt', $response );

            $mediaUrl = $response->json()['url'];
            return $mediaUrl;
        }

        return null;
    }

    public function descargarImageTemporal($imageId)
    {
        // URL base para obtener imágenes de WhatsApp
        $url = "https://graph.facebook.com/v20.0/{$imageId}";
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('TOKEN_WHATSAPP')
        ])->get($url);

        if ($response->successful()) {
            $mediaUrl = $response->json()['url'];

            // Descargar el archivo de medios
            $mediaResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('TOKEN_WHATSAPP')
            ])->get($mediaUrl);

            if ($mediaResponse->successful()) {
                $extension = explode('/', $mediaResponse->header('Content-Type'))[1];
                $filename = $imageId . '.' . $extension;
                Storage::disk('publico')->put('temporalWhatsapp/' . $filename, $mediaResponse->body());
                return 'temporalWhatsapp/'.$filename;
            }
        }

        return null;
    }


    public function descargarImage($imageId)
    {
        // URL base para obtener imágenes de WhatsApp
        $url = "https://graph.facebook.com/v20.0/{$imageId}";
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('TOKEN_WHATSAPP')
        ])->get($url);

        if ($response->successful()) {
            $mediaUrl = $response->json()['url'];

            // Descargar el archivo de medios
            $mediaResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('TOKEN_WHATSAPP')
            ])->get($mediaUrl);

            if ($mediaResponse->successful()) {
                $extension = explode('/', $mediaResponse->header('Content-Type'))[1];
                $filename = $imageId . '.' . $extension;
                Storage::disk('publico')->put('imagenesWhatsapp/' . $filename, $mediaResponse->body());
                return $filename;
            }
        }

        return null;
    }

    public function textMensaje( $data )
    {
        // Obtenemos la fecha actual de la peticion
        $fecha = Carbon::now()->format('Y-m-d_H-i-s');

        Storage::disk('local')->put('Mensaje_Texto_Reicibido-'.$fecha.'.txt', json_encode($data) );

        // Whatsapp::create(['mensaje' => json_encode($data)]);
        $id = $data['entry'][0]['changes'][0]['value']['messages'][0]['id'];
        $phone = $data['entry'][0]['changes'][0]['value']['messages'][0]['from'];
        $mensaje = $data['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'];

        $mensajeExiste = ChatGpt::where('id_mensaje', $id)->get();

        if (count($mensajeExiste) > 0) {

        }else {
            
            $isAveria = $this->chatGpModelo($mensaje);
            Storage::disk('local')->put( 'Contestacion del modelo-'.$fecha.'.txt', json_encode($isAveria) );

            if ($isAveria == 'NULL') {
                $dataRegistrar = [
                    'id_mensaje' => $id,
                    'id_three' => null,
                    'remitente' => $phone,
                    'mensaje' => $mensaje,
                    'respuesta' => null,
                    'status' => 1,
                    'status_mensaje' => null,
                    'type' => 'text',
                    'date' => Carbon::now()
                ];
                $mensajeCreado = ChatGpt::create($dataRegistrar);

                $cliente = Cliente::where('telefono', $phone)->first();
                $reserva = Reserva::where('cliente_id', $cliente->id)->first();

                if ($cliente == null) {
                    $mensajeAveria = 'Hola, lamento lo que nos indica, acabo de contactar con la persona responsable y le contactara en breve para darle una solucion. Muchas Gracias.';
                    $respuestaWhatsapp = $this->contestarWhatsapp($phone, $mensajeAveria);
                    $enviarMensajeLimpiadora = $this->mensajesPlantillaNull('Laura', $mensaje, $phone, '34622440984',  );

                    return response($mensajeAveria)->header('Content-Type', 'text/plain');
                }
                if ($reserva == null) {
                    $mensajeAveria = 'Hola, lamento lo que nos indica, acabo de contactar con la persona responsable y le contactara en breve para darle una solucion. Muchas Gracias.';
                    $respuestaWhatsapp = $this->contestarWhatsapp($phone, $mensajeAveria);
                    $enviarMensajeLimpiadora = $this->mensajesPlantillaNull('Laura', $mensaje, $phone, '34622440984',  );

                    return response($mensajeAveria)->header('Content-Type', 'text/plain');
                }

                foreach ($reserva->apartamento->titulo as $string) {
                    if (preg_match('/^(Edificio Hawkins(?: Costa)?)(.*)$/', $string, $matches)) {
                        //echo "Edificio: " . $matches[1] . "\n";
                        $edificio = trim($matches[1]);
                        $apartamento =trim($matches[2]);
                        //echo "Apartamento: " . trim($matches[2]) . "\n\n";
                    }
                }
                $mensajeAveria = 'Hemos procesado el mensaje a nuestra encargada de los apartamento, en el mayor tiempo posible se pondra en contacto con usted. Muchas gracias';
                $respuestaWhatsapp = $this->contestarWhatsapp($phone, $mensajeAveria);

                $enviarMensajeLimpiadora = $this->mensajesPlantillaLimpiadora($apartamento, $edificio, $phone, '34633065237', $mensaje );
                return response($mensajeAveria)->header('Content-Type', 'text/plain');
            } elseif ($isAveria == "TRUE") {
                $dataRegistrar = [
                    'id_mensaje' => $id,
                    'id_three' => null,
                    'remitente' => $phone,
                    'mensaje' => $mensaje,
                    'respuesta' => null,
                    'status' => 1,
                    'status_mensaje' => null,
                    'type' => 'text',
                    'date' => Carbon::now()
                ];
                $mensajeCreado = ChatGpt::create($dataRegistrar);

                $cliente = Cliente::where('telefono', $phone)->first();
                $reserva = Reserva::where('cliente_id', $cliente->id)->first();
                $manitas = Reparaciones::all();

                if ($cliente == null) {
                    $mensajeAveria = 'Hola, lamento lo que nos indica, acabo de contactar con la persona responsable y le contactara en breve para darle una solucion. Muchas Gracias.';
                    $respuestaWhatsapp = $this->contestarWhatsapp($phone, $mensajeAveria);

                    $enviarMensajeAverias = $this->mensajesPlantillaNull( $manitas[0]->nombre, $mensaje , $phone, $manitas[0]->telefono );

                    return response($mensajeAveria)->header('Content-Type', 'text/plain');
                }
                if ($reserva == null) {
                    $mensajeAveria = 'Hola, lamento lo que nos indica, acabo de contactar con la persona responsable y le contactara en breve para darle una solucion. Muchas Gracias.';
                    $respuestaWhatsapp = $this->contestarWhatsapp($phone, $mensajeAveria);
                    $enviarMensajeAverias = $this->mensajesPlantillaNull( $manitas[0]->nombre, $mensaje , $phone, $manitas[0]->telefono );

                    return response($mensajeAveria)->header('Content-Type', 'text/plain');
                }

                foreach ($reserva->apartamento->titulo as $string) {
                    if (preg_match('/^(Edificio Hawkins(?: Costa)?)(.*)$/', $string, $matches)) {
                        //echo "Edificio: " . $matches[1] . "\n";
                        $edificio = trim($matches[1]);
                        $apartamento =trim($matches[2]);
                        //echo "Apartamento: " . trim($matches[2]) . "\n\n";
                    }
                }
                $mensajeAveria = 'Hemos procesado un parte para solucionar el problemas que nos has descrito, en el mayor tiempo posible nuestro tecnico se pondra en contacto con usted. Muchas gracias';
                $respuestaWhatsapp = $this->contestarWhatsapp($phone, $mensajeAveria);
                // $manitas = Reparaciones::all();
                //$nombreManita, $apartamento, $edificio, $mensaje, $telefono, $telefonoManitas $manitas[0]->telefono
                $enviarMensajeAverias = $this->mensajesPlantillaAverias( $manitas[0]->nombre, $apartamento, $edificio, $mensaje , $phone, $manitas[0]->telefono );

                return response($mensajeAveria)->header('Content-Type', 'text/plain');

            } else {
                $mensajesAnteriores = ChatGpt::where('remitente', $phone)
                ->latest() // Asegura que el mensaje más reciente sea seleccionado
                ->first();

                if ($mensajesAnteriores == null) {
                    $dataRegistrar = [
                        'id_mensaje' => $id,
                        'id_three' => null,
                        'remitente' => $phone,
                        'mensaje' => $mensaje,
                        'respuesta' => null,
                        'status' => 1,
                        'status_mensaje' => null,
                        'type' => 'text',
                        'date' => Carbon::now()
                    ];
                } else {
                    $dataRegistrar = [
                        'id_mensaje' => $id,
                        'id_three' => $mensajesAnteriores->id_three,
                        'remitente' => $phone,
                        'mensaje' => $mensaje,
                        'respuesta' => null,
                        'status' => 1,
                        'status_mensaje' => null,
                        'type' => 'text',
                        'date' => Carbon::now()
                    ];

                }
                $mensajeCreado = ChatGpt::create($dataRegistrar);

                // Enviar la question al asistente
                $reponseChatGPT = $this->chatGpt($mensaje, $id, $phone, $mensajeCreado->id);
                //dd($reponseChatGPT);
                $respuestaWhatsapp = $this->contestarWhatsapp($phone, $reponseChatGPT);
    
                if(isset($respuestaWhatsapp['error'])){
                    dd($respuestaWhatsapp);
                };
    
                $mensajeCreado->update([
                    'respuesta'=> $reponseChatGPT
                ]);

                return response($reponseChatGPT)->header('Content-Type', 'text/plain');
            }
            

        }
    }

    public function envioAutoVoz(Request $request){
        
        $tipo = $request->tipo;

        // Leticia  y Saray

        if ($tipo == 1) {
            $manitas = Reparaciones::all();
            $mensaje = $request->mensaje;
            $phone = $request->phone;
            $enviarMensajeLimpiadora = $this->mensajesPlantillaNull( 'Leticia o Saray', $mensaje, $phone, '34633065237' );
            return response('Mensaje Enviado')->header('Content-Type', 'text/plain');

        } elseif ($tipo == 2){
            $manitas = Reparaciones::all();
            $mensaje = $request->mensaje;
            $phone = $request->phone;
            $enviarMensajeAverias = $this->mensajesPlantillaNull( $manitas[0]->nombre, $mensaje , $phone, $manitas[0]->telefono);
            return response('Mensaje Enviado')->header('Content-Type', 'text/plain');

        } elseif ($tipo == 3){
            $telefonos = [
                '34622440984',
                // '34664368232',
                // '34605621704'
            ];
            $origen = $request->origen;
            foreach ($telefonos as $key => $telefono) {
                $enviarMensajeAverias = $this->mensajesPlantillaAlerta( $telefono, $origen );
                # code...
            }
            return response('Mensaje Enviado')->header('Content-Type', 'text/plain');
        }
    }

    public function chatGpt($mensaje, $id, $phone = null, $idMensaje)
    {
        $existeHilo = ChatGpt::find($idMensaje);
		$mensajeAnterior = ChatGpt::where('remitente', $existeHilo->remitente)->get();
		
            if ($mensajeAnterior[1]->id_three == null) {
				//dd($existeHilo);
                $three_id = $this->crearHilo();
				//dd($three_id);
				$existeHilo->id_three = $three_id['id'];
                $existeHilo->save();
                $mensajeAnterior[1]->id_three = $three_id['id'];
                $mensajeAnterior[1]->save();
				//dd($existeHilo);
            } else {
                $three_id['id'] = $mensajeAnterior[1]->id_three;
				$existeHilo->id_three = $mensajeAnterior[1]->id_three;
                $existeHilo->save();
                $three_id['id'] = $existeHilo->id_three;
            }
                     
    
            $hilo = $this->mensajeHilo($three_id['id'], $mensaje);
            // Independientemente de si el hilo es nuevo o existente, inicia la ejecución
            $ejecuccion = $this->ejecutarHilo($three_id['id']);
            $ejecuccionStatus = $this->ejecutarHiloStatus($three_id['id'], $ejecuccion['id']);
            //dd($ejecuccionStatus);
            // Inicia un bucle para esperar hasta que el hilo se complete
            while (true) {
                //$ejecuccion = $this->ejecutarHilo($three_id['id']);

                if ($ejecuccionStatus['status'] === 'in_progress') {
                    // Espera activa antes de verificar el estado nuevamente
                    sleep(2); // Ajusta este valor según sea necesario

                    // Verifica el estado del paso actual del hilo
                    $pasosHilo = $this->ejecutarHiloISteeps($three_id['id'], $ejecuccion['id']);
                    if ($pasosHilo['data'][0]['status'] === 'completed') {
                        // Si el paso se completó, verifica el estado general del hilo
                        $ejecuccionStatus = $this->ejecutarHiloStatus($three_id['id'],$ejecuccion['id']);
                    }
                } elseif ($ejecuccionStatus['status'] === 'completed') {
                    // El hilo ha completado su ejecución, obtiene la respuesta final
                    $mensajes = $this->listarMensajes($three_id['id']);
                    //dd($mensajes);
                    if(count($mensajes['data']) > 0){
                        return $mensajes['data'][0]['content'][0]['text']['value'];
                    }
                } else {
                    // Maneja otros estados, por ejemplo, errores
                    //dd($ejecuccionStatus);
                    //return; // Sale del bucle si se encuentra un estado inesperado
                }
			}
    }

    public function crearHilo()
    {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        $url = 'https://api.openai.com/v1/threads';

        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer '. $token,
            "OpenAI-Beta: assistants=v1"
        );

        // Inicializar cURL y configurar las opciones
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // Ejecutar la solicitud y obtener la respuesta
        $response = curl_exec($curl);
        curl_close($curl);

        // Procesar la respuesta
        if ($response === false) {
            $response_data = json_decode($response, true);
            $error = [
            'status' => 'error',
            'messages' => 'Error al realizar la solicitud: '.$response_data
            ];
            return $error;

        } else {
            $response_data = json_decode($response, true);
            //Storage::disk('local')->put('Respuesta_Peticion_ChatGPT-'.$id.'.txt', $response );
            return $response_data;
        }
    }
    
    public function recuperarHilo($id_thread)
    {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        $url = 'https://api.openai.com/v1/threads/'.$id_thread;

        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer '. $token,
            "OpenAI-Beta: assistants=v1"
        );

        // Inicializar cURL y configurar las opciones
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
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

        } else {
            $response_data = json_decode($response, true);
            // Storage::disk('local')->put('Respuesta_Peticion_ChatGPT-'.$id.'.txt', $response );
            return $response_data;
        }
    }

    public function ejecutarHilo($id_thread){
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        $url = 'https://api.openai.com/v1/threads/'.$id_thread.'/runs';

        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer '. $token,
            "OpenAI-Beta: assistants=v1"
        );

        $body = [
            "assistant_id" => 'asst_zYokKNRE98fbjUsKpkSzmU9Y'
        ];
        // Inicializar cURL y configurar las opciones
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($body));

        // Ejecutar la solicitud y obtener la respuesta
        $response = curl_exec($curl);
        curl_close($curl);

        // Procesar la respuesta
        if ($response === false) {
            $error = [
            'status' => 'error',
            'messages' => 'Error al realizar la solicitud'
            ];

        } else {
            $response_data = json_decode($response, true);
            return $response_data;
        }
    }
    public function mensajeHilo($id_thread, $pregunta)
    {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        $url = 'https://api.openai.com/v1/threads/'.$id_thread.'/messages';

        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer '. $token,
            "OpenAI-Beta: assistants=v1"
        );
        $body = [
            "role" => "user",
            "content" => $pregunta
        ];

        // Inicializar cURL y configurar las opciones
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($body));


        // Ejecutar la solicitud y obtener la respuesta
        $response = curl_exec($curl);
        curl_close($curl);

        // Procesar la respuesta
        if ($response === false) {
            $response_data = json_decode($response, true);
            $error = [
            'status' => 'error',
            'messages' => 'Error al realizar la solicitud: '.$response_data
            ];
            return $error;

        } else {
            $response_data = json_decode($response, true);
            //Storage::disk('local')->put('Respuesta_Peticion_ChatGPT-'.$id.'.txt', $response );
            return $response_data;
        }
    }
    public function ejecutarHiloStatus($id_thread, $id_runs){
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        $url = 'https://api.openai.com/v1/threads/'. $id_thread .'/runs/'.$id_runs;

        $headers = array(
            'Authorization: Bearer '. $token,
            "OpenAI-Beta: assistants=v1"
        );

        // Inicializar cURL y configurar las opciones
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, false);
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

        } else {
            $response_data = json_decode($response, true);
            return $response_data;
        }
    }

    public function ejecutarHiloISteeps($id_thread, $id_runs)
    {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        $url = 'https://api.openai.com/v1/threads/'.$id_thread. '/runs/' .$id_runs. '/steps';

        $headers = array(
            'Authorization: Bearer '. $token,
            "OpenAI-Beta: assistants=v1"
        );

        // Inicializar cURL y configurar las opciones
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, false);
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

        } else {
            $response_data = json_decode($response, true);
            return $response_data;
        }
    }
    public function listarMensajes($id_thread)
    {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        $url = 'https://api.openai.com/v1/threads/'. $id_thread .'/messages';

        $headers = array(
            'Authorization: Bearer '. $token,
            "OpenAI-Beta: assistants=v1"
        );

        // Inicializar cURL y configurar las opciones
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // Ejecutar la solicitud y obtener la respuesta
        $response = curl_exec($curl);
        curl_close($curl);

        // Procesar la respuesta
        if( $response === false ){
            $error = [
            'status' => 'error',
            'messages' => 'Error al realizar la solicitud'
            ];

        } else {
            $response_data = json_decode( $response, true );
            return $response_data;
        }
    }

	function asegurarSignoInterrogacion( $string ) {
		// Comprueba si el último carácter es ?
		if ( substr( $string, -1 ) !== '?' ) {
			// Si no lo es, añade ? al final
			$string .= '?';
		}
		return $string;
	}

    public function contestarWhatsapp($phone, $texto) {
        $token = env('TOKEN_WHATSAPP', 'valorPorDefecto');
    
        // Construir la carga útil como un array en lugar de un string JSON
        $mensajePersonalizado = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $phone,
            "type" => "text",
            "text" => [
                "body" => $texto
            ]
        ];
    
        $urlMensajes = 'https://graph.facebook.com/v16.0/102360642838173/messages';
    
        $curl = curl_init();
    
        curl_setopt_array($curl, [
            CURLOPT_URL => $urlMensajes,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($mensajePersonalizado),  // Asegúrate de que mensajePersonalizado sea un array
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
        ]);
    
        $response = curl_exec($curl);
        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            Log::error("Error en cURL al enviar mensaje de WhatsApp: " . $error);
            return ['error' => $error];
        }
        curl_close($curl);
    
        try {
            $responseJson = json_decode($response, true);
            Storage::disk('local')->put("Respuesta_Envio_Whatsapp-{$phone}.txt", $response);
            return $responseJson;
        } catch (\Exception $e) {
            Log::error("Error al guardar la respuesta de WhatsApp: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }



    // public function chatGptPruebasConImagen($imagenFilename)
    // {
    //     $token = env('TOKEN_OPENAI', 'valorPorDefecto');
    
    //     // Cargar los JSON de paises y tipos desde la carpeta pública
    //     $paisesFilePath = public_path('paises.json');
    //     $tiposFilePath = public_path('tipos.json');
    
    //     $paisesData = json_decode(file_get_contents($paisesFilePath), true);
    //     $tiposData = json_decode(file_get_contents($tiposFilePath), true);
    
    //     // Leer la imagen y convertirla a base64
    //     $imagePath = public_path('imagenesWhatsapp/' . $imagenFilename);
    //     if (file_exists($imagePath)) {
    //         $imageData = file_get_contents($imagePath);
    //         $imageBase64 = 'data:image/jpeg;base64,' . base64_encode($imageData); // Cambia 'image/jpeg' según el formato de la imagen
    //     } else {
    //         return response()->json(['error' => 'La imagen no se encuentra.']);
    //     }
    
    //     // Convertir los datos de países y tipos a texto JSON
    //     $paisesJsonText = json_encode($paisesData);
    //     $tiposJsonText = json_encode($tiposData);
    
    //     // Configurar los parámetros de la solicitud
    //     $url = 'https://api.openai.com/v1/chat/completions';
    //     $headers = array(
    //         'Authorization: Bearer ' . $token,
    //         'Content-Type: application/json'
    //     );
    
    //     // Construir el contenido del mensaje que incluye la imagen en base64, paises y tipos de documento como texto
    //     $data = array(
    //         "model" => "gpt-4o",
    //         "messages" => [
    //             [
    //                 "role" => "user",
    //                 "content" => [
    //                     [
    //                         "type" => "text",
    //                         "text" => "Analiza esta imagen y dime si es un DNI o pasaporte. Devuélveme solo un JSON con esta estructura: {isDni: true/false, isPasaporte: true/false, informacion: {nombre, apellido, fecha de nacimiento, fecha de expedicion, localidad, pais, numero de dni o pasaporte, value, isEuropean, mensaje}. En mensaje debes colocar tu respuesta para poder contestar al cliente, Aquí tienes información adicional sobre países y tipos de documentos:"
    //                     ],
    //                     [
    //                         "type" => "text",
    //                         "text" => "Paises: " . $paisesJsonText
    //                     ],
    //                     [
    //                         "type" => "text",
    //                         "text" => "Tipos: " . $tiposJsonText
    //                     ],
    //                     [
    //                         "type" => "image_url",
    //                         "image_url" => [
    //                             "url" => $imageBase64
    //                         ]
    //                     ]
    //                 ]
    //             ]
    //         ]
    //     );
    
    //     // Inicializar cURL y configurar las opciones
    //     $curl = curl_init();
    //     curl_setopt($curl, CURLOPT_URL, $url);
    //     curl_setopt($curl, CURLOPT_POST, true);
    //     curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    //     curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    //     // Ejecutar la solicitud y obtener la respuesta
    //     $response = curl_exec($curl);
    //     curl_close($curl);
    
    //     // Guardar la respuesta en un archivo para depuración
    //     //Storage::disk('local')->put('RespuestaImagenChat.txt', $response);
    
    //     // Decodificar la respuesta JSON
    //     $response_data = json_decode($response, true);
    
    //     // Si ocurre un error, devolver una respuesta de error
    //     if ($response === false) {
    //         $error = [
    //             'status' => 'error',
    //             'message' => 'Error al realizar la solicitud'
    //         ];
    //         //Storage::disk('local')->put('errorChat.txt', $error['message']);
    //         return response()->json($error);
    //     } else {
    //         // Guardar la respuesta para seguimiento
    //         $responseReturn = [
    //             'status' => 'ok',
    //             'message' => $response_data
    //         ];
    //         //Storage::disk('local')->put('respuestaFuncionChat.txt', json_encode($responseReturn));
    
    //         // Retornar la respuesta decodificada
    //         return $response_data;
    //     }
    // }
    
    public function chatGptPruebasConImagen($imagenFilename)
    {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
    
        // Cargar los JSON de paises y tipos desde la carpeta pública
        $paisesFilePath = public_path('paises.json');
        $tiposFilePath = public_path('tipos.json');
    
        $paisesData = json_decode(file_get_contents($paisesFilePath), true);
        $tiposData = json_decode(file_get_contents($tiposFilePath), true);
    
        // Leer la imagen y convertirla a base64
        $imagePath = public_path('imagenesWhatsapp/' . $imagenFilename);
        if (file_exists($imagePath)) {
            $imageData = file_get_contents($imagePath);
            $imageBase64 = 'data:image/jpeg;base64,' . base64_encode($imageData);
        } else {
            return response()->json(['error' => 'La imagen no se encuentra.']);
        }
    
        // Convertir los datos de países y tipos a texto JSON
        $paisesJsonText = json_encode($paisesData);
        $tiposJsonText = json_encode($tiposData);
    
        // Configurar los parámetros de la solicitud
        $url = 'https://api.openai.com/v1/chat/completions';
        $headers = array(
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        );
    
        // Construir el contenido del mensaje que incluye la imagen en base64, paises y tipos de documento como texto
        $data = array(
            "model" => "gpt-4o",
            "messages" => [
                [
                    "role" => "user",
                    "content" => [
                        [
                            "type" => "text",
                            "text" => "Analiza esta imagen y dime si es un DNI o pasaporte. Devuélveme solo un JSON con esta estructura: {isDni: true/false, isPasaporte: true/false, informacion: {nombre, apellido1, apellido2, fechaNacimiento, fechaExpedicionDoc, pais, numIdentificacion, value, isEuropean, mensaje}. En mensaje debes colocar tu respuesta para poder contestar al cliente. Aquí tienes información adicional sobre países y tipos de documentos:"
                        ],
                        [
                            "type" => "text",
                            "text" => "Paises: " . $paisesJsonText
                        ],
                        [
                            "type" => "text",
                            "text" => "Tipos: " . $tiposJsonText
                        ],
                        [
                            "type" => "image_url",
                            "image_url" => [
                                "url" => $imageBase64
                            ]
                        ]
                    ]
                ]
            ]
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
    
        // Decodificar la respuesta JSON
        $response_data = json_decode($response, true);
    
        // Si ocurre un error, devolver una respuesta de error
        if ($response === false) {
            return response()->json(['status' => 'error', 'message' => 'Error al realizar la solicitud']);
        }
    
        // Procesar la respuesta para ajustar los campos adicionales
        if (!empty($response_data)) {
            $informacion = $response_data['informacion'] ?? [];
    
            // Agregar campos adicionales basados en los datos JSON y la lógica que mencionas:
            
            // Buscar la nacionalidad en el JSON de países
            $pais = $informacion['pais'] ?? '';
            if (!empty($paisesData[$pais])) {
                $informacion['nacionalidadStr'] = $pais;
                $informacion['nacionalidad'] = $paisesData[$pais]['value'] ?? '';
                $informacion['isEuropean'] = $paisesData[$pais]['isEuropean'] ?? false;
            }
    
            // Buscar el tipo de documento en el JSON de tipos de documento
            $tipoDocumento = $informacion['tipoDocumento'] ?? '';
            foreach ($tiposData as $tipo) {
                if ($tipo['codigo'] == $tipoDocumento) {
                    $informacion['tipoDocumentoStr'] = $tipo['descripcion'];
                    break;
                }
            }
    
            // Validar el sexo y convertirlo a "M" o "F"
            $sexo = $informacion['sexo'] ?? '';
            $informacion['sexoStr'] = ($sexo == 'Masculino') ? 'M' : 'F';
    
            // Procesar fecha de nacimiento (día, mes, año)
            if (!empty($informacion['fechaNacimiento'])) {
                $fecha = explode(' ', $informacion['fechaNacimiento']);
                if (count($fecha) === 3) {
                    $informacion['dia'] = $fecha[0];
                    $informacion['mes'] = $fecha[1];
                    $informacion['ano'] = $fecha[2];
                }
            }
    
            // Retornar la respuesta procesada
            return [
                'isDni' => $response_data['isDni'] ?? false,
                'isPasaporte' => $response_data['isPasaporte'] ?? false,
                'informacion' => $informacion
            ];
        }
    
        return response()->json(['status' => 'error', 'message' => 'No se recibió respuesta válida.']);
    }
    
    public function obtenerStringDNI($tipo)
    {
        switch ($tipo) {
            case 'D':
                return "DNI";
            case 'C':
                return "PERMISO CONDUCIR ESPAÑOL";
            case 'X':
                return "PERMISO DE RESIDENCIA DE ESTADO MIEMBRO DE LA UE";
            case 'N':
                return "NIE O TARJETA ESPAÑOLA DE EXTRANJEROS";
            case 'I':
                return "CARTA DE IDENTIDAD EXTRANJERA";
            case 'P':
                return "PASAPORTE";
            default:
                return "Desconocido";
        }
    }
    



    // public function chatGptPruebasConImagen($imagenFilename) {
    //     $token = env('TOKEN_OPENAI', 'valorPorDefecto');
       
    //     // Configurar los parámetros de la solicitud
    //     $url = 'https://api.openai.com/v1/chat/completions';
    //     $headers = array(
    //         'Authorization: Bearer ' . $token,
    //         'Content-Type: application/json'
    //     );

    //     // Construir la URL completa de la imagen
    //     $imageUrl = 'https://crm.apartamentosalgeciras.com/imagenesWhatsapp/' . $imagenFilename;

    //     $data = array(
    //         "model" => "gpt-4o",
    //         "messages" => [
    //             [
    //                 "role" => "user",
    //                 "content" => [
    //                     [
    //                         "type" => "text",
    //                         "text" => "Analiza esta imagen y dime si es un dni o pasaporte, hazme la contestacion devolviendome solo un JSON, donde tenga la sigueinte estructura: {isDni: true o false, isPasaporte: true o false (dependiendo si es un dni o pasaporte lo que te envie, si es un dni o pasaporte entonces agregamos otra propiedades), informacion: { nombre, apellido,fecha de nacimiento, fecha de expedicion, localidad, pais, numero de dni o pasaporte }, si no es dni o pasaporte esa dos propiedades de isDni o isPasaporte deben venir false."
    //                     ],
    //                     [
    //                         "type" => "image_url",
    //                         "image_url" => [
    //                             "url" => $imageUrl
    //                         ]
    //                     ]
    //                 ]
    //             ]
    //         ]
    //     );

    //     // Inicializar cURL y configurar las opciones
    //     $curl = curl_init();
    //     curl_setopt($curl, CURLOPT_URL, $url);
    //     curl_setopt($curl, CURLOPT_POST, true);
    //     curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    //     curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    //     // Ejecutar la solicitud y obtener la respuesta
    //     $response = curl_exec($curl);
    //     curl_close($curl);
    //     Storage::disk('local')->put('REspuestaImagenChat.txt', json_encode($response));
    //     $response_data = json_decode($response, true);
    //     return response()->json($response_data);
    //     // Procesar la respuesta
    //     if ($response === false) {
    //         $error = [
    //             'status' => 'error',
    //             'messages' => 'Error al realizar la solicitud'
    //         ];
    //         Storage::disk('local')->put('errorChapt.txt', $error['messages']);

    //         return response()->json($error);
    //     } else {
    //         $response_data = json_decode($response, true);
    //         $responseReturn = [
    //             'status' => 'ok',
    //             'messages' => $response_data['choices'][0]['text']
    //         ];
    //         Storage::disk('local')->put('respuestaFuncionChapt.txt', $responseReturn['messages']);

    //         return response()->json($response_data);
    //     }
        

    // }

    public function chatGpModelo( $texto ) {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        // Configurar los parámetros de la solicitud
        $url = 'https://api.openai.com/v1/chat/completions';
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer '. $token
        );

        $fecha = Carbon::now()->format('Y-m-d_H-i-s');

        $data = array(
            "model" => "gpt-4o",
            "messages" => [
                [
                    "role" => "user",
                    "content" => [
                        [
                            "type" => "text",
                            "text" => 'Analiza el contenido del mensaje recibido:
                            1. Si el mensaje contiene quejas sobre averías, fallos, roturas o mal funcionamiento (excluyendo problemas con WiFi y claves de acceso al apartamento):
                            - Devuelve "TRUE".

                            2. Si el mensaje es sobre problemas con WiFi o claves de acceso al apartamento:
                            - Devuelve "FALSE".

                            3. Si el mensaje trata sobre la limpieza o los suministros del apartamento (tales como toallas, papel higiénico, champú, etc.) y se refiere a incidencias (no ubicación o deseos de servicios adicionales):
                            - Devuelve "NULL".

                            4. Si el mensaje pregunta por la ubicación de los suministros o desea información sobre servicios adicionales de limpieza (por ejemplo, precios o solicitud de limpieza extra):
                            - Devuelve "FALSE".

                            5. Si el mensaje no está relacionado con ninguno de los temas anteriores:
                            - Devuelve "FALSE".

                            Recuerda: La respuesta debe ser "TRUE", "FALSE" o "NULL" en mayúsculas. No incluyas ningún otro tipo de respuesta.
                            
                            Este es el mensaje: ' . $texto
                            
                        ]
                    ]
                ]
            ]
        );
        Storage::disk('local')->put('Justo antes de enviar al modelo'.$fecha.'.txt', json_encode($texto) );

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
        $response_data = json_decode($response, true);
        Storage::disk('local')->put('respuestaFuncionChaptParaReparaciones.txt', $response );

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
            'messages' => trim($response_data['choices'][0]['message']['content'])
            ];
            Storage::disk('local')->put('respuestaFuncionChaptParaReparaciones.txt', $response_data['choices'][0]['message']['content'] );

            return $response_data['choices'][0]['message']['content'];
        }
    }
    
    public function chatGptPruebas( $texto ) {
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

    function limpiarNumeroTelefono( $numero ) {
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
    public function mensajesPlantillaLimpiadora($apartamento, $edificio, $mensaje, $telefono, $telefonoLimpiadora, $idioma = 'es'){
        $token = env('TOKEN_WHATSAPP', 'valorPorDefecto');

        $mensajePersonalizado = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $telefonoLimpiadora,
            "type" => "template",
            "template" => [
                "name" => '',
                "language" => ["code" => $idioma],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            ["type" => "text", "text" => $apartamento],
                            ["type" => "text", "text" => $edificio],
                            ["type" => "text", "text" => $mensaje],
                            ["type" => "text", "text" => $telefono],
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
    public function mensajesPlantillaAverias($nombreManita, $apartamento, $edificio, $mensaje, $telefono, $telefonoManitas, $idioma = 'es'){
        $token = env('TOKEN_WHATSAPP', 'valorPorDefecto');

        $mensajePersonalizado = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $telefonoManitas,
            "type" => "template",
            "template" => [
                "name" => 'reparaciones',
                "language" => ["code" => $idioma],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            ["type" => "text", "text" => $nombreManita],
                            ["type" => "text", "text" => $apartamento],
                            ["type" => "text", "text" => $edificio],
                            ["type" => "text", "text" => $mensaje],
                            ["type" => "text", "text" => $telefono],
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
    public function mensajesPlantillaNull($nombre, $mensaje, $telefono, $telefonoManitas, $idioma = 'es'){
        $token = env('TOKEN_WHATSAPP', 'valorPorDefecto');

        $mensajePersonalizado = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $telefonoManitas,
            "type" => "template",
            "template" => [
                "name" => 'reparaciones_null',
                "language" => ["code" => $idioma],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            ["type" => "text", "text" => $nombre],
                            ["type" => "text", "text" => $mensaje],
                            ["type" => "text", "text" => $telefono],
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
    public function mensajesPlantillaAlerta($telefonoManitas, $origen, $idioma = 'en'){
        $token = env('TOKEN_WHATSAPP', 'valorPorDefecto');

        $mensajePersonalizado = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $telefonoManitas,
            "type" => "template",
            "template" => [
                "name" => 'averias_scrapping',
                "language" => ["code" => $idioma],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            ["type" => "text", "text" => $origen],
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

    // Vista de los mensajes
    public function whatsapp()
    {
        // $mensajes = ChatGpt::all();
        $mensajes = ChatGpt::orderBy('created_at', 'desc')->get();
        $resultado = [];
        foreach ($mensajes as $elemento) {

            //$remitenteSinPrefijo = (substr($elemento['remitente'], 0, 2) == "34") ? substr($elemento['remitente'], 2) : $elemento['remitente'];

			$remitenteSinPrefijo =$elemento['remitente'];
            // Busca el cliente cuyo teléfono coincide con el remitente del mensaje.
            $cliente = Cliente::where('telefono', '+'.$remitenteSinPrefijo)->first();

            // Si se encontró un cliente, añade su nombre al elemento del mensaje.
            if ($cliente) {
				if($cliente->nombre != ''){
                $elemento['nombre_remitente'] = $cliente->nombre . ' ' . $cliente->apellido1;
				}else {
					$elemento['nombre_remitente'] = $cliente->alias;
				}
            } else {
                // Si no se encuentra el cliente, puedes optar por dejar el campo vacío o asignar un valor predeterminado.
                $elemento['nombre_remitente'] = 'Desconocido';
            }

            $resultado[$elemento['remitente']][] = $elemento;


        }
        // dd($resultado);

        // var_dump(var_export($result, true));
        return view('whatsapp.index', compact('resultado'));
    }

}
