<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Http\Request;

class ComprobarReserva extends Controller
{
    public function index($estado){

        $reserva = Reserva::where('codigo_reserva', $estado)->first();

        if($reserva != null){
            return true;
        }
        return false;
    }
}
