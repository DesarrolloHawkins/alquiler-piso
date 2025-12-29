<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Amenity;
use App\Models\AmenityConsumo;
use App\Models\AmenityReposicion;
use App\Services\AmenityConsumptionService;

class FixAmenityStocksPorTiempo extends Command
{
    protected $signature = 'amenity:fix-stocks-por-tiempo {--dry-run}';
    protected $description = 'Corrige consumos de amenities tipo "por_tiempo" recalculando cantidades según días de reserva y ajusta stocks';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info('🔧 CORRECCIÓN DE CONSUMOS Y STOCKS POR TIEMPO');
        $this->newLine();

        if ($dryRun) {
            $this->warn('⚠️  Modo DRY-RUN: no se realizarán cambios permanentes');
            $this->newLine();
        }

        // Obtener todos los amenities activos tipo "por_tiempo"
        $amenities = Amenity::where('activo', true)
            ->where('tipo_consumo', 'por_tiempo')
            ->whereNotNull('duracion_dias')
            ->where('duracion_dias', '>', 0)
            ->get();

        if ($amenities->isEmpty()) {
            $this->error('No se encontraron amenities tipo "por_tiempo" con duracion_dias configurada');
            return self::FAILURE;
        }

        $this->info("Procesando {$amenities->count()} amenity(ies) tipo 'por_tiempo'...");
        $this->newLine();

        $totalConsumosCorregidos = 0;
        $totalStockCorregido = 0;
        $amenitiesProcesados = 0;

        foreach ($amenities as $amenity) {
            $this->line("Procesando: {$amenity->nombre} (ID: {$amenity->id})");
            $this->line("  Duración configurada: {$amenity->duracion_dias} días");
            $this->line("  Stock actual: {$amenity->stock_actual} {$amenity->unidad_medida}");

            $resultado = $this->corregirConsumosAmenity($amenity, $dryRun);
            
            $totalConsumosCorregidos += $resultado['consumos_corregidos'];
            $totalStockCorregido += $resultado['stock_corregido'];
            $amenitiesProcesados++;

            if ($resultado['consumos_corregidos'] > 0 || abs($resultado['stock_corregido']) > 0.01) {
                $this->info("  ✅ Corregidos {$resultado['consumos_corregidos']} consumos");
                $this->line("  📊 Stock corregido: {$resultado['stock_anterior']} -> {$resultado['stock_actual']} {$amenity->unidad_medida}");
                if ($resultado['stock_corregido'] > 0) {
                    $this->line("  📈 Diferencia: +{$resultado['stock_corregido']} {$amenity->unidad_medida}");
                } elseif ($resultado['stock_corregido'] < 0) {
                    $this->line("  📉 Diferencia: {$resultado['stock_corregido']} {$amenity->unidad_medida}");
                }
            } else {
                $this->line("  ✓ Sin correcciones necesarias");
            }
            $this->newLine();
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════');
        $this->info("✅ RESUMEN FINAL");
        $this->info("═══════════════════════════════════════════════════════");
        $this->info("Amenities procesados: {$amenitiesProcesados}");
        $this->info("Consumos corregidos: {$totalConsumosCorregidos}");
        $this->info("Stocks corregidos: {$totalStockCorregido}");

        if ($dryRun) {
            $this->warn("\n⚠️  Este fue un DRY-RUN. Ejecuta sin --dry-run para aplicar los cambios.");
        } else {
            $this->info("\n✅ Correcciones aplicadas exitosamente.");
        }

        return self::SUCCESS;
    }

    private function corregirConsumosAmenity(Amenity $amenity, bool $dryRun): array
    {
        $consumosCorregidos = 0;
        $stockAnterior = (float) $amenity->stock_actual;
        $stockActual = $stockAnterior;

        // Obtener todos los consumos de este amenity con sus reservas
        $consumos = AmenityConsumo::where('amenity_id', $amenity->id)
            ->with(['reserva', 'apartamento'])
            ->orderBy('created_at')
            ->get();

        if ($consumos->isEmpty()) {
            return [
                'consumos_corregidos' => 0,
                'stock_corregido' => 0,
                'stock_anterior' => $stockAnterior,
                'stock_actual' => $stockActual
            ];
        }

        // Obtener todas las reposiciones ordenadas por fecha
        $reposiciones = AmenityReposicion::where('amenity_id', $amenity->id)
            ->orderBy('created_at')
            ->get();

        // Estimar stock inicial desde la primera reposición
        $stockCalculado = 0.0;
        if ($reposiciones->isNotEmpty()) {
            $primeraReposicion = $reposiciones->first();
            if ($primeraReposicion->stock_anterior !== null) {
                $stockCalculado = (float) $primeraReposicion->stock_anterior;
            }
        }

        // Merge y ordenar todos los movimientos por fecha
        $movimientos = $reposiciones->concat($consumos)->sortBy('created_at')->values();

        DB::beginTransaction();
        try {
            foreach ($movimientos as $movimiento) {
                if ($movimiento instanceof AmenityReposicion) {
                    // Reposición: sumar al stock
                    $stockCalculado = $stockCalculado + (float) $movimiento->cantidad_reponida;
                } else { // AmenityConsumo
                    // Recalcular cantidad correcta según la reserva
                    $reserva = $movimiento->reserva;
                    $apartamento = $movimiento->apartamento;
                    
                    $cantidadIncorrecta = (float) $movimiento->cantidad_consumida;
                    
                    // Si no hay reserva, no podemos recalcular correctamente, usar cantidad actual
                    if (!$reserva) {
                        $cantidadCorrecta = $cantidadIncorrecta;
                        $this->line("    ⚠️  Consumo ID {$movimiento->id}: Sin reserva asociada, manteniendo cantidad actual: {$cantidadIncorrecta}");
                    } else {
                        $cantidadCorrecta = AmenityConsumptionService::calculateRecommendedQuantity(
                            $amenity,
                            $reserva,
                            $apartamento
                        );
                    }
                    
                    // Calcular diferencia
                    $diferencia = $cantidadIncorrecta - $cantidadCorrecta;
                    
                    // Solo corregir si hay diferencia significativa (más de 0.01)
                    if (abs($diferencia) > 0.01) {
                        $stockAnteriorMovimiento = $stockCalculado;
                        $stockCalculado = $stockCalculado - $cantidadCorrecta;
                        
                        if (!$dryRun) {
                            $movimiento->update([
                                'cantidad_consumida' => $cantidadCorrecta,
                                'cantidad_anterior' => $stockAnteriorMovimiento,
                                'cantidad_actual' => $stockCalculado,
                            ]);
                        }
                        
                        $consumosCorregidos++;
                        $reservaId = $reserva ? $reserva->id : 'N/A';
                        $this->line("    🔧 Consumo ID {$movimiento->id} (Reserva: {$reservaId}): {$cantidadIncorrecta} -> {$cantidadCorrecta} {$amenity->unidad_medida}");
                        
                        if ($reserva) {
                            $diasReserva = 1;
                            if ($reserva->fecha_entrada && $reserva->fecha_salida) {
                                try {
                                    $fechaEntrada = \Carbon\Carbon::parse($reserva->fecha_entrada);
                                    $fechaSalida = \Carbon\Carbon::parse($reserva->fecha_salida);
                                    if ($fechaEntrada->isValid() && $fechaSalida->isValid() && $fechaSalida->gt($fechaEntrada)) {
                                        $diasReserva = $fechaEntrada->diffInDays($fechaSalida);
                                    }
                                } catch (\Exception $e) {
                                    // Ignorar errores de parseo
                                }
                            }
                            $this->line("      📅 Días reserva: {$diasReserva}, Duración amenity: {$amenity->duracion_dias}, Cantidad calculada: {$cantidadCorrecta}");
                        }
                    } else {
                        // Aunque la cantidad sea correcta, verificar que los stocks anteriores/actuales sean correctos
                        $stockAnteriorMovimiento = $stockCalculado;
                        $stockCalculado = $stockCalculado - $cantidadCorrecta;
                        
                        if (abs((float)$movimiento->cantidad_anterior - $stockAnteriorMovimiento) > 0.01 || 
                            abs((float)$movimiento->cantidad_actual - $stockCalculado) > 0.01) {
                            if (!$dryRun) {
                                $movimiento->update([
                                    'cantidad_anterior' => $stockAnteriorMovimiento,
                                    'cantidad_actual' => $stockCalculado,
                                ]);
                            }
                            $consumosCorregidos++;
                        }
                    }
                }
            }

            // Actualizar stock_actual del amenity
            $stockFinal = max(0, $stockCalculado); // No permitir stocks negativos
            $diferenciaStock = $stockFinal - $stockAnterior;
            
            if (!$dryRun) {
                $amenity->update(['stock_actual' => $stockFinal]);
            }
            $stockActual = $stockFinal;

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("  ❌ Error procesando amenity {$amenity->id}: " . $e->getMessage());
            $this->error("  Stack trace: " . $e->getTraceAsString());
            throw $e;
        }

        return [
            'consumos_corregidos' => $consumosCorregidos,
            'stock_corregido' => $diferenciaStock,
            'stock_anterior' => $stockAnterior,
            'stock_actual' => $stockActual
        ];
    }
}
