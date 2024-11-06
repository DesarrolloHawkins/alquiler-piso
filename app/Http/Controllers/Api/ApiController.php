<?php

namespace App\Http\Controllers\Api;

use App\Models\Apartamento;
use App\Models\ChatGpt;
use App\Models\Cliente;
use App\Models\Huesped;
use App\Models\Invoices;
use App\Models\InvoicesReferenceAutoincrement;
use App\Models\MensajeAuto;
use App\Models\Photo;
use App\Models\Reserva;
use App\Services\ChatGptService;
use Carbon\Carbon;
use Carbon\Cli\Invoker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller; // Asegúrate de que esta línea esté presente


class ApiController extends Controller
{
    /**
     * Obtener las reservas para hoy
     */
    public function obtenerReservasHoy(Request $request)
    {
        // Obtener la fecha y la hora actual
        $hoy = Carbon::now();
        $horaLimite = Carbon::createFromTime(14, 0, 0); // Hora límite: 14:00

        // Filtrar las reservas cuya fecha de entrada sea hoy
        $reservas = Reserva::whereDate('fecha_entrada', $hoy->toDateString())
            ->where(function ($query) use ($hoy, $horaLimite) {
                // Excluir las reservas cuya fecha de salida sea hoy y la hora sea mayor a las 14:00
                $query->whereDate('fecha_salida', '>', $hoy->toDateString())
                    ->orWhere(function ($query) use ($hoy, $horaLimite) {
                        $query->whereDate('fecha_salida', $hoy->toDateString())
                            ->whereTime('fecha_salida', '<', $horaLimite->toTimeString());
                    });
            })
            ->get();

        // Verificar si hay reservas y formatear los datos para la respuesta
        if ($reservas->isNotEmpty()) {
            $data = $reservas->map(function ($reserva) {
                return [
                    'codigo_reserva' => $reserva->codigo_reserva,
                    'cliente' => $reserva->cliente['nombre'] == null ? $reserva->cliente->alias : $reserva->cliente['nombre'] . ' ' . $reserva->cliente['apellido1'],
                    'apartamento' => $reserva->apartamento->titulo,
                    'edificio' => isset($reserva->apartamento->edificioName->nombre) ? $reserva->apartamento->edificioName->nombre : 'Edificio Hawkins Suite',
                    'fecha_entrada' => $reserva->fecha_entrada,
                    'clave' => $reserva->apartamento->claves
                ];
            });

            return response()->json($data, 200);
        } else {
            return response()->json('Error, no se encontraron reservas para hoy', 400);
        }
    }

    /**
     * Obtener los apartamentos
     */
    public function obtenerApartamentos()
    {
        $apartamentos = Apartamento::all();
        return response()->json($apartamentos);
    }

    /**
     * Obtener los apartamentos disponibles
     */
    public function obtenerApartamentosDisponibles(Request $request)
    {
        // Obtener la fecha y la hora actual
        $hoy = Carbon::now();

        // Obtener los IDs de los apartamentos que están reservados hoy
        $reservasHoy = Reserva::whereDate('fecha_entrada', '<=', $hoy->toDateString())
            ->whereDate('fecha_salida', '>=', $hoy->toDateString())
            ->pluck('apartamento_id');

        // Obtener los apartamentos que no están en las reservas de hoy
        $apartamentosDisponibles = Apartamento::whereNotIn('id', $reservasHoy)->get();

        // Formatear los datos para la respuesta
        $data = $apartamentosDisponibles->map(function ($apartamento) {
            return [
                'id' => $apartamento->id,
                'titulo' => $apartamento->titulo,
                'descripcion' => $apartamento->descripcion, // Asegúrate de que este campo existe en tu modelo
                'edificio' => $apartamento->edificioName->nombre ?? 'Edificio Hawkins Suite', // Agregar el nombre del edificio
                'claves' => $apartamento->claves,
                // Agrega más campos según lo necesites
            ];
        });

        return response()->json($data, 200);
    }


    /**
     * Averias tecnico
     */
    public function averiasTecnico(Request $request)
    {
        return response()->json('Averias tecnico enviada', 200);
    }
    /**
     * Averias tecnico
     */
    public function equipoLimpieza(Request $request)
    {
        return response()->json('Equipo de limpieza enviada', 200);
    }


}

