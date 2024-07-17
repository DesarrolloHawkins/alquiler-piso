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
        $confi->save();
        
        return redirect()->route('configuracion.index');
    }
}
