<?php

namespace App\Http\Controllers;

use App\Models\Incidencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
     * Añadir fotos a una incidencia existente (permitido en cualquier estado)
     * Cualquier usuario autenticado puede añadir fotos
     */
    public function addPhotos(Request $request, Incidencia $incidencia)
    {
        // Cualquier usuario autenticado puede añadir fotos a cualquier incidencia
        $validator = Validator::make($request->all(), [
            'fotos.*' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ], [
            'fotos.*.required' => 'Debes seleccionar al menos una foto',
            'fotos.*.image' => 'Los archivos deben ser imágenes',
            'fotos.*.max' => 'Las imágenes no pueden superar 2MB'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Error al validar las fotos');
        }

        try {
            // Procesar nuevas fotos
            $fotos = $incidencia->fotos ?? [];
            if ($request->hasFile('fotos')) {
                foreach ($request->file('fotos') as $foto) {
                    $path = $foto->store('incidencias', 'public');
                    $fotos[] = $path;
                }
            }

            // Actualizar solo las fotos
            $incidencia->update([
                'fotos' => $fotos
            ]);

            Log::info('Fotos añadidas a incidencia desde mantenimiento', [
                'incidencia_id' => $incidencia->id,
                'user_id' => Auth::id(),
                'fotos_count' => count($fotos)
            ]);

            return redirect()->route('mantenimiento.incidencias.show', $incidencia)
                ->with('status', 'Fotos añadidas correctamente');

        } catch (\Exception $e) {
            Log::error('Error al añadir fotos a incidencia desde mantenimiento', [
                'incidencia_id' => $incidencia->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al añadir las fotos: ' . $e->getMessage());
        }
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
