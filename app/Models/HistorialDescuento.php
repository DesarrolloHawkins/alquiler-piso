<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialDescuento extends Model
{
    use HasFactory;

    protected $fillable = [
        'apartamento_id',
        'tarifa_id',
        'configuracion_descuento_id',
        'fecha_aplicacion',
        'fecha_inicio_descuento',
        'fecha_fin_descuento',
        'precio_original',
        'precio_con_descuento',
        'porcentaje_descuento',
        'dias_aplicados',
        'ahorro_total',
        'estado',
        'observaciones',
        'datos_channex'
    ];

    protected $casts = [
        'fecha_aplicacion' => 'date',
        'fecha_inicio_descuento' => 'date',
        'fecha_fin_descuento' => 'date',
        'precio_original' => 'decimal:2',
        'precio_con_descuento' => 'decimal:2',
        'porcentaje_descuento' => 'decimal:2',
        'dias_aplicados' => 'integer',
        'ahorro_total' => 'decimal:2',
        'datos_channex' => 'array'
    ];

    /**
     * Relación con apartamento
     */
    public function apartamento()
    {
        return $this->belongsTo(Apartamento::class);
    }

    /**
     * Relación con tarifa
     */
    public function tarifa()
    {
        return $this->belongsTo(Tarifa::class);
    }

    /**
     * Relación con configuración de descuento
     */
    public function configuracionDescuento()
    {
        return $this->belongsTo(ConfiguracionDescuento::class);
    }

    /**
     * Scope para descuentos pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    /**
     * Scope para descuentos aplicados
     */
    public function scopeAplicados($query)
    {
        return $query->where('estado', 'aplicado');
    }

    /**
     * Scope para descuentos por fecha
     */
    public function scopePorFecha($query, $fecha)
    {
        return $query->where('fecha_aplicacion', $fecha);
    }

    /**
     * Obtener el estado formateado
     */
    public function getEstadoFormateadoAttribute()
    {
        $estados = [
            'pendiente' => 'Pendiente',
            'aplicado' => 'Aplicado',
            'revertido' => 'Revertido',
            'error' => 'Error'
        ];

        return $estados[$this->estado] ?? $this->estado;
    }

    /**
     * Obtener el porcentaje formateado
     */
    public function getPorcentajeFormateadoAttribute()
    {
        return $this->porcentaje_descuento . '%';
    }

    /**
     * Obtener el rango de fechas formateado
     */
    public function getRangoFechasAttribute()
    {
        return $this->fecha_inicio_descuento->format('d/m/Y') . ' - ' . $this->fecha_fin_descuento->format('d/m/Y');
    }
}
