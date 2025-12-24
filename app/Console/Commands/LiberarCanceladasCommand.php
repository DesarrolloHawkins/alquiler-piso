<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;
use App\Models\Apartamento;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LiberarCanceladasCommand extends Command
{
    protected $signature = 'ari:liberar-canceladas';
    protected $description = 'Libera disponibilidad en Channex para reservas canceladas (excluyendo Booking y Airbnb)';

    public function handle()
    {
        $this->info("🔍 Buscando reservas canceladas para liberar en Channex...");
        $this->newLine();

        // Buscar reservas canceladas que:
        // 1. Estén canceladas (estado_id = 4) pero NO ya liberadas en Channex (estado_id = 9)
        // 2. Tengan fechas futuras o actuales
        // 3. NO sean de Booking ni Airbnb (esas se gestionan desde sus plataformas)
        // 4. Tengan datos completos (apartamento y room_type)
        $canceladas = Reserva::where('estado_id', 4) // Canceladas (excluye estado 9 = "Cancelada en Channex")
            ->where(function ($query) {
                // Fechas futuras o que terminen hoy o después
                $query->whereDate('fecha_salida', '>=', now()->toDateString());
            })
            ->whereNotNull('apartamento_id')
            ->whereNotNull('room_type_id')
            ->whereNotIn('origen', ['Booking', 'BookingCom', 'Airbnb', 'AirbnbCom'])
            ->get();

        if ($canceladas->isEmpty()) {
            $this->info("✅ No se encontraron reservas canceladas para liberar.");
            return 0;
        }

        $this->info("📋 Encontradas {$canceladas->count()} reserva(s) cancelada(s) para procesar.");
        $this->newLine();

        $apiUrl = env('CHANNEX_URL', 'https://app.channex.io/api/v1');
        $apiToken = env('CHANNEX_TOKEN');

        if (!$apiToken) {
            $this->error("❌ CHANNEX_TOKEN no está configurado en .env");
            Log::error('CHANNEX_TOKEN no configurado para liberar canceladas');
            return 1;
        }

        $liberadas = 0;
        $errores = 0;
        $omitidas = 0; // Reservas omitidas por tener reservas activas

        foreach ($canceladas as $reserva) {
            $apartamento = $reserva->apartamento;
            $roomType = RoomType::find($reserva->room_type_id);

            // Validar datos necesarios
            if (!$apartamento || !$roomType) {
                $this->warn("⚠️  Reserva #{$reserva->id}: Apartamento o RoomType no encontrado. Saltando...");
                continue;
            }

            if (!$apartamento->id_channex || !$roomType->id_channex) {
                $this->warn("⚠️  Reserva #{$reserva->id}: Faltan IDs de Channex. Saltando...");
                Log::warning('Reserva cancelada sin IDs de Channex', [
                    'reserva_id' => $reserva->id,
                    'apartamento_id' => $apartamento->id,
                    'apartamento_id_channex' => $apartamento->id_channex,
                    'room_type_id' => $roomType->id ?? null,
                    'room_type_id_channex' => $roomType->id_channex ?? null,
                ]);
                continue;
            }

            $start = Carbon::parse($reserva->fecha_entrada);
            $end = Carbon::parse($reserva->fecha_salida)->subDay(); // Restar 1 día como en otros comandos

            // ⚠️ VALIDACIÓN CRÍTICA: Verificar si hay reservas activas en esas fechas
            // Si hay una reserva activa, NO liberamos (evita liberar fechas ocupadas)
            // Esto es crítico porque puede haber entrado una nueva reserva después de cancelar
            $reservasActivas = Reserva::where('apartamento_id', $apartamento->id)
                ->where('room_type_id', $roomType->id)
                ->where('id', '!=', $reserva->id) // Excluir la reserva cancelada actual
                ->activas() // Usar el scope activas() que excluye canceladas (estado_id = 4)
                ->where('estado_id', '!=', 7) // Excluir también temporales (estado_id = 7)
                ->where(function ($query) use ($start, $reserva) {
                    // Verificar solapamiento: la reserva activa solapa si:
                    // - Su fecha_entrada es anterior a nuestra fecha_salida
                    // - Y su fecha_salida es posterior a nuestra fecha_entrada
                    $query->where('fecha_entrada', '<', $reserva->fecha_salida)
                          ->where('fecha_salida', '>', $start);
                })
                ->exists();

            if ($reservasActivas) {
                $this->warn("⚠️  Reserva #{$reserva->id}: Hay reservas activas en esas fechas. NO se liberará disponibilidad.");
                $this->line("   Apartamento: {$apartamento->nombre}");
                $this->line("   Fechas: {$start->format('d/m/Y')} - {$reserva->fecha_salida->format('d/m/Y')}");
                $this->newLine();
                
                $omitidas++;
                
                Log::info('Reserva cancelada NO liberada: hay reservas activas en esas fechas', [
                    'reserva_id' => $reserva->id,
                    'apartamento_id' => $apartamento->id,
                    'fecha_entrada' => $start->toDateString(),
                    'fecha_salida' => $reserva->fecha_salida->toDateString(),
                ]);
                continue;
            }
            
            // Preparar actualización de disponibilidad (formato consistente con otros comandos)
            $update = [
                'property_id' => $apartamento->id_channex,
                'room_type_id' => $roomType->id_channex,
                'date_from' => $start->toDateString(),
                'date_to' => $end->toDateString(),
                'update_type' => 'availability',
                'availability' => 1, // Habilitar disponibilidad (liberar)
            ];

            $dias = $start->diffInDays($end) + 1; // +1 para incluir ambos días

            $this->info("🔄 Procesando reserva #{$reserva->id} ({$reserva->origen})");
            $this->line("   Apartamento: {$apartamento->nombre} (Channex ID: {$apartamento->id_channex})");
            $this->line("   Fechas: {$start->format('d/m/Y')} - {$reserva->fecha_salida->format('d/m/Y')}");
            $this->line("   Días a liberar: {$dias}");

            // Enviar actualización a Channex
            try {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'user-api-key' => $apiToken,
                        'Content-Type' => 'application/json',
                    ])
                    ->post("{$apiUrl}/availability", [
                        'values' => [$update] // Formato consistente con otros comandos
                    ]);

                if ($response->successful()) {
                    // Cambiar estado a 9 (Cancelada en Channex) para que no se procese más
                    $reserva->estado_id = 9;
                    $reserva->save();
                    
                    $this->info("   ✅ Disponibilidad liberada correctamente en Channex");
                    $this->line("   📝 Estado cambiado a 'Cancelada en Channex' (ID: 9)");
                    $liberadas++;
                    
                    Log::info('Reserva cancelada liberada en Channex y estado actualizado', [
                        'reserva_id' => $reserva->id,
                        'codigo_reserva' => $reserva->codigo_reserva,
                        'origen' => $reserva->origen,
                        'apartamento_id' => $apartamento->id,
                        'apartamento_id_channex' => $apartamento->id_channex,
                        'fecha_entrada' => $start->toDateString(),
                        'fecha_salida' => $reserva->fecha_salida->toDateString(),
                        'dias_liberados' => $dias,
                        'nuevo_estado_id' => 9,
                    ]);
                } else {
                    $this->error("   ❌ Error al liberar: HTTP {$response->status()}");
                    $this->line("   Respuesta: " . substr($response->body(), 0, 200));
                    $errores++;
                    
                    Log::error('Error al liberar reserva cancelada en Channex', [
                        'reserva_id' => $reserva->id,
                        'http_status' => $response->status(),
                        'error_body' => $response->body(),
                        'payload' => $update,
                    ]);
                }
            } catch (\Exception $e) {
                $this->error("   ❌ Excepción: " . $e->getMessage());
                $errores++;
                
                Log::error('Excepción al liberar reserva cancelada', [
                    'reserva_id' => $reserva->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            
            $this->newLine();
        }

        // Resumen final
        $this->newLine();
        $this->info("📊 Resumen:");
        $this->info("   ✅ Liberadas: {$liberadas}");
        if ($omitidas > 0) {
            $this->warn("   ⚠️  Omitidas (hay reservas activas): {$omitidas}");
        }
        if ($errores > 0) {
            $this->error("   ❌ Errores: {$errores}");
        }
        $this->info("   📋 Total procesadas: {$canceladas->count()}");
        $this->newLine();
        $this->info("✅ Proceso de liberación de reservas canceladas finalizado.");

        return 0;
    }
}

