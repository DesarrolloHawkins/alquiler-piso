<?php

namespace App\Http\Controllers;

use App\Models\ApartamentoLimpieza;
use App\Models\Photo;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PhotoController extends Controller
{
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

    /**
     * Show the form for creating a new resource.
     */
    public function dormitorioStore($id, Request $request)
    {
        if ($request->image_general) {
            $randomPrefix = Str::random(10);
            $imageName = $randomPrefix . '_' . time() . '.' . $request->image_general->getClientOriginalExtension();  
            $request->image_general->move(public_path('images'), $imageName);

            $imageUrl = 'images/' . $imageName;

            // Verificar si ya existe una imagen para ese limpieza_id y photo_categoria_id
            $imagenExistente = Photo::where('limpieza_id', $id)
                ->where('photo_categoria_id', 1)
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
                $imagenes->photo_categoria_id = 1;
                $imagenes->save();
            }
        }

        if ($request->image_almohada) {
            $randomPrefix = Str::random(10);
            $imageNameAlmohada = $randomPrefix . '_' . time() . '.' . $request->image_almohada->getClientOriginalExtension();  
            $request->image_almohada->move(public_path('images'), $imageNameAlmohada);

            $imageUrlAlmohada = 'images/' . $imageNameAlmohada;

            // Verificar si ya existe una imagen para ese limpieza_id y photo_categoria_id
            $imagenExistenteAlmohada = Photo::where('limpieza_id', $id)
                ->where('photo_categoria_id', 2)
                ->first();

            if ($imagenExistenteAlmohada) {
                // Si existe, borrar la imagen antigua del servidor
                $rutaImagenAntiguaAlmohada = public_path($imagenExistenteAlmohada->url);
                if (File::exists($rutaImagenAntiguaAlmohada)) {
                    File::delete($rutaImagenAntiguaAlmohada);
                }

                // Actualizar la URL en la base de datos
                $imagenExistenteAlmohada->url = $imageUrlAlmohada;
                $imagenExistenteAlmohada->save();
            } else {
                // Si no existe, guardar la nueva imagen
                $imagenesAlmohada = new Photo;
                $imagenesAlmohada->limpieza_id = $id;
                $imagenesAlmohada->url = $imageUrlAlmohada;
                $imagenesAlmohada->photo_categoria_id = 2;
                $imagenesAlmohada->save();
            }
        }

        if ($request->image_canape) {
            $randomPrefix = Str::random(10);
            $imageNameCanape = $randomPrefix . '_' . time() . '.' . $request->image_canape->getClientOriginalExtension();  
            $request->image_canape->move(public_path('images'), $imageNameCanape);

            $imageUrlCanape = 'images/' . $imageNameCanape;

            // Verificar si ya existe una imagen para ese limpieza_id y photo_categoria_id
            $imagenExistenteCanape = Photo::where('limpieza_id', $id)
                ->where('photo_categoria_id', 3)
                ->first();

            if ($imagenExistenteCanape) {
                // Si existe, borrar la imagen antigua del servidor
                $rutaImagenAntiguaCanape = public_path($imagenExistenteCanape->url);
                if (File::exists($rutaImagenAntiguaCanape)) {
                    File::delete($rutaImagenAntiguaCanape);
                }

                // Actualizar la URL en la base de datos
                $imagenExistenteCanape->url = $imageUrlCanape;
                $imagenExistenteCanape->save();
            } else {
                // Si no existe, guardar la nueva imagen
                $imagenesCanape = new Photo;
                $imagenesCanape->limpieza_id = $id;
                $imagenesCanape->url = $imageUrlCanape;
                $imagenesCanape->photo_categoria_id = 3;
                $imagenesCanape->save();
            }
        }

        Alert::success('Subida con Éxito', 'Imágenes subidas correctamente');
        $limpiezaBano = ApartamentoLimpieza::where('id', $id)->first();
        $limpiezaBano->dormitorio_photo = true;
        $limpiezaBano->save();
        return redirect()->route('gestion.edit', $id);
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
        $imageUrlAlmohada = $imagen2 ? asset($imagen2->url) : null;

        return view('photos.salonIndex', compact('id','imageUrl','imageUrlAlmohada'));
    }

    /**
     * Show the form for creating a new resource.
     */

    public function salonStore($id, Request $request)
    {
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
        if ($request->image_general) {
            $randomPrefix = Str::random(10);
            $imageName = $randomPrefix . '_' . time() . '.' . $request->image_general->getClientOriginalExtension();  
            $request->image_general->move(public_path('images'), $imageName);

            $imageUrl = 'images/' . $imageName;

            // Verificar si ya existe una imagen para ese limpieza_id y photo_categoria_id
            $imagenExistente = Photo::where('limpieza_id', $id)
                ->where('photo_categoria_id', 6)
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
                $imagenes->photo_categoria_id = 6;
                $imagenes->save();
            }
        }

        if ($request->image_nevera) {
            $randomPrefix = Str::random(10);
            $imageNamesNevera = $randomPrefix . '_' . time() . '.' . $request->image_nevera->getClientOriginalExtension();  
            $request->image_nevera->move(public_path('images'), $imageNamesNevera);

            $imageUrlNevera = 'images/' . $imageNamesNevera;

            // Verificar si ya existe una imagen para ese limpieza_id y photo_categoria_id
            $imagenExistenteNevera = Photo::where('limpieza_id', $id)
                ->where('photo_categoria_id', 7)
                ->first();

            if ($imagenExistenteNevera) {
                // Si existe, borrar la imagen antigua del servidor
                $rutaImagenAntiguaNevera = public_path($imagenExistenteNevera->url);
                if (File::exists($rutaImagenAntiguaNevera)) {
                    File::delete($rutaImagenAntiguaNevera);
                }

                // Actualizar la URL en la base de datos
                $imagenExistenteNevera->url = $imageUrlNevera;
                $imagenExistenteNevera->save();
            } else {
                // Si no existe, guardar la nueva imagen
                $imagenesNevera = new Photo;
                $imagenesNevera->limpieza_id = $id;
                $imagenesNevera->url = $imageUrlNevera;
                $imagenesNevera->photo_categoria_id = 7;
                $imagenesNevera->save();
            }
        }

        if ($request->image_microondas) {
            $randomPrefix = Str::random(10);
            $imageNamesMicroondas = $randomPrefix . '_' . time() . '.' . $request->image_microondas->getClientOriginalExtension();  
            $request->image_microondas->move(public_path('images'), $imageNamesMicroondas);

            $imageUrlMicroondas = 'images/' . $imageNamesMicroondas;

            // Verificar si ya existe una imagen para ese limpieza_id y photo_categoria_id
            $imagenExistenteMicroondas = Photo::where('limpieza_id', $id)
                ->where('photo_categoria_id', 8)
                ->first();

            if ($imagenExistenteMicroondas) {
                // Si existe, borrar la imagen antigua del servidor
                $rutaImagenAntiguaMicroondas = public_path($imagenExistenteMicroondas->url);
                if (File::exists($rutaImagenAntiguaMicroondas)) {
                    File::delete($rutaImagenAntiguaMicroondas);
                }

                // Actualizar la URL en la base de datos
                $imagenExistenteMicroondas->url = $imageUrlMicroondas;
                $imagenExistenteMicroondas->save();
            } else {
                // Si no existe, guardar la nueva imagen
                $imagenesMicroondas = new Photo;
                $imagenesMicroondas->limpieza_id = $id;
                $imagenesMicroondas->url = $imageUrlMicroondas;
                $imagenesMicroondas->photo_categoria_id = 8;
                $imagenesMicroondas->save();
            }
        }

        if ($request->image_bajos) {
            $randomPrefix = Str::random(10);
            $imageNamesBajos = $randomPrefix . '_' . time() . '.' . $request->image_bajos->getClientOriginalExtension();  
            $request->image_bajos->move(public_path('images'), $imageNamesBajos);

            $imageUrlBajos = 'images/' . $imageNamesBajos;

            // Verificar si ya existe una imagen para ese limpieza_id y photo_categoria_id
            $imagenExistenteBajos = Photo::where('limpieza_id', $id)
                ->where('photo_categoria_id', 9)
                ->first();

            if ($imagenExistenteBajos) {
                // Si existe, borrar la imagen antigua del servidor
                $rutaImagenAntiguaBajos = public_path($imagenExistenteBajos->url);
                if (File::exists($rutaImagenAntiguaBajos)) {
                    File::delete($rutaImagenAntiguaBajos);
                }

                // Actualizar la URL en la base de datos
                $imagenExistenteBajos->url = $imageUrlBajos;
                $imagenExistenteBajos->save();
            } else {
                // Si no existe, guardar la nueva imagen
                $imagenesBajos = new Photo;
                $imagenesBajos->limpieza_id = $id;
                $imagenesBajos->url = $imageUrlBajos;
                $imagenesBajos->photo_categoria_id = 9;
                $imagenesBajos->save();
            }
        }
        
        Alert::success('Subida con Éxito', 'Imágenes subidas correctamente');
        $limpiezaBano = ApartamentoLimpieza::where('id', $id)->first();
        $limpiezaBano->cocina_photo = true;
        $limpiezaBano->save();
        return redirect()->route('gestion.edit', $id);
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
        if ($request->image_general) {
            $randomPrefix = Str::random(10);
            $imageName = $randomPrefix . '_' . time() . '.' . $request->image_general->getClientOriginalExtension();  
            $request->image_general->move(public_path('images'), $imageName);
    
            $imageUrl = 'images/' . $imageName;
    
            // Verificar si ya existe una imagen para ese limpieza_id y photo_categoria_id
            $imagenExistente = Photo::where('limpieza_id', $id)
                ->where('photo_categoria_id', 10)
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
                $imagenes->photo_categoria_id = 10;
                $imagenes->save();
            }
        }
    
        if ($request->image_inodoro) {
            $randomPrefix = Str::random(10);
            $imageNamesInodoro = $randomPrefix . '_' . time() . '.' . $request->image_inodoro->getClientOriginalExtension();  
            $request->image_inodoro->move(public_path('images'), $imageNamesInodoro);
    
            $imageUrlInodoro = 'images/' . $imageNamesInodoro;
    
            // Verificar si ya existe una imagen para ese limpieza_id y photo_categoria_id
            $imagenExistenteInodoro = Photo::where('limpieza_id', $id)
                ->where('photo_categoria_id', 11)
                ->first();
    
            if ($imagenExistenteInodoro) {
                // Si existe, borrar la imagen antigua del servidor
                $rutaImagenAntiguaInodoro = public_path($imagenExistenteInodoro->url);
                if (File::exists($rutaImagenAntiguaInodoro)) {
                    File::delete($rutaImagenAntiguaInodoro);
                }
    
                // Actualizar la URL en la base de datos
                $imagenExistenteInodoro->url = $imageUrlInodoro;
                $imagenExistenteInodoro->save();
            } else {
                // Si no existe, guardar la nueva imagen
                $imagenesInodoro = new Photo;
                $imagenesInodoro->limpieza_id = $id;
                $imagenesInodoro->url = $imageUrlInodoro;
                $imagenesInodoro->photo_categoria_id = 11;
                $imagenesInodoro->save();
            }
        }
    
        if ($request->image_desague) {
            $randomPrefix = Str::random(10);
            $imageNamesDesague = $randomPrefix . '_' . time() . '.' . $request->image_desague->getClientOriginalExtension();  
            $request->image_desague->move(public_path('images'), $imageNamesDesague);
    
            $imageUrlDesague = 'images/' . $imageNamesDesague;
    
            // Verificar si ya existe una imagen para ese limpieza_id y photo_categoria_id
            $imagenExistenteDesague = Photo::where('limpieza_id', $id)
                ->where('photo_categoria_id', 12)
                ->first();
    
            if ($imagenExistenteDesague) {
                // Si existe, borrar la imagen antigua del servidor
                $rutaImagenAntiguaDesague = public_path($imagenExistenteDesague->url);
                if (File::exists($rutaImagenAntiguaDesague)) {
                    File::delete($rutaImagenAntiguaDesague);
                }
    
                // Actualizar la URL en la base de datos
                $imagenExistenteDesague->url = $imageUrlDesague;
                $imagenExistenteDesague->save();
            } else {
                // Si no existe, guardar la nueva imagen
                $imagenesDesague = new Photo;
                $imagenesDesague->limpieza_id = $id;
                $imagenesDesague->url = $imageUrlDesague;
                $imagenesDesague->photo_categoria_id = 12;
                $imagenesDesague->save();
            }
        }
        
        Alert::success('Subida con Éxito', 'Imágenes subidas correctamente');
        $limpiezaBano = ApartamentoLimpieza::where('id', $id)->first();
        $limpiezaBano->bano_photo = true;
        $limpiezaBano->save();
        return redirect()->route('gestion.edit', $id);
    }
    
    
}
