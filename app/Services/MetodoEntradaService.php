<?php

namespace App\Services;

use App\Models\Edificio;
use App\Models\Reserva;

class MetodoEntradaService
{
    public const METODO_FISICA = 'fisica';
    public const METODO_DIGITAL = 'digital';

    /**
     * Resolver método de entrada final para una reserva.
     * Solo usa el valor guardado en edificio.metodo_entrada.
     * Por defecto siempre física (comportamiento actual); solo cambia si en configuración se elige "digital".
     */
    public function resolverParaReserva(?Reserva $reserva): string
    {
        $edificio = $reserva?->apartamento?->edificio ?? $reserva?->apartamento?->edificioName;

        return $this->resolverParaEdificio($edificio);
    }

    public function resolverParaEdificio(?Edificio $edificio): string
    {
        $metodo = strtolower(trim((string) ($edificio?->metodo_entrada ?? '')));
        if ($metodo === self::METODO_DIGITAL) {
            return self::METODO_DIGITAL;
        }

        return self::METODO_FISICA;
    }
}

