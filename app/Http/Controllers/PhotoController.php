<?php

namespace App\Http\Controllers;

use App\Models\ApartamentoLimpieza;
use App\Models\Photo;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotoController extends Controller
{

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:2048', // Máximo 2MB
        ]);

        // Subir la imagen al almacenamiento local o externo (e.g., AWS S3)
        $path = $request->file('file')->store('photos', 'public');

        // Generar la URL pública
        $url = Storage::url($path);

        return response()->json(['url' => $url], 200);
    }
    /**
     * Display a listing of the resource.
     */
    public function indexDormitorio($id)
    {
        // Cargar la URL de la imagen si existe
        $imagen = Photo::where('limpieza_id', $id)->where('photo_categoria_id', 1)->first();
        $imageUrl = $imagen ? asset($imagen->url) : null;

        $imagen2 = Photo::where('limpieza_id', $id)->where('photo_categoria_id', 2)->first();
        $imageUrlAlmohada = $imagen2 ? asset($imagen2->url) : null;

        $imagen3 = Photo::where('limpieza_id', $id)->where('photo_categoria_id', 3)->first();
        $imageUrlCanape = $imagen3 ? asset($imagen3->url) : null;

        return view('photos.dormitorioIndex', compact('id','imageUrl','imageUrlAlmohada','imageUrlCanape'));
    }

    public function obtenerIdCategorias ( $categoriaName )
    {
        switch ($categoriaName) {
            case 'image_general':
                return 1;
                break;
            case 'image_almohada':
                return 2;
                break;
            case 'image_canape':
                return 3;
                break;
            case 'image_general_sofa':
                return 4;
                break;
            case 'image_sofa':
                return 5;
                break;
            case 'image_general_cocina':
                return 6;
                break;
            case 'image_nevera':
                return 7;
                break;
            case 'image_microondas':
                return 8;
                break;
            case 'image_bajos':
                return 9;
                break;
            case 'image_general_banio':
                return 10;
                break;
            case 'image_inodoro':
                return 11;
                break;
            case 'image_desague':
                return 12;
                break;

            default:
                return null;
                break;
        }
    }

    public function actualizarDormitorio($id) {
        $limpiezaBano = ApartamentoLimpieza::where('id', $id)->first();
        $limpiezaBano->dormitorio_photo = true;
        $limpiezaBano->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Guardado con correctamente',
            'redirect_url' => route('gestion.edit', $id)
        ]);
        //return redirect()->route('gestion.edit', $id);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function dormitorioStore($id, Request $request)
    {
        $randomPrefix = Str::random(10);
        $imageName = $randomPrefix . '_' . time() . '.' . $request->image->getClientOriginalExtension();
        $request->image->move(public_path('images'), $imageName);

        $imageUrl = 'images/' . $imageName;

        // Verificar si ya existe una imagen para ese limpieza_id y photo_categoria_id
        $imagenExistente = Photo::where('limpieza_id', $id)
            ->where('photo_categoria_id', $this->obtenerIdCategorias($request->elementId))
            ->first();

        if ($imagenExistente) {
            // Si existe, borrar la imagen antigua del servidor
            $rutaImagenAntigua = public_path($imagenExistente->url);
            if (File::exists($rutaImagenAntigua)) {
                File::delete($rutaImagenAntigua);
            }

            // Actualizar la URL en la base de datos
            $imagenExistente->url = $imageUrl;
            $imagenExistente->save();
        } else {
            // Si no existe, guardar la nueva imagen
            $imagenes = new Photo;
            $imagenes->limpieza_id = $id;
            $imagenes->url = $imageUrl;
            $imagenes->photo_categoria_id = $this->obtenerIdCategorias($request->elementId);
            $imagenes->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Imágenes subidas correctamente',
            'redirect_url' => route('fotos.dormitorio', $id)
        ]);
    }

     /**
     * Display a listing of the resource.
     */
    public function indexSalon($id)
    {
        // Cargar la URL de la imagen si existe
        $imagen = Photo::where('limpieza_id', $id)->where('photo_categoria_id', 4)->first();
        $imageUrl = $imagen ? asset($imagen->url) : null;

        $imagen2 = Photo::where('limpieza_id', $id)->where('photo_categoria_id', 5)->first();
        $imageUrlSofa = $imagen2 ? asset($imagen2->url) : null;

        return view('photos.salonIndex', compact('id','imageUrl','imageUrlSofa'));
    }

    /**
     * Show the form for creating a new resource.
     */

    public function salonStore($id, Request $request)
    {

        $randomPrefix = Str::random(10);
        $imageName = $randomPrefix . '_' . time() . '.' . $request->image->getClientOriginalExtension();
        $request->image->move(public_path('images'), $imageName);

        $imageUrl = 'images/' . $imageName;

        // Verificar si ya existe una imagen para ese limpieza_id y photo_categoria_id
        $imagenExistente = Photo::where('limpieza_id', $id)
            ->where('photo_categoria_id', $this->obtenerIdCategorias($request->elementId))
            ->first();

        if ($imagenExistente) {
            // Si existe, borrar la imagen antigua del servidor
            $rutaImagenAntigua = public_path($imagenExistente->url);
            if (File::exists($rutaImagenAntigua)) {
                File::delete($rutaImagenAntigua);
            }

            // Actualizar la URL en la base de datos
            $imagenExistente->url = $imageUrl;
            $imagenExistente->save();
        } else {
            // Si no existe, guardar la nueva imagen
            $imagenes = new Photo;
            $imagenes->limpieza_id = $id;
            $imagenes->url = $imageUrl;
            $imagenes->photo_categoria_id = $this->obtenerIdCategorias($request->elementId);
            $imagenes->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Imágenes subidas correctamente',
            'redirect_url' => route('fotos.dormitorio', $id)
        ]);


        if ($request->image_general) {
            $randomPrefix = Str::random(10);
            $imageName = $randomPrefix . '_' . time() . '.' . $request->image_general->getClientOriginalExtension();
            $request->image_general->move(public_path('images'), $imageName);

            $imageUrl = 'images/' . $imageName;

            // Verificar si ya existe una imagen para ese limpieza_id y photo_categoria_id
            $imagenExistente = Photo::where('limpieza_id', $id)
                ->where('photo_categoria_id', 4)
                ->first();

            if ($imagenExistente) {
                // Si existe, borrar la imagen antigua del servidor
                $rutaImagenAntigua = public_path($imagenExistente->url);
                if (File::exists($rutaImagenAntigua)) {
                    File::delete($rutaImagenAntigua);
                }

                // Actualizar la URL en la base de datos
                $imagenExistente->url = $imageUrl;
                $imagenExistente->save();
            } else {
                // Si no existe, guardar la nueva imagen
                $imagenes = new Photo;
                $imagenes->limpieza_id = $id;
                $imagenes->url = $imageUrl;
                $imagenes->photo_categoria_id = 4;
                $imagenes->save();
            }
        }

        if ($request->image_sofa) {
            $randomPrefix = Str::random(10);
            $imageNameSofa = $randomPrefix . '_' . time() . '.' . $request->image_sofa->getClientOriginalExtension();
            $request->image_sofa->move(public_path('images'), $imageNameSofa);

            $imageUrlSofa = 'images/' . $imageNameSofa;

            // Verificar si ya existe una imagen para ese limpieza_id y photo_categoria_id
            $imagenExistenteSofa = Photo::where('limpieza_id', $id)
                ->where('photo_categoria_id', 5)
                ->first();

            if ($imagenExistenteSofa) {
                // Si existe, borrar la imagen antigua del servidor
                $rutaImagenAntiguaSofa = public_path($imagenExistenteSofa->url);
                if (File::exists($rutaImagenAntiguaSofa)) {
                    File::delete($rutaImagenAntiguaSofa);
                }

                // Actualizar la URL en la base de datos
                $imagenExistenteSofa->url = $imageUrlSofa;
                $imagenExistenteSofa->save();
            } else {
                // Si no existe, guardar la nueva imagen
                $imagenesSofa = new Photo;
                $imagenesSofa->limpieza_id = $id;
                $imagenesSofa->url = $imageUrlSofa;
                $imagenesSofa->photo_categoria_id = 5;
                $imagenesSofa->save();
            }
        }

        Alert::success('Subida con Éxito', 'Imágenes subidas correctamente');
        $limpiezaBano = ApartamentoLimpieza::where('id', $id)->first();
        $limpiezaBano->salon_photo = true;
        $limpiezaBano->save();
        return redirect()->route('gestion.edit', $id);
    }

    public function actualizarSalon($id) {
        $limpiezaBano = ApartamentoLimpieza::where('id', $id)->first();
        $limpiezaBano->salon_photo = true;
        $limpiezaBano->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Guardado con correctamente',
            'redirect_url' => route('gestion.edit', $id)
        ]);
        //return redirect()->route('gestion.edit', $id);
    }
     /**
     * Display a listing of the resource.
     */
    public function indexCocina($id)
    {
        // Cargar la URL de la imagen si existe
        $imagen = Photo::where('limpieza_id', $id)->where('photo_categoria_id', 6)->first();
        $imageUrl = $imagen ? asset($imagen->url) : null;

        $imagen2 = Photo::where('limpieza_id', $id)->where('photo_categoria_id', 7)->first();
        $imageUrlNevera = $imagen2 ? asset($imagen2->url) : null;

        $imagen3 = Photo::where('limpieza_id', $id)->where('photo_categoria_id', 8)->first();
        $imageUrlMicroondas = $imagen3 ? asset($imagen3->url) : null;

        $imagen4 = Photo::where('limpieza_id', $id)->where('photo_categoria_id', 9)->first();
        $imageUrlBajos = $imagen4 ? asset($imagen4->url) : null;

        return view('photos.cocinaIndex', compact('id','imageUrl','imageUrlNevera','imageUrlMicroondas','imageUrlBajos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function cocinaStore($id, Request $request)
    {

        $randomPrefix = Str::random(10);
        $imageName = $randomPrefix . '_' . time() . '.' . $request->image->getClientOriginalExtension();
        $request->image->move(public_path('images'), $imageName);

        $imageUrl = 'images/' . $imageName;

        // Verificar si ya existe una imagen para ese limpieza_id y photo_categoria_id
        $imagenExistente = Photo::where('limpieza_id', $id)
            ->where('photo_categoria_id', $this->obtenerIdCategorias($request->elementId))
            ->first();

        if ($imagenExistente) {
            // Si existe, borrar la imagen antigua del servidor
            $rutaImagenAntigua = public_path($imagenExistente->url);
            if (File::exists($rutaImagenAntigua)) {
                File::delete($rutaImagenAntigua);
            }

            // Actualizar la URL en la base de datos
            $imagenExistente->url = $imageUrl;
            $imagenExistente->save();
        } else {
            // Si no existe, guardar la nueva imagen
            $imagenes = new Photo;
            $imagenes->limpieza_id = $id;
            $imagenes->url = $imageUrl;
            $imagenes->photo_categoria_id = $this->obtenerIdCategorias($request->elementId);
            $imagenes->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Imágenes subidas correctamente',
            'redirect_url' => route('fotos.dormitorio', $id)
        ]);
    }

    public function actualizarCocina($id) {
        $limpiezaBano = ApartamentoLimpieza::where('id', $id)->first();
        $limpiezaBano->cocina_photo = true;
        $limpiezaBano->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Guardado con correctamente',
            'redirect_url' => route('gestion.edit', $id)
        ]);
        //return redirect()->route('gestion.edit', $id);
    }


    /**
     * Display a listing of the resource.
     */
    public function indexBanio($id)
    {
        // Cargar la URL de la imagen si existe
        $imagen = Photo::where('limpieza_id', $id)->where('photo_categoria_id', 10)->first();
        $imageUrl = $imagen ? asset($imagen->url) : null;

        $imagen2 = Photo::where('limpieza_id', $id)->where('photo_categoria_id', 11)->first();
        $imageUrlInodoro = $imagen2 ? asset($imagen2->url) : null;

        $imagen3 = Photo::where('limpieza_id', $id)->where('photo_categoria_id', 12)->first();
        $imageUrlDesague = $imagen3 ? asset($imagen3->url) : null;

        return view('photos.banioIndex', compact('id','imageUrl','imageUrlInodoro','imageUrlDesague'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function banioStore($id, Request $request)
    {

        $randomPrefix = Str::random(10);
        $imageName = $randomPrefix . '_' . time() . '.' . $request->image->getClientOriginalExtension();
        $request->image->move(public_path('images'), $imageName);

        $imageUrl = 'images/' . $imageName;

        // Verificar si ya existe una imagen para ese limpieza_id y photo_categoria_id
        $imagenExistente = Photo::where('limpieza_id', $id)
            ->where('photo_categoria_id', $this->obtenerIdCategorias($request->elementId))
            ->first();

        if ($imagenExistente) {
            // Si existe, borrar la imagen antigua del servidor
            $rutaImagenAntigua = public_path($imagenExistente->url);
            if (File::exists($rutaImagenAntigua)) {
                File::delete($rutaImagenAntigua);
            }

            // Actualizar la URL en la base de datos
            $imagenExistente->url = $imageUrl;
            $imagenExistente->save();
        } else {
            // Si no existe, guardar la nueva imagen
            $imagenes = new Photo;
            $imagenes->limpieza_id = $id;
            $imagenes->url = $imageUrl;
            $imagenes->photo_categoria_id = $this->obtenerIdCategorias($request->elementId);
            $imagenes->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Imágenes subidas correctamente',
            'redirect_url' => route('fotos.dormitorio', $id)
        ]);
    }

    public function actualizarBanio($id) {
        $limpiezaBano = ApartamentoLimpieza::where('id', $id)->first();
        $limpiezaBano->bano_photo = true;
        $limpiezaBano->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Guardado con correctamente',
            'redirect_url' => route('gestion.edit', $id)
        ]);
        //return redirect()->route('gestion.edit', $id);
    }

}
