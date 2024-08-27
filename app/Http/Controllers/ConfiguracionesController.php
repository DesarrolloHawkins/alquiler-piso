<?php

namespace App\Http\Controllers;

use App\Models\Anio;
use App\Models\Configuraciones;
use App\Models\EmailNotificaciones;
use App\Models\FormasPago;
use App\Models\LimpiadoraGuardia;
use App\Models\PromptAsistente;
use App\Models\Reparaciones;
use App\Models\User;
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

        $limpiadorasUsers = User::where('inactive', null)->where('role', 'USER')->get();
        $limpiadorasGuardia = LimpiadoraGuardia::all();

        return view('admin.configuraciones.index', compact(
            'configuraciones',
            'reparaciones',
            'anio',
            'anios',
            'formasPago',
            'prompt',
            'emailsNotificaciones',
            'limpiadorasUsers',
            'limpiadorasGuardia'
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

    // Crear reparador
    public function storeReparaciones(Request $request){
        //dd($request->all());
        $data = [
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'lunes' => isset($request->lunes) ? true : null,
            'martes' => isset($request->martes) ? true : null,
            'miercoles' => isset($request->miercoles) ? true : null,
            'jueves' => isset($request->jueves) ? true : null,
            'viernes' => isset($request->viernes) ? true : null,
            'sabado' => isset($request->sabado) ? true : null,
            'domingo' => isset($request->domingo) ? true : null
        ];

        $tecnicoNuevo = Reparaciones::create($data);

        Alert::toast('Tecnico de reparaciones creado correctamente', 'success');
        return redirect()->route('configuracion.index');
    }
    // Actualizar los reparadores
    public function updateReparaciones($id, Request $request){
        $data = [
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'lunes' => isset($request->lunes) ? true : null,
            'martes' => isset($request->martes) ? true : null,
            'miercoles' => isset($request->miercoles) ? true : null,
            'jueves' => isset($request->jueves) ? true : null,
            'viernes' => isset($request->viernes) ? true : null,
            'sabado' => isset($request->sabado) ? true : null,
            'domingo' => isset($request->domingo) ? true : null
        ];

        $reparaciones = Reparaciones::find($id);
        $reparaciones->update($data);
        Alert::toast('Tecnico de reparaciones actualizado correctamente', 'success');
        return redirect()->route('configuracion.index');
    }
    // Crear reparador
    public function storeLimpiadora(Request $request){
        //dd($request->all());
        $data = [
            'user_id' => $request->user_id,
            'telefono' => $request->telefono,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'lunes' => isset($request->lunes) ? true : null,
            'martes' => isset($request->martes) ? true : null,
            'miercoles' => isset($request->miercoles) ? true : null,
            'jueves' => isset($request->jueves) ? true : null,
            'viernes' => isset($request->viernes) ? true : null,
            'sabado' => isset($request->sabado) ? true : null,
            'domingo' => isset($request->domingo) ? true : null
        ];

        $limpiadoraNueva = LimpiadoraGuardia::create($data);

        Alert::toast('Limpiadora de guardia creado correctamente', 'success');
        return redirect()->route('configuracion.index');
    }
    // Actualizar los reparadores
    public function updateLimpiadora($id, Request $request){
        $data = [
            'user_id' => $request->user_id,
            'telefono' => $request->telefono,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'lunes' => isset($request->lunes) ? true : null,
            'martes' => isset($request->martes) ? true : null,
            'miercoles' => isset($request->miercoles) ? true : null,
            'jueves' => isset($request->jueves) ? true : null,
            'viernes' => isset($request->viernes) ? true : null,
            'sabado' => isset($request->sabado) ? true : null,
            'domingo' => isset($request->domingo) ? true : null
        ];

        $limpiadora = LimpiadoraGuardia::find($id);
        $limpiadora->update($data);
        Alert::toast('Limpiadora de guardia actualizado correctamente', 'success');
        return redirect()->route('configuracion.index');
    }

    // Obtener User y Pass de Booking
    public function deleteReparaciones($id){
        $reparaciones = Reparaciones::find($id);
        $reparaciones->delete();
        Alert::toast('Tecnico de reparaciones actualizado correctamente', 'success');
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
