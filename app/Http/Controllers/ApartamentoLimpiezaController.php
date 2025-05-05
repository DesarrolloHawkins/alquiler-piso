<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\ApartamentoLimpieza;
use App\Models\Checklist;
use App\Models\Photo;
use Illuminate\Http\Request;
use App\Models\ApartamentoLimpiezaItem;
use App\Models\Reserva;


class ApartamentoLimpiezaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    // public function show($id)
    // {
    //     // Buscar la limpieza del apartamento con sus fotos y otras relaciones necesarias
    //     $apartamentoLimpieza = ApartamentoLimpieza::with(['apartamento.edificioRelacion.checklists.items', 'fotos'])
    //                         ->findOrFail($id);

    //     // Agrupar items por sección del checklist
    //     $itemChecklists = [];
    //     foreach ($apartamentoLimpieza->apartamento->edificioRelacion->checklists as $checklist) {
    //         foreach ($checklist->items as $item) {
    //             $itemChecklists[$checklist->nombre][] = $item; // Asume que cada checklist tiene un 'nombre'
    //         }
    //     }

    //     // Pasar los datos a la vista
    //     return view('admin.apartamentos.limpieza-show', [
    //         'apartamentoLimpieza' => $apartamentoLimpieza,
    //         'itemChecklists' => $itemChecklists
    //     ]);
    // }
    public function show($id)
    {
        $apartamento = ApartamentoLimpieza::findOrFail($id);

        // Trae los ítems de limpieza con su checklist relacionado
        $apartamentoLimpiezaItem = ApartamentoLimpiezaItem::with('checklist')
            ->where('id_limpieza', $apartamento->id)
            ->get()
            ->map(function ($item) {
                $item->grupo = $item->checklist->nombre ?? 'Sin grupo';
                return $item;
            });

        $fotos = Photo::where('limpieza_id', $apartamento->id)->with('categoria')->get();
        $apartamentoId = Reserva::find($id)->apartamento_id;
        $edificioId = Apartamento::find($apartamentoId)->edificio_id;

        $checklists = Checklist::with('items')->where('edificio_id', $edificioId)->get();
        return view('admin.apartamentos.limpieza-show', compact('apartamento', 'apartamentoLimpiezaItem', 'fotos', 'checklists'));
    }







    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ApartamentoLimpieza $apartamentoLimpieza)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ApartamentoLimpieza $apartamentoLimpieza)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ApartamentoLimpieza $apartamentoLimpieza)
    {
        //
    }
}
