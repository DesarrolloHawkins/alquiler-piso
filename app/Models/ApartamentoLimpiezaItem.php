<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApartamentoLimpiezaItem extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'apartamento_limpieza_items';

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_limpieza',
        'id_reserva',
        'item_id',
        'estado',
        'checklist_id',
        'photo_url',
        'photo_cat' // Categoría de la foto
    ];

    /**
     * Mutaciones de fecha.
     *
     * @var array
     */
    protected $dates = [
        'created_at', 'updated_at', 'deleted_at',
    ];

    /**
     * Get the checklist that owns the item.
     */
    public function checklist()
    {
        return $this->belongsTo(Checklist::class, 'checklist_id');
    }
}
