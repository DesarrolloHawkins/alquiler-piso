<?php

namespace App\Console;

use App\Mail\EnvioClavesEmail;
use App\Models\Apartamento;
use App\Models\Cliente;
use App\Models\Huesped;
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
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Spatie\UrlSigner\UrlSigner;

class Kernel extends ConsoleKernel
{

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\CheckComprobacion::class,
    ];


    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        // $clienteService = app(ClienteService::class);
        $schedule->command('clean:phonenumbers')->twiceDaily(1, 13);

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

        // Miramos si el cliente ha entregado el DNI el dia de entrada
        $schedule->call(function () {
            $hoy = Carbon::now();
        
            // Solo ejecutar después de las 10 de la mañana
            if ($hoy->hour >= 8) {
                // Obtener reservas que tengan la fecha de entrada igual al día de hoy y que no tengan el DNI entregado
                $reservasEntrada = Reserva::where('dni_entregado', null)
                                          ->where('estado_id', 1)
                                          ->whereDate('fecha_entrada', '=', $hoy->toDateString())
                                          ->get();
        
                foreach ($reservasEntrada as $reserva) {
                    // Comprobamos si ya existe un mensaje automático para esta reserva
                    $mensaje = MensajeAuto::where('reserva_id', $reserva->id)
                                          ->where('categoria_id', 8)
                                          ->first(); // Asegúrate de que 'first' esté escrito correctamente
                    if (!$mensaje) {
                        $cliente = $reserva->cliente;
                        $url = 'https://crm.apartamentosalgeciras.com/dni-user/'.$reserva->token;
                        $telefonosEnvios = [
                            'Ivan' => '34605621704',
                            'Elena' => '34664368232',
                            // 'Africa' => '34655659573',
                            'David' => '34622440984'
                        ];
                        $telefonoCliente = $this->limpiarNumeroTelefono($cliente->telefono);
        
                        foreach ($telefonosEnvios as $phone) {
                            $resultado = $this->noEntregadoDNIMensaje($cliente->alias, $reserva->codigo_reserva, $reserva->origen, $phone, $telefonoCliente, $url);
                        }
                        // Crear la data para guardar el mensaje
                        $dataMensaje = [
                            'reserva_id' => $reserva->id,
                            'cliente_id' => $reserva->cliente_id,
                            'categoria_id' => 8,
                            'fecha_envio' => Carbon::now()
                        ];
                        // Crear el mensaje
                        MensajeAuto::create($dataMensaje);
                    }
                }

                Log::info("Tarea programada de NO Entrega del DNI el día de entrada ejecutada con éxito.");
            }
        })->everyMinute();
    
        // Tarea comprobacion del estado del PC
        $schedule->command('check:comprobacion')->everyFifteenMinutes();

        
        // $schedule->call(function (ClienteService $clienteService) {
        //     // Obtener la fecha de hoy
        //     $hoy = Carbon::now();
        //     // Obtenemos la reservas que sean igual o superior a la fecha de entrada de hoy y no tengan el DNI Enrtegado.
        //     $clientes = Cliente::where('idioma', null)
        //     ->get();

        //     foreach($clientes as $cliente){
        //         $cliente = $clienteService->getIdiomaClienteID($cliente->id);
        //     }
        //     Log::info("Tarea programada de Nacionalidad del cliente ejecutada con éxito.");
        // })->everyMinute();


        // $schedule->call(function (ClienteService $clienteService) {
        //     // Obtener la fecha de hoy
        //     $hoy = Carbon::now();

        //       // Modificamos la consulta para obtener registros donde enviado_webpol no sea true (1)
        //         $reservasEntrada = Reserva::where('dni_entregado', true)
        //         ->where('fecha_entrada', '=', $hoy->toDateString())
        //         ->where(function($query) {
        //             $query->where('enviado_webpol', '=', 0)
        //                 ->orWhereNull('enviado_webpol');
        //         })
        //         ->first();

        //         if ($reservasEntrada != null) {
        //             $cliente = Cliente::where('id',$reservasEntrada->cliente_id)
        //             ->where(function($query) {
        //                 $query->where('webpol', '=', 0)
        //                     ->orWhereNull('webpol');
        //             })
        //             ->first();
        //             if ($cliente != null) {
        //                 $fechaExpedicion = new Carbon($cliente->fecha_expedicion_doc);
        //                 $fechaNacimiento = new Carbon($cliente->fecha_nacimiento);
        //                 $fechaReservaFinal = new Carbon($reservasEntrada->fecha_entrada);

        //                 $data = [
        //                     'jsonHiddenComunes'=> null, 
        //                     // 'idHospederia' => $idHospederia,
        //                     'nombre' => $cliente->nombre,
        //                     'apellido1' => $cliente->apellido1,
        //                     'apellido2' => $cliente->apellido2,
        //                     'nacionalidad' => $cliente->nacionalidadCode,
        //                     'nacionalidadStr' => $cliente->nacionalidadStr,
        //                     'tipoDocumento' => $cliente->tipo_documento,
        //                     'tipoDocumentoStr' => $cliente->tipo_documento_str,
        //                     'numIdentificacion' => $cliente->num_identificacion,
        //                     'fechaExpedicionDoc' => $fechaExpedicion->format('m/d/Y'),
        //                     'dia' => $fechaNacimiento->day,
        //                     'mes' => $fechaNacimiento->month,
        //                     'ano' => $fechaNacimiento->year,
        //                     'fechaNacimiento' => $fechaNacimiento->format('m/d/Y'),
        //                     'sexo' => $cliente->sexo_str,
        //                     'sexoStr' => $cliente->sexo,
        //                     'fechaEntrada' => $fechaReservaFinal->format('m/d/Y'),
        //                     // '_csrf' => $csrfToken
        //                 ];
        //                 $this->webPol($data);
        //                 $cliente->wepbol = true;
        //                 $cliente->save();
        //                 return true;
        //             }else{
        //                 $huesped = Huesped::where('reserva_id', $reservasEntrada->id)
        //                 ->where(function($query) {
        //                     $query->where('webpol', '=', 0)
        //                         ->orWhereNull('webpol');
        //                 })
        //                 ->first();
        //                 if ($huesped != null) {
        //                     $fechaExpedicion = new Carbon($huesped->fecha_expedicion);
        //                     $fechaNacimiento = new Carbon($huesped->fecha_nacimiento);
        //                     $fechaReservaFinal = new Carbon($reservasEntrada->fecha_entrada);

        //                     $data = [
        //                         'jsonHiddenComunes'=> null, 
        //                         // 'idHospederia' => $idHospederia,
        //                         'nombre' => $huesped->nombre,
        //                         'apellido1' => $huesped->primer_apellido,
        //                         'apellido2' => $huesped->segundo_apellido,
        //                         'nacionalidad' => $huesped->nacionalidadCode,
        //                         'nacionalidadStr' => $huesped->nacionalidadStr,
        //                         'tipoDocumento' => $huesped->tipo_documento,
        //                         'tipoDocumentoStr' => $huesped->tipo_documento_str,
        //                         'numIdentificacion' => $huesped->numero_identificacion,
        //                         'fechaExpedicionDoc' => $fechaExpedicion->format('m/d/Y'),
        //                         'dia' => $fechaNacimiento->day,
        //                         'mes' => $fechaNacimiento->month,
        //                         'ano' => $fechaNacimiento->year,
        //                         'fechaNacimiento' => $fechaNacimiento->format('m/d/Y'),
        //                         'sexo' => $huesped->sexo_str,
        //                         'sexoStr' => $huesped->sexo,
        //                         'fechaEntrada' => $fechaReservaFinal->format('m/d/Y'),
        //                         // '_csrf' => $csrfToken
        //                     ];
        //                     $this->webPol($data);
        //                     $huesped->wepbol = true;
        //                     $huesped->save();
        //                 }
        //                 $reservasEntrada->enviado_webpol = true;
        //                 $reservasEntrada->save();
        //             }

        //         }

        //     Log::info("Tarea programada de WebPol ejecutada con éxito.");
        // })->everyMinute();

        $schedule->call(function () {

            // $mensajeEmail = $this->dniEmail('es', '123456789');
            // $enviarEmail = $this->enviarEmail('david@hawkins.es', 'emails.envioClavesEmail', $mensajeEmail, 'Hawkins Suite - DNI', '123456789');

            // Log::info("Tarea programada de Email al cliente ejecutada con éxito.");
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
                        $enviarEmail = $this->enviarEmail($reserva->cliente->email_secundario, 'emails.envioClavesEmail', $mensajeEmail, 'Hawkins Suite - DNI', $token);
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

            $reservas = Reserva::whereDate('fecha_entrada', '=', date('Y-m-d'))
            // ->where('dni_entregado', '!=', null)
            ->get();
            
            foreach($reservas as $reserva){
                if ($reserva->apartamento_id > 7) {
                    $codigoPuertaPrincipal = '1048#';
                    # code...
                }else {
                    $codigoPuertaPrincipal = '5543#';
                }
                $apartamentoReservado = Apartamento::find($reserva->apartamento_id);
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
                        $enlace = $apartamentoReservado->edificio == 1 ? 'https://goo.gl/maps/qb7AxP1JAxx5yg3N9' : 'https://maps.app.goo.gl/t81tgLXnNYxKFGW4A';
                        $enlaceLimpio = $apartamentoReservado->edificio == 1 ? 'goo.gl/maps/qb7AxP1JAxx5yg3N9' : 'maps.app.goo.gl/t81tgLXnNYxKFGW4A';
                        if ($reserva->apartamento_id === 1) {
                            $data = $this->clavesMensajeAtico(
                                $reserva->cliente->nombre,
                                $code['nombre'], $codigoPuertaPrincipal,
                                $code['codigo'],
                                $reserva->cliente->telefono,
                                $idiomaCliente,
                                $idiomaCliente == 'pt_PT' ? 'codigo_atico_por' : 'codigos_atico',
                                $url = $enlace,
                                $url2 = $enlaceLimpio
                            );
                            # code...
                        } else {
                            $data = $this->clavesMensaje($reserva->cliente->nombre, $code['nombre'], $codigoPuertaPrincipal, $code['codigo'], $reserva->cliente->telefono, $idiomaCliente );
                        }

                        // Creamos la data para guardar el mensaje
                        $dataMensaje = [
                            'reserva_id' => $reserva->id,
                            'cliente_id' => $reserva->cliente_id,
                            'categoria_id' => 3,
                            'fecha_envio' => Carbon::now()
                        ];
                        // Creamos el mensaje
                        MensajeAuto::create($dataMensaje);


                        if ($reserva->apartamento_id === 1) {
                            $mensaje = $this->clavesEmailAtico($idiomaCliente, $reserva->cliente->nombre, $code['nombre'], $codigoPuertaPrincipal, $code['codigo']);
                        }else {
                            $mensaje = $this->clavesEmail($idiomaCliente, $reserva->cliente->nombre, $code['nombre'], $codigoPuertaPrincipal, $code['codigo'],$apartamentoReservado->edificio);
                        }
                        $enviarEmail = $this->enviarEmail($reserva->cliente->email_secundario, 'emails.envioClavesEmail', $mensaje, 'Hawkins Suite - Claves', $token = null);
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

    public function webPol($data){
        // $credentials = array(
        //     'user' => 'H11070GEV04',
        //     'pass' => 'H4Kins4p4rtamento2023'
        // ); 
        // $data = [
        //     'username' => 'H11070GEV04',
        //     'password' => 'H4Kins4p4rtamento2023',
        //     '_csrf' => '49614a9a-efc7-4c36-9063-b1cd6824aa9a'
        // ];
        //https://webpol.policia.es/e-hotel/execute_login
        //https://webpol.policia.es/e-hotel/login
        //https://webpol.policia.es/hospederia/manual/vista/grabadorManual
        //https://webpol.policia.es/hospederia/manual/insertar/huesped

        $browser = new HttpBrowser(HttpClient::create());
        $crawler = $browser->request('GET', 'https://webpol.policia.es/e-hotel/login');
        $csrfToken = $crawler->filter('meta[name="_csrf"]')->attr('content');

        $response1 = $browser->getResponse();
        $statusCode1 = $response1->getStatusCode();
        if ($statusCode1 == 200) {
            $responseContent = $crawler->html();
        } else {
            // Manejar el caso en que la respuesta no es exitosa
            echo '1 - Código de estado HTTP: ' . $statusCode1;
            return;
        }

        $cookiesArray = [];
        foreach ($browser->getCookieJar()->all() as $cookie) {
            $cookiesArray[$cookie->getName()] = $cookie->getValue();
        }
        
        $postData = [
            'username' => 'H11070GEV04',
            'password' => 'HaKinsapartamento2024',
            '_csrf'    => $csrfToken
        ];

        $headers = [
            'HTTP_CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'HTTP_COOKIE' => 'FRONTAL_JSESSIONID: ' . $cookiesArray['FRONTAL_JSESSIONID'] . ' UqZBpD3n3iHPAgNS9Fnn5SbNcvsF5IlbdcvFr4ieqh8_: ' . $cookiesArray['UqZBpD3n3iHPAgNS9Fnn5SbNcvsF5IlbdcvFr4ieqh8_'] . ' cookiesession1: ' . $cookiesArray['cookiesession1']
        ];

        $browser->setServerParameters($headers);
        $crawler = $browser->request(
            'POST',
            'https://webpol.policia.es/e-hotel/execute_login',
            $postData
        );

        $response2 = $browser->getResponse();
        $statusCode2 = $response2->getStatusCode();
        if ($statusCode2 == 200) {
            $responseContent = $crawler->html();
        } else {
            // Manejar el caso en que la respuesta no es exitosa
            echo '2 - Código de estado HTTP: ' . $statusCode2;
            return;
        }

        $crawler = $browser->request('GET', 'https://webpol.policia.es/e-hotel/hospederia/manual/vista/grabadorManual');
        $idHospederia = $crawler->filter('#idHospederia')->attr('value');

        $response3 = $browser->getResponse();
        $statusCode3 = $response3->getStatusCode();
        if ($statusCode3 == 200) {
            $responseContent = $crawler->html();
        } else {
            // Manejar el caso en que la respuesta no es exitosa
            echo '3 - Código de estado HTTP: ' . $statusCode3;
            return;
        }
        mb_internal_encoding("UTF-8");

        $apellido = mb_convert_encoding('CASTAÑOS', 'UTF-8');
        
       
        $headers = [
            'Cookie' => 'FRONTAL_JSESSIONID: ' . $cookiesArray['FRONTAL_JSESSIONID'] . ' UqZBpD3n3iHPAgNS9Fnn5SbNcvsF5IlbdcvFr4ieqh8_: ' . $cookiesArray['UqZBpD3n3iHPAgNS9Fnn5SbNcvsF5IlbdcvFr4ieqh8_'] . ' cookiesession1: ' . $cookiesArray['cookiesession1'],
            'Accept' => 'text/html, */*; q=0.01',
            'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            'Referer' => 'https://webpol.policia.es/e-hotel/inicio',
            'X-Csrf-Token' => $csrfToken,
            'X-Requested-With' => 'XMLHttpRequest',
            // Otros encabezados según sea necesario
        ];
        // $data['apellido1'] = mb_convert_encoding('CASTAÑOS', 'UTF-8');
        $data['idHospederia'] = $idHospederia;
        $data['_csrf'] = $csrfToken;

        // 'idHospederia' => $idHospederia,
        // '_csrf' => $csrfToken
        $browser->setServerParameters($headers);

        $crawler = $browser->request(
            'POST',
            'https://webpol.policia.es/e-hotel/hospederia/manual/insertar/huesped',
            $data
        );
        // Diagnóstico: Ver contenido de la respuesta
        $responseContent = $browser->getResponse()->getContent();
        echo $responseContent;

        $response4 = $browser->getResponse();
        $statusCode4 = $response4->getStatusCode();
        
        if ($browser->getResponse()->getStatusCode() == 302) {
            $crawler = $browser->followRedirect();
            // Sigue la redirección
        }

        if ($statusCode4 == 200) {
            $responseContent = $crawler->html();
        } else {
            // Manejar el caso en que la respuesta no es exitosa
            // echo '4 - Código de estado HTTP: ' . $statusCode4 . $csrfToken . ' id: '. $idHospederia;
            return;
        }
        return [
            $csrfToken,
            $cookiesArray,
            $responseContent
        ];
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
        $apartamento = Apartamento::find($habitacion);
        if ($apartamento) {
            switch ($habitacion) {
                case 1:
                    return [
                            'nombre' => 'ATICO',
                            'codigo' => $apartamento->claves
                        ];
                    break;
    
                case 2:
                    return [
                        'nombre' => '2A',
                        'codigo' => $apartamento->claves
                    ];
                    break;
    
                case 3:
                    return [
                        'nombre' => '2B',
                        'codigo' => $apartamento->claves
                    ];
                    break;
    
                case 4:
                    return [
                        'nombre' => '1A',
                        'codigo' => $apartamento->claves
                    ];
                    break;
    
                case 5:
                    return [
                        'nombre' => '1B',
                        'codigo' => $apartamento->claves
                    ];
                    break;
    
                case 6:
                    return [
                        'nombre' => 'BA',
                        'codigo' => $apartamento->claves
                    ];
                    break;
    
                case 7:
                    return [
                        'nombre' => 'BB',
                        'codigo' => $apartamento->claves
                    ];
                    break;
                case 8:
                    return [
                        'nombre' => 'Atico',
                        'codigo' => $apartamento->claves
                    ];
                    break;
                case 9:
                    return [
                        'nombre' => '3A',
                        'codigo' => $apartamento->claves
                    ];
                    break;
                case 10:
                    return [
                        'nombre' => '3B',
                        'codigo' => $apartamento->claves
                    ];
                    break;
                case 11:
                    return [
                        'nombre' => '3C',
                        'codigo' => $apartamento->claves
                    ];
                    break;
                case 12:
                    return [
                        'nombre' => '2A',
                        'codigo' => '2568'
                    ];
                    break;
                case 13:
                    return [
                        'nombre' => '2B',
                        'codigo' => $apartamento->claves
                    ];
                    break;
                case 14:
                    return [
                        'nombre' => '1A',
                        'codigo' => $apartamento->claves
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

    public function noEntregadoDNIMensaje($nombre, $codigoReserva, $plataforma, $telefono, $telefonoCliente, $url, $idioma = 'es', ){
        $tokenEnv = env('TOKEN_WHATSAPP', 'valorPorDefecto');

        $mensajePersonalizado = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $telefono,
            "type" => "template",
            "template" => [
                "name" => 'dni_no_entregado',
                "language" => ["code" => $idioma],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            [
                                "type" => "text", 
                                "text" => $nombre
                            ],
                            [
                                "type" => "text", 
                                "text" => $codigoReserva
                            ],
                            [
                                "type" => "text", 
                                "text" => $plataforma
                            ],
                            [
                                "type" => "text", 
                                "text" => $telefonoCliente
                            ],
                            [
                                "type" => "text", 
                                "text" => $url
                            ],
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

    public function clavesMensajeAtico($nombre, $apartamento, $puertaPrincipal, $codigoApartamento, $telefono, $idioma = 'en', $template, $url, $url2){
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
                            ["type" => "text", "text" => $apartamento],
                            ["type" => "text", "text" => $puertaPrincipal],
                            ["type" => "text", "text" => $codigoApartamento],
                            ["type" => "text", "text" => $url]
                        ],
                    ],
                    [
                        "type" => "button",
                        "sub_type" => "url",
                        "index" => "0",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => $url2
                            ]
                        ]
                    ]
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
                    <a class="btn btn-primary" href="https://crm.apartamentosalgeciras.com/dni-user/'.$token.'">Rellenar datos</a>
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
                    <a class="btn btn-primary" href="https://crm.apartamentosalgeciras.com/dni-user/'.$token.'">Remplir les données</a>
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
                    <a class="btn btn-primary" href="https://crm.apartamentosalgeciras.com/dni-user/'.$token.'">ملء البيانات</a>
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
                    <a class="btn btn-primary" href="https://crm.apartamentosalgeciras.com/dni-user/'.$token.'">Daten füllen</a>
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
                    <a class="btn btn-primary" href="https://crm.apartamentosalgeciras.com/dni-user/'.$token.'">Preencha os dados</a>
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
                    <a class="btn btn-primary" href="https://crm.apartamentosalgeciras.com/dni-user/'.$token.'">Compila i dati</a>
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
                    <a class="btn btn-primary" href="https://crm.apartamentosalgeciras.com/dni-user/'.$token.'">Fill data</a>
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

    public function clavesEmail($idioma, $cliente, $apartamento, $claveEntrada, $clavePiso, $edificio = 1){

        $enlace = $edificio == 1 ? 'https://goo.gl/maps/qb7AxP1JAxx5yg3N9' : 'https://maps.app.goo.gl/t81tgLXnNYxKFGW4A';

        switch ($idioma) {
            case 'es':
                $temaplate = '
                <h3 style="color:#0F1739; text-align: center">
                    Gracias por reservar en los apartamentos Hawkins!!
                </h3>

                <p style="margin: 0 !important">
                Hola '.$cliente.'!! La ubicación de los apartamentos es: <a class="btn btn-primary" href="'.$enlace.'">'.$enlace.'</a>.
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
                Bonjour '.$cliente.'!! L’emplacement des appartements est: <a class="btn btn-primary" href="'.$enlace.'">'.$enlace.'</a>.
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
                مرحبًا '.$cliente.'!! موقع الشقق هو: <a class="btn btn-primary" href="'.$enlace.'">'.$enlace.'</a>.
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
                Hallo '.$cliente.'!! Die Lage der Apartments ist: <a class="btn btn-primary" href="'.$enlace.'">'.$enlace.'</a>.
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
                Olá '.$cliente.'!! A localização dos apartamentos é: <a class="btn btn-primary" href="'.$enlace.'">'.$enlace.'</a>.
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
                    Ciao  '.$cliente.'!! La posizione degli appartamenti è: <a class="btn btn-primary" href="'.$enlace.'">'.$enlace.'</a>.
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
                    Hello  '.$cliente.'!! The location of the apartments is: <a class="btn btn-primary" href="'.$enlace.'">'.$enlace.'</a>.
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

    public function clavesEmailAtico($idioma, $cliente, $apartamento, $claveEntrada, $clavePiso){

        switch ($idioma) {
            case 'es':
                $temaplate = '
                <h3 style="color:#0F1739; text-align: center">
                    Gracias por reservar en los apartamentos Hawkins!!
                </h3>

                <p style="margin: 0 !important">
                Hola '.$cliente.'!!
                </p>
                <p>
                Te indico que la entrada debes realizarla después de las 14 horas
                </p>
                <p>
                    La ubicación de los apartamentos es: <a class="btn btn-primary" href="https://goo.gl/maps/qb7AxP1JAxx5yg3N9" >Ir a google map</a></p>
                </p>
                <p style="margin: 0 !important">
                    Tu apartamento es el '.$apartamento.', los códigos para entrar al apartamento son: Para la puerta principal '.$claveEntrada.'.
                </p>
                <p>
                Tienes que subir a la 3 planta, ahi estará la caja con sus llaves, clave es '.$clavePiso.' debes de darle a la pestaña negra y ahí estarán las llaves.
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
                Bonjour '.$cliente.'!! L’emplacement des appartements est: <a class="btn btn-primary" href="https://goo.gl/maps/qb7AxP1JAxx5yg3N9">Allez sur Google Map</a>.
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
                        شكرًا لحجزك في شقق هوكينز!!
                    </h3>
                
                    <p style="margin: 0 !important">
                    مرحبًا '.$cliente.'!!
                    </p>
                    <p>
                    يرجى ملاحظة أنه يمكنك تسجيل الدخول بعد الساعة 2 مساءً.
                    </p>
                    <p>
                        موقع الشقق هو: <a class="btn btn-primary" href="https://goo.gl/maps/qb7AxP1JAxx5yg3N9">اذهب إلى خريطة جوجل</a></p>
                    </p>
                    <p style="margin: 0 !important">
                        شقتك هي الرقم '.$apartamento.'، أكواد الدخول هي: للباب الرئيسي '.$claveEntrada.'.
                    </p>
                    <p>
                    عليك الصعود إلى الطابق الثالث، حيث ستجد صندوقًا يحتوي على المفاتيح، الرمز هو '.$clavePiso.'. اضغط على اللسان الأسود للوصول إلى المفاتيح.
                    </p>
                    <p style="margin: 0 !important">
                        أتمنى لك إقامة رائعة.
                    </p>
                    <br>
                    <p style="margin: 0 !important">شكرًا لاستخدامك تطبيقنا!</p>
                    ';
                    return $temaplate;
                    break;
                

                case 'de':
                    $temaplate = '
                    <h3 style="color:#0F1739; text-align: center">
                        Danke für Ihre Buchung bei den Hawkins Apartments!!
                    </h3>
                
                    <p style="margin: 0 !important">
                    Hallo '.$cliente.'!!
                    </p>
                    <p>
                    Bitte beachten Sie, dass der Check-in nach 14 Uhr möglich ist.
                    </p>
                    <p>
                        Die Lage der Apartments ist: <a class="btn btn-primary" href="https://goo.gl/maps/qb7AxP1JAxx5yg3N9">Gehen Sie zu Google Map</a></p>
                    </p>
                    <p style="margin: 0 !important">
                        Ihr Apartment ist das '.$apartamento.', die Zugangscodes sind: Für die Haupteingangstür '.$claveEntrada.'.
                    </p>
                    <p>
                    Sie müssen in die 3. Etage gehen, dort finden Sie eine Box mit den Schlüsseln, der Code ist '.$clavePiso.'. Bitte drücken Sie die schwarze Lasche, um die Schlüssel zu entnehmen.
                    </p>
                    <p style="margin: 0 !important">
                        Ich hoffe, Sie haben einen wunderbaren Aufenthalt.
                    </p>
                    <br>
                    <p style="margin: 0 !important">Vielen Dank für die Nutzung unserer App!</p>
                    ';
                    return $temaplate;
                    break;
                

                    case 'pt':
                        $temaplate = '
                        <h3 style="color:#0F1739; text-align: center">
                            Obrigado por reservar nos apartamentos Hawkins!!
                        </h3>
                    
                        <p style="margin: 0 !important">
                        Olá '.$cliente.'!!
                        </p>
                        <p>
                        Por favor, note que o check-in é após as 14 horas.
                        </p>
                        <p>
                            A localização dos apartamentos é: <a class="btn btn-primary" href="https://goo.gl/maps/qb7AxP1JAxx5yg3N9">Vá para o mapa do Google</a></p>
                        </p>
                        <p style="margin: 0 !important">
                            Seu número de apartamento é '.$apartamento.', os códigos para entrar são: Para a porta principal '.$claveEntrada.'.
                        </p>
                        <p>
                        Você deve subir ao 3º andar, onde encontrará uma caixa com as chaves, o código é '.$clavePiso.'.
                        </p>
                        <p style="margin: 0 !important">
                            Espero que tenha uma estadia maravilhosa.
                        </p>
                        <br>
                        <p style="margin: 0 !important">Obrigado por usar nosso aplicativo!</p>
                        ';
                        return $temaplate;
                        break;
                    

                        case 'it':
                            $temaplate = '
                            <h3 style="color:#0F1739; text-align: center">
                                Grazie per aver prenotato presso Hawkins Apartments!!
                            </h3>
                        
                            <p style="margin: 0 !important">
                            Ciao '.$cliente.'!!
                            </p>
                            <p>
                            Ti ricordo che il check-in è possibile dopo le 14:00.
                            </p>
                            <p>
                                La posizione degli appartamenti è: <a class="btn btn-primary" href="https://goo.gl/maps/qb7AxP1JAxx5yg3N9">Vai su Google Map</a></p>
                            </p>
                            <p style="margin: 0 !important">
                                Il tuo appartamento è il numero '.$apartamento.', i codici per entrare sono: Per la porta principale '.$claveEntrada.'.
                            </p>
                            <p>
                            Devi salire al terzo piano, dove troverai una scatola con le chiavi, il codice è '.$clavePiso.'. Premi la linguetta nera per accedere alle chiavi.
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
                    Hello '.$cliente.'!!
                    </p>
                    <p>
                    Please note that check-in is after 2 PM.
                    </p>
                    <p>
                        The location of the apartments is: <a class="btn btn-primary" href="https://goo.gl/maps/qb7AxP1JAxx5yg3N9">Go to google map</a></p>
                    </p>
                    <p style="margin: 0 !important">
                        Your apartment number is '.$apartamento.', the entry codes are: For the main door '.$claveEntrada.'.
                    </p>
                    <p>
                    You need to go up to the 3rd floor, where you will find a box with the keys, the code is '.$clavePiso.'. Please press the black tab to access the keys.
                    </p>
                    <p style="margin: 0 !important">
                        I hope you have a wonderful stay.
                    </p>
                    <br>
                    <p style="margin: 0 !important">Thank you for using our app!</p>
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
