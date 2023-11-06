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
    public function index($id)
    {
        $paises = array("Afganistán","Albania","Alemania","Andorra","Angola","Antigua y Barbuda","Arabia Saudita","Argelia","Argentina","Armenia","Australia","Austria","Azerbaiyán","Bahamas","Bangladés","Barbados","Baréin","Bélgica","Belice","Benín","Bielorrusia","Birmania","Bolivia","Bosnia y Herzegovina","Botsuana","Brasil","Brunéi","Bulgaria","Burkina Faso","Burundi","Bután","Cabo Verde","Camboya","Camerún","Canadá","Catar","Chad","Chile","China","Chipre","Ciudad del Vaticano","Colombia","Comoras","Corea del Norte","Corea del Sur","Costa de Marfil","Costa Rica","Croacia","Cuba","Dinamarca","Dominica","Ecuador","Egipto","El Salvador","Emiratos Árabes Unidos","Eritrea","Eslovaquia","Eslovenia","España","Estados Unidos","Estonia","Etiopía","Filipinas","Finlandia","Fiyi","Francia","Gabón","Gambia","Georgia","Ghana","Granada","Grecia","Guatemala","Guyana","Guinea","Guinea ecuatorial","Guinea-Bisáu","Haití","Honduras","Hungría","India","Indonesia","Irak","Irán","Irlanda","Islandia","Islas Marshall","Islas Salomón","Israel","Italia","Jamaica","Japón","Jordania","Kazajistán","Kenia","Kirguistán","Kiribati","Kuwait","Laos","Lesoto","Letonia","Líbano","Liberia","Libia","Liechtenstein","Lituania","Luxemburgo","Madagascar","Malasia","Malaui","Maldivas","Malí","Malta","Marruecos","Mauricio","Mauritania","México","Micronesia","Moldavia","Mónaco","Mongolia","Montenegro","Mozambique","Namibia","Nauru","Nepal","Nicaragua","Níger","Nigeria","Noruega","Nueva Zelanda","Omán","Países Bajos","Pakistán","Palaos","Palestina","Panamá","Papúa Nueva Guinea","Paraguay","Perú","Polonia","Portugal","Reino Unido","República Centroafricana","República Checa","República de Macedonia","República del Congo","República Democrática del Congo","República Dominicana","República Sudafricana","Ruanda","Rumanía","Rusia","Samoa","San Cristóbal y Nieves","San Marino","San Vicente y las Granadinas","Santa Lucía","Santo Tomé y Príncipe","Senegal","Serbia","Seychelles","Sierra Leona","Singapur","Siria","Somalia","Sri Lanka","Suazilandia","Sudán","Sudán del Sur","Suecia","Suiza","Surinam","Tailandia","Tanzania","Tayikistán","Timor Oriental","Togo","Tonga","Trinidad y Tobago","Túnez","Turkmenistán","Turquía","Tuvalu","Ucrania","Uganda","Uruguay","Uzbekistán","Vanuatu","Venezuela","Vietnam","Yemen","Yibuti","Zambia","Zimbabue");

        $apartamento = Reserva::find($id);
        if (!$apartamento ) {
            return view('404');
        }
        return view('dni.index', compact('id', 'paises'));
    }

    public function store(Request $request)
    {
        // Definir las reglas de validación
        $rules = [
            'nombre' => 'required|string|max:255',
            'apellido1' => 'required|string|max:255',
            'apellido2' => 'nullable|string|max:255',
            'nacionalidad' => 'required|string|max:255',
            'tipo_documento' => 'required|string|max:255',
            'num_identificacion' => 'required|string|max:255',
            'fecha_expedicion_doc' => 'required|date',
            'fecha_nacimiento' => 'required|date',
            'sexo' => 'required',
            'email' => 'required|email',
        ];

        // Crear la instancia del validador
        $validator = Validator::make($request->all(), $rules);

        // Verificar si la validación falla
        if ($validator->fails()) {
            // Redirigir o devolver con errores
            return redirect(route('dni.index', $request->id))
                    ->withErrors($validator)
                    ->withInput();
        }

        $reserva = Reserva:: find($request->id);

        if ($reserva) {
            $id = $request->id;

            $cliente = Cliente::where('id', $reserva->cliente_id)->first();
             // Comprobamos si la reserva ya tiene los dni entregados
            if ($reserva->dni_entregado == false || $reserva->dni_entregado == null) {
                
                if ($reserva->verificado == false || $reserva->verificado == null) {
                    if ($cliente->tipo_documento == 0) {
                        return redirect(route('dni.dni', $request->id));
                    }else {
                        return redirect(route('dni.pasaporte', $request->id));
                    }                
                }
                return view('gracias');

            }else{

                $cliente->nombre = $request->nombre;
                $cliente->apellido1 = $request->apellido1;
                $cliente->apellido2 = $request->apellido2 ? $request->apellido2 : null;
                $cliente->nombre = $request->nombre;
                $cliente->tipo_documento = $request->tipo_documento;
                $cliente->num_identificacion = $request->num_identificacion;
                $cliente->fecha_expedicion_doc = $request->fecha_expedicion_doc;
                $cliente->fecha_nacimiento = $request->fecha_nacimiento;
                $cliente->sexo = $request->sexo;
                $cliente->email = $request->email;
                $cliente->dni_entregado = true;
                $cliente->save();
                $id = $request->id;
    
                if ($request->tipo_documento == 0) {
                    return view('dni.dni', compact('id'));
                }else {
                    return view('dni.pasaporte', compact('id'));
                }
            }

        }else {

        }

        return view('404');
    }

    public function dniUpload(Request $request){
        $id = $request->id;
        

        if ($request->frontal) {
            $imageName = time().'.'.$request->image_general->getClientOriginalExtension();  
            $request->frontal->move(public_path('imagesCliente'), $imageName);

            $imageUrl = 'imagesCliente/' . $imageName;

            // Verificar si ya existe una imagen para ese limpieza_id y photo_categoria_id
            $imagenExistente = Photo::where('reserva_id', $id)
            ->where('photo_categoria_id', 13)
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

                $reserva = Reserva::find($id);
                $cliente = Cliente::where('id', $reserva->cliente_id)->first();
                // Si no existe, guardar la nueva imagen
                $imagenes = new Photo;
                $imagenes->url = $imageUrl;
                $imagenes->photo_categoria_id = 13;
                $imagenes->reserva_id = $id;
                $imagenes->cliente_id = $cliente->id;
                $imagenes->save();

            }

        }

        if ($request->trasera) {
            $imageName2 = time().'.'.$request->trasera->getClientOriginalExtension();  
            $request->trasera->move(public_path('imagesCliente'), $imageName2);

            $imageUrl2 = 'imagesCliente/' . $imageName2;

            // Verificar si ya existe una imagen para ese limpieza_id y photo_categoria_id
            $imagenExistente2 = Photo::where('reserva_id', $id)
            ->where('photo_categoria_id', 14)
            ->first();

            if ($imagenExistente2) {
                // Si existe, borrar la imagen antigua del servidor
                $rutaImagenAntigua2 = public_path($imagenExistente2->url);
                
                if (File::exists($rutaImagenAntigua2)) {
                    File::delete($rutaImagenAntigua2);
                }

                // Actualizar la URL en la base de datos
                $imagenExistente2->url = $imageUrl2;
                $imagenExistente2->save();

            } else {

                $reserva = Reserva::find($id);
                $cliente = Cliente::where('id', $reserva->cliente_id)->first();
                // Si no existe, guardar la nueva imagen
                $imagen = new Photo;
                $imagen->url = $imageUrl;
                $imagen->photo_categoria_id = 14;
                $imagen->reserva_id = $id;
                $imagen->cliente_id = $cliente->id;
                $imagen->save();

            }

        }
        Alert::success('Subida con Exito', 'Imagenes subida correctamente correctamente');

        return redirect()->route('gracias.index');

    }
    public function pasaporteUpload(Request $request){

    }
    public function dni($id){

        // Cargar la URL de la imagen si existe
        $imagen = Photo::where('reserva_id', $id)->where('photo_categoria_id', 13)->first();
        $frontal = $imagen ? asset($imagen->url) : null;

        $imagen2 = Photo::where('reserva_id', $id)->where('photo_categoria_id', 14)->first();
        $trasera = $imagen2 ? asset($imagen2->url) : null;
        return view('dni.dni', compact('id','frontal','trasera'));

    }
    public function pasaporte($id){
        return view('dni.pasaporte', compact('id'));

    }
    
}
