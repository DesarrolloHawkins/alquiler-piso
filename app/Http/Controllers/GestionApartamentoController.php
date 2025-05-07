<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\ApartamentoLimpieza;
use App\Models\Fichaje;
use App\Models\Pausa;
use App\Models\GestionApartamento;
use App\Models\LimpiezaFondo;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Auth; // Añade esta línea
use App\Models\Checklist;
use App\Models\ApartamentoLimpiezaItem;

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
        // $reservasLimpieza = Reserva::apartamentosLimpiados();
        $reservasLimpieza = ApartamentoLimpieza::apartamentosLimpiados();
        $reservasEnLimpieza = ApartamentoLimpieza::apartamentosEnLimpiados();

        $hoy = now()->toDateString();
        $limpiezaFondo = LimpiezaFondo::whereDate('fecha', $hoy)->get();

        $fichajeHoy = Fichaje::where('user_id', Auth::id())
                                ->whereDate('hora_entrada', $hoy)
                                ->whereNull('hora_salida')  // Asegúrate de considerar solo los fichajes no finalizados
                                ->latest()
                                ->first();

        $pausaActiva = null;
        if ($fichajeHoy) {
            $pausaActiva = $fichajeHoy->pausas()->whereNull('fin_pausa')->latest()->first();
        }


        return view('gestion.index', compact('reservasPendientes','reservasOcupados','reservasSalida','reservasLimpieza','reservasEnLimpieza', 'fichajeHoy', 'pausaActiva','limpiezaFondo'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {
        if (strpos($id, 'null') !== false) {
            preg_match('/-?\d+/', $id, $matches);
            $nuevoID = $matches[0];
            $apartamentoLimpio = ApartamentoLimpieza::where('fecha_fin', null)->where('apartamento_id', $nuevoID)->first();

            if ($apartamentoLimpio == null) {
                $apartamentoLimpieza = ApartamentoLimpieza::create([
                    'apartamento_id' => $nuevoID,
                    'fecha_comienzo' => Carbon::now(),
                    'status_id' => 2,
                    'reserva_id' => null,
                    'user_id' => Auth::user()->id
                ]);

            } else {
                $apartamentoLimpieza = $apartamentoLimpio;
            }
        } else {
            $reserva = Reserva::find($id);
            $apartamentoLimpio = ApartamentoLimpieza::where('fecha_fin', null)->where('apartamento_id', $reserva->apartamento_id)->first();

                if ($apartamentoLimpio == null) {
                    $apartamentoLimpieza = ApartamentoLimpieza::create([
                        'apartamento_id' => $reserva->apartamento_id,
                        'fecha_comienzo' => Carbon::now(),
                        'status_id' => 2,
                        'reserva_id' => $id,
                        'user_id' => Auth::user()->id
                    ]);
                    $reserva->fecha_limpieza = Carbon::now();
                    $reserva->save();
                } else {
                    $apartamentoLimpieza = $apartamentoLimpio;
                }
        }
        $apartamentoId = Reserva::find($id)->apartamento_id;
        $edificioId = Apartamento::find($apartamentoId)->edificio_id;

        $checklists = Checklist::with('items')->where('edificio_id', $edificioId)->get();
        $item_check = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)->get();
        $itemsExistentes = $item_check->pluck('estado', 'item_id')->toArray();

        // $apartamento = Apartamento::find($id);
        return view('gestion.edit', compact('apartamentoLimpieza','id', 'checklists', 'itemsExistentes'));
    }

    public function store(Request $request)
    {
        $id = $request->id;
        $apartamento = ApartamentoLimpieza::find($id);

        if (!$apartamento) {
            Alert::error('Error', 'Apartamento no encontrado');
            return redirect()->route('gestion.index');
        }

        // // Eliminar registros anteriores para este apartamento y limpieza
        // ApartamentoLimpiezaItem::where('id_limpieza', $apartamento->id)->delete();

        // Guardar los nuevos ítems marcados en el formulario
        if ($request->has('items')) {
            foreach ($request->items as $itemId => $estado) {
                ApartamentoLimpiezaItem::create([
                    'id_limpieza' => $apartamento->id,
                    'id_reserva' => $apartamento->reserva_id,
                    'item_id' => $itemId,
                    'estado' => $estado == 1 ? 1 : 0,
                ]);
            }
            foreach ($request->checklist as $checklistId => $estado) {
                ApartamentoLimpiezaItem::create([
                    'id_limpieza' => $apartamento->id,
                    'id_reserva' => $apartamento->reserva_id,
                    'estado' => $estado == 1 ? 1 : 0,
                    'checklist_id' => $checklistId
                ]);
            }
        }

        // Guardar observación
        $apartamento->observacion = $request->observacion;

        // Asignar el usuario si no existe
        if (empty($apartamento->user_id)) {
            $apartamento->user_id = Auth::user()->id;
        }

        $apartamento->save();

        Alert::success('Guardado con Éxito', 'Apartamento actualizado correctamente');
        return redirect()->route('gestion.index');
    }


    /**
     * Display the specified resource.
     */
    public function storeColumn(Request $request)
    {
        $apartamento = ApartamentoLimpieza::find($request->id);

        if ($apartamento) {
            $columna = $request->name;
            $apartamento->$columna = $request->checked == 'true' ? true : false;
            $apartamento->save();
            Alert::toast('Actualizado', 'success');
            return true;

        }
        Alert::toast('Error, intentelo mas tarde', 'error');

        return false;
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
    public function edit(ApartamentoLimpieza $apartamentoLimpieza)
    {
        ApartamentoLimpieza::find($apartamentoLimpieza);

        $id = $apartamentoLimpieza->id;
        $apartamentoId = $apartamentoLimpieza->apartamento_id;
        $edificioId = Apartamento::find($apartamentoId)->edificio_id;

        $checklists = Checklist::with('items')->where('edificio_id', $edificioId)->get();
        $item_check = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)->get();
        $itemsExistentes = $item_check->pluck('estado', 'item_id')->toArray();
        $checklist_check = $item_check->whereNotNull('checklist_id')->filter(function ($item) {
            return $item->estado == 1;
        });

        $checklistsExistentes = $checklist_check->pluck('estado', 'checklist_id')->toArray();
                // $apartamento = Apartamento::find($id);
        return view('gestion.edit', compact('apartamentoLimpieza','id', 'checklists', 'itemsExistentes', 'checklistsExistentes'));
    }


    public function update(Request $request, ApartamentoLimpieza $apartamentoLimpieza)
{
    // Eliminar ítems anteriores para este registro
    // ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)->delete();

    // Guardar nuevos ítems desde los checkboxes de ítems
    if ($request->has('items')) {
        foreach ($request->items as $itemId => $estado) {
            ApartamentoLimpiezaItem::create([
                'id_limpieza'   => $apartamentoLimpieza->id,
                'id_reserva'    => $apartamentoLimpieza->reserva_id,
                'item_id'       => $itemId,
                'estado'        => $estado == 1 ? 1 : 0,
            ]);
        }
    }

    // Guardar nuevos ítems desde los checkboxes de checklist
    if ($request->has('checklist')) {
        foreach ($request->checklist as $checklistId => $estado) {
            ApartamentoLimpiezaItem::create([
                'id_limpieza'   => $apartamentoLimpieza->id,
                'id_reserva'    => $apartamentoLimpieza->reserva_id,
                'checklist_id'  => $checklistId,
                'estado'        => $estado == 1 ? 1 : 0,
            ]);
        }
    }

    // Guardar observación
    $apartamentoLimpieza->observacion = $request->observacion;
    $apartamentoLimpieza->save();

    $id = $apartamentoLimpieza->id;
    Alert::success('Guardado con Éxito', 'Apartamento actualizado correctamente');

    $apartamentoId = $apartamentoLimpieza->apartamento_id;
    $edificioId = Apartamento::find($apartamentoId)->edificio_id;

    $checklists = Checklist::with('items')->where('edificio_id', $edificioId)->get();
    $item_check = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)->get();

    $itemsExistentes = $item_check->pluck('estado', 'item_id')->toArray();
    $checklist_check = $item_check->whereNotNull('checklist_id')->filter(function ($item) {
        return $item->estado == 1;
    });
    $checklistsExistentes = $checklist_check->pluck('estado', 'checklist_id')->toArray();

    return view('gestion.edit', compact(
        'apartamentoLimpieza',
        'id',
        'checklists',
        'itemsExistentes',
        'checklistsExistentes'
    ));
}



    /**
     * Remove the specified resource from storage.
     */
    public function finalizar(ApartamentoLimpieza $apartamentoLimpieza)
    {
        $hoy = Carbon::now();
        $apartamentoLimpieza->status_id = 3;
        $apartamentoLimpieza->fecha_fin = $hoy;
        $apartamentoLimpieza->save();
        $reserva = Reserva::find($apartamentoLimpieza->reserva_id);
        if ($reserva != null) {
            $reserva->fecha_limpieza = $hoy;
            $reserva->save();
        }
        // dd($reserva);
        Alert::success('Fizalizado con Exito', 'Apartamento Fizalizado correctamente');

        return redirect()->route('gestion.index');
    }

    public function limpiezaFondo(Request $request) {
        $apartamentos = LimpiezaFondo::all();
        return view('admin.limpieza.index', compact('apartamentos'));

    }

    public function limpiezaFondoDestroy($id) {
        $limpieza = LimpiezaFondo::find($id);
        $limpieza->delete();
        return redirect(route('admin.limpiezaFondo.index'));

    }

    public function limpiezaCreate(Request $request) {
        $apartamentos = Apartamento::all();
        return view('admin.limpieza.create', compact('apartamentos'));

    }
    public function limpiezaFondoEdit($id) {
        $limpieza = LimpiezaFondo::find($id);
        $apartamentos = Apartamento::all();

        return view('admin.limpieza.edit', compact('apartamentos', 'limpieza'));

    }

    public function limpiezaFondoStore(Request $request) {
        $rules = [
            'fecha' => 'required|date',
            'apartamento_id' => 'required'
        ];

        // Validar los datos del formulario
        $validatedData = $request->validate($rules);
        $limpiezaAFondo = LimpiezaFondo::create([
            'apartamento_id' => $request->apartamento_id,
            'fecha' => $request->fecha
        ]);
        Alert::success('Fizalizado con Exito', 'Apartamento Fizalizado correctamente');

        return redirect()->route('admin.limpiezaFondo.index');
    }

    /**
     * Update checkbox state via AJAX
     */
    public function updateCheckbox(Request $request)
    {
        try {
            $type = $request->input('type');
            $id = $request->input('id');
            $checked = $request->input('checked');
            $limpiezaId = $request->input('limpieza_id');
            $idReserva = ApartamentoLimpieza::find($limpiezaId)->reserva_id;
            if ($type === 'checklist') {
                // Actualizar estado del checklist
                $limpiezaItem = ApartamentoLimpiezaItem::where('id_limpieza', $limpiezaId)
                    ->where('checklist_id', $id)
                    ->first();

                if (!$limpiezaItem) {
                    // Si no existe, crear nuevo registro
                    $limpiezaItem = new ApartamentoLimpiezaItem([
                        'id_limpieza' => $limpiezaId,
                        'checklist_id' => $id,
                        'id_reserva' => $idReserva,
                        'estado' => $checked
                    ]);
                } else {
                    $limpiezaItem->estado = $checked;
                }

                $limpiezaItem->save();
            } else if ($type === 'item') {
                // Actualizar estado del item
                $limpiezaItem = ApartamentoLimpiezaItem::where('id_limpieza', $limpiezaId)
                    ->where('item_id', $id)
                    ->first();

                if (!$limpiezaItem) {
                    // Si no existe, crear nuevo registro
                    $limpiezaItem = new ApartamentoLimpiezaItem([
                        'id_limpieza' => $limpiezaId,
                        'item_id' => $id,
                        'id_reserva' => $idReserva,
                        'estado' => $checked
                    ]);
                } else {
                    $limpiezaItem->estado = $checked;
                }

                $limpiezaItem->save();
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
