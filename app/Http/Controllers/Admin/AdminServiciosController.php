<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class AdminServiciosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $servicios = Servicio::orderBy('categoria')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        // Agrupar por categoría para la vista
        $serviciosPorCategoria = $servicios->groupBy('categoria');

        return view('admin.servicios.index', compact('servicios', 'serviciosPorCategoria'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.servicios.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'icono' => 'nullable|string|max:255',
            'nombre' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:servicios,slug',
            'descripcion' => 'nullable|string',
            'precio' => 'nullable|numeric|min:0|max:999999.99',
            'imagen' => 'nullable|string|max:255',
            'orden' => 'nullable|integer|min:0',
            'categoria' => 'nullable|string|max:255',
            'es_popular' => 'nullable|boolean',
            'activo' => 'nullable|boolean',
        ]);

        Servicio::create($validated);

        Alert::success('Éxito', 'Servicio creado correctamente');
        return redirect()->route('admin.servicios.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Servicio $servicio)
    {
        return view('admin.servicios.edit', compact('servicio'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Servicio $servicio)
    {
        $validated = $request->validate([
            'icono' => 'nullable|string|max:255',
            'nombre' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:servicios,slug,' . $servicio->id,
            'descripcion' => 'nullable|string',
            'precio' => 'nullable|numeric|min:0|max:999999.99',
            'imagen' => 'nullable|string|max:255',
            'orden' => 'nullable|integer|min:0',
            'categoria' => 'nullable|string|max:255',
            'es_popular' => 'nullable|boolean',
            'activo' => 'nullable|boolean',
        ]);

        $servicio->update($validated);

        Alert::success('Éxito', 'Servicio actualizado correctamente');
        return redirect()->route('admin.servicios.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Servicio $servicio)
    {
        $servicio->delete();

        Alert::success('Éxito', 'Servicio eliminado correctamente');
        return redirect()->route('admin.servicios.index');
    }
}
