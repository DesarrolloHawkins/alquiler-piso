<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PresupuestoConcepto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'presupuesto_id',
        'concepto',
        'fecha_entrada',
        'fecha_salida',
        'precio_por_dia',
        'dias_totales',
        'precio_total',
    ];

    public function presupuesto()
    {
        return $this->belongsTo(Presupuesto::class, 'presupuesto_id');
    }

    /**
     * Calcula el total en base al precio por día y los días totales.
     */
    public function calcularPrecioTotal()
    {
        $this->dias_totales = $this->calcularDiasTotales();
        $this->precio_total = $this->dias_totales * $this->precio_por_dia;
    }

    /**
     * Calcula la cantidad de días entre la fecha de entrada y salida.
     */
    public function calcularDiasTotales()
    {
        $fechaEntrada = \Carbon\Carbon::parse($this->fecha_entrada);
        $fechaSalida = \Carbon\Carbon::parse($this->fecha_salida);

        return $fechaEntrada->diffInDays($fechaSalida);
    }
}
