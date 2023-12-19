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

    public function test(){
         // Obtener la fecha de hoy
         $hoy = Carbon::now();
         $fechaHoy = $hoy->format('Y-m-d');
         
         $reservas = Reserva::whereDate('fecha_entrada', '=', date('Y-m-d'))->where('dni_entregado', '!=', null)->get();
         $codigoPuertaPrincipal = '0404';

             // $dias = date_diff($hoy, date_create($reservas[0]['fecha_entrada']))->format('%R%a');

             // $diasSalida = date_diff($hoy, date_create($reservas[0]['fecha_salida']))->format('%R%a');

             // if($dias == 0 ){
             //     $FechaHoy = new \DateTime();

             //     $horaObjetivoBienvenida = new DateTime($FechaHoy .' 11:00:00');

             //     $diferenciasHoraBienvenida = $hoy->diff($horaObjetivoBienvenida)->format('%R%H%I');


             //     $mensajeBienvenida = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 4)->first();

             //     if ($diferenciasHoraBienvenida  == 0 && $mensajeBienvenida == null) {

             //         // Bienvenida a los apartamentos
             //         $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente->nacionalidad);

             //         // $data = $this->mensajesAutomaticos('despedida', 'Ivan', '+34605621704', 'es' );
             //         $data = $this->bienvenidoMensaje($reserva->cliente->nombre, $reserva->cliente->telefono, $idiomaCliente );

             //         $dataMensaje = [
             //             'reserva_id' => $reserva->id,
             //             'cliente_id' => $reserva->cliente_id,
             //             'categoria_id' => 4,
             //             'fecha_envio' => Carbon::now()
             //         ];

             //         MensajeAuto::create($dataMensaje);
             //     }

             //     $mensajeClaves = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 3)->first();

             //     $diferenciasHoraCodigos = date_diff($hoy, date_create($fechaHoy .' 12:01:00'))->format('%R%H%I');

             //     if ($diferenciasHoraCodigos  == 0 && $mensajeClaves == null) {

             //         // Bienvenida a los apartamentos
             //         $code = $this->codigoApartamento($reserva->apartamento_id);


             //         $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente->nacionalidad);
             //         // $data = $this->mensajesAutomaticos('despedida', 'Ivan', '+34605621704', 'es' );
             //         $data = $this->clavesMensaje($reserva->cliente->nombre, $code['nombre'], $codigoPuertaPrincipal, $code['codigo'], $reserva->cliente->telefono, $idiomaCliente );

             //         $dataMensaje = [
             //             'reserva_id' => $reserva->id,
             //             'cliente_id' => $reserva->cliente_id,
             //             'categoria_id' => 3,
             //             'fecha_envio' => Carbon::now()
             //         ];

             //         MensajeAuto::create($dataMensaje);
             //     }

             //     $mensajeConsulta = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 5)->first();

             //     $diferenciasHoraConsulta = date_diff($hoy, date_create($fechaHoy .' 16:01:00'))->format('%R%H%I');
             //     if ($diferenciasHoraConsulta  == 0 && $mensajeConsulta == null) {

             //         // Bienvenida a los apartamentos
             //         $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente->nacionalidad);
             //         // $data = $this->mensajesAutomaticos('despedida', 'Ivan', '+34605621704', 'es' );
             //         $data = $this->consultaMensaje($reserva->cliente->nombre, $reserva->cliente->telefono, $idiomaCliente );

             //         $dataMensaje = [
             //             'reserva_id' => $reserva->id,
             //             'cliente_id' => $reserva->cliente_id,
             //             'categoria_id' => 5,
             //             'fecha_envio' => Carbon::now()
             //         ];

             //         MensajeAuto::create($dataMensaje);
             //     }
                 
             //     $mensajeOcio = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 6)->first();

             //     $diferenciasHoraOcio = date_diff($hoy, date_create($fechaHoy .' 18:01:00'))->format('%R%H%I');

             //     if ($diferenciasHoraOcio  == 0 && $mensajeOcio == null) {

             //         // Bienvenida a los apartamentos
             //         $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente->nacionalidad);
             //         // $data = $this->mensajesAutomaticos('despedida', 'Ivan', '+34605621704', 'es' );

             //         $data = $this->ocioMensaje($reserva->cliente->nombre, $reserva->cliente->telefono, $idiomaCliente);

             //         $dataMensaje = [
             //             'reserva_id' => $reserva->id,
             //             'cliente_id' => $reserva->cliente_id,
             //             'categoria_id' => 6,
             //             'fecha_envio' => Carbon::now()
             //         ];

             //         MensajeAuto::create($dataMensaje);
             //     }
             // }
         //$dias = 'no';
         foreach($reservas as $reserva){

            $FechaHoy = new \DateTime();

            // Formatea la fecha actual a una cadena 'Y-m-d'
            $fechaHoyStr = $FechaHoy->format('Y-m-d');

            // Crea un nuevo objeto DateTime para las 11:00 del día actual
            $horaObjetivoBienvenida = new \DateTime($fechaHoyStr . ' 11:00:00');

            // Calcula la diferencia de tiempo
            $diferenciasHoraBienvenida = $FechaHoy->diff($horaObjetivoBienvenida)->format('%R%H%I');


             $mensajeBienvenida = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 4)->first();

             if ($diferenciasHoraBienvenida >= 0 && $mensajeBienvenida == null) {
                dd($reserva);
                 // Bienvenida a los apartamentos
                 $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente->nacionalidad);

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


                 $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente->nacionalidad);
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
                 $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente->nacionalidad);
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
                 $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente->nacionalidad);
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

             $mensajeDespedida = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 7)->first();
             // if ($diasSalida == 0 && $mensajeDespedida == null) {
             if ($mensajeDespedida == null) {

                 $diferenciasHoraDespedida = date_diff($hoy, date_create($fechaHoy .' 12:01:00'))->format('%R%H%I');

                 if ($diferenciasHoraDespedida  == 0 && $mensajeDespedida == null) {

                     // Bienvenida a los apartamentos
                     $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente->nacionalidad);
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

    }
}
