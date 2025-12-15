<?php

namespace App\Services;

use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReservationOverlapService
{
    /**
     * Detecta solapes por apartamento en el rango indicado.
     */
    public function detect(Carbon $from, Carbon $to): Collection
    {
        $reservas = Reserva::query()
            ->activas()
            ->whereNull('deleted_at')
            ->where(function ($query) use ($from, $to) {
                $query
                    ->whereBetween('fecha_entrada', [$from->toDateString(), $to->toDateString()])
                    ->orWhereBetween('fecha_salida', [$from->toDateString(), $to->toDateString()])
                    ->orWhere(function ($q) use ($from, $to) {
                        $q->where('fecha_entrada', '<=', $from->toDateString())
                            ->where('fecha_salida', '>=', $to->toDateString());
                    });
            })
            ->with('apartamento')
            ->orderBy('apartamento_id')
            ->orderBy('fecha_entrada')
            ->get()
            ->groupBy('apartamento_id');

        return $reservas->flatMap(function (Collection $reservasApartamento) {
            return $this->detectOverlapsForApartment($reservasApartamento);
        });
    }

    /**
     * Detecta grupos solapados dentro de un apartamento.
     */
    private function detectOverlapsForApartment(Collection $reservasApartamento): Collection
    {
        $reservasOrdenadas = $reservasApartamento->sortBy('fecha_entrada')->values();
        $conflictos = collect();

        if ($reservasOrdenadas->isEmpty()) {
            return $conflictos;
        }

        $primera = $reservasOrdenadas->first();
        if (!$primera->fecha_entrada || !$primera->fecha_salida) {
            return $conflictos;
        }

        $grupoActual = [$primera];
        $finGrupo = Carbon::parse($primera->fecha_salida);

        foreach ($reservasOrdenadas->skip(1) as $reserva) {
            if (!$reserva->fecha_entrada || !$reserva->fecha_salida) {
                continue;
            }

            $inicio = Carbon::parse($reserva->fecha_entrada);
            $fin = Carbon::parse($reserva->fecha_salida);

            // Solape si el inicio es anterior al fin vigente del grupo
            if ($inicio->lt($finGrupo)) {
                $grupoActual[] = $reserva;
                if ($fin->gt($finGrupo)) {
                    $finGrupo = $fin;
                }
                continue;
            }

            if (count($grupoActual) >= 2) {
                $conflictos->push($this->buildConflict($grupoActual));
            }

            $grupoActual = [$reserva];
            $finGrupo = $fin;
        }

        if (count($grupoActual) >= 2) {
            $conflictos->push($this->buildConflict($grupoActual));
        }

        return $conflictos;
    }

    private function buildConflict(array $reservasGrupo): array
    {
        $ids = collect($reservasGrupo)->pluck('id')->sort()->values()->all();
        $inicio = collect($reservasGrupo)->pluck('fecha_entrada')->min();
        $fin = collect($reservasGrupo)->pluck('fecha_salida')->max();
        $apartamento = $reservasGrupo[0]->apartamento;

        return [
            'apartamento_id' => $reservasGrupo[0]->apartamento_id,
            'apartamento_nombre' => $apartamento->nombre ?? ('Apartamento #' . $reservasGrupo[0]->apartamento_id),
            'reserva_ids' => $ids,
            'rango_inicio' => $inicio,
            'rango_fin' => $fin,
        ];
    }
}

