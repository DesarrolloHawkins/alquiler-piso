<?php

namespace App\Console\Commands;

use App\Models\ConfiguracionDescuento;
use App\Models\Apartamento;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AnalizarDescuentosTemporadaBaja extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analizar:descuentos-temporada-baja {--fecha= : Fecha de análisis (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analiza descuentos de temporada baja basados en ocupación por edificio';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fechaAnalisis = $this->option('fecha') ? Carbon::parse($this->option('fecha')) : Carbon::now();
        
        $this->info('🔍 ANALIZANDO DESCUENTOS POR OCUPACIÓN DE EDIFICIO');
        $this->line("Fecha de análisis: {$fechaAnalisis->format('d/m/Y')} (" . $fechaAnalisis->format('l') . ")");
        $this->line('');

        // Obtener configuraciones activas por edificio
        $configuraciones = ConfiguracionDescuento::with('edificio.apartamentos')
            ->activas()
            ->get();

        if ($configuraciones->isEmpty()) {
            $this->warn("⚠️  No hay configuraciones de descuento activas");
            return;
        }

        $this->info("📊 Se encontraron {$configuraciones->count()} configuraciones activas:");
        $this->line('');

        $edificiosConAccion = 0;

        foreach ($configuraciones as $configuracion) {
            $this->analizarConfiguracion($configuracion, $fechaAnalisis, $edificiosConAccion);
        }

        $this->info('📈 RESUMEN DEL ANÁLISIS:');
        $this->line("   • Configuraciones analizadas: {$configuraciones->count()}");
        $this->line("   • Edificios con acción aplicable: {$edificiosConAccion}");
        if ($edificiosConAccion > 0) {
            $this->warn("   ⚠️  Se encontraron edificios que requieren ajuste de precios");
        }
    }

    /**
     * Analiza una configuración específica
     */
    private function analizarConfiguracion($configuracion, $fechaAnalisis, &$edificiosConAccion)
    {
        $this->info("🏢 EDIFICIO: {$configuracion->edificio->nombre}");
        $this->line("   Configuración: {$configuracion->nombre}");
        $this->line("   Descuento: {$configuracion->porcentaje_formateado}");
        $this->line("   Incremento: {$configuracion->porcentaje_incremento_formateado}");
        $this->line('');

        // Verificar si hoy es el día configurado
        $diaConfigurado = $configuracion->condiciones['dia_semana'] ?? 'friday';
        $esDiaConfigurado = $this->esDiaConfigurado($fechaAnalisis, $diaConfigurado);
        
        $this->line("   📅 ¿Es {$this->getNombreDia($diaConfigurado)}? " . ($esDiaConfigurado ? '✅ SÍ' : '❌ NO'));

        if (!$esDiaConfigurado) {
            $this->line("   ℹ️  No es el día configurado, no se aplica la lógica");
            $this->line('');
            return;
        }

        // Calcular la semana que viene (lunes a jueves)
        $lunesSiguiente = $fechaAnalisis->copy()->addDays(3); // Viernes + 3 = Lunes
        $juevesSiguiente = $lunesSiguiente->copy()->addDays(3); // Lunes + 3 = Jueves

        $this->line("   📅 Semana siguiente: {$lunesSiguiente->format('d/m/Y (l)')} - {$juevesSiguiente->format('d/m/Y (l)')}");

        // Calcular ocupación del edificio
        $ocupacion = $configuracion->calcularOcupacionEdificio($lunesSiguiente, $juevesSiguiente);
        $this->line("   📊 Ocupación del edificio: {$ocupacion}%");

        // Determinar acción basada en ocupación
        $accion = $configuracion->determinarAccionOcupacion($lunesSiguiente, $juevesSiguiente);
        
        if ($accion['accion'] === 'ninguna') {
            $this->line("   ✅ Ocupación normal ({$ocupacion}%), no se requiere acción");
            $this->line('');
            return;
        }

        $edificiosConAccion++;
        
        if ($accion['accion'] === 'descuento') {
            $this->warn("   🎯 ¡DESCUENTO APLICABLE!");
            $this->line("   📉 Ocupación baja ({$ocupacion}% < {$accion['ocupacion_limite']}%)");
            $this->line("   💰 Se aplicará descuento del {$accion['porcentaje']}%");
        } else {
            $this->warn("   🎯 ¡INCREMENTO APLICABLE!");
            $this->line("   📈 Ocupación alta ({$ocupacion}% > {$accion['ocupacion_limite']}%)");
            $this->line("   💰 Se aplicará incremento del {$accion['porcentaje']}%");
        }

        // Analizar apartamentos del edificio
        $this->analizarApartamentosEdificio($configuracion, $lunesSiguiente, $juevesSiguiente, $accion);
        
        $this->line('');
    }

    /**
     * Analiza los apartamentos de un edificio
     */
    private function analizarApartamentosEdificio($configuracion, $lunesSiguiente, $juevesSiguiente, $accion)
    {
        $apartamentos = $configuracion->edificio->apartamentos;
        
        $this->line("   🏠 Apartamentos del edificio ({$apartamentos->count()}):");
        
        $apartamentosConAccion = 0;
        
        foreach ($apartamentos as $apartamento) {
            $disponibilidad = $this->verificarDisponibilidad($apartamento, $lunesSiguiente, $juevesSiguiente);
            $diasLibres = $disponibilidad['dias_libres'];
            
            if (!empty($diasLibres)) {
                $apartamentosConAccion++;
                $this->line("      ✅ {$apartamento->nombre}: " . count($diasLibres) . " días libres");
                
                foreach ($diasLibres as $fecha) {
                    $this->line("         • {$fecha->format('d/m/Y (l)')}");
                }
            } else {
                $this->line("      ❌ {$apartamento->nombre}: Sin días libres");
            }
        }
        
        $this->line("   📊 Total apartamentos con acción: {$apartamentosConAccion}/{$apartamentos->count()}");
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

    /**
     * Verifica si la fecha es el día configurado
     */
    private function esDiaConfigurado($fecha, $diaConfigurado)
    {
        $dias = [
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 0
        ];
        
        return $fecha->dayOfWeek === $dias[$diaConfigurado];
    }

    /**
     * Obtiene el nombre del día en español
     */
    private function getNombreDia($diaConfigurado)
    {
        $dias = [
            'monday' => 'Lunes',
            'tuesday' => 'Martes',
            'wednesday' => 'Miércoles',
            'thursday' => 'Jueves',
            'friday' => 'Viernes',
            'saturday' => 'Sábado',
            'sunday' => 'Domingo'
        ];
        
        return $dias[$diaConfigurado] ?? $diaConfigurado;
    }
}
