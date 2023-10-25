<?php

namespace App\Http\Controllers;

use App\Models\GestionApartamento;
use Illuminate\Http\Request;

class GestionApartamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view('gestion.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
