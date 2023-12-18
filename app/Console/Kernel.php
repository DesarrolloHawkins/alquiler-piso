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
            ->where('cliente_id',133)
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
                        $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente_id);
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
                        if ($reserva->fecha_entrada == $hoyFormateado) {
                            // Obtenemos el token ya creado
                            $token = $reserva->token;
                            // Limpiamos el numero de telefono
                            $phoneCliente =  $this->limpiarNumeroTelefono($reserva->cliente->telefono);
                            // Enviamos el mensaje
                            $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente_id);

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
            Log::info("Tarea programada de Nacionalidad del cliente ejecutada con éxito.");
        })->everyMinute();


         // Tarea par enviar el mensaje del Dni
         $schedule->call(function (ClienteService $clienteService) {
            // Obtener la fecha de hoy
            $hoy = Carbon::now();
            $fechaHoy = $hoy->format('Y-m-d');
            
            $reservas = Reserva::whereDate('fecha_entrada', '=', date('Y-m-d'))->where('dni_entregado', '!=', null)->get();

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
            $codigoPuertaPrincipal = '0404';
            $dias = 'no';
            foreach($reservas as $reserva){
                $dias = date_diff($hoy, date_create($reservas[0]['fecha_entrada']))->format('%R%a');
                $diasSalida = date_diff($hoy, date_create($reservas[0]['fecha_salida']))->format('%R%a');

                if($dias == 0 ){

                    $diferenciasHoraBienvenida = date_diff($hoy, date_create($fechaHoy .' 11:01:00'))->format('%R%H%I');

                    $mensajeBienvenida = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 4)->first();

                    if ($diferenciasHoraBienvenida  == 0 && $mensajeBienvenida == null) {

                        // Bienvenida a los apartamentos
                        $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente_id);

                        // $data = $this->mensajesAutomaticos('despedida', 'Ivan', '+34605621704', 'es' );
                        $data = $this->bienvenidoMensaje($reserva->cliente->nombre, $reserva->cliente->telefono, $idiomaCliente );

                        $dataMensaje = [
                            'reserva_id' => $reserva->id,
                            'cliente_id' => $reserva->cliente_id,
                            'categoria_id' => 4,
                            'fecha_envio' => Carbon::now()
                        ];

                        MensajeAuto::create($dataMensaje);
                    }

                    $mensajeClaves = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 3)->first();

                    $diferenciasHoraCodigos = date_diff($hoy, date_create($fechaHoy .' 12:01:00'))->format('%R%H%I');

                    if ($diferenciasHoraCodigos  == 0 && $mensajeClaves == null) {

                        // Bienvenida a los apartamentos
                        $code = $this->codigoApartamento($reserva->apartamento_id);


                        $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente_id);
                        // $data = $this->mensajesAutomaticos('despedida', 'Ivan', '+34605621704', 'es' );
                        $data = $this->clavesMensaje($reserva->cliente->nombre, $code['nombre'], $codigoPuertaPrincipal, $code['codigo'], $reserva->cliente->telefono, $idiomaCliente );

                        $dataMensaje = [
                            'reserva_id' => $reserva->id,
                            'cliente_id' => $reserva->cliente_id,
                            'categoria_id' => 3,
                            'fecha_envio' => Carbon::now()
                        ];

                        MensajeAuto::create($dataMensaje);
                    }

                    $mensajeConsulta = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 5)->first();

                    $diferenciasHoraConsulta = date_diff($hoy, date_create($fechaHoy .' 16:01:00'))->format('%R%H%I');
                    if ($diferenciasHoraConsulta  == 0 && $mensajeConsulta == null) {

                        // Bienvenida a los apartamentos
                        $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente_id);
                        // $data = $this->mensajesAutomaticos('despedida', 'Ivan', '+34605621704', 'es' );
                        $data = $this->consultaMensaje($reserva->cliente->nombre, $reserva->cliente->telefono, $idiomaCliente );

                        $dataMensaje = [
                            'reserva_id' => $reserva->id,
                            'cliente_id' => $reserva->cliente_id,
                            'categoria_id' => 5,
                            'fecha_envio' => Carbon::now()
                        ];

                        MensajeAuto::create($dataMensaje);
                    }
                    
                    $mensajeOcio = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 6)->first();

                    $diferenciasHoraOcio = date_diff($hoy, date_create($fechaHoy .' 18:01:00'))->format('%R%H%I');

                    if ($diferenciasHoraOcio  == 0 && $mensajeOcio == null) {

                        // Bienvenida a los apartamentos
                        $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente_id);
                        // $data = $this->mensajesAutomaticos('despedida', 'Ivan', '+34605621704', 'es' );

                        $data = $this->ocioMensaje($reserva->cliente->nombre, $reserva->cliente->telefono, $idiomaCliente);

                        $dataMensaje = [
                            'reserva_id' => $reserva->id,
                            'cliente_id' => $reserva->cliente_id,
                            'categoria_id' => 6,
                            'fecha_envio' => Carbon::now()
                        ];

                        MensajeAuto::create($dataMensaje);
                    }
                }

                $mensajeDespedida = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 7)->first();

                if ($diasSalida == 0 && $mensajeDespedida == null) {

                    $diferenciasHoraDespedida = date_diff($hoy, date_create($fechaHoy .' 12:01:00'))->format('%R%H%I');

                    if ($diferenciasHoraDespedida  == 0 && $mensajeDespedida == null) {

                        // Bienvenida a los apartamentos
                        $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente_id);
                        // $data = $this->mensajesAutomaticos('despedida', 'Ivan', '+34605621704', 'es' );
                        $data = $this->despedidaMensaje($reserva->cliente->nombre, $reserva->cliente->telefono, $idiomaCliente);

                        $dataMensaje = [
                            'reserva_id' => $reserva->id,
                            'cliente_id' => $reserva->cliente_id,
                            'categoria_id' => 7,
                            'fecha_envio' => Carbon::now()
                        ];

                        MensajeAuto::create($dataMensaje);
                    }

                }
            }

            Log::info("Tarea programada de Nacionalidad del cliente ejecutada con éxito.");
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

    public function mensajesAutomaticosBoton($template, $token, $telefono, $idioma = 'es'){
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
                        'codigo' => '0407'
                    ];
                break;
    
            case 2:
                return [
                    'nombre' => '2A',
                    'codigo' => '0407'
                ];
                break;
    
            case 3:
                return [
                    'nombre' => '2B',
                    'codigo' => '0407'
                ];
                break;
    
            case 4:
                return [
                    'nombre' => '1A',
                    'codigo' => '0407'
                ];
                break;
    
            case 5:
                return [
                    'nombre' => '1B',
                    'codigo' => '0407'
                ];
                break;
    
            case 6:
                return [
                    'nombre' => 'BA',
                    'codigo' => '0407'
                ];
                break;
    
            case 7:
                return [
                    'nombre' => 'BB',
                    'codigo' => '0407'
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

    public function mensajesAutomaticos($template, $nombre, $telefono, $idioma = 'es'){
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
