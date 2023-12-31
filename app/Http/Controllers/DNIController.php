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

        $idiomaAPais = [
            "Afganistán" => "Pastún",
            "Albania" => "Albanés",
            "Alemania" => "Alemán",
            "Andorra" => "Catalán",
            "Angola" => "Portugués",
            "Antigua y Barbuda" => "Inglés",
            "Arabia Saudita" => "Árabe",
            "Argelia" => "Árabe",
            "Argentina" => "Español",
            "Armenia" => "Armenio",
            "Australia" => "Inglés",
            "Austria" => "Alemán",
            "Azerbaiyán" => "Azerí",
            "Bahamas" => "Inglés",
            "Bangladés" => "Bengalí",
            "Barbados" => "Inglés",
            "Baréin" => "Árabe",
            "Bélgica" => "Neerlandés",
            "Belice" => "Inglés",
            "Benín" => "Francés",
            "Bielorrusia" => "Bielorruso",
            "Birmania" => "Birmano",
            "Bolivia" => "Español",
            "Bosnia y Herzegovina" => "Bosnio",
            "Botsuana" => "Inglés",
            "Brasil" => "Portugués",
            "Brunéi" => "Malayo",
            "Bulgaria" => "Búlgaro",
            "Burkina Faso" => "Francés",
            "Burundi" => "Kirundi",
            "Bután" => "Dzongkha",
            "Cabo Verde" => "Portugués",
            "Camboya" => "Jemer",
            "Camerún" => "Francés",
            "Canadá" => "Inglés",
            "Catar" => "Árabe",
            "Chad" => "Francés",
            "Chile" => "Español",
            "China" => "Mandarín",
            "Chipre" => "Griego",
            "Ciudad del Vaticano" => "Italiano",
            "Colombia" => "Español",
            "Comoras" => "Comorense",
            "Corea del Norte" => "Coreano",
            "Corea del Sur" => "Coreano",
            "Costa de Marfil" => "Francés",
            "Costa Rica" => "Español",
            "Croacia" => "Croata",
            "Cuba" => "Español",
            "Dinamarca" => "Danés",
            "Dominica" => "Inglés",
            "Ecuador" => "Español",
            "Egipto" => "Árabe",
            "El Salvador" => "Español",
            "Emiratos Árabes Unidos" => "Árabe",
            "Eritrea" => "Tigriña",
            "Eslovaquia" => "Eslovaco",
            "Eslovenia" => "Esloveno",
            "España" => "Español",
            "Estados Unidos" => "Inglés",
            "Estonia" => "Estonio",
            "Etiopía" => "Amárico",
            "Filipinas" => "Filipino",
            "Finlandia" => "Finés",
            "Fiyi" => "Fiyiano",
            "Francia" => "Francés",
            "Gabón" => "Francés",
            "Gambia" => "Inglés",
            "Georgia" => "Georgiano",
            "Ghana" => "Inglés",
            "Granada" => "Inglés",
            "Grecia" => "Griego",
            "Guatemala" => "Español",
            "Guyana" => "Inglés",
            "Guinea" => "Francés",
            "Guinea ecuatorial" => "Español",
            "Guinea-Bisáu" => "Portugués",
            "Haití" => "Francés",
            "Honduras" => "Español",
            "Hungría" => "Húngaro",
            "India" => "Hindi",
            "Indonesia" => "Indonesio",
            "Irak" => "Árabe",
            "Irán" => "Persa",
            "Irlanda" => "Inglés",
            "Islandia" => "Islandés",
            "Islas Marshall" => "Marshalés",
            "Islas Salomón" => "Inglés",
            "Israel" => "Hebreo",
            "Italia" => "Italiano",
            "Jamaica" => "Inglés",
            "Japón" => "Japonés",
            "Jordania" => "Árabe",
            "Kazajistán" => "Kazajo",
            "Kenia" => "Suajili",
            "Kirguistán" => "Kirguís",
            "Kiribati" => "Inglés",
            "Kuwait" => "Árabe",
            "Laos" => "Lao",
            "Lesoto" => "Sesotho",
            "Letonia" => "Letón",
            "Líbano" => "Árabe",
            "Liberia" => "Inglés",
            "Libia" => "Árabe",
            "Liechtenstein" => "Alemán",
            "Lituania" => "Lituano",
            "Luxemburgo" => "Luxemburgués",
            "Madagascar" => "Malgache",
            "Malasia" => "Malayo",
            "Malaui" => "Chichewa",
            "Maldivas" => "Divehi",
            "Malí" => "Francés",
            "Malta" => "Maltés",
            "Marruecos" => "Árabe",
            "Mauricio" => "Inglés",
            "Mauritania" => "Árabe",
            "México" => "Español",
            "Micronesia" => "Inglés",
            "Moldavia" => "Rumano",
            "Mónaco" => "Francés",
            "Mongolia" => "Mongol",
            "Montenegro" => "Montenegrino",
            "Mozambique" => "Portugués",
            "Namibia" => "Inglés",
            "Nauru" => "Nauruano",
            "Nepal" => "Nepalí",
            "Nicaragua" => "Español",
            "Níger" => "Francés",
            "Nigeria" => "Inglés",
            "Noruega" => "Noruego",
            "Nueva Zelanda" => "Inglés",
            "Omán" => "Árabe",
            "Países Bajos" => "Neerlandés",
            "Pakistán" => "Urdu",
            "Palaos" => "Palauano",
            "Palestina" => "Árabe",
            "Panamá" => "Español",
            "Papúa Nueva Guinea" => "Tok Pisin",
            "Paraguay" => "Guaraní",
            "Perú" => "Español",
            "Polonia" => "Polaco",
            "Portugal" => "Portugués",
            "Reino Unido" => "Inglés",
            "República Centroafricana" => "Sango",
            "República Checa" => "Checo",
            "República de Macedonia" => "Macedonio",
            "República del Congo" => "Francés",
            "República Democrática del Congo" => "Francés",
            "República Dominicana" => "Español",
            "República Sudafricana" => "Zulú",
            "Ruanda" => "Kinyarwanda",
            "Rumanía" => "Rumano",
            "Rusia" => "Ruso",
            "Samoa" => "Samoano",
            "San Cristóbal y Nieves" => "Inglés",
            "San Marino" => "Italiano",
            "San Vicente y las Granadinas" => "Inglés",
            "Santa Lucía" => "Inglés",
            "Santo Tomé y Príncipe" => "Portugués",
            "Senegal" => "Francés",
            "Serbia" => "Serbio",
            "Seychelles" => "Seychellense",
            "Sierra Leona" => "Inglés",
            "Singapur" => "Inglés",
            "Siria" => "Árabe",
            "Somalia" => "Somalí",
            "Sri Lanka" => "Cingalés",
            "Suazilandia" => "Swazi",
            "Sudán" => "Árabe",
            "Sudán del Sur" => "Inglés",
            "Suecia" => "Sueco",
            "Suiza" => "Alemán",
            "Surinam" => "Neerlandés",
            "Tailandia" => "Tailandés",
            "Tanzania" => "Suajili",
            "Tayikistán" => "Tayiko",
            "Timor Oriental" => "Tetún",
            "Togo" => "Francés",
            "Tonga" => "Tongano",
            "Trinidad y Tobago" => "Inglés",
            "Túnez" => "Árabe",
            "Turkmenistán" => "Turcomano",
            "Turquía" => "Turco",
            "Tuvalu" => "Tuvaluano",
            "Ucrania" => "Ucraniano",
            "Uganda" => "Inglés",
            "Uruguay" => "Español",
            "Uzbekistán" => "Uzbeko",
            "Vanuatu" => "Bislama",
            "Venezuela" => "Español",
            "Vietnam" => "Vietnamita",
            "Yemen" => "Árabe",
            "Yibuti" => "Árabe",
            "Zambia" => "Inglés",
            "Zimbabue" => "Inglés"
        ];

        // Obtenemos la Reserva
        $reserva = Reserva::where('token',$token)->first();
        // Obtenemos el Cliente
        $cliente = Cliente::where('id', $reserva->cliente_id)->first();
        Session::put('idioma', $cliente->nacionalidad);
        // Cambiar el idioma de la aplicación
        App::setLocale($cliente->nacionalidad);

        $idiomaCliente = $cliente->nacionalidad; // Esto contiene el idioma del cliente
        $paisCliente = "";

        // Comprobamos si el idioma del cliente está en el array mapeado
        if (array_key_exists($idiomaCliente, $idiomaAPais)) {
            $paisCliente = $idiomaAPais[$idiomaCliente];
        } else {
            $paisCliente = "No disponible"; // o cualquier valor por defecto que prefieras
        }
        
        // $idiomaClientePaises = $cliente->nacionalidad;
        // $nombreArchivoPaises = 'traducciones_paises_' . $idiomaClientePaises . '.json';
        // $path_paises = storage_path('app/public/' . $nombreArchivoPaises);

        // if (file_exists($path_paises)) {
        //     // Leer el contenido del archivo si ya existe
        //     $textosTraducidos = json_decode(file_get_contents($path_paises), true);
        // } else {
        //     // Si no existe el archivo, hacer la petición a chatGpt
        //     $traducciones = $this->chatGpt('Puedes traducirme este array al idioma '. $idiomaClientePaises.', no me expliques nada devuelve solo el json en formato texto donde no se envie como code, te adjunto el array: ' . json_encode($paises));
        //     $textosTraducidos = json_decode($traducciones['messages']['choices'][0]['message']['content'], true);

        //     // Guardar la traducción en un nuevo archivo
        //     file_put_contents($path_paises, json_encode($textosTraducidos));
        // }

        // $paises = $textosTraducidos;


        $id = $reserva->id;
        if ($reserva->numero_personas > 0) {
            if($reserva->dni_entregado == true){
                return redirect(route('gracias.index', $cliente->idioma));
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
            'Frontal' => 'Frontal',
            'Trasera' => 'Trasera',
            'Pais' => 'Selecciona Pais',
            'Dni' => 'Documento Nacional de Identidad',
            'Pasaporte' => 'Pasaporte',
            'Masculino' => 'Masculino',
            'Femenino' => 'Femenino',
            'Selecciona_tipo' => 'Seleccion el tipo',
            'nombre_obli' => 'El nombre es obligatorio.',
            'apellido_obli' => 'El primer apellido es obligatorio.',
            'fecha_naci_obli' => 'La fecha de nacimiento es obligatoria.',
            'pais_obli' => 'El pais obligatorio.',
            'tipo_obli' => 'El primer tipo de documento es obligatorio.',
            'numero_obli' => 'El numero de identificación es obligatorio.',
            'fecha_obli' => 'La fecha de expedición es obligatoria.',
            'email_obli' => 'El correo electronico es obligatorio.',
            'dni_front_obli' => 'La foto frontal del DNI es obligatoria.',
            'pasaporte_obli' => 'La foto frontal del PASAPORTE es obligatoria.',
            'sexo_obli' => 'El sexo es obligatorio.',
            'Correcto' => 'Correcto!',

        ];

        $idiomaCliente = $cliente->nacionalidad;
        $nombreArchivo = 'traducciones_' . $idiomaCliente . '.json';
        $path = storage_path('app/public/' . $nombreArchivo);

        if (file_exists($path)) {
            // Leer el contenido del archivo si ya existe
            $textosTraducidos = json_decode(file_get_contents($path), true);
        } else {
            // Si no existe el archivo, hacer la petición a chatGpt
            $traduccion = $this->chatGpt('Puedes traducirme este array al idioma '. $idiomaCliente.', manteniendo la propiedad y traduciendo solo el valor. contestame solo con el array traducido, no me expliques nada devuelve solo el json en formato texto donde no se envie como code, te adjunto el array: ' . json_encode($textos));
            $textosTraducidos = json_decode($traduccion['messages']['choices'][0]['message']['content'], true);

            // Guardar la traducción en un nuevo archivo
            file_put_contents($path, json_encode($textosTraducidos));
        }

        $textos = $textosTraducidos;
        
        return view('dni.index', compact('id', 'paises', 'reserva', 'data', 'textos','paisCliente'));
    }


    public function chatGpt($texto) 
    {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        // Configurar los parámetros de la solicitud
        $url = 'https://api.openai.com/v1/chat/completions';
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer '. $token
        );


        $data = array(
            "messages" => [
                [
                    "role" => "user",
                    'content' => $texto
                ]
            ], 
            "model" => "gpt-4-1106-preview",
            "temperature" => 0,
            "max_tokens" => 1000,
            "top_p" => 1,
            "frequency_penalty" => 0,
            "presence_penalty" => 0,
            "stop" => ["_END"]
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
            //    'messages' => $response_data['choices'][0]['text']
            'messages' => $response_data
            ];
            //  Storage::disk('local')->put('respuestaFuncionChapt.txt', $responseReturn );

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
                    $huesped->reserva_id = $reserva->id;
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
                        'reserva_id' => $reserva->id,
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
