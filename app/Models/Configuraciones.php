<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Configuraciones extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'configuracion';

    /**
     * Atributos asignados en masa.
     *
     * @var array
     */
    protected $fillable = [
        'password_booking',
        'password_airbnb'
        
    ];
}
