<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reserva;
use App\Models\MensajeAuto;
use App\Services\ClienteService;
use App\Http\Controllers\WebhookController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EnviarClavesChannexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ari:enviar-claves-channex';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía las claves del apartamento por Channex a reservas que ya recibieron las claves por WhatsApp/Email pero no por Channex';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔑 Iniciando envío de claves por Channex...');

        $clienteService = new ClienteService();

        // Buscar reservas que:
        // 1. No sean de origen 'web'
        // 2. Tengan id_channex (están en Channex)
        // 3. Ya tengan el mensaje de claves enviado (por WhatsApp/Email)
        // 4. No hayan recibido el mensaje de claves por Channex aún
        $reservas = Reserva::where('origen', '!=', 'web')
            ->whereNotNull('id_channex')
            ->with(['cliente', 'apartamento.edificioName'])
            ->get();

        $enviadas = 0;
        $errores = 0;
        $omitidas = 0;

        foreach ($reservas as $reserva) {
            try {
                // Verificar si ya se envió el mensaje de claves (por WhatsApp/Email)
                $mensajeClaves = MensajeAuto::where('reserva_id', $reserva->id)
                    ->where('tipo', 'claves')
                    ->first();

                if (!$mensajeClaves) {
                    // No se ha enviado el mensaje de claves aún, omitir
                    $omitidas++;
                    continue;
                }

                // Verificar si ya se envió por Channex (podríamos agregar un campo para esto, pero por ahora verificamos si existe)
                // Por simplicidad, asumimos que si el mensaje de claves existe, debemos enviarlo por Channex si no es de web

                // Obtener idioma del cliente
                $idiomaCliente = $clienteService->idiomaCodigo($reserva->cliente->nacionalidad ?? 'ES');

                // Preparar datos para el mensaje
                $apartamentoReservado = $reserva->apartamento;
                if (!$apartamentoReservado) {
                    Log::warning('Reserva sin apartamento para envío de claves Channex', [
                        'reserva_id' => $reserva->id
                    ]);
                    $errores++;
                    continue;
                }

                $datosClaves = [
                    'nombre' => $reserva->cliente->nombre ?? $reserva->cliente->alias,
                    'apartamento' => $apartamentoReservado->titulo,
                    'claveEntrada' => $apartamentoReservado->edificioName->clave ?? 'N/A',
                    'clavePiso' => $apartamentoReservado->claves ?? 'N/A',
                    'url' => $apartamentoReservado->edificio == 1 
                        ? 'https://goo.gl/maps/qb7AxP1JAxx5yg3N9' 
                        : 'https://maps.app.goo.gl/t81tgLXnNYxKFGW4A'
                ];

                // Crear mensaje de chat
                $mensajeChat = WebhookController::crearMensajeChat('claves', $datosClaves, $idiomaCliente);

                // Enviar a Channex
                $resultado = WebhookController::enviarMensajeAutomaticoAChannex(
                    $mensajeChat,
                    $reserva->id_channex
                );

                if ($resultado) {
                    $enviadas++;
                    $this->info("✅ Claves enviadas a Channex para reserva #{$reserva->id} (Código: {$reserva->codigo_reserva})");
                    Log::info('Claves enviadas por Channex desde comando', [
                        'reserva_id' => $reserva->id,
                        'codigo_reserva' => $reserva->codigo_reserva,
                        'id_channex' => $reserva->id_channex
                    ]);
                } else {
                    $errores++;
                    $this->error("❌ Error al enviar claves a Channex para reserva #{$reserva->id}");
                    Log::error('Error al enviar claves por Channex desde comando', [
                        'reserva_id' => $reserva->id,
                        'codigo_reserva' => $reserva->codigo_reserva,
                        'id_channex' => $reserva->id_channex
                    ]);
                }

            } catch (\Exception $e) {
                $errores++;
                $this->error("❌ Excepción al procesar reserva #{$reserva->id}: " . $e->getMessage());
                Log::error('Excepción al enviar claves por Channex', [
                    'reserva_id' => $reserva->id ?? null,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("\n📊 Resumen:");
        $this->info("   ✅ Enviadas: {$enviadas}");
        $this->info("   ⏭️  Omitidas: {$omitidas}");
        $this->info("   ❌ Errores: {$errores}");

        return Command::SUCCESS;
    }
}

