<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Apartamento extends Model
{
    use HasFactory, SoftDeletes;
      /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'id_booking',
        'id_airbnb',
        'id_web',
        'titulo',
        'claves',
        // 'edificio',
        'edificio_id',
        'id_channex',
        'currency',
        'country',
        'state',
        'city',
        'address',
        'zip_code',
        'latitude',
        'longitude',
        'timezone',
        'property_type',
        'description',
        'important_information',
        'email',
        'phone',
        'website',
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
     * Obtener el usuario
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function edificioName()
    {
        return $this->belongsTo(\App\Models\Edificio::class,'edificio_id');
    }
    public function edificioRelacion()
    {
        return $this->belongsTo(\App\Models\Edificio::class, 'edificio_id');
    }
     /**
     * Relación con las fotos del apartamento.
     */
    public function photos()
    {
        return $this->hasMany(ApartamentoPhoto::class);
    }
    public function roomTypes()
    {
        return $this->hasMany(RoomType::class, 'property_id', 'id');
    }
    
    public function ratePlans()
    {
        return $this->hasMany(RatePlan::class, 'property_id', 'id');
    }
    
    public function edificio()
    {
        return $this->belongsTo(\App\Models\Edificio::class, 'edificio_id');
    }

    /**
     * Relación con tarifas
     */
    public function tarifas()
    {
        return $this->belongsToMany(Tarifa::class, 'apartamento_tarifa')
                    ->withPivot('activo')
                    ->withTimestamps();
    }

    /**
     * Obtener tarifas activas
     */
    public function tarifasActivas()
    {
        return $this->tarifas()->wherePivot('activo', true);
    }

    /**
     * Obtener tarifa vigente para una fecha específica
     */
    public function tarifaVigente($fecha)
    {
        return $this->tarifas()
                    ->wherePivot('activo', true)
                    ->where('activo', true)
                    ->where('fecha_inicio', '<=', $fecha)
                    ->where('fecha_fin', '>=', $fecha)
                    ->first();
    }
}
