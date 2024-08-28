<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'edificio_id'];

    public function items()
    {
        return $this->hasMany(ItemChecklist::class);
    }
    
    public function edificio()
    {
        return $this->belongsTo(Edificio::class, 'edificio_id');
    }
}
