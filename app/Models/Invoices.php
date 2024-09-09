<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoices extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'invoices';

    protected $fillable = [
        'budget_id',
        'cliente_id',
        'reserva_id',
        'invoice_status_id ',
        'concepto',
        'description',
        'fecha',
        'fecha_cobro',
        'base',
        'iva',
        'descuento',
        'total',

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
