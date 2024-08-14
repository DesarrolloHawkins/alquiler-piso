<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiarioCaja extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'diario_caja';

    /**
     * Atributos asignados en masa.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'gasto_id',
        'ingreso_id',
        'asiento_contable',
        'tipo',
        'cuenta_id',
        'date',
        'concepto',
        'debe',
        'haber',
        'formas_pago',
        
    ];

    /**
     * Mutaciones de fecha.
     *
     * @var array
     */
    protected $dates = [
        'created_at', 'updated_at', 'deleted_at', 
    ];
}
