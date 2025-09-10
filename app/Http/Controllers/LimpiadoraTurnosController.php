<?php

namespace App\Http\Controllers;

use App\Models\TurnoTrabajo;
use App\Models\TareaAsignada;
use App\Models\TipoTarea;
use App\Models\HorasExtras;
use App\Models\EmpleadaHorario;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LimpiadoraTurnosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->role !== 'LIMPIEZA') {
                abort(403, 'No tienes permisos para acceder a esta sección');
            }
            return $next($request);
        });
    }

    /**
     * Mostrar listado de turnos de la limpiadora logueada
     */
    public function index(Request $request)
    {
        $fecha = $request->get('fecha', today()->format('Y-m-d'));
        $fechaCarbon = Carbon::parse($fecha);
        
        // Obtener turnos de la limpiadora logueada
        $turnos = TurnoTrabajo::porFecha($fechaCarbon)
            ->where('user_id', Auth::id())
            ->with(['tareasAsignadas.tipoTarea', 'tareasAsignadas.apartamento', 'tareasAsignadas.zonaComun'])
            ->orderBy('hora_inicio')
            ->get();

        // Estadísticas del día para la limpiadora
        $estadisticas = [
            'total_turnos' => $turnos->count(),
            'turnos_completados' => $turnos->where('estado', 'completado')->count(),
            'turnos_en_progreso' => $turnos->where('estado', 'en_progreso')->count(),
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
                return $turno->tareasAsignadas->sum(function($tarea) {
                    return $tarea->tipoTarea->tiempo_estimado_minutos;
                });
            })
        ];

        return view('limpiadora.turnos.index', compact('turnos', 'fecha', 'estadisticas'));
    }

    /**
     * Mostrar detalles de un turno específico
     */
    public function show(TurnoTrabajo $turno)
    {
        // Verificar que el turno pertenece a la limpiadora logueada
        if ($turno->user_id !== Auth::id()) {
            abort(403, 'No tienes permisos para ver este turno');
        }

        $turno->load([
            'tareasAsignadas.tipoTarea', 
            'tareasAsignadas.apartamento', 
            'tareasAsignadas.zonaComun'
        ]);

        return view('limpiadora.turnos.show', compact('turno'));
    }

    /**
     * Iniciar un turno
     */
    public function iniciarTurno(TurnoTrabajo $turno)
    {
        // Verificar que el turno pertenece a la limpiadora logueada
        if ($turno->user_id !== Auth::id()) {
            abort(403, 'No tienes permisos para iniciar este turno');
        }

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
     * Finalizar un turno
     */
    public function finalizarTurno(Request $request, TurnoTrabajo $turno)
    {
        // Verificar que el turno pertenece a la limpiadora logueada
        if ($turno->user_id !== Auth::id()) {
            abort(403, 'No tienes permisos para finalizar este turno');
        }

        $request->validate([
            'horas_trabajadas' => 'nullable|numeric|min:0|max:24',
            'observaciones' => 'nullable|string|max:1000',
            'motivo_horas_extras' => 'nullable|string|max:500'
        ]);

        try {
            $turno->finalizarTurno($request->horas_trabajadas);
            
            if ($request->observaciones) {
                $turno->update(['observaciones' => $request->observaciones]);
            }

            // Verificar si hay horas extras y crearlas automáticamente
            $this->procesarHorasExtras($turno, $request->motivo_horas_extras);
            
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
     * Procesar horas extras automáticamente
     */
    private function procesarHorasExtras(TurnoTrabajo $turno, $motivo = null)
    {
        try {
            // Obtener horas contratadas de la empleada
            $empleadaHorario = EmpleadaHorario::where('user_id', $turno->user_id)->first();
            $horasContratadas = $empleadaHorario ? $empleadaHorario->horas_contratadas_dia : 8.0;
            
            $horasTrabajadas = $turno->horas_trabajadas ?? 0;
            
            // Solo crear horas extras si hay exceso
            if ($horasTrabajadas > $horasContratadas) {
                $horasExtras = $horasTrabajadas - $horasContratadas;
                
                // Verificar si ya existe un registro de horas extras para este turno
                $existeHorasExtras = HorasExtras::where('turno_id', $turno->id)->exists();
                
                if (!$existeHorasExtras) {
                    HorasExtras::create([
                        'user_id' => $turno->user_id,
                        'turno_id' => $turno->id,
                        'fecha' => $turno->fecha,
                        'horas_contratadas' => $horasContratadas,
                        'horas_trabajadas' => $horasTrabajadas,
                        'horas_extras' => $horasExtras,
                        'motivo' => $motivo ?? 'Trabajo adicional requerido',
                        'estado' => HorasExtras::ESTADO_PENDIENTE
                    ]);
                    
                    Log::info("Horas extras creadas para turno {$turno->id}: {$horasExtras}h extras");
                }
            }
        } catch (\Exception $e) {
            Log::error('Error procesando horas extras: ' . $e->getMessage());
        }
    }

    /**
     * Iniciar una tarea específica
     */
    public function iniciarTarea(TareaAsignada $tarea)
    {
        // Verificar que la tarea pertenece a un turno de la limpiadora logueada
        if ($tarea->turno->user_id !== Auth::id()) {
            abort(403, 'No tienes permisos para iniciar esta tarea');
        }

        try {
            $tarea->iniciarTarea();
            
            return response()->json([
                'success' => true,
                'message' => 'Tarea iniciada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error iniciando tarea: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar la tarea: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Completar una tarea específica
     */
    public function completarTarea(Request $request, TareaAsignada $tarea)
    {
        // Verificar que la tarea pertenece a un turno de la limpiadora logueada
        if ($tarea->turno->user_id !== Auth::id()) {
            abort(403, 'No tienes permisos para completar esta tarea');
        }

        $request->validate([
            'observaciones' => 'nullable|string|max:500'
        ]);

        try {
            $tarea->completarTarea($request->observaciones);
            
            return response()->json([
                'success' => true,
                'message' => 'Tarea completada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error completando tarea: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al completar la tarea: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de la limpiadora
     */
    public function estadisticas(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', now()->startOfWeek());
        $fechaFin = $request->get('fecha_fin', now()->endOfWeek());
        
        $turnos = TurnoTrabajo::where('user_id', Auth::id())
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->with(['tareasAsignadas'])
            ->get();
            
        $estadisticas = [
            'total_turnos' => $turnos->count(),
            'turnos_completados' => $turnos->where('estado', 'completado')->count(),
            'turnos_en_progreso' => $turnos->where('estado', 'en_progreso')->count(),
            'total_tareas' => $turnos->sum(function($turno) {
                return $turno->tareasAsignadas->count();
            }),
            'tareas_completadas' => $turnos->sum(function($turno) {
                return $turno->tareasAsignadas->where('estado', 'completada')->count();
            }),
            'horas_trabajadas' => $turnos->sum('horas_trabajadas'),
            'por_dia' => $turnos->groupBy(function($turno) {
                return $turno->fecha->format('Y-m-d');
            })->map(function($turnosDia) {
                return [
                    'fecha' => $turnosDia->first()->fecha->format('d/m/Y'),
                    'turnos' => $turnosDia->count(),
                    'tareas_completadas' => $turnosDia->sum(function($turno) {
                        return $turno->tareasAsignadas->where('estado', 'completada')->count();
                    }),
                    'horas_trabajadas' => $turnosDia->sum('horas_trabajadas')
                ];
            })
        ];
        
        return response()->json($estadisticas);
    }
}
