<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\ApartamentoLimpieza;
use App\Models\Checklist;
use App\Models\Fichaje;
use App\Models\Pausa;
use App\Models\GestionApartamento;
use App\Models\LimpiezaFondo;
use App\Models\Photo;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Auth; // Añade esta línea

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

        // Obtener el edificio del apartamento y los checklists asociados a ese edificio
        $apartamento = Apartamento::find($apartamentoLimpieza->apartamento_id);
        $edificio = $apartamento->edificioName; // Relación con el modelo Edificio
        $checklists = Checklist::where('edificio_id', $edificio->id)->with('items')->get();

        return view('gestion.edit', compact('apartamentoLimpieza', 'checklists', 'id'));
    }


    public function uploadPhoto(Request $request, $id)
    {
        // dd($request->all());

        // Validar los campos necesarios
        $validatedData = $request->validate([
            'photo' => 'required|image|max:1024',  // Ajusta el tamaño máximo permitido si es necesario
            'requirement_id' => 'required|exists:checklist_photo_requirements,id',  // Asegura que el requirement_id exista en la base de datos
            // 'photo_categoria_id' => 'required|exists:photo_categorias,id'  // Asegura que la categoría exista en la base de datos
        ]);

        // Intentar almacenar la imagen en el disco y guardar la entrada en la base de datos
        try {
            $path = $request->file('photo')->store('photos', 'public');
            // Crear un nuevo registro en la base de datos para la foto
            $photo = new Photo([
                'limpieza_id' => $id,
                'requirement_id' => $request->input('requirement_id'),
                // 'photo_categoria_id' => $request->input('photo_categoria_id'),  // Guardar la categoría de la foto
                'url' => $path,
            ]);

            $photo->save();  // Guardar el objeto Photo

            // Devolver una respuesta JSON exitosa
            return response()->json([
                'success' => true,
                'message' => 'Foto subida exitosamente',
                'photo_id' => $photo->id  // Devolver el ID de la foto si es necesario
            ]);

        } catch (\Exception $e) {
            // Manejar cualquier excepción que pueda ocurrir durante el proceso de almacenamiento
            return response()->json([
                'success' => false,
                'message' => 'Error al subir la foto',
                'error' => $e->getMessage()  // Devolver el mensaje de error para diagnóstico
            ], 500);  // Código de estado HTTP para error interno del servidor
        }
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validación de datos
        $request->validate([
            'id' => 'required|exists:apartamentos_limpiezas,id',
            'items' => 'array' // Asegurarse que 'items' es un array de checkboxes
        ]);

        $apartamentoLimpieza = ApartamentoLimpieza::find($request->id);

        // Limpiar los controles previos
        $apartamentoLimpieza->controles()->detach(); // Esto limpiará los ítems anteriores relacionados a este apartamento

        // Almacenar los nuevos items seleccionados
        if ($request->has('items')) {
            foreach ($request->items as $itemId => $value) {
                $apartamentoLimpieza->controles()->attach($itemId, ['status' => true]); // Asume que status será true si el checkbox fue marcado
            }
        }

        Alert::success('Guardado con éxito', 'El checklist ha sido guardado correctamente');
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
        // Cargar el apartamento relacionado y el edificio
        $apartamento = $apartamentoLimpieza->apartamento;

        // Obtener el edificio relacionado
        $edificio = $apartamento->edificioName;

        // Obtener los checklists y sus items relacionados con el edificio
        $checklists = $edificio ? $edificio->checklists()->with('items', 'photoRequirements')->get() : collect();

        // Obtener fotos ya subidas relacionadas con esta limpieza
        $uploadedPhotos = Photo::where('limpieza_id', $apartamentoLimpieza->id)
                                ->get()
                                ->groupBy('photo_categoria_id');

        // Pasar todos los datos necesarios a la vista, incluyendo las fotos subidas
        return view('gestion.edit', compact('apartamentoLimpieza', 'checklists', 'uploadedPhotos'));
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validación de datos
        $request->validate([
            'id' => 'required|exists:apartamentos_limpiezas,id',
            'items' => 'array|required' // Asegurarse de que 'items' es un array de checkboxes
        ]);

        $apartamentoLimpieza = ApartamentoLimpieza::findOrFail($id);

        // Limpiar los controles previos
        $apartamentoLimpieza->controles()->detach(); // Elimina las asociaciones previas

        // Actualizar los nuevos items seleccionados
        if (!empty($request->items)) {
            foreach ($request->items as $itemId => $checked) {
                if ($checked) { // Asumiendo que los checkboxes envían un valor verdadero si están marcados
                    $apartamentoLimpieza->controles()->attach($itemId, ['status' => true]);
                }
            }
        }

        Alert::success('Actualizado con éxito', 'El checklist ha sido actualizado correctamente');
        return redirect()->route('gestion.index');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function finalizar(ApartamentoLimpieza $apartamentoLimpieza)
    {
        $apartamentoLimpieza->status_id = 3;
        $apartamentoLimpieza->fecha_fin = Carbon::now();
        $apartamentoLimpieza->save();
        $reserva = Reserva::find($apartamentoLimpieza->reserva_id);
        if ($reserva) {
            # code...
            $reserva->fecha_limpieza = Carbon::now();
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
}



