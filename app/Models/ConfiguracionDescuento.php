<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionDescuento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'porcentaje_descuento',
        'activo',
        'condiciones'
    ];

    protected $casts = [
        'porcentaje_descuento' => 'decimal:2',
        'activo' => 'boolean',
        'condiciones' => 'array'
    ];

    /**
     * Relación con historial de descuentos
     */
    public function historialDescuentos()
    {
        return $this->hasMany(HistorialDescuento::class);
    }

    /**
     * Scope para configuraciones activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Obtener el porcentaje de descuento formateado
     */
    public function getPorcentajeFormateadoAttribute()
    {
        return $this->porcentaje_descuento . '%';
    }

    /**
     * Calcular precio con descuento
     */
    public function calcularPrecioConDescuento($precioOriginal)
    {
        $factorDescuento = (100 - $this->porcentaje_descuento) / 100;
        return $precioOriginal * $factorDescuento;
    }

    /**
     * Calcular ahorro por día
     */
    public function calcularAhorroPorDia($precioOriginal)
    {
        return $precioOriginal - $this->calcularPrecioConDescuento($precioOriginal);
    }
}
