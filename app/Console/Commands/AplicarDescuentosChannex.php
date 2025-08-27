<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Apartamento;
use App\Models\Tarifa;
use App\Models\Reserva;
use App\Models\ConfiguracionDescuento;
use App\Models\HistorialDescuento;
use App\Http\Controllers\ARIController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class AplicarDescuentosChannex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aplicar:descuentos-channex 
                            {--fecha= : Fecha específica para analizar (formato: Y-m-d)}
                            {--configuracion= : ID de la configuración de descuento a usar}
                            {--dry-run : Solo mostrar qué se haría sin aplicar cambios}
                            {--confirmar : Confirmar automáticamente sin preguntar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aplica descuentos de temporada baja a Channex y guarda el historial';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fechaAnalisis = $this->option('fecha') ? Carbon::parse($this->option('fecha')) : Carbon::now();
        $configuracionId = $this->option('configuracion');
        $dryRun = $this->option('dry-run');
        $confirmar = $this->option('confirmar');
        
        $this->info('🚀 APLICANDO DESCUENTOS A CHANNEX');
        $this->info('Fecha de análisis: ' . $fechaAnalisis->format('d/m/Y (l)'));
        $this->info('Modo: ' . ($dryRun ? 'SIMULACIÓN' : 'APLICACIÓN REAL'));
        $this->line('');

        // Obtener configuración de descuento
        $configuracion = $this->obtenerConfiguracionDescuento($configuracionId);
        if (!$configuracion) {
            return;
        }

        // Analizar apartamentos
        $apartamentosConDescuento = $this->analizarApartamentos($fechaAnalisis, $configuracion);
        
        if (empty($apartamentosConDescuento)) {
            $this->info('✅ No hay apartamentos que requieran descuento');
            return;
        }

        // Mostrar resumen
        $this->mostrarResumen($apartamentosConDescuento, $configuracion);

        // Confirmar aplicación
        if (!$confirmar && !$dryRun) {
            if (!$this->confirm('¿Deseas aplicar estos descuentos a Channex?')) {
                $this->info('❌ Operación cancelada');
                return;
            }
        }

        // Aplicar descuentos
        $this->aplicarDescuentos($apartamentosConDescuento, $configuracion, $dryRun);
    }

    /**
     * Obtener configuración de descuento
     */
    private function obtenerConfiguracionDescuento($configuracionId = null)
    {
        if ($configuracionId) {
            $configuracion = ConfiguracionDescuento::find($configuracionId);
        } else {
            $configuracion = ConfiguracionDescuento::activas()->first();
        }

        if (!$configuracion) {
            $this->error('❌ No se encontró configuración de descuento activa');
            $this->line('Crea una configuración de descuento primero');
            return null;
        }

        $this->info("📋 Configuración: {$configuracion->nombre}");
        $this->line("   Descripción: {$configuracion->descripcion}");
        $this->line("   Descuento: {$configuracion->porcentaje_formateado}");
        $this->line('');

        return $configuracion;
    }

    /**
     * Analizar apartamentos para descuentos
     */
    private function analizarApartamentos($fechaAnalisis, $configuracion)
    {
        $apartamentosConDescuento = [];

        // Obtener apartamentos con id_channex
        $apartamentos = Apartamento::whereNotNull('id_channex')
            ->with(['edificioName', 'roomTypes', 'ratePlans', 'tarifas' => function($query) {
                $query->where('tarifas.temporada_baja', true)
                      ->where('tarifas.activo', true);
            }])
            ->get();

        foreach ($apartamentos as $apartamento) {
            $descuento = $this->analizarApartamento($apartamento, $fechaAnalisis, $configuracion);
            if ($descuento) {
                $apartamentosConDescuento[] = $descuento;
            }
        }

        return $apartamentosConDescuento;
    }

    /**
     * Analizar un apartamento específico
     */
    private function analizarApartamento($apartamento, $fechaAnalisis, $configuracion)
    {
        // Verificar si es viernes
        if (!$fechaAnalisis->isFriday()) {
            return null;
        }

        // Calcular semana siguiente
        $lunesSiguiente = $fechaAnalisis->copy()->addDays(3);
        $juevesSiguiente = $lunesSiguiente->copy()->addDays(3);

        // Verificar tarifas de temporada baja
        $tarifasTemporadaBaja = $apartamento->tarifas;
        if ($tarifasTemporadaBaja->isEmpty()) {
            return null;
        }

        foreach ($tarifasTemporadaBaja as $tarifa) {
            // Verificar si la tarifa está vigente
            if ($tarifa->fecha_inicio <= $juevesSiguiente && $tarifa->fecha_fin >= $lunesSiguiente) {
                // Verificar disponibilidad
                $disponibilidad = $this->verificarDisponibilidad($apartamento, $lunesSiguiente, $juevesSiguiente);
                $diasLibres = $disponibilidad['dias_libres'];

                if (!empty($diasLibres)) {
                    return [
                        'apartamento' => $apartamento,
                        'tarifa' => $tarifa,
                        'dias_libres' => $diasLibres,
                        'fecha_inicio' => $lunesSiguiente,
                        'fecha_fin' => $juevesSiguiente,
                        'configuracion' => $configuracion
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Verificar disponibilidad
     */
    private function verificarDisponibilidad($apartamento, $fechaInicio, $fechaFin)
    {
        $diasLibres = [];
        $diasOcupados = [];
        $fechaActual = $fechaInicio->copy();

        while ($fechaActual <= $fechaFin) {
            $reservas = Reserva::where('apartamento_id', $apartamento->id)
                ->where('fecha_entrada', '<=', $fechaActual)
                ->where('fecha_salida', '>', $fechaActual)
                ->whereNull('deleted_at')
                ->exists();

            if (!$reservas) {
                $diasLibres[] = $fechaActual->copy();
            } else {
                $diasOcupados[] = $fechaActual->copy();
            }

            $fechaActual->addDay();
        }

        return [
            'dias_libres' => $diasLibres,
            'dias_ocupados' => $diasOcupados,
            'total_dias_libres' => count($diasLibres),
            'total_dias_ocupados' => count($diasOcupados)
        ];
    }

    /**
     * Mostrar resumen de descuentos
     */
    private function mostrarResumen($apartamentosConDescuento, $configuracion)
    {
        $this->info('📊 RESUMEN DE DESCUENTOS A APLICAR:');
        $this->line('');

        $totalDias = 0;
        $ahorroTotal = 0;

        foreach ($apartamentosConDescuento as $descuento) {
            $apartamento = $descuento['apartamento'];
            $tarifa = $descuento['tarifa'];
            $diasLibres = $descuento['dias_libres'];
            $disponibilidad = $this->verificarDisponibilidad($apartamento, $descuento['fecha_inicio'], $descuento['fecha_fin']);

            $precioConDescuento = $configuracion->calcularPrecioConDescuento($tarifa->precio);
            $ahorroPorDia = $configuracion->calcularAhorroPorDia($tarifa->precio);
            $ahorroTotalDias = $ahorroPorDia * count($diasLibres);

            $this->line("🏠 {$apartamento->nombre}");
            $this->line("   Tarifa: {$tarifa->nombre} ({$tarifa->precio}€)");
            $this->line("   📊 Disponibilidad: {$disponibilidad['total_dias_libres']}/4 días libres, {$disponibilidad['total_dias_ocupados']}/4 días ocupados");
            $this->line("   📅 Días libres (se aplicará descuento):");
            foreach ($diasLibres as $fecha) {
                $this->line("      ✅ {$fecha->format('d/m/Y (l)')}");
            }
            $this->line("   💰 Precio con descuento: {$precioConDescuento}€");
            $this->line("   💵 Ahorro total: {$ahorroTotalDias}€");
            $this->line("");

            $totalDias += count($diasLibres);
            $ahorroTotal += $ahorroTotalDias;
        }

        $this->info("📈 TOTAL:");
        $this->line("   Apartamentos: " . count($apartamentosConDescuento));
        $this->line("   Días totales: {$totalDias}");
        $this->line("   Ahorro total: {$ahorroTotal}€");
        $this->line("");
    }

    /**
     * Aplicar descuentos
     */
    private function aplicarDescuentos($apartamentosConDescuento, $configuracion, $dryRun)
    {
        $this->info('🔄 APLICANDO DESCUENTOS...');
        $this->line('');

        $exitosos = 0;
        $errores = 0;

        foreach ($apartamentosConDescuento as $descuento) {
            $apartamento = $descuento['apartamento'];
            $tarifa = $descuento['tarifa'];
            $diasLibres = $descuento['dias_libres'];

            $this->line("🏠 Procesando: {$apartamento->nombre}");

            try {
                // Crear registro en historial
                $historial = $this->crearHistorial($descuento, $configuracion);

                if (!$dryRun) {
                    // Aplicar descuento a Channex
                    $resultado = $this->aplicarDescuentoChannex($apartamento, $diasLibres, $configuracion, $tarifa);
                    
                    // Actualizar estado del historial
                    $historial->estado = $resultado['success'] ? 'aplicado' : 'error';
                    $historial->datos_channex = $resultado['response'] ?? null;
                    $historial->observaciones = $resultado['message'] ?? null;
                    $historial->save();

                    if ($resultado['success']) {
                        $exitosos++;
                        $this->info("   ✅ Descuento aplicado exitosamente");
                    } else {
                        $errores++;
                        $this->error("   ❌ Error: " . ($resultado['message'] ?? 'Error desconocido'));
                    }
                } else {
                    $exitosos++;
                    $this->info("   ✅ Simulación exitosa");
                }

            } catch (\Exception $e) {
                $errores++;
                $this->error("   ❌ Error: " . $e->getMessage());
                
                if (!$dryRun) {
                    $historial->estado = 'error';
                    $historial->observaciones = $e->getMessage();
                    $historial->save();
                }
            }

            $this->line('');
        }

        $this->info('📊 RESULTADO FINAL:');
        $this->line("   ✅ Exitosos: {$exitosos}");
        $this->line("   ❌ Errores: {$errores}");
    }

    /**
     * Crear registro en historial
     */
    private function crearHistorial($descuento, $configuracion)
    {
        $apartamento = $descuento['apartamento'];
        $tarifa = $descuento['tarifa'];
        $diasLibres = $descuento['dias_libres'];

        $precioConDescuento = $configuracion->calcularPrecioConDescuento($tarifa->precio);
        $ahorroPorDia = $configuracion->calcularAhorroPorDia($tarifa->precio);
        $ahorroTotal = $ahorroPorDia * count($diasLibres);

        return HistorialDescuento::create([
            'apartamento_id' => $apartamento->id,
            'tarifa_id' => $tarifa->id,
            'configuracion_descuento_id' => $configuracion->id,
            'fecha_aplicacion' => now()->toDateString(),
            'fecha_inicio_descuento' => $descuento['fecha_inicio'],
            'fecha_fin_descuento' => $descuento['fecha_fin'],
            'precio_original' => $tarifa->precio,
            'precio_con_descuento' => $precioConDescuento,
            'porcentaje_descuento' => $configuracion->porcentaje_descuento,
            'dias_aplicados' => count($diasLibres),
            'ahorro_total' => $ahorroTotal,
            'estado' => 'pendiente',
            'observaciones' => 'Descuento de temporada baja aplicado automáticamente'
        ]);
    }

    /**
     * Aplicar descuento a Channex
     */
    private function aplicarDescuentoChannex($apartamento, $diasLibres, $configuracion, $tarifa)
    {
        try {
            // Primero verificar si ya tenemos precios establecidos para la temporada
            $preciosEstablecidos = $this->verificarPreciosEstablecidos($apartamento, $diasLibres);
            
            if (!$preciosEstablecidos) {
                // Establecer precios base para la temporada
                $resultadoEstablecimiento = $this->establecerPreciosBase($apartamento, $diasLibres, $tarifa);
                
                if (!$resultadoEstablecimiento['success']) {
                    return [
                        'success' => false,
                        'message' => 'Error estableciendo precios base: ' . $resultadoEstablecimiento['message']
                    ];
                }
                
                $this->line("   ✅ Precios base establecidos para la temporada");
            } else {
                $this->line("   ✅ Precios base ya establecidos para la temporada");
            }

            // Ahora aplicar el descuento
            $precioConDescuento = $configuracion->calcularPrecioConDescuento($tarifa->precio);
            
            // Obtener room types y rate plans del apartamento
            $roomTypes = $apartamento->roomTypes;
            $ratePlans = $apartamento->ratePlans;
            
            if ($roomTypes->isEmpty() || $ratePlans->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No se encontraron room types o rate plans para el apartamento'
                ];
            }

            $updates = [];
            foreach ($diasLibres as $fecha) {
                foreach ($roomTypes as $roomType) {
                    foreach ($ratePlans as $ratePlan) {
                        if ($ratePlan->room_type_id == $roomType->id) {
                            $updates[] = [
                                'property_id' => $apartamento->id_channex,
                                'room_type_id' => $roomType->id_channex,
                                'rate_plan_id' => $ratePlan->id_channex,
                                'date' => $fecha->format('Y-m-d'),
                                'rate' => $precioConDescuento
                            ];
                        }
                    }
                }
            }

            // Enviar actualización a Channex
            $response = Http::withHeaders([
                'user-api-key' => env('CHANNEX_TOKEN'),
            ])->post(env('CHANNEX_URL') . "/restrictions", [
                'values' => $updates
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Descuento aplicado correctamente',
                    'response' => [
                        'sent_data' => $updates,
                        'channex_response' => $response->json()
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error en la respuesta de Channex: ' . $response->body(),
                    'response' => [
                        'sent_data' => $updates,
                        'channex_response' => $response->json()
                    ]
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error aplicando descuento: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verificar si ya tenemos precios establecidos para la temporada
     */
    private function verificarPreciosEstablecidos($apartamento, $diasLibres)
    {
        try {
            // Obtener el primer día para verificar
            $primerDia = $diasLibres->first();
            $roomTypes = $apartamento->roomTypes;
            $ratePlans = $apartamento->ratePlans;

            if ($roomTypes->isEmpty() || $ratePlans->isEmpty()) {
                return false;
            }

            // Verificar con el primer room type y rate plan
            $roomType = $roomTypes->first();
            $ratePlan = $ratePlans->first();

            $response = Http::withHeaders([
                'user-api-key' => env('CHANNEX_TOKEN'),
            ])->get(env('CHANNEX_URL') . "/availability", [
                'filter[property_id]' => $apartamento->id_channex,
                'filter[room_type_id]' => $roomType->id_channex,
                'filter[rate_plan_id]' => $ratePlan->id_channex,
                'filter[date]' => $primerDia->format('Y-m-d'),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['data']) && !empty($data['data'])) {
                    foreach ($data['data'] as $item) {
                        if (isset($item['attributes']['rate']) && $item['attributes']['rate'] > 0) {
                            return true; // Ya hay precios establecidos
                        }
                    }
                }
            }

            return false; // No hay precios establecidos

        } catch (\Exception $e) {
            // Si hay error, asumir que no hay precios establecidos
            return false;
        }
    }

    /**
     * Establecer precios base para la temporada
     */
    private function establecerPreciosBase($apartamento, $diasLibres, $tarifa)
    {
        try {
            $roomTypes = $apartamento->roomTypes;
            $ratePlans = $apartamento->ratePlans;
            
            if ($roomTypes->isEmpty() || $ratePlans->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No se encontraron room types o rate plans para el apartamento'
                ];
            }

            $updates = [];
            foreach ($diasLibres as $fecha) {
                foreach ($roomTypes as $roomType) {
                    foreach ($ratePlans as $ratePlan) {
                        if ($ratePlan->room_type_id == $roomType->id) {
                            $updates[] = [
                                'property_id' => $apartamento->id_channex,
                                'room_type_id' => $roomType->id_channex,
                                'rate_plan_id' => $ratePlan->id_channex,
                                'date' => $fecha->format('Y-m-d'),
                                'rate' => $tarifa->precio
                            ];
                        }
                    }
                }
            }

            // Enviar actualización a Channex
            $response = Http::withHeaders([
                'user-api-key' => env('CHANNEX_TOKEN'),
            ])->post(env('CHANNEX_URL') . "/restrictions", [
                'values' => $updates
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Precios base establecidos correctamente',
                    'response' => $response->json()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error estableciendo precios base: ' . $response->body(),
                    'response' => $response->json()
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error estableciendo precios base: ' . $e->getMessage()
            ];
        }
    }
}
