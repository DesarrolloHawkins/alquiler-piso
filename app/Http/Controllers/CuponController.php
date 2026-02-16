<?php

namespace App\Http\Controllers;

use App\Models\Cupon;
use App\Models\Apartamento;
use App\Models\Edificio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CuponController extends Controller
{
    /**
     * Mostrar listado de cupones
     */
    public function index(Request $request)
    {
        $query = Cupon::with('creador')->withCount('usos');

        // Filtros
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('codigo', 'like', "%{$buscar}%")
                    ->orWhere('nombre', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo === '1');
        }

        if ($request->filled('tipo_descuento')) {
            $query->where('tipo_descuento', $request->tipo_descuento);
        }

        $cupones = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.cupones.index', compact('cupones'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $cupon = new Cupon(); // Cupón vacío para el formulario
        $apartamentos = Apartamento::orderBy('titulo')->get();
        $edificios = Edificio::orderBy('nombre')->get();

        return view('admin.cupones.create', compact('cupon', 'apartamentos', 'edificios'));
    }

    /**
     * Guardar nuevo cupón
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:cupones,codigo',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo_descuento' => 'required|in:porcentaje,fijo',
            'valor_descuento' => 'required|numeric|min:0',
            'usos_maximos' => 'nullable|integer|min:1',
            'usos_por_cliente' => 'required|integer|min:1',
            'importe_minimo' => 'nullable|numeric|min:0',
            'descuento_maximo' => 'nullable|numeric|min:0',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'reserva_desde' => 'nullable|date',
            'reserva_hasta' => 'nullable|date|after_or_equal:reserva_desde',
            'noches_minimas' => 'nullable|integer|min:1',
            'apartamentos_ids' => 'nullable|array',
            'apartamentos_ids.*' => 'exists:apartamentos,id',
            'edificios_ids' => 'nullable|array',
            'edificios_ids.*' => 'exists:edificios,id',
            'activo' => 'boolean',
        ], [
            'codigo.required' => 'El código del cupón es obligatorio',
            'codigo.unique' => 'Ya existe un cupón con este código',
            'nombre.required' => 'El nombre del cupón es obligatorio',
            'tipo_descuento.required' => 'Debes seleccionar el tipo de descuento',
            'valor_descuento.required' => 'El valor del descuento es obligatorio',
            'valor_descuento.min' => 'El valor del descuento debe ser mayor a 0',
            'fecha_fin.after_or_equal' => 'La fecha fin debe ser posterior a la fecha inicio',
            'reserva_hasta.after_or_equal' => 'La fecha hasta debe ser posterior a la fecha desde',
        ]);

        // Convertir código a mayúsculas
        $validated['codigo'] = strtoupper($validated['codigo']);
        $validated['creado_por'] = Auth::id();
        $validated['activo'] = $request->has('activo');

        $cupon = Cupon::create($validated);

        Log::info('Cupón creado', [
            'cupon_id' => $cupon->id,
            'codigo' => $cupon->codigo,
            'usuario_id' => Auth::id(),
        ]);

        return redirect()->route('admin.cupones.index')
            ->with('success', 'Cupón creado correctamente: ' . $cupon->codigo);
    }

    /**
     * Mostrar detalles del cupón
     */
    public function show(Cupon $cupon)
    {
        $cupon->load(['creador', 'usos.reserva', 'usos.cliente']);

        return view('admin.cupones.show', compact('cupon'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Cupon $cupon)
    {
        $apartamentos = Apartamento::orderBy('titulo')->get();
        $edificios = Edificio::orderBy('nombre')->get();

        return view('admin.cupones.edit', compact('cupon', 'apartamentos', 'edificios'));
    }

    /**
     * Actualizar cupón
     */
    public function update(Request $request, Cupon $cupon)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:cupones,codigo,' . $cupon->id,
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo_descuento' => 'required|in:porcentaje,fijo',
            'valor_descuento' => 'required|numeric|min:0',
            'usos_maximos' => 'nullable|integer|min:1',
            'usos_por_cliente' => 'required|integer|min:1',
            'importe_minimo' => 'nullable|numeric|min:0',
            'descuento_maximo' => 'nullable|numeric|min:0',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'reserva_desde' => 'nullable|date',
            'reserva_hasta' => 'nullable|date|after_or_equal:reserva_desde',
            'noches_minimas' => 'nullable|integer|min:1',
            'apartamentos_ids' => 'nullable|array',
            'apartamentos_ids.*' => 'exists:apartamentos,id',
            'edificios_ids' => 'nullable|array',
            'edificios_ids.*' => 'exists:edificios,id',
            'activo' => 'boolean',
        ]);

        $validated['codigo'] = strtoupper($validated['codigo']);
        $validated['activo'] = $request->has('activo');

        $cupon->update($validated);

        Log::info('Cupón actualizado', [
            'cupon_id' => $cupon->id,
            'codigo' => $cupon->codigo,
            'usuario_id' => Auth::id(),
        ]);

        return redirect()->route('admin.cupones.index')
            ->with('success', 'Cupón actualizado correctamente');
    }

    /**
     * Eliminar cupón
     */
    public function destroy(Cupon $cupon)
    {
        $codigo = $cupon->codigo;
        
        $cupon->delete();

        Log::info('Cupón eliminado', [
            'cupon_id' => $cupon->id,
            'codigo' => $codigo,
            'usuario_id' => Auth::id(),
        ]);

        return redirect()->route('admin.cupones.index')
            ->with('success', 'Cupón eliminado correctamente');
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function toggleActivo(Cupon $cupon)
    {
        $cupon->activo = !$cupon->activo;
        $cupon->save();

        $estado = $cupon->activo ? 'activado' : 'desactivado';

        Log::info('Estado de cupón cambiado', [
            'cupon_id' => $cupon->id,
            'codigo' => $cupon->codigo,
            'nuevo_estado' => $estado,
            'usuario_id' => Auth::id(),
        ]);

        return redirect()->back()
            ->with('success', "Cupón {$estado} correctamente");
    }

    /**
     * Duplicar cupón
     */
    public function duplicate(Cupon $cupon)
    {
        $nuevoCupon = $cupon->replicate();
        $nuevoCupon->codigo = $cupon->codigo . '_COPIA';
        $nuevoCupon->nombre = $cupon->nombre . ' (Copia)';
        $nuevoCupon->usos_actuales = 0;
        $nuevoCupon->activo = false;
        $nuevoCupon->creado_por = Auth::id();
        $nuevoCupon->save();

        Log::info('Cupón duplicado', [
            'cupon_original_id' => $cupon->id,
            'cupon_nuevo_id' => $nuevoCupon->id,
            'usuario_id' => Auth::id(),
        ]);

        return redirect()->route('admin.cupones.edit', $nuevoCupon)
            ->with('success', 'Cupón duplicado correctamente. Recuerda cambiar el código antes de activarlo.');
    }
}
