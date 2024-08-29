<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemChecklist;
use App\Models\Checklist;

class ItemChecklistController extends Controller
{
    public function index(Request $request)
    {
        // dd($request->all());
        if (isset($request->id)) {
            $checklist = Checklist::findOrFail($request->id);
            $items = ItemChecklist::where('checklist_id', $request->id)->get();
            return view('admin.itemsChecklist.index', compact('checklist', 'items'));
        }
        return view('admin.itemsChecklist.index');
    }

    public function create(Request $request)
    {
        $checklist = Checklist::findOrFail($request->id);
        return view('admin.itemsChecklist.create', compact('checklist'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
        ]);

        ItemChecklist::create([
            'nombre' => $request->nombre,
            'checklist_id' => $request->checklistId,
        ]);

        return redirect()->route('admin.itemsChecklist.index', ['id' => $request->checklistId])->with('success', 'Item creado con éxito.');
    }

    public function edit($id)
    {
        $item = ItemChecklist::findOrFail($id);
        $checklist = Checklist::find($item->checklist_id);
        return view('admin.itemsChecklist.edit', compact('checklist', 'item'));
    }

    public function update(Request $request, $id)
    {
        // dd($request->checklistId);
        $request->validate([
            'nombre' => 'required',
        ]);

        $item = ItemChecklist::findOrFail($id);
        $item->update($request->all());

        return redirect()->route('admin.itemsChecklist.index', ['id' => $request->checklistId])->with('success', 'Item actualizado con éxito.');
    }

    public function destroy($checklistId, $id)
    {
        $item = ItemChecklist::findOrFail($id);
        $item->delete();

        return redirect()->route('admin.itemsChecklist.index', $checklistId)->with('success', 'Item eliminado con éxito.');
    }
}
