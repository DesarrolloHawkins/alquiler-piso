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
            return response('La reserva existe', 200)->json($reserva);
        }
        // Si no existe la reserva
        return response('La reserva no existe', 404);
    }

    public function verificarReserva($reserva){
		//return 'ok';

        // Comprobamos la reserva
        $reservaCheck = Reserva::where('codigo_reserva', $reserva)->first();
        // Si la reserva  existe
        if($reservaCheck != null){
            return response('La reserva existe', 200)->json($reservaCheck);
        }
        // Si no existe la reserva
        return response('La reserva no existe', 404);
    }

    public function comprobarReservaWeb($id){
        // Comprobamos la reserva
        $reserva = Reserva::where('codigo_reserva', $id)->first();
        // Si la reserva  existe
        if($reserva != null){
            return response('La reserva existe', 200)->json($reserva);
        }
        // Si no existe la reserva
        return response('La reserva no existe', 404);
    }
}
