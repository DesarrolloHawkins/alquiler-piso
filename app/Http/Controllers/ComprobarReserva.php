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
}
