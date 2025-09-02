<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Incidencia extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'titulo',
        'descripcion',
        'tipo',
        'apartamento_id',
        'zona_comun_id',
        'empleada_id',
        'apartamento_limpieza_id',
        'prioridad',
        'estado',
        'fotos',
        'solucion',
        'admin_resuelve_id',
        'fecha_resolucion',
        'observaciones_admin'
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fotos' => 'array',
        'fecha_resolucion' => 'datetime',
        'consentimiento_finalizacion' => 'boolean',
    ];

    /**
     * Los atributos que pueden ser nulos.
     *
     * @var array
     */
    protected $nullable = [
        'apartamento_id',
        'zona_comun_id',
        'apartamento_limpieza_id',
        'fotos',
        'solucion',
        'admin_resuelve_id',
        'fecha_resolucion',
        'observaciones_admin'
    ];

    /**
     * Mutaciones de fecha.
     *
     * @var array
     */
    protected $dates = [
        'created_at', 'updated_at', 'deleted_at', 'fecha_resolucion'
    ];

    /**
     * Relación con Apartamento
     */
    public function apartamento()
    {
        return $this->belongsTo(Apartamento::class, 'apartamento_id');
    }

    /**
     * Relación con Zona Común
     */
    public function zonaComun()
    {
        return $this->belongsTo(ZonaComun::class, 'zona_comun_id');
    }

    /**
     * Relación con Empleada (quien reporta)
     */
    public function empleada()
    {
        return $this->belongsTo(User::class, 'empleada_id');
    }

    /**
     * Relación con Limpieza
     */
    public function limpieza()
    {
        return $this->belongsTo(ApartamentoLimpieza::class, 'apartamento_limpieza_id');
    }

    /**
     * Relación con Admin que resuelve
     */
    public function adminResuelve()
    {
        return $this->belongsTo(User::class, 'admin_resuelve_id');
    }

    /**
     * Scope para incidencias pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    /**
     * Scope para incidencias urgentes
     */
    public function scopeUrgentes($query)
    {
        return $query->where('prioridad', 'urgente');
    }

    /**
     * Scope para incidencias de hoy
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Obtener el elemento asociado (apartamento o zona común)
     */
    public function getElementoAttribute()
    {
        if ($this->apartamento) {
            return $this->apartamento;
        }
        return $this->zonaComun;
    }

    /**
     * Obtener el nombre del elemento
     */
    public function getNombreElementoAttribute()
    {
        if ($this->apartamento) {
            return $this->apartamento->nombre;
        }
        if ($this->zonaComun) {
            return $this->zonaComun->nombre;
        }
        return 'N/A';
    }

    /**
     * Obtener el tipo de elemento formateado
     */
    public function getTipoElementoAttribute()
    {
        if ($this->apartamento) {
            return 'Apartamento';
        }
        if ($this->zonaComun) {
            return 'Zona Común';
        }
        return 'N/A';
    }

    /**
     * Verificar si la incidencia es urgente
     */
    public function getEsUrgenteAttribute()
    {
        return $this->prioridad === 'urgente';
    }

    /**
     * Verificar si la incidencia está pendiente
     */
    public function getEstaPendienteAttribute()
    {
        return $this->estado === 'pendiente';
    }
}
