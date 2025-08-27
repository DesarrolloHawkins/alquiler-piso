<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionDescuento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConfiguracionDescuentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $configuraciones = ConfiguracionDescuento::orderBy('created_at', 'desc')->get();
        return view('admin.configuracion-descuentos.index', compact('configuraciones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.configuracion-descuentos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:configuracion_descuentos',
            'descripcion' => 'nullable|string',
            'porcentaje_descuento' => 'required|numeric|min:0|max:100',
            'activo' => 'boolean',
            'condiciones' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $configuracion = ConfiguracionDescuento::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'porcentaje_descuento' => $request->porcentaje_descuento,
            'activo' => $request->has('activo'),
            'condiciones' => $request->condiciones ?? [
                'dia_semana' => 'friday',
                'temporada' => 'baja',
                'dias_minimos_libres' => 1
            ]
        ]);

        return redirect()->route('configuracion-descuentos.index')
            ->with('success', 'Configuración de descuento creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ConfiguracionDescuento $configuracionDescuento)
    {
        return view('admin.configuracion-descuentos.show', compact('configuracionDescuento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ConfiguracionDescuento $configuracionDescuento)
    {
        return view('admin.configuracion-descuentos.edit', compact('configuracionDescuento'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ConfiguracionDescuento $configuracionDescuento)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:configuracion_descuentos,nombre,' . $configuracionDescuento->id,
            'descripcion' => 'nullable|string',
            'porcentaje_descuento' => 'required|numeric|min:0|max:100',
            'activo' => 'boolean',
            'condiciones' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $configuracionDescuento->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'porcentaje_descuento' => $request->porcentaje_descuento,
            'activo' => $request->has('activo'),
            'condiciones' => $request->condiciones ?? $configuracionDescuento->condiciones
        ]);

        return redirect()->route('configuracion-descuentos.index')
            ->with('success', 'Configuración de descuento actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ConfiguracionDescuento $configuracionDescuento)
    {
        // Verificar si tiene historial asociado
        if ($configuracionDescuento->historialDescuentos()->count() > 0) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar la configuración porque tiene historial de descuentos asociado.');
        }

        $configuracionDescuento->delete();

        return redirect()->route('configuracion-descuentos.index')
            ->with('success', 'Configuración de descuento eliminada exitosamente.');
    }

    /**
     * Toggle the active status of a configuration
     */
    public function toggleStatus(ConfiguracionDescuento $configuracionDescuento)
    {
        $configuracionDescuento->update([
            'activo' => !$configuracionDescuento->activo
        ]);

        $status = $configuracionDescuento->activo ? 'activada' : 'desactivada';
        
        return redirect()->back()
            ->with('success', "Configuración {$status} exitosamente.");
    }
}
