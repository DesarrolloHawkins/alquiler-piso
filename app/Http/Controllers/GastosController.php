<?php

namespace App\Http\Controllers;

use App\Models\Bancos;
use App\Models\CategoriaGastos;
use App\Models\EstadosGastos;
use App\Models\Gastos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GastosController extends Controller
{
    public function index(Request $request) {
        $search = $request->get('search');
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'asc');
        $month = $request->get('month');
        $category = $request->get('category');  // Nuevo parámetro para la categoría
        $perPage = $request->get('perPage', 10);
        $estado_id = $request->get('estado_id');  // Nuevo parámetro para la categoría

        $query = Gastos::where(function ($query) use ($search, $month, $category, $estado_id) {
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
        $gastos = $query->orderBy($sort, $order)->paginate($perPage);
    
        // Pasamos también las categorías a la vista para el selector
        $categorias = CategoriaGastos::all();
        $estados = EstadosGastos::all();

        return view('admin.gastos.index', compact('gastos', 'totalQuantity', 'categorias', 'estados'));
    }   

    public function create(){
        $categorias = CategoriaGastos::all();
        $bancos = Bancos::all();
        $estados = EstadosGastos::all();

        return view('admin.gastos.create', compact('categorias','bancos', 'estados'));
    }

    public function store(Request $request){
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
    
        // Crear el gasto en la base de datos sin la foto
        $gasto = Gastos::create($validatedData);
        
        // Manejar la carga de la foto si existe
        if ($request->hasFile('factura_foto')) {
            if ($request->file('factura_foto')->isValid()) {
                $file = $request->file('factura_foto');
                $filename = time() . '_' . $file->getClientOriginalName(); // Crear un nombre de archivo único
                $path = $file->storeAs('public/facturas', $filename); // Guardar el archivo en el storage
    
                // Actualizar la instancia de gasto con la ruta de la foto
                $gasto->factura_foto = $path;
                $gasto->save(); // Guardar el path en la columna factura_foto
            }
        }
    
        // Redireccionar al índice de gastos con un mensaje de éxito
        return redirect()->route('admin.gastos.index')->with('status', 'Gasto creado con éxito!');
    }
    
    
    public function edit($id)
    {
        $gasto = Gastos::findOrFail($id);  // Asegúrate de usar findOrFail para manejar errores si el ID no existe
        $categorias = CategoriaGastos::all();
        $bancos = Bancos::all();  // Asegúrate de tener el modelo y controlador para Bancos también
        $estados = EstadosGastos::all();
        return view('admin.gastos.edit', compact('gasto', 'categorias', 'bancos', 'estados'));
    }

    public function update(Request $request, $id)
    {
        $gasto = Gastos::findOrFail($id); // Asegúrate de obtener el gasto existente
    
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
    
        // Actualizar el gasto en la base de datos sin la foto
        $gasto->update($validatedData);
        
        // Manejar la carga de la foto si existe
        if ($request->hasFile('factura_foto')) {
            if ($request->file('factura_foto')->isValid()) {
                // Opcional: Eliminar el archivo antiguo si existe
                if ($gasto->factura_foto) {
                    Storage::delete($gasto->factura_foto);
                }
    
                $file = $request->file('factura_foto');
                $filename = time() . '_' . $file->getClientOriginalName(); // Crear un nombre de archivo único
                $path = $file->storeAs('public/facturas', $filename); // Guardar el archivo en el storage
    
                // Actualizar la instancia de gasto con la ruta de la foto
                $gasto->factura_foto = $path;
                $gasto->save(); // Guardar el path en la columna factura_foto
            }
        }
    
        // Redireccionar al índice de gastos con un mensaje de éxito
        return redirect()->route('admin.gastos.index')->with('status', 'Gasto actualizado con éxito!');
    }
    
    public function destroy(Gastos $categoria){
        $categoria->delete();
        return redirect()->route('admin.gastos.index')->with('status', 'Gasto eliminada con éxito!');
    }
    public function clasificarGastos(Request $request){
        $origen = $request->Origen;
        $contenido  = $request->Contenido;
        // $tipo = $request->Tipo;
        $importe = $request->Importe;
        $fecha = $request->Fecha;
        $crearGasto = Gastos::create([
            'title' => $contenido,
            'quantity' => $importe,
            'date' => $fecha,
            'estado_id' => 1
        ]);

        return response()->json([
            'mensaje' => 'El gasto se añadio correctamente'
        ]);

        // if($tipo == 0){

        // }

    }

    public function download($id)
    {
        $gasto = Gastos::findOrFail($id);
        if (!$gasto->factura_foto) {
            return abort(404);
        }
    
        $pathToFile = storage_path('app/' . $gasto->factura_foto);
        return response()->download($pathToFile);
    }
}
