<?php

namespace App\Http\Controllers;

use App\Models\Anio;
use App\Models\Configuraciones;
use App\Models\EmailNotificaciones;
use App\Models\FormasPago;
use App\Models\PromptAsistente;
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
        // Prompr del Asistente IA
        $prompt =  PromptAsistente::all();

        // Emails para notificaciones
        $emailsNotificaciones = EmailNotificaciones::all();

        return view('admin.configuraciones.index', compact(
            'configuraciones',
            'reparaciones',
            'anio',
            'anios',
            'formasPago',
            'prompt',
            'emailsNotificaciones'
        ));
    }

    public function edit($id, Request $request){
        $configuraciones = Configuraciones::all();
        return view('admin.configuraciones.index', compact('configuraciones'));
    }

    // Actualizar usuarios de AIRBNB y BOOKING
    public function update($id, Request $request){
        $confi = Configuraciones::find($id);
        $confi->password_booking = $request->password_booking;
        $confi->password_airbnb = $request->password_airbnb;
        $confi->user_booking = $request->user_booking;
        $confi->user_airbnb = $request->user_airbnb;
        $confi->save();
        
        return redirect()->route('configuracion.index');
    }

    // Actualizar los reparadores
    public function updateReparaciones(Request $request){
        $reparaciones = Reparaciones::all();
        if (count($reparaciones) > 0 ) {
            foreach($reparaciones as $reparacion){
                $reparacion->nombre = $request->nombre;
                $reparacion->telefono = $request->telefono;
                $reparacion->save();
            }
            Alert::toast('Tecnico de reparaciones actualizado correctamente', 'success');

        } else {
            Reparaciones::create([
                'nombre' => $request->nombre,
                'telefono' => $request->telefono
            ]);
            Alert::toast('Tecnico de reparaciones creado correctamente', 'success');

        }
        return redirect()->route('configuracion.index');
    }

    // Obtener User y Pass de Booking
    public function passBooking(){
        $configuraciones = Configuraciones::first();
        return response()->json([
            'user' => $configuraciones->user_booking,
            'pass' => $configuraciones->password_booking
        ]);
    }

    // Obtener User y Pass de Airbnb
    public function passAirbnb(){
        $configuraciones = Configuraciones::first();
        return response()->json([
            'user' => $configuraciones->user_airbnb,
            'pass' => $configuraciones->password_airbnb
        ]);
    }

    // Actualizar año de gestion
    public function updateAnio(Request $request){
        $anio = Anio::first();
        $anio->anio = $request->anio;
        $anio->save();

        Alert::toast('Actualizado', 'success');

        return redirect()->route('configuracion.index');
    }

    // Cierre del año
    public function cierreAnio(Request $request){
        $anio = Anio::first();
        $anio->anio = $request->anio;
        $anio->save();

        Alert::toast('Actualizado', 'success');

        return redirect()->route('configuracion.index');
    }

    // Establecer el Saldo inicial
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

    // Actualizar Prompt
    public function actualizarPrompt(Request  $request) {
        $prompt = PromptAsistente::first();
        if ($prompt != null) {
            $prompt->prompt = $request->prompt;
            $prompt->save();
            Alert::toast('Actualizado', 'success');
            return redirect()->route('configuracion.index');

        }else {
            $promprNew = PromptAsistente::create([
                'prompt' => $request->prompt
            ]);
            Alert::toast('Actualizado', 'success');
            return redirect()->route('configuracion.index');
        }
    }

    // Añadir personas de notificaciones
    public function addEmailNotificaciones(Request $request) {
        // dd($request);
        $crearPersona = EmailNotificaciones::create([
            'email' => $request->email,
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Guardado con correctamente',
            'redirect_url' => route('configuracion.index')
        ]);
    }

    // Borrar Persona de Notificaciones
    public function deleteEmailNotificaciones($id) {
        $persona = EmailNotificaciones::find($id);
        $persona ->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Eliminada con correctamente',
            'redirect_url' => route('configuracion.index')
        ]);
    }

    // Actualizar persona de notificaciones
    public function updateEmailNotificaciones($id, Request $request) {
        $persona = EmailNotificaciones::find($id);
        if (isset($request->telefono)) {
            //dd($request->telefono);
            $telefonoLimpio = $this->preformatPhone($request->telefono);
            $persona->update([
                'telefono' => $telefonoLimpio
            ]);

        } else {
            $persona->update($request->all());
        }
        //$persona ->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Eliminada con correctamente',
            'redirect_url' => route('configuracion.index')
        ]);
    }



    // Preformatear el numero de telefono
    public function preformatPhone($phone)
    {
        // Remove any non-digit characters from the phone number
        $phone = preg_replace('/\D+/', '', $phone);
        return $phone;
    }

    
}
