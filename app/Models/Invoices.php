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
        'invoice_status_id',
        'concepto',
        'description',
        'fecha',
        'fecha_cobro',
        'base',
        'iva',
        'descuento',
        'total'
    ];

    /**
     * Mutaciones de fecha.
     *
     * @var array
     */
    protected $dates = [
        'created_at', 'updated_at', 'deleted_at', 
    ];
    
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
     // Relación con el modelo Client (Cliente)
     public function client()
     {
         return $this->belongsTo(Cliente::class, 'cliente_id'); // cliente_id es la clave foránea en la tabla invoices
     }
     // Relación con el modelo Client (Cliente)
     public function estado()
     {
         return $this->belongsTo(InvoicesStatus::class, 'invoice_status_id'); // cliente_id es la clave foránea en la tabla invoices
     }
     public function reserva()
     {
         return $this->belongsTo(Reserva::class, 'reserva_id');
     }
     
}
