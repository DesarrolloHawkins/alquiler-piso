<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fichaje extends Model
{
    protected $fillable = ['user_id', 'hora_entrada', 'hora_salida'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pausas()
    {
        return $this->hasMany(Pausa::class);
    }
}
