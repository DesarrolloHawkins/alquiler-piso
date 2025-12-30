<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cupon;
use App\Models\Apartamento;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CuponesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cupones = Cupon::orderBy('created_at', 'desc')->get();
        return view('admin.cupones.index', compact('cupones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $apartamentos = Apartamento::orderBy('titulo')->get();
        return view('admin.cupones.create', compact('apartamentos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Normalizar el campo activo antes de la validación
            // Si el checkbox no está marcado, no se envía, así que lo establecemos a false
            $request->merge(['activo' => $request->has('activo')]);

            $validated = $request->validate([
                'codigo' => 'required|string|max:50|unique:cupones,codigo',
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'tipo' => 'required|in:porcentaje,fijo',
                'valor' => 'required|numeric|min:0',
                'descuento_maximo' => 'nullable|numeric|min:0',
                'importe_minimo' => 'nullable|numeric|min:0',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'usos_maximos' => 'nullable|integer|min:1',
                'usos_por_cliente' => 'required|integer|min:1',
                'activo' => 'required|boolean',
                'apartamentos_ids' => 'nullable|array',
                'apartamentos_ids.*' => 'exists:apartamentos,id',
            ]);

            // Convertir apartamentos_ids a JSON si existe
            if (isset($validated['apartamentos_ids']) && !empty($validated['apartamentos_ids'])) {
                $validated['apartamentos_ids'] = array_map('intval', $validated['apartamentos_ids']);
            } else {
                $validated['apartamentos_ids'] = null;
            }

            $validated['usos_actuales'] = 0;

            Cupon::create($validated);

            Alert::success('Éxito', 'Cupón creado correctamente');
            return redirect()->route('admin.cupones.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Error al crear cupón: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            Alert::error('Error', 'No se pudo crear el cupón: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Cupon $cupone)
    {
        $cupone->load('pagos.reserva', 'pagos.cliente');
        return view('admin.cupones.show', compact('cupone'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cupon $cupone)
    {
        $apartamentos = Apartamento::orderBy('titulo')->get();
        return view('admin.cupones.edit', compact('cupone', 'apartamentos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cupon $cupone)
    {
        try {
            // Normalizar el campo activo antes de la validación
            $request->merge(['activo' => $request->has('activo')]);

            $validated = $request->validate([
                'codigo' => 'required|string|max:50|unique:cupones,codigo,' . $cupone->id,
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'tipo' => 'required|in:porcentaje,fijo',
                'valor' => 'required|numeric|min:0',
                'descuento_maximo' => 'nullable|numeric|min:0',
                'importe_minimo' => 'nullable|numeric|min:0',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'usos_maximos' => 'nullable|integer|min:1',
                'usos_por_cliente' => 'required|integer|min:1',
                'activo' => 'required|boolean',
                'apartamentos_ids' => 'nullable|array',
                'apartamentos_ids.*' => 'exists:apartamentos,id',
            ]);

            // Convertir apartamentos_ids a JSON si existe
            if (isset($validated['apartamentos_ids']) && !empty($validated['apartamentos_ids'])) {
                $validated['apartamentos_ids'] = array_map('intval', $validated['apartamentos_ids']);
            } else {
                $validated['apartamentos_ids'] = null;
            }

            $cupone->update($validated);

            Alert::success('Éxito', 'Cupón actualizado correctamente');
            return redirect()->route('admin.cupones.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error al actualizar cupón: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            Alert::error('Error', 'No se pudo actualizar el cupón: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cupon $cupone)
    {
        // Verificar si tiene pagos asociados
        if ($cupone->pagos()->count() > 0) {
            Alert::error('Error', 'No se puede eliminar el cupón porque tiene pagos asociados');
            return redirect()->route('admin.cupones.index');
        }

        $cupone->delete();

        Alert::success('Éxito', 'Cupón eliminado correctamente');
        return redirect()->route('admin.cupones.index');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(Cupon $cupone)
    {
        $cupone->update(['activo' => !$cupone->activo]);
        
        Alert::success('Éxito', $cupone->activo ? 'Cupón activado' : 'Cupón desactivado');
        return redirect()->route('admin.cupones.index');
    }
}
