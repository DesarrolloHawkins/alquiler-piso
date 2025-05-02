<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\MensajeAuto;
use App\Models\Photo;
use App\Models\Reserva;
use Illuminate\Http\Request;

class ClientesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'id'); // Default sort column
        $order = $request->get('order', 'asc'); // Default sort order

        $clientes = Cliente::where(function($query) {
            $query->where('inactivo', '!=', 1)
                  ->orWhereNull('inactivo');
        })
        ->where(function ($query) use ($search) {
            $query->where('alias', 'like', '%'.$search.'%')
                  ->orWhere('apellido1', 'like', '%'.$search.'%')
                  ->orWhere('apellido2', 'like', '%'.$search.'%')
                  ->orWhere('nombre', 'like', '%'.$search.'%')
                  ->orWhere('idioma', 'like', '%'.$search.'%');
        })
        ->orderBy($sort, $order)
        ->paginate(10);


        return view('Clientes.index', compact('clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
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
        return view('Clientes.create', compact('paises','idiomaAPais'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Definir las reglas de validación
        $rules = [
            'nombre' => 'required|string|max:255',
            'apellido1' => 'required|string|max:255',
            'apellido2' => 'nullable|string|max:255',
            // 'fecha_nacimiento' => 'required|date',
            'sexo' => 'required|string|max:255',
            'telefono' => 'required|string|max:20', // Ajusta la longitud máxima según tus necesidades
            'email' => 'required|email|max:255|unique:clientes,email', // Asegúrate de cambiar 'clientes' al nombre de tu tabla
            'idiomas' => 'nullable|string|max:255', // Este campo es de sólo lectura en el formulario, considera si necesitas validarlo
            'nacionalidad' => 'required|string|max:255',
            // 'tipo_documento' => 'required|string|max:255|in:DNI,Pasaporte', // Asegúrate de que el tipo de documento esté dentro de los valores permitidos
            // 'num_identificacion' => 'required|string|max:255',
            // 'fecha_expedicion_doc' => 'required|date',
            'direccion' => 'nullable|string|max:255',
            'localidad' => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|max:255',
            'provincia' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:255',
            'tipo_documento' => 'nullable'
        ];

        // Validar los datos del formulario
        $validatedData = $request->validate($rules);

        // Procesar los datos validados...
        $cliente = new Cliente($validatedData);
        $cliente->save();

        // Redireccionar o enviar una respuesta apropiada
        return redirect()->route('clientes.index')->with('status', 'Cliente creado con éxito!');
    }


    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente)
    {
        $reservas = Reserva::where('cliente_id', $cliente->id)->get();
        $mensajes = MensajeAuto::where('cliente_id', $cliente->id)->get();
        $photos = Photo::where('cliente_id', $cliente->id)->get();
        return view('Clientes.show', compact('cliente', 'mensajes', 'photos', 'reservas'));
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cliente = Cliente::find($id);
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
        return view('Clientes.edit', compact('cliente','idiomaAPais','paises'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        // Encuentra el cliente por ID
        $cliente = Cliente::findOrFail($id);

        // Definir las reglas de validación
        $rules = [
            'alias' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',
            'apellido1' => 'required|string|max:255',
            'apellido2' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'sexo' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'required|email|max:255|unique:clientes,email,' . $cliente->id, // Ignora el email del cliente actual
            'nacionalidad' => 'required|string|max:255',
            'tipo_documento' => 'required|string|max:255|in:DNI,Pasaporte',
            'num_identificacion' => 'required|string|max:255',
            'fecha_expedicion_doc' => 'required|date',
            'idiomas' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'localidad' => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|max:255',
            'provincia' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:255',
        ];
        // Validar los datos del formulario
        $validatedData = $request->validate($rules);

        // Actualizar el cliente con los datos validados
        $cliente->update($validatedData);

        // Redireccionar a una ruta de éxito o devolver una respuesta
        return redirect()->route('clientes.index')->with('status', 'Cliente actualizado con éxito!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->inactivo = 1;
        $cliente->save();

        return redirect()->route('clientes.index')->with('status', 'Cliente inactivado con exito!');
    }
}
