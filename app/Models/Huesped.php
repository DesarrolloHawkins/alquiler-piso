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
        'numero_soporte_documento', // Nuevo campo
        'fecha_expedicion',
        'sexo',
        'sexo_str',
        'email',
        'telefono_movil', // Nuevo campo
        'direccion',
        'localidad',
        'codigo_postal',
        'provincia',
        'relacion_parentesco', // Nuevo campo
        'numero_referencia_contrato', // Nuevo campo
        'fecha_firma_contrato', // Nuevo campo
        'fecha_hora_entrada', // Nuevo campo
        'fecha_hora_salida', // Nuevo campo
        'numero_habitaciones', // Nuevo campo
        'conexion_internet', // Nuevo campo
        'tipo_pago', // Nuevo campo
        'identificacion_medio_pago', // Nuevo campo
        'titular_medio_pago', // Nuevo campo
        'fecha_caducidad_tarjeta', // Nuevo campo
        'fecha_pago', // Nuevo campo
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
