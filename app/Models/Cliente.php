<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

      /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'apellido1',
        'apellido2',
        'nacionalidad',
        'tipo_documento',
        'tipo_documento_str',
        'num_identificacion',
        'fecha_expedicion_doc',
        'fecha_nacimiento',
        'sexo',
        'sexo_str',
        'telefono',
        'email',
        'identificador',
        'idiomas',
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
