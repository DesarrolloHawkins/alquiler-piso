<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Huesped extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'huespedes';

      /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reserva_id',
        'nombre',
        'primer_apellido',
        'segundo_apellido',
        'fecha_nacimiento',
        'pais',
        'tipo_documento',
        'tipo_documento_str',
        'numero_identificacion',
        'fecha_expedicion',
        'sexo',
        'sexo_str',
        'email',
        'contador',
        'nacionalidadStr',
        'nacionalidadCode',
        'nacionalidad'
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
