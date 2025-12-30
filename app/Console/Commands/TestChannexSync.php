<?php

namespace App\Console\Commands;

use App\Models\Reserva;
use App\Models\Apartamento;
use App\Models\RoomType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TestChannexSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:channex-sync 
                            {--reserva_id= : ID de la reserva a probar}
                            {--apartamento_id= : ID del apartamento a probar}
                            {--fecha_entrada= : Fecha de entrada (Y-m-d)}
                            {--fecha_salida= : Fecha de salida (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la sincronización de reservas web con Channex';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Iniciando prueba de sincronización con Channex...');
        $this->newLine();

        // Verificar configuración
        $apiUrl = env('CHANNEX_URL', 'https://app.channex.io/api/v1');
        $apiToken = env('CHANNEX_TOKEN');

        if (!$apiToken) {
            $this->error('❌ CHANNEX_TOKEN no está configurado en .env');
            return 1;
        }

        $this->info("✅ API URL: {$apiUrl}");
        $this->info("✅ API Token: " . substr($apiToken, 0, 10) . '...');
        $this->newLine();

        // Obtener datos de prueba
        $reservaId = $this->option('reserva_id');
        $apartamentoId = $this->option('apartamento_id');
        $fechaEntrada = $this->option('fecha_entrada');
        $fechaSalida = $this->option('fecha_salida');

        if ($reservaId) {
            // Probar con una reserva existente
            $reserva = Reserva::find($reservaId);
            if (!$reserva) {
                $this->error("❌ Reserva #{$reservaId} no encontrada");
                return 1;
            }

            $this->info("📋 Probando con Reserva #{$reservaId}");
            $this->info("   Código: {$reserva->codigo_reserva}");
            $this->info("   Origen: {$reserva->origen}");
            $this->info("   Fechas: {$reserva->fecha_entrada} - {$reserva->fecha_salida}");
            $this->newLine();

            $apartamento = $reserva->apartamento;
            $roomType = RoomType::find($reserva->room_type_id);
            $startDate = Carbon::parse($reserva->fecha_entrada);
            $endDate = Carbon::parse($reserva->fecha_salida)->subDay();
        } elseif ($apartamentoId && $fechaEntrada && $fechaSalida) {
            // Probar con datos manuales
            $apartamento = Apartamento::find($apartamentoId);
            if (!$apartamento) {
                $this->error("❌ Apartamento #{$apartamentoId} no encontrado");
                return 1;
            }

            $roomType = RoomType::where('property_id', $apartamento->id)->first();
            if (!$roomType) {
                $this->error("❌ No se encontró RoomType para el apartamento #{$apartamentoId}");
                return 1;
            }

            $startDate = Carbon::parse($fechaEntrada);
            $endDate = Carbon::parse($fechaSalida)->subDay();

            $this->info("📋 Probando con Apartamento #{$apartamentoId}");
            $this->info("   Nombre: {$apartamento->nombre}");
            $this->info("   Fechas: {$fechaEntrada} - {$fechaSalida}");
            $this->newLine();
        } else {
            // Buscar la última reserva web
            $reserva = Reserva::where('origen', 'Web')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$reserva) {
                $this->error('❌ No se encontraron reservas web para probar');
                $this->info('💡 Usa: php artisan test:channex-sync --reserva_id=ID');
                return 1;
            }

            $this->info("📋 Usando última reserva web: #{$reserva->id}");
            $this->info("   Código: {$reserva->codigo_reserva}");
            $this->info("   Fechas: {$reserva->fecha_entrada} - {$reserva->fecha_salida}");
            $this->newLine();

            $apartamento = $reserva->apartamento;
            $roomType = RoomType::find($reserva->room_type_id);
            $startDate = Carbon::parse($reserva->fecha_entrada);
            $endDate = Carbon::parse($reserva->fecha_salida)->subDay();
        }

        // Validar datos necesarios
        if (!$apartamento || !$apartamento->id_channex) {
            $this->error("❌ El apartamento no tiene id_channex configurado");
            return 1;
        }

        if (!$roomType || !$roomType->id_channex) {
            $this->error("❌ El RoomType no tiene id_channex configurado");
            return 1;
        }

        $this->info("📊 Datos de sincronización:");
        $this->table(
            ['Campo', 'Valor'],
            [
                ['Apartamento ID', $apartamento->id],
                ['Apartamento Channex ID', $apartamento->id_channex],
                ['RoomType ID', $roomType->id],
                ['RoomType Channex ID', $roomType->id_channex],
                ['Fecha desde', $startDate->toDateString()],
                ['Fecha hasta', $endDate->toDateString()],
            ]
        );
        $this->newLine();

        // Preparar payload
        $update = [
            'property_id' => $apartamento->id_channex,
            'room_type_id' => $roomType->id_channex,
            'date_from' => $startDate->toDateString(),
            'date_to' => $endDate->toDateString(),
            'update_type' => 'availability',
            'availability' => 0, // Bloqueamos la disponibilidad
        ];

        $this->info("📤 Enviando petición a Channex...");
        $this->info("   Endpoint: {$apiUrl}/availability");
        $this->info("   Payload: " . json_encode(['values' => [$update]], JSON_PRETTY_PRINT));
        $this->newLine();

        // Enviar petición
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'user-api-key' => $apiToken,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$apiUrl}/availability", ['values' => [$update]]);

            if ($response->successful()) {
                $responseData = $response->json();
                $this->info('✅ ¡Sincronización exitosa!');
                $this->newLine();
                $this->info('📥 Respuesta de Channex:');
                $this->line(json_encode($responseData, JSON_PRETTY_PRINT));
                return 0;
            } else {
                $this->error('❌ Error en la sincronización');
                $this->newLine();
                $this->error("HTTP Status: {$response->status()}");
                $this->error("Respuesta: " . $response->body());
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Excepción al sincronizar:');
            $this->error($e->getMessage());
            $this->newLine();
            $this->error('Stack trace:');
            $this->line($e->getTraceAsString());
            return 1;
        }
    }
}

