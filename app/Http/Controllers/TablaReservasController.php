<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\Cliente;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TablaReservasController extends Controller
{
    public function index(Request $request)
    {

        $date = $request->get('date', now()->format('Y-m'));
        $carbonDate   = \Carbon\Carbon::createFromFormat('Y-m', $date);
        $dateObject = Carbon::createFromFormat('Y-m', $date);
        $daysInMonth = $dateObject->daysInMonth;

        $monthName = ucfirst($dateObject->locale('es')->isoFormat('MMMM YYYY')); // Formatea el mes en español
        $year         = $dateObject->year; // año
        $apartamentos = Apartamento::all();

        foreach ($apartamentos as $apartamento) {
            // Obtener reservas que empiezan en el mes y año actual seleccionado
            $reservas = Reserva::where('apartamento_id', $apartamento->id)
                                ->whereMonth('fecha_entrada', $dateObject->month)
                                ->whereYear('fecha_entrada', $dateObject->year)
                                ->orderBy('fecha_entrada', 'asc')
                                ->get();

            $apartamento->reservas = $reservas; // Añadir reservas al apartamento

            foreach ($reservas as $reserva) {
                $reserva->fecha_entrada = Carbon::parse($reserva->fecha_entrada);
                $reserva->fecha_salida = Carbon::parse($reserva->fecha_salida);

                // if (!$reserva->fecha_entrada instanceof Carbon) {
                //     $reserva->fecha_entrada = Carbon::parse($reserva->fecha_entrada);
                // }
            }
        }

        return view('admin.reservas.tabla', compact('apartamentos', 'daysInMonth', 'monthName','carbonDate','date','year'));
    }

}
