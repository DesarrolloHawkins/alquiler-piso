<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Models\Pago;

class Cupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cupones';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'tipo',
        'valor',
        'descuento_maximo',
        'importe_minimo',
        'fecha_inicio',
        'fecha_fin',
        'usos_maximos',
        'usos_actuales',
        'usos_por_cliente',
        'activo',
        'apartamentos_ids',
        'restricciones',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'descuento_maximo' => 'decimal:2',
        'importe_minimo' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'usos_maximos' => 'integer',
        'usos_actuales' => 'integer',
        'usos_por_cliente' => 'integer',
        'activo' => 'boolean',
        'apartamentos_ids' => 'array',
        'restricciones' => 'array',
    ];

    /**
     * Validar si el cupón es válido para una reserva
     */
    public function esValido($importeTotal = null, $apartamentoId = null, $clienteId = null)
    {
        // Verificar si está activo
        if (!$this->activo) {
            return ['valido' => false, 'mensaje' => 'El cupón no está activo'];
        }

        // Verificar fechas
        $hoy = Carbon::today();
        if ($hoy->lt($this->fecha_inicio) || $hoy->gt($this->fecha_fin)) {
            return ['valido' => false, 'mensaje' => 'El cupón no está vigente en estas fechas'];
        }

        // Verificar usos máximos
        if ($this->usos_maximos !== null && $this->usos_actuales >= $this->usos_maximos) {
            return ['valido' => false, 'mensaje' => 'El cupón ha alcanzado su límite de usos'];
        }

        // Verificar importe mínimo
        if ($importeTotal !== null && $this->importe_minimo !== null && $importeTotal < $this->importe_minimo) {
            return ['valido' => false, 'mensaje' => "El importe mínimo para usar este cupón es {$this->importe_minimo}€"];
        }

        // Verificar apartamento específico
        if ($apartamentoId !== null && $this->apartamentos_ids !== null && !in_array($apartamentoId, $this->apartamentos_ids)) {
            return ['valido' => false, 'mensaje' => 'Este cupón no es válido para este apartamento'];
        }

        // Verificar usos por cliente
        if ($clienteId !== null && $this->usos_por_cliente > 0) {
            $usosCliente = Pago::where('cliente_id', $clienteId)
                ->where('cupon_id', $this->id)
                ->where('estado', 'completado')
                ->count();
            
            if ($usosCliente >= $this->usos_por_cliente) {
                return ['valido' => false, 'mensaje' => 'Has alcanzado el límite de usos de este cupón'];
            }
        }

        return ['valido' => true, 'mensaje' => 'Cupón válido'];
    }

    /**
     * Calcular el descuento aplicado
     */
    public function calcularDescuento($importeTotal)
    {
        if ($this->tipo === 'porcentaje') {
            $descuento = ($importeTotal * $this->valor) / 100;
            
            // Aplicar descuento máximo si existe
            if ($this->descuento_maximo !== null && $descuento > $this->descuento_maximo) {
                $descuento = $this->descuento_maximo;
            }
            
            return round($descuento, 2);
        } else {
            // Descuento fijo
            return min($this->valor, $importeTotal); // No puede ser mayor que el importe total
        }
    }

    /**
     * Incrementar contador de usos
     */
    public function incrementarUso()
    {
        $this->increment('usos_actuales');
    }

    /**
     * Relación con pagos
     */
    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }
}
