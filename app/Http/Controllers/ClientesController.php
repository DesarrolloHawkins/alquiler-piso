<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\MensajeAuto;
use App\Models\Photo;
use App\Models\Reserva;
use Illuminate\Http\Request;

class ClientesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clientes = Cliente::paginate(10);
        return view('Clientes.index', compact('clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Clientes.create');

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
    public function show(Cliente $cliente)
    {
        $reservas = Reserva::where('cliente_id', $cliente->id)->get();
        $mensajes = MensajeAuto::where('cliente_id', $cliente->id)->get();
        $photos = Photo::where('cliente_id', $cliente->id)->get();
        return view('Clientes.show', compact('cliente', 'mensajes', 'photos', 'reservas'));
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
