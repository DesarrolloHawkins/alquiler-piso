<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemChecklist extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'items_checklists';

    protected $fillable = ['nombre', 'checklist_id'];

    public function controles()
    {
        return $this->hasMany(ControlLimpieza::class);
    }
    public function apartamentos()
    {
        return $this->belongsToMany(ApartamentoLimpieza::class, 'apartamento_item_checklist')
                    ->withPivot('status')
                    ->withTimestamps();
    }

}
