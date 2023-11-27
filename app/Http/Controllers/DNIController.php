<?php

namespace App\Http\Controllers;

use App\Models\ApartamentoLimpieza;
use App\Models\Cliente;
use App\Models\Photo;
use App\Models\Reserva;
use Faker\Core\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DNIController extends Controller
{
    public function index($token)
    {
        // Array de Paises
        $paises = array("Afganistán","Albania","Alemania","Andorra","Angola","Antigua y Barbuda","Arabia Saudita","Argelia","Argentina","Armenia","Australia","Austria","Azerbaiyán","Bahamas","Bangladés","Barbados","Baréin","Bélgica","Belice","Benín","Bielorrusia","Birmania","Bolivia","Bosnia y Herzegovina","Botsuana","Brasil","Brunéi","Bulgaria","Burkina Faso","Burundi","Bután","Cabo Verde","Camboya","Camerún","Canadá","Catar","Chad","Chile","China","Chipre","Ciudad del Vaticano","Colombia","Comoras","Corea del Norte","Corea del Sur","Costa de Marfil","Costa Rica","Croacia","Cuba","Dinamarca","Dominica","Ecuador","Egipto","El Salvador","Emiratos Árabes Unidos","Eritrea","Eslovaquia","Eslovenia","España","Estados Unidos","Estonia","Etiopía","Filipinas","Finlandia","Fiyi","Francia","Gabón","Gambia","Georgia","Ghana","Granada","Grecia","Guatemala","Guyana","Guinea","Guinea ecuatorial","Guinea-Bisáu","Haití","Honduras","Hungría","India","Indonesia","Irak","Irán","Irlanda","Islandia","Islas Marshall","Islas Salomón","Israel","Italia","Jamaica","Japón","Jordania","Kazajistán","Kenia","Kirguistán","Kiribati","Kuwait","Laos","Lesoto","Letonia","Líbano","Liberia","Libia","Liechtenstein","Lituania","Luxemburgo","Madagascar","Malasia","Malaui","Maldivas","Malí","Malta","Marruecos","Mauricio","Mauritania","México","Micronesia","Moldavia","Mónaco","Mongolia","Montenegro","Mozambique","Namibia","Nauru","Nepal","Nicaragua","Níger","Nigeria","Noruega","Nueva Zelanda","Omán","Países Bajos","Pakistán","Palaos","Palestina","Panamá","Papúa Nueva Guinea","Paraguay","Perú","Polonia","Portugal","Reino Unido","República Centroafricana","República Checa","República de Macedonia","República del Congo","República Democrática del Congo","República Dominicana","República Sudafricana","Ruanda","Rumanía","Rusia","Samoa","San Cristóbal y Nieves","San Marino","San Vicente y las Granadinas","Santa Lucía","Santo Tomé y Príncipe","Senegal","Serbia","Seychelles","Sierra Leona","Singapur","Siria","Somalia","Sri Lanka","Suazilandia","Sudán","Sudán del Sur","Suecia","Suiza","Surinam","Tailandia","Tanzania","Tayikistán","Timor Oriental","Togo","Tonga","Trinidad y Tobago","Túnez","Turkmenistán","Turquía","Tuvalu","Ucrania","Uganda","Uruguay","Uzbekistán","Vanuatu","Venezuela","Vietnam","Yemen","Yibuti","Zambia","Zimbabue");

        // Obtenemos la Reserva
        $reserva = Reserva::where('token',$token)->first();
        // Obtenemos el Cliente
        $cliente = Cliente::where('id', $reserva->cliente_id)->first();
        $id = $reserva->id;
        if ($reserva->numero_personas > 0) {
            if($cliente->data_dni == true){
                return view('gracias');
            }
        }

        // if ( $cliente->data_dni == null) {
        //     return view('dni.index', compact('id', 'paises'));

        // }elseif (!$cliente->photo_dni){
            
        //     if ($cliente->tipo_documento == 0) {
        //         return redirect(route('dni.dni', $token));
        //     }else {
        //         return redirect(route('dni.pasaporte', $token));
        //     } 
        // }
        //return view('404');
        return view('dni.index', compact('id', 'paises','reserva'));
    }
    public function storeNumeroPersonas(Request $request){
        $reserva = Reserva::find($request->id);
        if (!$reserva) {
            return response(404);

        }
        $reserva->numero_personas = $request->cantidad;
        $reserva->save();
        return redirect(route('dni.index', $reserva->token));
    }

    public function store(Request $request)
    {
        // Definir las reglas de validación
        // $rules = [
        //     'nombre' => 'required|string|max:255',
        //     'apellido1' => 'required|string|max:255',
        //     'apellido2' => 'nullable|string|max:255',
        //     'nacionalidad' => 'required|string|max:255',
        //     'tipo_documento' => 'required|string|max:255',
        //     'num_identificacion' => 'required|string|max:255',
        //     'fecha_expedicion_doc' => 'required|date',
        //     'fecha_nacimiento' => 'required|date',
        //     'sexo' => 'required',
        //     'email' => 'required|email',
        // ];

        // // Crear la instancia del validador
        // $validator = Validator::make($request->all(), $rules);

        // // Verificar si la validación falla
        // if ($validator->fails()) {
        //     // Redirigir o devolver con errores
        //     return redirect(route('dni.index', $request->id))
        //             ->withErrors($validator)
        //             ->withInput();
        // }

        $reserva = Reserva:: find($request->id);
        for ($i=0; $i < $reserva->numero_personas; $i++) { 
            if ($i == 0 ) {
                $cliente = Cliente::where('id', $reserva->cliente_id)->first();
                // Comprobamos si la reserva ya tiene los dni entregados
                $cliente->nombre = $request->input('nombre_'.$i);
                $cliente->apellido1 = $request->input('apellido1_'.$i);
                $cliente->apellido2 = $request->input('apellido2_'.$i) ? $request->input('apellido2_'.$i) : null;
                $cliente->tipo_documento = $request->input('tipo_documento_'.$i);
                $cliente->num_identificacion = $request->input('num_identificacion_'.$i);
                $cliente->fecha_expedicion_doc = $request->input('fecha_expedicion_doc_'.$i);
                $cliente->fecha_nacimiento = $request->input('fecha_nacimiento_'.$i);
                $cliente->sexo = $request->input('sexo_'.$i);
                $cliente->email = $request->input('email_'.$i);
                $cliente->data_dni = true;
                $cliente->save();
                if ($request->input('tipo_documento_'.$i) == 0) {
                    if($request->hasFile('fontal_'.$i)){
                        // Imagen Frontal DNI
                        $file = $request->file('fontal_'.$i);
                        $imageName = time().'_'.$cliente->id.'_FrontalDNI.'.$file->getClientOriginalExtension();
                        $file->move(public_path('imagesCliente'), $imageName);

                        $imageUrl = 'imagesCliente/' . $imageName;

                        // Verificar si ya existe una imagen para ese limpieza_id y photo_categoria_id
                        $imagenExistente = Photo::where('reserva_id', $reserva->id)
                        ->where('photo_categoria_id', 13)
                        ->first();

                        if ($imagenExistente) {
                            // Si existe, borrar la imagen antigua del servidor
                            $rutaImagenAntigua = public_path($imagenExistente->url);
                            
                            if (file_exists($rutaImagenAntigua)) {
                                unlink($rutaImagenAntigua);
                            }

                            // Actualizar la URL en la base de datos
                            $imagenExistente->url = $imageUrl;
                            $imagenExistente->save();

                        } else {

                            $cliente = Cliente::where('id', $reserva->cliente_id)->first();
                            // Si no existe, guardar la nueva imagen
                            $imagenes = new Photo;
                            $imagenes->url = $imageUrl;
                            $imagenes->photo_categoria_id = 13;
                            $imagenes->reserva_id = $reserva->id;
                            $imagenes->cliente_id = $cliente->id;
                            $imagenes->save();

                        }
                    }else{
                        return redirect(route('dni.dni', $reserva->token));

                    }

                    if($request->hasFile('trasera_'.$i)){
                        // Imagen Frontal DNI
                        $fileTrasera = $request->file('trasera_'.$i);
                        $imageNameTrasera = time().'_'.$cliente->id.'_TraseraDNI.'.$file->getClientOriginalExtension();
                        $fileTrasera->move(public_path('imagesCliente'), $imageNameTrasera);

                        $imageUrlTrasera = 'imagesCliente/' . $imageNameTrasera;

                        // Verificar si ya existe una imagen para ese limpieza_id y photo_categoria_id
                        $imagenExistenteTrasera = Photo::where('reserva_id', $reserva->id)
                        ->where('photo_categoria_id', 14)
                        ->first();

                        if ($imagenExistenteTrasera) {
                            // Si existe, borrar la imagen antigua del servidor
                            $rutaImagenAntiguaTrasera = public_path($imagenExistenteTrasera->url);
                            
                            if (file_exists($rutaImagenAntiguaTrasera)) {
                                unlink($rutaImagenAntiguaTrasera);
                            }

                            // Actualizar la URL en la base de datos
                            $imagenExistenteTrasera->url = $imageUrlTrasera;
                            $imagenExistenteTrasera->save();

                        } else {

                            $cliente = Cliente::where('id', $reserva->cliente_id)->first();
                            // Si no existe, guardar la nueva imagen
                            $imagenes = new Photo;
                            $imagenes->url = $imageUrlTrasera;
                            $imagenes->photo_categoria_id = 14;
                            $imagenes->reserva_id = $reserva->id;
                            $imagenes->cliente_id = $cliente->id;
                            $imagenes->save();

                        }
                        $reserva->dni_entregado = true;
                        return 'llego hasta aqui';
                    }else {
                        return redirect(route('dni.dni', $reserva->token));
                    }

                }else {
                    if($request->hasFile('frontal_'.$i)){
                        // Imagen Frontal DNI
                        $file = $request->file('frontal_'.$i);
                        $imageName = time().'_'.$cliente->id.'_FrontalPasaporte.'.$file->getClientOriginalExtension();
                        $file->move(public_path('imagesCliente'), $imageName);

                        $imageUrl = 'imagesCliente/' . $imageName;

                        // Verificar si ya existe una imagen para ese limpieza_id y photo_categoria_id
                        $imagenExistente = Photo::where('reserva_id', $reserva->id)
                        ->where('photo_categoria_id', 15)
                        ->first();

                        if ($imagenExistente) {
                            // Si existe, borrar la imagen antigua del servidor
                            $rutaImagenAntigua = public_path($imagenExistente->url);
                            
                            if (file_exists($rutaImagenAntigua)) {
                                unlink($rutaImagenAntigua);
                            }

                            // Actualizar la URL en la base de datos
                            $imagenExistente->url = $imageUrl;
                            $imagenExistente->save();

                        } else {

                            $cliente = Cliente::where('id', $reserva->cliente_id)->first();
                            // Si no existe, guardar la nueva imagen
                            $imagenes = new Photo;
                            $imagenes->url = $imageUrl;
                            $imagenes->photo_categoria_id = 15;
                            $imagenes->reserva_id = $reserva->id;
                            $imagenes->cliente_id = $cliente->id;
                            $imagenes->save();

                        }
                        $reserva->dni_entregado = true;
                        return 'llego hasta aqui';
                    }
                    return redirect(route('dni.pasaporte', $reserva->token));
                }
            }
        }
            

        return view('404');
    }

    public function dni($token){
        // Obtenemos la reserva
        $reserva = Reserva::where('token', $token)->first();
        // Obtenemos el cliente
        $cliente = Cliente::where('id', $reserva->cliente_id)->first();
        $id = $reserva->id; 
        // Comprobamos si el cliente relleno los datos principales
        if ($cliente->data_dni) {
            return redirect(route('dni.index', $token));
        }

        // Cargar la URL de la imagen si existe
        $imagen = Photo::where('cliente_id', $cliente->id)->where('photo_categoria_id', 13)->first();
        $frontal = $imagen ? asset($imagen->url) : null;

        $imagen2 = Photo::where('cliente_id', $cliente->id)->where('photo_categoria_id', 14)->first();
        $trasera = $imagen2 ? asset($imagen2->url) : null;

        return view('dni.dni', compact('id','frontal','trasera'));

    }

    public function pasaporte($id){

        return view('dni.pasaporte', compact('id'));
    }
    
}
