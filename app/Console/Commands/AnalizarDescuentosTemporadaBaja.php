<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Apartamento;
use App\Models\Tarifa;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AnalizarDescuentosTemporadaBaja extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analizar:descuentos-temporada-baja {--fecha= : Fecha específica para analizar (formato: Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analiza apartamentos con id_channex para aplicar descuentos de temporada baja';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fechaAnalisis = $this->option('fecha') ? Carbon::parse($this->option('fecha')) : Carbon::now();
        
        $this->info('🔍 ANALIZANDO DESCUENTOS DE TEMPORADA BAJA');
        $this->info('Fecha de análisis: ' . $fechaAnalisis->format('d/m/Y (l)'));
        $this->line('');

        // Obtener apartamentos con id_channex
        $apartamentos = Apartamento::whereNotNull('id_channex')
            ->with(['edificioName', 'tarifas' => function($query) {
                $query->where('tarifas.temporada_baja', true)
                      ->where('tarifas.activo', true);
            }])
            ->get();

        if ($apartamentos->isEmpty()) {
            $this->warn('❌ No se encontraron apartamentos con id_channex configurado.');
            return;
        }

        $this->info("📊 Se encontraron {$apartamentos->count()} apartamentos con id_channex:");
        $this->line('');

        $totalApartamentosAnalizados = 0;
        $apartamentosConDescuento = 0;

        foreach ($apartamentos as $apartamento) {
            $this->analizarApartamento($apartamento, $fechaAnalisis, $totalApartamentosAnalizados, $apartamentosConDescuento);
        }

        $this->line('');
        $this->info('📈 RESUMEN DEL ANÁLISIS:');
        $this->info("   • Apartamentos analizados: {$totalApartamentosAnalizados}");
        $this->info("   • Apartamentos con descuento aplicable: {$apartamentosConDescuento}");
        
        if ($apartamentosConDescuento > 0) {
            $this->warn('   ⚠️  Se encontraron apartamentos que requieren descuento del 20%');
        } else {
            $this->info('   ✅ No se encontraron apartamentos que requieran descuento');
        }
    }

    /**
     * Analiza un apartamento específico
     */
    private function analizarApartamento($apartamento, $fechaAnalisis, &$totalAnalizados, &$conDescuento)
    {
        $totalAnalizados++;
        
        $this->info("🏠 APARTAMENTO: {$apartamento->nombre}");
        $this->line("   ID: {$apartamento->id}");
        $this->line("   ID Channex: {$apartamento->id_channex}");
        $this->line("   Edificio: " . ($apartamento->edificioName ? $apartamento->edificioName->nombre : 'Sin edificio'));
        $this->line('');

        // Verificar si hoy es viernes
        $esViernes = $fechaAnalisis->isFriday();
        $this->line("   📅 ¿Es viernes? " . ($esViernes ? '✅ SÍ' : '❌ NO'));

        if (!$esViernes) {
            $this->line("   ℹ️  No es viernes, no se aplica la lógica de descuento");
            $this->line('');
            return;
        }

        // Calcular la semana que viene (lunes a jueves)
        $lunesSiguiente = $fechaAnalisis->copy()->addDays(3); // Viernes + 3 = Lunes
        $juevesSiguiente = $lunesSiguiente->copy()->addDays(3); // Lunes + 3 = Jueves

        $this->line("   📅 Semana siguiente: {$lunesSiguiente->format('d/m/Y (l)')} - {$juevesSiguiente->format('d/m/Y (l)')}");

        // Verificar tarifas de temporada baja
        $tarifasTemporadaBaja = $apartamento->tarifas;
        
        if ($tarifasTemporadaBaja->isEmpty()) {
            $this->warn("   ⚠️  No tiene tarifas de temporada baja configuradas");
            $this->line('');
            return;
        }

        $this->line("   💰 Tarifas de temporada baja encontradas: {$tarifasTemporadaBaja->count()}");
        
        foreach ($tarifasTemporadaBaja as $tarifa) {
            $this->analizarTarifa($apartamento, $tarifa, $lunesSiguiente, $juevesSiguiente, $conDescuento);
        }

        $this->line('');
    }

    /**
     * Analiza una tarifa específica
     */
    private function analizarTarifa($apartamento, $tarifa, $lunesSiguiente, $juevesSiguiente, &$conDescuento)
    {
        $this->line("      📋 Tarifa: {$tarifa->nombre}");
        $this->line("         Precio base: {$tarifa->precio}€");
        $this->line("         Vigente: {$tarifa->fecha_inicio->format('d/m/Y')} - {$tarifa->fecha_fin->format('d/m/Y')}");

        // Verificar si la tarifa está vigente en la semana siguiente
        $tarifaVigente = $tarifa->fecha_inicio <= $juevesSiguiente && $tarifa->fecha_fin >= $lunesSiguiente;
        
        if (!$tarifaVigente) {
            $this->line("         ❌ No está vigente en la semana siguiente");
            return;
        }

        $this->line("         ✅ Está vigente en la semana siguiente");

        // Verificar disponibilidad (días libres) de lunes a jueves
        $disponibilidad = $this->verificarDisponibilidad($apartamento, $lunesSiguiente, $juevesSiguiente);
        $diasLibres = $disponibilidad['dias_libres'];
        $diasOcupados = $disponibilidad['dias_ocupados'];
        $reservasExistentes = $disponibilidad['reservas'];
        $totalDiasLibres = $disponibilidad['total_dias_libres'];
        $totalDiasOcupados = $disponibilidad['total_dias_ocupados'];
        
        $this->line("         📊 Resumen de disponibilidad:");
        $this->line("            • Días libres: {$totalDiasLibres}/4");
        $this->line("            • Días ocupados: {$totalDiasOcupados}/4");
        
        if (empty($diasLibres)) {
            $this->line("         ❌ No hay días libres en la semana siguiente");
            
            // Mostrar reservas existentes
            if (!empty($reservasExistentes)) {
                $this->line("         📋 Reservas existentes:");
                foreach ($reservasExistentes as $fecha => $reservas) {
                    $fechaObj = Carbon::parse($fecha);
                    $this->line("            📅 {$fechaObj->format('d/m/Y (l)')}:");
                    foreach ($reservas as $reserva) {
                        $estado = $reserva->estado ? $reserva->estado->nombre : 'Sin estado';
                        $cliente = $reserva->cliente ? $reserva->cliente->nombre : 'Sin cliente';
                        $this->line("               • Reserva #{$reserva->id} - {$cliente} ({$estado})");
                    }
                }
            }
            return;
        }

        $conDescuento++;
        $this->warn("         🎯 ¡DESCUENTO APLICABLE!");
        $this->line("         📅 Días libres (se aplicará descuento):");
        
        foreach ($diasLibres as $fecha) {
            $this->line("            ✅ {$fecha->format('d/m/Y (l)')} - LIBRE");
        }

        // Mostrar días ocupados
        if (!empty($diasOcupados)) {
            $this->line("         📅 Días ocupados (NO se aplicará descuento):");
            foreach ($diasOcupados as $fecha) {
                $this->line("            ❌ {$fecha->format('d/m/Y (l)')} - OCUPADO");
            }
        }

        // Mostrar reservas existentes en días ocupados
        if (!empty($reservasExistentes)) {
            $this->line("         📋 Detalle de reservas en días ocupados:");
            foreach ($reservasExistentes as $fecha => $reservas) {
                $fechaObj = Carbon::parse($fecha);
                $this->line("            📅 {$fechaObj->format('d/m/Y (l)')}:");
                foreach ($reservas as $reserva) {
                    $estado = $reserva->estado ? $reserva->estado->nombre : 'Sin estado';
                    $cliente = $reserva->cliente ? $reserva->cliente->nombre : 'Sin cliente';
                    $this->line("               • Reserva #{$reserva->id} - {$cliente} ({$estado})");
                }
            }
        }

        $precioConDescuento = $tarifa->precio * 0.8; // 20% de descuento
        $this->line("         💰 Precio con descuento: {$precioConDescuento}€ (20% menos)");
        $this->line("         📊 Ahorro por día: " . ($tarifa->precio - $precioConDescuento) . "€");
    }

    /**
     * Verifica la disponibilidad de un apartamento en un rango de fechas
     */
    private function verificarDisponibilidad($apartamento, $fechaInicio, $fechaFin)
    {
        $diasLibres = [];
        $diasOcupados = [];
        $reservasExistentes = [];
        $fechaActual = $fechaInicio->copy();

        while ($fechaActual <= $fechaFin) {
            // Verificar si hay reservas para esta fecha
            $reservas = Reserva::where('apartamento_id', $apartamento->id)
                ->where('fecha_entrada', '<=', $fechaActual)
                ->where('fecha_salida', '>', $fechaActual)
                ->whereNull('deleted_at')
                ->with(['cliente', 'estado'])
                ->get();

            if ($reservas->isEmpty()) {
                $diasLibres[] = $fechaActual->copy();
            } else {
                $diasOcupados[] = $fechaActual->copy();
                $reservasExistentes[$fechaActual->format('Y-m-d')] = $reservas;
            }

            $fechaActual->addDay();
        }

        return [
            'dias_libres' => $diasLibres, 
            'dias_ocupados' => $diasOcupados,
            'reservas' => $reservasExistentes,
            'total_dias_libres' => count($diasLibres),
            'total_dias_ocupados' => count($diasOcupados)
        ];
    }
}
