<?php

namespace App\Http\Controllers;

use App\Models\Presupuesto;
use App\Models\PresupuestoConcepto;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PresupuestoController extends Controller
{
    /**
     * Mostrar la lista de presupuestos.
     */
    public function index()
    {
        $presupuestos = Presupuesto::with('cliente')->paginate(10);
        return view('admin.presupuestos.index', compact('presupuestos'));
    }

    /**
     * Mostrar el formulario para crear un presupuesto.
     */
    public function create()
    {
        $clientes = Cliente::all();
        return view('admin.presupuestos.create', compact('clientes'));
    }

    /**
     * Almacenar un presupuesto en la base de datos.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'cliente_id' => 'nullable|exists:clientes,id',
        'fecha' => 'required|date',
        'conceptos' => 'required|array',
        'conceptos.*.concepto' => 'required|string|max:255',
        'conceptos.*.fecha_entrada' => 'required|date',
        'conceptos.*.fecha_salida' => 'required|date|after:conceptos.*.fecha_entrada',
        'conceptos.*.precio_por_dia' => 'required|numeric|min:0',
    ]);

    $presupuesto = Presupuesto::create([
        'cliente_id' => $validated['cliente_id'],
        'fecha' => $validated['fecha'],
    ]);

    foreach ($validated['conceptos'] as $conceptoData) {
        $concepto = new PresupuestoConcepto($conceptoData);
        $concepto->calcularPrecioTotal(); // Calcula días y precio total
        $presupuesto->conceptos()->save($concepto);
    }

    return redirect()->route('presupuestos.index')->with('success', 'Presupuesto creado correctamente.');
}


    /**
     * Mostrar el detalle de un presupuesto.
     */
    public function show($id)
    {
        $presupuesto = Presupuesto::with('cliente', 'conceptos')->findOrFail($id);
        return view('admin.presupuestos.show', compact('presupuesto'));
    }

    /**
     * Mostrar el formulario para editar un presupuesto.
     */
    public function edit($id)
    {
        $presupuesto = Presupuesto::with('conceptos')->findOrFail($id);
        $clientes = Cliente::all();
        return view('admin.presupuestos.edit', compact('presupuesto', 'clientes'));
    }

    /**
     * Actualizar un presupuesto en la base de datos.
     */
    public function update(Request $request, $id)
    {
        $presupuesto = Presupuesto::findOrFail($id);

        $request->validate([
            'conceptos.*.concepto' => 'required|string|max:255',
            'conceptos.*.precio' => 'required|numeric|min:0',
            'conceptos.*.iva' => 'required|numeric|min:0',
            'conceptos.*.subtotal' => 'required|numeric|min:0',
            'cliente_id' => 'nullable|exists:clientes,id',
        ]);

        // Actualizar cliente si es necesario
        if (!$request->cliente_id) {
            $cliente = Cliente::create([
                'nombre' => $request->nombre,
                'apellido1' => $request->apellido1,
                'apellido2' => $request->apellido2,
                'email' => $request->email,
            ]);

            $clienteId = $cliente->id;
        } else {
            $clienteId = $request->cliente_id;
        }

        // Actualizar presupuesto
        $presupuesto->update([
            'cliente_id' => $clienteId,
            'descripcion' => $request->descripcion,
            'total' => collect($request->conceptos)->sum('subtotal'),
        ]);

        // Eliminar conceptos existentes y volver a crearlos
        $presupuesto->conceptos()->delete();

        foreach ($request->conceptos as $concepto) {
            PresupuestoConcepto::create([
                'presupuesto_id' => $presupuesto->id,
                'concepto' => $concepto['concepto'],
                'precio' => $concepto['precio'],
                'iva' => $concepto['iva'],
                'subtotal' => $concepto['subtotal'],
            ]);
        }

        return redirect()->route('presupuestos.index')->with('success', 'Presupuesto actualizado correctamente.');
    }

    /**
     * Eliminar un presupuesto.
     */
    public function destroy($id)
    {
        $presupuesto = Presupuesto::findOrFail($id);
        $presupuesto->delete();

        return redirect()->route('presupuestos.index')->with('success', 'Presupuesto eliminado correctamente.');
    }
}
