<?php

namespace App\Http\Controllers;

use App\Models\Edificio;
use Illuminate\Http\Request;
use Yajra\DataTables\Html\Editor\Editor;

class EdificiosController extends Controller
{
    public function index(Request $request) {
        $search = $request->get('search');
        $sort = $request->get('sort', 'id'); // Default sort column
        $order = $request->get('order', 'asc'); // Default sort order

        $edificios = Edificio::where(function ($query) use ($search) {
            $query->where('nombre', 'like', '%'.$search.'%');
        })
        ->orderBy($sort, $order)
        ->paginate(30);
        // $bancos = Bancos::all();
        return view('admin.edificios.index', compact('edificios'));
    }

    public function create(){
        return view('admin.edificios.create');
    }

    public function store(Request $request){
        $rules = [
            'nombre' => 'required|string|max:255',
            'clave' => 'required|string|max:255',
        ];

        // Validar los datos del formulario
        $validatedData = $request->validate($rules);
        $edificio = Edificio::create($validatedData);

        return redirect()->route('admin.edificios.index')->with('status', 'Edificio creado con éxito!');

    }
    public function edit($id){
        $edificio = Edificio::find($id);
        return view('admin.edificios.edit', compact('edificio'));
    }

    public function update(Request $request, $id){
        $rules = [
            'nombre' => 'required|string|max:255',
            'clave' => 'required|string|max:255',
        ];

        // Validar los datos del formulario
        $validatedData = $request->validate($rules);
        $edificio = Edificio::find($id);

        $edificio->update([
            'nombre' => $validatedData['nombre'],
            'clave' => $validatedData['clave']
        ]);

        return redirect()->route('admin.edificios.index')->with('status', 'Edificio actualizado con éxito!');

    }
    public function destroy($id){
        $edificio = Edificio::find($id);
        $edificio->delete();
        return redirect()->route('admin.edificios.index')->with('status', 'Edificio eliminado con éxito!');
    }
}
