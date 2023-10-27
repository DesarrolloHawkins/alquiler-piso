<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Http\Request;

class ComprobarReserva extends Controller
{
    public function index($estado){
        // Comprobamos la reserva
        $reserva = Reserva::where('codigo_reserva', $estado)->first();
        // Si la reserva  existe
        if($reserva != null){
            return response('La reserva existe', 200);
        }
        // Si no existe la reserva
        return response('La reserva no existe', 404);
    }

    public function verificarReserva(Request $request){
		//return 'ok';

        // Obtenemos los dato de la Reserva
        $data = $request->all();
        // Comprobamos si existe
        $reserva = Reserva::where('codigo_reserva', $data['codigo_reserva'])->first();

        if ($reserva != null) {
            return response('La reserva existe', 404);
        }
        
        return response('No existe la reserva', 200);
    }

    public function comprobarReservaWeb($id){
        // Comprobamos la reserva
        $reserva = Reserva::where('codigo_reserva', $id)->first();
        // Si la reserva  existe
        if($reserva != null){
            return response('La reserva existe', 200);
        }
        // Si no existe la reserva
        return response('La reserva no existe', 404);
    }
}
