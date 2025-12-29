<?php

namespace App\Services;

use App\Models\Amenity;
use App\Models\Apartamento;
use App\Models\Reserva;
use Carbon\Carbon;

class AmenityConsumptionService
{
    /**
     * Calcula la cantidad recomendada para un amenity teniendo en cuenta
     * el tipo de consumo configurado y los datos de la reserva/apartamento.
     */
    public static function calculateRecommendedQuantity(
        Amenity $amenity,
        ?Reserva $reserva = null,
        ?Apartamento $apartamento = null
    ): float {
        $numeroPersonas = $reserva ? max(1, (int) $reserva->numero_personas) : 1;
        
        // Calcular días de reserva de forma más robusta
        $diasReserva = 1; // Valor por defecto
        if ($reserva && $reserva->fecha_entrada && $reserva->fecha_salida) {
            try {
                $fechaEntrada = Carbon::parse($reserva->fecha_entrada);
                $fechaSalida = Carbon::parse($reserva->fecha_salida);
                
                if ($fechaEntrada->isValid() && $fechaSalida->isValid() && $fechaSalida->gt($fechaEntrada)) {
                    $diasCalculados = $fechaEntrada->diffInDays($fechaSalida);
                    $diasReserva = max(1, $diasCalculados);
                }
            } catch (\Exception $e) {
                // Si hay error al parsear fechas, usar valor por defecto
                \Log::warning("Error calculando días de reserva para amenity: " . $e->getMessage());
            }
        }

        switch ($amenity->tipo_consumo) {
            case 'por_reserva':
                // Siempre usar consumo_por_reserva exactamente como está configurado
                $cantidad = $amenity->consumo_por_reserva ?? 0;
                if ($cantidad <= 0) {
                    $cantidad = 1;
                }
                return (float) $cantidad;

            case 'por_tiempo':
                if ($amenity->duracion_dias && $amenity->duracion_dias > 0) {
                    // Calcular cantidad basada en días de reserva dividido por duración del amenity
                    // Ejemplo: si duracion_dias = 7 y diasReserva = 10, entonces ceil(10/7) = 2
                    $cantidad = ceil($diasReserva / $amenity->duracion_dias);
                    $cantidad = max(1, $cantidad);

                    if ($amenity->consumo_maximo_reserva) {
                        $cantidad = min($cantidad, (float) $amenity->consumo_maximo_reserva);
                    }

                    \Log::info("Amenity por_tiempo calculado", [
                        'amenity_id' => $amenity->id,
                        'amenity_nombre' => $amenity->nombre,
                        'duracion_dias' => $amenity->duracion_dias,
                        'dias_reserva' => $diasReserva,
                        'cantidad_calculada' => $cantidad
                    ]);

                    return (float) $cantidad;
                }
                return 1.0;

            case 'por_persona':
                $cantidadPorPersonaPorDia = $amenity->consumo_por_persona ?? 1;
                $cantidad = $cantidadPorPersonaPorDia * $numeroPersonas * $diasReserva;

                if ($amenity->consumo_minimo_reserva) {
                    $cantidad = max($cantidad, $amenity->consumo_minimo_reserva);
                }
                if ($amenity->consumo_maximo_reserva) {
                    $cantidad = min($cantidad, $amenity->consumo_maximo_reserva);
                }

                return (float) ceil($cantidad);

            default:
                return 1.0;
        }
    }
}

