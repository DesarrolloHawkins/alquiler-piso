<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MovimientoStock;
use App\Models\Articulo;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MovimientoStockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MovimientoStock::with(['articulo', 'proveedor', 'user', 'apartamentoLimpieza']);

        // Filtros
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('articulo_id')) {
            $query->where('articulo_id', $request->articulo_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->where('fecha_movimiento', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_movimiento', '<=', $request->fecha_hasta);
        }

        if ($request->filled('motivo')) {
            $query->where('motivo', 'like', '%' . $request->motivo . '%');
        }

        $movimientos = $query->orderBy('fecha_movimiento', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Datos para filtros
        $articulos = Articulo::activos()->orderBy('nombre')->get();
        $proveedores = Proveedor::activos()->orderBy('nombre')->get();
        
        $tipos = ['entrada', 'salida', 'ajuste'];
        $motivos = MovimientoStock::distinct()->pluck('motivo')->filter()->sort()->values();

        // Estadísticas
        $estadisticas = [
            'total_movimientos' => MovimientoStock::count(),
            'entradas' => MovimientoStock::entradas()->count(),
            'salidas' => MovimientoStock::salidas()->count(),
            'ajustes' => MovimientoStock::ajustes()->count()
        ];

        return view('admin.movimientos-stock.index', compact(
            'movimientos', 
            'articulos', 
            'proveedores', 
            'tipos', 
            'motivos', 
            'estadisticas'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $articulos = Articulo::activos()->orderBy('nombre')->get();
        $proveedores = Proveedor::activos()->orderBy('nombre')->get();
        
        return view('admin.movimientos-stock.create', compact('articulos', 'proveedores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'articulo_id' => 'required|exists:articulos,id',
            'tipo' => 'required|in:entrada,salida,ajuste',
            'cantidad' => 'required|numeric|min:0.01',
            'precio_unitario' => 'nullable|numeric|min:0',
            'motivo' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'proveedor_id' => 'nullable|exists:proveedors,id',
            'numero_factura' => 'nullable|string|max:100',
            'fecha_movimiento' => 'required|date'
        ]);

        try {
            $articulo = Articulo::findOrFail($request->articulo_id);

            switch ($request->tipo) {
                case 'entrada':
                    MovimientoStock::crearEntrada(
                        $request->articulo_id,
                        $request->cantidad,
                        $request->precio_unitario,
                        $request->motivo ?? 'entrada_manual',
                        $request->observaciones,
                        $request->proveedor_id,
                        $request->numero_factura
                    );
                    break;

                case 'salida':
                    if ($articulo->stock_actual < $request->cantidad) {
                        return redirect()->back()
                            ->withInput()
                            ->with('swal_error', 'No hay suficiente stock. Stock actual: ' . $articulo->stock_actual);
                    }

                    MovimientoStock::crearSalida(
                        $request->articulo_id,
                        $request->cantidad,
                        $request->motivo ?? 'salida_manual',
                        $request->observaciones
                    );
                    break;

                case 'ajuste':
                    MovimientoStock::crearAjuste(
                        $request->articulo_id,
                        $request->cantidad,
                        $request->motivo ?? 'ajuste_manual',
                        $request->observaciones
                    );
                    break;
            }

            return redirect()->route('admin.movimientos-stock.index')
                ->with('swal_success', 'Movimiento creado correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('swal_error', 'Error al crear el movimiento: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MovimientoStock $movimientoStock)
    {
        $movimientoStock->load(['articulo', 'proveedor', 'user', 'apartamentoLimpieza']);
        
        return view('admin.movimientos-stock.show', compact('movimientoStock'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MovimientoStock $movimientoStock)
    {
        // Los movimientos no se pueden editar por integridad de datos
        return redirect()->back()
            ->with('swal_error', 'Los movimientos de stock no se pueden editar para mantener la integridad de los datos.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MovimientoStock $movimientoStock)
    {
        // Los movimientos no se pueden editar por integridad de datos
        return redirect()->back()
            ->with('swal_error', 'Los movimientos de stock no se pueden editar para mantener la integridad de los datos.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MovimientoStock $movimientoStock)
    {
        // Los movimientos no se pueden eliminar por integridad de datos
        return redirect()->back()
            ->with('swal_error', 'Los movimientos de stock no se pueden eliminar para mantener la integridad de los datos.');
    }

    /**
     * Exportar movimientos a Excel/CSV
     */
    public function exportar(Request $request)
    {
        // Implementar exportación si es necesario
        return redirect()->back()
            ->with('swal_info', 'Función de exportación pendiente de implementar.');
    }
}
