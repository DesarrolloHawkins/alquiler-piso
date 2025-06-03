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
        'room_type_id',
        'rate_plans_id',
        'id_channex',
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
        'token',
        'numero_personas',
        'numero_personas_plataforma',
        'neto',
        'comision',
        'cargo_por_pago',
        'iva'
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
        $hoy = Carbon::now()->toDateString(); // Asegurarse de obtener la fecha en formato adecuado

        // Obtener las reservas cuya fecha de salida es hoy y no tienen asignada fecha de limpieza
        $reservasPendientes = self::whereNull('fecha_limpieza')->where('estado_id', '!=', 4)->whereDate('fecha_salida', $hoy)->get();

        // Obtener las limpiezas de fondo programadas para hoy
        $limpiezasDeFondo = LimpiezaFondo::whereDate('fecha', $hoy)->get();

        // Obtener los apartamentos que ya tienen una limpieza registrada hoy en ApartamentosLimpieza
        $apartamentosLimpieza = ApartamentoLimpieza::whereDate('fecha_comienzo', $hoy)->pluck('apartamento_id')->toArray();

        $apartamentos = collect(); // Colección para almacenar todos los resultados

        // Agregar las reservas pendientes, excluyendo los que ya tienen una limpieza registrada
        foreach ($reservasPendientes as $reserva) {
            if (!in_array($reserva->apartamento_id, $apartamentosLimpieza)) {
                $apartamentos->push($reserva);
            }
        }

        // Agregar los apartamentos de limpieza de fondo si no están ya considerados ni ya limpiados
        foreach ($limpiezasDeFondo as $limpieza) {
            if (!in_array($limpieza->apartamento_id, $apartamentosLimpieza) && !$apartamentos->contains('apartamento_id', $limpieza->apartamento_id)) {
                // Simular un objeto Reserva para mantener la compatibilidad
                $simulatedReserva = new self;
                $simulatedReserva->apartamento_id = $limpieza->apartamento_id;
                $simulatedReserva->fecha_salida = $hoy; // Asumir la fecha de salida igual a hoy
                $simulatedReserva->fecha_limpieza = $hoy; // Asumir que la limpieza está programada para hoy
                $simulatedReserva->limpieza_fondo = true; // Indicar que es una limpieza de fondo

                // Añadir al resultado
                $apartamentos->push($simulatedReserva);
            }
        }

        return $apartamentos;
    }

    /**
     * Obtener apartamentos ocupados para el dia de hoy
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public static function apartamentosOcupados()
    {
        $hoy = Carbon::now();
        return self::whereDate('fecha_entrada','<=', $hoy)
                ->where('estado_id', '!=', 4)
                ->get();
    }


    /**
     * Obtener apartamentos fechas salida para el dia de mañana
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public static function apartamentosSalida()
    {
        $manana = Carbon::now()->addDay();
        return self::whereDate('fecha_salida', $manana)
                   ->where('estado_id', '!=', 4)
                   ->get();
    }

    /**
     * Obtener apartamentos fechas salida para el dia de mañana
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public static function apartamentosLimpiados()
    {
        $hoy = Carbon::now();
        return self::whereDate('fecha_limpieza', $hoy)
                ->where('estado_id', '!=', 4)
                ->get();
    }

     // Aquí agregamos la función para obtener la siguiente reserva
     public function siguienteReserva()
     {
         return self::where('apartamento_id', $this->apartamento_id)
                    ->where('fecha_entrada', '>', $this->fecha_salida)
                    ->where('estado_id', '!=', 4)
                    ->orderBy('fecha_entrada', 'asc')
                    ->first();
     }

    public function scopeActivas($query)
    {
        return $query->where('estado_id', '!=', 4);
    }

}

