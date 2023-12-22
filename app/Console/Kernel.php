<?php

namespace App\Console;

use App\Models\Cliente;
use App\Models\MensajeAuto;
use App\Models\Reserva;
use Carbon\Carbon;
use App\Services\ClienteService;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use DateTime;

class Kernel extends ConsoleKernel
{
   
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        // $clienteService = app(ClienteService::class);

        // // Miramos si el cliente tiene la Nacionalidad e idioma
        $schedule->call(function (ClienteService $clienteService) {
            // Obtener la fecha de hoy
            $hoy = Carbon::now();
            // Obtenemos la reservas que sean igual o superior a la fecha de entrada de hoy y no tengan el DNI Enrtegado.
            $reservasEntrada = Reserva::where('dni_entregado', null)
            ->where('estado_id', 1)
            // ->where('fecha_entrada', '>=', $hoy->toDateString())
            ->get();

            foreach($reservasEntrada as $reserva){
                $resultado = $clienteService->getIdiomaClienteID($reserva->cliente_id);
            }
            Log::info("Tarea programada de Nacionalidad del cliente ejecutada con éxito.");
        })->everyMinute();

        // Tarea par enviar el mensaje del Dni
        $schedule->call(function (ClienteService $clienteService) {
            // Obtener la fecha de hoy
            $hoy = Carbon::now();
            // Obtener la fecha de dos días después
            $dosDiasDespues = Carbon::now()->addDays(2)->format('Y-m-d');
            $hoyFormateado = Carbon::now()->format('Y-m-d');

            // Modificar la consulta para obtener reservas desde hoy hasta dentro de dos días
            $reservasEntrada = Reserva::where('dni_entregado', null)
            ->where('estado_id', 1)
            ->whereDate('fecha_entrada', '>=', now())
            ->get();
            
            // $reservasEntrada = Reserva::whereBetween('fecha_entrada', [date('Y-m-d'), $dosDiasDespues])
            // ->where('estado_id', 1)
            // ->get();


            /*  MENSAJES TEMPLATE:
                    - dni
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
            */
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
                        Storage::disk('local')->put('reserva.txt', $reserva );

                        $mensaje = 'https://crm.apartamentosalgeciras.com/dni-user/'.$token;
                        $phoneCliente =  $this->limpiarNumeroTelefono($reserva->cliente->telefono);
                        $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente->nacionalidad);
                        $enviarMensaje = $this->mensajesAutomaticosBoton('dni', $token , $phoneCliente, $idiomaCliente );

                       // $enviarMensaje = $this->contestarWhatsapp($phoneCliente, $mensaje);
                        // return $enviarMensaje;
                        Storage::disk('local')->put('enviaMensaje.txt', $enviarMensaje );

                        // Data para guardar Mensaje enviado
                        $dataMensaje = [
                            'reserva_id' => $reserva->id,
                            'cliente_id' => $reserva->cliente_id,
                            'categoria_id' => 1,
                            'fecha_envio' => Carbon::now()
                        ];

                        MensajeAuto::create($dataMensaje);                    
                    } else {
                        if ($reserva->dni_entregado == null) {
                            if ($reserva->fecha_entrada == $hoyFormateado) {
                                // Obtenemos el token ya creado
                                $token = $reserva->token;
                                // Limpiamos el numero de telefono
                                $phoneCliente =  $this->limpiarNumeroTelefono($reserva->cliente->telefono);
                                // Enviamos el mensaje
                                $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente->nacionalidad);
    
                                $enviarMensaje = $this->mensajesAutomaticos('dni', $token , $phoneCliente, $idiomaCliente );
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
                        
                    }
                }
                
            }
            Log::info("Tarea programada de Nacionalidad del cliente ejecutada con éxito.");
        })->everyMinute();

         // Tarea par enviar los mensajes automatizados cuando se ha entregado el DNI
         $schedule->call(function (ClienteService $clienteService) {
            // Obtener la fecha de hoy
            $hoy = Carbon::now();
           
            $reservas = Reserva::whereDate('fecha_entrada', '=', date('Y-m-d'))->where('dni_entregado', '!=', null)->get();
            $codigoPuertaPrincipal = '1314#';

            foreach($reservas as $reserva){
                // Fecha de Hoy
                $FechaHoy = new \DateTime();
                // Formatea la fecha actual a una cadena 'Y-m-d'
                $fechaHoyStr = $FechaHoy->format('Y-m-d');

                // Horas objetivo para lanzar mensajes
                $horaObjetivoBienvenida = new \DateTime($fechaHoyStr . ' 11:00:00');
                $horaObjetivoCodigo = new \DateTime($fechaHoyStr . ' 12:00:00');
                $horaObjetivoConsulta = new \DateTime($fechaHoyStr . ' 16:00:00');
                $horaObjetivoOcio = new \DateTime($fechaHoyStr . ' 18:00:00');
                $horaObjetivoDespedida = new \DateTime($fechaHoyStr . '12:00:00');

                // Diferencias horarias para las horas objetivos
                $diferenciasHoraBienvenida = $hoy->diff($horaObjetivoBienvenida)->format('%R%H%I');
                $diferenciasHoraCodigos = $hoy->diff($horaObjetivoCodigo)->format('%R%H%I');
                $diferenciasHoraConsulta = $hoy->diff($horaObjetivoConsulta)->format('%R%H%I');
                $diferenciasHoraOcio = $hoy->diff($horaObjetivoOcio)->format('%R%H%I');
                $diferenciasHoraDespedida = $hoy->diff($horaObjetivoDespedida)->format('%R%H%I');

                // Comprobacion de los mensajes enviados automaticamente
                $mensajeBienvenida = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 4)->first();
                $mensajeClaves = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 3)->first();
                $mensajeConsulta = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 5)->first();
                $mensajeOcio = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 6)->first();
                $mensajeDespedida = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 7)->first();

                if ($diferenciasHoraBienvenida <= 0 && $mensajeBienvenida == null) {

                    // Obtenemos codigo de idioma
                    $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente->nacionalidad);
                    // Enviamos el mensaje
                    $data = $this->bienvenidoMensaje($reserva->cliente->nombre, $reserva->cliente->telefono, $idiomaCliente );

                    // Creamos la data para guardar el mensaje
                    $dataMensaje = [
                        'reserva_id' => $reserva->id,
                        'cliente_id' => $reserva->cliente_id,
                        'categoria_id' => 4,
                        'fecha_envio' => Carbon::now()
                    ];
                    // Creamos el mensaje
                    MensajeAuto::create($dataMensaje);
                }

                if ($diferenciasHoraCodigos <= 0 && $mensajeBienvenida != null && $mensajeClaves == null) {
                    $tiempoDesdeBienvenida = $mensajeBienvenida->created_at->diffInMinutes(Carbon::now());
                    if ($tiempoDesdeBienvenida >= 1) {
                        // Obtenemos el codigo de entrada del apartamento
                        $code = $this->codigoApartamento($reserva->apartamento_id);
                        // Obtenemos codigo de idioma
                        $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente->nacionalidad);
                        // Enviamos el mensaje
                        $data = $this->clavesMensaje($reserva->cliente->nombre, $code['nombre'], $codigoPuertaPrincipal, $code['codigo'], $reserva->cliente->telefono, $idiomaCliente );

                        // Creamos la data para guardar el mensaje
                        $dataMensaje = [
                            'reserva_id' => $reserva->id,
                            'cliente_id' => $reserva->cliente_id,
                            'categoria_id' => 3,
                            'fecha_envio' => Carbon::now()
                        ];
                        // Creamos el mensaje
                        MensajeAuto::create($dataMensaje);
                    }   
                }

                if ($diferenciasHoraConsulta <= 0 && $mensajeClaves != null && $mensajeConsulta == null) {
                    $tiempoDesdeClaves = $mensajeClaves->created_at->diffInMinutes(Carbon::now());
                    if ($tiempoDesdeClaves >= 1) {
                        // Obtenemos codigo de idioma
                        $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente->nacionalidad);
                        // Enviamos el mensaje
                        $data = $this->consultaMensaje($reserva->cliente->nombre, $reserva->cliente->telefono, $idiomaCliente );
        
                        // Creamos la data para guardar el mensaje
                        $dataMensaje = [
                            'reserva_id' => $reserva->id,
                            'cliente_id' => $reserva->cliente_id,
                            'categoria_id' => 5,
                            'fecha_envio' => Carbon::now()
                        ];
                        // Creamos el mensaje
                        MensajeAuto::create($dataMensaje);
                    }
                }

                if ($diferenciasHoraOcio <= 0 && $mensajeConsulta != null && $mensajeOcio == null) {
                    $tiempoDesdeConsulta = $mensajeClaves->created_at->diffInMinutes(Carbon::now());
                    if ($tiempoDesdeConsulta >= 1) {
                        // Obtenemos codigo de idioma
                        $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente->nacionalidad);
                        // Enviamos el mensaje
                        $data = $this->ocioMensaje($reserva->cliente->nombre, $reserva->cliente->telefono, $idiomaCliente);
        
                        // Creamos la data para guardar el mensaje
                        $dataMensaje = [
                            'reserva_id' => $reserva->id,
                            'cliente_id' => $reserva->cliente_id,
                            'categoria_id' => 6,
                            'fecha_envio' => Carbon::now()
                        ];
                        // Creamos el mensaje
                        MensajeAuto::create($dataMensaje);
                    }
                }
                
                // if ($diferenciasHoraDespedida <= 0 && $mensajeOcio != null && $mensajeDespedida == null) {
                //     $tiempoDesdeOcio = $mensajeOcio->created_at->diffInMinutes(Carbon::now());
                //     if ($tiempoDesdeOcio >= 1) {
                //        // Obtenemos codigo de idioma
                //         $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente->nacionalidad);
                //         // Enviamos el mensaje
                //         $data = $this->despedidaMensaje($reserva->cliente->nombre, $reserva->cliente->telefono, $idiomaCliente);

                //         // Creamos la data para guardar el mensaje
                //         $dataMensaje = [
                //             'reserva_id' => $reserva->id,
                //             'cliente_id' => $reserva->cliente_id,
                //             'categoria_id' => 7,
                //             'fecha_envio' => Carbon::now()
                //         ];
                //         // Creamos el mensaje
                //         MensajeAuto::create($dataMensaje);
                //     }
                    
                // }
            }

            Log::info("Tarea programada de Nacionalidad del cliente ejecutada con éxito.");
        })->everyMinute();


        // Tarea par enviar los mensajes despedida cuando se ha entregado el DNI
        $schedule->call(function (ClienteService $clienteService) {
            // Obtener la fecha de hoy
            $hoy = Carbon::now();
            
            $reservas = Reserva::whereDate('fecha_salida', '=', date('Y-m-d'))->where('dni_entregado', '!=', null)->get();

            foreach($reservas as $reserva){
                // Fecha de Hoy
                $FechaHoy = new \DateTime();
                // Formatea la fecha actual a una cadena 'Y-m-d'
                $fechaHoyStr = $FechaHoy->format('Y-m-d');

                // Horas objetivo para lanzar mensajes

                $horaObjetivoDespedida = new \DateTime($fechaHoyStr . '12:00:00');

                // Diferencias horarias para las horas objetivos

                $diferenciasHoraDespedida = $hoy->diff($horaObjetivoDespedida)->format('%R%H%I');

                // Comprobacion de los mensajes enviados automaticamente

                $mensajeDespedida = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 7)->first();
                
                if ($diferenciasHoraDespedida <= 0 && $mensajeDespedida == null) {
                        // Obtenemos codigo de idioma
                        $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente->nacionalidad);
                        // Enviamos el mensaje
                        $data = $this->despedidaMensaje($reserva->cliente->nombre, $reserva->cliente->telefono, $idiomaCliente);

                        // Creamos la data para guardar el mensaje
                        $dataMensaje = [
                            'reserva_id' => $reserva->id,
                            'cliente_id' => $reserva->cliente_id,
                            'categoria_id' => 7,
                            'fecha_envio' => Carbon::now()
                        ];
                        // Creamos el mensaje
                        MensajeAuto::create($dataMensaje);
                    
                }
            }
        
            Log::info("Tarea programada de Nacionalidad del cliente ejecutada con éxito.");
        })->everyMinute();

        // Tarea añadir a webpol
        $schedule->call(function () {
            $reservas = Reserva::where('dni_entregado', true)->where('enviado_webpol', null)->get();
            if (count($reservas) > 0) {
                foreach($reservas as $reserva){

                }
            }
            

            Log::info("Tarea programada de webpol del cliente ejecutada con éxito.");
        })->everyMinute();

         // $schedule->call(function () {

        //     Log::info("Tarea programada de Nacionalidad del cliente ejecutada con éxito.");
        // })->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    function limpiarNumeroTelefono($numero) {
        // Eliminar el signo más y cualquier espacio
        $numeroLimpio = preg_replace('/\+|\s+/', '', $numero);
    
        return $numeroLimpio;
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

    public function mensajesAutomaticosBoton($template, $token, $telefono, $idioma = 'en'){
        $tokenEnv = env('TOKEN_WHATSAPP', 'valorPorDefecto');

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
                        "type" => "button",
                        "sub_type" => "url",
                        "index" => 0,
                        "parameters" => [
                            ["type" => "text", "text" => $token]
                        ]
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
                'Authorization: Bearer '.$tokenEnv
            ),
        
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        // $responseJson = json_decode($response);
        return $response;
    }

    public function codigoApartamento($habitacion){
        switch ($habitacion) {
            case 1:
                return [
                        'nombre' => 'ATICO',
                        'codigo' => '0807'
                    ];
                break;
    
            case 2:
                return [
                    'nombre' => '2A',
                    'codigo' => '5032'
                ];
                break;
    
            case 3:
                return [
                    'nombre' => '2B',
                    'codigo' => '6032'
                ];
                break;
    
            case 4:
                return [
                    'nombre' => '1A',
                    'codigo' => '3032'
                ];
                break;
    
            case 5:
                return [
                    'nombre' => '1B',
                    'codigo' => '4032'
                ];
                break;
    
            case 6:
                return [
                    'nombre' => 'BA',
                    'codigo' => '1032'
                ];
                break;
    
            case 7:
                return [
                    'nombre' => 'BB',
                    'codigo' => '2032'
                ];
                break;
            
            default:
            return [
                'nombre' => 'Error',
                'codigo' => '0000'
            ];
                break;
        }
    }

    public function mensajesAutomaticos($template, $nombre, $telefono, $idioma = 'en'){
        $tokenEnv = env('TOKEN_WHATSAPP', 'valorPorDefecto');

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
                'Authorization: Bearer '.$tokenEnv
            ),
        
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        // $responseJson = json_decode($response);
        return $response;

    }
    

    public function bienvenidoMensaje($nombre, $telefono, $idioma = 'en'){
        $tokenEnv = env('TOKEN_WHATSAPP', 'valorPorDefecto');

        $mensajePersonalizado = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $telefono,
            "type" => "template",
            "template" => [
                "name" => 'bienvenido',
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
                'Authorization: Bearer '.$tokenEnv
            ),
        
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        // $responseJson = json_decode($response);
        return $response;
    }

    public function clavesMensaje($nombre, $apartamento, $puertaPrincipal, $codigoApartamento, $telefono, $idioma = 'en'){
        $tokenEnv = env('TOKEN_WHATSAPP', 'valorPorDefecto');

        $mensajePersonalizado = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $telefono,
            "type" => "template",
            "template" => [
                "name" => 'codigos',
                "language" => ["code" => $idioma],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            ["type" => "text", "text" => $nombre],
                            ["type" => "text", "text" => $apartamento],
                            ["type" => "text", "text" => $puertaPrincipal],
                            ["type" => "text", "text" => $codigoApartamento]
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
                'Authorization: Bearer '.$tokenEnv
            ),
        
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        // $responseJson = json_decode($response);
        return $response;
    }

    public function consultaMensaje($nombre, $telefono, $idioma = 'en'){
        $tokenEnv = env('TOKEN_WHATSAPP', 'valorPorDefecto');

        $mensajePersonalizado = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $telefono,
            "type" => "template",
            "template" => [
                "name" => 'consulta',
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
                'Authorization: Bearer '.$tokenEnv
            ),
        
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        // $responseJson = json_decode($response);
        return $response;
    }

    public function despedidaMensaje($nombre, $telefono, $idioma = 'en'){
        $tokenEnv = env('TOKEN_WHATSAPP', 'valorPorDefecto');

        $mensajePersonalizado = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $telefono,
            "type" => "template",
            "template" => [
                "name" => 'despedida',
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
                'Authorization: Bearer '.$tokenEnv
            ),
        
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        // $responseJson = json_decode($response);
        return $response;
    }

    public function ocioMensaje($nombre, $telefono, $idioma = 'en'){
        $tokenEnv = env('TOKEN_WHATSAPP', 'valorPorDefecto');

        $mensajePersonalizado = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $telefono,
            "type" => "template",
            "template" => [
                "name" => 'ocio',
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
                'Authorization: Bearer '.$tokenEnv
            ),
        
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        // $responseJson = json_decode($response);
        return $response;
    }
}
