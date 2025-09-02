<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemChecklistZonaComun extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'item_checklist_zona_comuns';
    
    protected $fillable = [
        'checklist_id',
        'nombre',
        'descripcion',
        'categoria',
        'activo',
        'orden'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer'
    ];

    // Relación con el checklist padre
    public function checklist()
    {
        return $this->belongsTo(ChecklistZonaComun::class, 'checklist_id');
    }

    // Scope para items activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Scope para ordenar
    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden', 'asc')->orderBy('nombre', 'asc');
    }
}
