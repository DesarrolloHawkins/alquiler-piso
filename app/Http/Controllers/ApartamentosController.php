<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use Illuminate\Http\Request;

class ApartamentosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pisos = Apartamento::all();
        return view('apartamentos.index', compact('pisos'));
    }

    public function indexAdmin()
    {
        $apartamentos = Apartamento::all();
        return view('admin.apartamentos.index', compact('apartamentos'));
    }
    public function createAdmin()
    {
        return view('admin.apartamentos.create');
    }

    public function editAdmin($id)
    {
        $apartamento = Apartamento::find($id);
        return view('admin.apartamentos.edit', compact('apartamento'));
    }

    public function updateAdmin(Request $request, $id)
    {
        $apartamento = Apartamento::findOrFail($id);
        $rules = [
                'nombre' => 'required|string|max:255',
                'id_booking' => 'required|string|max:255',
                'id_web' => 'required|string|max:255',
                'titulo' => 'required|string|max:255'
        ];

        // Validar los datos del formulario
        $validatedData = $request->validate($rules);
        //    dd($apartamento->titulo);
        $apartamento->titulo = $validatedData['titulo'];
        $apartamento->id_booking = $validatedData['id_booking'];
        $apartamento->id_web = $validatedData['id_web'];
        $apartamento->nombre = $validatedData['nombre'];
        $apartamento->claves = $request['claves'];
        $apartamento->edificio = $request['edificio'];
        $apartamento->save();
       // Actualizar el cliente con los datos validados
        //    $apartamento->update($validatedData);

       // Redireccionar a una ruta de éxito o devolver una respuesta
       return redirect()->route('apartamentos.admin.index')->with('status', 'Apartamento actualizado con éxito!');
    }
    public function storeAdmin(Request $request)
    {
        $rules = [
                'nombre' => 'required|string|max:255',
                'id_booking' => 'required|string|max:255',
                'id_web' => 'required|string|max:255',
                'titulo' => 'required|string|max:255',
                'claves' => 'required|string|max:255',
                'edificio' => 'required'
        ];

        // Validar los datos del formulario
        $validatedData = $request->validate($rules);
        $apartamento = Apartamento::create($validatedData);

       // Redireccionar a una ruta de éxito o devolver una respuesta
       return redirect()->route('apartamentos.admin.index')->with('status', 'Apartamento actualizado con éxito!');
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
    public function show(string $id)
    {
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
