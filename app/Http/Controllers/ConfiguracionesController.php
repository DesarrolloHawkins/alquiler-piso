<?php

namespace App\Http\Controllers;

use App\Models\Anio;
use App\Models\Configuraciones;
use App\Models\FormasPago;
use App\Models\Reparaciones;
use Carbon\Carbon;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ConfiguracionesController extends Controller
{
    public function index(){
        $configuraciones = Configuraciones::all();
        $reparaciones = Reparaciones::all();
        $anio = app('anio'); // Obtiene el año global

        // Obtener el año actual
        $anioActual = date('Y');

        // Inicializar el array de años
        $anios = [];
        $formasPago = FormasPago::all();
        // Añadir el año actual y los cinco años anteriores al array
        for ($i = 0; $i <= 5; $i++) {
            $anios[] = strval($anioActual - $i);
        }

        return view('admin.configuraciones.index', compact('configuraciones', 'reparaciones', 'anio', 'anios','formasPago'));
    }
    public function edit($id, Request $request){
        $configuraciones = Configuraciones::all();
        return view('admin.configuraciones.index', compact('configuraciones'));
    }
    public function update($id, Request $request){
        $confi = Configuraciones::find($id);
        $confi->password_booking = $request->password_booking;
        $confi->password_airbnb = $request->password_airbnb;
        $confi->user_booking = $request->user_booking;
        $confi->user_airbnb = $request->user_airbnb;
        $confi->save();
        
        return redirect()->route('configuracion.index');
    }
    public function updateReparaciones(Request $request){
        $reparaciones = Reparaciones::all();
        if (count($reparaciones) > 0 ) {
            foreach($reparaciones as $reparacion){
                $reparacion->nombre = $request->nombre;
                $reparacion->telefono = $request->telefono;
                $reparacion->save();
            }
        } else {
            Reparaciones::create([
                'nombre' => $request->nombre,
                'telefono' => $request->telefono
            ]);
        }
        return redirect()->route('configuracion.index');
    }
    public function passBooking(){
        $configuraciones = Configuraciones::first();
        return response()->json([
            'user' => $configuraciones->user_booking,
            'pass' => $configuraciones->password_booking
        ]);
    }
    public function passAirbnb(){
        $configuraciones = Configuraciones::first();
        return response()->json([
            'user' => $configuraciones->user_airbnb,
            'pass' => $configuraciones->password_airbnb
        ]);
    }
    public function updateAnio(Request $request){
        $anio = Anio::first();
        $anio->anio = $request->anio;
        $anio->save();

        Alert::toast('Actualizado', 'success');

        return redirect()->route('configuracion.index');
    }

    public function cierreAnio(Request $request){
        $anio = Anio::first();
        $anio->anio = $request->anio;
        $anio->save();

        Alert::toast('Actualizado', 'success');

        return redirect()->route('configuracion.index');
    }
    public function saldoInicial(Request $request){
        $anio = Anio::first();
        $saldo = $request->saldo_inicial;

        if (!$anio) {
            $nuevoAnio = Anio::create([
                'anio' => Carbon::now()->format('Y'),
                'saldo_inicial' => $saldo,
            ]);
        }else {
            $anio->saldo_inicial = $saldo;
            $anio->save();
        }

        Alert::toast('Actualizado', 'success');
        return redirect()->route('configuracion.index');
    }

    
}
