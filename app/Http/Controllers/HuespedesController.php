<?php

namespace App\Http\Controllers;

use App\Models\Huesped;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HuespedesController extends Controller
{
    public function index(){
        $huespedes = Huesped::with('reserva')->paginate(15);
        return view('huespedes.index', compact('huespedes'));
    }

    public function show(string $id){
        $huesped = Huesped::findOrFail($id);
        $photos = Photo::with('categoria')->where('huespedes_id', $id)->get();
        return view('huespedes.show', compact('huesped','photos'));
    }

    public function create(){
        return view('huespedes.create');
    }

    public function store(Request $request){
        $request->validate([
            'reserva_id' => 'required|exists:reservas,id',
            'nombre' => 'required|string|max:255',
            'primer_apellido' => 'required|string|max:255',
            'segundo_apellido' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|in:M,F',
            'nacionalidad' => 'nullable|string|max:255',
            'tipo_documento' => 'required|in:1,2',
            'numero_identificacion' => 'required|string|max:255',
            'fecha_expedicion' => 'nullable|date',
            'email' => 'nullable|email|max:255',
            'telefono_movil' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'localidad' => 'nullable|string|max:255',
            'foto_dni_frente' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'foto_dni_reverso' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $huesped = Huesped::create($request->all());

        // Subir fotos
        if ($request->hasFile('foto_dni_frente')) {
            $fotoFrente = $request->file('foto_dni_frente');
            $fotoFrentePath = $fotoFrente->store('imagesCliente', 'public');
            
            // Determinar categoría según tipo de documento
            $categoriaId = $request->tipo_documento == 1 ? 13 : 15; // 13=DNI Frontal, 15=Pasaporte
            
            Photo::create([
                'huespedes_id' => $huesped->id,
                'reserva_id' => $huesped->reserva_id,
                'url' => $fotoFrentePath,
                'photo_categoria_id' => $categoriaId
            ]);
        }

        if ($request->hasFile('foto_dni_reverso') && $request->tipo_documento == 1) {
            $fotoReverso = $request->file('foto_dni_reverso');
            $fotoReversoPath = $fotoReverso->store('imagesCliente', 'public');
            
            Photo::create([
                'huespedes_id' => $huesped->id,
                'reserva_id' => $huesped->reserva_id,
                'url' => $fotoReversoPath,
                'photo_categoria_id' => 14 // 14=DNI Trasera
            ]);
        }

        return redirect()->route('huespedes.show', $huesped->id)
            ->with('success', 'Huésped creado exitosamente.');
    }

    public function edit(string $id){
        $huesped = Huesped::findOrFail($id);
        $photos = Photo::with('categoria')->where('huespedes_id', $id)->get();
        return view('huespedes.edit', compact('huesped', 'photos'));
    }

    public function update(Request $request, string $id){
        $huesped = Huesped::findOrFail($id);
        
        $request->validate([
            'nombre' => 'required|string|max:255',
            'primer_apellido' => 'required|string|max:255',
            'segundo_apellido' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|in:M,F',
            'nacionalidad' => 'nullable|string|max:255',
            'tipo_documento' => 'required|in:1,2',
            'numero_identificacion' => 'required|string|max:255',
            'fecha_expedicion' => 'nullable|date',
            'email' => 'nullable|email|max:255',
            'telefono_movil' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'localidad' => 'nullable|string|max:255',
            'foto_dni_frente' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'foto_dni_reverso' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $huesped->update($request->all());

        // Actualizar fotos si se suben nuevas
        if ($request->hasFile('foto_dni_frente')) {
            // Determinar categoría según tipo de documento
            $categoriaId = $request->tipo_documento == 1 ? 13 : 15; // 13=DNI Frontal, 15=Pasaporte
            
            // Eliminar foto anterior si existe
            $fotoAnterior = Photo::where('huespedes_id', $huesped->id)
                ->where('photo_categoria_id', $categoriaId)
                ->first();
            if ($fotoAnterior) {
                Storage::disk('public')->delete($fotoAnterior->url);
                $fotoAnterior->delete();
            }

            $fotoFrente = $request->file('foto_dni_frente');
            $fotoFrentePath = $fotoFrente->store('imagesCliente', 'public');
            
            Photo::create([
                'huespedes_id' => $huesped->id,
                'reserva_id' => $huesped->reserva_id,
                'url' => $fotoFrentePath,
                'photo_categoria_id' => $categoriaId
            ]);
        }

        if ($request->hasFile('foto_dni_reverso') && $request->tipo_documento == 1) {
            // Eliminar foto anterior si existe
            $fotoAnterior = Photo::where('huespedes_id', $huesped->id)
                ->where('photo_categoria_id', 14) // 14=DNI Trasera
                ->first();
            if ($fotoAnterior) {
                Storage::disk('public')->delete($fotoAnterior->url);
                $fotoAnterior->delete();
            }

            $fotoReverso = $request->file('foto_dni_reverso');
            $fotoReversoPath = $fotoReverso->store('imagesCliente', 'public');
            
            Photo::create([
                'huespedes_id' => $huesped->id,
                'reserva_id' => $huesped->reserva_id,
                'url' => $fotoReversoPath,
                'photo_categoria_id' => 14 // 14=DNI Trasera
            ]);
        }

        return redirect()->route('huespedes.show', $huesped->id)
            ->with('success', 'Huésped actualizado exitosamente.');
    }

    public function destroy(string $id){
        $huesped = Huesped::findOrFail($id);
        
        // Eliminar fotos asociadas
        $photos = Photo::where('huespedes_id', $id)->get();
        foreach ($photos as $photo) {
            Storage::disk('public')->delete($photo->url);
            $photo->delete();
        }
        
        $huesped->delete();
        
        return redirect()->route('huespedes.index')
            ->with('success', 'Huésped eliminado exitosamente.');
    }
}
