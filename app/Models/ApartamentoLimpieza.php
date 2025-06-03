<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApartamentoLimpieza extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'apartamento_limpieza';

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'apartamento_id',
        'status_id',
        'reserva_id',
        'bano',
        'bano_toallas_aseos',
        'bano_toallas_mano',
        'bano_alfombra',
        'bano_secador',
        'bano_papel',
        'bano_rellenar_gel',
        'bano_espejo',
        'bano_ganchos',
        'bano_muebles',
        'bano_desague',
        'dormitorio',
        'dormitorio_sabanas',
        'dormitorio_cojines',
        'dormitorio_edredon',
        'dormitorio_funda_edredon',
        'dormitorio_canape',
        'dormitorio_manta_cubrepies',
        'dormitorio_papel_plancha',
        'dormitorio_toallas_rulo',
        'dormitorio_revision_pelos',
        'armario',
        'armario_perchas',
        'armario_almohada_repuesto_sofa',
        'armario_edredon_repuesto_sofa',
        'armario_funda_repuesto_edredon',
        'armario_sabanas_repuesto',
        'armario_plancha',
        'armario_tabla_plancha',
        'armario_toalla',
        'canape',
        'canape_almohada',
        'canape_gel',
        'canape_sabanas',
        'canape_toallas',
        'canape_papel_wc',
        'canape_estropajo',
        'canape_bayeta',
        'canape_antihumedad',
        'canape_ambientador',
        'salon',
        'salon_cojines',
        'salon_sofa_cama',
        'salon_planta_cesta',
        'salon_mandos',
        'salon_tv',
        'salon_cortinas',
        'salon_sillas',
        'salon_salvamanteles',
        'salon_estanteria',
        'salon_decoracion',
        'salon_ambientador',
        'salon_libros_juego',
        'cocina',
        'cocina_vitroceramica',
        'cocina_vajilla',
        'cocina_vasos',
        'cocina_tazas',
        'cocina_tapadera',
        'cocina_sartenes',
        'cocina_paño_cocina',
        'cocina_cuberteria',
        'cocina_cuchillo',
        'cocina_ollas',
        'cocina_papel_cocina',
        'cocina_tapadera_micro',
        'cocina_estropajo',
        'cocina_mistol',
        'cocina_tostadora',
        'cocina_bolsa_basura',
        'cocina_tabla_cortar',
        'cocina_escurreplatos',
        'cocina_bol_escurridor',
        'cocina_utensilios_cocina',
        'cocina_dolcegusto',
        'amenities',
        'amenities_gafas',
        'amenities_nota_agradecimiento',
        'amenities_magdalenas',
        'amenities_caramelos',
        'amenities_',
        'observacion',
        'fecha_comienzo',
        'fecha_fin',
        'user_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_comienzo' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    /**
     * The attributes that can be null.
     *
     * @var array
     */
    protected $nullable = [
        'reserva_id',
        'fecha_fin',
        'observacion'
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
    // Relación con Apartamento
    public function apartamento()
    {
        return $this->belongsTo(Apartamento::class, 'apartamento_id');
    }

    // // Relación con Reserva
    // public function reserva()
    // {
    //     return $this->belongsTo(Reserva::class, 'reserva_id');
    // }

    // Relación con el estado de la limpieza
    public function estado()
    {
        return $this->belongsTo(ApartamentoLimpiezaEstado::class, 'status_id');
    }
    public function itemsMarcados()
    {
        return $this->belongsToMany(ItemChecklist::class, 'apartamento_item_checklist', 'apartamento_limpieza_id', 'item_checklist_id');
    }

    public function controles()
    {
        return $this->belongsToMany(ItemChecklist::class, 'apartamento_item_checklist', 'apartamento_limpieza_id', 'item_checklist_id')
                    ->withPivot('status'); // Asumiendo que también estás almacenando un 'status' o cualquier otro dato adicional.
    }




    // Relación con fotos
    public function fotos()
    {
        return $this->hasMany(Photo::class, 'limpieza_id');
    }

    // Obtener el Checklist del edificio relacionado con el apartamento
    public function checklist()
    {
        return $this->apartamento->edificio->checklist;
    }

    // Obtener los ítems del Checklist del edificio
    public function itemChecklists()
    {
        return $this->checklist->items;
    }
    /**
     * Obtener apartamentos fechas salida para el dia de mañana
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public static function apartamentosEnLimpiados()
    {
        // Obtener la fecha y hora actual en el formato deseado
        $fechaActual = Carbon::now()->format('Y-m-d');
        return self::where('status_id', 2)
                ->whereDate('fecha_comienzo', $fechaActual)
                ->get();
    }
    /**
     * Obtener apartamentos fechas salida para el dia de mañana
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public static function apartamentosLimpiados()
    {
        $fechaActual = Carbon::now()->format('Y-m-d');

        return self::where('status_id', 3)
                ->whereDate('fecha_comienzo', $fechaActual)
                ->whereDate('fecha_fin', $fechaActual)
                ->get();
    }
}
