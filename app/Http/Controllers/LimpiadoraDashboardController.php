<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApartamentoLimpieza;
use App\Models\Fichaje;
use App\Models\User;
use App\Models\Incidencia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LimpiadoraDashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:LIMPIEZA');
    }

    public function index()
    {
        $user = Auth::user();
        $hoy = Carbon::today();
        
        Log::info('LimpiadoraDashboardController - Usuario autenticado: ' . $user->id . ' - Email: ' . $user->email . ' - Rol: ' . $user->role);
        
        try {
            // Obtener apartamentos que SALEN hoy (necesitan limpieza) - misma lógica que /gestion
            $apartamentosPendientesHoy = DB::table('reservas')
                ->whereNull('fecha_limpieza')
                ->where('estado_id', '!=', 4)
                ->whereDate('fecha_salida', $hoy)
                ->pluck('apartamento_id')
                ->toArray();
            
            // Obtener limpiezas de fondo programadas para hoy
            $limpiezasFondoHoy = DB::table('limpieza_fondo')
                ->whereDate('fecha', $hoy)
                ->pluck('apartamento_id')
                ->toArray();
            
            // Combinar todos los apartamentos que necesitan limpieza hoy
            $apartamentosNecesitanLimpieza = array_merge($apartamentosPendientesHoy, $limpiezasFondoHoy);
            $apartamentosNecesitanLimpieza = array_unique($apartamentosNecesitanLimpieza);
            
            // Obtener limpiezas ya asignadas a esta empleada para hoy
            $limpiezasAsignadasHoy = DB::table('apartamento_limpieza')
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
            $limpiezasCompletadasHoy = DB::table('apartamento_limpieza')
                ->where('empleada_id', $user->id)
                ->whereDate('fecha_comienzo', $hoy)
                ->where('status_id', 2) // Completada
                ->count();
                
            // Obtener limpiezas en proceso hoy por esta empleada
            $limpiezasPendientesHoy = DB::table('apartamento_limpieza')
                ->where('empleada_id', $user->id)
                ->whereDate('fecha_comienzo', $hoy)
                ->where('status_id', 1) // En proceso
                ->count();
                
            // Obtener próximas limpiezas - SOLO las de HOY (reservas que salen hoy)
            $proximasLimpiezas = collect();
            
            // Debug: Ver qué fecha estamos usando
            Log::info('Dashboard Limpiadora - Fecha de hoy: ' . $hoy->toDateString());
            
            try {
                // Apartamentos que salen HOY (necesitan limpieza hoy) - CONSULTA SIMPLIFICADA
                $reservasSalidaHoy = DB::table('reservas')
                    ->whereNull('fecha_limpieza')
                    ->where('estado_id', '!=', 4)
                    ->whereDate('fecha_salida', $hoy)
                    ->select('id', 'apartamento_id', 'fecha_salida')
                    ->get();
                
                Log::info('Dashboard Limpiadora - Reservas que salen hoy: ' . $reservasSalidaHoy->count());
                Log::info('Dashboard Limpiadora - Detalles reservas: ' . $reservasSalidaHoy->toJson());
                
                foreach ($reservasSalidaHoy as $reserva) {
                    // Obtener nombre del apartamento por separado
                    $apartamento = DB::table('apartamentos')
                        ->where('id', $reserva->apartamento_id)
                        ->select('nombre')
                        ->first();
                    
                    $proximasLimpiezas->push([
                        'id' => $reserva->id,
                        'apartamento_id' => $reserva->apartamento_id,
                        'nombre_apartamento' => $apartamento->nombre ?? 'Apartamento #' . $reserva->apartamento_id,
                        'numero_apartamento' => null, // No existe la columna numero
                        'fecha_salida' => $reserva->fecha_salida,
                        'hora_salida' => '00:00', // Hora por defecto ya que no existe la columna
                        'status_id' => null, // Pendiente de asignar
                        'tipo' => 'reserva'
                    ]);
                }
                
                // Limpiezas de fondo programadas para HOY
                $limpiezasFondoHoy = DB::table('limpieza_fondo')
                    ->whereDate('fecha', $hoy)
                    ->select('id', 'apartamento_id', 'fecha')
                    ->get();
                
                Log::info('Dashboard Limpiadora - Limpiezas de fondo hoy: ' . $limpiezasFondoHoy->count());
                
                foreach ($limpiezasFondoHoy as $limpieza) {
                    // Obtener nombre del apartamento por separado
                    $apartamento = DB::table('apartamentos')
                        ->where('id', $limpieza->apartamento_id)
                        ->select('nombre')
                        ->first();
                    
                    $proximasLimpiezas->push([
                        'id' => 'fondo_' . $limpieza->id,
                        'apartamento_id' => $limpieza->apartamento_id,
                        'nombre_apartamento' => $apartamento->nombre ?? 'Apartamento #' . $limpieza->apartamento_id,
                        'numero_apartamento' => null, // No existe la columna numero
                        'fecha_salida' => $limpieza->fecha,
                        'hora_salida' => '00:00',
                        'status_id' => null, // Pendiente de asignar
                        'tipo' => 'fondo'
                    ]);
                }
                
                Log::info('Dashboard Limpiadora - Total próximas limpiezas: ' . $proximasLimpiezas->count());
                
                // Ordenar por fecha de salida (más temprano primero)
                $proximasLimpiezas = $proximasLimpiezas->sortBy('fecha_salida')->values();
                
            } catch (\Exception $e) {
                Log::error('Dashboard Limpiadora - Error obteniendo próximas limpiezas: ' . $e->getMessage());
                Log::error('Dashboard Limpiadora - Stack trace: ' . $e->getTraceAsString());
                $proximasLimpiezas = collect(); // En caso de error, usar colección vacía
            }
            
            // Obtener incidencias pendientes del usuario
            $incidenciasPendientes = DB::table('incidencias')
                ->where('empleada_id', $user->id)
                ->where('estado', 'pendiente')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
                
            // Obtener estadísticas de la semana
            $inicioSemana = $hoy->copy()->startOfWeek();
            $finSemana = $hoy->copy()->endOfWeek();
            
            // Limpiezas asignadas a esta empleada en la semana
            $limpiezasSemana = DB::table('apartamento_limpieza')
                ->where('empleada_id', $user->id)
                ->whereBetween('fecha_comienzo', [$inicioSemana, $finSemana])
                ->count();
                
            $limpiezasCompletadasSemana = DB::table('apartamento_limpieza')
                ->where('empleada_id', $user->id)
                ->whereBetween('fecha_comienzo', [$inicioSemana, $finSemana])
                ->where('status_id', 2)
                ->count();
                
            // Calcular porcentaje de completado de la semana
            $porcentajeSemana = $limpiezasSemana > 0 ? round(($limpiezasCompletadasSemana / $limpiezasSemana) * 100) : 0;
                
            // Obtener estado del fichaje actual (usando la estructura correcta)
            $fichajeActual = DB::table('fichajes')
                ->where('user_id', $user->id)
                ->whereDate('hora_entrada', $hoy)
                ->whereNull('hora_salida')
                ->first();
                
            Log::info('Dashboard Limpiadora - Fichaje actual encontrado: ' . ($fichajeActual ? 'SÍ - ID: ' . $fichajeActual->id : 'NO'));
            if ($fichajeActual) {
                Log::info('Dashboard Limpiadora - Detalles fichaje: ID: ' . $fichajeActual->id . ', hora_entrada: ' . $fichajeActual->hora_entrada . ', hora_salida: ' . ($fichajeActual->hora_salida ?? 'NULL'));
            }
                
            // Obtener estadísticas de calidad de limpieza (si existen análisis)
            $analisisRecientes = [];
            try {
                $analisisRecientes = DB::table('photo_analyses')
                    ->where('empleada_id', $user->id)
                    ->whereDate('fecha_analisis', '>=', $hoy->copy()->subDays(7))
                    ->select('calidad_general', DB::raw('count(*) as total'))
                    ->groupBy('calidad_general')
                    ->get()
                    ->pluck('total', 'calidad_general')
                    ->toArray();
            } catch (\Exception $e) {
                // Si hay error, usar array vacío
                $analisisRecientes = [];
            }
            
            // Preparar datos para la vista
            $datos = [
                'limpiezasHoy' => $limpiezasHoy,
                'limpiezasAsignadas' => $limpiezasAsignadas,
                'limpiezasCompletadasHoy' => $limpiezasCompletadasHoy,
                'limpiezasPendientesHoy' => $limpiezasPendientesHoy,
                'apartamentosPendientes' => count($apartamentosPendientes),
                'proximasLimpiezas' => $proximasLimpiezas,
                'incidenciasPendientes' => $incidenciasPendientes,
                'limpiezasSemana' => $limpiezasSemana,
                'limpiezasCompletadasSemana' => $limpiezasCompletadasSemana,
                'porcentajeSemana' => $porcentajeSemana,
                'fichajeActual' => $fichajeActual,
                'analisisRecientes' => $analisisRecientes,
                'hoy' => $hoy->format('d/m/Y'),
                'diaSemana' => $hoy->locale('es')->dayName
            ];
            
            Log::info('Dashboard Limpiadora - Datos preparados correctamente, enviando vista');
            return view('limpiadora.dashboard', compact('datos'));
            
        } catch (\Exception $e) {
            // Si hay algún error, devolver vista con datos mínimos
            Log::error('Dashboard Limpiadora - Error general: ' . $e->getMessage());
            Log::error('Dashboard Limpiadora - Stack trace: ' . $e->getTraceAsString());
            
            $datos = [
                'limpiezasHoy' => 0,
                'limpiezasAsignadas' => 0,
                'limpiezasCompletadasHoy' => 0,
                'limpiezasPendientesHoy' => 0,
                'apartamentosPendientes' => 0,
                'proximasLimpiezas' => collect(),
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
            
            return view('limpiadora.dashboard', compact('datos'));
        }
    }
    
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
            ->whereBetween('fecha', [$inicioMes, $hoy])
            ->whereNotNull('hora_fin')
            ->get()
            ->sum(function($fichaje) {
                if ($fichaje->hora_inicio && $fichaje->hora_fin) {
                    $inicio = Carbon::parse($fichaje->hora_inicio);
                    $fin = Carbon::parse($fichaje->hora_fin);
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
