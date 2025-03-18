<?php

namespace App\Http\Controllers;

use App\Models\Anio;
use App\Models\Metalico;
use Illuminate\Http\Request;

class MetalicoController extends Controller
{
    public function index(Request $request)
    {
        // Obtener saldo inicial
        $anio = Anio::first();
        $saldoInicial = $anio->saldo_inicial_metalico ?? 0;

        // Filtrar registros
        $query = Metalico::query();

        if ($request->filled('start_date')) {
            $query->where('fecha_ingreso', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('fecha_ingreso', '<=', $request->end_date);
        }

        if ($request->filled('reserva_id')) {
            $query->where('reserva_id', $request->reserva_id);
        }

        if ($request->filled('titulo')) {
            $query->where('titulo', 'like', '%' . $request->titulo . '%');
        }

        // Ordenar registros
        $entries = $query->orderBy('id', 'asc')->get();

        // Inicializar saldo acumulado
        $saldoAcumulado = $saldoInicial;

        // Recorrer registros y calcular saldo acumulado
        foreach ($entries as $linea) {
            $importe = abs($linea->importe);

            if ($linea->tipo === 'gasto') {
                $saldoAcumulado -= $importe;
            } else {
                $saldoAcumulado += $importe;
            }

            $linea->saldo = $saldoAcumulado;
        }

        // Ordenar en orden descendente para la vista
        $response = $entries->sortByDesc('id');

        return view('metalicos.index', compact('response', 'saldoInicial'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'importe' => 'required|numeric',
            'fecha_ingreso' => 'required|date',
            'tipo' => 'required|in:ingreso,gasto',
            'observaciones' => 'nullable|string|max:500'
        ]);

        Metalico::create($request->all());

        return redirect()->route('metalicos.index')->with('success', 'Registro creado correctamente.');
    }


    public function create()
    {
        return view('metalicos.create');
    }

    public function store2(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'importe' => 'required|numeric',
            // 'reserva_id' => 'required|exists:reservas,id',
            'fecha_ingreso' => 'required|date',
        ]);
        $request['reserva_id'] = null;

        Metalico::create($request->all());

        return redirect()->route('metalicos.index')->with('success', 'Registro creado correctamente.');
    }

    public function show(Metalico $metalico)
    {
        return view('metalicos.show', compact('metalico'));
    }

    public function edit(Metalico $metalico)
    {
        return view('metalicos.edit', compact('metalico'));
    }

    public function update(Request $request, Metalico $metalico)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'importe' => 'required|numeric',
            'fecha_ingreso' => 'required|date',
            'tipo' => 'required|in:ingreso,gasto',
            'observaciones' => 'nullable|string|max:500'
        ]);

        $metalico->update($request->all());

        return redirect()->route('metalicos.index')->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(Metalico $metalico)
    {
        $metalico->delete();

        return redirect()->route('metalicos.index')->with('success', 'Registro eliminado correctamente.');
    }
}

