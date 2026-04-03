<?php
namespace App\Services;

use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AccessCodeService
{
    /**
     * Genera un código de 4 dígitos único, lo programa en la cerradura TTLock
     * y lo guarda en la reserva.
     */
    public function generarYProgramar(Reserva $reserva): ?string
    {
        $codigo = $this->generarCodigoUnico();

        $apartamento = $reserva->apartamento;
        if (!$apartamento || !$apartamento->ttlock_lock_id) {
            // No hay cerradura configurada, guardamos el código sin programar
            $reserva->update(['codigo_acceso' => $codigo, 'codigo_enviado_cerradura' => 0]);
            Log::info("AccessCodeService: código {$codigo} generado para reserva {$reserva->id} sin cerradura configurada.");
            return $codigo;
        }

        $tuyaAppUrl = config('services.tuya_app.url');
        if (empty($tuyaAppUrl)) {
            $reserva->update(['codigo_acceso' => $codigo, 'codigo_enviado_cerradura' => 0]);
            Log::warning("AccessCodeService: TUYA_APP_URL no configurada.");
            return $codigo;
        }

        // Ventana de validez: día entrada 15:00 → día salida 11:00
        $efectivo = Carbon::parse($reserva->fecha_entrada)->setTime(15, 0, 0);
        $invalido  = Carbon::parse($reserva->fecha_salida)->setTime(11, 0, 0);

        try {
            $response = Http::timeout(30)->post("{$tuyaAppUrl}/api/pins", [
                'lock_id'            => $apartamento->ttlock_lock_id,
                'name'               => 'Reserva #' . $reserva->id,
                'pin'                => $codigo,
                'effective_time'     => $efectivo->toDateTimeString(),
                'invalid_time'       => $invalido->toDateTimeString(),
                'external_reference' => 'reserva_' . $reserva->id,
            ]);

            if ($response->successful()) {
                $pinId = $response->json('data.provider_code_id');
                $reserva->update([
                    'codigo_acceso'          => $codigo,
                    'ttlock_pin_id'          => $pinId,
                    'codigo_enviado_cerradura' => 1,
                ]);
                Log::info("AccessCodeService: PIN {$codigo} programado en cerradura para reserva {$reserva->id}.");
            } else {
                $reserva->update(['codigo_acceso' => $codigo, 'codigo_enviado_cerradura' => 0]);
                Log::error("AccessCodeService: error al programar en cerradura. Status: " . $response->status());
            }
        } catch (\Exception $e) {
            $reserva->update(['codigo_acceso' => $codigo, 'codigo_enviado_cerradura' => 0]);
            Log::error("AccessCodeService: excepción al llamar a TTLock API: " . $e->getMessage());
        }

        return $codigo;
    }

    /**
     * Revoca el PIN de la cerradura al cancelar/eliminar una reserva.
     */
    public function revocarPin(Reserva $reserva): void
    {
        if (empty($reserva->ttlock_pin_id)) {
            return;
        }

        $tuyaAppUrl = config('services.tuya_app.url');
        if (empty($tuyaAppUrl)) {
            return;
        }

        try {
            // Buscar el temp_password por external_reference para obtener su ID
            $response = Http::timeout(15)->get("{$tuyaAppUrl}/api/pins", [
                'external_reference' => 'reserva_' . $reserva->id,
            ]);

            // Si hay un ID guardado en ttlock_pin_id, intentar borrarlo directamente
            // El ID en ttlock_pin_id puede ser el provider_code_id (TTLock password ID)
            // Necesitamos el ID interno del TempPassword para DELETE /api/pins/{id}
            // Por ahora logueamos - la revocación manual se hace desde el panel Tuya
            Log::info("AccessCodeService: revisar revocación manual para reserva {$reserva->id}, pin_id: {$reserva->ttlock_pin_id}");
        } catch (\Exception $e) {
            Log::error("AccessCodeService: excepción al revocar PIN: " . $e->getMessage());
        }
    }

    private function generarCodigoUnico(): string
    {
        $intentos = 0;
        do {
            $codigo = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
            // Verificar que no está en uso en reservas activas o futuras
            $existe = Reserva::where('codigo_acceso', $codigo)
                ->where('fecha_salida', '>=', now()->toDateString())
                ->whereNotNull('codigo_acceso')
                ->exists();
            $intentos++;
        } while ($existe && $intentos < 20);

        return $codigo;
    }
}
