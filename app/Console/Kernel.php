<?php

namespace App\Console;

use App\Mail\EnvioClavesEmail;
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
use Illuminate\Support\Facades\Mail;

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

        $schedule->call(function () {

            $mensajeEmail = $this->dniEmail('es', '123456789');
            $enviarEmail = $this->enviarEmail('david@hawkins.es', 'emails.envioClavesEmail', $mensajeEmail, 'Hawkins Suite - DNI', '123456789');

            Log::info("Tarea programada de Email al cliente ejecutada con éxito.");
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

                        $mensajeEmail = $this->dniEmail($idiomaCliente, $token);
                        $enviarEmail = $this->enviarEmail($reserva->cliente->email, 'emails.envioClavesEmail', $mensajeEmail, 'Hawkins Suite - DNI', $token);
                    } else {
                        // if ($reserva->dni_entregado == null) {
                        //     if ($reserva->fecha_entrada == $hoyFormateado) {
                        //         // Obtenemos el token ya creado
                        //         $token = $reserva->token;
                        //         // Limpiamos el numero de telefono
                        //         $phoneCliente =  $this->limpiarNumeroTelefono($reserva->cliente->telefono);
                        //         // Enviamos el mensaje
                        //         $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente->nacionalidad);

                        //         $enviarMensaje = $this->mensajesAutomaticos('dni', $token , $phoneCliente, $idiomaCliente );
                        //             // Data para guardar Mensaje enviado
                        //         $dataMensaje = [
                        //             'reserva_id' => $reserva->id,
                        //             'cliente_id' => $reserva->cliente_id,
                        //             'categoria_id' => 1,
                        //             'fecha_envio' => Carbon::now()
                        //         ];

                        //         MensajeAuto::create($dataMensaje);
                        //     }
                        // }

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
            $codigoPuertaPrincipal = '2404#';

            foreach($reservas as $reserva){
                // Fecha de Hoy
                $FechaHoy = new \DateTime();
                // Formatea la fecha actual a una cadena 'Y-m-d'
                $fechaHoyStr = $FechaHoy->format('Y-m-d');

                // Horas objetivo para lanzar mensajes
                $horaObjetivoBienvenida = new \DateTime($fechaHoyStr . ' 10:00:00');
                $horaObjetivoCodigo = new \DateTime($fechaHoyStr . ' 11:00:00');
                $horaObjetivoConsulta = new \DateTime($fechaHoyStr . ' 15:00:00');
                $horaObjetivoOcio = new \DateTime($fechaHoyStr . ' 17:00:00');
                $horaObjetivoDespedida = new \DateTime($fechaHoyStr . '11:00:00');

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
                        $mensaje = $this->clavesEmail($idiomaCliente, $reserva->cliente->nombre, $code['nombre'], $codigoPuertaPrincipal, $code['codigo']);
                        $enviarEmail = $this->enviarEmail($reserva->cliente->email, 'emails.envioClavesEmail', $mensaje, 'Hawkins Suite - Claves', $token = null);
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

    // Mensaje DNI
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
                    'codigo' => '2306'
                ];
                break;

            case 4:
                return [
                    'nombre' => '1A',
                    'codigo' => '4243'
                ];
                break;

            case 5:
                return [
                    'nombre' => '1B',
                    'codigo' => '2304'
                ];
                break;

            case 6:
                return [
                    'nombre' => 'BA',
                    'codigo' => '4241'
                ];
                break;

            case 7:
                return [
                    'nombre' => 'BB',
                    'codigo' => '2302'
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

    public function dniEmail($idioma, $token){

        switch ($idioma) {
            case 'es':
                $temaplate = '
                <h3 style="color:#0F1739; text-align: center">
                    Gracias por reservar en los apartamentos Hawkins!!
                </h3>

                <p style="margin: 0 !important">
                La legislación Española Nos obliga a solicitarle si Documento Nacional de Identidad o su pasaporte. Es obligatorio que nos lo facilite o no podrá alojarse en el apartamento.
                </p>
                <p style="margin: 0 !important">
                    Le dejamos un enlace para que rellene sus datos y nos lo facilite la copia del DNI o Pasaporte:
                </p>
                <p>
                    <a class="btn btn-primary" href="https://crm.apartamentosalgeciras.com/dni-user/'.$token.'">https://crm.apartamentosalgeciras.com/dni-user/'.$token.'</a>
                </p>
                <p style="margin: 0 !important">
                    Las claves de acceso al apartamento se las enviamos el dia de su llegada por whatsapp y correo electronico, asegurese de tener la informacion de contacto correctamente.
                </p>
                <br>
                <p style="margin: 0 !important">Gracias por utilizar nuestra aplicación!</p>
                ';
                return $temaplate;
                break;

            case 'fr':
                $temaplate = '
                <h3 style="color:#0F1739; text-align: center">
                    Merci de réserver chez les appartements Hawkins!!
                </h3>

                <p style="margin: 0 !important">
                    La législation espagnole nous oblige à vous demander votre carte d'."'".'identité nationale ou votre passeport. l est obligatoire que vous nous le fournissiez, sinon vous ne pourrez pas séjourner dans l'."'".'appartement.
                </p>
                <p style="margin: 0 !important">
                    Nous vous laissons un lien pour nous le fournir via le bouton ci-dessous:
                </p>
                <p>
                    <a class="btn btn-primary" href="https://crm.apartamentosalgeciras.com/dni-user/'.$token.'">https://crm.apartamentosalgeciras.com/dni-user/'.$token.'</a>
                </p>
                <p style="margin: 0 !important">
                    Les codes d'."'".'accès à l'."'".'appartement vous seront envoyés le jour de votre arrivée par WhatsApp et par e-mail, assurez-vous d'."'".'avoir les informations de contact correctes.
                </p>
                <br>
                <p style="margin: 0 !important">Merci d'."'".'utiliser notre application!</p>
                ';
                return $temaplate;
                break;

            case 'ar':
                $temaplate = '
                <h3 style="color:#0F1739; text-align: center">
                    شكراً لحجزكم في شقق هوكينز!!
                </h3>

                <p style="margin: 0 !important">
                    يُلزمنا القانون الإسباني بطلب هويتكم الوطنية أو جواز سفركم. من الضروري أن تقدموه لنا، وإلا لن تتمكنوا من الإقامة في الشقة.
                </p>
                <p style="margin: 0 !important">
                    :نترك لكم رابطاً لتقديمه لنا عبر الزر أدناه.
                </p>
                <p>
                    <a class="btn btn-primary" href="https://crm.apartamentosalgeciras.com/dni-user/'.$token.'">https://crm.apartamentosalgeciras.com/dni-user/'.$token.'</a>
                </p>
                <p style="margin: 0 !important">
                سنرسل لك رموز الوصول إلى الشقة في يوم وصولك عبر تطبيق WhatsApp والبريد الإلكتروني، وتأكد من حصولك على معلومات الاتصال بشكل صحيح.
                </p>
                <br>
                <p style="margin: 0 !important">شكرا لك على استخدام التطبيق لدينا!</p>
                ';
                return $temaplate;
                break;

            case 'de':
                $temaplate = '
                <h3 style="color:#0F1739; text-align: center">
                    Danke, dass Sie sich für die Hawkins Apartments entschieden haben!!
                </h3>

                <p style="margin: 0 !important">
                    Die spanische Gesetzgebung verpflichtet uns, Ihren Personalausweis oder Ihren Reisepass anzufordern. Es ist obligatorisch, dass Sie uns diesen zur Verfügung stellen, ansonsten können Sie nicht in der Wohnung übernachten.
                </p>
                <p style="margin: 0 !important">
                Wir hinterlassen Ihnen einen Link, um uns dies über den unteren Button zu übermitteln.:
                </p>
                <p>
                    <a class="btn btn-primary" href="https://crm.apartamentosalgeciras.com/dni-user/'.$token.'">https://crm.apartamentosalgeciras.com/dni-user/'.$token.'</a>
                </p>
                <p style="margin: 0 !important">
                    Wir senden Ihnen die Zugangscodes zum Apartment am Tag Ihrer Ankunft per WhatsApp und E-Mail zu. Stellen Sie sicher, dass Sie die Kontaktinformationen korrekt haben.                </p>
                <br>
                <p style="margin: 0 !important">
                    Vielen Dank, dass Sie unsere Anwendung nutzen!
                </p>
                ';
                return $temaplate;
                break;

            case 'pt_PT':
                $temaplate = '
                <h3 style="color:#0F1739; text-align: center">
                Obrigado por reservar nos apartamentos Hawkins!!
                </h3>

                <p style="margin: 0 !important">
                    A legislação espanhola nos obriga a solicitar o seu Documento Nacional de Identidade ou passaporte. É obrigatório que nos forneça, caso contrário, não poderá ficar no apartamento.
                </p>
                <p style="margin: 0 !important">
                    Deixamos um link para nos fornecer isso através do botão abaixo:
                </p>
                <p>
                    <a class="btn btn-primary" href="https://crm.apartamentosalgeciras.com/dni-user/'.$token.'">https://crm.apartamentosalgeciras.com/dni-user/'.$token.'</a>
                </p>
                <p style="margin: 0 !important">
                    Enviaremos os códigos de acesso ao apartamento no dia da sua chegada por WhatsApp e email, certifique-se de ter os dados de contato corretos.
                </p>
                <br>
                <p style="margin: 0 !important">
                    Obrigado por usar nosso aplicativo!
                </p>
                ';
                return $temaplate;
                break;

            case 'it':
                $$temaplate = '
                <h3 style="color:#0F1739; text-align: center">
                    Grazie per aver prenotato presso gli appartamenti Hawkins!!
                </h3>

                <p style="margin: 0 !important">
                    La legislazione spagnola ci obbliga a richiedere il vostro Documento Nazionale d'."'".'Identità o il passaporto. È obbligatorio che ce lo forniate, altrimenti non potrete soggiornare nell'."'".'appartamento.
                </p>
                <p style="margin: 0 !important">
                    Vi lasciamo un link per fornircelo tramite il pulsante in basso:
                </p>
                <p>
                    <a class="btn btn-primary" href="https://crm.apartamentosalgeciras.com/dni-user/'.$token.'">https://crm.apartamentosalgeciras.com/dni-user/'.$token.'</a>
                </p>
                <p style="margin: 0 !important">
                Ti invieremo i codici di accesso all'."'".'appartamento il giorno del tuo arrivo tramite WhatsApp ed e-mail, assicurati di avere le informazioni di contatto corrette.
                </p>
                <br>
                <p style="margin: 0 !important">
                    Grazie per aver utilizzato la nostra applicazione!
                </p>
                ';
                return $temaplate;
                break;

            default:
                //en
                $temaplate = '
                <h3 style="color:#0F1739; text-align: center">
                    Thank you for booking at Hawkins Apartments!!
                </h3>

                <p style="margin: 0 !important">
                    Spanish legislation requires us to request your National Identity Document or your passport. It is mandatory that you provide it to us or you will not be able to stay in the apartment.
                </p>
                <p style="margin: 0 !important">
                    We leave you a link to fill out your information and provide us with a copy of your DNI or Passport:
                </p>
                <p>
                    <a class="btn btn-primary" href="https://crm.apartamentosalgeciras.com/dni-user/'.$token.'">https://crm.apartamentosalgeciras.com/dni-user/'.$token.'</a>
                </p>
                <p style="margin: 0 !important">
                    Thank you for using our application!We will send you the access codes to the apartment on the day of your arrival by WhatsApp and email, make sure you have the contact information correctly.
                </p>
                <br>
                <p style="margin: 0 !important">Thank you for using our application!</p>
                ';
                return $temaplate;
                break;
        }

    }
    public function clavesEmail($idioma, $cliente, $apartamento, $claveEntrada, $clavePiso){

        switch ($idioma) {
            case 'es':
                $temaplate = '
                <h3 style="color:#0F1739; text-align: center">
                    Gracias por reservar en los apartamentos Hawkins!!
                </h3>

                <p style="margin: 0 !important">
                Hola '.$cliente.'!! La ubicación de los apartamentos es: <a class="btn btn-primary" href="https://goo.gl/maps/qb7AxP1JAxx5yg3N9"https://goo.gl/maps/qb7AxP1JAxx5yg3N9</a>.
                </p>
                <p style="margin: 0 !important">
                    Tu apartamento es el '.$apartamento.', los códigos para entrar al apartamento son: Para la puerta principal '.$claveEntrada.' y para la puerta de tu apartamento '.$clavePiso.'.
                </p>
                <p style="margin: 0 !important">
                    Espero que pases una estancia maravillosa.
                </p>
                <br>
                <p style="margin: 0 !important">Gracias por utilizar nuestra aplicación!</p>
                ';
                return $temaplate;
                break;

            case 'fr':
                $temaplate = '
                <h3 style="color:#0F1739; text-align: center">
                    Merci de votre réservation chez les appartements Hawkins!!
                </h3>

                <p style="margin: 0 !important">
                Bonjour '.$cliente.'!! L’emplacement des appartements est: <a class="btn btn-primary" href="https://goo.gl/maps/qb7AxP1JAxx5yg3N9">https://goo.gl/maps/qb7AxP1JAxx5yg3N9</a>.
                </p>
                <p style="margin: 0 !important">
                    Votre appartement est le '.$apartamento.', les codes pour entrer dans l’appartement sont : Pour la porte principale '.$claveEntrada.' et pour la porte de votre appartement '.$clavePiso.'.
                </p>
                <p style="margin: 0 !important">
                    J’espère que vous passerez un séjour merveilleux.
                </p>
                <br>
                <p style="margin: 0 !important">Merci d’utiliser notre application!</p>
                ';

                return $temaplate;
                break;

            case 'ar':
                $temaplate = '
                <h3 style="color:#0F1739; text-align: center">
                    شكرًا لك على حجزك في شقق هوكينز!!
                </h3>

                <p style="margin: 0 !important">
                مرحبًا '.$cliente.'!! موقع الشقق هو: <a class="btn btn-primary" href="https://goo.gl/maps/qb7AxP1JAxx5yg3N9">https://goo.gl/maps/qb7AxP1JAxx5yg3N9</a>.
                </p>
                <p style="margin: 0 !important">
                    شقتك هي '.$apartamento.'، رموز الدخول للشقة هي: للباب الرئيسي '.$claveEntrada.' ولباب شقتك '.$clavePiso.'.
                </p>
                <p style="margin: 0 !important">
                    أتمنى لك إقامة رائعة.
                </p>
                <br>
                <p style="margin: 0 !important">شكرًا لك على استخدام تطبيقنا!</p>
                ';
                return $temaplate;
                break;

            case 'de':
                $temaplate = '
                <h3 style="color:#0F1739; text-align: center">
                    Danke für Ihre Buchung bei den Hawkins Apartments!!
                </h3>

                <p style="margin: 0 !important">
                Hallo '.$cliente.'!! Die Lage der Apartments ist: <a class="btn btn-primary" href="https://goo.gl/maps/qb7AxP1JAxx5yg3N9">https://goo.gl/maps/qb7AxP1JAxx5yg3N9</a>.
                </p>
                <p style="margin: 0 !important">
                    Ihr Apartment ist das '.$apartamento.', die Codes zum Betreten des Apartments sind: Für die Haupteingangstür '.$claveEntrada.' und für die Tür Ihrer Wohnung '.$clavePiso.'.
                </p>
                <p style="margin: 0 !important">
                    Ich hoffe, Sie haben einen wunderbaren Aufenthalt.
                </p>
                <br>
                <p style="margin: 0 !important">Danke, dass Sie unsere Anwendung nutzen!</p>
                ';
                return $temaplate;
                break;

            case 'pt_PT':
                $temaplate = '
                <h3 style="color:#0F1739; text-align: center">
                    Obrigado por reservar nos apartamentos Hawkins!!
                </h3>

                <p style="margin: 0 !important">
                Olá '.$cliente.'!! A localização dos apartamentos é: <a class="btn btn-primary" href="https://goo.gl/maps/qb7AxP1JAxx5yg3N9">https://goo.gl/maps/qb7AxP1JAxx5yg3N9</a>.
                </p>
                <p style="margin: 0 !important">
                    Espero que tenha uma estadia maravilhosa.
                </p>
                <br>
                <p style="margin: 0 !important">Obrigado por utilizar nossa aplicação!</p>
                ';
                return $temaplate;
                break;

            case 'it':
                $temaplate = '
                <h3 style="color:#0F1739; text-align: center">
                    Grazie per aver prenotato all'."'".'Hawkins Apartments!!
                </h3>

                <p style="margin: 0 !important">
                    Ciao  '.$cliente.'!! La posizione degli appartamenti è: <a class="btn btn-primary" href="https://goo.gl/maps/qb7AxP1JAxx5yg3N9"https://goo.gl/maps/qb7AxP1JAxx5yg3N9</a>.
                </p>
                <p style="margin: 0 !important">
                    Spero che tu abbia un soggiorno meraviglioso.
                </p>
                <br>
                <p style="margin: 0 !important">Grazie per aver utilizzato la nostra applicazione!</p>
                ';
                return $temaplate;
                break;

            default:
                //en
                $temaplate = '
                <h3 style="color:#0F1739; text-align: center">
                    Thank you for booking at Hawkins Apartments!!
                </h3>

                <p style="margin: 0 !important">
                    Hello  '.$cliente.'!! The location of the apartments is: <a class="btn btn-primary" href="https://goo.gl/maps/qb7AxP1JAxx5yg3N9"https://goo.gl/maps/qb7AxP1JAxx5yg3N9</a>.
                </p>
                <p style="margin: 0 !important">
                    Your apartment is '.$apartamento.', the codes to enter the apartment are: for the main door '.$claveEntrada.' and for the door of your apartment '.$clavePiso.'.
                </p>
                <p style="margin: 0 !important">
                    I hope you have a wonderful stay.
                </p>
                <br>
                <p style="margin: 0 !important">Thank you for using our application!</p>
                ';
                return $temaplate;
                break;
        }

    }
    public function enviarEmail( $correo, $vista, $data, $asunto, $token, ){

        // 'emails.envioClavesEmail'

        Mail::to($correo)->send(new EnvioClavesEmail(
            $vista,
            $data,
            $asunto,
            $token
        ));

    }
}
