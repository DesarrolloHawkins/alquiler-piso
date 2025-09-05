<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmpleadaHorario;
use App\Models\TipoTarea;
use App\Models\TurnoTrabajo;
use App\Models\TareaAsignada;
use App\Models\Apartamento;
use App\Models\ZonaComun;
use App\Models\ApartamentoLimpieza;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerarTurnosTrabajo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'turnos:generar {fecha?} {--force : Forzar regeneración de turnos existentes} {--ia : Usar OpenAI para optimizar asignación} {--test-ia : Probar sistema IA con respuesta simulada}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera turnos de trabajo para las empleadas de limpieza basado en horarios y tareas pendientes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fecha = $this->argument('fecha') ? Carbon::parse($this->argument('fecha')) : today();
        $forzar = $this->option('force');
        $usarIA = $this->option('ia');
        $testIA = $this->option('test-ia');
        
        $this->info("🔄 Generando turnos para la fecha: {$fecha->format('Y-m-d')}");
        
        if ($usarIA) {
            $this->info("🤖 Modo IA activado - Usando OpenAI para optimización");
        } elseif ($testIA) {
            $this->info("🧪 Modo Test IA activado - Usando respuesta simulada");
        }
        
        try {
            DB::beginTransaction();
            
            // 1. Obtener empleadas disponibles para la fecha
            $empleadasDisponibles = $this->obtenerEmpleadasDisponibles($fecha);
            
            if ($empleadasDisponibles->isEmpty()) {
                $this->warn('⚠️  No hay empleadas disponibles para esta fecha');
                return;
            }
            
            $this->info("👥 Empleadas disponibles: {$empleadasDisponibles->count()}");
            
            // 2. Verificar si ya existen turnos para esta fecha
            if (!$forzar && TurnoTrabajo::porFecha($fecha)->exists()) {
                $this->warn('⚠️  Ya existen turnos para esta fecha. Usa --force para regenerar.');
                return;
            }
            
            // 3. Eliminar turnos existentes si se fuerza
            if ($forzar) {
                TurnoTrabajo::porFecha($fecha)->delete();
                $this->info('🗑️  Turnos existentes eliminados');
            }
            
            // 4. Generar tareas pendientes
            $tareasPendientes = $this->generarTareasPendientes($fecha);
            
            if ($tareasPendientes->isEmpty()) {
                $this->warn('⚠️  No hay tareas pendientes para generar');
                return;
            }
            
            $this->info("📋 Tareas pendientes: {$tareasPendientes->count()}");
            
            // 5. Distribuir tareas entre empleadas
            if ($usarIA) {
                // Usar IA para optimizar asignación
                $asignacionIA = $this->generarAsignacionConIA($empleadasDisponibles, $tareasPendientes, $fecha);
                
                if ($asignacionIA) {
                    $this->info("✅ Asignación optimizada por IA recibida");
                    $turnosGenerados = $this->crearTurnosDesdeIA($asignacionIA, $empleadasDisponibles, $fecha);
                } else {
                    $this->warn("⚠️  Fallback al sistema tradicional");
                    $turnosGenerados = $this->distribuirTareasEntreEmpleadas($empleadasDisponibles, $tareasPendientes, $fecha);
                }
            } elseif ($testIA) {
                // Usar respuesta simulada de IA para probar
                $asignacionIA = $this->generarRespuestaSimuladaIA($empleadasDisponibles, $tareasPendientes);
                $this->info("✅ Respuesta simulada de IA generada");
                $turnosGenerados = $this->crearTurnosDesdeIA($asignacionIA, $empleadasDisponibles, $fecha);
            } else {
                // Usar sistema tradicional
            $turnosGenerados = $this->distribuirTareasEntreEmpleadas($empleadasDisponibles, $tareasPendientes, $fecha);
            }
            
            $this->info("✅ Turnos generados exitosamente: {$turnosGenerados}");
            
            DB::commit();
            
            // 6. Mostrar resumen
            $this->mostrarResumen($fecha);
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error al generar turnos: " . $e->getMessage());
            Log::error('Error generando turnos: ' . $e->getMessage(), [
                'fecha' => $fecha->format('Y-m-d'),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Obtener empleadas disponibles para una fecha específica
     */
    private function obtenerEmpleadasDisponibles($fecha)
    {
        $diaSemana = $fecha->dayOfWeek;
        $diasColumnas = [
            1 => 'lunes',
            2 => 'martes', 
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
            0 => 'domingo'
        ];
        
        $columnaDia = $diasColumnas[$diaSemana] ?? 'lunes';
        
        // Obtener empleadas que trabajan ese día de la semana
        $empleadasQueTrabajan = EmpleadaHorario::activos()
            ->where($columnaDia, true)
            ->with('user')
            ->get();
        
        // Filtrar por días libres por semana
        $empleadasDisponibles = $empleadasQueTrabajan->filter(function($empleada) use ($fecha) {
            return $this->estaDisponibleEnFecha($empleada, $fecha);
        });
        
        return $empleadasDisponibles;
    }
    
    /**
     * Verificar si una empleada está disponible en una fecha específica
     * considerando sus días libres por semana
     */
    private function estaDisponibleEnFecha($empleada, $fecha)
    {
        // Usar el nuevo modelo EmpleadaDiasLibres para verificar disponibilidad
        return \App\Models\EmpleadaDiasLibres::estaDisponibleEnFecha($empleada->id, $fecha);
    }
    
    /**
     * Generar lista de tareas pendientes para la fecha
     */
    private function generarTareasPendientes($fecha)
    {
        $tareas = collect();
        
        // 1. Apartamentos que necesitan limpieza HOY (usando la lógica correcta del sistema)
        $apartamentosPendientesHoy = $this->obtenerApartamentosPendientesHoy($fecha);
            
        foreach ($apartamentosPendientesHoy as $apartamento) {
            $tipoTarea = TipoTarea::activos()
                ->limpiezaApartamentos()
                ->first();
                
            if ($tipoTarea) {
                $tareas->push([
                    'tipo_tarea' => $tipoTarea,
                    'apartamento_id' => $apartamento->id,
                    'zona_comun_id' => null,
                    'prioridad' => $this->calcularPrioridadApartamento($apartamento, $tipoTarea),
                    'tiempo_estimado' => $tipoTarea->tiempo_estimado_minutos
                ]);
            }
        }
        
        // 2. Limpiezas de zonas comunes pendientes
        $limpiezasZonasComunes = ApartamentoLimpieza::whereDate('fecha_comienzo', $fecha)
            ->where('tipo_limpieza', 'zona_comun')
            ->whereIn('status_id', [1, 2])
            ->with(['zonaComun'])
            ->get();
            
        foreach ($limpiezasZonasComunes as $limpieza) {
            $tipoTarea = TipoTarea::activos()
                ->limpiezaZonasComunes()
                ->first();
                
            if ($tipoTarea) {
                $tareas->push([
                    'tipo_tarea' => $tipoTarea,
                    'apartamento_id' => null,
                    'zona_comun_id' => $limpieza->zona_comun_id,
                    'prioridad' => $this->calcularPrioridadLimpieza($limpieza, $tipoTarea),
                    'tiempo_estimado' => $tipoTarea->tiempo_estimado_minutos
                ]);
            }
        }
        
        // 3. Tareas de limpieza común por TODOS los edificios (siempre se generan)
        $todosLosEdificios = \App\Models\Edificio::all();
        
        foreach ($todosLosEdificios as $edificio) {
            $tipoTareaLimpiezaComun = TipoTarea::activos()
                ->limpiezaZonasComunes()
                ->first();
                
            if ($tipoTareaLimpiezaComun) {
                $tareas->push([
                    'tipo_tarea' => $tipoTareaLimpiezaComun,
                    'apartamento_id' => null,
                    'zona_comun_id' => null,
                    'edificio_id' => $edificio->id,
                    'prioridad' => $tipoTareaLimpiezaComun->prioridad_base,
                    'tiempo_estimado' => $tipoTareaLimpiezaComun->tiempo_estimado_minutos,
                    'motivo_prioridad' => "Limpieza común edificio {$edificio->nombre} (ID: {$edificio->id}) - SIEMPRE OBLIGATORIA"
                ]);
            }
        }
        
        // 4. Tareas generales (oficinas, amenities, etc.) - excluyendo limpieza y mantenimiento
        $tareasGenerales = TipoTarea::activos()
            ->whereNotIn('categoria', ['limpieza_apartamento', 'limpieza_zona_comun', 'mantenimiento'])
            ->get();
            
        foreach ($tareasGenerales as $tipoTarea) {
            $tareas->push([
                'tipo_tarea' => $tipoTarea,
                'apartamento_id' => null,
                'zona_comun_id' => null,
                'edificio_id' => null,
                'prioridad' => $tipoTarea->prioridad_base,
                'tiempo_estimado' => $tipoTarea->tiempo_estimado_minutos
            ]);
        }
        
        // Ordenar por prioridad (mayor primero)
        return $tareas->sortByDesc('prioridad')->values();
    }
    
    /**
     * Obtener apartamentos que necesitan limpieza HOY usando la lógica correcta del sistema
     */
    private function obtenerApartamentosPendientesHoy($fecha)
    {
        $hoy = $fecha->toDateString();
        
        // 1. Reservas que SALEN hoy (necesitan limpieza)
        $reservasSalidaHoy = \App\Models\Reserva::whereNull('fecha_limpieza')
            ->where('estado_id', '!=', 4)
            ->whereDate('fecha_salida', $hoy)
            ->with('apartamento')
            ->get();
        
        // 2. Limpiezas de fondo programadas para hoy
        $limpiezasFondoHoy = \App\Models\LimpiezaFondo::whereDate('fecha', $hoy)
            ->with('apartamento')
            ->get();
        
        // 3. Apartamentos que ya tienen limpieza asignada hoy
        $apartamentosLimpiezaHoy = \App\Models\ApartamentoLimpieza::whereDate('fecha_comienzo', $hoy)
            ->pluck('apartamento_id')
            ->toArray();
        
        $apartamentos = collect();
        
        // Agregar apartamentos de reservas que salen hoy
        foreach ($reservasSalidaHoy as $reserva) {
            if (!in_array($reserva->apartamento_id, $apartamentosLimpiezaHoy)) {
                $apartamentos->push($reserva->apartamento);
            }
        }
        
        // Agregar apartamentos de limpieza de fondo
        foreach ($limpiezasFondoHoy as $limpieza) {
            if (!in_array($limpieza->apartamento_id, $apartamentosLimpiezaHoy) && 
                !$apartamentos->contains('id', $limpieza->apartamento_id)) {
                $apartamentos->push($limpieza->apartamento);
            }
        }
        
        return $apartamentos;
    }
    
    /**
     * Calcular prioridad de una limpieza
     */
    private function calcularPrioridadLimpieza($limpieza, $tipoTarea)
    {
        $prioridad = $tipoTarea->prioridad_base;
        
        // Si la limpieza tiene días sin limpiar, aumentar prioridad
        if ($limpieza->fecha_comienzo && $tipoTarea->necesitaActualizacionPrioridad()) {
            $diasSinLimpiar = Carbon::parse($limpieza->fecha_comienzo)->diffInDays(now());
            $prioridad = $tipoTarea->calcularPrioridad($diasSinLimpiar);
        }
        
        return $prioridad;
    }
    
    /**
     * Calcular prioridad para un apartamento
     */
    private function calcularPrioridadApartamento($apartamento, $tipoTarea)
    {
        $prioridad = $tipoTarea->prioridad_base;
        
        // Prioridad alta para apartamentos que salen hoy
        $reservaSalidaHoy = \App\Models\Reserva::where('apartamento_id', $apartamento->id)
            ->whereNull('fecha_limpieza')
            ->where('estado_id', '!=', 4)
            ->whereDate('fecha_salida', now()->toDateString())
            ->first();
            
        if ($reservaSalidaHoy) {
            $prioridad = 10; // Máxima prioridad para salidas de hoy
        }
        
        return $prioridad;
    }
    
    /**
     * Distribuir tareas entre empleadas disponibles
     */
    private function distribuirTareasEntreEmpleadas($empleadas, $tareas, $fecha)
    {
        $turnosGenerados = 0;
        $turnos = [];
        
        // Crear turnos para cada empleada
        foreach ($empleadas as $empleada) {
            $turno = TurnoTrabajo::create([
                'fecha' => $fecha,
                'user_id' => $empleada->user_id,
                'hora_inicio' => $empleada->hora_inicio_atencion,
                'hora_fin' => $empleada->hora_fin_atencion,
                'estado' => 'programado',
                'fecha_creacion' => now()
            ]);
            
            // Si solo hay una empleada disponible, asignar 8 horas completas
            $tiempoDisponible = $empleadas->count() === 1 ? 8 * 60 : $empleada->horas_contratadas_dia * 60;
            
            $turnos[] = [
                'turno' => $turno,
                'empleada' => $empleada,
                'tiempo_disponible' => $tiempoDisponible, // 8 horas si es la única, sino su jornada normal
                'tiempo_asignado' => 0,
                'tareas_asignadas' => 0
            ];
            
            $turnosGenerados++;
        }
        
        // Separar tareas de apartamentos (prioridad máxima) de tareas generales
        $tareasApartamentos = $tareas->filter(function($tarea) {
            return $tarea['apartamento_id'] !== null;
        });
        
        $tareasGenerales = $tareas->filter(function($tarea) {
            return $tarea['apartamento_id'] === null;
        });
        
        // Agrupar tareas de apartamentos por edificio para optimizar asignación
        $tareasPorEdificio = $this->agruparTareasPorEdificio($tareasApartamentos);
        
        $ordenEjecucion = 1;
        
        // 1. PRIMERO: Asignar tareas de apartamentos agrupadas por edificio
        foreach ($tareasPorEdificio as $edificioId => $tareasEdificio) {
            $this->info("🏢 Procesando edificio {$edificioId} con " . count($tareasEdificio) . " apartamentos");
            
            // Intentar asignar todo el edificio a la misma empleada
            $edificioAsignado = $this->asignarEdificioCompleto($turnos, $tareasEdificio, $ordenEjecucion);
            
            if ($edificioAsignado['asignado']) {
                $this->info("✅ Edificio {$edificioId} asignado completamente a {$edificioAsignado['empleada']}");
                $ordenEjecucion = $edificioAsignado['ordenEjecucion'];
            } else {
                // Si no se puede asignar completo, asignar apartamentos individualmente
                $this->info("⚠️  Edificio {$edificioId} no se puede asignar completo, distribuyendo apartamentos");
                foreach ($tareasEdificio as $tarea) {
                    $asignado = $this->asignarTareaIndividual($turnos, $tarea, $ordenEjecucion);
                    if ($asignado) {
                        $ordenEjecucion++;
                    }
                }
            }
        }
        
        // 2. SEGUNDO: Asignar tareas generales respetando la jornada diaria
        foreach ($tareasGenerales as $tarea) {
            // Encontrar el turno con menos tiempo asignado que tenga capacidad
            $turnoSeleccionado = null;
            $turnoIndex = -1;
            
            foreach ($turnos as $index => $turno) {
                if ($turno['tiempo_asignado'] + $tarea['tiempo_estimado'] <= $turno['tiempo_disponible']) {
                    if (!$turnoSeleccionado || $turno['tiempo_asignado'] < $turnoSeleccionado['tiempo_asignado']) {
                        $turnoSeleccionado = $turno;
                        $turnoIndex = $index;
                    }
                }
            }
            
            // Si no hay turno con capacidad, NO asignar la tarea general
            if (!$turnoSeleccionado) {
                $this->warn("⚠️  Tarea general '{$tarea['tipo_tarea']->nombre}' no asignada - jornada completa");
                continue;
            }
            
            // Verificar que no se sobrepase la jornada después de asignar
            if ($turnoSeleccionado['tiempo_asignado'] + $tarea['tiempo_estimado'] > $turnoSeleccionado['tiempo_disponible']) {
                $this->warn("⚠️  Tarea general '{$tarea['tipo_tarea']->nombre}' no asignada - sobrepasa jornada");
                continue;
            }
            
            // Crear la tarea asignada
            TareaAsignada::create([
                'turno_id' => $turnoSeleccionado['turno']->id,
                'tipo_tarea_id' => $tarea['tipo_tarea']->id,
                'apartamento_id' => $tarea['apartamento_id'],
                'zona_comun_id' => $tarea['zona_comun_id'] ?? null,
                'prioridad_calculada' => $tarea['prioridad'],
                'orden_ejecucion' => $ordenEjecucion,
                'estado' => 'pendiente',
                'dias_sin_limpiar' => 0
            ]);
            
            // Actualizar estadísticas del turno en el array original
            $turnos[$turnoIndex]['tiempo_asignado'] += $tarea['tiempo_estimado'];
            $turnos[$turnoIndex]['tareas_asignadas']++;
            
            $ordenEjecucion++;
        }
        
        return $turnosGenerados;
    }
    
    /**
     * Mostrar resumen de turnos generados
     */
    private function mostrarResumen($fecha)
    {
        $turnos = TurnoTrabajo::porFecha($fecha)
            ->with(['user', 'tareasAsignadas.tipoTarea'])
            ->get();
            
        $this->info("\n📊 RESUMEN DE TURNOS GENERADOS:");
        $this->info("Fecha: {$fecha->format('Y-m-d')}");
        $this->info("Total turnos: {$turnos->count()}");
        
        $totalTareas = $turnos->sum(function($turno) {
            return $turno->tareasAsignadas->count();
        });
        
        $this->info("Total tareas: {$totalTareas}");
        
        $this->info("\n👥 DETALLE POR EMPLEADA:");
        foreach ($turnos as $turno) {
            $tareasCount = $turno->tareasAsignadas->count();
            $tiempoEstimado = $turno->tareasAsignadas->sum(function($tarea) {
                return $tarea->tipoTarea->tiempo_estimado_minutos;
            });
            $tiempoFormateado = $this->formatearTiempo($tiempoEstimado);
            
            // Obtener jornada contratada
            $empleadaHorario = EmpleadaHorario::where('user_id', $turno->user_id)->first();
            $jornadaContratada = $empleadaHorario ? $empleadaHorario->horas_contratadas_dia : 0;
            
            // Si solo hay una empleada, usar 8 horas como jornada efectiva
            $jornadaEfectiva = $turnos->count() === 1 ? 8 : $jornadaContratada;
            $jornadaEfectivaMinutos = $jornadaEfectiva * 60;
            
            // Verificar si se sobrepasa la jornada efectiva
            $sobrepasaJornada = $tiempoEstimado > $jornadaEfectivaMinutos;
            $estadoJornada = $sobrepasaJornada ? "⚠️  SOBREPASA JORNADA" : "✅ DENTRO DE JORNADA";
            
            $this->info("- {$turno->user->name}: {$tareasCount} tareas ({$tiempoFormateado}) - Jornada: {$jornadaEfectiva}h - {$estadoJornada}");
            
            // Mostrar detalle de tareas
            $tareasApartamentos = $turno->tareasAsignadas->filter(function($tarea) {
                return $tarea->apartamento_id !== null;
            });
            $tareasGenerales = $turno->tareasAsignadas->filter(function($tarea) {
                return $tarea->apartamento_id === null;
            });
            
            $this->info("  📍 Apartamentos: {$tareasApartamentos->count()} tareas (prioridad alta)");
            $this->info("  🏢 Generales: {$tareasGenerales->count()} tareas (respetando jornada)");
            
            if ($sobrepasaJornada) {
                $this->warn("  ⚠️  ADVERTENCIA: Se sobrepasa la jornada contratada");
            }
        }
    }
    
    /**
     * Agrupar tareas de apartamentos por edificio
     */
    private function agruparTareasPorEdificio($tareasApartamentos)
    {
        $tareasPorEdificio = [];
        
        foreach ($tareasApartamentos as $tarea) {
            $apartamento = \App\Models\Apartamento::find($tarea['apartamento_id']);
            $edificioId = $apartamento ? $apartamento->edificio_id : 'sin_edificio';
            
            if (!isset($tareasPorEdificio[$edificioId])) {
                $tareasPorEdificio[$edificioId] = [];
            }
            
            $tareasPorEdificio[$edificioId][] = $tarea;
        }
        
        // Ordenar por número de apartamentos (edificios con más apartamentos primero)
        uasort($tareasPorEdificio, function($a, $b) {
            return count($b) - count($a);
        });
        
        return $tareasPorEdificio;
    }
    
    /**
     * Intentar asignar un edificio completo a una empleada
     */
    private function asignarEdificioCompleto(&$turnos, $tareasEdificio, $ordenEjecucion)
    {
        $tiempoTotalEdificio = collect($tareasEdificio)->sum('tiempo_estimado');
        
        // Buscar empleada que pueda asumir todo el edificio, priorizando balance de carga
        $mejorOpcion = null;
        $mejorIndex = -1;
        $mejorBalance = PHP_INT_MAX;
        
        foreach ($turnos as $index => $turno) {
            if ($turno['tiempo_asignado'] + $tiempoTotalEdificio <= $turno['tiempo_disponible']) {
                // Calcular balance de carga (tiempo asignado actual)
                $balanceActual = $turno['tiempo_asignado'];
                
                // Preferir empleada con menos carga actual
                if ($balanceActual < $mejorBalance) {
                    $mejorOpcion = $turno;
                    $mejorIndex = $index;
                    $mejorBalance = $balanceActual;
                }
            }
        }
        
        // Si encontramos una empleada que pueda asumir el edificio completo
        if ($mejorOpcion) {
            // Asignar todas las tareas del edificio a esta empleada
            foreach ($tareasEdificio as $tarea) {
                TareaAsignada::create([
                    'turno_id' => $mejorOpcion['turno']->id,
                    'tipo_tarea_id' => $tarea['tipo_tarea']->id,
                    'apartamento_id' => $tarea['apartamento_id'],
                    'zona_comun_id' => $tarea['zona_comun_id'] ?? null,
                    'prioridad_calculada' => $tarea['prioridad'],
                    'orden_ejecucion' => $ordenEjecucion,
                    'estado' => 'pendiente',
                    'dias_sin_limpiar' => 0
                ]);
                
                $turnos[$mejorIndex]['tiempo_asignado'] += $tarea['tiempo_estimado'];
                $turnos[$mejorIndex]['tareas_asignadas']++;
                $ordenEjecucion++;
            }
            
            return [
                'asignado' => true,
                'empleada' => $mejorOpcion['empleada']->user->name,
                'ordenEjecucion' => $ordenEjecucion
            ];
        }
        
        return ['asignado' => false];
    }
    
    /**
     * Asignar una tarea individual a la mejor empleada disponible
     */
    private function asignarTareaIndividual(&$turnos, $tarea, $ordenEjecucion)
    {
        $turnoSeleccionado = null;
        $turnoIndex = -1;
        
        foreach ($turnos as $index => $turno) {
            if ($turno['tiempo_asignado'] + $tarea['tiempo_estimado'] <= $turno['tiempo_disponible']) {
                if (!$turnoSeleccionado || $turno['tiempo_asignado'] < $turnoSeleccionado['tiempo_asignado']) {
                    $turnoSeleccionado = $turno;
                    $turnoIndex = $index;
                }
            }
        }
        
        if (!$turnoSeleccionado) {
            $this->warn("⚠️  Apartamento '{$tarea['apartamento_id']}' no asignado - jornada completa");
            return false;
        }
        
        // Crear la tarea asignada
        TareaAsignada::create([
            'turno_id' => $turnoSeleccionado['turno']->id,
            'tipo_tarea_id' => $tarea['tipo_tarea']->id,
            'apartamento_id' => $tarea['apartamento_id'],
            'zona_comun_id' => $tarea['zona_comun_id'] ?? null,
            'prioridad_calculada' => $tarea['prioridad'],
            'orden_ejecucion' => $ordenEjecucion,
            'estado' => 'pendiente',
            'dias_sin_limpiar' => 0
        ]);
        
        $turnos[$turnoIndex]['tiempo_asignado'] += $tarea['tiempo_estimado'];
        $turnos[$turnoIndex]['tareas_asignadas']++;
        
        return true;
    }
    
    /**
     * Generar asignación optimizada usando OpenAI
     */
    private function generarAsignacionConIA($empleadas, $tareas, $fecha)
    {
        try {
            // Preparar datos para OpenAI
            $datosIA = $this->prepararDatosParaIA($empleadas, $tareas);
            
            // Crear prompt para OpenAI
            $prompt = $this->crearPromptParaIA($datosIA);
            
            $this->info("🤖 Enviando datos a OpenAI para optimización...");
            $this->info("📊 Datos enviados:");
            $this->info(json_encode($datosIA, JSON_PRETTY_PRINT));
            
            // Llamar a OpenAI
            $respuestaIA = $this->llamarOpenAI($prompt);
            
            $this->info("🤖 Respuesta de OpenAI:");
            $this->info($respuestaIA);
            
            // Parsear respuesta JSON
            $asignacionIA = json_decode($respuestaIA, true);
            
            if (!$asignacionIA) {
                throw new \Exception("Error al parsear respuesta de OpenAI");
            }
            
            return $asignacionIA;
            
        } catch (\Exception $e) {
            $this->error("❌ Error en generación con IA: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Preparar datos estructurados para OpenAI
     */
    private function prepararDatosParaIA($empleadas, $tareas)
    {
        // Separar tareas por categorías
        $tareasApartamentos = $tareas->filter(function($tarea) {
            return $tarea['apartamento_id'] !== null;
        });
        
        $tareasZonasComunes = $tareas->filter(function($tarea) {
            return isset($tarea['zona_comun_id']) && $tarea['zona_comun_id'] !== null;
        });
        
        // Las tareas de mantenimiento ahora están integradas en limpieza de zonas comunes por edificio
        $tareasMantenimiento = $tareas->filter(function($tarea) {
            return $tarea['apartamento_id'] === null && (!isset($tarea['zona_comun_id']) || $tarea['zona_comun_id'] === null) && isset($tarea['edificio_id']);
        });
        
        $tareasGenerales = $tareas->filter(function($tarea) {
            return $tarea['apartamento_id'] === null && (!isset($tarea['zona_comun_id']) || $tarea['zona_comun_id'] === null) && !isset($tarea['edificio_id']);
        });
        
        // Agrupar apartamentos por edificio
        $apartamentosPorEdificio = [];
        foreach ($tareasApartamentos as $tarea) {
            $apartamento = \App\Models\Apartamento::find($tarea['apartamento_id']);
            $edificioId = $apartamento ? $apartamento->edificio_id : 'sin_edificio';
            
            if (!isset($apartamentosPorEdificio[$edificioId])) {
                $apartamentosPorEdificio[$edificioId] = [
                    'edificio_id' => $edificioId,
                    'nombre' => $apartamento ? $apartamento->edificio->nombre ?? "Edificio {$edificioId}" : 'Sin edificio',
                    'apartamentos' => [],
                    'tiempo_total' => 0,
                    'prioridad_maxima' => 0
                ];
            }
            
            $apartamentosPorEdificio[$edificioId]['apartamentos'][] = [
                'apartamento_id' => $tarea['apartamento_id'],
                'apartamento_titulo' => $apartamento ? $apartamento->titulo : 'Sin título',
                'tiempo_estimado' => $tarea['tiempo_estimado'],
                'prioridad' => $tarea['prioridad'],
                'motivo_prioridad' => $this->obtenerMotivoPrioridadApartamento($apartamento)
            ];
            
            $apartamentosPorEdificio[$edificioId]['tiempo_total'] += $tarea['tiempo_estimado'];
            $apartamentosPorEdificio[$edificioId]['prioridad_maxima'] = max(
                $apartamentosPorEdificio[$edificioId]['prioridad_maxima'], 
                $tarea['prioridad']
            );
        }
        
        // Procesar tareas generales con priorización dinámica
        $tareasGeneralesProcesadas = [];
        foreach ($tareasGenerales as $tarea) {
            $tareaInfo = [
                'id' => $tarea['tipo_tarea']->id,
                'tipo' => $tarea['tipo_tarea']->nombre,
                'categoria' => $tarea['tipo_tarea']->categoria,
                'tiempo_estimado' => $tarea['tiempo_estimado'],
                'prioridad_base' => $tarea['tipo_tarea']->prioridad_base,
                'prioridad_calculada' => $tarea['prioridad'],
                'dias_max_sin_limpiar' => $tarea['tipo_tarea']->dias_max_sin_limpiar,
                'incremento_prioridad_por_dia' => $tarea['tipo_tarea']->incremento_prioridad_por_dia,
                'prioridad_maxima' => $tarea['tipo_tarea']->prioridad_maxima,
                'ultima_ejecucion' => $this->obtenerUltimaEjecucionTarea($tarea['tipo_tarea']->id),
                'dias_desde_ultima_ejecucion' => $this->calcularDiasDesdeUltimaEjecucion($tarea['tipo_tarea']->id),
                'necesita_urgente' => $this->necesitaUrgente($tarea['tipo_tarea']),
                'motivo_prioridad' => $this->obtenerMotivoPrioridadTarea($tarea['tipo_tarea'])
            ];
            
            $tareasGeneralesProcesadas[] = $tareaInfo;
        }
        
        // Procesar zonas comunes
        $zonasComunesProcesadas = [];
        foreach ($tareasZonasComunes as $tarea) {
            $zonaComun = \App\Models\ZonaComun::find($tarea['zona_comun_id']);
            $zonasComunesProcesadas[] = [
                'id' => $tarea['tipo_tarea']->id,
                'tipo' => $tarea['tipo_tarea']->nombre,
                'zona_comun_id' => $tarea['zona_comun_id'] ?? null,
                'zona_nombre' => $zonaComun ? $zonaComun->nombre : 'Sin nombre',
                'tiempo_estimado' => $tarea['tiempo_estimado'],
                'prioridad' => $tarea['prioridad'],
                'motivo_prioridad' => 'Limpieza de zona común programada'
            ];
        }
        
        // Procesar tareas de limpieza común por edificio (SIEMPRE OBLIGATORIAS)
        $limpiezaComunProcesadas = [];
        foreach ($tareasMantenimiento as $tarea) {
            $limpiezaComunProcesadas[] = [
                'id' => $tarea['tipo_tarea']->id,
                'tipo' => $tarea['tipo_tarea']->nombre,
                'edificio_id' => $tarea['edificio_id'],
                'tiempo_estimado' => $tarea['tiempo_estimado'],
                'prioridad' => $tarea['prioridad'],
                'motivo_prioridad' => $tarea['motivo_prioridad'] ?? 'Limpieza común edificio - SIEMPRE OBLIGATORIA'
            ];
        }
        
        return [
            'empleadas' => $empleadas->map(function($empleada) use ($empleadas) {
                // Si solo hay una empleada disponible, asignar 8 horas completas
                $tiempoDisponible = $empleadas->count() === 1 ? 8 * 60 : $empleada->horas_contratadas_dia * 60;
                
                return [
                    'id' => $empleada->id,
                    'nombre' => $empleada->user->name,
                    'jornada_horas' => $empleada->horas_contratadas_dia,
                    'jornada_minutos' => $empleada->horas_contratadas_dia * 60,
                    'tiempo_disponible' => $tiempoDisponible, // 8 horas si es la única, sino su jornada normal
                    'dias_trabajo_semana' => $empleada->numero_dias_trabajo
                ];
            })->toArray(),
            'edificios' => array_values($apartamentosPorEdificio),
            'zonas_comunes' => $zonasComunesProcesadas,
            'limpieza_comun_edificios' => $limpiezaComunProcesadas,
            'tareas_generales' => $tareasGeneralesProcesadas,
            'reglas_priorizacion' => [
                'prioridad_maxima' => 'Apartamentos y zonas comunes (prioridad 10)',
                'prioridad_alta' => 'Limpieza común de edificios (SIEMPRE OBLIGATORIA) y tareas urgentes',
                'prioridad_media' => 'Tareas con prioridad base normal',
                'prioridad_baja' => 'Tareas recientemente ejecutadas (< 7 días)',
                'relleno_jornada' => 'Usar tareas de menor prioridad para completar jornadas'
            ],
            'constraints' => [
                'respetar_jornada_estricto' => true,
                'priorizar_edificios_completos' => true,
                'balancear_carga_trabajo' => true,
                'max_tiempo_por_empleada' => 'No sobrepasar jornada contratada',
                'regla_especial_una_empleada' => 'Si solo hay una empleada disponible, asignar 8 horas completas'
            ]
        ];
    }
    
    /**
     * Obtener motivo de prioridad para apartamento
     */
    private function obtenerMotivoPrioridadApartamento($apartamento)
    {
        if (!$apartamento) return 'Apartamento no encontrado';
        
        $reservaSalidaHoy = \App\Models\Reserva::where('apartamento_id', $apartamento->id)
            ->whereNull('fecha_limpieza')
            ->where('estado_id', '!=', 4)
            ->whereDate('fecha_salida', now()->toDateString())
            ->first();
            
        if ($reservaSalidaHoy) {
            return 'Reserva sale hoy - MÁXIMA PRIORIDAD';
        }
        
        $limpiezaFondo = \App\Models\LimpiezaFondo::where('apartamento_id', $apartamento->id)
            ->whereDate('fecha', now()->toDateString())
            ->first();
            
        if ($limpiezaFondo) {
            return 'Limpieza de fondo programada';
        }
        
        return 'Limpieza pendiente';
    }
    
    /**
     * Obtener última ejecución de una tarea
     */
    private function obtenerUltimaEjecucionTarea($tipoTareaId)
    {
        $ultimaTarea = \App\Models\TareaAsignada::where('tipo_tarea_id', $tipoTareaId)
            ->where('estado', 'completada')
            ->whereNotNull('fecha_ultima_limpieza')
            ->orderBy('fecha_ultima_limpieza', 'desc')
            ->first();
            
        return $ultimaTarea ? $ultimaTarea->fecha_ultima_limpieza->format('Y-m-d H:i') : 'Nunca ejecutada';
    }
    
    /**
     * Calcular días desde última ejecución
     */
    private function calcularDiasDesdeUltimaEjecucion($tipoTareaId)
    {
        $ultimaTarea = \App\Models\TareaAsignada::where('tipo_tarea_id', $tipoTareaId)
            ->where('estado', 'completada')
            ->whereNotNull('fecha_ultima_limpieza')
            ->orderBy('fecha_ultima_limpieza', 'desc')
            ->first();
            
        if (!$ultimaTarea) {
            return 999; // Nunca ejecutada = muy urgente
        }
        
        return Carbon::parse($ultimaTarea->fecha_ultima_limpieza)->diffInDays(now());
    }
    
    /**
     * Verificar si una tarea necesita ser urgente
     */
    private function necesitaUrgente($tipoTarea)
    {
        $diasSinLimpiar = $this->calcularDiasDesdeUltimaEjecucion($tipoTarea->id);
        
        // Si es limpieza de oficina y se hizo hace menos de 7 días, bajar prioridad
        if ($tipoTarea->categoria === 'limpieza_oficina' && $diasSinLimpiar < 7) {
            return false; // No urgente
        }
        
        // Si supera los días máximos sin limpiar, es urgente
        if ($diasSinLimpiar >= $tipoTarea->dias_max_sin_limpiar) {
            return true; // Urgente
        }
        
        return false;
    }
    
    /**
     * Obtener motivo de prioridad para tarea general
     */
    private function obtenerMotivoPrioridadTarea($tipoTarea)
    {
        $diasSinLimpiar = $this->calcularDiasDesdeUltimaEjecucion($tipoTarea->id);
        
        if ($diasSinLimpiar === 999) {
            return 'Nunca ejecutada - URGENTE';
        }
        
        if ($tipoTarea->categoria === 'limpieza_oficina' && $diasSinLimpiar < 7) {
            return "Ejecutada hace {$diasSinLimpiar} días - Prioridad baja";
        }
        
        if ($diasSinLimpiar >= $tipoTarea->dias_max_sin_limpiar) {
            return "Sin limpiar {$diasSinLimpiar} días - URGENTE";
        }
        
        if ($diasSinLimpiar > 0) {
            return "Ejecutada hace {$diasSinLimpiar} días";
        }
        
        return 'Prioridad base';
    }
    
    /**
     * Crear prompt optimizado para OpenAI
     */
    private function crearPromptParaIA($datos)
    {
        return "Eres un experto en optimización de recursos humanos para empresas de limpieza.

OBJETIVO: Asignar tareas de limpieza de apartamentos, zonas comunes y tareas generales a empleadas de forma óptima.

REGLAS DE PRIORIZACIÓN (EN ORDEN DE IMPORTANCIA):
1. MÁXIMA PRIORIDAD: Apartamentos y zonas comunes (prioridad 10) - SIEMPRE se asignan primero
2. ALTA PRIORIDAD: Mantenimiento de edificios (una tarea por cada edificio con apartamentos)
3. MEDIA PRIORIDAD: Tareas urgentes por tiempo sin ejecutar (superan días máximos sin limpiar)
4. BAJA PRIORIDAD: Tareas recientemente ejecutadas (< 7 días, especialmente limpieza de oficina)
5. RELLENO: Usar tareas de menor prioridad para completar jornadas

REGLAS DE ASIGNACIÓN:
1. PRIORIZAR: Asignar edificios completos a la misma empleada
2. RESPETAR: Jornada laboral estricta (NO sobrepasar horas contratadas)
3. ESPECIAL: Si solo hay UNA empleada disponible, asignar 8 horas completas (no su jornada normal)
4. BALANCEAR: Distribuir carga de trabajo equitativamente entre múltiples empleadas
5. EFICIENCIA: Agrupar tareas por ubicación cuando sea posible

LÓGICA ESPECIAL PARA LIMPIEZA DE OFICINA:
- Si se ejecutó hace menos de 7 días: BAJAR prioridad
- Si no se ha ejecutado o supera días máximos: SUBIR a urgente

DATOS DE ENTRADA:
" . json_encode($datos, JSON_PRETTY_PRINT) . "

INSTRUCCIONES DETALLADAS:
1. PRIMERO: Asignar TODOS los apartamentos (prioridad máxima)
2. SEGUNDO: Asignar limpieza de zonas comunes por edificio (incluye mantenimiento)
3. TERCERO: Asignar tareas urgentes por tiempo sin ejecutar
4. CUARTO: Asignar tareas de prioridad media
5. QUINTO: Usar tareas de baja prioridad para rellenar jornadas
6. RESPETAR: Jornada estricta - nunca sobrepasar tiempo disponible
7. OPTIMIZAR: Agrupar por edificio cuando sea posible

FORMATO DE RESPUESTA (JSON):
{
  \"asignaciones\": [
    {
      \"empleada_id\": 1,
      \"empleada_nombre\": \"Test Limpieadoras\",
      \"edificios_asignados\": [1],
      \"tareas_apartamentos\": [
        {
          \"apartamento_id\": 101,
          \"edificio_id\": 1,
          \"tiempo_estimado\": 60,
          \"prioridad\": 10,
          \"motivo_prioridad\": \"Reserva sale hoy - MÁXIMA PRIORIDAD\",
          \"orden_ejecucion\": 1
        }
      ],
      \"tareas_zonas_comunes\": [
        {
          \"zona_comun_id\": 1,
          \"tiempo_estimado\": 45,
          \"prioridad\": 10,
          \"orden_ejecucion\": 2
        }
      ],
      \"tareas_zonas_comunes\": [
        {
          \"tarea_id\": 3,
          \"tipo\": \"Limpieza Zona Común\",
          \"edificio_id\": 1,
          \"tiempo_estimado\": 45,
          \"prioridad\": 10,
          \"orden_ejecucion\": 2
        }
      ],
      \"tareas_generales\": [
        {
          \"tarea_id\": 1,
          \"tipo\": \"Limpieza de Oficina\",
          \"tiempo_estimado\": 60,
          \"prioridad_calculada\": 3,
          \"motivo_prioridad\": \"Ejecutada hace 3 días - Prioridad baja\",
          \"orden_ejecucion\": 5
        }
      ],
      \"tiempo_total_asignado\": 420,
      \"tiempo_disponible\": 480,
      \"eficiencia\": \"Edificio completo asignado\"
    }
  ],
  \"resumen\": {
    \"total_empleadas_utilizadas\": 2,
    \"edificios_completos_asignados\": 2,
    \"tareas_prioridad_maxima\": 7,
    \"tareas_urgentes\": 1,
    \"tareas_relleno\": 2,
    \"tiempo_total_optimizado\": 660,
    \"eficiencia_global\": \"Alta - Edificios agrupados\"
  }
}

Responde SOLO con el JSON, sin texto adicional.";
    }
    
    /**
     * Generar respuesta simulada de IA para pruebas
     */
    private function generarRespuestaSimuladaIA($empleadas, $tareas)
    {
        $this->info("🧪 Generando respuesta simulada de IA...");
        
        // Separar tareas
        $tareasApartamentos = $tareas->filter(function($tarea) {
            return $tarea['apartamento_id'] !== null;
        });
        
        $tareasMantenimiento = $tareas->filter(function($tarea) {
            return $tarea['apartamento_id'] === null && isset($tarea['edificio_id']);
        });
        
        $tareasGenerales = $tareas->filter(function($tarea) {
            return $tarea['apartamento_id'] === null && !isset($tarea['edificio_id']);
        });
        
        // Agrupar apartamentos por edificio
        $apartamentosPorEdificio = [];
        foreach ($tareasApartamentos as $tarea) {
            $apartamento = \App\Models\Apartamento::find($tarea['apartamento_id']);
            $edificioId = $apartamento ? $apartamento->edificio_id : 'sin_edificio';
            
            if (!isset($apartamentosPorEdificio[$edificioId])) {
                $apartamentosPorEdificio[$edificioId] = [];
            }
            
            $apartamentosPorEdificio[$edificioId][] = $tarea;
        }
        
        $asignaciones = [];
        $empleadasArray = $empleadas->toArray();
        
        // Simular asignación optimizada
        $edificioIndex = 0;
        foreach ($empleadasArray as $index => $empleada) {
            $asignacion = [
                'empleada_id' => $empleada['id'],
                'empleada_nombre' => $empleada['user']['name'],
                'edificios_asignados' => [],
                'tareas_apartamentos' => [],
                'tareas_zonas_comunes' => [],
                'tareas_generales' => [],
                'tiempo_total_asignado' => 0,
                'tiempo_disponible' => $empleadas->count() === 1 ? 8 * 60 : $empleada['horas_contratadas_dia'] * 60, // 8 horas si es la única
                'eficiencia' => 'Asignación optimizada por IA'
            ];
            
            // Asignar edificio si hay disponibles
            if ($edificioIndex < count($apartamentosPorEdificio)) {
                $edificios = array_keys($apartamentosPorEdificio);
                $edificioId = $edificios[$edificioIndex];
                $tareasEdificio = $apartamentosPorEdificio[$edificioId];
                
                $tiempoEdificio = collect($tareasEdificio)->sum('tiempo_estimado');
                
                // Solo asignar si cabe en la jornada
                if ($tiempoEdificio <= $asignacion['tiempo_disponible']) {
                    $asignacion['edificios_asignados'][] = $edificioId;
                    
                    $ordenEjecucion = 1;
                    foreach ($tareasEdificio as $tarea) {
                        $asignacion['tareas_apartamentos'][] = [
                            'apartamento_id' => $tarea['apartamento_id'],
                            'edificio_id' => $edificioId,
                            'tiempo_estimado' => $tarea['tiempo_estimado'],
                            'prioridad' => $tarea['prioridad'],
                            'orden_ejecucion' => $ordenEjecucion
                        ];
                        
                        $asignacion['tiempo_total_asignado'] += $tarea['tiempo_estimado'];
                        $ordenEjecucion++;
                    }
                    
                    $edificioIndex++;
                }
            }
            
            // Asignar tarea de limpieza común del edificio asignado (SIEMPRE OBLIGATORIA)
            if ($edificioIndex > 0 && $edificioIndex <= count($apartamentosPorEdificio)) {
                $edificios = array_keys($apartamentosPorEdificio);
                $edificioId = $edificios[$edificioIndex - 1];
                
                // Buscar tarea de limpieza común para este edificio
                $tareaLimpiezaComun = $tareasMantenimiento->first(function($tarea) use ($edificioId) {
                    return $tarea['edificio_id'] == $edificioId;
                });
                
                if ($tareaLimpiezaComun && $tareaLimpiezaComun['tiempo_estimado'] <= $asignacion['tiempo_disponible'] - $asignacion['tiempo_total_asignado']) {
                    $asignacion['tareas_zonas_comunes'][] = [
                        'tarea_id' => $tareaLimpiezaComun['tipo_tarea']->id,
                        'tipo' => $tareaLimpiezaComun['tipo_tarea']->nombre,
                        'edificio_id' => $edificioId,
                        'tiempo_estimado' => $tareaLimpiezaComun['tiempo_estimado'],
                        'prioridad' => $tareaLimpiezaComun['prioridad'],
                        'orden_ejecucion' => count($asignacion['tareas_apartamentos']) + 1
                    ];
                    
                    $asignacion['tiempo_total_asignado'] += $tareaLimpiezaComun['tiempo_estimado'];
                }
            }
            
            // Asignar tareas generales para completar jornada
            $tiempoRestante = $asignacion['tiempo_disponible'] - $asignacion['tiempo_total_asignado'];
            $ordenEjecucion = count($asignacion['tareas_apartamentos']) + 1;
            
            foreach ($tareasGenerales as $tarea) {
                if ($tarea['tiempo_estimado'] <= $tiempoRestante) {
                    $asignacion['tareas_generales'][] = [
                        'tarea_id' => $tarea['tipo_tarea']->id,
                        'tipo' => $tarea['tipo_tarea']->nombre,
                        'tiempo_estimado' => $tarea['tiempo_estimado'],
                        'prioridad_calculada' => $tarea['prioridad'],
                        'orden_ejecucion' => $ordenEjecucion
                    ];
                    
                    $asignacion['tiempo_total_asignado'] += $tarea['tiempo_estimado'];
                    $tiempoRestante -= $tarea['tiempo_estimado'];
                    $ordenEjecucion++;
                }
            }
            
            $asignaciones[] = $asignacion;
        }
        
        return [
            'asignaciones' => $asignaciones,
            'resumen' => [
                'total_empleadas_utilizadas' => count($asignaciones),
                'edificios_completos_asignados' => $edificioIndex,
                'tareas_prioridad_maxima' => $tareasApartamentos->count(),
                'tareas_urgentes' => $tareasGenerales->where('prioridad', '>', 5)->count(),
                'tareas_relleno' => $tareasGenerales->where('prioridad', '<=', 5)->count(),
                'tiempo_total_optimizado' => collect($asignaciones)->sum('tiempo_total_asignado'),
                'eficiencia_global' => 'Alta - Distribución optimizada por IA'
            ]
        ];
    }
    
    /**
     * Crear turnos basándose en la respuesta de IA
     */
    private function crearTurnosDesdeIA($asignacionIA, $empleadas, $fecha)
    {
        $turnosGenerados = 0;
        
        try {
            $this->info("🤖 Procesando asignación de IA...");
            
            // Crear mapa de empleadas por ID
            $empleadasMap = $empleadas->keyBy('id');
            
            foreach ($asignacionIA['asignaciones'] as $asignacion) {
                $empleadaId = $asignacion['empleada_id'];
                $empleada = $empleadasMap->get($empleadaId);
                
                if (!$empleada) {
                    $this->warn("⚠️  Empleada ID {$empleadaId} no encontrada");
                    continue;
                }
                
                $this->info("👩‍💼 Creando turno para {$asignacion['empleada_nombre']}");
                
                // Crear turno de trabajo
                $turno = TurnoTrabajo::create([
                    'fecha' => $fecha,
                    'user_id' => $empleada->user_id,
                    'hora_inicio' => $empleada->hora_inicio_atencion,
                    'hora_fin' => $empleada->hora_fin_atencion,
                    'estado' => 'programado',
                    'fecha_creacion' => now()
                ]);
                
                $ordenEjecucion = 1;
                $tiempoTotalAsignado = 0;
                
                // Asignar tareas de apartamentos
                if (isset($asignacion['tareas_apartamentos'])) {
                    foreach ($asignacion['tareas_apartamentos'] as $tareaApto) {
                        $tipoTarea = TipoTarea::limpiezaApartamentos()->first();
                        
                        if ($tipoTarea) {
                            TareaAsignada::create([
                                'turno_id' => $turno->id,
                                'tipo_tarea_id' => $tipoTarea->id,
                                'apartamento_id' => $tareaApto['apartamento_id'],
                                'zona_comun_id' => null,
                                'prioridad_calculada' => $tareaApto['prioridad'] ?? 10,
                                'orden_ejecucion' => $tareaApto['orden_ejecucion'] ?? $ordenEjecucion,
                                'estado' => 'pendiente',
                                'dias_sin_limpiar' => 0
                            ]);
                            
                            $tiempoTotalAsignado += $tareaApto['tiempo_estimado'];
                            $ordenEjecucion++;
                        }
                    }
                }
                
                // Asignar tareas de zonas comunes
                if (isset($asignacion['tareas_zonas_comunes'])) {
                    foreach ($asignacion['tareas_zonas_comunes'] as $tareaZona) {
                        $tipoTarea = TipoTarea::limpiezaZonasComunes()->first();
                        
                        if ($tipoTarea) {
                            TareaAsignada::create([
                                'turno_id' => $turno->id,
                                'tipo_tarea_id' => $tipoTarea->id,
                                'apartamento_id' => null,
                                'zona_comun_id' => $tareaZona['zona_comun_id'] ?? null,
                                'prioridad_calculada' => $tareaZona['prioridad'] ?? 10,
                                'orden_ejecucion' => $tareaZona['orden_ejecucion'] ?? $ordenEjecucion,
                                'estado' => 'pendiente',
                                'dias_sin_limpiar' => 0
                            ]);
                            
                            $tiempoTotalAsignado += $tareaZona['tiempo_estimado'];
                            $ordenEjecucion++;
                        }
                    }
                }
                
                // Asignar tareas de limpieza común de edificios (SIEMPRE OBLIGATORIAS)
                if (isset($asignacion['tareas_zonas_comunes'])) {
                    foreach ($asignacion['tareas_zonas_comunes'] as $tareaLimpiezaComun) {
                        $tipoTarea = TipoTarea::find($tareaLimpiezaComun['tarea_id']);
                        
                        if ($tipoTarea) {
                            TareaAsignada::create([
                                'turno_id' => $turno->id,
                                'tipo_tarea_id' => $tipoTarea->id,
                                'apartamento_id' => null,
                                'zona_comun_id' => null,
                                'prioridad_calculada' => $tareaLimpiezaComun['prioridad'] ?? $tipoTarea->prioridad_base,
                                'orden_ejecucion' => $tareaLimpiezaComun['orden_ejecucion'] ?? $ordenEjecucion,
                                'estado' => 'pendiente',
                                'dias_sin_limpiar' => 0
                            ]);
                            
                            $tiempoTotalAsignado += $tareaLimpiezaComun['tiempo_estimado'];
                            $ordenEjecucion++;
                        }
                    }
                }
                
                // Asignar tareas generales
                if (isset($asignacion['tareas_generales'])) {
                    foreach ($asignacion['tareas_generales'] as $tareaGeneral) {
                        $tipoTarea = TipoTarea::find($tareaGeneral['tarea_id']);
                        
                        if ($tipoTarea) {
                            TareaAsignada::create([
                                'turno_id' => $turno->id,
                                'tipo_tarea_id' => $tipoTarea->id,
                                'apartamento_id' => null,
                                'zona_comun_id' => null,
                                'prioridad_calculada' => $tareaGeneral['prioridad_calculada'] ?? $tipoTarea->prioridad_base,
                                'orden_ejecucion' => $tareaGeneral['orden_ejecucion'] ?? $ordenEjecucion,
                                'estado' => 'pendiente',
                                'dias_sin_limpiar' => 0
                            ]);
                            
                            $tiempoTotalAsignado += $tareaGeneral['tiempo_estimado'];
                            $ordenEjecucion++;
                        }
                    }
                }
                
                $this->info("✅ Turno creado: {$asignacion['empleada_nombre']} - {$tiempoTotalAsignado}min asignados");
                $turnosGenerados++;
            }
            
            // Mostrar resumen de IA
            if (isset($asignacionIA['resumen'])) {
                $resumen = $asignacionIA['resumen'];
                $this->info("📊 Resumen IA: {$resumen['total_empleadas_utilizadas']} empleadas, {$resumen['edificios_completos_asignados']} edificios completos");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error creando turnos desde IA: " . $e->getMessage());
            throw $e;
        }
        
        return $turnosGenerados;
    }
    
    /**
     * Llamar a OpenAI API
     */
    private function llamarOpenAI($prompt)
    {
        $apiKey = config('openai.api_key');
        
        if (!$apiKey) {
            throw new \Exception("API key de OpenAI no configurada. Configura OPENAI_API_KEY en tu archivo .env");
        }
        
        $client = \OpenAI::client($apiKey);
        
        $response = $client->chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.1,
            'max_tokens' => 2000
        ]);
        
        return $response->choices[0]->message->content;
    }
    
    /**
     * Formatear tiempo en minutos a formato legible
     */
    private function formatearTiempo($minutos)
    {
        $horas = floor($minutos / 60);
        $mins = $minutos % 60;
        
        if ($horas > 0 && $mins > 0) {
            return "{$horas}h {$mins}m";
        } elseif ($horas > 0) {
            return "{$horas}h";
        } else {
            return "{$mins}m";
        }
    }
}