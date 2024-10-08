<?php

namespace App\Http\Controllers;

use App\Models\Bancos;
use App\Models\CategoriaIngresos;
use App\Models\DiarioCaja;
use App\Models\EstadosIngresos;
use App\Models\Ingresos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IngresosController extends Controller
{
    public function index(Request $request) {
        $search = $request->get('search');
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'asc');
        $month = $request->get('month');
        $category = $request->get('category');  // Nuevo parámetro para la categoría
        $perPage = $request->get('perPage', 10);
        $estado_id = $request->get('estado_id');  // Nuevo parámetro para la categoría

        $query = Ingresos::where(function ($query) use ($search, $month, $category, $estado_id) {
            $query->where('title', 'like', '%'.$search.'%');
            if ($month) {
                $query->whereMonth('date', $month);
            }
            if ($category) {
                $query->where('categoria_id', $category); // Filtrar por categoría
            }
            if ($estado_id) {
                $query->where('estado_id', $estado_id); // Filtrar por categoría
            }
        });
    
        $totalQuantity = $query->sum('quantity');
        $ingresos = $query->orderBy($sort, $order)->paginate($perPage);
    
        // Pasamos también las categorías a la vista para el selector
        $categorias = CategoriaIngresos::all();
        $estados = EstadosIngresos::all();

        return view('admin.ingresos.index', compact('ingresos', 'totalQuantity', 'categorias', 'estados'));
    }   

    public function create(){
        $categorias = CategoriaIngresos::all();
        $bancos = Bancos::all();
        $estados = EstadosIngresos::all();

        return view('admin.ingresos.create', compact('categorias','bancos', 'estados'));
    }

    public function store(Request $request)
    {
        $rules = [
            'estado_id' => 'required|exists:estados_gastos,id',
            'categoria_id' => 'required|exists:categoria_gastos,id',
            'bank_id' => 'required|exists:bank_accounts,id',
            'title' => 'required|string|max:255',
            'date' => 'required',
            'quantity' => 'required|numeric'
        ];

        // Validar los datos del formulario
        $validatedData = $request->validate($rules);

        // Crear el ingreso en la base de datos sin la foto
        $ingreso = Ingresos::create($validatedData);

        // Manejar la carga de la foto si existe
        if ($request->hasFile('factura_foto')) {
            if ($request->file('factura_foto')->isValid()) {
                $file = $request->file('factura_foto');
                $filename = time() . '_' . $file->getClientOriginalName(); // Crear un nombre de archivo único
                $path = $file->storeAs('public/facturas', $filename); // Guardar el archivo en el storage

                // Actualizar la instancia de ingreso con la ruta de la foto
                $ingreso->factura_foto = $path;
                $ingreso->save(); // Guardar el path en la columna factura_foto
            }
        }

        // Registrar el ingreso en el Diario de Caja
        DiarioCaja::create([
            'asiento_contable' => $this->generarAsientoContable(),
            'cuenta_id' => $validatedData['categoria_id'],
            'ingreso_id' => $ingreso->id,
            'date' => Carbon::parse($validatedData['date']),
            'concepto' => $validatedData['title'],
            'haber' => $validatedData['quantity'],
            'tipo' => 'ingreso',
            'estado_id' => $validatedData['estado_id']
        ]);

        // Redireccionar al índice de ingresos con un mensaje de éxito
        return redirect()->route('admin.ingresos.index')->with('status', 'Ingreso creado con éxito!');
    }

    
    
    public function edit($id)
    {
        $ingreso = Ingresos::findOrFail($id);  // Asegúrate de usar findOrFail para manejar errores si el ID no existe
        $categorias = CategoriaIngresos::all();
        $bancos = Bancos::all();  // Asegúrate de tener el modelo y controlador para Bancos también
        $estados = EstadosIngresos::all();
        return view('admin.ingresos.edit', compact('ingreso', 'categorias', 'bancos', 'estados'));
    }

    public function update(Request $request, $id)
    {
        $ingreso = Ingresos::findOrFail($id); // Obtener el ingreso existente
        
        $rules = [
            'estado_id' => 'required|exists:estados_gastos,id',
            'categoria_id' => 'required|exists:categoria_gastos,id',
            'bank_id' => 'required|exists:bank_accounts,id',
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'quantity' => 'required|numeric'
        ];
        
        // Validar los datos del formulario
        $validatedData = $request->validate($rules);
        
        // Actualizar el ingreso en la base de datos sin la foto
        $ingreso->update($validatedData);
        
        // Manejar la carga de la foto si existe
        if ($request->hasFile('factura_foto')) {
            if ($request->file('factura_foto')->isValid()) {
                // Eliminar el archivo antiguo si existe
                if ($ingreso->factura_foto) {
                    Storage::delete($ingreso->factura_foto);
                }

                $file = $request->file('factura_foto');
                $filename = time() . '_' . $file->getClientOriginalName(); // Crear un nombre de archivo único
                $path = $file->storeAs('public/facturas', $filename); // Guardar el archivo en el storage

                // Actualizar la instancia de ingreso con la ruta de la foto
                $ingreso->factura_foto = $path;
                $ingreso->save(); // Guardar el path en la columna factura_foto
            }
        }

        // Actualizar también el Diario de Caja correspondiente
        $diarioCaja = DiarioCaja::where('ingreso_id', $id)->first();
        if ($diarioCaja) {
            $diarioCaja->update([
                'date' => $validatedData['date'],
                'concepto' => $validatedData['title'],
                'haber' => $validatedData['quantity']
            ]);
        }

        // Redireccionar al índice de ingresos con un mensaje de éxito
        return redirect()->route('admin.ingresos.index')->with('status', 'Ingreso actualizado con éxito!');
    }

    
    public function destroy($id){
        $ingreso = Ingresos::findOrFail($id);

        // Buscar y eliminar la referencia en el Diario de Caja
        $diarioCaja = DiarioCaja::where('ingreso_id', $id)->first();
        if ($diarioCaja) {
            $diarioCaja->delete();
        }
    
        // Eliminar el ingreso
        $ingreso->delete();
    
        return redirect()->route('admin.ingresos.index')->with('status', 'Ingreso eliminado con éxito.');
    }

    public function clasificarIngresos(Request $request){
        $origen = $request->Origen;
        $contenido  = $request->Contenido;
        // $tipo = $request->Tipo;
        $importe = $request->Importe;
        $fecha = $request->Fecha;

        $crearGasto = Ingresos::create([
            'title' => $contenido,
            'quantity' => $importe,
            'date' => $fecha,
            'estado_id' => 1
        ]);
        return response()->json([
            'mensaje' => 'El ingreso se añadio correctamente'
        ]);

    }

    public function download($id)
    {
        $gasto = Ingresos::findOrFail($id);
        if (!$gasto->factura_foto) {
            return abort(404);
        }
    
        $pathToFile = storage_path('app/' . $gasto->factura_foto);
        return response()->download($pathToFile);
    }
}
