<?php

namespace App\Http\Controllers;

use App\Models\Configuraciones;
use Illuminate\Http\Request;

class ConfiguracionesController extends Controller
{
    public function index(){
        $configuraciones = Configuraciones::all();
        return view('admin.configuraciones.index', compact('configuraciones'));
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
}
