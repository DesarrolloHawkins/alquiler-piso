<?php

namespace App\Http\Controllers;

use App\Models\MensajeAuto;
use App\Models\Reserva;
use Illuminate\Http\Request;
use App\Services\ClienteService;
use Carbon\Carbon;
use DateTime;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function test(ClienteService $clienteService){
        // Obtener la fecha de hoy
        $hoy = Carbon::now();
           
        $reservas = Reserva::whereDate('fecha_entrada', '=', date('Y-m-d'))->where('dni_entregado', '!=', null)->get();
        $codigoPuertaPrincipal = '0404';

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

            // dd(
            //     $diferenciasHoraBienvenida,
            //     $diferenciasHoraCodigos,
            //     $diferenciasHoraConsulta,
            //     $diferenciasHoraOcio,
            //     $diferenciasHoraDespedida
            // );
            if ($diferenciasHoraBienvenida >= 0) {
                dd($diferenciasHoraBienvenida, $FechaHoy );
            }

            if ($diferenciasHoraCodigos >= 0) {
                dd($diferenciasHoraCodigos, $FechaHoy );
            }

            if ($diferenciasHoraConsulta >= 0) {
                dd($diferenciasHoraConsulta, $FechaHoy );
            }

            if ($diferenciasHoraBienvenida >= 0 && $mensajeBienvenida == null) {

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

            if ($diferenciasHoraCodigos >= 0 && $mensajeBienvenida != null && $mensajeClaves == null) {

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

            if ($diferenciasHoraConsulta >= 0 && $mensajeConsulta == null) {

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

            if ($diferenciasHoraOcio >= 0 && $mensajeOcio == null) {

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
            
            if ($mensajeDespedida == null) {
                if ($diferenciasHoraDespedida >= 0 && $mensajeDespedida == null) {

                    // Obtenemos codigo de idioma
                    $idiomaCliente = $this->clienteService->idiomaCodigo($reserva->cliente->nacionalidad);
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
        }
    }
}
