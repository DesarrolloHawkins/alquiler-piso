<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TareaAsignada extends Model
{
    use HasFactory;

    protected $table = 'tareas_asignadas';

    protected $fillable = [
        'turno_id',
        'tipo_tarea_id',
        'apartamento_id',
        'zona_comun_id',
        'prioridad_calculada',
        'orden_ejecucion',
        'estado',
        'fecha_ultima_limpieza',
        'dias_sin_limpiar',
        'observaciones',
        'fecha_inicio_real',
        'fecha_fin_real',
        'tiempo_real_minutos'
    ];

    protected $casts = [
        'prioridad_calculada' => 'integer',
        'orden_ejecucion' => 'integer',
        'fecha_ultima_limpieza' => 'datetime',
        'dias_sin_limpiar' => 'integer',
        'fecha_inicio_real' => 'datetime',
        'fecha_fin_real' => 'datetime',
        'tiempo_real_minutos' => 'integer'
    ];

    // Relaciones
    public function turno()
    {
        return $this->belongsTo(TurnoTrabajo::class, 'turno_id');
    }

    public function tipoTarea()
    {
        return $this->belongsTo(TipoTarea::class, 'tipo_tarea_id');
    }

    public function apartamento()
    {
        return $this->belongsTo(Apartamento::class);
    }

    public function zonaComun()
    {
        return $this->belongsTo(ZonaComun::class);
    }

    // Scopes
    public function scopePorTurno($query, $turnoId)
    {
        return $query->where('turno_id', $turnoId);
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeEnProgreso($query)
    {
        return $query->where('estado', 'en_progreso');
    }

    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'completada');
    }

    public function scopePorPrioridad($query)
    {
        return $query->orderBy('prioridad_calculada', 'desc');
    }

    public function scopePorOrden($query)
    {
        return $query->orderBy('orden_ejecucion', 'asc');
    }

    public function scopeLimpiezaApartamentos($query)
    {
        return $query->whereHas('tipoTarea', function($q) {
            $q->where('categoria', 'limpieza_apartamento');
        });
    }

    public function scopeLimpiezaZonasComunes($query)
    {
        return $query->whereHas('tipoTarea', function($q) {
            $q->where('categoria', 'limpieza_zona_comun');
        });
    }

    // Métodos
    public function iniciarTarea()
    {
        $this->update([
            'estado' => 'en_progreso',
            'fecha_inicio_real' => now()
        ]);
    }

    public function completarTarea($observaciones = null)
    {
        $tiempoReal = $this->calcularTiempoReal();
        
        $this->update([
            'estado' => 'completada',
            'fecha_fin_real' => now(),
            'fecha_ultima_limpieza' => now(),
            'dias_sin_limpiar' => 0,
            'tiempo_real_minutos' => $tiempoReal,
            'observaciones' => $observaciones
        ]);
    }

    public function calcularTiempoReal()
    {
        if ($this->fecha_inicio_real && $this->fecha_fin_real) {
            $inicio = Carbon::parse($this->fecha_inicio_real);
            $fin = Carbon::parse($this->fecha_fin_real);
            return $fin->diffInMinutes($inicio);
        }
        
        return null;
    }

    public function actualizarPrioridad()
    {
        if ($this->fecha_ultima_limpieza) {
            $diasSinLimpiar = Carbon::parse($this->fecha_ultima_limpieza)->diffInDays(now());
            $this->dias_sin_limpiar = $diasSinLimpiar;
        } else {
            $this->dias_sin_limpiar = 0;
        }
        
        $prioridadCalculada = $this->tipoTarea->calcularPrioridad($this->dias_sin_limpiar);
        $this->prioridad_calculada = $prioridadCalculada;
        $this->save();
    }

    public function getElementoNombreAttribute()
    {
        if ($this->apartamento) {
            return $this->apartamento->titulo;
        } elseif ($this->zonaComun) {
            return $this->zonaComun->nombre;
        }
        
        return 'Sin elemento específico';
    }

    public function getTiempoEstimadoAttribute()
    {
        return $this->tipoTarea->tiempo_estimado_minutos;
    }

    public function getTiempoRealFormateadoAttribute()
    {
        if (!$this->tiempo_real_minutos) return 'No completada';
        
        $horas = floor($this->tiempo_real_minutos / 60);
        $minutos = $this->tiempo_real_minutos % 60;
        
        if ($horas > 0 && $minutos > 0) {
            return "{$horas}h {$minutos}m";
        } elseif ($horas > 0) {
            return "{$horas}h";
        } else {
            return "{$minutos}m";
        }
    }

    public function getTiempoEstimadoFormateadoAttribute()
    {
        $horas = floor($this->tiempo_estimado / 60);
        $minutos = $this->tiempo_estimado % 60;
        
        if ($horas > 0 && $minutos > 0) {
            return "{$horas}h {$minutos}m";
        } elseif ($horas > 0) {
            return "{$horas}h";
        } else {
            return "{$minutos}m";
        }
    }

    public function estaPendiente()
    {
        return $this->estado === 'pendiente';
    }

    public function estaEnProgreso()
    {
        return $this->estado === 'en_progreso';
    }

    public function estaCompletada()
    {
        return $this->estado === 'completada';
    }

    public function estaCancelada()
    {
        return $this->estado === 'cancelada';
    }
}