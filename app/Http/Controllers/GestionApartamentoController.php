<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\ApartamentoLimpieza;
use App\Models\GestionApartamento;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class GestionApartamentoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reservasPendientes = Reserva::apartamentosPendiente();
        $reservasOcupados = Reserva::apartamentosOcupados();
        $reservasSalida = Reserva::apartamentosSalida();
        // $reservasLimpieza = Reserva::apartamentosLimpiados();
        $reservasLimpieza = ApartamentoLimpieza::apartamentosLimpiados();
        $reservasEnLimpieza = ApartamentoLimpieza::apartamentosEnLimpiados();

        return view('gestion.index', compact('reservasPendientes','reservasOcupados','reservasSalida','reservasLimpieza','reservasEnLimpieza'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {
        $reserva = Reserva::find($id);
        $apartamentoLimpio = ApartamentoLimpieza::where('fecha_fin', null)->where('apartamento_id', $reserva->apartamento_id)->first();

            if ($apartamentoLimpio == null) {
                $apartamentoLimpieza = ApartamentoLimpieza::create([
                    'apartamento_id' => $reserva->apartamento_id,
                    'fecha_comienzo' => Carbon::now(),
                    'status_id' => 2,
                    'reserva_id' => $id
                ]);
                $reserva->fecha_limpieza = Carbon::now();
                $reserva->save();
            } else {
                $apartamentoLimpieza = $apartamentoLimpio;
            }
            
        // $apartamento = Apartamento::find($id);
      return view('gestion.edit', compact('apartamentoLimpieza','id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $id = $request->id;
        $apartamento = ApartamentoLimpieza::where('id', $id)->first();
        // Validaciones
        isset($request->bano) ? $apartamento->bano = true :$apartamento->bano = false;
        isset($request->bano_toallas_aseos) ? $apartamento->bano_toallas_aseos = true :$apartamento->bano_toallas_aseos = false;
        isset($request->bano_toallas_mano) ? $apartamento->bano_toallas_mano = true :$apartamento->bano_toallas_mano = false;
        isset($request->bano_alfombra) ? $apartamento->bano_alfombra = true :$apartamento->bano_alfombra = false;
        isset($request->bano_secador) ? $apartamento->bano_secador = true :$apartamento->bano_secador = false;
        isset($request->bano_papel) ? $apartamento->bano_papel = true :$apartamento->bano_papel = false;
        isset($request->bano_rellenar_gel) ? $apartamento->bano_rellenar_gel = true :$apartamento->bano_rellenar_gel = false;
        isset($request->bano_espejo) ? $apartamento->bano_espejo = true :$apartamento->bano_espejo = false;
        isset($request->bano_ganchos) ? $apartamento->bano_ganchos = true :$apartamento->bano_ganchos = false;
        isset($request->bano_muebles) ? $apartamento->bano_muebles = true :$apartamento->bano_muebles = false;
        isset($request->bano_desague) ? $apartamento->bano_desague = true :$apartamento->bano_desague = false;
        isset($request->dormitorio) ? $apartamento->dormitorio = true :$apartamento->dormitorio = false;
        isset($request->dormitorio_sabanas) ? $apartamento->dormitorio_sabanas = true :$apartamento->dormitorio_sabanas = false;
        isset($request->dormitorio_cojines) ? $apartamento->dormitorio_cojines = true :$apartamento->dormitorio_cojines = false;
        isset($request->dormitorio_edredon) ? $apartamento->dormitorio_edredon = true :$apartamento->dormitorio_edredon = false;
        isset($request->dormitorio_funda_edredon) ? $apartamento->dormitorio_funda_edredon = true :$apartamento->dormitorio_funda_edredon = false;
        isset($request->dormitorio_canape) ? $apartamento->dormitorio_canape = true :$apartamento->dormitorio_canape = false;
        isset($request->dormitorio_manta_cubrepies) ? $apartamento->dormitorio_manta_cubrepies = true :$apartamento->dormitorio_manta_cubrepies = false;
        isset($request->dormitorio_papel_plancha) ? $apartamento->dormitorio_papel_plancha = true :$apartamento->dormitorio_papel_plancha = false;
        isset($request->dormitorio_toallas_rulo) ? $apartamento->dormitorio_toallas_rulo = true :$apartamento->dormitorio_toallas_rulo = false;
        isset($request->dormitorio_revision_pelos) ? $apartamento->dormitorio_revision_pelos = true :$apartamento->dormitorio_revision_pelos = false;
        isset($request->armario) ? $apartamento->armario = true :$apartamento->armario = false;
        isset($request->armario_perchas) ? $apartamento->armario_perchas = true :$apartamento->armario_perchas = false;
        isset($request->armario_almohada_repuesto_sofa) ? $apartamento->armario_almohada_repuesto_sofa = true :$apartamento->armario_almohada_repuesto_sofa = false;
        isset($request->armario_edredon_repuesto_sofa) ? $apartamento->armario_edredon_repuesto_sofa = true :$apartamento->armario_edredon_repuesto_sofa = false;
        isset($request->armario_funda_repuesto_edredon) ? $apartamento->armario_funda_repuesto_edredon = true :$apartamento->armario_funda_repuesto_edredon = false;
        isset($request->armario_sabanas_repuesto) ? $apartamento->armario_sabanas_repuesto = true :$apartamento->armario_sabanas_repuesto = false;
        isset($request->armario_plancha) ? $apartamento->armario_plancha = true :$apartamento->armario_plancha = false;
        isset($request->armario_tabla_plancha) ? $apartamento->armario_tabla_plancha = true :$apartamento->armario_tabla_plancha = false;
        isset($request->armario_toalla) ? $apartamento->armario_toalla = true :$apartamento->armario_toalla = false;
        isset($request->canape) ? $apartamento->canape = true :$apartamento->canape = false;
        isset($request->canape_almohada) ? $apartamento->canape_almohada = true :$apartamento->canape_almohada = false;
        isset($request->canape_gel) ? $apartamento->canape_gel = true :$apartamento->canape_gel = false;
        isset($request->canape_sabanas) ? $apartamento->canape_sabanas = true :$apartamento->canape_sabanas = false;
        isset($request->canape_toallas) ? $apartamento->canape_toallas = true :$apartamento->canape_toallas = false;
        isset($request->canape_papel_wc) ? $apartamento->canape_papel_wc = true :$apartamento->canape_papel_wc = false;
        isset($request->canape_estropajo) ? $apartamento->canape_estropajo = true :$apartamento->canape_estropajo = false;
        isset($request->canape_bayeta) ? $apartamento->canape_bayeta = true :$apartamento->canape_bayeta = false;
        isset($request->canape_antihumedad) ? $apartamento->canape_antihumedad = true :$apartamento->canape_antihumedad = false;
        isset($request->canape_ambientador) ? $apartamento->canape_ambientador = true :$apartamento->canape_ambientador = false;
        isset($request->salon) ? $apartamento->salon = true :$apartamento->salon = false;
        isset($request->salon_cojines) ? $apartamento->salon_cojines = true :$apartamento->salon_cojines = false;
        isset($request->salon_sofa_cama) ? $apartamento->salon_sofa_cama = true :$apartamento->salon_sofa_cama = false;
        isset($request->salon_planta_cesta) ? $apartamento->salon_planta_cesta = true :$apartamento->salon_planta_cesta = false;
        isset($request->salon_mandos) ? $apartamento->salon_mandos = true :$apartamento->salon_mandos = false;
        isset($request->salon_tv) ? $apartamento->salon_tv = true :$apartamento->salon_tv = false;
        isset($request->salon_cortinas) ? $apartamento->salon_cortinas = true :$apartamento->salon_cortinas = false;
        isset($request->salon_sillas) ? $apartamento->salon_sillas = true :$apartamento->salon_sillas = false;
        isset($request->salon_salvamanteles) ? $apartamento->salon_salvamanteles = true :$apartamento->salon_salvamanteles = false;
        isset($request->salon_estanteria) ? $apartamento->salon_estanteria = true :$apartamento->salon_estanteria = false;
        isset($request->salon_decoracion) ? $apartamento->salon_decoracion = true :$apartamento->salon_decoracion = false;
        isset($request->salon_ambientador) ? $apartamento->salon_ambientador = true :$apartamento->salon_ambientador = false;
        isset($request->salon_libros_juego) ? $apartamento->salon_libros_juego = true :$apartamento->salon_libros_juego = false;
        isset($request->cocina) ? $apartamento->cocina = true :$apartamento->cocina = false;
        isset($request->cocina_vitroceramica) ? $apartamento->cocina_vitroceramica = true :$apartamento->cocina_vitroceramica = false;
        isset($request->cocina_vajilla) ? $apartamento->cocina_vajilla = true :$apartamento->cocina_vajilla = false;
        isset($request->cocina_vasos) ? $apartamento->cocina_vasos = true :$apartamento->cocina_vasos = false;
        isset($request->cocina_tazas) ? $apartamento->cocina_tazas = true :$apartamento->cocina_tazas = false;
        isset($request->cocina_tapadera) ? $apartamento->cocina_tapadera = true :$apartamento->cocina_tapadera = false;
        isset($request->cocina_sartenes) ? $apartamento->cocina_sartenes = true :$apartamento->cocina_sartenes = false;
        isset($request->cocina_paño_cocina) ? $apartamento->cocina_paño_cocina = true :$apartamento->cocina_paño_cocina = false;
        isset($request->cocina_cuberteria) ? $apartamento->cocina_cuberteria = true :$apartamento->cocina_cuberteria = false;
        isset($request->cocina_cuchillo) ? $apartamento->cocina_cuchillo = true :$apartamento->cocina_cuchillo = false;
        isset($request->cocina_ollas) ? $apartamento->cocina_ollas = true :$apartamento->cocina_ollas = false;
        isset($request->cocina_papel_cocina) ? $apartamento->cocina_papel_cocina = true :$apartamento->cocina_papel_cocina = false;
        isset($request->cocina_tapadera_micro) ? $apartamento->cocina_tapadera_micro = true :$apartamento->cocina_tapadera_micro = false;
        isset($request->cocina_estropajo) ? $apartamento->cocina_estropajo = true :$apartamento->cocina_estropajo = false;
        isset($request->cocina_mistol) ? $apartamento->cocina_mistol = true :$apartamento->cocina_mistol = false;
        isset($request->cocina_tostadora) ? $apartamento->cocina_tostadora = true :$apartamento->cocina_tostadora = false;
        isset($request->cocina_bolsa_basura) ? $apartamento->cocina_bolsa_basura = true :$apartamento->cocina_bolsa_basura = false;
        isset($request->cocina_tabla_cortar) ? $apartamento->cocina_tabla_cortar = true :$apartamento->cocina_tabla_cortar = false;
        isset($request->cocina_escurreplatos) ? $apartamento->cocina_escurreplatos = true :$apartamento->cocina_escurreplatos = false;
        isset($request->cocina_bol_escurridor) ? $apartamento->cocina_bol_escurridor = true :$apartamento->cocina_bol_escurridor = false;
        isset($request->cocina_utensilios_cocina) ? $apartamento->cocina_utensilios_cocina = true :$apartamento->cocina_utensilios_cocina = false;
        isset($request->cocina_dolcegusto) ? $apartamento->cocina_dolcegusto = true :$apartamento->cocina_dolcegusto = false;
        isset($request->amenities) ? $apartamento->amenities = true :$apartamento->amenities = false;
        isset($request->amenities_nota_agradecimiento) ? $apartamento->amenities_nota_agradecimiento = true :$apartamento->amenities_nota_agradecimiento = false;
        isset($request->amenities_magdalenas) ? $apartamento->amenities_magdalenas = true :$apartamento->amenities_magdalenas = false;
        isset($request->amenities_caramelos) ? $apartamento->amenities_caramelos = true :$apartamento->amenities_caramelos = false;

        // isset($request->status) ? $apartamento->status = $request->status : $apartamento->status = 0;
        $apartamento->observacion = $request->observacion;
        $response = $apartamento->save();

        Alert::success('Guardado con Exito', 'Apartamento actualizado correctamente');
        return redirect()->route('gestion.index');

    }

    /**
     * Display the specified resource.
     */
    public function storeColumn(Request $request)
    {
        $apartamento = ApartamentoLimpieza::find($request->id);

        if ($apartamento) {
            $columna = $request->name;
            $apartamento->$columna = $request->checked == 'true' ? true : false;
            $apartamento->save();
            Alert::toast('Actualizado', 'success');
            return true;

        }
        Alert::toast('Error, intentelo mas tarde', 'error');

        return false;
    }

    /**
     * Display the specified resource.
     */
    public function show(GestionApartamento $gestionApartamento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ApartamentoLimpieza $apartamentoLimpieza)
    {
        ApartamentoLimpieza::find($apartamentoLimpieza);
        // dd($apartamentoLimpieza->apartamento);
        return view('gestion.edit', compact('apartamentoLimpieza'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ApartamentoLimpieza $apartamentoLimpieza)
    {
        // dd($apartamento);
        // Validaciones
        isset($request->bano) ? $apartamentoLimpieza->bano = true :$apartamentoLimpieza->bano = false;
        isset($request->bano_toallas_aseos) ? $apartamentoLimpieza->bano_toallas_aseos = true :$apartamentoLimpieza->bano_toallas_aseos = false;
        isset($request->bano_toallas_mano) ? $apartamentoLimpieza->bano_toallas_mano = true :$apartamentoLimpieza->bano_toallas_mano = false;
        isset($request->bano_alfombra) ? $apartamentoLimpieza->bano_alfombra = true :$apartamentoLimpieza->bano_alfombra = false;
        isset($request->bano_secador) ? $apartamentoLimpieza->bano_secador = true :$apartamentoLimpieza->bano_secador = false;
        isset($request->bano_papel) ? $apartamentoLimpieza->bano_papel = true :$apartamentoLimpieza->bano_papel = false;
        isset($request->bano_rellenar_gel) ? $apartamentoLimpieza->bano_rellenar_gel = true :$apartamentoLimpieza->bano_rellenar_gel = false;
        isset($request->bano_espejo) ? $apartamentoLimpieza->bano_espejo = true :$apartamentoLimpieza->bano_espejo = false;
        isset($request->bano_ganchos) ? $apartamentoLimpieza->bano_ganchos = true :$apartamentoLimpieza->bano_ganchos = false;
        isset($request->bano_muebles) ? $apartamentoLimpieza->bano_muebles = true :$apartamentoLimpieza->bano_muebles = false;
        isset($request->bano_desague) ? $apartamentoLimpieza->bano_desague = true :$apartamentoLimpieza->bano_desague = false;
        isset($request->dormitorio) ? $apartamentoLimpieza->dormitorio = true :$apartamentoLimpieza->dormitorio = false;
        isset($request->dormitorio_sabanas) ? $apartamentoLimpieza->dormitorio_sabanas = true :$apartamentoLimpieza->dormitorio_sabanas = false;
        isset($request->dormitorio_cojines) ? $apartamentoLimpieza->dormitorio_cojines = true :$apartamentoLimpieza->dormitorio_cojines = false;
        isset($request->dormitorio_edredon) ? $apartamentoLimpieza->dormitorio_edredon = true :$apartamentoLimpieza->dormitorio_edredon = false;
        isset($request->dormitorio_funda_edredon) ? $apartamentoLimpieza->dormitorio_funda_edredon = true :$apartamentoLimpieza->dormitorio_funda_edredon = false;
        isset($request->dormitorio_canape) ? $apartamentoLimpieza->dormitorio_canape = true :$apartamentoLimpieza->dormitorio_canape = false;
        isset($request->dormitorio_manta_cubrepies) ? $apartamentoLimpieza->dormitorio_manta_cubrepies = true :$apartamentoLimpieza->dormitorio_manta_cubrepies = false;
        isset($request->dormitorio_papel_plancha) ? $apartamentoLimpieza->dormitorio_papel_plancha = true :$apartamentoLimpieza->dormitorio_papel_plancha = false;
        isset($request->dormitorio_toallas_rulo) ? $apartamentoLimpieza->dormitorio_toallas_rulo = true :$apartamentoLimpieza->dormitorio_toallas_rulo = false;
        isset($request->dormitorio_revision_pelos) ? $apartamentoLimpieza->dormitorio_revision_pelos = true :$apartamentoLimpieza->dormitorio_revision_pelos = false;
        isset($request->armario) ? $apartamentoLimpieza->armario = true :$apartamentoLimpieza->armario = false;
        isset($request->armario_perchas) ? $apartamentoLimpieza->armario_perchas = true :$apartamentoLimpieza->armario_perchas = false;
        isset($request->armario_almohada_repuesto_sofa) ? $apartamentoLimpieza->armario_almohada_repuesto_sofa = true :$apartamentoLimpieza->armario_almohada_repuesto_sofa = false;
        isset($request->armario_edredon_repuesto_sofa) ? $apartamentoLimpieza->armario_edredon_repuesto_sofa = true :$apartamentoLimpieza->armario_edredon_repuesto_sofa = false;
        isset($request->armario_funda_repuesto_edredon) ? $apartamentoLimpieza->armario_funda_repuesto_edredon = true :$apartamentoLimpieza->armario_funda_repuesto_edredon = false;
        isset($request->armario_sabanas_repuesto) ? $apartamentoLimpieza->armario_sabanas_repuesto = true :$apartamentoLimpieza->armario_sabanas_repuesto = false;
        isset($request->armario_plancha) ? $apartamentoLimpieza->armario_plancha = true :$apartamentoLimpieza->armario_plancha = false;
        isset($request->armario_tabla_plancha) ? $apartamentoLimpieza->armario_tabla_plancha = true :$apartamentoLimpieza->armario_tabla_plancha = false;
        isset($request->armario_toalla) ? $apartamentoLimpieza->armario_toalla = true :$apartamentoLimpieza->armario_toalla = false;
        isset($request->canape) ? $apartamentoLimpieza->canape = true :$apartamentoLimpieza->canape = false;
        isset($request->canape_almohada) ? $apartamentoLimpieza->canape_almohada = true :$apartamentoLimpieza->canape_almohada = false;
        isset($request->canape_gel) ? $apartamentoLimpieza->canape_gel = true :$apartamentoLimpieza->canape_gel = false;
        isset($request->canape_sabanas) ? $apartamentoLimpieza->canape_sabanas = true :$apartamentoLimpieza->canape_sabanas = false;
        isset($request->canape_toallas) ? $apartamentoLimpieza->canape_toallas = true :$apartamentoLimpieza->canape_toallas = false;
        isset($request->canape_papel_wc) ? $apartamentoLimpieza->canape_papel_wc = true :$apartamentoLimpieza->canape_papel_wc = false;
        isset($request->canape_estropajo) ? $apartamentoLimpieza->canape_estropajo = true :$apartamentoLimpieza->canape_estropajo = false;
        isset($request->canape_bayeta) ? $apartamentoLimpieza->canape_bayeta = true :$apartamentoLimpieza->canape_bayeta = false;
        isset($request->canape_antihumedad) ? $apartamentoLimpieza->canape_antihumedad = true :$apartamentoLimpieza->canape_antihumedad = false;
        isset($request->canape_ambientador) ? $apartamentoLimpieza->canape_ambientador = true :$apartamentoLimpieza->canape_ambientador = false;
        isset($request->salon) ? $apartamentoLimpieza->salon = true :$apartamentoLimpieza->salon = false;
        isset($request->salon_cojines) ? $apartamentoLimpieza->salon_cojines = true :$apartamentoLimpieza->salon_cojines = false;
        isset($request->salon_sofa_cama) ? $apartamentoLimpieza->salon_sofa_cama = true :$apartamentoLimpieza->salon_sofa_cama = false;
        isset($request->salon_planta_cesta) ? $apartamentoLimpieza->salon_planta_cesta = true :$apartamentoLimpieza->salon_planta_cesta = false;
        isset($request->salon_mandos) ? $apartamentoLimpieza->salon_mandos = true :$apartamentoLimpieza->salon_mandos = false;
        isset($request->salon_tv) ? $apartamentoLimpieza->salon_tv = true :$apartamentoLimpieza->salon_tv = false;
        isset($request->salon_cortinas) ? $apartamentoLimpieza->salon_cortinas = true :$apartamentoLimpieza->salon_cortinas = false;
        isset($request->salon_sillas) ? $apartamentoLimpieza->salon_sillas = true :$apartamentoLimpieza->salon_sillas = false;
        isset($request->salon_salvamanteles) ? $apartamentoLimpieza->salon_salvamanteles = true :$apartamentoLimpieza->salon_salvamanteles = false;
        isset($request->salon_estanteria) ? $apartamentoLimpieza->salon_estanteria = true :$apartamentoLimpieza->salon_estanteria = false;
        isset($request->salon_decoracion) ? $apartamentoLimpieza->salon_decoracion = true :$apartamentoLimpieza->salon_decoracion = false;
        isset($request->salon_ambientador) ? $apartamentoLimpieza->salon_ambientador = true :$apartamentoLimpieza->salon_ambientador = false;
        isset($request->salon_libros_juego) ? $apartamentoLimpieza->salon_libros_juego = true :$apartamentoLimpieza->salon_libros_juego = false;
        isset($request->cocina) ? $apartamentoLimpieza->cocina = true :$apartamentoLimpieza->cocina = false;
        isset($request->cocina_vitroceramica) ? $apartamentoLimpieza->cocina_vitroceramica = true :$apartamentoLimpieza->cocina_vitroceramica = false;
        isset($request->cocina_vajilla) ? $apartamentoLimpieza->cocina_vajilla = true :$apartamentoLimpieza->cocina_vajilla = false;
        isset($request->cocina_vasos) ? $apartamentoLimpieza->cocina_vasos = true :$apartamentoLimpieza->cocina_vasos = false;
        isset($request->cocina_tazas) ? $apartamentoLimpieza->cocina_tazas = true :$apartamentoLimpieza->cocina_tazas = false;
        isset($request->cocina_tapadera) ? $apartamentoLimpieza->cocina_tapadera = true :$apartamentoLimpieza->cocina_tapadera = false;
        isset($request->cocina_sartenes) ? $apartamentoLimpieza->cocina_sartenes = true :$apartamentoLimpieza->cocina_sartenes = false;
        isset($request->cocina_paño_cocina) ? $apartamentoLimpieza->cocina_paño_cocina = true :$apartamentoLimpieza->cocina_paño_cocina = false;
        isset($request->cocina_cuberteria) ? $apartamentoLimpieza->cocina_cuberteria = true :$apartamentoLimpieza->cocina_cuberteria = false;
        isset($request->cocina_cuchillo) ? $apartamentoLimpieza->cocina_cuchillo = true :$apartamentoLimpieza->cocina_cuchillo = false;
        isset($request->cocina_ollas) ? $apartamentoLimpieza->cocina_ollas = true :$apartamentoLimpieza->cocina_ollas = false;
        isset($request->cocina_papel_cocina) ? $apartamentoLimpieza->cocina_papel_cocina = true :$apartamentoLimpieza->cocina_papel_cocina = false;
        isset($request->cocina_tapadera_micro) ? $apartamentoLimpieza->cocina_tapadera_micro = true :$apartamentoLimpieza->cocina_tapadera_micro = false;
        isset($request->cocina_estropajo) ? $apartamentoLimpieza->cocina_estropajo = true :$apartamentoLimpieza->cocina_estropajo = false;
        isset($request->cocina_mistol) ? $apartamentoLimpieza->cocina_mistol = true :$apartamentoLimpieza->cocina_mistol = false;
        isset($request->cocina_tostadora) ? $apartamentoLimpieza->cocina_tostadora = true :$apartamentoLimpieza->cocina_tostadora = false;
        isset($request->cocina_bolsa_basura) ? $apartamentoLimpieza->cocina_bolsa_basura = true :$apartamentoLimpieza->cocina_bolsa_basura = false;
        isset($request->cocina_tabla_cortar) ? $apartamentoLimpieza->cocina_tabla_cortar = true :$apartamentoLimpieza->cocina_tabla_cortar = false;
        isset($request->cocina_escurreplatos) ? $apartamentoLimpieza->cocina_escurreplatos = true :$apartamentoLimpieza->cocina_escurreplatos = false;
        isset($request->cocina_bol_escurridor) ? $apartamentoLimpieza->cocina_bol_escurridor = true :$apartamentoLimpieza->cocina_bol_escurridor = false;
        isset($request->cocina_utensilios_cocina) ? $apartamentoLimpieza->cocina_utensilios_cocina = true :$apartamentoLimpieza->cocina_utensilios_cocina = false;
        isset($request->cocina_dolcegusto) ? $apartamentoLimpieza->cocina_dolcegusto = true :$apartamentoLimpieza->cocina_dolcegusto = false;
        isset($request->amenities) ? $apartamentoLimpieza->amenities = true :$apartamentoLimpieza->amenities = false;
        isset($request->amenities_nota_agradecimiento) ? $apartamentoLimpieza->amenities_nota_agradecimiento = true :$apartamentoLimpieza->amenities_nota_agradecimiento = false;
        isset($request->amenities_magdalenas) ? $apartamentoLimpieza->amenities_magdalenas = true :$apartamentoLimpieza->amenities_magdalenas = false;
        isset($request->amenities_caramelos) ? $apartamentoLimpieza->amenities_caramelos = true :$apartamentoLimpieza->amenities_caramelos = false;

        $apartamentoLimpieza->observacion = $request->observacion;
        $response = $apartamentoLimpieza->save();

        Alert::success('Actualizado con Exito', 'Apartamento actualizado correctamente');
        return redirect()->route('gestion.edit', $apartamentoLimpieza)->with('apartamentoLimpieza');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function finalizar(ApartamentoLimpieza $apartamentoLimpieza)
    {
        $apartamentoLimpieza->status_id = 3;
        $apartamentoLimpieza->fecha_fin = Carbon::now();
        $apartamentoLimpieza->save();
        $reserva = Reserva::find($apartamentoLimpieza->reserva_id);
        $reserva->fecha_limpieza = Carbon::now();
        $reserva->save();
        // dd($reserva);
        Alert::success('Fizalizado con Exito', 'Apartamento Fizalizado correctamente');

        return redirect()->route('gestion.index');
    }
}
