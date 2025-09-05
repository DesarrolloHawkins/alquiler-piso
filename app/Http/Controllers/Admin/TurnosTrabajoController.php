<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TurnoTrabajo;
use App\Models\EmpleadaHorario;
use App\Models\TipoTarea;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class TurnosTrabajoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $fecha = $request->get('fecha', today()->format('Y-m-d'));
        $fechaCarbon = Carbon::parse($fecha);
        
        $turnos = TurnoTrabajo::porFecha($fechaCarbon)
            ->with(['user', 'tareasAsignadas.tipoTarea', 'tareasAsignadas.apartamento', 'tareasAsignadas.zonaComun'])
            ->orderBy('hora_inicio')
            ->get();
            
        $empleadasDisponibles = EmpleadaHorario::disponiblesHoy()
            ->with('user')
            ->get();
            
        $tiposTareas = TipoTarea::activos()->get();
        
        // Estadísticas del día
        $estadisticas = [
            'total_turnos' => $turnos->count(),
            'total_tareas' => $turnos->sum(function($turno) {
                return $turno->tareasAsignadas->count();
            }),
            'tareas_completadas' => $turnos->sum(function($turno) {
                return $turno->tareasAsignadas->where('estado', 'completada')->count();
            }),
            'tareas_pendientes' => $turnos->sum(function($turno) {
                return $turno->tareasAsignadas->whereIn('estado', ['pendiente', 'en_progreso'])->count();
            }),
            'tiempo_estimado_total' => $turnos->sum(function($turno) {
                // Obtener jornada contratada de la empleada
                $empleadaHorario = EmpleadaHorario::where('user_id', $turno->user_id)->first();
                $jornadaContratadaMinutos = $empleadaHorario ? $empleadaHorario->horas_contratadas_dia * 60 : 480; // Default 8h
                
                // Calcular tiempo total de tareas asignadas
                $tiempoTotalTareas = $turno->tareasAsignadas->sum(function($tarea) {
                    return $tarea->tipoTarea->tiempo_estimado_minutos;
                });
                
                // Retornar el menor entre tiempo total y jornada contratada
                return min($tiempoTotalTareas, $jornadaContratadaMinutos);
            })
        ];
        
        return view('admin.turnos.index', compact(
            'turnos', 
            'empleadasDisponibles', 
            'tiposTareas', 
            'fecha', 
            'estadisticas'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $empleadas = User::where('role', 'LIMPIEZA')
            ->where('inactive', null)
            ->get();
            
        $tiposTareas = TipoTarea::activos()->get();
        
        return view('admin.turnos.create', compact('empleadas', 'tiposTareas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'tareas' => 'required|array|min:1',
            'tareas.*.tipo_tarea_id' => 'required|exists:tipos_tareas,id',
            'tareas.*.apartamento_id' => 'nullable|exists:apartamentos,id',
            'tareas.*.zona_comun_id' => 'nullable|exists:zona_comuns,id',
            'tareas.*.prioridad' => 'required|integer|min:1|max:10',
            'tareas.*.orden' => 'required|integer|min:1'
        ]);

        try {
            $turno = TurnoTrabajo::create([
                'fecha' => $request->fecha,
                'user_id' => $request->user_id,
                'hora_inicio' => $request->hora_inicio,
                'hora_fin' => $request->hora_fin,
                'estado' => 'programado',
                'fecha_creacion' => now()
            ]);

            // Crear tareas asignadas
            foreach ($request->tareas as $tarea) {
                $turno->tareasAsignadas()->create([
                    'tipo_tarea_id' => $tarea['tipo_tarea_id'],
                    'apartamento_id' => $tarea['apartamento_id'] ?? null,
                    'zona_comun_id' => $tarea['zona_comun_id'] ?? null,
                    'prioridad_calculada' => $tarea['prioridad'],
                    'orden_ejecucion' => $tarea['orden'],
                    'estado' => 'pendiente'
                ]);
            }

            return redirect()->route('admin.turnos.index', ['fecha' => $request->fecha])
                ->with('success', 'Turno creado exitosamente');

        } catch (\Exception $e) {
            Log::error('Error creando turno: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el turno: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TurnoTrabajo $turno)
    {
        $turno->load([
            'user', 
            'tareasAsignadas.tipoTarea', 
            'tareasAsignadas.apartamento', 
            'tareasAsignadas.zonaComun'
        ]);
        
        return view('admin.turnos.show', compact('turno'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TurnoTrabajo $turno)
    {
        $empleadas = User::where('role', 'LIMPIEZA')
            ->where('inactive', null)
            ->get();
            
        $tiposTareas = TipoTarea::activos()->get();
        
        $turno->load('tareasAsignadas');
        
        return view('admin.turnos.edit', compact('turno', 'empleadas', 'tiposTareas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TurnoTrabajo $turno)
    {
        $request->validate([
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'estado' => 'required|in:programado,en_progreso,completado,ausente',
            'observaciones' => 'nullable|string|max:1000'
        ]);

        try {
            $turno->update([
                'hora_inicio' => $request->hora_inicio,
                'hora_fin' => $request->hora_fin,
                'estado' => $request->estado,
                'observaciones' => $request->observaciones
            ]);

            return redirect()->route('admin.turnos.show', $turno)
                ->with('success', 'Turno actualizado exitosamente');

        } catch (\Exception $e) {
            Log::error('Error actualizando turno: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el turno: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TurnoTrabajo $turno)
    {
        try {
            $fecha = $turno->fecha->format('Y-m-d');
            $turno->delete();

            return redirect()->route('admin.turnos.index', ['fecha' => $fecha])
                ->with('success', 'Turno eliminado exitosamente');

        } catch (\Exception $e) {
            Log::error('Error eliminando turno: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al eliminar el turno: ' . $e->getMessage());
        }
    }

    /**
     * Generar turnos automáticamente para una fecha
     */
    public function generarTurnos(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'forzar' => 'boolean',
            'usar_ia' => 'boolean',
            'tipo_ia' => 'in:real,simulada'
        ]);

        try {
            $fecha = $request->fecha;
            $forzar = $request->boolean('forzar', false);
            $usarIA = $request->boolean('usar_ia', false);
            $tipoIA = $request->get('tipo_ia', 'real');
            
            // Construir comando según opciones
            $comando = 'turnos:generar';
            $argumentos = ['fecha' => $fecha];
            $opciones = [];
            
            if ($forzar) {
                $opciones['--force'] = true;
            }
            
            if ($usarIA) {
                if ($tipoIA === 'simulada') {
                    $opciones['--test-ia'] = true;
                } else {
                    $opciones['--ia'] = true;
                }
            }
            
            // Capturar la salida del comando
            ob_start();
            $exitCode = Artisan::call($comando, array_merge($argumentos, $opciones));
            $output = ob_get_clean();
            
            // También obtener la salida de Artisan
            $artisanOutput = Artisan::output();

            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Turnos generados exitosamente',
                    'output' => $artisanOutput ?: $output
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al generar los turnos',
                    'output' => $artisanOutput ?: $output
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error generando turnos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar los turnos: ' . $e->getMessage(),
                'output' => $e->getMessage()
            ]);
        }
    }

    /**
     * Iniciar turno
     */
    public function iniciarTurno(TurnoTrabajo $turno)
    {
        try {
            $turno->iniciarTurno();
            
            return response()->json([
                'success' => true,
                'message' => 'Turno iniciado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error iniciando turno: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar el turno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalizar turno
     */
    public function finalizarTurno(Request $request, TurnoTrabajo $turno)
    {
        $request->validate([
            'horas_trabajadas' => 'nullable|numeric|min:0|max:24',
            'observaciones' => 'nullable|string|max:1000'
        ]);

        try {
            $turno->finalizarTurno($request->horas_trabajadas);
            
            if ($request->observaciones) {
                $turno->update(['observaciones' => $request->observaciones]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Turno finalizado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error finalizando turno: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al finalizar el turno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de turnos
     */
    public function estadisticas(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfWeek());
        $fechaFin = $request->get('fecha_fin', now()->endOfWeek());
        
        $turnos = TurnoTrabajo::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->with(['user', 'tareasAsignadas'])
            ->get();
            
        $estadisticas = [
            'total_turnos' => $turnos->count(),
            'turnos_completados' => $turnos->where('estado', 'completado')->count(),
            'turnos_en_progreso' => $turnos->where('estado', 'en_progreso')->count(),
            'turnos_ausentes' => $turnos->where('estado', 'ausente')->count(),
            'total_tareas' => $turnos->sum(function($turno) {
                return $turno->tareasAsignadas->count();
            }),
            'tareas_completadas' => $turnos->sum(function($turno) {
                return $turno->tareasAsignadas->where('estado', 'completada')->count();
            }),
            'horas_trabajadas' => $turnos->sum('horas_trabajadas'),
            'por_empleada' => $turnos->groupBy('user_id')->map(function($turnosEmpleada) {
                $empleada = $turnosEmpleada->first()->user;
                return [
                    'nombre' => $empleada->name,
                    'turnos' => $turnosEmpleada->count(),
                    'horas_trabajadas' => $turnosEmpleada->sum('horas_trabajadas'),
                    'tareas_completadas' => $turnosEmpleada->sum(function($turno) {
                        return $turno->tareasAsignadas->where('estado', 'completada')->count();
                    })
                ];
            })
        ];
        
        return response()->json($estadisticas);
    }
}