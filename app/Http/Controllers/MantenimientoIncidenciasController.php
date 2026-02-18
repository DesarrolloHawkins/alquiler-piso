<?php

namespace App\Http\Controllers;

use App\Models\Incidencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MantenimientoIncidenciasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:MANTENIMIENTO');
    }

    /**
     * Listado de todas las incidencias (mismo layout que el dashboard).
     */
    public function index(Request $request)
    {
        $query = Incidencia::with(['apartamento', 'zonaComun'])
            ->orderByRaw("CASE WHEN prioridad = 'urgente' THEN 1 WHEN prioridad = 'alta' THEN 2 WHEN prioridad = 'media' THEN 3 ELSE 4 END")
            ->orderBy('created_at', 'desc');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $incidencias = $query->paginate(15);

        $estadisticas = [
            'pendientes' => Incidencia::where('estado', 'pendiente')->count(),
            'en_proceso' => Incidencia::where('estado', 'en_proceso')->count(),
            'resueltas' => Incidencia::where('estado', 'resuelta')->count(),
        ];

        return view('mantenimiento.incidencias.index', compact('incidencias', 'estadisticas'));
    }

    /**
     * Detalle de una incidencia.
     */
    public function show(Incidencia $incidencia)
    {
        $incidencia->load(['apartamento', 'zonaComun', 'empleada', 'limpieza']);

        return view('mantenimiento.incidencias.show', compact('incidencia'));
    }

    /**
     * Marcar incidencia como resuelta (desde mantenimiento).
     */
    public function resolver(Request $request, Incidencia $incidencia)
    {
        $validator = Validator::make($request->all(), [
            'solucion' => 'required|string|max:2000',
        ], [
            'solucion.required' => 'Indica qué se ha hecho para resolver la incidencia.',
            'solucion.max' => 'La solución no puede superar 2000 caracteres.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = Auth::user();

            $incidencia->update([
                'estado' => 'resuelta',
                'solucion' => $request->solucion,
                'admin_resuelve_id' => $user->id,
                'fecha_resolucion' => now(),
            ]);

            return redirect()->route('mantenimiento.incidencias.show', $incidencia)
                ->with('status', 'Incidencia marcada como resuelta correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al resolver: ' . $e->getMessage());
        }
    }
}
