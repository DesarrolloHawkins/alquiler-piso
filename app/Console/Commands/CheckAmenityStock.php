<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Amenity;
use App\Models\AmenityConsumo;
use App\Models\AmenityReposicion;

class CheckAmenityStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amenity:check-stock {--amenity-id= : ID específico del amenity a comprobar} {--all : Comprobar todos los amenities, incluso inactivos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comprueba el stock de amenities sin hacer cambios. Muestra stock actual vs calculado y detecta inconsistencias.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $amenityId = $this->option('amenity-id');
        $all = $this->option('all');

        $this->info('🔍 COMPROBACIÓN DE STOCK DE AMENITIES');
        $this->line('Este comando solo comprueba el stock, NO realiza cambios.');
        $this->newLine();

        // Obtener amenities a comprobar
        $query = Amenity::query();
        
        if ($amenityId) {
            $query->where('id', $amenityId);
        } elseif (!$all) {
            $query->where('activo', true);
        }

        $amenities = $query->orderBy('categoria')->orderBy('nombre')->get();

        if ($amenities->isEmpty()) {
            $this->error('No se encontraron amenities para comprobar.');
            return self::FAILURE;
        }

        $this->info("📦 Amenities a comprobar: {$amenities->count()}");
        $this->newLine();

        $amenitiesConProblemas = [];
        $amenitiesConStockBajo = [];
        $amenitiesCorrectos = 0;
        $totalInconsistencias = 0;

        foreach ($amenities as $amenity) {
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->line("📦 [{$amenity->id}] {$amenity->nombre}");
            $this->line("   Categoría: {$amenity->categoria}");
            $this->line("   Tipo consumo: {$amenity->tipo_consumo}");
            $this->line("   Unidad: {$amenity->unidad_medida}");
            $this->line("   Activo: " . ($amenity->activo ? 'Sí' : 'No'));
            $this->newLine();

            // Stock actual en BD
            $stockActual = (float) $amenity->stock_actual;
            $this->line("   📊 Stock actual (BD): " . number_format($stockActual, 2) . " {$amenity->unidad_medida}");

            // Calcular stock desde cero
            $totalReposiciones = (float) AmenityReposicion::where('amenity_id', $amenity->id)
                ->sum('cantidad_reponida');

            $totalConsumos = (float) AmenityConsumo::where('amenity_id', $amenity->id)
                ->sum('cantidad_consumida');

            $stockCalculado = $totalReposiciones - $totalConsumos;

            $this->line("   ➕ Total reposiciones: " . number_format($totalReposiciones, 2) . " {$amenity->unidad_medida}");
            $this->line("   ➖ Total consumos: " . number_format($totalConsumos, 2) . " {$amenity->unidad_medida}");
            $this->line("   🧮 Stock calculado: " . number_format($stockCalculado, 2) . " {$amenity->unidad_medida}");

            // Comparar stock actual vs calculado
            $diferencia = abs($stockActual - $stockCalculado);
            $tolerancia = 0.01; // Tolerancia para comparaciones de float

            if ($diferencia > $tolerancia) {
                $this->warn("   ⚠️  DIFERENCIA DETECTADA: " . number_format($diferencia, 2) . " {$amenity->unidad_medida}");
                $amenitiesConProblemas[] = [
                    'amenity' => $amenity,
                    'stock_actual' => $stockActual,
                    'stock_calculado' => $stockCalculado,
                    'diferencia' => $diferencia
                ];
                $totalInconsistencias++;
            } else {
                $this->info("   ✅ Stock consistente");
                $amenitiesCorrectos++;
            }

            // Verificar stock mínimo
            if ($amenity->stock_minimo !== null) {
                $stockMinimo = (float) $amenity->stock_minimo;
                if ($stockActual < $stockMinimo) {
                    $this->warn("   🔴 STOCK BAJO: Actual ({$stockActual}) < Mínimo ({$stockMinimo})");
                    $amenitiesConStockBajo[] = [
                        'amenity' => $amenity,
                        'stock_actual' => $stockActual,
                        'stock_minimo' => $stockMinimo
                    ];
                } elseif ($stockActual <= ($stockMinimo * 1.2)) {
                    $this->comment("   🟡 Stock cerca del mínimo: {$stockActual} (mínimo: {$stockMinimo})");
                }
            }

            // Para amenities tipo "por_reserva", verificar consumo_por_reserva
            if ($amenity->tipo_consumo === 'por_reserva' && $amenity->consumo_por_reserva) {
                $consumoEsperado = (float) $amenity->consumo_por_reserva;
                $consumos = AmenityConsumo::where('amenity_id', $amenity->id)->get();
                $consumosIncorrectos = 0;

                foreach ($consumos as $consumo) {
                    if (abs((float) $consumo->cantidad_consumida - $consumoEsperado) > 0.001) {
                        $consumosIncorrectos++;
                    }
                }

                if ($consumosIncorrectos > 0) {
                    $this->warn("   ⚠️  {$consumosIncorrectos} consumos no usan consumo_por_reserva ({$consumoEsperado})");
                } else {
                    $this->info("   ✅ Todos los consumos usan consumo_por_reserva correctamente");
                }
            }

            $this->newLine();
        }

        // Resumen final
        $this->newLine();
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("📊 RESUMEN DE COMPROBACIÓN");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->line("   ✅ Amenities con stock correcto: {$amenitiesCorrectos}");
        $this->line("   ⚠️  Amenities con inconsistencias: " . count($amenitiesConProblemas));
        $this->line("   🔴 Amenities con stock bajo: " . count($amenitiesConStockBajo));
        $this->newLine();

        if (count($amenitiesConProblemas) > 0) {
            $this->warn("⚠️  AMENITIES CON INCONSISTENCIAS:");
            foreach ($amenitiesConProblemas as $problema) {
                $a = $problema['amenity'];
                $this->line("   • [{$a->id}] {$a->nombre}");
                $this->line("     Stock actual: " . number_format($problema['stock_actual'], 2));
                $this->line("     Stock calculado: " . number_format($problema['stock_calculado'], 2));
                $this->line("     Diferencia: " . number_format($problema['diferencia'], 2) . " {$a->unidad_medida}");
            }
            $this->newLine();
        }

        if (count($amenitiesConStockBajo) > 0) {
            $this->error("🔴 AMENITIES CON STOCK BAJO:");
            foreach ($amenitiesConStockBajo as $bajo) {
                $a = $bajo['amenity'];
                $this->line("   • [{$a->id}] {$a->nombre}");
                $this->line("     Stock actual: " . number_format($bajo['stock_actual'], 2) . " {$a->unidad_medida}");
                $this->line("     Stock mínimo: " . number_format($bajo['stock_minimo'], 2) . " {$a->unidad_medida}");
            }
            $this->newLine();
        }

        if (count($amenitiesConProblemas) === 0 && count($amenitiesConStockBajo) === 0) {
            $this->info("✅ ¡Todos los amenities tienen stock correcto!");
        }

        return self::SUCCESS;
    }
}
