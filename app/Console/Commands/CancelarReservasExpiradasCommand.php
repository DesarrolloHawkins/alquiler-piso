<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;
use App\Models\Pago;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class CancelarReservasExpiradasCommand extends Command
{
    protected $signature = 'reservas:cancelar-expiradas';
    protected $description = 'Cancela reservas en estado "Progreso" que llevan más de 5 minutos sin completar el pago';

    public function handle()
    {
        $this->info("🕐 Buscando reservas en progreso expiradas...");
        
        // Buscar reservas con estado "Progreso" (10) creadas hace más de 5 minutos
        $fechaLimite = Carbon::now()->subMinutes(5);
        
        $reservasExpiradas = Reserva::where('estado_id', 10) // Progreso
            ->where('created_at', '<', $fechaLimite)
            ->whereHas('pagos', function ($query) {
                $query->whereIn('estado', ['pendiente', 'procesando'])
                      ->whereNotNull('stripe_checkout_session_id');
            })
            ->with(['pagos', 'apartamento'])
            ->get();
        
        if ($reservasExpiradas->isEmpty()) {
            $this->info("✅ No se encontraron reservas expiradas para cancelar.");
            return 0;
        }
        
        $this->info("📋 Encontradas {$reservasExpiradas->count()} reserva(s) expirada(s).");
        $this->newLine();
        
        $canceladas = 0;
        $errores = 0;
        
        foreach ($reservasExpiradas as $reserva) {
            try {
                $this->line("Procesando reserva #{$reserva->id} ({$reserva->codigo_reserva})...");
                
                // Obtener el pago pendiente asociado
                $pago = $reserva->pagos()
                    ->whereIn('estado', ['pendiente', 'procesando'])
                    ->whereNotNull('stripe_checkout_session_id')
                    ->first();
                
                if (!$pago) {
                    $this->warn("  ⚠️  No se encontró pago pendiente para la reserva #{$reserva->id}");
                    continue;
                }
                
                // Actualizar el pago a cancelado
                $pago->update(['estado' => 'cancelado']);
                $this->line("  ✓ Pago #{$pago->id} marcado como cancelado");
                
                // Actualizar la reserva a cancelada
                $reserva->estado_id = 4; // Cancelada
                $reserva->save();
                $this->line("  ✓ Reserva #{$reserva->id} marcada como cancelada");
                
                // Liberar disponibilidad en Channex
                $this->liberarDisponibilidadChannex($reserva);
                
                $canceladas++;
                
                Log::info('Reserva cancelada por expiración de tiempo de pago', [
                    'reserva_id' => $reserva->id,
                    'codigo_reserva' => $reserva->codigo_reserva,
                    'pago_id' => $pago->id,
                    'minutos_transcurridos' => Carbon::now()->diffInMinutes($reserva->created_at),
                    'stripe_checkout_session_id' => $pago->stripe_checkout_session_id,
                ]);
                
                $this->info("  ✅ Reserva #{$reserva->id} cancelada exitosamente");
                $this->newLine();
                
            } catch (\Exception $e) {
                $errores++;
                $this->error("  ❌ Error al cancelar reserva #{$reserva->id}: " . $e->getMessage());
                
                Log::error('Error al cancelar reserva expirada', [
                    'reserva_id' => $reserva->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
        
        $this->newLine();
        $this->info("📊 Resumen:");
        $this->info("  ✅ Canceladas: {$canceladas}");
        if ($errores > 0) {
            $this->warn("  ❌ Errores: {$errores}");
        }
        
        return 0;
    }
    
    /**
     * Liberar disponibilidad en Channex para una reserva cancelada
     */
    private function liberarDisponibilidadChannex(Reserva $reserva)
    {
        try {
            $apartamento = $reserva->apartamento;
            $roomType = RoomType::find($reserva->room_type_id);
            
            if (!$apartamento || !$apartamento->id_channex || !$roomType || !$roomType->id_channex) {
                $this->warn("  ⚠️  No se puede liberar disponibilidad en Channex: faltan datos");
                Log::warning('No se puede liberar disponibilidad en Channex: faltan datos', [
                    'reserva_id' => $reserva->id,
                    'apartamento_id_channex' => $apartamento->id_channex ?? null,
                    'room_type_id_channex' => $roomType->id_channex ?? null,
                ]);
                return;
            }
            
            $startDate = Carbon::parse($reserva->fecha_entrada);
            $endDate = Carbon::parse($reserva->fecha_salida)->subDay();
            
            $update = [
                'property_id' => $apartamento->id_channex,
                'room_type_id' => $roomType->id_channex,
                'date_from' => $startDate->toDateString(),
                'date_to' => $endDate->toDateString(),
                'update_type' => 'availability',
                'availability' => 1, // Habilitar disponibilidad (liberar)
            ];
            
            $apiUrl = env('CHANNEX_URL', 'https://app.channex.io/api/v1');
            $apiToken = env('CHANNEX_TOKEN');
            
            if (!$apiToken) {
                $this->warn("  ⚠️  CHANNEX_TOKEN no configurado");
                Log::error('CHANNEX_TOKEN no configurado para liberar disponibilidad');
                return;
            }
            
            $response = Http::timeout(10)
                ->withHeaders([
                    'user-api-key' => $apiToken,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$apiUrl}/availability", ['values' => [$update]]);
            
            if ($response->successful()) {
                $this->line("  ✓ Disponibilidad liberada en Channex");
                Log::info('Disponibilidad liberada en Channex - reserva cancelada por expiración', [
                    'reserva_id' => $reserva->id,
                    'codigo_reserva' => $reserva->codigo_reserva,
                    'apartamento_id' => $apartamento->id,
                    'fecha_entrada' => $startDate->toDateString(),
                    'fecha_salida' => $endDate->toDateString(),
                ]);
            } else {
                $this->warn("  ⚠️  Error al liberar disponibilidad en Channex: " . $response->status());
                Log::error('Error al liberar disponibilidad en Channex', [
                    'reserva_id' => $reserva->id,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            $this->warn("  ⚠️  Excepción al liberar disponibilidad en Channex: " . $e->getMessage());
            Log::error('Excepción al liberar disponibilidad en Channex', [
                'reserva_id' => $reserva->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

