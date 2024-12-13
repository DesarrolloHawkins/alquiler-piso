<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApartamentoPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'apartamento_id',
        'url',
        'position',
        'author',
        'kind',
        'description',
    ];

    /**
     * Relación con el apartamento.
     */
    public function apartamento()
    {
        return $this->belongsTo(Apartamento::class);
    }
}
