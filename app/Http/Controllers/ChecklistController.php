<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Checklist;
use App\Models\Edificio;

class ChecklistController extends Controller
{
    public function index()
    {
        $checklists = Checklist::all();
        return view('admin.checklists.index', compact('checklists'));
    }

    public function create()
    {
        $edificios = Edificio::all();
        return view('admin.checklists.create', compact('edificios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'edificio_id' => 'required',
        ]);

        Checklist::create($request->all());

        return redirect()->route('admin.checklists.index')->with('success', 'Categoria creado con éxito.');
    }

    public function edit($id)
    {
        $checklist = Checklist::findOrFail($id);
        $edificios = Edificio::all();

        return view('admin.checklists.edit', compact('checklist','edificios'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required',
            'edificio_id' => 'required',
        ]);

        $checklist = Checklist::findOrFail($id);
        $checklist->update($request->all());

        return redirect()->route('admin.checklists.index')->with('success', 'Categoria actualizado con éxito.');
    }

    public function destroy($id)
    {
        $checklist = Checklist::findOrFail($id);
        $checklist->delete();

        return redirect()->route('admin.checklists.index')->with('success', 'Categoria eliminado con éxito.');
    }
}
