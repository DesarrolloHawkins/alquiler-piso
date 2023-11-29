<?php

namespace App\Http\Controllers;

use App\Models\ApartamentoLimpieza;
use App\Models\Cliente;
use App\Models\Huesped;
use App\Models\Photo;
use App\Models\Reserva;
use Faker\Core\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DNIController extends Controller
{
    public function index($token)
    {
        // Guardar el idioma en la sesión
 
        // Array de Paises
        $paises = array("Afganistán","Albania","Alemania","Andorra","Angola","Antigua y Barbuda","Arabia Saudita","Argelia","Argentina","Armenia","Australia","Austria","Azerbaiyán","Bahamas","Bangladés","Barbados","Baréin","Bélgica","Belice","Benín","Bielorrusia","Birmania","Bolivia","Bosnia y Herzegovina","Botsuana","Brasil","Brunéi","Bulgaria","Burkina Faso","Burundi","Bután","Cabo Verde","Camboya","Camerún","Canadá","Catar","Chad","Chile","China","Chipre","Ciudad del Vaticano","Colombia","Comoras","Corea del Norte","Corea del Sur","Costa de Marfil","Costa Rica","Croacia","Cuba","Dinamarca","Dominica","Ecuador","Egipto","El Salvador","Emiratos Árabes Unidos","Eritrea","Eslovaquia","Eslovenia","España","Estados Unidos","Estonia","Etiopía","Filipinas","Finlandia","Fiyi","Francia","Gabón","Gambia","Georgia","Ghana","Granada","Grecia","Guatemala","Guyana","Guinea","Guinea ecuatorial","Guinea-Bisáu","Haití","Honduras","Hungría","India","Indonesia","Irak","Irán","Irlanda","Islandia","Islas Marshall","Islas Salomón","Israel","Italia","Jamaica","Japón","Jordania","Kazajistán","Kenia","Kirguistán","Kiribati","Kuwait","Laos","Lesoto","Letonia","Líbano","Liberia","Libia","Liechtenstein","Lituania","Luxemburgo","Madagascar","Malasia","Malaui","Maldivas","Malí","Malta","Marruecos","Mauricio","Mauritania","México","Micronesia","Moldavia","Mónaco","Mongolia","Montenegro","Mozambique","Namibia","Nauru","Nepal","Nicaragua","Níger","Nigeria","Noruega","Nueva Zelanda","Omán","Países Bajos","Pakistán","Palaos","Palestina","Panamá","Papúa Nueva Guinea","Paraguay","Perú","Polonia","Portugal","Reino Unido","República Centroafricana","República Checa","República de Macedonia","República del Congo","República Democrática del Congo","República Dominicana","República Sudafricana","Ruanda","Rumanía","Rusia","Samoa","San Cristóbal y Nieves","San Marino","San Vicente y las Granadinas","Santa Lucía","Santo Tomé y Príncipe","Senegal","Serbia","Seychelles","Sierra Leona","Singapur","Siria","Somalia","Sri Lanka","Suazilandia","Sudán","Sudán del Sur","Suecia","Suiza","Surinam","Tailandia","Tanzania","Tayikistán","Timor Oriental","Togo","Tonga","Trinidad y Tobago","Túnez","Turkmenistán","Turquía","Tuvalu","Ucrania","Uganda","Uruguay","Uzbekistán","Vanuatu","Venezuela","Vietnam","Yemen","Yibuti","Zambia","Zimbabue");

        // Obtenemos la Reserva
        $reserva = Reserva::where('token',$token)->first();
        // Obtenemos el Cliente
        $cliente = Cliente::where('id', $reserva->cliente_id)->first();
        Session::put('idioma', $cliente->nacionalidad);
        // Cambiar el idioma de la aplicación
        App::setLocale($cliente->nacionalidad);
        $id = $reserva->id;
        if ($reserva->numero_personas > 0) {
            if($reserva->dni_entregado == true){
                return view('gracias');
            }
        }
        $data = [];
        if($cliente != null){

            if ($cliente->tipo_documento == 1) {
                $photoFrontal = Photo::where('cliente_id', $cliente->id)->where('photo_categoria_id', 13)->first();
                $cliente['frontal'] = $photoFrontal;
                $photoTrasera = Photo::where('cliente_id', $cliente->id)->where('photo_categoria_id', 14)->first();
                $cliente['trasera'] = $photoTrasera;
                array_push($data, $cliente);
            } else {
                $photoFrontal = Photo::where('cliente_id', $cliente->id)->where('photo_categoria_id', 15)->first();
                $cliente['pasaporte'] = $photoFrontal;
                array_push($data, $cliente);
            }
            
        }
        
        $huespedes = Huesped::where('reserva_id', $reserva->id)->get();

        if (count($huespedes)>0) {
            foreach($huespedes as $huesped){
                if ($huesped->tipo_documento == 1) {
                    $photoFrontal = Photo::where('huespedes_id', $huesped->id)->where('photo_categoria_id', 13)->first();
                    $huesped['frontal'] = $photoFrontal;
                    $photoTrasera = Photo::where('huespedes_id', $huesped->id)->where('photo_categoria_id', 14)->first();
                    $huesped['trasera'] = $photoTrasera;
                    array_push($data, $huesped);
                } else {
                    $photoFrontal = Photo::where('huespedes_id', $huesped->id)->where('photo_categoria_id', 15)->first();
                    $huesped['pasaporte'] = $photoFrontal;
                    array_push($data, $huesped);
                }
            }
        }
        // dd($data);

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


        $textos = [
            'Inicio' => 'Debes rellenar los datos para verificar el numero de personas que ya añadiste.',
            'Huesped.Principal' => 'Huesped Principal',
            'Acompañante' => 'Acompañante',
            'Nombre' => 'Nombre',
            'Primer.Apellido' => 'Primer Apellido',
            'Segundo.Apellido' => 'Segun Apellido',
            'Fecha.Nacimiento' => 'Fecha de Nacimiento',
            'Tipo.Documento' => 'Seleccione tipo de documento',
            'Numero.Identificacion' => 'Numero de Identificacion',
            'Fecha.Expedicion' => 'Fecha de Expedición',
            'Sexo' => 'Sexo',
            'Correo.Electronico' => 'Correo electronico',
            'Imagen.Frontal' => 'Imagen frontal del DNI',
            'Imagen.Trasera' => 'Imagen trasera del DNI',
            'Imagen.Pasaporte' => 'Imagen de la hoja de información del Pasaporte',
            'Enviar' => 'Enviar',

        ];
        $traduccion = $this->chatGpt('Puedes traducirme este array al idioma'. $cliente->nacionalidad.', manteniendo la propiedad y traduciendo solo el valor. no me conteste solo con el array traducido, te adjunto el json: ' . json_encode($textos));
        dd($traduccion);

        return view('dni.index', compact('id', 'paises', 'reserva', 'data', 'textos'));
    }


    public function chatGpt($texto) {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        // Configurar los parámetros de la solicitud
     $url = 'https://api.openai.com/v1/chat/completions';
     $headers = array(
         'Content-Type: application/json',
         'Authorization: Bearer '. $token
     );


     $data = array(
       "message" => [
            "role" => "user",
            'content' => $texto
       ], 
       // "model" => "davinci:ft-personal:apartamentos-hawkins-2023-04-27-09-45-29",
       // "model" => "davinci:ft-personal:modeloapartamentos-2023-05-24-16-36-49",
       // "model" => "davinci:ft-personal:apartamentosjunionew-2023-06-14-21-19-15",
       // "model" => "davinci:ft-personal:apartamento-junio-2023-07-26-23-23-07",
       // "model" => "davinci:ft-personal:apartamentosoctubre-2023-10-03-16-01-24",
       "model" => "gpt-4-1106-preview",
       "temperature" => 0,
       "max_tokens"=> 200,
       "top_p"=> 1,
       "frequency_penalty"=> 0,
       "presence_penalty"=> 0,
       "stop"=> ["_END"]
     );

     // Inicializar cURL y configurar las opciones
     $curl = curl_init();
     curl_setopt($curl, CURLOPT_URL, $url);
     curl_setopt($curl, CURLOPT_POST, true);
     curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
     curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

     // Ejecutar la solicitud y obtener la respuesta
     $response = curl_exec($curl);
     curl_close($curl);

     // Procesar la respuesta
     if ($response === false) {
         $error = [
           'status' => 'error',
           'messages' => 'Error al realizar la solicitud'
         ];
         Storage::disk('local')->put('errorChapt.txt', $error['messages'] );

         return response()->json( $error );

     } else {
         $response_data = json_decode($response, true);
         $responseReturn = [
           'status' => 'ok',
           'messages' => $response_data['choices'][0]['text']
         ];
         Storage::disk('local')->put('respuestaFuncionChapt.txt', $responseReturn['messages'] );

         return $responseReturn;
     }
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
                $cliente->nacionalidad = $request->input('nacionalidad_'.$i);
                // $cliente->data_dni = true;
                $cliente->save();

                if ($request->input('tipo_documento_'.$i) == 1) {
                    // Si tenemos imagen Frontal DNI
                    if($request->hasFile('fontal_'.$i)){
                        // Imagen Frontal DNI
                        $file = $request->file('fontal_'.$i);
                        // Guardamos la imagen
                        $reponseImage = $this->guardarImagen($file, $cliente, $reserva, 13, 'FrontalDNI', null);
                        // Si devuelve error
                        if (!$reponseImage) {
                            return redirect(route('dni.index', $reserva->token))->with('alerta', 'Error a la hora de guardar la imagen intentelo mas tarde.');
                        }
                    }
                    if ($request->input('tipo_documento_'.$i) == 1) {
                        // Si no obtenemos imagen Frontal del DNI
                        $frontal = Photo::where('reserva_id', $reserva->id)
                        ->where('photo_categoria_id', 13)
                        ->first();
                        if (!$frontal) {
                            return redirect(route('dni.index', $reserva->token))->with('alerta', 'No adjuntaste la imagen frontal del DNI');
                        }
                    }
                    // Si tenemos imagen Trasera DNI
                    if($request->hasFile('trasera_'.$i)){
                        // Imagen Frontal DNI
                        $fileTrasera = $request->file('trasera_'.$i);
                        // Guardamos la imagen
                        $reponseImage = $this->guardarImagen($fileTrasera, $cliente, $reserva, 14, 'TraseraDNI', null);
                        // Si devuelve error
                        if (!$reponseImage) {
                            return redirect(route('dni.index', $reserva->token))->with('alerta', 'Error a la hora de guardar la imagen intentelo mas tarde.');
                        }
                    }
                    if ($request->input('tipo_documento_'.$i) == 1) {
                        $trasera = Photo::where('reserva_id', $reserva->id)
                        ->where('photo_categoria_id', 14)
                        ->first();
                        if (!$trasera) {
                            return redirect(route('dni.index', $reserva->token))->with('alerta', 'No adjuntaste la imagen trasera del DNI');
                        }
                    }

                }else {
                    // Si tenemos imagen Pasaporte
                    if($request->hasFile('pasaporte_'.$i)){
                        // Imagen Frontal DNI
                        $file = $request->file('pasaporte_'.$i);
                        // Guardamos la imagen
                        $reponseImage = $this->guardarImagen($file, $cliente, $reserva, 15, 'Pasaporte', null);
                        // Si devuelve error
                        if (!$reponseImage) {
                            return redirect(route('dni.index', $reserva->token))->with('alerta', 'Error a la hora de guardar la imagen intentelo mas tarde.');
                        }
                    } 
                    if ($request->input('tipo_documento_'.$i) == 2) {
                        $pasaporte = Photo::where('reserva_id', $reserva->id)
                        ->where('photo_categoria_id', 15)
                        ->first();
                        if (!$pasaporte) {
                            return redirect(route('dni.index', $reserva->token))->with('alerta', 'No adjuntaste la imagen del Pasaporte');
                        }
                    }
                }
            } else {

                $huesped = Huesped::where('reserva_id', $reserva->id)->where('contador', $i)->first();
                // dd($huesped);
                if ($huesped != null) {
                    // Comprobamos si la reserva ya tiene los dni entregados
                    $huesped->nombre = $request->input('nombre_'.$i);
                    $huesped->primer_apellido = $request->input('apellido1_'.$i);
                    $huesped->segundo_apellido = $request->input('apellido2_'.$i) ? $request->input('apellido2_'.$i) : null;
                    $huesped->tipo_documento = $request->input('tipo_documento_'.$i);
                    $huesped->numero_identificacion = $request->input('num_identificacion_'.$i);
                    $huesped->fecha_expedicion = $request->input('fecha_expedicion_doc_'.$i);
                    $huesped->fecha_nacimiento = $request->input('fecha_nacimiento_'.$i);
                    $huesped->sexo = $request->input('sexo_'.$i);
                    $huesped->pais = $request->input('pais'.$i);
                    $huesped->email = $request->input('email_'.$i);
                    $huesped->contador = $i;
                    $huesped->save();
                    // dd($huesped);

                    if ($request->input('tipo_documento_'.$i) == 1) {
                        // Si tenemos imagen Frontal DNI
                        if($request->hasFile('fontal_'.$i)){
                            // Imagen Frontal DNI
                            $file = $request->file('fontal_'.$i);
                            // Guardamos la imagen
                            $reponseImage = $this->guardarImagen($file, $huesped, $reserva, 13, 'FrontalDNI', true);
                            // Si devuelve error
                            if (!$reponseImage) {
                                return redirect(route('dni.index', $reserva->token))->with('alerta', 'Error a la hora de guardar la imagen intentelo mas tarde.');
                            }
                        } else {
                            if ($request->input('tipo_documento_'.$i) == 1) {
                                $frontal = Photo::where('huespedes_id', $huesped->id)
                                ->where('photo_categoria_id', 13)
                                ->first();
                                if (!$frontal) {
                                    return redirect(route('dni.index', $reserva->token))->with('alerta', 'No adjuntaste la imagen frontal del DNI');
                                }
        
                            }
                        }
                        
                        // Si tenemos imagen Trasera DNI
                        if($request->hasFile('trasera_'.$i)){
                            // Imagen Frontal DNI
                            $fileTrasera = $request->file('trasera_'.$i);
                            // Guardamos la imagen
                            $reponseImage = $this->guardarImagen($fileTrasera, $huesped, $reserva, 14, 'TraseraDNI', true);
                            // Si devuelve error
                            if (!$reponseImage) {
                                return redirect(route('dni.index', $reserva->token))->with('alerta', 'Error a la hora de guardar la imagen intentelo mas tarde.');
                            }
                            $reserva->dni_entregado = true;
                        } else {
                            if ($request->input('tipo_documento_'.$i) == 1) {
                                $trasera = Photo::where('huespedes_id', $huesped->id)
                                ->where('photo_categoria_id', 14)
                                ->first();
                                if (!$trasera) {
                                    return redirect(route('dni.index', $reserva->token))->with('alerta', 'No adjuntaste la imagen trasera del DNI');
                                }
                            }
                        }
                        
    
                    }else {
                        // Si tenemos imagen Pasaporte
                        if($request->hasFile('frontal_'.$i)){
                            // Imagen Pasaporte
                            $file = $request->file('frontal_'.$i);
                            // Guardamos la imagen
                            $reponseImage = $this->guardarImagen($file, $huesped, $reserva, 15, 'Pasaporte', true);
                            // Si devuelve error
                            if (!$reponseImage) {
                                return redirect(route('dni.index', $reserva->token))->with('alerta', 'Error a la hora de guardar la imagen intentelo mas tarde.');
                            }
                            $reserva->dni_entregado = true;
                        } else {
                            if ($request->input('tipo_documento_'.$i) == 2) {
                                $pasaporte = Photo::where('huespedes_id', $huesped->id)
                                ->where('photo_categoria_id', 15)
                                ->first();
                                if (!$pasaporte) {
                                    return redirect(route('dni.index', $reserva->token))->with('alerta', 'No adjuntaste la imagen del Pasaporte');
                                }
                            }
                        }
                        
                        
                    }
                }else{

                    // Comprobamos si la reserva ya tiene los dni entregados
                    $huespedNew = [
                        'nombre' => $request->input('nombre_'.$i),
                        'primer_apellido' => $request->input('apellido1_'.$i),
                        'segundo_apellido' => $request->input('apellido2_'.$i) ? $request->input('apellido2_'.$i) : null,
                        'tipo_documento' => $request->input('tipo_documento_'.$i),
                        'numero_identificacion' => $request->input('num_identificacion_'.$i),
                        'fecha_expedicion' => $request->input('fecha_expedicion_doc_'.$i),
                        'fecha_nacimiento' => $request->input('fecha_nacimiento_'.$i),
                        'sexo' => $request->input('sexo_'.$i),
                        'pais' => $request->input('pais'.$i),
                        'email'  => $request->input('email_'.$i),
                        'contador' => $i,
                        'reserva_id' => $reserva->id

                    ];
                    $huespedFinal = Huesped::create($huespedNew);
                    // dd($huespedNew);

                    if ($request->input('tipo_documento_'.$i) == 1) {
                        // Si tenemos imagen Frontal DNI
                        if($request->hasFile('fontal_'.$i)){
                            // Imagen Frontal DNI
                            $file = $request->file('fontal_'.$i);
                            // Guardamos la imagen
                            $reponseImage = $this->guardarImagen($file, $huespedFinal, $reserva, 13, 'FrontalDNI', true);
                            // Si devuelve error
                            if (!$reponseImage) {
                                return redirect(route('dni.index', $reserva->token))->with('alerta', 'Error a la hora de guardar la imagen intentelo mas tarde.');
                            }

                        }
                        if ($request->input('tipo_documento_'.$i) == 1) {
                            $frontal = Photo::where('huespedes_id', $huespedFinal->id)
                            ->where('photo_categoria_id', 13)
                            ->first();
                            if (!$frontal) {
                                return redirect(route('dni.index', $reserva->token))->with('alerta', 'No adjuntaste la imagen frontal del DNI');
                            }
                        }
                        // Si tenemos imagen Trasera DNI
                        if($request->hasFile('trasera_'.$i)){
                            // Imagen Frontal DNI
                            $fileTrasera = $request->file('trasera_'.$i);
                            // Guardamos la imagen
                            $reponseImage = $this->guardarImagen($fileTrasera, $huespedFinal, $reserva, 14, 'TraseraDNI', true);
                            // Si devuelve error
                            if (!$reponseImage) {
                                return redirect(route('dni.index', $reserva->token))->with('alerta', 'Error a la hora de guardar la imagen intentelo mas tarde.');
                            }
                            $reserva->dni_entregado = true;
                        }
                        if ($request->input('tipo_documento_'.$i) == 1) {
                            $trasera = Photo::where('huespedes_id', $huespedFinal->id)
                            ->where('photo_categoria_id', 14)
                            ->first();
                            if (!$trasera) {
                                return redirect(route('dni.index', $reserva->token))->with('alerta', 'No adjuntaste la imagen trasera del DNI');
                            }
                        }
    
                    }else {
                        // Si tenemos imagen Pasaporte
                        if($request->hasFile('frontal_'.$i)){
                            // Imagen Frontal DNI
                            $file = $request->file('frontal_'.$i);
                            // Guardamos la imagen
                            $reponseImage = $this->guardarImagen($file, $huespedFinal, $reserva, 15, 'Pasaporte', true);
                            // Si devuelve error
                            if (!$reponseImage) {
                                return redirect(route('dni.index', $reserva->token))->with('alerta', 'Error a la hora de guardar la imagen intentelo mas tarde.');
                            }
                            $reserva->dni_entregado = true;
                        }
                        if ($request->input('tipo_documento_'.$i) == 2) {
                            $pasaporte = Photo::where('huespedes_id', $huespedFinal->id)
                            ->where('photo_categoria_id', 15)
                            ->first();
                            if (!$pasaporte) {
                                return redirect(route('dni.index', $reserva->token))->with('alerta', 'No adjuntaste la imagen del Pasaporte');
                            }
                        }
                    }
                }
            }
        }
        $reserva->dni_entregado = true;
        $reserva->save();

        $cliente = Cliente::where('id', $reserva->cliente_id)->first();
        $cliente->data_dni = true;
        $cliente->save();

        return redirect(route('dni.index', $reserva->token));
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

    public function guardarImagen($file, $cliente, $reserva, $categoria, $name, $huesped)
    {
        // Imagen Frontal DNI
        // dd($cliente);
        // $file = $file->file('fontal_'.$i);
        $imageName = time().'_'.$cliente->id.'_'.$name.'.'.$file->getClientOriginalExtension();
        $file->move(public_path('imagesCliente'), $imageName);

        $imageUrl = 'imagesCliente/' . $imageName;

        if($huesped == true){
            $imagenExistente = Photo::where('reserva_id', $reserva->id)
            ->where('photo_categoria_id', $categoria)
            ->where('huespedes_id', $cliente->id)
            ->first();
        }else {
            $imagenExistente = Photo::where('reserva_id', $reserva->id)
            ->where('photo_categoria_id', $categoria)
            ->where('cliente_id', $cliente->id)
            ->first();
        }
        // Verificar si ya existe una imagen para ese limpieza_id y photo_categoria_id
        

        if ($imagenExistente) {
            // Si existe, borrar la imagen antigua del servidor
            $rutaImagenAntigua = public_path($imagenExistente->url);
            
            if (file_exists($rutaImagenAntigua)) {
                unlink($rutaImagenAntigua);
            }

            // Actualizar la URL en la base de datos
            $imagenExistente->url = $imageUrl;
            $imagenExistente->save();
            return true;
        } else {

            // $cliente = Cliente::where('id', $reserva->cliente_id)->first();
            // Si no existe, guardar la nueva imagen
            $imagenes = new Photo;
            $imagenes->url = $imageUrl;
            $imagenes->photo_categoria_id = $categoria;
            $imagenes->reserva_id = $reserva->id;
            // dd($huesped == null);

            if ($huesped == true) {
                // dd($cliente);
                $imagenes->huespedes_id = $cliente->id;
            }else {
                $imagenes->cliente_id = $cliente->id;
            }
            $imagenes->save();
            return true;
        }

        return false;
    }
    
}
