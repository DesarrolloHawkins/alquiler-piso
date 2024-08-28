<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\Edificio;
use Illuminate\Http\Request;

class ApartamentosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pisos = Apartamento::all();
        return view('apartamentos.index', compact('pisos'));
    }

    public function indexAdmin(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'id'); // Default sort column
        $order = $request->get('order', 'asc'); // Default sort order

        $apartamentos = Apartamento::where(function ($query) use ($search) {
            $query->where('nombre', 'like', '%'.$search.'%')
                  ->orWhere('edificio', 'like', '%'.$search.'%');
        })
        ->orderBy($sort, $order)
        ->paginate(30);
        // $apartamentos = Apartamento::all();
        return view('admin.apartamentos.index', compact('apartamentos'));
    }
    public function createAdmin()
    {
        $edificios = Edificio::all();
        
        return view('admin.apartamentos.create', compact('edificios'));
    }

    public function editAdmin($id)
    {
        $apartamento = Apartamento::find($id);
        $edificios = Edificio::all();
        return view('admin.apartamentos.edit', compact('apartamento','edificios'));
    }

    public function updateAdmin(Request $request, $id)
    {
        $apartamento = Apartamento::findOrFail($id);
        $rules = [
                'nombre' => 'required|string|max:255',
                'id_booking' => 'required|string|max:255',
                'id_web' => 'required|string|max:255',
                'titulo' => 'required|string|max:255',
                'edificio_id' => 'required'
        ];

        // Validar los datos del formulario
        $validatedData = $request->validate($rules);
        //    dd($apartamento->titulo);
        $apartamento->titulo = $validatedData['titulo'];
        $apartamento->id_booking = $validatedData['id_booking'];
        $apartamento->id_web = $validatedData['id_web'];
        $apartamento->nombre = $validatedData['nombre'];
        $apartamento->claves = $request['claves'];
        $apartamento->edificio_id = $request['edificio_id'];
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
                'edificio_id' => 'required'
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
