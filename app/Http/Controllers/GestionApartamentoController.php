<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\ApartamentoLimpieza;
use App\Models\Fichaje;
use App\Models\Pausa;
use App\Models\GestionApartamento;
use App\Models\LimpiezaFondo;
use App\Models\Reserva;
use App\Models\TurnoTrabajo;
use App\Models\TareaAsignada;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Auth; // Añade esta línea
use App\Models\Checklist;
use App\Models\ApartamentoLimpiezaItem;
use App\Services\AlertService;

class GestionApartamentoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */


    public function index()
    {
        $user = Auth::user();
        $hoy = Carbon::today();
        
        // Verificar si hay turnos generados para hoy
        $turnoHoy = TurnoTrabajo::where('user_id', $user->id)
            ->whereDate('fecha', $hoy)
            ->with(['tareasAsignadas.tipoTarea', 'tareasAsignadas.apartamento', 'tareasAsignadas.zonaComun'])
            ->first();
        
        // Si hay turno generado, usar el nuevo sistema
        if ($turnoHoy) {
            return $this->indexConTurnos($turnoHoy, $hoy);
        }
        
        // Si no hay turno, usar el sistema antiguo
        $reservasPendientes = Reserva::apartamentosPendiente();
        
        // Cargar la relación siguienteReserva con campos de niños para cada reserva pendiente
        foreach ($reservasPendientes as $reserva) {
            if (!$reserva->limpieza_fondo) {
                // Solo para reservas reales, no para limpiezas de fondo
                // Verificar que la relación existe antes de cargarla
                if (method_exists($reserva, 'siguienteReserva')) {
                    try {
                        $reserva->load(['siguienteReserva' => function($query) {
                            $query->select('id', 'apartamento_id', 'fecha_entrada', 'fecha_salida', 'numero_personas', 'numero_ninos', 'edades_ninos', 'notas_ninos', 'codigo_reserva');
                        }]);
                    } catch (Exception $e) {
                        // Si hay error al cargar la relación, continuar sin ella
                        $reserva->siguienteReserva = null;
                    }
                }
                
                // Obtener manualmente la reserva que entra hoy si es la misma fecha
                try {
                    $reservaEntraHoy = \App\Models\Reserva::where('apartamento_id', $reserva->apartamento_id)
                        ->where('fecha_entrada', $reserva->fecha_salida)
                        ->where('id', '!=', $reserva->id)
                        ->where(function($query) {
                            $query->where('estado_id', '!=', 4)
                                  ->orWhereNull('estado_id');
                        })
                        ->select('id', 'apartamento_id', 'fecha_entrada', 'fecha_salida', 'numero_personas', 'numero_ninos', 'edades_ninos', 'notas_ninos', 'codigo_reserva')
                        ->first();
                    
                    if ($reservaEntraHoy) {
                        $reserva->reserva_entra_hoy = $reservaEntraHoy;
                    }
                } catch (Exception $e) {
                    // Si hay error en la consulta, continuar sin esta información
                    $reserva->reserva_entra_hoy = null;
                }
            }
        }
        
        $reservasOcupados = Reserva::apartamentosOcupados();
        $reservasSalida = Reserva::apartamentosSalida();
        // $reservasLimpieza = Reserva::apartamentosLimpiados();
        $reservasLimpieza = ApartamentoLimpieza::apartamentosLimpiados()->with(['apartamento', 'zonaComun'])->get();
        $reservasEnLimpieza = ApartamentoLimpieza::apartamentosEnLimpiados()->with(['apartamento', 'zonaComun'])->get();

        // Obtener información de la siguiente reserva para las limpiezas en proceso
        foreach ($reservasEnLimpieza as $limpieza) {
            try {
                // Buscar la siguiente reserva para este apartamento
                $siguienteReserva = \App\Models\Reserva::where('apartamento_id', $limpieza->apartamento_id)
                    ->where('fecha_entrada', '>', now()->toDateString())
                    ->where(function($query) {
                        $query->where('estado_id', '!=', 4)
                              ->orWhereNull('estado_id');
                    })
                    ->orderBy('fecha_entrada', 'asc')
                    ->select('id', 'apartamento_id', 'fecha_entrada', 'fecha_salida', 'numero_personas', 'numero_ninos', 'edades_ninos', 'notas_ninos', 'codigo_reserva')
                    ->first();
                
                if ($siguienteReserva) {
                    $limpieza->siguiente_reserva = $siguienteReserva;
                }
                
                // También buscar si hay una reserva que entra hoy
                $reservaEntraHoy = \App\Models\Reserva::where('apartamento_id', $limpieza->apartamento_id)
                    ->where('fecha_entrada', now()->toDateString())
                    ->where(function($query) {
                        $query->where('estado_id', '!=', 4)
                              ->orWhereNull('estado_id');
                    })
                    ->select('id', 'apartamento_id', 'fecha_entrada', 'fecha_salida', 'numero_personas', 'numero_ninos', 'edades_ninos', 'notas_ninos', 'codigo_reserva')
                    ->first();
                
                if ($reservaEntraHoy) {
                    $limpieza->reserva_entra_hoy = $reservaEntraHoy;
                }
            } catch (Exception $e) {
                // Si hay error, continuar sin esta información
                $limpieza->siguiente_reserva = null;
                $limpieza->reserva_entra_hoy = null;
            }
        }

        // Obtener información de la siguiente reserva para las limpiezas completadas
        foreach ($reservasLimpieza as $limpieza) {
            try {
                // Buscar la siguiente reserva para este apartamento
                $siguienteReserva = \App\Models\Reserva::where('apartamento_id', $limpieza->apartamento_id)
                    ->where('fecha_entrada', '>', now()->toDateString())
                    ->where(function($query) {
                        $query->where('estado_id', '!=', 4)
                              ->orWhereNull('estado_id');
                    })
                    ->orderBy('fecha_entrada', 'asc')
                    ->select('id', 'apartamento_id', 'fecha_entrada', 'fecha_salida', 'numero_personas', 'numero_ninos', 'edades_ninos', 'notas_ninos', 'codigo_reserva')
                    ->first();
                
                if ($siguienteReserva) {
                    $limpieza->siguiente_reserva = $siguienteReserva;
                }
                
                // También buscar si hay una reserva que entra hoy
                $reservaEntraHoy = \App\Models\Reserva::where('apartamento_id', $limpieza->apartamento_id)
                    ->where('fecha_entrada', now()->toDateString())
                    ->where(function($query) {
                        $query->where('estado_id', '!=', 4)
                              ->orWhereNull('estado_id');
                    })
                    ->select('id', 'apartamento_id', 'fecha_entrada', 'fecha_salida', 'numero_personas', 'numero_ninos', 'edades_ninos', 'notas_ninos', 'codigo_reserva')
                    ->first();
                
                if ($reservaEntraHoy) {
                    $limpieza->reserva_entra_hoy = $reservaEntraHoy;
                }
            } catch (Exception $e) {
                // Si hay error, continuar sin esta información
                $limpieza->siguiente_reserva = null;
                $limpieza->reserva_entra_hoy = null;
            }
        }

        // Obtener apartamentos previstos para mañana (los que SALEN mañana para limpiar)
        $reservasManana = Reserva::where('fecha_salida', now()->addDay()->toDateString())
            ->where(function($query) {
                $query->where('estado_id', '!=', 4)
                      ->orWhereNull('estado_id');
            })
            ->with(['apartamento'])
            ->orderBy('apartamento_id')
            ->get();

        // Para cada apartamento que sale mañana, obtener información de la siguiente reserva
        foreach ($reservasManana as $reserva) {
            try {
                // Buscar la siguiente reserva para este apartamento
                $siguienteReserva = \App\Models\Reserva::where('apartamento_id', $reserva->apartamento_id)
                    ->where('fecha_entrada', '>', $reserva->fecha_salida)
                    ->where(function($query) {
                        $query->where('estado_id', '!=', 4)
                              ->orWhereNull('estado_id');
                    })
                    ->orderBy('fecha_entrada', 'asc')
                    ->select('id', 'apartamento_id', 'fecha_entrada', 'fecha_salida', 'numero_personas', 'numero_ninos', 'edades_ninos', 'notas_ninos', 'codigo_reserva')
                    ->first();
                
                if ($siguienteReserva) {
                    $reserva->siguiente_reserva = $siguienteReserva;
                }
                
                // También buscar si hay una reserva que entra mañana mismo
                $reservaEntraManana = \App\Models\Reserva::where('apartamento_id', $reserva->apartamento_id)
                    ->where('fecha_entrada', now()->addDay()->toDateString())
                    ->where(function($query) {
                        $query->where('estado_id', '!=', 4)
                              ->orWhereNull('estado_id');
                    })
                    ->select('id', 'apartamento_id', 'fecha_entrada', 'fecha_salida', 'numero_personas', 'numero_ninos', 'edades_ninos', 'notas_ninos', 'codigo_reserva')
                    ->first();
                
                if ($reservaEntraManana) {
                    $reserva->reserva_entra_manana = $reservaEntraManana;
                }
            } catch (Exception $e) {
                // Si hay error, continuar sin esta información
                $reserva->siguiente_reserva = null;
                $reserva->reserva_entra_manana = null;
            }
        }

        // Obtener zonas comunes activas que NO estén EN PROCESO de limpieza
        // Las zonas ya limpiadas hoy SÍ pueden aparecer para limpiar de nuevo
        $zonasComunesIdsEnLimpieza = $reservasEnLimpieza->pluck('zona_comun_id')->filter()->toArray();
        
        $zonasComunes = \App\Models\ZonaComun::activas()
            ->ordenadas()
            ->whereNotIn('id', $zonasComunesIdsEnLimpieza) // Solo excluir las EN PROCESO
            ->get();

        $hoy = now()->toDateString();
        $limpiezaFondo = LimpiezaFondo::whereDate('fecha', $hoy)->get();

        // Obtener amenities de consumo para todas las secciones
        $amenities = \App\Models\Amenity::activos()
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get()
            ->groupBy('categoria');

        // Obtener consumos existentes para todas las limpiezas de hoy
        $limpiezaIds = collect([$reservasPendientes, $reservasEnLimpieza, $reservasLimpieza])
            ->flatten()
            ->pluck('id')
            ->filter()
            ->toArray();

        $consumosExistentes = \App\Models\AmenityConsumo::whereIn('limpieza_id', $limpiezaIds)
            ->with('amenity')
            ->get()
            ->groupBy('limpieza_id');

        // Obtener estadísticas del dashboard de limpieza
        $dashboardStats = $this->getDashboardStats();

        return view('gestion.index', compact(
            'reservasPendientes',
            'reservasOcupados',
            'reservasSalida',
            'reservasLimpieza',
            'reservasEnLimpieza', 
            'limpiezaFondo', 
            'zonasComunes', 
            'reservasManana',
            'amenities',
            'consumosExistentes',
            'dashboardStats'
        ));
    }
    
    /**
     * Mostrar gestión con el nuevo sistema de turnos
     */
    private function indexConTurnos($turnoHoy, $hoy)
    {
        $user = Auth::user();
        
        // Obtener tareas asignadas ordenadas por prioridad y orden de ejecución
        $tareasAsignadas = $turnoHoy->tareasAsignadas()
            ->with(['tipoTarea', 'apartamento', 'zonaComun'])
            ->orderBy('prioridad_calculada', 'desc')
            ->orderBy('orden_ejecucion', 'asc')
            ->get();
        
        // Preparar datos para la vista usando el formato del sistema antiguo
        $reservasPendientes = collect();
        $reservasEnLimpieza = collect();
        $reservasLimpieza = collect();
        
        // Convertir tareas asignadas al formato esperado por la vista
        foreach ($tareasAsignadas as $tarea) {
            if ($tarea->apartamento_id) {
                // Es una tarea de apartamento
                $apartamento = $tarea->apartamento;
                if ($apartamento) {
                    // Crear un objeto similar a Reserva para compatibilidad
                    $reserva = new \stdClass();
                    $reserva->id = $tarea->id;
                    $reserva->apartamento_id = $apartamento->id;
                    $reserva->apartamento = $apartamento;
                    $reserva->codigo_reserva = 'TAREA-' . $tarea->id;
                    $reserva->numero_personas = 0; // No aplicable para tareas
                    $reserva->fecha_salida = $hoy->toDateString();
                    $reserva->fecha_entrada = $hoy->toDateString();
                    $reserva->limpieza_fondo = false;
                    $reserva->tarea_asignada = $tarea;
                    $reserva->tipo_tarea = $tarea->tipoTarea;
                    $reserva->prioridad = $tarea->prioridad_calculada;
                    $reserva->orden_ejecucion = $tarea->orden_ejecucion;
                    $reserva->estado = $tarea->estado;
                    $reserva->tiempo_estimado = $tarea->tipoTarea->tiempo_estimado_minutos;
                    
                    // Agregar a la colección apropiada según el estado
                    if ($tarea->estado === 'pendiente') {
                        $reservasPendientes->push($reserva);
                    } elseif ($tarea->estado === 'en_progreso') {
                        $reservasEnLimpieza->push($reserva);
                    } elseif ($tarea->estado === 'completada') {
                        $reservasLimpieza->push($reserva);
                    }
                }
            } elseif ($tarea->zona_comun_id) {
                // Es una tarea de zona común
                $zonaComun = $tarea->zonaComun;
                if ($zonaComun) {
                    $reserva = new \stdClass();
                    $reserva->id = $tarea->id;
                    $reserva->zona_comun_id = $zonaComun->id;
                    $reserva->zonaComun = $zonaComun;
                    $reserva->codigo_reserva = 'ZONA-' . $tarea->id;
                    $reserva->numero_personas = 0;
                    $reserva->fecha_salida = $hoy->toDateString();
                    $reserva->fecha_entrada = $hoy->toDateString();
                    $reserva->limpieza_fondo = false;
                    $reserva->tarea_asignada = $tarea;
                    $reserva->tipo_tarea = $tarea->tipoTarea;
                    $reserva->prioridad = $tarea->prioridad_calculada;
                    $reserva->orden_ejecucion = $tarea->orden_ejecucion;
                    $reserva->estado = $tarea->estado;
                    $reserva->tiempo_estimado = $tarea->tipoTarea->tiempo_estimado_minutos;
                    
                    if ($tarea->estado === 'pendiente') {
                        $reservasPendientes->push($reserva);
                    } elseif ($tarea->estado === 'en_progreso') {
                        $reservasEnLimpieza->push($reserva);
                    } elseif ($tarea->estado === 'completada') {
                        $reservasLimpieza->push($reserva);
                    }
                }
            } else {
                // Es una tarea general
                $reserva = new \stdClass();
                $reserva->id = $tarea->id;
                $reserva->apartamento_id = null;
                $reserva->zona_comun_id = null;
                $reserva->codigo_reserva = 'GENERAL-' . $tarea->id;
                $reserva->numero_personas = 0;
                $reserva->fecha_salida = $hoy->toDateString();
                $reserva->fecha_entrada = $hoy->toDateString();
                $reserva->limpieza_fondo = false;
                $reserva->tarea_asignada = $tarea;
                $reserva->tipo_tarea = $tarea->tipoTarea;
                $reserva->prioridad = $tarea->prioridad_calculada;
                $reserva->orden_ejecucion = $tarea->orden_ejecucion;
                $reserva->estado = $tarea->estado;
                $reserva->tiempo_estimado = $tarea->tipoTarea->tiempo_estimado_minutos;
                
                if ($tarea->estado === 'pendiente') {
                    $reservasPendientes->push($reserva);
                } elseif ($tarea->estado === 'en_progreso') {
                    $reservasEnLimpieza->push($reserva);
                } elseif ($tarea->estado === 'completada') {
                    $reservasLimpieza->push($reserva);
                }
            }
        }
        
        // Obtener zonas comunes activas
        $zonasComunes = \App\Models\ZonaComun::activas()
            ->ordenadas()
            ->get();
        
        // Obtener amenities
        $amenities = \App\Models\Amenity::activos()
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get()
            ->groupBy('categoria');
        
        // Obtener estadísticas del dashboard
        $dashboardStats = $this->getDashboardStatsConTurnos($turnoHoy, $tareasAsignadas);
        
        // Datos adicionales para compatibilidad
        $reservasOcupados = collect();
        $reservasSalida = collect();
        $limpiezaFondo = collect();
        $reservasManana = collect();
        $consumosExistentes = collect();
        
        return view('gestion.index', compact(
            'reservasPendientes',
            'reservasOcupados',
            'reservasSalida',
            'reservasLimpieza',
            'reservasEnLimpieza', 
            'limpiezaFondo', 
            'zonasComunes', 
            'reservasManana',
            'amenities',
            'consumosExistentes',
            'dashboardStats',
            'turnoHoy'
        ));
    }
    
    /**
     * Obtener estadísticas del dashboard con el nuevo sistema de turnos
     */
    private function getDashboardStatsConTurnos($turnoHoy, $tareasAsignadas)
    {
        $user = Auth::user();
        $hoy = Carbon::today();
        
        // Estadísticas del día
        $limpiezasHoy = $tareasAsignadas->count();
        $limpiezasAsignadas = $tareasAsignadas->count();
        $limpiezasCompletadasHoy = $tareasAsignadas->where('estado', 'completada')->count();
        $limpiezasPendientesHoy = $tareasAsignadas->where('estado', 'pendiente')->count();
        
        // Apartamentos pendientes (tareas de apartamento pendientes)
        $apartamentosPendientes = $tareasAsignadas->where('apartamento_id', '!=', null)
            ->where('estado', 'pendiente')
            ->count();
        
        // Obtener incidencias pendientes del usuario
        $incidenciasPendientes = \DB::table('incidencias')
            ->where('empleada_id', $user->id)
            ->where('estado', 'pendiente')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Obtener estadísticas de la semana
        $inicioSemana = $hoy->copy()->startOfWeek();
        $finSemana = $hoy->copy()->endOfWeek();
        
        $limpiezasSemana = TurnoTrabajo::where('user_id', $user->id)
            ->whereBetween('fecha', [$inicioSemana, $finSemana])
            ->withCount('tareasAsignadas')
            ->get()
            ->sum('tareas_asignadas_count');
        
        $limpiezasCompletadasSemana = TareaAsignada::whereHas('turno', function($query) use ($user, $inicioSemana, $finSemana) {
                $query->where('user_id', $user->id)
                      ->whereBetween('fecha', [$inicioSemana, $finSemana]);
            })
            ->where('estado', 'completada')
            ->count();
        
        $porcentajeSemana = $limpiezasSemana > 0 ? round(($limpiezasCompletadasSemana / $limpiezasSemana) * 100, 1) : 0;
        
        // Obtener fichaje actual (comentado temporalmente por error de columna)
        $fichajeActual = null;
        // $fichajeActual = Fichaje::where('user_id', $user->id)
        //     ->whereNull('fecha_fin')
        //     ->first();
        
        // Estadísticas de calidad (placeholder)
        $estadisticasCalidad = [];
        
        return [
            'limpiezasHoy' => $limpiezasHoy,
            'limpiezasAsignadas' => $limpiezasAsignadas,
            'limpiezasCompletadasHoy' => $limpiezasCompletadasHoy,
            'limpiezasPendientesHoy' => $limpiezasPendientesHoy,
            'apartamentosPendientes' => $apartamentosPendientes,
            'incidenciasPendientes' => $incidenciasPendientes,
            'limpiezasSemana' => $limpiezasSemana,
            'limpiezasCompletadasSemana' => $limpiezasCompletadasSemana,
            'porcentajeSemana' => $porcentajeSemana,
            'fichajeActual' => $fichajeActual,
            'estadisticasCalidad' => $estadisticasCalidad
        ];
    }
    
    /**
     * Obtener información de una tarea asignada
     */
    public function infoTarea(TareaAsignada $tarea)
    {
        try {
            // Verificar que la tarea pertenece al usuario autenticado
            if ($tarea->turno->user_id !== Auth::id()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            
            // Cargar relaciones necesarias
            $tarea->load(['tipoTarea', 'apartamento.edificio', 'zonaComun', 'turno.user']);
            
            // Obtener checklist si existe
            $checklist = $tarea->checklist();
            $itemsChecklist = $tarea->itemChecklists();
            
            // Preparar información de la tarea
            $info = [
                'id' => $tarea->id,
                'tipo_tarea' => $tarea->tipoTarea->nombre,
                'categoria' => $tarea->tipoTarea->categoria,
                'prioridad' => $tarea->prioridad_calculada,
                'orden_ejecucion' => $tarea->orden_ejecucion,
                'tiempo_estimado' => $tarea->tipoTarea->tiempo_estimado_minutos,
                'estado' => $tarea->estado,
                'observaciones' => $tarea->observaciones,
                'fecha_asignacion' => $tarea->created_at->format('d/m/Y H:i'),
                'empleada' => $tarea->turno->user->name,
                'elemento' => null,
                'checklist' => null,
                'items_checklist' => []
            ];
            
            // Información del elemento (apartamento, zona común, etc.)
            if ($tarea->apartamento_id) {
                $info['elemento'] = [
                    'tipo' => 'apartamento',
                    'nombre' => $tarea->apartamento->titulo,
                    'edificio' => $tarea->apartamento->edificio->nombre ?? 'N/A'
                ];
            } elseif ($tarea->zona_comun_id) {
                $info['elemento'] = [
                    'tipo' => 'zona_comun',
                    'nombre' => $tarea->zonaComun->nombre,
                    'descripcion' => $tarea->zonaComun->descripcion ?? 'N/A'
                ];
            } else {
                $info['elemento'] = [
                    'tipo' => 'general',
                    'nombre' => $tarea->tipoTarea->nombre
                ];
            }
            
            // Información del checklist
            if ($checklist) {
                $info['checklist'] = [
                    'id' => $checklist->id,
                    'nombre' => $checklist->nombre,
                    'descripcion' => $checklist->descripcion ?? 'N/A'
                ];
                
                $info['items_checklist'] = $itemsChecklist->map(function($item) {
                    return [
                        'id' => $item->id,
                        'nombre' => $item->nombre,
                        'descripcion' => $item->descripcion ?? 'N/A',
                        'categoria' => $item->categoria ?? 'N/A',
                        'tiene_stock' => $item->tiene_stock,
                        'tiene_averias' => $item->tiene_averias
                    ];
                });
            }
            
            return response()->json([
                'success' => true,
                'data' => $info
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error obteniendo información de tarea: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
    
    /**
     * Iniciar una tarea asignada
     */
    public function iniciarTarea(TareaAsignada $tarea)
    {
        try {
            // Verificar que la tarea pertenece al usuario autenticado
            if ($tarea->turno->user_id !== Auth::id()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            
            // Verificar que la tarea está en estado pendiente
            if ($tarea->estado !== 'pendiente') {
                return response()->json(['error' => 'La tarea no está en estado pendiente'], 400);
            }
            
            // Actualizar estado de la tarea
            $tarea->update([
                'estado' => 'en_progreso',
                'fecha_inicio' => now()
            ]);
            
            // Si es una tarea de apartamento, crear ApartamentoLimpieza real
            if ($tarea->apartamento_id) {
                $this->crearApartamentoLimpiezaParaTarea($tarea);
            }
            
            // Log de la acción
            Log::info('Tarea iniciada', [
                'tarea_id' => $tarea->id,
                'usuario_id' => Auth::id(),
                'tipo_tarea' => $tarea->tipoTarea->nombre,
                'fecha_inicio' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Tarea iniciada correctamente',
                'data' => [
                    'tarea_id' => $tarea->id,
                    'estado' => $tarea->estado,
                    'fecha_inicio' => $tarea->fecha_inicio ? $tarea->fecha_inicio->format('d/m/Y H:i') : now()->format('d/m/Y H:i')
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error iniciando tarea: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
    
    /**
     * Finalizar una tarea asignada
     */
    public function finalizarTarea(TareaAsignada $tarea)
    {
        try {
            // Verificar que la tarea pertenece al usuario autenticado
            if ($tarea->turno->user_id !== Auth::id()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            
            // Verificar que la tarea está en estado en_progreso
            if ($tarea->estado !== 'en_progreso') {
                return response()->json(['error' => 'La tarea no está en estado en progreso'], 400);
            }
            
            // Actualizar estado de la tarea
            $tarea->update([
                'estado' => 'completada',
                'fecha_fin_real' => now()
            ]);
            
            // Log de la acción
            Log::info('Tarea finalizada', [
                'tarea_id' => $tarea->id,
                'usuario_id' => Auth::id(),
                'tipo_tarea' => $tarea->tipoTarea->nombre,
                'fecha_fin_real' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Tarea finalizada correctamente',
                'data' => [
                    'tarea_id' => $tarea->id,
                    'estado' => $tarea->estado,
                    'fecha_fin' => $tarea->fecha_fin_real ? $tarea->fecha_fin_real->format('d/m/Y H:i') : now()->format('d/m/Y H:i')
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error finalizando tarea: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
    
    /**
     * Mostrar checklist de una tarea asignada
     */
    public function checklistTarea(TareaAsignada $tarea)
    {
        try {
            // Cargar la relación turno explícitamente
            $tarea->load('turno');
            
            // Debug: Log de información de la tarea y usuario
            Log::info('Acceso a checklist de tarea', [
                'tarea_id' => $tarea->id,
                'tarea_turno_user_id' => $tarea->turno->user_id,
                'auth_user_id' => Auth::id(),
                'auth_user_role' => Auth::user()->role ?? 'no_role',
                'comparison' => $tarea->turno->user_id === Auth::id()
            ]);
            
            // Verificar que la tarea pertenece al usuario autenticado
            if ($tarea->turno->user_id !== Auth::id()) {
                Log::warning('Acceso denegado a tarea', [
                    'tarea_id' => $tarea->id,
                    'tarea_turno_user_id' => $tarea->turno->user_id,
                    'auth_user_id' => Auth::id()
                ]);
                
                if (request()->expectsJson()) {
                    return response()->json(['error' => 'No autorizado'], 403);
                }
                return redirect()->route('gestion.index')->with('error', 'No tienes autorización para acceder a esta tarea');
            }
            
            // Cargar relaciones necesarias
            $tarea->load(['tipoTarea', 'apartamento.edificio', 'zonaComun', 'turno.user']);
            
            // Obtener checklists según el tipo de tarea
            $checklists = collect();
            $itemsExistentes = [];
            $checklistsExistentes = [];
            
            if ($tarea->apartamento_id) {
                // Checklist de apartamento - Usar funcionalidad completa de gestion/edit
                return $this->checklistTareaApartamento($tarea);
            } elseif ($tarea->zona_comun_id) {
                // Checklist de zona común
                $checklists = \App\Models\ChecklistZonaComun::activos()
                    ->ordenados()
                    ->with(['items.articulo'])
                    ->get();
            } else {
                // Checklist de tarea general
                $checklistGeneral = \App\Models\ChecklistTareaGeneral::activos()
                    ->porCategoria($tarea->tipoTarea->categoria)
                    ->ordenados()
                    ->first();
                if ($checklistGeneral) {
                    $checklists = collect([$checklistGeneral]);
                }
            }
            
            // Obtener elementos ya completados desde apartamento_limpieza_items
            $elementosCompletados = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)
                ->whereNotNull('item_id')
                ->where('estado', 1)
                ->pluck('item_id')
                ->toArray();
            
            // Obtener amenities si es apartamento
            $amenities = collect();
            $amenitiesConRecomendaciones = [];
            $consumosExistentes = collect();
            
            if ($tarea->apartamento_id) {
                $amenities = \App\Models\Amenity::activos()
                    ->orderBy('categoria')
                    ->orderBy('nombre')
                    ->get()
                    ->groupBy('categoria');
                
                // Obtener consumos existentes (solo si la tabla existe)
                try {
                    $consumosExistentes = \DB::table('consumos_amenities')
                        ->where('tarea_asignada_id', $tarea->id)
                        ->get()
                        ->keyBy('amenity_id');
                } catch (\Exception $e) {
                    // Si la tabla no existe, usar colección vacía
                    $consumosExistentes = collect();
                }
                
                // Calcular cantidades recomendadas para cada amenity
                foreach ($amenities as $categoria => $amenitiesCategoria) {
                    foreach ($amenitiesCategoria as $amenity) {
                        $cantidadRecomendada = $this->calcularCantidadRecomendadaAmenity($amenity, null, $tarea->apartamento);
                        $consumoExistente = $consumosExistentes->get($amenity->id);
                        
                        $amenitiesConRecomendaciones[$categoria][] = [
                            'amenity' => $amenity,
                            'cantidad_recomendada' => $cantidadRecomendada,
                            'consumo_existente' => $consumoExistente,
                            'stock_disponible' => $amenity->stock_actual
                        ];
                    }
                }
                
                // Añadir amenities automáticos para niños si la siguiente reserva tiene niños
                $siguienteReserva = $this->obtenerSiguienteReserva($tarea->apartamento->id);
                if ($siguienteReserva && $siguienteReserva->numero_ninos > 0) {
                    $amenitiesNinos = \App\Models\Amenity::paraNinos()->activos()->get();
                    
                    foreach ($amenitiesNinos as $amenityNino) {
                        $cantidadParaNinos = $amenityNino->calcularCantidadParaNinos($siguienteReserva->numero_ninos, $siguienteReserva->edades_ninos ?? []);
                        
                        if ($cantidadParaNinos > 0) {
                            $categoria = $amenityNino->categoria;
                            if (!isset($amenitiesConRecomendaciones[$categoria])) {
                                $amenitiesConRecomendaciones[$categoria] = [];
                            }
                            
                            // Verificar si ya existe este amenity
                            $existe = false;
                            foreach ($amenitiesConRecomendaciones[$categoria] as $amenityExistente) {
                                if ($amenityExistente['amenity']->id === $amenityNino->id) {
                                    $amenityExistente['cantidad_recomendada'] += $cantidadParaNinos;
                                    $amenityExistente['es_automatico_ninos'] = true;
                                    $amenityExistente['motivo_ninos'] = "Automático para {$siguienteReserva->numero_ninos} niño(s)";
                                    $existe = true;
                                    break;
                                }
                            }
                            
                            if (!$existe) {
                                $consumoExistente = $consumosExistentes->get($amenityNino->id);
                                $amenitiesConRecomendaciones[$categoria][] = [
                                    'amenity' => $amenityNino,
                                    'cantidad_recomendada' => $cantidadParaNinos,
                                    'consumo_existente' => $consumoExistente,
                                    'stock_disponible' => $amenityNino->stock_actual,
                                    'es_automatico_ninos' => true,
                                    'motivo_ninos' => "Automático para {$siguienteReserva->numero_ninos} niño(s)"
                                ];
                            }
                        }
                    }
                }
            }
            
            // Obtener mensaje de amenities del session flash si existe
            $mensajeAmenities = session('mensajeAmenities');
            
            // Devolver vista específica según el tipo de tarea
            if ($tarea->zona_comun_id) {
                // Para zonas comunes, usar la vista específica
                $zonaComun = $tarea->zonaComun;
                $id = $apartamentoLimpieza->id;
                return view('gestion.edit-zona-comun', compact(
                    'apartamentoLimpieza',
                    'zonaComun',
                    'id',
                    'checklists',
                    'itemsExistentes',
                    'checklistsExistentes'
                ));
            } else {
                // Para apartamentos y tareas generales, usar checklist-tarea
                return view('gestion.checklist-tarea', compact(
                    'tarea',
                    'checklists',
                    'itemsExistentes',
                    'checklistsExistentes',
                    'elementosCompletados',
                    'amenities',
                    'amenitiesConRecomendaciones',
                    'consumosExistentes',
                    'siguienteReserva',
                    'mensajeAmenities'
                ));
            }
            
        } catch (\Exception $e) {
            Log::error('Error mostrando checklist de tarea: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el checklist de la tarea');
        }
    }

    /**
     * Actualizar tarea asignada (guardar progreso o finalizar)
     */
    public function updateTarea(Request $request, TareaAsignada $tarea)
    {
        try {
            // Verificar que la tarea pertenece al usuario autenticado
            if ($tarea->turno->user_id !== Auth::id()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }

            $accion = $request->input('accion', 'guardar');
            
            if ($accion === 'finalizar') {
                return $this->finalizarTareaChecklist($request, $tarea);
            } else {
                return $this->guardarProgresoTarea($request, $tarea);
            }
            
        } catch (\Exception $e) {
            Log::error('Error actualizando tarea: ' . $e->getMessage());
            return response()->json(['error' => 'Error al actualizar la tarea'], 500);
        }
    }

    /**
     * Guardar progreso de la tarea
     */
    private function guardarProgresoTarea(Request $request, TareaAsignada $tarea)
    {
        try {
            Log::info('Iniciando guardarProgresoTarea', [
                'tarea_id' => $tarea->id,
                'items' => $request->input('items', []),
                'checklist' => $request->input('checklist', []),
                'amenities' => $request->input('amenities', [])
            ]);
            
            DB::beginTransaction();
            
            // Obtener items completados del formulario
            $itemsCompletados = $request->input('items', []);
            $checklistsCompletados = $request->input('checklist', []);
            
            // Limpiar elementos completados existentes
            DB::table('tarea_checklist_completados')
                ->where('tarea_asignada_id', $tarea->id)
                ->delete();
            
            // Guardar nuevos elementos completados
            foreach ($itemsCompletados as $itemId => $valor) {
                if ($valor == '1') {
                    DB::table('tarea_checklist_completados')->insert([
                        'tarea_asignada_id' => $tarea->id,
                        'item_checklist_id' => $itemId,
                        'completado_por' => Auth::id(),
                        'fecha_completado' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
            
            // Los checklists se marcan como completados cuando todos sus items están completados
            // No se guardan directamente en la tabla tarea_checklist_completados
            
            // Guardar amenities si es apartamento (usar sistema original)
            if ($tarea->apartamento_id) {
                $amenities = $request->input('amenities', []);
                
                // Obtener la limpieza asociada a esta tarea
                $apartamentoLimpieza = ApartamentoLimpieza::where('tarea_asignada_id', $tarea->id)->first();
                
                if ($apartamentoLimpieza) {
                    // Limpiar consumos existentes
                    \App\Models\AmenityConsumo::where('limpieza_id', $apartamentoLimpieza->id)->delete();
                    
                    // Guardar nuevos consumos
                    foreach ($amenities as $amenityId => $amenityData) {
                        $cantidad = intval($amenityData['cantidad_dejada'] ?? 0);
                        if ($cantidad > 0) {
                            \App\Models\AmenityConsumo::create([
                                'limpieza_id' => $apartamentoLimpieza->id,
                                'amenity_id' => $amenityId,
                                'cantidad_consumida' => $cantidad,
                                'cantidad_anterior' => 0,
                                'cantidad_actual' => $cantidad,
                                'tipo_consumo' => 'limpieza',
                                'fecha_consumo' => now()->toDateString(),
                                'user_id' => auth()->id(),
                                'reserva_id' => $apartamentoLimpieza->reserva_id,
                                'apartamento_id' => $apartamentoLimpieza->apartamento_id
                            ]);
                        }
                    }
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Progreso guardado correctamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error guardando progreso de tarea: ' . $e->getMessage());
            return response()->json(['error' => 'Error al guardar el progreso'], 500);
        }
    }

    /**
     * Finalizar tarea desde checklist
     */
    private function finalizarTareaChecklist(Request $request, TareaAsignada $tarea)
    {
        try {
            DB::beginTransaction();
            
            // Guardar progreso primero
            $this->guardarProgresoTarea($request, $tarea);
            
            // Verificar si necesita consentimiento
            $totalItems = 0;
            $itemsCompletados = 0;
            
            if ($tarea->apartamento_id) {
                $checklists = \App\Models\Checklist::with(['items'])
                    ->where('edificio_id', $tarea->apartamento->edificio_id)
                    ->get();
            } elseif ($tarea->zona_comun_id) {
                $checklists = \App\Models\ChecklistZonaComun::activos()
                    ->ordenados()
                    ->with(['items'])
                    ->get();
            } else {
                $checklistGeneral = \App\Models\ChecklistTareaGeneral::activos()
                    ->porCategoria($tarea->tipoTarea->categoria)
                    ->ordenados()
                    ->first();
                $checklists = $checklistGeneral ? collect([$checklistGeneral]) : collect();
            }
            
            foreach ($checklists as $checklist) {
                $totalItems += $checklist->items->count();
            }
            
            $elementosCompletados = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)
                ->whereNotNull('item_id')
                ->where('estado', 1)
                ->pluck('item_id')
                ->toArray();
            
            $itemsCompletados = count($elementosCompletados);
            $porcentajeCompletado = $totalItems > 0 ? ($itemsCompletados / $totalItems) * 100 : 100;
            
            // Si no está completo, verificar consentimiento
            if ($porcentajeCompletado < 100) {
                $consentimiento = $request->input('consentimiento_finalizacion', false);
                $motivo = $request->input('motivo_consentimiento', '');
                $fechaConsentimiento = $request->input('fecha_consentimiento', now()->toISOString());
                
                if (!$consentimiento || !$motivo) {
                    return response()->json([
                        'error' => 'Se requiere consentimiento para finalizar sin completar todos los checklists'
                    ], 400);
                }
                
                // Guardar consentimiento
                $tarea->update([
                    'consentimiento_finalizacion' => true,
                    'motivo_consentimiento' => $motivo,
                    'fecha_consentimiento' => $fechaConsentimiento
                ]);
            }
            
            // Marcar tarea como completada
            $tarea->update([
                'estado' => 'completada',
                'fecha_fin_real' => now(),
                'porcentaje_completado' => $porcentajeCompletado
            ]);
            
            // Crear nueva tarea si es necesario (para tareas recurrentes)
            if ($tarea->tipoTarea->es_recurrente) {
                $this->crearTareaRecurrente($tarea);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Tarea finalizada correctamente',
                'porcentaje_completado' => $porcentajeCompletado
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error finalizando tarea: ' . $e->getMessage());
            return response()->json(['error' => 'Error al finalizar la tarea'], 500);
        }
    }

    /**
     * Crear tarea recurrente
     */
    private function crearTareaRecurrente(TareaAsignada $tarea)
    {
        try {
            $fechaSiguiente = $this->calcularFechaSiguienteTarea($tarea);
            
            if ($fechaSiguiente) {
                TareaAsignada::create([
                    'turno_id' => $tarea->turno_id,
                    'tipo_tarea_id' => $tarea->tipo_tarea_id,
                    'apartamento_id' => $tarea->apartamento_id,
                    'zona_comun_id' => $tarea->zona_comun_id,
                    'fecha_asignada' => $fechaSiguiente,
                    'estado' => 'pendiente',
                    'prioridad_calculada' => $tarea->tipoTarea->prioridad_base,
                    'orden_ejecucion' => $tarea->orden_ejecucion
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error creando tarea recurrente: ' . $e->getMessage());
        }
    }

    /**
     * Checklist de tarea para apartamento - Usa funcionalidad completa de gestion/edit
     */
    private function checklistTareaApartamento(TareaAsignada $tarea)
    {
        $apartamento = $tarea->apartamento;
        $edificioId = $apartamento->edificio_id;
        
        // Obtener checklists con items y artículos (igual que gestion/edit)
        $checklists = Checklist::with(['items.articulo'])->where('edificio_id', $edificioId)->get();
        
        // Obtener o crear ApartamentoLimpieza real para esta tarea
        $apartamentoLimpieza = ApartamentoLimpieza::where('tarea_asignada_id', $tarea->id)->first();
        
        Log::info('Buscando ApartamentoLimpieza existente', [
            'tarea_id' => $tarea->id,
            'limpieza_encontrada' => $apartamentoLimpieza ? $apartamentoLimpieza->id : 'null'
        ]);
        
        if (!$apartamentoLimpieza) {
            // Si no existe, crear uno nuevo
            Log::info('No se encontró ApartamentoLimpieza, creando nuevo');
            $apartamentoLimpieza = $this->crearApartamentoLimpiezaParaTarea($tarea);
        } else {
            Log::info('ApartamentoLimpieza existente encontrado', [
                'limpieza_id' => $apartamentoLimpieza->id,
                'tarea_asignada_id' => $apartamentoLimpieza->tarea_asignada_id
            ]);
        }
        
        // Cargar relación apartamento
        $apartamentoLimpieza->load('apartamento');
        
        // Obtener items marcados para esta tarea
        $item_check = \DB::table('tarea_checklist_completados')
            ->where('tarea_asignada_id', $tarea->id)
            ->get();
        $itemsExistentes = $item_check->pluck('estado', 'item_checklist_id')->toArray();
        $checklist_check = $item_check->whereNotNull('checklist_id');
        $checklistsExistentes = $checklist_check->pluck('estado', 'checklist_id')->toArray();
        
        // Obtener amenities para esta limpieza (igual que gestion/edit)
        $amenities = \App\Models\Amenity::activos()
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get()
            ->groupBy('categoria');
        
        // Obtener consumos existentes para esta limpieza (usar tabla del sistema original)
        $consumosExistentes = \App\Models\AmenityConsumo::where('limpieza_id', $apartamentoLimpieza->id)
            ->with('amenity')
            ->get()
            ->keyBy('amenity_id');
        
        // Calcular cantidades recomendadas para cada amenity (igual que gestion/edit)
        $amenitiesConRecomendaciones = [];
        foreach ($amenities as $categoria => $amenitiesCategoria) {
            foreach ($amenitiesCategoria as $amenity) {
                $cantidadRecomendada = $this->calcularCantidadRecomendadaAmenity($amenity, null, $apartamento);
                $consumoExistente = $consumosExistentes->get($amenity->id);
                
                $amenitiesConRecomendaciones[$categoria][] = [
                    'amenity' => $amenity,
                    'cantidad_recomendada' => $cantidadRecomendada,
                    'consumo_existente' => $consumoExistente,
                    'stock_disponible' => $amenity->stock_actual
                ];
            }
        }

        // Añadir amenities automáticos para niños si la siguiente reserva tiene niños
        $siguienteReserva = $this->obtenerSiguienteReserva($apartamento->id);
        if ($siguienteReserva && $siguienteReserva->numero_ninos > 0) {
            $amenitiesNinos = \App\Models\Amenity::paraNinos()->activos()->get();
            
            foreach ($amenitiesNinos as $amenityNino) {
                $cantidadParaNinos = $amenityNino->calcularCantidadParaNinos($siguienteReserva->numero_ninos, $siguienteReserva->edades_ninos ?? []);
                
                if ($cantidadParaNinos > 0) {
                    $categoria = $amenityNino->categoria;
                    if (!isset($amenitiesConRecomendaciones[$categoria])) {
                        $amenitiesConRecomendaciones[$categoria] = [];
                    }
                    
                    // Verificar si ya existe este amenity
                    $existe = false;
                    foreach ($amenitiesConRecomendaciones[$categoria] as $amenityExistente) {
                        if ($amenityExistente['amenity']->id === $amenityNino->id) {
                            $amenityExistente['cantidad_recomendada'] += $cantidadParaNinos;
                            $amenityExistente['es_automatico_ninos'] = true;
                            $amenityExistente['motivo_ninos'] = "Automático para {$siguienteReserva->numero_ninos} niño(s)";
                            $existe = true;
                            break;
                        }
                    }
                    
                    if (!$existe) {
                        $consumoExistente = $consumosExistentes->get($amenityNino->id);
                        $amenitiesConRecomendaciones[$categoria][] = [
                            'amenity' => $amenityNino,
                            'cantidad_recomendada' => $cantidadParaNinos,
                            'consumo_existente' => $consumoExistente,
                            'stock_disponible' => $amenityNino->stock_actual,
                            'es_automatico_ninos' => true,
                            'motivo_ninos' => "Automático para {$siguienteReserva->numero_ninos} niño(s)"
                        ];
                    }
                }
            }
        }
        
        // Obtener mensaje de amenities del session flash si existe
        $mensajeAmenities = session('mensajeAmenities');
        
        // Usar la vista de gestion/edit pero adaptada para tareas
        return view('gestion.edit-tarea', compact(
            'tarea',
            'apartamentoLimpieza',
            'apartamento',
            'checklists',
            'itemsExistentes',
            'checklistsExistentes',
            'amenitiesConRecomendaciones',
            'siguienteReserva',
            'mensajeAmenities'
        ));
    }

    /**
     * Crear ApartamentoLimpieza real para una tarea de apartamento
     */
    private function crearApartamentoLimpiezaParaTarea(TareaAsignada $tarea)
    {
        try {
            // Verificar si ya existe una limpieza para esta tarea
            $limpiezaExistente = ApartamentoLimpieza::where('tarea_asignada_id', $tarea->id)->first();
            
            if ($limpiezaExistente) {
                return $limpiezaExistente;
            }
            
            // Crear nueva limpieza
            $limpieza = ApartamentoLimpieza::create([
                'apartamento_id' => $tarea->apartamento_id,
                'empleada_id' => $tarea->turno->user_id,
                'tipo_limpieza' => 'apartamento',
                'status_id' => 1, // En progreso
                'fecha_comienzo' => now(),
                'tarea_asignada_id' => $tarea->id, // Relación con la tarea
                'origen' => 'tarea_asignada'
            ]);
            
            Log::info('ApartamentoLimpieza creado para tarea', [
                'tarea_id' => $tarea->id,
                'limpieza_id' => $limpieza->id,
                'apartamento_id' => $tarea->apartamento_id,
                'empleada_id' => $tarea->turno->user_id
            ]);
            
            return $limpieza;
            
        } catch (\Exception $e) {
            Log::error('Error creando ApartamentoLimpieza para tarea: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Actualizar estado de un item del checklist
     */
    public function updateChecklistTarea(Request $request, TareaAsignada $tarea)
    {
        try {
            // Verificar que la tarea pertenece al usuario autenticado
            if ($tarea->turno->user_id !== Auth::id()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            
            $itemId = $request->input('item_id');
            $completado = $request->input('completado', false);
            
            if ($completado) {
                // Marcar como completado
                \DB::table('tarea_checklist_completados')->updateOrInsert(
                    [
                        'tarea_asignada_id' => $tarea->id,
                        'item_checklist_id' => $itemId
                    ],
                    [
                        'completado_por' => Auth::id(),
                        'fecha_completado' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            } else {
                // Marcar como no completado
                \DB::table('tarea_checklist_completados')
                    ->where('tarea_asignada_id', $tarea->id)
                    ->where('item_checklist_id', $itemId)
                    ->delete();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error actualizando checklist de tarea: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
    
    /**
     * Finalizar checklist y completar tarea
     */
    public function finalizarChecklistTarea(Request $request, TareaAsignada $tarea)
    {
        try {
            // Verificar que la tarea pertenece al usuario autenticado
            if ($tarea->turno->user_id !== Auth::id()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            
            // Verificar que la tarea está en progreso
            if ($tarea->estado !== 'en_progreso') {
                return response()->json(['error' => 'La tarea no está en estado en progreso'], 400);
            }
            
            // Obtener items completados
            $itemsCompletados = \DB::table('tarea_checklist_completados')
                ->where('tarea_asignada_id', $tarea->id)
                ->count();
            
            // Obtener total de items del checklist
            $totalItems = 0;
            if ($tarea->apartamento_id) {
                $apartamento = $tarea->apartamento;
                if ($apartamento && $apartamento->edificio && is_object($apartamento->edificio) && $apartamento->edificio->checklist) {
                    $totalItems = $apartamento->edificio->checklist->items()->activos()->count();
                }
            } elseif ($tarea->zona_comun_id) {
                $checklist = \App\Models\ChecklistZonaComun::activos()->ordenados()->first();
                if ($checklist) {
                    $totalItems = $checklist->items()->activos()->count();
                }
            } else {
                $checklist = \App\Models\ChecklistTareaGeneral::activos()
                    ->porCategoria($tarea->tipoTarea->categoria)
                    ->ordenados()
                    ->first();
                if ($checklist) {
                    $totalItems = $checklist->items()->activos()->count();
                }
            }
            
            // Actualizar estado de la tarea
            $tarea->update([
                'estado' => 'completada',
                'fecha_fin_real' => now(),
                'observaciones' => $request->input('observaciones', '')
            ]);
            
            // Log de la acción
            Log::info('Tarea completada con checklist', [
                'tarea_id' => $tarea->id,
                'usuario_id' => Auth::id(),
                'tipo_tarea' => $tarea->tipoTarea->nombre,
                'items_completados' => $itemsCompletados,
                'total_items' => $totalItems,
                'fecha_fin_real' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Tarea completada correctamente',
                'data' => [
                    'tarea_id' => $tarea->id,
                    'estado' => $tarea->estado,
                    'fecha_fin' => $tarea->fecha_fin->format('d/m/Y H:i'),
                    'items_completados' => $itemsCompletados,
                    'total_items' => $totalItems
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error finalizando checklist de tarea: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create_fondo($id)
    {

        $apartamentoLimpio = ApartamentoLimpieza::where('fecha_fin', null)
            ->where('apartamento_id', explode(' - ', $id)[1])
            ->first();
        $reserva = Reserva::find($id);
            if ($reserva == null) {
                $apartamentoId = explode(' - ', $id)[1];
                $id = null;
            } else {
                $apartamentoId = $reserva->apartamento_id;
            }
            if ($apartamentoLimpio == null) {
                $apartamentoLimpieza = ApartamentoLimpieza::create([
                    'apartamento_id' => $apartamentoId,
                    'fecha_comienzo' => Carbon::now(),
                    'status_id' => 2,
                    'reserva_id' => $id,
                    'user_id' => Auth::user()->id
                ]);
                $apartamentoLimpieza->save();
                if ($reserva != null) {
                    $reserva->fecha_limpieza = Carbon::now();
                    $reserva->save();
                }
            } else {
                $apartamentoLimpieza = $apartamentoLimpio;
            }




        // Verificar que el apartamento existe
        $apartamento = Apartamento::find($apartamentoId);
        if (!$apartamento) {
            abort(404, 'Apartamento no encontrado');
        }
        
        $edificioId = $apartamento->edificio_id;
        
        // Verificar que el edificio existe
        if (!$edificioId) {
            abort(404, 'Edificio no encontrado para este apartamento');
        }

        $checklists = Checklist::with('items')->where('edificio_id', $edificioId)->get();
        $item_check = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)->get();
        $itemsExistentes = $item_check->pluck('estado', 'item_id')->toArray();

        // Obtener amenities para esta limpieza
        $amenities = \App\Models\Amenity::activos()
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get()
            ->groupBy('categoria');
        
        // Obtener consumos existentes para esta limpieza
        $consumosExistentes = \App\Models\AmenityConsumo::where('limpieza_id', $apartamentoLimpieza->id)
            ->with('amenity')
            ->get()
            ->keyBy('amenity_id');
        
        // Calcular cantidades recomendadas para cada amenity
        $amenitiesConRecomendaciones = [];
        foreach ($amenities as $categoria => $amenitiesCategoria) {
            foreach ($amenitiesCategoria as $amenity) {
                $cantidadRecomendada = $this->calcularCantidadRecomendadaAmenity($amenity, $reserva, $apartamentoLimpieza->apartamento);
                $consumoExistente = $consumosExistentes->get($amenity->id);
                
                $amenitiesConRecomendaciones[$categoria][] = [
                    'amenity' => $amenity,
                    'cantidad_recomendada' => $cantidadRecomendada,
                    'consumo_existente' => $consumoExistente,
                    'stock_disponible' => $amenity->stock_actual
                ];
            }
        }

        return view('gestion.edit', compact('apartamentoLimpieza', 'id', 'checklists', 'itemsExistentes', 'amenitiesConRecomendaciones', 'consumosExistentes'));
    }

    public function create($id)
    {
        if (isset(explode(' - ', $id)[1])) {
            return redirect()->route('gestion.create_fondo', $id);
        } else {

        $reserva = Reserva::find($id);
        if (!$reserva) {
            Alert::error('Error', 'Reserva no encontrada');
            return redirect()->route('gestion.index');
        }

        $apartamentoLimpio = ApartamentoLimpieza::where('fecha_fin', null)
            ->where('apartamento_id', $reserva->apartamento_id)
            ->first();

        if ($apartamentoLimpio == null) {
            $apartamentoLimpieza = ApartamentoLimpieza::create([
                'apartamento_id' => $reserva->apartamento_id,
                'fecha_comienzo' => Carbon::now(),
                'status_id' => 2,
                'reserva_id' => $id,
                'user_id' => Auth::user()->id
            ]);
            $reserva->fecha_limpieza = Carbon::now();
            $reserva->save();
        } else {
            $apartamentoLimpieza = $apartamentoLimpio;
        }
        $apartamentoId = $reserva->apartamento_id;

        // Verificar que el apartamento existe
        $apartamento = Apartamento::find($apartamentoId);
        if (!$apartamento) {
            abort(404, 'Apartamento no encontrado');
        }
        
        $edificioId = $apartamento->edificio_id;
        
        // Verificar que el edificio existe
        if (!$edificioId) {
            abort(404, 'Edificio no encontrado para este apartamento');
        }

        $checklists = Checklist::with('items')->where('edificio_id', $edificioId)->get();
        $item_check = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)->get();
        $itemsExistentes = $item_check->pluck('estado', 'item_id')->toArray();

        // Obtener amenities para esta limpieza
        $amenities = \App\Models\Amenity::activos()
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get()
            ->groupBy('categoria');
        
        // Obtener consumos existentes para esta limpieza
        $consumosExistentes = \App\Models\AmenityConsumo::where('limpieza_id', $apartamentoLimpieza->id)
            ->with('amenity')
            ->get()
            ->keyBy('amenity_id');
        
        // Calcular cantidades recomendadas para cada amenity
        $amenitiesConRecomendaciones = [];
        foreach ($amenities as $categoria => $amenitiesCategoria) {
            foreach ($amenitiesCategoria as $amenity) {
                $cantidadRecomendada = $this->calcularCantidadRecomendadaAmenity($amenity, $reserva, $apartamentoLimpieza->apartamento);
                $consumoExistente = $consumosExistentes->get($amenity->id);
                
                $amenitiesConRecomendaciones[$categoria][] = [
                    'amenity' => $amenity,
                    'cantidad_recomendada' => $cantidadRecomendada,
                    'consumo_existente' => $consumoExistente,
                    'stock_disponible' => $amenity->stock_actual
                ];
            }
        }

        return view('gestion.edit', compact(
            'apartamentoLimpieza', 
            'id', 
            'checklists', 
            'itemsExistentes',
            'amenitiesConRecomendaciones',
            'consumosExistentes'
        ));
    }
    }


    public function store(Request $request)
    {
        $id = $request->id;
        $apartamento = ApartamentoLimpieza::find($id);

        if (!$apartamento) {
            Alert::error('Error', 'Apartamento no encontrado');
            return redirect()->route('gestion.index');
        }

        // Eliminar registros anteriores para este apartamento y limpieza
        ApartamentoLimpiezaItem::where('id_limpieza', $apartamento->id)->delete();

        // Guardar los nuevos ítems marcados en el formulario
        if ($request->has('items')) {
            foreach ($request->items as $itemId => $estado) {
                ApartamentoLimpiezaItem::create([
                    'id_limpieza' => $apartamento->id,
                    'id_reserva' => $apartamento->reserva_id,
                    'item_id' => $itemId,
                    'estado' => $estado == 1 ? 1 : 0,
                ]);
            }
            foreach ($request->checklist as $checklistId => $estado) {
                ApartamentoLimpiezaItem::create([
                    'id_limpieza' => $apartamento->id,
                    'id_reserva' => $apartamento->reserva_id,
                    'estado' => $estado == 1 ? 1 : 0,
                    'checklist_id' => $checklistId
                ]);
            }
        }

        // Guardar observación
        $apartamento->observacion = $request->observacion;

        // Asignar el usuario si no existe
        if (empty($apartamento->user_id)) {
            $apartamento->user_id = Auth::user()->id;
        }

        $apartamento->save();

        Alert::success('Guardado con Éxito', 'Apartamento actualizado correctamente');
        return redirect()->route('gestion.index');
    }


    /**
     * Display the specified resource.
     */
    public function storeColumn(Request $request)
    {
        $apartamento = ApartamentoLimpieza::find($request->id);

        if ($apartamento) {
            $columna = $request->name;
            $apartamento->$columna = $request->checked == 'true' ? true : false;
            $apartamento->save();
            Alert::toast('Actualizado', 'success');
            return true;

        }
        Alert::toast('Error, intentelo mas tarde', 'error');

        return false;
    }

    /**
     * Display the specified resource.
     */
    public function show(GestionApartamento $gestionApartamento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ApartamentoLimpieza $apartamentoLimpieza)
    {
        // Verificar que el apartamentoLimpieza existe y tiene los datos necesarios
        if (!$apartamentoLimpieza) {
            abort(404, 'Limpieza no encontrada');
        }

        $id = $apartamentoLimpieza->id;
        
        // Debug temporal - ver en consola del navegador
        if (app()->environment('local')) {
            error_log("Debug ApartamentoLimpieza ID: {$apartamentoLimpieza->id}, Tipo: {$apartamentoLimpieza->tipo_limpieza}, ZonaComunID: {$apartamentoLimpieza->zona_comun_id}, ApartamentoID: {$apartamentoLimpieza->apartamento_id}");
        }
        
        // Determinar si es una zona común o un apartamento
        if ($apartamentoLimpieza->tipo_limpieza === 'zona_comun') {
            // Es una zona común
            $zonaComun = $apartamentoLimpieza->zonaComun;
            if (!$zonaComun) {
                abort(404, 'Zona común no encontrada');
            }
            
            // Obtener checklists específicos para zonas comunes
            $checklists = \App\Models\ChecklistZonaComun::activos()->ordenados()->with(['items.articulo'])->get();
            
            // Obtener items marcados para esta limpieza
            $item_check = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)->get();
            $itemsExistentes = $item_check->pluck('estado', 'item_id')->toArray();
            $checklist_check = $item_check->whereNotNull('checklist_zona_comun_id')->filter(function ($item) {
                return $item->estado == 1;
            });
            $checklistsExistentes = $checklist_check->pluck('estado', 'checklist_zona_comun_id')->toArray();
            
            return view('gestion.edit-zona-comun', compact(
                'apartamentoLimpieza',
                'zonaComun',
                'id', 
                'checklists', 
                'itemsExistentes', 
                'checklistsExistentes'
            ));
            
        } else {
            // Es un apartamento
            $apartamentoId = $apartamentoLimpieza->apartamento_id;
            
            // Verificar que el apartamento existe
            $apartamento = Apartamento::find($apartamentoId);
            if (!$apartamento) {
                abort(404, 'Apartamento no encontrado');
            }
            
            $edificioId = $apartamento->edificio_id;
            
            // Verificar que el edificio existe
            if (!$edificioId) {
                abort(404, 'Edificio no encontrado para este apartamento');
            }

            $checklists = Checklist::with(['items.articulo'])->where('edificio_id', $edificioId)->get();
            $item_check = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)->get();
            $itemsExistentes = $item_check->pluck('estado', 'item_id')->toArray();
            $checklist_check = $item_check->whereNotNull('checklist_id')->filter(function ($item) {
                return $item->estado == 1;
            });

            $checklistsExistentes = $checklist_check->pluck('estado', 'checklist_id')->toArray();
            
            // Obtener amenities para esta limpieza
            $amenities = \App\Models\Amenity::activos()
                ->orderBy('categoria')
                ->orderBy('nombre')
                ->get()
                ->groupBy('categoria');
            
            // Obtener consumos existentes para esta limpieza
            $consumosExistentes = \App\Models\AmenityConsumo::where('limpieza_id', $apartamentoLimpieza->id)
                ->with('amenity')
                ->get()
                ->keyBy('amenity_id');
            
            // Calcular cantidades recomendadas para cada amenity
            $amenitiesConRecomendaciones = [];
            foreach ($amenities as $categoria => $amenitiesCategoria) {
                foreach ($amenitiesCategoria as $amenity) {
                    $cantidadRecomendada = $this->calcularCantidadRecomendadaAmenity($amenity, $apartamentoLimpieza->origenReserva, $apartamentoLimpieza->apartamento);
                    $consumoExistente = $consumosExistentes->get($amenity->id);
                    
                    $amenitiesConRecomendaciones[$categoria][] = [
                        'amenity' => $amenity,
                        'cantidad_recomendada' => $cantidadRecomendada,
                        'consumo_existente' => $consumoExistente,
                        'stock_disponible' => $amenity->stock_actual
                    ];
                }
            }

            // Añadir amenities automáticos para niños si la siguiente reserva tiene niños
            $siguienteReserva = $this->obtenerSiguienteReserva($apartamento->id);
            if ($siguienteReserva && $siguienteReserva->numero_ninos > 0) {
                $amenitiesNinos = \App\Models\Amenity::paraNinos()->activos()->get();
                
                foreach ($amenitiesNinos as $amenityNino) {
                    $cantidadParaNinos = $amenityNino->calcularCantidadParaNinos($siguienteReserva->numero_ninos, $siguienteReserva->edades_ninos ?? []);
                    
                    if ($cantidadParaNinos > 0) {
                        $categoria = $amenityNino->categoria;
                        if (!isset($amenitiesConRecomendaciones[$categoria])) {
                            $amenitiesConRecomendaciones[$categoria] = [];
                        }
                        
                        // Verificar si ya existe este amenity
                        $existe = false;
                        foreach ($amenitiesConRecomendaciones[$categoria] as $amenityExistente) {
                            if ($amenityExistente['amenity']->id === $amenityNino->id) {
                                $amenityExistente['cantidad_recomendada'] += $cantidadParaNinos;
                                $amenityExistente['es_automatico_ninos'] = true;
                                $amenityExistente['motivo_ninos'] = "Automático para {$siguienteReserva->numero_ninos} niño(s)";
                                $existe = true;
                                break;
                            }
                        }
                        
                        if (!$existe) {
                            $consumoExistente = $consumosExistentes->get($amenityNino->id);
                            $amenitiesConRecomendaciones[$categoria][] = [
                                'amenity' => $amenityNino,
                                'cantidad_recomendada' => $cantidadParaNinos,
                                'consumo_existente' => $consumoExistente,
                                'stock_disponible' => $amenityNino->stock_actual,
                                'es_automatico_ninos' => true,
                                'motivo_ninos' => "Automático para {$siguienteReserva->numero_ninos} niño(s)"
                            ];
                        }
                    }
                }
            }
            
            // Obtener mensaje de amenities del session flash si existe
            $mensajeAmenities = session('mensajeAmenities');
            
            return view('gestion.edit', compact(
                'apartamentoLimpieza',
                'id', 
                'checklists', 
                'itemsExistentes', 
                'checklistsExistentes',
                'amenitiesConRecomendaciones',
                'consumosExistentes',
                'mensajeAmenities'
            ));
        }
    }

    /**
     * Obtener la siguiente reserva para un apartamento
     */
    private function obtenerSiguienteReserva($apartamentoId)
    {
        return \App\Models\Reserva::where('apartamento_id', $apartamentoId)
            ->where('fecha_entrada', '>', now()->toDateString())
            ->where(function($query) {
                $query->where('estado_id', '!=', 4)
                      ->orWhereNull('estado_id');
            })
            ->orderBy('fecha_entrada', 'asc')
            ->select('id', 'apartamento_id', 'fecha_entrada', 'fecha_salida', 'numero_personas', 'numero_ninos', 'edades_ninos', 'notas_ninos', 'codigo_reserva')
            ->first();
    }


    public function update(Request $request, ApartamentoLimpieza $apartamentoLimpieza)
{
    // Eliminar ítems anteriores para este registro
    ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)->delete();

    // Guardar nuevos ítems desde los checkboxes de ítems
    if ($request->has('items')) {
        foreach ($request->items as $itemId => $estado) {
            ApartamentoLimpiezaItem::create([
                'id_limpieza'   => $apartamentoLimpieza->id,
                'id_reserva'    => $apartamentoLimpieza->reserva_id,
                'item_id'       => $itemId,
                'estado'        => $estado == 1 ? 1 : 0,
            ]);
        }
    }

    // Guardar nuevos ítems desde los checkboxes de checklist
    if ($request->has('checklist')) {
        foreach ($request->checklist as $checklistId => $estado) {
            ApartamentoLimpiezaItem::create([
                'id_limpieza'   => $apartamentoLimpieza->id,
                'id_reserva'    => $apartamentoLimpieza->reserva_id,
                'checklist_id'  => $checklistId,
                'estado'        => $estado == 1 ? 1 : 0,
            ]);
        }
    }

    // Guardar amenities de consumo
    if ($request->has('amenities')) {
        // Debug: Log de los datos recibidos
        Log::info('Amenities recibidos:', $request->amenities);
        
        $amenitiesGuardados = 0;
        $amenitiesCreados = 0;
        $amenitiesActualizados = 0;
        
        foreach ($request->amenities as $amenityId => $amenityData) {
            try {
                // Validar datos antes de insertar
                $cantidadDejada = intval($amenityData['cantidad_dejada'] ?? 0);
                $observaciones = $amenityData['observaciones'] ?? null;
                
                // Solo procesar si hay cantidad dejada
                if ($cantidadDejada > 0) {
                    // Buscar si ya existe un consumo para este amenity
                    $consumoExistente = \App\Models\AmenityConsumo::where('limpieza_id', $apartamentoLimpieza->id)
                        ->where('amenity_id', $amenityId)
                        ->first();
                    
                    if ($consumoExistente) {
                        // ACTUALIZAR el consumo existente
                        $cantidadAnterior = $consumoExistente->cantidad_consumida;
                        $diferencia = $cantidadDejada - $cantidadAnterior;
                        
                        $consumoExistente->update([
                            'cantidad_consumida' => $cantidadDejada,
                            'cantidad_anterior' => $cantidadAnterior,
                            'cantidad_actual' => $consumoExistente->cantidad_actual + $diferencia,
                            'observaciones' => $observaciones,
                            'fecha_consumo' => now()
                        ]);
                        
                        // Actualizar el stock del amenity usando el método del modelo
                        $amenity = \App\Models\Amenity::find($amenityId);
                        if ($amenity) {
                            \Log::info("ANTES de ajustar stock - Amenity {$amenityId}: stock_actual = {$amenity->stock_actual}");
                            
                            // Siempre ajustar el stock basado en la diferencia
                            $amenity->ajustarStock($cantidadAnterior, $cantidadDejada);
                            
                            \Log::info("DESPUÉS de ajustar stock - Amenity {$amenityId}: stock_actual = {$amenity->stock_actual}");
                            \Log::info("Stock del amenity {$amenityId} ajustado: diferencia {$diferencia} (de {$cantidadAnterior} a {$cantidadDejada})");
                            
                            // Verificar si el stock está bajo después del ajuste
                            if ($amenity->verificarStockBajo()) {
                                Alert::warning('Stock Bajo', "El amenity '{$amenity->nombre}' tiene stock bajo (actual: {$amenity->stock_actual})");
                            }
                        } else {
                            \Log::error("No se pudo encontrar el amenity {$amenityId} para actualizar stock");
                        }
                        
                        $amenitiesActualizados++;
                        \Log::info("Amenity {$amenityId} ACTUALIZADO con cantidad {$cantidadDejada}");
                    } else {
                        // CREAR nuevo consumo solo si no existe
                        \App\Models\AmenityConsumo::create([
                            'amenity_id' => $amenityId,
                            'limpieza_id' => $apartamentoLimpieza->id,
                            'reserva_id' => $apartamentoLimpieza->reserva_id,
                            'apartamento_id' => $apartamentoLimpieza->apartamento_id,
                            'user_id' => auth()->id(),
                            'tipo_consumo' => 'limpieza',
                            'cantidad_consumida' => $cantidadDejada,
                            'cantidad_anterior' => 0,
                            'cantidad_actual' => $cantidadDejada,
                            'costo_unitario' => 0,
                            'costo_total' => 0,
                            'observaciones' => $observaciones,
                            'fecha_consumo' => now()
                        ]);
                        
                        // Actualizar el stock del amenity usando el método del modelo
                        $amenity = \App\Models\Amenity::find($amenityId);
                        if ($amenity) {
                            \Log::info("ANTES de descontar stock - Amenity {$amenityId}: stock_actual = {$amenity->stock_actual}");
                            $amenity->descontarStock($cantidadDejada);
                            \Log::info("DESPUÉS de descontar stock - Amenity {$amenityId}: stock_actual = {$amenity->stock_actual}");
                            \Log::info("Stock del amenity {$amenityId} descontado: -{$cantidadDejada} (nuevo consumo)");
                            
                            // Verificar si el stock está bajo después del descuento
                            if ($amenity->verificarStockBajo()) {
                                Alert::warning('Stock Bajo', "El amenity '{$amenity->nombre}' tiene stock bajo (actual: {$amenity->stock_actual})");
                            }
                        } else {
                            \Log::error("No se pudo encontrar el amenity {$amenityId} para descontar stock");
                        }
                        
                        $amenitiesCreados++;
                        \Log::info("Amenity {$amenityId} CREADO con cantidad {$cantidadDejada}");
                    }
                    
                    $amenitiesGuardados++;
                }
            } catch (\Exception $e) {
                \Log::error("Error guardando amenity {$amenityId}: " . $e->getMessage());
                \Log::error("Datos del amenity: " . json_encode($amenityData));
            }
        }
        
        if ($amenitiesGuardados > 0) {
            $mensaje = "Se han procesado {$amenitiesGuardados} amenities: ";
            if ($amenitiesCreados > 0) {
                $mensaje .= "{$amenitiesCreados} creados";
            }
            if ($amenitiesActualizados > 0) {
                if ($amenitiesCreados > 0) $mensaje .= ", ";
                $mensaje .= "{$amenitiesActualizados} actualizados";
            }
            $mensaje .= " correctamente";
            
            // En lugar de Alert::success, pasamos el mensaje a la vista
            $mensajeAmenities = $mensaje;
        }
    }

    // Guardar observación
    $apartamentoLimpieza->observacion = $request->observacion;
    $apartamentoLimpieza->save();

    $id = $apartamentoLimpieza->id;
    Alert::success('Guardado con Éxito', 'Apartamento actualizado correctamente');

    $apartamentoId = $apartamentoLimpieza->apartamento_id;
    
    // Verificar que el apartamento existe
    $apartamento = Apartamento::find($apartamentoId);
    if (!$apartamento) {
        abort(404, 'Apartamento no encontrado');
    }
    
    $edificioId = $apartamento->edificio_id;
    
    // Verificar que el edificio existe
    if (!$edificioId) {
        abort(404, 'Edificio no encontrado para este apartamento');
    }

    $checklists = Checklist::with('items')->where('edificio_id', $edificioId)->get();
    $item_check = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)->get();

    $itemsExistentes = $item_check->pluck('estado', 'item_id')->toArray();
    $checklist_check = $item_check->whereNotNull('checklist_id')->filter(function ($item) {
        return $item->estado == 1;
    });
    $checklistsExistentes = $checklist_check->pluck('estado', 'checklist_id')->toArray();

    // Obtener amenities para esta limpieza
    $amenities = \App\Models\Amenity::activos()
        ->orderBy('categoria')
        ->orderBy('nombre')
        ->get()
        ->groupBy('categoria');
    
    // Obtener consumos existentes para esta limpieza
    $consumosExistentes = \App\Models\AmenityConsumo::where('limpieza_id', $apartamentoLimpieza->id)
        ->with('amenity')
        ->get()
        ->keyBy('amenity_id');
    
    // Calcular cantidades recomendadas para cada amenity
    $amenitiesConRecomendaciones = [];
    foreach ($amenities as $categoria => $amenitiesCategoria) {
        foreach ($amenitiesCategoria as $amenity) {
            $cantidadRecomendada = $this->calcularCantidadRecomendadaAmenity($amenity, $apartamentoLimpieza->origenReserva, $apartamentoLimpieza->apartamento);
            $consumoExistente = $consumosExistentes->get($amenity->id);
            
            $amenitiesConRecomendaciones[$categoria][] = [
                'amenity' => $amenity,
                'cantidad_recomendada' => $cantidadRecomendada,
                'consumo_existente' => $consumoExistente,
                'stock_disponible' => $amenity->stock_actual
            ];
        }
    }

    return redirect()->route('gestion.edit', $apartamentoLimpieza)->with('mensajeAmenities', $mensajeAmenities ?? null);
}

/**
 * Actualizar limpieza de zona común
 */
public function updateZonaComun(Request $request, ApartamentoLimpieza $apartamentoLimpieza)
{
    // Verificar que sea una limpieza de zona común
    if ($apartamentoLimpieza->tipo_limpieza !== 'zona_comun') {
        abort(400, 'Esta función solo es válida para zonas comunes');
    }
    
    // Guardar observación
    $apartamentoLimpieza->observacion = $request->observacion;
    $apartamentoLimpieza->save();
    
    Alert::success('Guardado con Éxito', 'Zona común actualizada correctamente');
    
    return redirect()->route('gestion.edit', $apartamentoLimpieza);
}



    /**
     * Remove the specified resource from storage.
     */
    public function finalizar(Request $request, ApartamentoLimpieza $apartamentoLimpieza)
    {
        // Verificar que todos los checklists estén marcados
        $apartamentoId = $apartamentoLimpieza->apartamento_id;
        
        // Verificar que el apartamento existe
        $apartamento = Apartamento::find($apartamentoId);
        if (!$apartamento) {
            abort(404, 'Apartamento no encontrado');
        }
        
        $edificioId = $apartamento->edificio_id;
        
        // Verificar que el edificio existe
        if (!$edificioId) {
            abort(404, 'Edificio no encontrado para este apartamento');
        }
        
        $checklists = Checklist::where('edificio_id', $edificioId)->get();
        
        // Obtener los checklists marcados
        $checklistsMarcados = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)
            ->whereNotNull('checklist_id')
            ->where('estado', 1)
            ->pluck('checklist_id')
            ->toArray();
        
        // Verificar si faltan checklists por marcar
        $checklistsFaltantes = $checklists->whereNotIn('id', $checklistsMarcados);
        
        // Si faltan checklists y no hay consentimiento, mostrar error
        if ($checklistsFaltantes->count() > 0) {
            $consentimiento = $request->input('consentimiento_finalizacion');
            
            if ($consentimiento !== 'true') {
                $nombresFaltantes = $checklistsFaltantes->pluck('nombre')->implode(', ');
                Alert::error('No se puede finalizar', 'Debes completar todos los checklists antes de finalizar: ' . $nombresFaltantes . ' O marcar el consentimiento de finalización.');
                
                // Redirigir de vuelta al formulario de edición
                return redirect()->route('gestion.edit', $apartamentoLimpieza);
            }
            
            // Si hay consentimiento, guardar la información del consentimiento
            $apartamentoLimpieza->consentimiento_finalizacion = true;
            $apartamentoLimpieza->motivo_consentimiento = $request->input('motivo_consentimiento', 'Usuario confirmó que puede finalizar sin completar todos los checklists');
            $apartamentoLimpieza->fecha_consentimiento = now();
            $apartamentoLimpieza->user_id_consentimiento = auth()->id();
            $apartamentoLimpieza->save();
            
            // Mostrar advertencia pero permitir continuar
            $nombresFaltantes = $checklistsFaltantes->pluck('nombre')->implode(', ');
            Alert::warning('Finalización con Checklists Incompletos', 'Has confirmado que puedes finalizar sin completar todos los checklists. Checklists faltantes: ' . $nombresFaltantes);
        }
        
        $hoy = Carbon::now();
        $apartamentoLimpieza->status_id = 3;
        $apartamentoLimpieza->fecha_fin = $hoy;
        $apartamentoLimpieza->save();
        
        // Actualizar tarea asignada si existe
        if ($apartamentoLimpieza->tarea_asignada_id) {
            $tarea = TareaAsignada::find($apartamentoLimpieza->tarea_asignada_id);
            if ($tarea && $tarea->estado === 'en_progreso') {
                $tarea->update([
                    'estado' => 'completada',
                    'fecha_fin_real' => $hoy
                ]);
                
                Log::info('Tarea finalizada desde limpieza', [
                    'tarea_id' => $tarea->id,
                    'limpieza_id' => $apartamentoLimpieza->id,
                    'fecha_fin' => $hoy
                ]);
            }
        }
        
        $reserva = Reserva::find($apartamentoLimpieza->reserva_id);
        if ($reserva != null) {
            $reserva->fecha_limpieza = $hoy;
            $reserva->save();
        }

        // DESCUENTO AUTOMÁTICO DE AMENITIES DE LIMPIEZA
        $this->descontarAmenitiesLimpieza($apartamentoLimpieza);

        // Crear alerta si hay observaciones al finalizar la limpieza
        if (!empty($apartamentoLimpieza->observacion)) {
            $apartamentoNombre = $apartamentoLimpieza->apartamento->nombre ?? 'Apartamento';
            AlertService::createCleaningObservationAlert(
                $apartamentoLimpieza->id,
                $apartamentoNombre,
                $apartamentoLimpieza->observacion
            );
        }

        Alert::success('Finalizado con Éxito', 'Apartamento Finalizado correctamente');

        return redirect()->route('gestion.index');
    }
    
    /**
     * Finalizar limpieza de zona común
     */
    public function finalizarZonaComun(ApartamentoLimpieza $apartamentoLimpieza)
    {
        // Verificar que sea una limpieza de zona común
        if ($apartamentoLimpieza->tipo_limpieza !== 'zona_comun') {
            abort(400, 'Esta función solo es válida para zonas comunes');
        }
        
        $hoy = Carbon::now();
        $apartamentoLimpieza->status_id = 3; // Finalizado
        $apartamentoLimpieza->fecha_fin = $hoy;
        $apartamentoLimpieza->save();
        
        // Actualizar tarea asignada si existe
        if ($apartamentoLimpieza->tarea_asignada_id) {
            $tarea = TareaAsignada::find($apartamentoLimpieza->tarea_asignada_id);
            if ($tarea && $tarea->estado === 'en_progreso') {
                $tarea->update([
                    'estado' => 'completada',
                    'fecha_fin_real' => $hoy
                ]);
                
                Log::info('Tarea finalizada desde limpieza de zona común', [
                    'tarea_id' => $tarea->id,
                    'limpieza_id' => $apartamentoLimpieza->id,
                    'fecha_fin' => $hoy
                ]);
            }
        }
        
        // DESCUENTO AUTOMÁTICO DE AMENITIES DE LIMPIEZA
        $this->descontarAmenitiesLimpieza($apartamentoLimpieza);
        
        // Crear alerta si hay observaciones al finalizar la limpieza
        if (!empty($apartamentoLimpieza->observacion)) {
            $zonaComunNombre = $apartamentoLimpieza->zonaComun->nombre ?? 'Zona Común';
            AlertService::createCleaningObservationAlert(
                $apartamentoLimpieza->id,
                $zonaComunNombre,
                $apartamentoLimpieza->observacion
            );
        }
        
        Alert::success('Finalizado con Éxito', 'Zona Común finalizada correctamente');
        
        return redirect()->route('gestion.index');
    }

    public function limpiezaFondo(Request $request) {
        $apartamentos = LimpiezaFondo::all();
        return view('admin.limpieza.index', compact('apartamentos'));

    }

    public function limpiezaFondoDestroy($id) {
        $limpieza = LimpiezaFondo::find($id);
        $limpieza->delete();
        return redirect(route('admin.limpiezaFondo.index'));

    }

    public function limpiezaCreate(Request $request) {
        $apartamentos = Apartamento::all();
        return view('admin.limpieza.create', compact('apartamentos'));

    }
    public function limpiezaFondoEdit($id) {
        $limpieza = LimpiezaFondo::find($id);
        $apartamentos = Apartamento::all();

        return view('admin.limpieza.edit', compact('apartamentos', 'limpieza'));

    }

    public function limpiezaFondoStore(Request $request) {
        $rules = [
            'fecha' => 'required|date',
            'apartamento_id' => 'required'
        ];

        // Validar los datos del formulario
        $validatedData = $request->validate($rules);
        $limpiezaAFondo = LimpiezaFondo::create([
            'apartamento_id' => $request->apartamento_id,
            'fecha' => $request->fecha
        ]);
        Alert::success('Fizalizado con Exito', 'Apartamento Fizalizado correctamente');

        return redirect()->route('admin.limpiezaFondo.index');
    }

    /**
     * Update checkbox state via AJAX
     */
    public function updateCheckbox(Request $request)
    {
        try {
            $type = $request->input('type');
            $id = $request->input('id');
            $checked = $request->input('checked');
            $limpiezaId = $request->input('limpieza_id');

            Log::info('updateCheckbox llamado', [
                'type' => $type,
                'id' => $id,
                'checked' => $checked,
                'limpieza_id' => $limpiezaId
            ]);

            // Obtener la tarea desde la limpieza
            $apartamentoLimpieza = ApartamentoLimpieza::find($limpiezaId);
            if (!$apartamentoLimpieza) {
                Log::error('Limpieza no encontrada', ['limpieza_id' => $limpiezaId]);
                return response()->json(['success' => false, 'message' => 'Limpieza no encontrada'], 404);
            }

            $tareaId = $apartamentoLimpieza->tarea_asignada_id;
            if (!$tareaId) {
                Log::error('Tarea no encontrada', ['limpieza_id' => $limpiezaId, 'tarea_asignada_id' => $apartamentoLimpieza->tarea_asignada_id]);
                return response()->json(['success' => false, 'message' => 'Tarea no encontrada'], 404);
            }

            if ($type === 'item') {
                if ($checked == 1) {
                    // Insertar o actualizar en tarea_checklist_completados
                    DB::table('tarea_checklist_completados')->updateOrInsert(
                        [
                            'tarea_asignada_id' => $tareaId,
                            'item_checklist_id' => $id
                        ],
                        [
                            'completado_por' => Auth::id(),
                            'fecha_completado' => now(),
                            'estado' => 1,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                    Log::info('Item marcado como completado', ['tarea_id' => $tareaId, 'item_id' => $id]);
                } else {
                    // Eliminar de tarea_checklist_completados
                    DB::table('tarea_checklist_completados')
                        ->where('tarea_asignada_id', $tareaId)
                        ->where('item_checklist_id', $id)
                        ->delete();
                    Log::info('Item desmarcado', ['tarea_id' => $tareaId, 'item_id' => $id]);
                }
            } else if ($type === 'checklist') {
                if ($checked == 1) {
                    // Marcar checklist como completado
                    DB::table('tarea_checklist_completados')->updateOrInsert(
                        [
                            'tarea_asignada_id' => $tareaId,
                            'checklist_id' => $id
                        ],
                        [
                            'completado_por' => Auth::id(),
                            'fecha_completado' => now(),
                            'estado' => 1,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                    Log::info('Checklist marcado como completado', ['tarea_id' => $tareaId, 'checklist_id' => $id]);
                } else {
                    // Desmarcar checklist
                    DB::table('tarea_checklist_completados')
                        ->where('tarea_asignada_id', $tareaId)
                        ->where('checklist_id', $id)
                        ->delete();
                    Log::info('Checklist desmarcado', ['tarea_id' => $tareaId, 'checklist_id' => $id]);
                }
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error en updateCheckbox: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get checklist status for AJAX requests
     */
    public function checklistStatus(ApartamentoLimpieza $apartamentoLimpieza)
    {
        $apartamentoId = $apartamentoLimpieza->apartamento_id;
        
        // Verificar que el apartamento existe
        $apartamento = Apartamento::find($apartamentoId);
        if (!$apartamento) {
            return response()->json(['error' => 'Apartamento no encontrado'], 404);
        }
        
        $edificioId = $apartamento->edificio_id;
        
        // Verificar que el edificio existe
        if (!$edificioId) {
            return response()->json(['error' => 'Edificio no encontrado para este apartamento'], 404);
        }
        
        $checklists = Checklist::where('edificio_id', $edificioId)->get();
        
        $checklistsMarcados = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)
            ->whereNotNull('checklist_id')
            ->where('estado', 1)
            ->pluck('checklist_id')
            ->toArray();
        
        $checklistsFaltantes = $checklists->whereNotIn('id', $checklistsMarcados);
        
        return response()->json([
            'total' => $checklists->count(),
            'completados' => count($checklistsMarcados),
            'faltantes' => $checklistsFaltantes->pluck('nombre')->toArray(),
            'puedeFinalizar' => $checklistsFaltantes->count() === 0
        ]);
    }

    /**
     * Editar limpieza de zona común
     */
    public function editZonaComun($id)
    {
        $apartamentoLimpieza = ApartamentoLimpieza::with(['zonaComun', 'empleada', 'status'])
            ->where('id', $id)
            ->where('tipo_limpieza', 'zona_comun')
            ->firstOrFail();
        
        $zonaComun = $apartamentoLimpieza->zonaComun;
        if (!$zonaComun) {
            abort(404, 'Zona común no encontrada');
        }
        
        // Obtener checklists para zonas comunes
        $checklists = \App\Models\ChecklistZonaComun::activos()->ordenados()->get();
        
        // Obtener items existentes para esta limpieza
        $item_check = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)->get();
        $itemsExistentes = $item_check->pluck('estado', 'item_id')->toArray();
        
        // Obtener checklists marcados
        $checklist_check = $item_check->whereNotNull('checklist_zona_comun_id')->filter(function ($item) {
            return $item->estado == 1;
        });
        $checklistsExistentes = $checklist_check->pluck('estado', 'checklist_zona_comun_id')->toArray();
        
        return view('gestion.edit-zona-comun', compact(
            'apartamentoLimpieza',
            'zonaComun',
            'id', 
            'checklists', 
            'itemsExistentes', 
            'checklistsExistentes'
        ));
    }

    /**
     * Crear limpieza para zona común
     */
    public function createZonaComun($id)
    {
        $zonaComun = \App\Models\ZonaComun::findOrFail($id);
        
        // Verificar si ya existe una limpieza activa para esta zona
        $limpiezaExistente = ApartamentoLimpieza::where('zona_comun_id', $id)
            ->where('tipo_limpieza', 'zona_comun')
            ->whereNull('fecha_fin')
            ->first();

        if ($limpiezaExistente) {
            Alert::warning('Atención', 'Ya existe una limpieza activa para esta zona común.');
            return redirect()->route('gestion.index');
        }

        // Crear nueva limpieza para zona común
        $apartamentoLimpieza = ApartamentoLimpieza::create([
            'zona_comun_id' => $id,
            'tipo_limpieza' => 'zona_comun',
            'fecha_comienzo' => Carbon::now(),
            'status_id' => 2, // En proceso
            'empleada_id' => Auth::user()->id,
            'user_id' => Auth::user()->id
        ]);

        Alert::success('Éxito', 'Limpieza de zona común iniciada correctamente.');
        return redirect()->route('gestion.edit', $apartamentoLimpieza->id);
    }

    /**
     * Get checklist status for zona común AJAX requests
     */
    public function checklistStatusZonaComun(ApartamentoLimpieza $apartamentoLimpieza)
    {
        if ($apartamentoLimpieza->tipo_limpieza !== 'zona_comun') {
            return response()->json(['error' => 'No es una limpieza de zona común'], 400);
        }

        $checklists = \App\Models\ChecklistZonaComun::activos()->ordenados()->get();
        
        $checklistsMarcados = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)
            ->whereNotNull('checklist_zona_comun_id')
            ->where('estado', 1)
            ->pluck('checklist_zona_comun_id')
            ->toArray();
        
        $checklistsFaltantes = $checklists->whereNotIn('id', $checklistsMarcados);
        
        return response()->json([
            'total' => $checklists->count(),
            'completados' => count($checklistsMarcados),
            'faltantes' => $checklistsFaltantes->pluck('nombre')->toArray(),
            'puedeFinalizar' => $checklistsFaltantes->count() === 0
        ]);
    }
    
    /**
     * Calcular cantidad recomendada para un amenity según las reglas de consumo
     */
    private function calcularCantidadRecomendadaAmenity($amenity, $reserva, $apartamento)
    {
        $numeroPersonas = $reserva ? $reserva->numero_personas : 1;
        $dias = $reserva ? \Carbon\Carbon::parse($reserva->fecha_entrada)->diffInDays($reserva->fecha_salida) : 1;
        
        switch ($amenity->tipo_consumo) {
            case 'por_reserva':
                // Para amenities por reserva (ej: gafas, toallas, etc.)
                $cantidad = $amenity->consumo_por_reserva ?? 1;
                
                // Aplicar límites mínimo y máximo si están configurados
                if ($amenity->consumo_minimo_reserva) {
                    $cantidad = max($cantidad, $amenity->consumo_minimo_reserva);
                }
                if ($amenity->consumo_maximo_reserva) {
                    $cantidad = min($cantidad, $amenity->consumo_maximo_reserva);
                }
                
                return $cantidad;
                
            case 'por_tiempo':
                // Para amenities por tiempo (ej: ambientador cada X días)
                if ($amenity->duracion_dias && $amenity->duracion_dias > 0) {
                    $cantidad = ceil($dias / $amenity->duracion_dias);
                    return max(1, $cantidad); // Mínimo 1
                }
                return 1;
                
            case 'por_persona':
                // Para amenities por persona por día (ej: champú, gel, etc.)
                $cantidadPorPersonaPorDia = $amenity->consumo_por_persona ?? 1;
                $cantidad = $cantidadPorPersonaPorDia * $numeroPersonas * $dias;
                
                // Aplicar límites mínimo y máximo si están configurados
                if ($amenity->consumo_minimo_reserva) {
                    $cantidad = max($cantidad, $amenity->consumo_minimo_reserva);
                }
                if ($amenity->consumo_maximo_reserva) {
                    $cantidad = min($cantidad, $amenity->consumo_maximo_reserva);
                }
                
                return ceil($cantidad);
                
            default:
                return 1;
        }
    }

    /**
     * Mostrar información de una reserva
     */
    public function mostrarInfoReserva($id)
    {
        $reserva = Reserva::with(['apartamento', 'cliente'])->findOrFail($id);
        return view('gestion.reserva-info', compact('reserva'));
    }

    /**
     * Ver limpieza completada (solo lectura)
     */
    public function verLimpiezaCompletada($id)
    {
        $apartamentoLimpieza = ApartamentoLimpieza::with([
            'apartamento.edificio', 
            'zonaComun', 
            'empleada', 
            'estado',
            'fotos' => function($query) {
                $query->orderBy('created_at', 'desc');
            }
        ])->findOrFail($id);

        // Obtener fotos desde ApartamentoLimpiezaItem
        $fotos = \App\Models\ApartamentoLimpiezaItem::where('id_limpieza', $id)
            ->whereNotNull('photo_url')
            ->orderBy('created_at', 'desc')
            ->get();

        // Combinar fotos de ambas fuentes
        $todasLasFotos = $apartamentoLimpieza->fotos->merge($fotos);

        // Obtener amenities de consumo para esta limpieza
        $amenities = \App\Models\Amenity::activos()
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get()
            ->groupBy('categoria');
        
        // Obtener consumos existentes para esta limpieza
        $consumosExistentes = \App\Models\AmenityConsumo::where('limpieza_id', $apartamentoLimpieza->id)
            ->with('amenity')
            ->get()
            ->keyBy('amenity_id');

        // Obtener checklists con sus items si existen
        $checklists = [];
        if ($apartamentoLimpieza->apartamento && $apartamentoLimpieza->apartamento->edificio_id) {
            $checklists = \App\Models\Checklist::with('items')->where('edificio_id', $apartamentoLimpieza->apartamento->edificio_id)->get();
        }

        // Obtener items existentes de la limpieza
        $itemsExistentes = [];
        if ($apartamentoLimpieza->id) {
            $itemsExistentes = \App\Models\ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)->get();
        }

        return view('gestion.ver-limpieza', compact(
            'apartamentoLimpieza', 
            'checklists', 
            'itemsExistentes', 
            'todasLasFotos',
            'amenities',
            'consumosExistentes'
        ));
    }

    /**
     * DESCUENTO AUTOMÁTICO DE AMENITIES DE LIMPIEZA
     */
    private function descontarAmenitiesLimpieza(ApartamentoLimpieza $apartamentoLimpieza)
    {
        try {
            \Log::info('Iniciando descuento automático de amenities para limpieza ID: ' . $apartamentoLimpieza->id);
            
            // Obtener amenities de limpieza activos
            $amenitiesLimpieza = \App\Models\Amenity::where('categoria', 'Limpieza')
                ->where('activo', true)
                ->get();

            \Log::info('Amenities de limpieza encontrados: ' . $amenitiesLimpieza->count());
            
            $totalGasto = 0;
            $amenitiesUsados = [];

            foreach ($amenitiesLimpieza as $amenity) {
                \Log::info('Procesando amenity: ' . $amenity->nombre . ' (Stock: ' . $amenity->stock_actual . ')');
                
                // Calcular cantidad recomendada para esta limpieza
                $cantidadRecomendada = $this->calcularCantidadRecomendadaAmenity($amenity, $apartamentoLimpieza->reserva, $apartamentoLimpieza->apartamento);
                
                \Log::info('Cantidad recomendada calculada: ' . $cantidadRecomendada);
                
                if ($cantidadRecomendada > 0) {
                    \Log::info('Cantidad > 0, verificando stock...');
                    
                    // Verificar stock disponible
                    if ($amenity->stock_actual >= $cantidadRecomendada) {
                        \Log::info('Stock suficiente, procediendo con descuento...');
                        
                        // Descontar del stock
                        $stockAnterior = $amenity->stock_actual;
                        $amenity->stock_actual -= $cantidadRecomendada;
                        $amenity->save();
                        
                        \Log::info('Stock actualizado: ' . $stockAnterior . ' -> ' . $amenity->stock_actual);

                        // Calcular costo
                        $costoTotal = $cantidadRecomendada * $amenity->precio_compra;
                        $totalGasto += $costoTotal;
                        
                        \Log::info('Costo calculado: €' . $costoTotal);

                        // Registrar el consumo
                        \Log::info('Creando registro de consumo...');
                        \App\Models\AmenityConsumo::create([
                            'amenity_id' => $amenity->id,
                            'reserva_id' => $apartamentoLimpieza->reserva_id,
                            'apartamento_id' => $apartamentoLimpieza->apartamento_id,
                            'limpieza_id' => $apartamentoLimpieza->id,
                            'user_id' => auth()->id(),
                            'tipo_consumo' => 'limpieza',
                            'cantidad_consumida' => $cantidadRecomendada,
                            'cantidad_anterior' => $stockAnterior,
                            'cantidad_actual' => $amenity->stock_actual,
                            'costo_unitario' => $amenity->precio_compra,
                            'costo_total' => $costoTotal,
                            'observaciones' => 'Descuento automático al finalizar limpieza',
                            'fecha_consumo' => now()
                        ]);
                        
                        \Log::info('Consumo registrado exitosamente');

                        $amenitiesUsados[] = [
                            'nombre' => $amenity->nombre,
                            'cantidad' => $cantidadRecomendada,
                            'unidad' => $amenity->unidad_medida,
                            'costo' => $costoTotal
                        ];

                        // Verificar si el stock está bajo después del descuento
                        if ($amenity->stock_actual <= $amenity->stock_minimo) {
                            \Alert::warning('Stock Bajo', "El amenity '{$amenity->nombre}' tiene stock bajo (actual: {$amenity->stock_actual} {$amenity->unidad_medida})");
                        }
                    } else {
                        // Stock insuficiente
                        \Alert::error('Stock Insuficiente', "No hay suficiente stock de '{$amenity->nombre}' para esta limpieza. Stock disponible: {$amenity->stock_actual} {$amenity->unidad_medida}, Necesario: {$cantidadRecomendada} {$amenity->unidad_medida}");
                    }
                }
            }

            // Mostrar resumen de amenities utilizados
            if (!empty($amenitiesUsados)) {
                $mensaje = "Amenities de limpieza utilizados:\n";
                foreach ($amenitiesUsados as $amenity) {
                    $mensaje .= "• {$amenity['nombre']}: {$amenity['cantidad']} {$amenity['unidad']} (€{$amenity['costo']})\n";
                }
                $mensaje .= "\nTotal gasto en amenities: €{$totalGasto}";
                
                \Alert::info('Amenities Aplicados', $mensaje);
            }

        } catch (\Exception $e) {
            \Log::error('Error al descontar amenities de limpieza: ' . $e->getMessage());
            \Alert::error('Error', 'Error al procesar amenities de limpieza: ' . $e->getMessage());
        }
    }

    /**
     * Obtener estadísticas del dashboard de limpieza
     */
    private function getDashboardStats()
    {
        $user = Auth::user();
        $hoy = Carbon::today();
        
        try {
            // Obtener apartamentos que SALEN hoy (necesitan limpieza) - misma lógica que /gestion
            $apartamentosPendientesHoy = \DB::table('reservas')
                ->whereNull('fecha_limpieza')
                ->where('estado_id', '!=', 4)
                ->whereDate('fecha_salida', $hoy)
                ->pluck('apartamento_id')
                ->toArray();
            
            // Obtener limpiezas de fondo programadas para hoy
            $limpiezasFondoHoy = \DB::table('limpieza_fondo')
                ->whereDate('fecha', $hoy)
                ->pluck('apartamento_id')
                ->toArray();
            
            // Combinar todos los apartamentos que necesitan limpieza hoy
            $apartamentosNecesitanLimpieza = array_merge($apartamentosPendientesHoy, $limpiezasFondoHoy);
            $apartamentosNecesitanLimpieza = array_unique($apartamentosNecesitanLimpieza);
            
            // Obtener limpiezas ya asignadas a esta empleada para hoy
            $limpiezasAsignadasHoy = \DB::table('apartamento_limpieza')
                ->where('empleada_id', $user->id)
                ->whereDate('fecha_comienzo', $hoy)
                ->pluck('apartamento_id')
                ->toArray();
            
            // Apartamentos pendientes de limpieza (necesitan limpieza pero no están asignados)
            $apartamentosPendientes = array_diff($apartamentosNecesitanLimpieza, $limpiezasAsignadasHoy);
            
            // Estadísticas del día
            $limpiezasHoy = count($apartamentosNecesitanLimpieza); // Total de apartamentos que necesitan limpieza
            $limpiezasAsignadas = count($limpiezasAsignadasHoy); // Total de limpiezas asignadas a esta empleada
            
            // Obtener limpiezas completadas hoy por esta empleada
            $limpiezasCompletadasHoy = \DB::table('apartamento_limpieza')
                ->where('empleada_id', $user->id)
                ->whereDate('fecha_comienzo', $hoy)
                ->where('status_id', 2) // Completada
                ->count();
                
            // Obtener limpiezas en proceso hoy por esta empleada
            $limpiezasPendientesHoy = \DB::table('apartamento_limpieza')
                ->where('empleada_id', $user->id)
                ->whereDate('fecha_comienzo', $hoy)
                ->where('status_id', 1) // En proceso
                ->count();
                
            // Obtener incidencias pendientes del usuario
            $incidenciasPendientes = \DB::table('incidencias')
                ->where('empleada_id', $user->id)
                ->where('estado', 'pendiente')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
                
            // Obtener estadísticas de la semana
            $inicioSemana = $hoy->copy()->startOfWeek();
            $finSemana = $hoy->copy()->endOfWeek();
            
            // Limpiezas asignadas a esta empleada en la semana
            $limpiezasSemana = \DB::table('apartamento_limpieza')
                ->where('empleada_id', $user->id)
                ->whereBetween('fecha_comienzo', [$inicioSemana, $finSemana])
                ->count();
                
            $limpiezasCompletadasSemana = \DB::table('apartamento_limpieza')
                ->where('empleada_id', $user->id)
                ->whereBetween('fecha_comienzo', [$inicioSemana, $finSemana])
                ->where('status_id', 2)
                ->count();
                
            // Calcular porcentaje de completado de la semana
            $porcentajeSemana = $limpiezasSemana > 0 ? round(($limpiezasCompletadasSemana / $limpiezasSemana) * 100) : 0;
                
            // Obtener estado del fichaje actual
            $fichajeActual = Fichaje::where('user_id', $user->id)
                ->whereDate('hora_entrada', $hoy)
                ->whereNull('hora_salida')
                ->first();
                
            // Obtener estadísticas de calidad de limpieza (si existen análisis)
            $analisisRecientes = [];
            try {
                $analisisRecientes = \DB::table('photo_analyses')
                    ->where('empleada_id', $user->id)
                    ->whereDate('fecha_analisis', '>=', $hoy->copy()->subDays(7))
                    ->select('calidad_general', \DB::raw('count(*) as total'))
                    ->groupBy('calidad_general')
                    ->get()
                    ->pluck('total', 'calidad_general')
                    ->toArray();
            } catch (\Exception $e) {
                // Si hay error, usar array vacío
                $analisisRecientes = [];
            }
            
            return [
                'limpiezasHoy' => $limpiezasHoy,
                'limpiezasAsignadas' => $limpiezasAsignadas,
                'limpiezasCompletadasHoy' => $limpiezasCompletadasHoy,
                'limpiezasPendientesHoy' => $limpiezasPendientesHoy,
                'apartamentosPendientes' => count($apartamentosPendientes),
                'incidenciasPendientes' => $incidenciasPendientes,
                'limpiezasSemana' => $limpiezasSemana,
                'limpiezasCompletadasSemana' => $limpiezasCompletadasSemana,
                'porcentajeSemana' => $porcentajeSemana,
                'fichajeActual' => $fichajeActual,
                'analisisRecientes' => $analisisRecientes,
                'hoy' => $hoy->format('d/m/Y'),
                'diaSemana' => $hoy->locale('es')->dayName
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error obteniendo estadísticas del dashboard: ' . $e->getMessage());
            
            return [
                'limpiezasHoy' => 0,
                'limpiezasAsignadas' => 0,
                'limpiezasCompletadasHoy' => 0,
                'limpiezasPendientesHoy' => 0,
                'apartamentosPendientes' => 0,
                'incidenciasPendientes' => collect(),
                'limpiezasSemana' => 0,
                'limpiezasCompletadasSemana' => 0,
                'porcentajeSemana' => 0,
                'fichajeActual' => null,
                'analisisRecientes' => [],
                'hoy' => $hoy->format('d/m/Y'),
                'diaSemana' => $hoy->locale('es')->dayName,
                'error' => 'Error al cargar datos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener estadísticas del dashboard via AJAX
     */
    public function estadisticas()
    {
        $user = Auth::user();
        $hoy = Carbon::today();
        $inicioMes = $hoy->copy()->startOfMonth();
        
        // Estadísticas del mes
        $limpiezasMes = ApartamentoLimpieza::where('empleada_id', $user->id)
            ->whereBetween('fecha_comienzo', [$inicioMes, $hoy])
            ->count();
            
        $limpiezasCompletadasMes = ApartamentoLimpieza::where('empleada_id', $user->id)
            ->whereBetween('fecha_comienzo', [$inicioMes, $hoy])
            ->where('status_id', 2)
            ->count();
            
        // Calcular horas trabajadas del mes
        $horasTrabajadasMes = Fichaje::where('user_id', $user->id)
            ->whereBetween('hora_entrada', [$inicioMes->startOfDay(), $hoy->endOfDay()])
            ->whereNotNull('hora_salida')
            ->get()
            ->sum(function($fichaje) {
                if ($fichaje->hora_entrada && $fichaje->hora_salida) {
                    $inicio = Carbon::parse($fichaje->hora_entrada);
                    $fin = Carbon::parse($fichaje->hora_salida);
                    return $inicio->diffInHours($fin, false);
                }
                return 0;
            });
            
        return response()->json([
            'limpiezas_mes' => $limpiezasMes,
            'limpiezas_completadas_mes' => $limpiezasCompletadasMes,
            'porcentaje_mes' => $limpiezasMes > 0 ? round(($limpiezasCompletadasMes / $limpiezasMes) * 100) : 0,
            'horas_trabajadas_mes' => round($horasTrabajadasMes, 1)
        ]);
    }
}
