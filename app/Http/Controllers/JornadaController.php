<?php

namespace App\Http\Controllers;

use App\Models\Fichaje;
use Illuminate\Http\Request;
use App\Models\User;

class JornadaController extends Controller
{
    public function index(Request $request) {
        $anio = app('anio'); // Obtiene el año global
        $users = User::where('role', 'USER')->get();
        
        // Obtener los parámetros de la solicitud
        $fecha_inicio = $request->fecha_inicio;
        $mes = $request->mes;

        foreach ($users as $user) {
            // Iniciar la consulta para fichajes del usuario
            $query = Fichaje::where('user_id', $user->id);
    
            if (!empty($fecha_inicio)) {
                // Filtrar fichajes por fecha específica dentro del año actual
                $query->whereDate('created_at', '=', $fecha_inicio);
            } elseif (!empty($mes)) {
                // Filtrar fichajes por mes dentro del año actual
                $query->whereMonth('created_at', '=', $mes);
            }
    
            // Asegurar que solo se consideren fichajes dentro del año actual
            $query->whereYear('created_at', '=', $anio);
    
            // Ejecutar la consulta y asignar resultados
            $user->jornada = $query->get();
        }

        return view('admin.jornada.index', compact('users'));
    }
}
