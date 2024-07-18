<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reserva extends Model
{
    use HasFactory, SoftDeletes;
    
      /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cliente_id',
        'apartamento_id',
        'estado_id',
        'origen',
        'fecha_entrada',
        'fecha_salida',
        'precio',
        'verificado',
        'dni_entregado',
        'enviado_webpol',
        'codigo_reserva',
        'fecha_limpieza',
        'token'
        
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
    public function cliente()
    {
        return $this->belongsTo(\App\Models\Cliente::class,'cliente_id');
    }

     /**
     * Obtener el usuario
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function apartamento()
    {
        return $this->belongsTo(\App\Models\Apartamento::class,'apartamento_id');
    }
    
      /**
     * Obtener el usuario
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function estado()
    {
        return $this->belongsTo(\App\Models\Estado::class,'estado_id');
    }

     /**
     * Obtener apartamentos pendientes para el dia de hoy
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public static function apartamentosPendiente()
    {
        $hoy = Carbon::now();
        // $apartamentos = self::where('fecha_limpieza', null)->whereDate('fecha_salida', $hoy)->get();
        $apartamentos = self::whereDate('fecha_salida', $hoy)->get();
        $apartamentoLimpieza = [];
        if (count($apartamentos) > 0) {
            foreach($apartamentos as $item){
                $apartamento = ApartamentoLimpieza::where('reserva_id',$item->id)->first();
                if($apartamento == null){
                    $apartamentoLimpieza[] = $item;
                }
            }
        }
        return $apartamentoLimpieza;
    }
    /**
     * Obtener apartamentos ocupados para el dia de hoy
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public static function apartamentosOcupados()
    {
        $hoy = Carbon::now();
        return self::whereDate('fecha_entrada','<=', $hoy)->get();
    }

    /**
     * Obtener apartamentos fechas salida para el dia de mañana
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public static function apartamentosSalida()
    {
        $manana = Carbon::now()->addDay();
        return self::whereDate('fecha_salida', $manana)->get();
    }

    /**
     * Obtener apartamentos fechas salida para el dia de mañana
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public static function apartamentosLimpiados()
    {
        $hoy = Carbon::now();
        return self::whereDate('fecha_limpieza', $hoy)->get();
    }
}

