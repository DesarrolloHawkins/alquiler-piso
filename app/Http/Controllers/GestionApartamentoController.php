<?php

namespace App\Http\Controllers;

use App\Models\GestionApartamento;
use App\Models\Reserva;
use Illuminate\Http\Request;

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
        $reservasLimpieza = Reserva::apartamentosLimpiados();
        return view('gestion.index', compact('reservasPendientes','reservasOcupados','reservasSalida','reservasLimpieza'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
      
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function edit(GestionApartamento $gestionApartamento)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GestionApartamento $gestionApartamento)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GestionApartamento $gestionApartamento)
    {
        //
    }
}
