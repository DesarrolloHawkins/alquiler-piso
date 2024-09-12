<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
     public function index(Request $request) {
        $anio = $request->input('anio', Carbon::now()->year); // Obtiene el año actual si no se proporciona uno
        $mes = $request->input('mes'); // El mes es opcional

        // Inicia la consulta para las reservas
        $reservas = Reserva::whereYear('fecha_entrada', $anio);

        // Filtra por mes si se proporcionó
        if ($mes) {
            $reservas->whereMonth('fecha_entrada', $mes);
        }

        // Realiza el conteo de las reservas y la suma de los precios
        $countReservas = $reservas->count();
        $sumPrecio = $reservas->sum('precio');
        $reservas = $reservas->get();
        $mesNombre = $mes ? Carbon::create()->month($mes)->locale('es')->monthName : null;

        // Puede pasar los datos a la vista o retornar una respuesta JSON
        // Retorno a la vista con los datos
        return view('admin.dashboard', compact('countReservas', 'sumPrecio', 'anio', 'mes', 'reservas', 'mesNombre'));
    }
}
