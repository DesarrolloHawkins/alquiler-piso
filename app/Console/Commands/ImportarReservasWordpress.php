<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportarReservasWordpress extends Command
{
    protected $signature = 'reservas:sincronizar';
    protected $description = 'Sincroniza las reservas pendientes desde el WordPress con HBook';

    public function handle()
    {
        $token = 't4fVqA3ZhGr6xBNkL8p2qR5We7yCm0TDj1oUvzMi9skgXNHaEYbcJlPwGtSdOQuV';
        $baseUrl = 'https://apartamentosalgeciras.com/wp-json/crm/v1';
        $getEndpoint = "{$baseUrl}/reservas-pendientes?token={$token}";

        $response = Http::get($getEndpoint);

        if (!$response->successful()) {
            Log::error("❌ Error al obtener reservas pendientes: " . $response->status());
            return;
        }

        $data = $response->json();

        if (!isset($data['reservas']) || empty($data['reservas'])) {
            Log::info("🔍 No hay reservas pendientes.");
            return;
        }

        foreach ($data['reservas'] as $reserva) {
            try {
                // ENVÍA LA RESERVA A TU BACKEND PARA GUARDARLA
                $envio = Http::post(route('reservas.agregarReserva'), $reserva);

                if ($envio->successful()) {
                    Log::info("✅ Reserva añadida correctamente: " . $reserva['codigo_reserva']);

                    // MARCA COMO ENVIADA EN WORDPRESS
                    $marcar = Http::post("{$baseUrl}/marcar-enviada?token={$token}", [
                        'codigo' => $reserva['codigo_reserva']
                    ]);

                    if ($marcar->successful()) {
                        Log::info("📌 Reserva marcada como enviada: " . $reserva['codigo_reserva']);
                    } else {
                        Log::warning("⚠️ No se pudo marcar como enviada la reserva " . $reserva['codigo_reserva']);
                    }

                } else {
                    Log::warning("⚠️ Error al guardar la reserva " . $reserva['codigo_reserva'] . ": " . $envio->status());
                }
            } catch (\Throwable $e) {
                Log::error("❌ Excepción al enviar reserva " . $reserva['codigo_reserva'] . ": " . $e->getMessage());
            }
        }
    }
}
