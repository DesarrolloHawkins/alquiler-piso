<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use App\Models\AmenityConsumo;
use App\Models\ApartamentoLimpieza;
use App\Models\Reserva;
use App\Models\Apartamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AmenityLimpiezaController extends Controller
{
    /**
     * Mostrar la vista de gestión de amenities para una limpieza
     */
    public function show(Request $request, $limpiezaId)
    {
        $limpieza = ApartamentoLimpieza::with(['apartamento', 'reserva'])->findOrFail($limpiezaId);
        $apartamento = $limpieza->apartamento;
        $reserva = $limpieza->reserva;
        
        // Obtener amenities activos por categoría
        $amenities = Amenity::activos()
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get()
            ->groupBy('categoria');
        
        // Obtener consumos existentes para esta limpieza
        $consumosExistentes = AmenityConsumo::where('limpieza_id', $limpiezaId)
            ->with('amenity')
            ->get()
            ->keyBy('amenity_id');
        
        // Calcular cantidades recomendadas para cada amenity
        $amenitiesConRecomendaciones = [];
        foreach ($amenities as $categoria => $amenitiesCategoria) {
            foreach ($amenitiesCategoria as $amenity) {
                $cantidadRecomendada = $this->calcularCantidadRecomendada($amenity, $reserva, $apartamento);
                $consumoExistente = $consumosExistentes->get($amenity->id);
                
                $amenitiesConRecomendaciones[$categoria][] = [
                    'amenity' => $amenity,
                    'cantidad_recomendada' => $cantidadRecomendada,
                    'consumo_existente' => $consumoExistente,
                    'stock_disponible' => $amenity->stock_actual
                ];
            }
        }
        
        // Si es una petición AJAX, devolver solo el contenido del formulario
        if ($request->ajax()) {
            return view('admin.amenities.limpieza-form', compact(
                'limpieza',
                'apartamento', 
                'reserva',
                'amenitiesConRecomendaciones',
                'consumosExistentes'
            ));
        }
        
        return view('admin.amenities.limpieza-gestion', compact(
            'limpieza',
            'apartamento', 
            'reserva',
            'amenitiesConRecomendaciones',
            'consumosExistentes'
        ));
    }
    
    /**
     * Guardar el consumo de amenities
     */
    public function store(Request $request, $limpiezaId)
    {
        $request->validate([
            'amenities' => 'required|array',
            'amenities.*.amenity_id' => 'required|exists:amenities,id',
            'amenities.*.cantidad_dejada' => 'required|integer|min:0',
            'amenities.*.observaciones' => 'nullable|string|max:500'
        ]);
        
        $limpieza = ApartamentoLimpieza::findOrFail($limpiezaId);
        
        DB::beginTransaction();
        try {
            foreach ($request->amenities as $amenityData) {
                $amenity = Amenity::find($amenityData['amenity_id']);
                $cantidadDejada = $amenityData['cantidad_dejada'];
                
                // Verificar si ya existe un consumo para este amenity en esta limpieza
                $consumoExistente = AmenityConsumo::where('limpieza_id', $limpiezaId)
                    ->where('amenity_id', $amenity->id)
                    ->first();
                
                if ($consumoExistente) {
                    // Actualizar consumo existente
                    $cantidadAnterior = $consumoExistente->cantidad_actual;
                    $cantidadConsumida = $cantidadAnterior - $cantidadDejada;
                    $costoTotal = $cantidadConsumida * $amenity->precio_compra;
                    
                    $consumoExistente->update([
                        'cantidad_consumida' => $cantidadConsumida,
                        'cantidad_anterior' => $cantidadAnterior,
                        'cantidad_actual' => $cantidadDejada,
                        'costo_total' => $costoTotal,
                        'observaciones' => $amenityData['observaciones'] ?? null,
                        'fecha_consumo' => now()
                    ]);
                } else {
                    // Crear nuevo consumo
                    $cantidadConsumida = $cantidadDejada; // Asumimos que se consume lo que se deja
                    $costoTotal = $cantidadConsumida * $amenity->precio_compra;
                    
                    AmenityConsumo::create([
                        'amenity_id' => $amenity->id,
                        'reserva_id' => $limpieza->reserva_id,
                        'apartamento_id' => $limpieza->apartamento_id,
                        'limpieza_id' => $limpiezaId,
                        'user_id' => auth()->id(),
                        'tipo_consumo' => $amenity->tipo_consumo,
                        'cantidad_consumida' => $cantidadConsumida,
                        'cantidad_anterior' => 0,
                        'cantidad_actual' => $cantidadDejada,
                        'costo_unitario' => $amenity->precio_compra,
                        'costo_total' => $costoTotal,
                        'observaciones' => $amenityData['observaciones'] ?? null,
                        'fecha_consumo' => now()
                    ]);
                }
                
                // Actualizar stock del amenity
                $amenity->decrement('stock_actual', $cantidadDejada);
            }
            
            DB::commit();
            
            return redirect()->back()->with('swal_success', 'Amenities actualizados correctamente');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('swal_error', 'Error al actualizar amenities: ' . $e->getMessage());
        }
    }
    
    /**
     * Calcular cantidad recomendada para un amenity según las reglas de consumo
     */
    private function calcularCantidadRecomendada($amenity, $reserva, $apartamento)
    {
        $numeroPersonas = $reserva ? $reserva->numero_personas : 1;
        $dias = $reserva ? Carbon::parse($reserva->fecha_entrada)->diffInDays($reserva->fecha_salida) : 1;
        
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
     * Obtener historial de consumos de un amenity
     */
    public function historial($amenityId)
    {
        $amenity = Amenity::findOrFail($amenityId);
        $consumos = AmenityConsumo::where('amenity_id', $amenityId)
            ->with(['reserva', 'apartamento', 'limpieza', 'user'])
            ->orderBy('fecha_consumo', 'desc')
            ->paginate(20);
            
        return view('admin.amenities.historial', compact('amenity', 'consumos'));
    }
    
    /**
     * Obtener amenities necesarios para una reserva específica
     */
    public function getAmenitiesReserva($reservaId)
    {
        try {
            $reserva = Reserva::with(['apartamento'])->findOrFail($reservaId);
            
            // Obtener amenities activos por categoría
            $amenities = Amenity::activos()
                ->orderBy('categoria')
                ->orderBy('nombre')
                ->get()
                ->groupBy('categoria');
            
            // Calcular cantidades recomendadas para cada amenity
            $amenitiesConRecomendaciones = [];
            foreach ($amenities as $categoria => $amenitiesCategoria) {
                foreach ($amenitiesCategoria as $amenity) {
                    $cantidadRecomendada = $this->calcularCantidadRecomendada($amenity, $reserva, $reserva->apartamento);
                    
                    $amenitiesConRecomendaciones[$categoria][] = [
                        'amenity' => $amenity,
                        'cantidad_recomendada' => $cantidadRecomendada,
                        'stock_disponible' => $amenity->stock_actual,
                        'precio_unitario' => $amenity->precio_compra,
                        'tipo_consumo' => $amenity->tipo_consumo
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'reserva' => [
                    'id' => $reserva->id,
                    'apartamento' => $reserva->apartamento->titulo ?? $reserva->apartamento->nombre,
                    'numero_personas' => $reserva->numero_personas,
                    'fecha_entrada' => $reserva->fecha_entrada,
                    'fecha_salida' => $reserva->fecha_salida,
                    'dias' => Carbon::parse($reserva->fecha_entrada)->diffInDays($reserva->fecha_salida)
                ],
                'amenities' => $amenitiesConRecomendaciones
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar amenities: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener amenities de una limpieza completada
     */
    public function getAmenitiesLimpiezaCompletada($limpiezaId)
    {
        try {
            $limpieza = ApartamentoLimpieza::with(['apartamento', 'reserva'])->findOrFail($limpiezaId);
            
            if (!$limpieza->apartamento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta limpieza no corresponde a un apartamento'
                ], 400);
            }
            
            // Obtener amenities activos por categoría
            $amenities = Amenity::activos()
                ->orderBy('categoria')
                ->orderBy('nombre')
                ->get()
                ->groupBy('categoria');
            
            // Obtener consumos reales registrados para esta limpieza
            $consumosReales = AmenityConsumo::where('limpieza_id', $limpiezaId)
                ->with('amenity')
                ->get()
                ->keyBy('amenity_id');
            
            // Calcular cantidades recomendadas y comparar con consumos reales
            $amenitiesConEstado = [];
            $totalCosto = 0;
            $amenitiesProporcionados = 0;
            $amenitiesFaltantes = 0;
            
            foreach ($amenities as $categoria => $amenitiesCategoria) {
                foreach ($amenitiesCategoria as $amenity) {
                    $cantidadRecomendada = $this->calcularCantidadRecomendada($amenity, $limpieza->reserva, $limpieza->apartamento);
                    $consumoReal = $consumosReales->get($amenity->id);
                    $cantidadReal = $consumoReal ? $consumoReal->cantidad_dejada : 0;
                    
                    // Determinar estado del amenity
                    $estado = 'completo';
                    if ($cantidadReal == 0) {
                        $estado = 'faltante';
                        $amenitiesFaltantes++;
                    } elseif ($cantidadReal < $cantidadRecomendada) {
                        $estado = 'incompleto';
                        $amenitiesFaltantes++;
                    } else {
                        $amenitiesProporcionados++;
                    }
                    
                    // Calcular costo
                    $costoAmenity = $cantidadReal * $amenity->precio_compra;
                    $totalCosto += $costoAmenity;
                    
                    $amenitiesConEstado[$categoria][] = [
                        'amenity' => $amenity,
                        'cantidad_recomendada' => $cantidadRecomendada,
                        'cantidad_real' => $cantidadReal,
                        'estado' => $estado,
                        'precio_unitario' => $amenity->precio_compra,
                        'costo_total' => $costoAmenity,
                        'tipo_consumo' => $amenity->tipo_consumo,
                        'observaciones' => $consumoReal ? $consumoReal->observaciones : null,
                        'fecha_consumo' => $consumoReal ? $consumoReal->fecha_consumo : null
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'limpieza' => [
                    'id' => $limpieza->id,
                    'apartamento' => $limpieza->apartamento->nombre,
                    'fecha_comienzo' => $limpieza->fecha_comienzo,
                    'fecha_fin' => $limpieza->fecha_fin,
                    'empleado' => $limpieza->empleado ? $limpieza->empleado->name : 'No asignado'
                ],
                'amenities' => $amenitiesConEstado,
                'resumen' => [
                    'total_amenities' => count($amenities->flatten()),
                    'proporcionados' => $amenitiesProporcionados,
                    'faltantes' => $amenitiesFaltantes,
                    'costo_total' => round($totalCosto, 2)
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar amenities de la limpieza: ' . $e->getMessage()
            ], 500);
        }
    }
}
