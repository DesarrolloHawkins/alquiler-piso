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


        $paisesEuropeos = [
            "ALBANIA", "ALEMANIA", "AUSTRIA", "BELGICA", "BULGARIA",
            "CHIPRE", "CROACIA", "DINAMARCA", "ESLOVAQUIA", "ESLOVENIA", "ESPAÑA",
            "ESTONIA", "FINLANDIA", "FRANCIA", "GRECIA", "HUNGRIA", "IRLANDA",
            "ISLANDIA", "ITALIA", "LETONIA", "LITUANIA", "LUXEMBURGO",
            "MALTA", "NORUEGA", "PAISES BAJOS", "POLONIA",
            "PORTUGAL", "REINO UNIDO", "REPUBLICA CHECA", "RUMANIA",
            "SUECIA"
        ];
        $paisesDni = [
            "AFGANISTAN" => ["value" => "A9401AAAAA", "isEuropean" => in_array("AFGANISTAN", $paisesEuropeos)],
            "AFRICA" => ["value" => "A9399AAAAA", "isEuropean" => in_array("AFRICA", $paisesEuropeos)],
            "ALBANIA" => ["value" => "A9102AAAAA", "isEuropean" => in_array("ALBANIA", $paisesEuropeos)],
            "ALEMANIA" => ["value" => "A9103AAAAA", "isEuropean" => in_array("ALEMANIA", $paisesEuropeos)],
            "AMERICA" => ["value" => "A9299AAAAA", "isEuropean" => in_array("AMERICA", $paisesEuropeos)],
            "ANDORRA" => ["value" => "A9133AAAAA", "isEuropean" => in_array("ANDORRA", $paisesEuropeos)],
            "ANGOLA" => ["value" => "A9301AAAAA", "isEuropean" => in_array("ANGOLA", $paisesEuropeos)],
            "ANTIGUA BARBUDA" => ["value" => "A9255AAAAA", "isEuropean" => in_array("ANTIGUA BARBUDA", $paisesEuropeos)],
            "ANTILLAS NEERLANDESAS" => ["value" => "A9200AAAAA", "isEuropean" => in_array("ANTILLAS NEERLANDESAS", $paisesEuropeos)],
            "APATRIDA" => ["value" => "A9600AAAAA", "isEuropean" => in_array("APATRIDA", $paisesEuropeos)],
            "ARABIA SAUDITA" => ["value" => "A9403AAA1A", "isEuropean" => in_array("ARABIA SAUDITA", $paisesEuropeos)],
            "ARGELIA" => ["value" => "A9304AAAAA", "isEuropean" => in_array("ARGELIA", $paisesEuropeos)],
            "ARGENTINA" => ["value" => "A9202AAAAA", "isEuropean" => in_array("ARGENTINA", $paisesEuropeos)],
            "ARMENIA" => ["value" => "A9142AAAAA", "isEuropean" => in_array("ARMENIA", $paisesEuropeos)],
            "ARUBA" => ["value" => "A9257AAAAA", "isEuropean" => in_array("ARUBA", $paisesEuropeos)],
            "ASIA" => ["value" => "A9499AAAAA", "isEuropean" => in_array("ASIA", $paisesEuropeos)],
            "AUSTRALIA" => ["value" => "A9500AAAAA", "isEuropean" => in_array("AUSTRALIA", $paisesEuropeos)],
            "AUSTRIA" => ["value" => "A9104AAAAA", "isEuropean" => in_array("AUSTRIA", $paisesEuropeos)],
            "AZERBAYAN" => ["value" => "A9143AAA2A", "isEuropean" => in_array("AZERBAYAN", $paisesEuropeos)],
            "BAHAMAS" => ["value" => "A9203AAAAA", "isEuropean" => in_array("BAHAMAS", $paisesEuropeos)],
            "BAHREIN" => ["value" => "A9405AAAAA", "isEuropean" => in_array("BAHREIN", $paisesEuropeos)],
            "BANGLADESH" => ["value" => "A9432AAAAA", "isEuropean" => in_array("BANGLADESH", $paisesEuropeos)],
            "BARBADOS" => ["value" => "A9205AAAAA", "isEuropean" => in_array("BARBADOS", $paisesEuropeos)],
            "BELGICA" => ["value" => "A9105AAAAA", "isEuropean" => in_array("BELGICA", $paisesEuropeos)],
            "BELICE" => ["value" => "A9207AAAAA", "isEuropean" => in_array("BELICE", $paisesEuropeos)],
            "BHUTAN" => ["value" => "A9407AAAAA", "isEuropean" => in_array("BHUTAN", $paisesEuropeos)],
            "BIELORRUSIA" => ["value" => "A9144AAAAA", "isEuropean" => in_array("BIELORRUSIA", $paisesEuropeos)],
            "BOLIVIA" => ["value" => "A9204AAAAA", "isEuropean" => in_array("BOLIVIA", $paisesEuropeos)],
            "BOSNIA HERZEGOVINA" => ["value" => "A9156AAAAA", "isEuropean" => in_array("BOSNIA HERZEGOVINA", $paisesEuropeos)],
            "BOTSWANA" => ["value" => "A9305AAAAA", "isEuropean" => in_array("BOTSWANA", $paisesEuropeos)],
            "BRASIL" => ["value" => "A9206AAAAA", "isEuropean" => in_array("BRASIL", $paisesEuropeos)],
            "BRUNEI" => ["value" => "A9409AAAAA", "isEuropean" => in_array("BRUNEI", $paisesEuropeos)],
            "BULGARIA" => ["value" => "A9134AAAAA", "isEuropean" => in_array("BULGARIA", $paisesEuropeos)],
            "BURKINA FASO" => ["value" => "A9303AAAAA", "isEuropean" => in_array("BURKINA FASO", $paisesEuropeos)],
            "BURUNDI" => ["value" => "A9302AAAAA", "isEuropean" => in_array("BURUNDI", $paisesEuropeos)],
            "BUTAN" => ["value" => "A9442AAAAA", "isEuropean" => in_array("BUTAN", $paisesEuropeos)],
            "CABO VERDE" => ["value" => "A9308AAAAA", "isEuropean" => in_array("CABO VERDE", $paisesEuropeos)],
            "CAMERUN" => ["value" => "A9307AAAAA", "isEuropean" => in_array("CAMERUN", $paisesEuropeos)],
            "CANADA" => ["value" => "A9259AAAAA", "isEuropean" => in_array("CANADA", $paisesEuropeos)],
            "CATAR" => ["value" => "A9408AAAAA", "isEuropean" => in_array("CATAR", $paisesEuropeos)],
            "CENTROAMERICA" => ["value" => "A9201AAAAA", "isEuropean" => in_array("CENTROAMERICA", $paisesEuropeos)],
            "CHAD" => ["value" => "A9306AAAAA", "isEuropean" => in_array("CHAD", $paisesEuropeos)],
            "CHECOSLOVAQUIA" => ["value" => "A9114AAAAA", "isEuropean" => in_array("CHECOSLOVAQUIA", $paisesEuropeos)],
            "CHILE" => ["value" => "A9212AAAAA", "isEuropean" => in_array("CHILE", $paisesEuropeos)],
            "CHINA" => ["value" => "A9433AAAAA", "isEuropean" => in_array("CHINA", $paisesEuropeos)],
            "CHIPRE" => ["value" => "A9135AAAAA", "isEuropean" => in_array("CHIPRE", $paisesEuropeos)],
            "COLOMBIA" => ["value" => "A9213AAAAA", "isEuropean" => in_array("COLOMBIA", $paisesEuropeos)],
            "COMORAS" => ["value" => "A9309AAAAA", "isEuropean" => in_array("COMORAS", $paisesEuropeos)],
            "CONGO" => ["value" => "A9311AAAAA", "isEuropean" => in_array("CONGO", $paisesEuropeos)],
            "COREA" => ["value" => "A9450AAAAA", "isEuropean" => in_array("COREA", $paisesEuropeos)],
            "COREA NORTE" => ["value" => "A9451AAAAA", "isEuropean" => in_array("COREA NORTE", $paisesEuropeos)],
            "COREA SUR" => ["value" => "A9452AAAAA", "isEuropean" => in_array("COREA SUR", $paisesEuropeos)],
            "COSTA DE MARFIL" => ["value" => "A9310AAAAA", "isEuropean" => in_array("COSTA DE MARFIL", $paisesEuropeos)],
            "COSTA RICA" => ["value" => "A9214AAAAA", "isEuropean" => in_array("COSTA RICA", $paisesEuropeos)],
            "CROACIA" => ["value" => "A9136AAAAA", "isEuropean" => in_array("CROACIA", $paisesEuropeos)],
            "CUBA" => ["value" => "A9250AAAAA", "isEuropean" => in_array("CUBA", $paisesEuropeos)],
            "DINAMARCA" => ["value" => "A9106AAAAA", "isEuropean" => in_array("DINAMARCA", $paisesEuropeos)],
            "DJIBOUTI" => ["value" => "A9312AAAAA", "isEuropean" => in_array("DJIBOUTI", $paisesEuropeos)],
            "DOMINICA" => ["value" => "A9260AAAAA", "isEuropean" => in_array("DOMINICA", $paisesEuropeos)],
            "ECUADOR" => ["value" => "A9215AAAAA", "isEuropean" => in_array("ECUADOR", $paisesEuropeos)],
            "EGIPTO" => ["value" => "A9313AAAAA", "isEuropean" => in_array("EGIPTO", $paisesEuropeos)],
            "EL SALVADOR" => ["value" => "A9216AAAAA", "isEuropean" => in_array("EL SALVADOR", $paisesEuropeos)],
            "EMIRATOS ARABES UNIDOS" => ["value" => "A9411AAAAA", "isEuropean" => in_array("EMIRATOS ARABES UNIDOS", $paisesEuropeos)],
            "ERITREA" => ["value" => "A9314AAAAA", "isEuropean" => in_array("ERITREA", $paisesEuropeos)],
            "ESLOVAQUIA" => ["value" => "A9137AAAAA", "isEuropean" => in_array("ESLOVAQUIA", $paisesEuropeos)],
            "ESLOVENIA" => ["value" => "A9138AAAAA", "isEuropean" => in_array("ESLOVENIA", $paisesEuropeos)],
            "ESPAÑA" => ["value" => "A9107AAAAA", "isEuropean" => in_array("ESPAÑA", $paisesEuropeos)],
            "ESTADOS UNIDOS" => ["value" => "A9261AAAAA", "isEuropean" => in_array("ESTADOS UNIDOS", $paisesEuropeos)],
            "ESTONIA" => ["value" => "A9139AAAAA", "isEuropean" => in_array("ESTONIA", $paisesEuropeos)],
            "ETIOPIA" => ["value" => "A9315AAAAA", "isEuropean" => in_array("ETIOPIA", $paisesEuropeos)],
            "EUROPA" => ["value" => "A9398AAAAA", "isEuropean" => in_array("EUROPA", $paisesEuropeos)],
            "FIJI" => ["value" => "A9503AAAAA", "isEuropean" => in_array("FIJI", $paisesEuropeos)],
            "FILIPINAS" => ["value" => "A9444AAAAA", "isEuropean" => in_array("FILIPINAS", $paisesEuropeos)],
            "FINLANDIA" => ["value" => "A9108AAAAA", "isEuropean" => in_array("FINLANDIA", $paisesEuropeos)],
            "FRANCIA" => ["value" => "A9109AAAAA", "isEuropean" => in_array("FRANCIA", $paisesEuropeos)],
            "GABON" => ["value" => "A9316AAAAA", "isEuropean" => in_array("GABON", $paisesEuropeos)],
            "GAMBIA" => ["value" => "A9323AAAAA", "isEuropean" => in_array("GAMBIA", $paisesEuropeos)],
            "GEORGIA" => ["value" => "A9145AAAAA", "isEuropean" => in_array("GEORGIA", $paisesEuropeos)],
            "GHANA" => ["value" => "A9322AAAAA", "isEuropean" => in_array("GHANA", $paisesEuropeos)],
            "GRECIA" => ["value" => "A9113AAAAA", "isEuropean" => in_array("GRECIA", $paisesEuropeos)],
            "GUATEMALA" => ["value" => "A9228AAAAA", "isEuropean" => in_array("GUATEMALA", $paisesEuropeos)],
            "GUINEA" => ["value" => "A9325AAA3A", "isEuropean" => in_array("GUINEA", $paisesEuropeos)],
            "GUINEA BISSAU" => ["value" => "A9328AAA1A", "isEuropean" => in_array("GUINEA BISSAU", $paisesEuropeos)],
            "GUINEA ECUATORIAL" => ["value" => "A9324AAAAA", "isEuropean" => in_array("GUINEA ECUATORIAL", $paisesEuropeos)],
            "GUYANA" => ["value" => "A9225AAAAA", "isEuropean" => in_array("GUYANA", $paisesEuropeos)],
            "HAITI" => ["value" => "A9230AAAAA", "isEuropean" => in_array("HAITI", $paisesEuropeos)],
            "HONDURAS" => ["value" => "A9232AAAAA", "isEuropean" => in_array("HONDURAS", $paisesEuropeos)],
            "HONG KONG CHINO" => ["value" => "A9462AAAAA", "isEuropean" => in_array("HONG KONG CHINO", $paisesEuropeos)],
            "HUNGRIA" => ["value" => "A9114AAAAA", "isEuropean" => in_array("HUNGRIA", $paisesEuropeos)],
            "IFNI" => ["value" => "A9395AAAAA", "isEuropean" => in_array("IFNI", $paisesEuropeos)],
            "INDIA" => ["value" => "A9412AAAAA", "isEuropean" => in_array("INDIA", $paisesEuropeos)],
            "INDONESIA" => ["value" => "A9414AAAAA", "isEuropean" => in_array("INDONESIA", $paisesEuropeos)],
            "IRAK" => ["value" => "A9413AAAAA", "isEuropean" => in_array("IRAK", $paisesEuropeos)],
            "IRAN" => ["value" => "A9415AAAAA", "isEuropean" => in_array("IRAN", $paisesEuropeos)],
            "IRLANDA" => ["value" => "A9115AAAAA", "isEuropean" => in_array("IRLANDA", $paisesEuropeos)],
            "ISLANDIA" => ["value" => "A9116AAAAA", "isEuropean" => in_array("ISLANDIA", $paisesEuropeos)],
            "ISLAS MARIANAS NORTE" => ["value" => "A9518AAAAA", "isEuropean" => in_array("ISLAS MARIANAS NORTE", $paisesEuropeos)],
            "ISLAS MARSHALL" => ["value" => "A9520AAAAA", "isEuropean" => in_array("ISLAS MARSHALL", $paisesEuropeos)],
            "ISLAS SALOMON" => ["value" => "A9551AAA1A", "isEuropean" => in_array("ISLAS SALOMON", $paisesEuropeos)],
            "ISRAEL" => ["value" => "A9417AAAAA", "isEuropean" => in_array("ISRAEL", $paisesEuropeos)],
            "ITALIA" => ["value" => "A9117AAAAA", "isEuropean" => in_array("ITALIA", $paisesEuropeos)],
            "JAMAICA" => ["value" => "A9233AAAAA", "isEuropean" => in_array("JAMAICA", $paisesEuropeos)],
            "JAPON" => ["value" => "A9416AAAAA", "isEuropean" => in_array("JAPON", $paisesEuropeos)],
            "JORDANIA" => ["value" => "A9419AAAAA", "isEuropean" => in_array("JORDANIA", $paisesEuropeos)],
            "KAZAJSTAN" => ["value" => "A9465AAAAA", "isEuropean" => in_array("KAZAJSTAN", $paisesEuropeos)],
            "KENIA" => ["value" => "A9336AAAAA", "isEuropean" => in_array("KENIA", $paisesEuropeos)],
            "KIRIBATI" => ["value" => "A9501AAAAA", "isEuropean" => in_array("KIRIBATI", $paisesEuropeos)],
            "KUWAIT" => ["value" => "A9421AAAAA", "isEuropean" => in_array("KUWAIT", $paisesEuropeos)],
            "LAOS" => ["value" => "A9418AAAAA", "isEuropean" => in_array("LAOS", $paisesEuropeos)],
            "LESOTHO" => ["value" => "A9337AAAAA", "isEuropean" => in_array("LESOTHO", $paisesEuropeos)],
            "LETONIA" => ["value" => "A9138AAAAA", "isEuropean" => in_array("LETONIA", $paisesEuropeos)],
            "LIBANO" => ["value" => "A9423AAAAA", "isEuropean" => in_array("LIBANO", $paisesEuropeos)],
            "LIBERIA" => ["value" => "A9342AAAAA", "isEuropean" => in_array("LIBERIA", $paisesEuropeos)],
            "LIBIA" => ["value" => "A9344AAAAA", "isEuropean" => in_array("LIBIA", $paisesEuropeos)],
            "LIECHTENSTEIN" => ["value" => "A9118AAAAA", "isEuropean" => in_array("LIECHTENSTEIN", $paisesEuropeos)],
            "LITUANIA" => ["value" => "A9139AAAAA", "isEuropean" => in_array("LITUANIA", $paisesEuropeos)],
            "LUXEMBURGO" => ["value" => "A9119AAAAA", "isEuropean" => in_array("LUXEMBURGO", $paisesEuropeos)],
            "MACAO" => ["value" => "A9463AAAAA", "isEuropean" => in_array("MACAO", $paisesEuropeos)],
            "MACEDONIA" => ["value" => "A9159AAAAA", "isEuropean" => in_array("MACEDONIA", $paisesEuropeos)],
            "MADAGASCAR" => ["value" => "A9354AAAAA", "isEuropean" => in_array("MADAGASCAR", $paisesEuropeos)],
            "MALASIA" => ["value" => "A9425AAAAA", "isEuropean" => in_array("MALASIA", $paisesEuropeos)],
            "MALAWI" => ["value" => "A9346AAAAA", "isEuropean" => in_array("MALAWI", $paisesEuropeos)],
            "MALDIVAS" => ["value" => "A9436AAAAA", "isEuropean" => in_array("MALDIVAS", $paisesEuropeos)],
            "MALI" => ["value" => "A9347AAAAA", "isEuropean" => in_array("MALI", $paisesEuropeos)],
            "MALTA" => ["value" => "A9120AAAAA", "isEuropean" => in_array("MALTA", $paisesEuropeos)],
            "MARRUECOS" => ["value" => "A9348AAAAA", "isEuropean" => in_array("MARRUECOS", $paisesEuropeos)],
            "MAURICIO" => ["value" => "A9349AAAAA", "isEuropean" => in_array("MAURICIO", $paisesEuropeos)],
            "MAURITANIA" => ["value" => "A9350AAAAA", "isEuropean" => in_array("MAURITANIA", $paisesEuropeos)],
            "MEXICO" => ["value" => "A9234AAA1A", "isEuropean" => in_array("MEXICO", $paisesEuropeos)],
            "MOLDAVIA" => ["value" => "A9148AAAAA", "isEuropean" => in_array("MOLDAVIA", $paisesEuropeos)],
            "MONACO" => ["value" => "A9121AAAAA", "isEuropean" => in_array("MONACO", $paisesEuropeos)],
            "MONGOLIA" => ["value" => "A9427AAAAA", "isEuropean" => in_array("MONGOLIA", $paisesEuropeos)],
            "MONTENEGRO" => ["value" => "A9160AAAAA", "isEuropean" => in_array("MONTENEGRO", $paisesEuropeos)],
            "MOZAMBIQUE" => ["value" => "A9351AAAAA", "isEuropean" => in_array("MOZAMBIQUE", $paisesEuropeos)],
            "MYANMAR" => ["value" => "A9400AAAAA", "isEuropean" => in_array("MYANMAR", $paisesEuropeos)],
            "NAMIBIA" => ["value" => "A9353AAAAA", "isEuropean" => in_array("NAMIBIA", $paisesEuropeos)],
            "NAURU" => ["value" => "A9541AAAAA", "isEuropean" => in_array("NAURU", $paisesEuropeos)],
            "NEPAL" => ["value" => "A9541AAAAA", "isEuropean" => in_array("NEPAL", $paisesEuropeos)],
            "NICARAGUA" => ["value" => "A9236AAAAA", "isEuropean" => in_array("NICARAGUA", $paisesEuropeos)],
            "NIGER" => ["value" => "A9360AAAAA", "isEuropean" => in_array("NIGER", $paisesEuropeos)],
            "NIGERIA" => ["value" => "A9352AAAAA", "isEuropean" => in_array("NIGERIA", $paisesEuropeos)],
            "NORUEGA" => ["value" => "A9122AAAAA", "isEuropean" => in_array("NORUEGA", $paisesEuropeos)],
            "NUEVA ZELANDA" => ["value" => "A9540AAAAA", "isEuropean" => in_array("NUEVA ZELANDA", $paisesEuropeos)],
            "OCEANIA" => ["value" => "A9599AAAAA", "isEuropean" => in_array("OCEANIA", $paisesEuropeos)],
            "OMAN" => ["value" => "A9444AAAAA", "isEuropean" => in_array("OMAN", $paisesEuropeos)],
            "PAISES BAJOS" => ["value" => "A9123AAA1A", "isEuropean" => in_array("PAISES BAJOS", $paisesEuropeos)],
            "PAKISTAN" => ["value" => "A9424AAA1A", "isEuropean" => in_array("PAKISTAN", $paisesEuropeos)],
            "PALESTINA" => ["value" => "A9440AAAAA", "isEuropean" => in_array("PALESTINA", $paisesEuropeos)],
            "PANAMA" => ["value" => "A9238AAAAA", "isEuropean" => in_array("PANAMA", $paisesEuropeos)],
            "PAPUA NUEVA GUINEA" => ["value" => "A9542AAAAA", "isEuropean" => in_array("PAPUA NUEVA GUINEA", $paisesEuropeos)],
            "PARAGUAY" => ["value" => "A9240AAAAA", "isEuropean" => in_array("PARAGUAY", $paisesEuropeos)],
            "PERU" => ["value" => "A9242AAAAA", "isEuropean" => in_array("PERU", $paisesEuropeos)],
            "POLONIA" => ["value" => "A9124AAAAA", "isEuropean" => in_array("POLONIA", $paisesEuropeos)],
            "PORTUGAL" => ["value" => "A9125AAAAA", "isEuropean" => in_array("PORTUGAL", $paisesEuropeos)],
            "PUERTO RICO" => ["value" => "A9244AAAAA", "isEuropean" => in_array("PUERTO RICO", $paisesEuropeos)],
            "QATAR" => ["value" => "A9431AAAAA", "isEuropean" => in_array("QATAR", $paisesEuropeos)],
            "REINO UNIDO" => ["value" => "A9112AAA1A", "isEuropean" => in_array("REINO UNIDO", $paisesEuropeos)],
            "REPUBLICA BENIN" => ["value" => "A9302AAA1A", "isEuropean" => in_array("REPUBLICA BENIN", $paisesEuropeos)],
            "REPUBLICA CENTROAFRICANA" => ["value" => "A9310AAA1A", "isEuropean" => in_array("REPUBLICA CENTROAFRICANA", $paisesEuropeos)],
            "REPUBLICA CHECA" => ["value" => "A9157AAAAA", "isEuropean" => in_array("REPUBLICA CHECA", $paisesEuropeos)],
            "REPUBLICA CONGO" => ["value" => "A9312AAA1A", "isEuropean" => in_array("REPUBLICA CONGO", $paisesEuropeos)],
            "REPUBLICA DEMOCRATICA CONGO" => ["value" => "A9380AAAAA", "isEuropean" => in_array("REPUBLICA DEMOCRATICA CONGO", $paisesEuropeos)],
            "REPUBLICA DOMINICANA" => ["value" => "A9218AAA1A", "isEuropean" => in_array("REPUBLICA DOMINICANA", $paisesEuropeos)],
            "REPUBLICA GRANADA" => ["value" => "A9229AAAAA", "isEuropean" => in_array("REPUBLICA GRANADA", $paisesEuropeos)],
            "REPUBLICA KIRGUISTAN" => ["value" => "A9466AAA1A", "isEuropean" => in_array("REPUBLICA KIRGUISTAN", $paisesEuropeos)],
            "REPUBLICA SUDAN SUR" => ["value" => "A9369AAAAA", "isEuropean" => in_array("REPUBLICA SUDAN SUR", $paisesEuropeos)],
            "RIO MUNI" => ["value" => "A9397AAAAA", "isEuropean" => in_array("RIO MUNI", $paisesEuropeos)],
            "RUANDA" => ["value" => "A9306AAAAA", "isEuropean" => in_array("RUANDA", $paisesEuropeos)],
            "RUMANIA" => ["value" => "A9127AAAAA", "isEuropean" => in_array("RUMANIA", $paisesEuropeos)],
            "RUSIA" => ["value" => "A9149AAAAA", "isEuropean" => in_array("RUSIA", $paisesEuropeos)],
            "SAHARA" => ["value" => "A9398AAAAA", "isEuropean" => in_array("SAHARA", $paisesEuropeos)],
            "SAINT KITTS NEVIS" => ["value" => "A9256AAA1A", "isEuropean" => in_array("SAINT KITTS NEVIS", $paisesEuropeos)],
            "SALVADOR" => ["value" => "A9220AAAAA", "isEuropean" => in_array("SALVADOR", $paisesEuropeos)],
            "SAMOA OCCIDENTAL" => ["value" => "A9552AAAAA", "isEuropean" => in_array("SAMOA OCCIDENTAL", $paisesEuropeos)],
            "SAN MARINO" => ["value" => "A9135AAAAA", "isEuropean" => in_array("SAN MARINO", $paisesEuropeos)],
            "SAN MARTIN" => ["value" => "A9259AAAAA", "isEuropean" => in_array("SAN MARTIN", $paisesEuropeos)],
            "SAN VICENTE GRANADINAS" => ["value" => "A9254AAA1A", "isEuropean" => in_array("SAN VICENTE GRANADINAS", $paisesEuropeos)],
            "SANTA LUCIA" => ["value" => "A9253AAAAA", "isEuropean" => in_array("SANTA LUCIA", $paisesEuropeos)],
            "SANTA SEDE" => ["value" => "A9136AAA2A", "isEuropean" => in_array("SANTA SEDE", $paisesEuropeos)],
            "SANTO TOME PRINCIPE" => ["value" => "A9361AAAAA", "isEuropean" => in_array("SANTO TOME PRINCIPE", $paisesEuropeos)],
            "SENEGAL" => ["value" => "A9362AAAAA", "isEuropean" => in_array("SENEGAL", $paisesEuropeos)],
            "SERBIA" => ["value" => "A9155AAAAA", "isEuropean" => in_array("SERBIA", $paisesEuropeos)],
            "SEYCHELLES" => ["value" => "A9363AAAAA", "isEuropean" => in_array("SEYCHELLES", $paisesEuropeos)],
            "SIERRA LEONA" => ["value" => "A9364AAAAA", "isEuropean" => in_array("SIERRA LEONA", $paisesEuropeos)],
            "SINGAPUR" => ["value" => "A9426AAAAA", "isEuropean" => in_array("SINGAPUR", $paisesEuropeos)],
            "SIRIA" => ["value" => "A9433AAAAA", "isEuropean" => in_array("SIRIA", $paisesEuropeos)],
            "SOMALIA" => ["value" => "A9365AAAAA", "isEuropean" => in_array("SOMALIA", $paisesEuropeos)],
            "SRI LANKA" => ["value" => "A9404AAAAA", "isEuropean" => in_array("SRI LANKA", $paisesEuropeos)],
            "SUDAFRICA" => ["value" => "A9367AAAAA", "isEuropean" => in_array("SUDAFRICA", $paisesEuropeos)],
            "SUDAN" => ["value" => "A9368AAAAA", "isEuropean" => in_array("SUDAN", $paisesEuropeos)],
            "SUDAN SUR" => ["value" => "A9369AAA1A", "isEuropean" => in_array("SUDAN SUR", $paisesEuropeos)],
            "SUECIA" => ["value" => "A9128AAAAA", "isEuropean" => in_array("SUECIA", $paisesEuropeos)],
            "SUIZA" => ["value" => "A9129AAAAA", "isEuropean" => in_array("SUIZA", $paisesEuropeos)],
            "SURINAM" => ["value" => "A9250AAAAA", "isEuropean" => in_array("SURINAM", $paisesEuropeos)],
            "SWAZILANDIA" => ["value" => "A9371AAAAA", "isEuropean" => in_array("SWAZILANDIA", $paisesEuropeos)],
            "TADJIKISTAN" => ["value" => "A9469AAAAA", "isEuropean" => in_array("TADJIKISTAN", $paisesEuropeos)],
            "TAILANDIA" => ["value" => "A9428AAA1A", "isEuropean" => in_array("TAILANDIA", $paisesEuropeos)],
            "TAIWAN TAIPEI" => ["value" => "A9408AAA3A", "isEuropean" => in_array("TAIWAN TAIPEI", $paisesEuropeos)],
            "TANZANIA" => ["value" => "A9370AAAAA", "isEuropean" => in_array("TANZANIA", $paisesEuropeos)],
            "TIMOR ORIENTAL" => ["value" => "A9464AAAAA", "isEuropean" => in_array("TIMOR ORIENTAL", $paisesEuropeos)],
            "TOGO" => ["value" => "A9374AAAAA", "isEuropean" => in_array("TOGO", $paisesEuropeos)],
            "TONGA" => ["value" => "A9554AAAAA", "isEuropean" => in_array("TONGA", $paisesEuropeos)],
            "TRINIDAD TOBAGO" => ["value" => "A9245AAAAA", "isEuropean" => in_array("TRINIDAD TOBAGO", $paisesEuropeos)],
            "TUNEZ" => ["value" => "A9378AAAAA", "isEuropean" => in_array("TUNEZ", $paisesEuropeos)],
            "TURKMENISTAN" => ["value" => "A9467AAA1A", "isEuropean" => in_array("TURKMENISTAN", $paisesEuropeos)],
            "TURQUIA" => ["value" => "A9130AAAAA", "isEuropean" => in_array("TURQUIA", $paisesEuropeos)],
            "TUVALU" => ["value" => "A9560AAAAA", "isEuropean" => in_array("TUVALU", $paisesEuropeos)],
            "UCRANIA" => ["value" => "A9152AAAAA", "isEuropean" => in_array("UCRANIA", $paisesEuropeos)],
            "UGANDA" => ["value" => "A9358AAAAA", "isEuropean" => in_array("UGANDA", $paisesEuropeos)],
            "UNION EUROPEA" => ["value" => "A9190AAA1A", "isEuropean" => in_array("UNION EUROPEA", $paisesEuropeos)],
            "URUGUAY" => ["value" => "A9246AAAAA", "isEuropean" => in_array("URUGUAY", $paisesEuropeos)],
            "UZBEKISTAN" => ["value" => "A9468AAAAA", "isEuropean" => in_array("UZBEKISTAN", $paisesEuropeos)],
            "VANUATU" => ["value" => "A9565AAAAA", "isEuropean" => in_array("VANUATU", $paisesEuropeos)],
            "VENEZUELA" => ["value" => "A9248AAAAA", "isEuropean" => in_array("VENEZUELA", $paisesEuropeos)],
            "VIETNAM" => ["value" => "A9430AAAAA", "isEuropean" => in_array("VIETNAM", $paisesEuropeos)],
            "YEMEN" => ["value" => "A9434AAAAA", "isEuropean" => in_array("YEMEN", $paisesEuropeos)],
            "ZAMBIA" => ["value" => "A9382AAAAA", "isEuropean" => in_array("ZAMBIA", $paisesEuropeos)],
            "ZIMBABWE" => ["value" => "A9357AAAAA", "isEuropean" => in_array("ZIMBABWE", $paisesEuropeos)]
        ];


        $optionesTipo = [
            [
                "codigo" => "P",
                "descripcion" => "PASAPORTE",
            ],
            [
                "codigo" => "I",
                "descripcion" => "CARTA DE IDENTIDAD EXTRANJERA",
            ],
            [
                "codigo" => "N",
                "descripcion" => "NIE O TARJETA ESPAÑOLA DE EXTRANJEROS",
            ],
            [
                "codigo" => "X",
                "descripcion" => "PERMISO DE RESIDENCIA DE ESTADO MIEMBRO DE LA UE",
            ],
            [
                "codigo" => "C",
                "descripcion" => "PERMISO CONDUCIR ESPAÑOL",
            ],
            [
                "codigo" => "D",
                "descripcion" => "DNI",
            ]
        ];

        //dd($data);
        return view('dni.index', compact('id', 'paises', 'reserva', 'data', 'textos','paisCliente','paisesDni', 'optionesTipo'));
    }

    public function listadoPaises(){
        $paisesEuropeos = [
            "ALBANIA", "ALEMANIA", "AUSTRIA", "BELGICA", "BULGARIA",
            "CHIPRE", "CROACIA", "DINAMARCA", "ESLOVAQUIA", "ESLOVENIA", "ESPAÑA",
            "ESTONIA", "FINLANDIA", "FRANCIA", "GRECIA", "HUNGRIA", "IRLANDA",
            "ISLANDIA", "ITALIA", "LETONIA", "LITUANIA", "LUXEMBURGO",
            "MALTA", "NORUEGA", "PAISES BAJOS", "POLONIA",
            "PORTUGAL", "REINO UNIDO", "REPUBLICA CHECA", "RUMANIA",
            "SUECIA"
        ];

        $paises = [
            "AFGANISTAN" => ["value" => "A9401AAAAA", "isEuropean" => in_array("AFGANISTAN", $paisesEuropeos)],
            "AFRICA" => ["value" => "A9399AAAAA", "isEuropean" => in_array("AFRICA", $paisesEuropeos)],
            "ALBANIA" => ["value" => "A9102AAAAA", "isEuropean" => in_array("ALBANIA", $paisesEuropeos)],
            "ALEMANIA" => ["value" => "A9103AAAAA", "isEuropean" => in_array("ALEMANIA", $paisesEuropeos)],
            "AMERICA" => ["value" => "A9299AAAAA", "isEuropean" => in_array("AMERICA", $paisesEuropeos)],
            "ANDORRA" => ["value" => "A9133AAAAA", "isEuropean" => in_array("ANDORRA", $paisesEuropeos)],
            "ANGOLA" => ["value" => "A9301AAAAA", "isEuropean" => in_array("ANGOLA", $paisesEuropeos)],
            "ANTIGUA BARBUDA" => ["value" => "A9255AAAAA", "isEuropean" => in_array("ANTIGUA BARBUDA", $paisesEuropeos)],
            "ANTILLAS NEERLANDESAS" => ["value" => "A9200AAAAA", "isEuropean" => in_array("ANTILLAS NEERLANDESAS", $paisesEuropeos)],
            "APATRIDA" => ["value" => "A9600AAAAA", "isEuropean" => in_array("APATRIDA", $paisesEuropeos)],
            "ARABIA SAUDITA" => ["value" => "A9403AAA1A", "isEuropean" => in_array("ARABIA SAUDITA", $paisesEuropeos)],
            "ARGELIA" => ["value" => "A9304AAAAA", "isEuropean" => in_array("ARGELIA", $paisesEuropeos)],
            "ARGENTINA" => ["value" => "A9202AAAAA", "isEuropean" => in_array("ARGENTINA", $paisesEuropeos)],
            "ARMENIA" => ["value" => "A9142AAAAA", "isEuropean" => in_array("ARMENIA", $paisesEuropeos)],
            "ARUBA" => ["value" => "A9257AAAAA", "isEuropean" => in_array("ARUBA", $paisesEuropeos)],
            "ASIA" => ["value" => "A9499AAAAA", "isEuropean" => in_array("ASIA", $paisesEuropeos)],
            "AUSTRALIA" => ["value" => "A9500AAAAA", "isEuropean" => in_array("AUSTRALIA", $paisesEuropeos)],
            "AUSTRIA" => ["value" => "A9104AAAAA", "isEuropean" => in_array("AUSTRIA", $paisesEuropeos)],
            "AZERBAYAN" => ["value" => "A9143AAA2A", "isEuropean" => in_array("AZERBAYAN", $paisesEuropeos)],
            "BAHAMAS" => ["value" => "A9203AAAAA", "isEuropean" => in_array("BAHAMAS", $paisesEuropeos)],
            "BAHREIN" => ["value" => "A9405AAAAA", "isEuropean" => in_array("BAHREIN", $paisesEuropeos)],
            "BANGLADESH" => ["value" => "A9432AAAAA", "isEuropean" => in_array("BANGLADESH", $paisesEuropeos)],
            "BARBADOS" => ["value" => "A9205AAAAA", "isEuropean" => in_array("BARBADOS", $paisesEuropeos)],
            "BELGICA" => ["value" => "A9105AAAAA", "isEuropean" => in_array("BELGICA", $paisesEuropeos)],
            "BELICE" => ["value" => "A9207AAAAA", "isEuropean" => in_array("BELICE", $paisesEuropeos)],
            "BHUTAN" => ["value" => "A9407AAAAA", "isEuropean" => in_array("BHUTAN", $paisesEuropeos)],
            "BIELORRUSIA" => ["value" => "A9144AAAAA", "isEuropean" => in_array("BIELORRUSIA", $paisesEuropeos)],
            "BOLIVIA" => ["value" => "A9204AAAAA", "isEuropean" => in_array("BOLIVIA", $paisesEuropeos)],
            "BOSNIA HERZEGOVINA" => ["value" => "A9156AAAAA", "isEuropean" => in_array("BOSNIA HERZEGOVINA", $paisesEuropeos)],
            "BOTSWANA" => ["value" => "A9305AAAAA", "isEuropean" => in_array("BOTSWANA", $paisesEuropeos)],
            "BRASIL" => ["value" => "A9206AAAAA", "isEuropean" => in_array("BRASIL", $paisesEuropeos)],
            "BRUNEI" => ["value" => "A9409AAAAA", "isEuropean" => in_array("BRUNEI", $paisesEuropeos)],
            "BULGARIA" => ["value" => "A9134AAAAA", "isEuropean" => in_array("BULGARIA", $paisesEuropeos)],
            "BURKINA FASO" => ["value" => "A9303AAAAA", "isEuropean" => in_array("BURKINA FASO", $paisesEuropeos)],
            "BURUNDI" => ["value" => "A9302AAAAA", "isEuropean" => in_array("BURUNDI", $paisesEuropeos)],
            "BUTAN" => ["value" => "A9442AAAAA", "isEuropean" => in_array("BUTAN", $paisesEuropeos)],
            "CABO VERDE" => ["value" => "A9308AAAAA", "isEuropean" => in_array("CABO VERDE", $paisesEuropeos)],
            "CAMERUN" => ["value" => "A9307AAAAA", "isEuropean" => in_array("CAMERUN", $paisesEuropeos)],
            "CANADA" => ["value" => "A9259AAAAA", "isEuropean" => in_array("CANADA", $paisesEuropeos)],
            "CATAR" => ["value" => "A9408AAAAA", "isEuropean" => in_array("CATAR", $paisesEuropeos)],
            "CENTROAMERICA" => ["value" => "A9201AAAAA", "isEuropean" => in_array("CENTROAMERICA", $paisesEuropeos)],
            "CHAD" => ["value" => "A9306AAAAA", "isEuropean" => in_array("CHAD", $paisesEuropeos)],
            "CHECOSLOVAQUIA" => ["value" => "A9114AAAAA", "isEuropean" => in_array("CHECOSLOVAQUIA", $paisesEuropeos)],
            "CHILE" => ["value" => "A9212AAAAA", "isEuropean" => in_array("CHILE", $paisesEuropeos)],
            "CHINA" => ["value" => "A9433AAAAA", "isEuropean" => in_array("CHINA", $paisesEuropeos)],
            "CHIPRE" => ["value" => "A9135AAAAA", "isEuropean" => in_array("CHIPRE", $paisesEuropeos)],
            "COLOMBIA" => ["value" => "A9213AAAAA", "isEuropean" => in_array("COLOMBIA", $paisesEuropeos)],
            "COMORAS" => ["value" => "A9309AAAAA", "isEuropean" => in_array("COMORAS", $paisesEuropeos)],
            "CONGO" => ["value" => "A9311AAAAA", "isEuropean" => in_array("CONGO", $paisesEuropeos)],
            "COREA" => ["value" => "A9450AAAAA", "isEuropean" => in_array("COREA", $paisesEuropeos)],
            "COREA NORTE" => ["value" => "A9451AAAAA", "isEuropean" => in_array("COREA NORTE", $paisesEuropeos)],
            "COREA SUR" => ["value" => "A9452AAAAA", "isEuropean" => in_array("COREA SUR", $paisesEuropeos)],
            "COSTA DE MARFIL" => ["value" => "A9310AAAAA", "isEuropean" => in_array("COSTA DE MARFIL", $paisesEuropeos)],
            "COSTA RICA" => ["value" => "A9214AAAAA", "isEuropean" => in_array("COSTA RICA", $paisesEuropeos)],
            "CROACIA" => ["value" => "A9136AAAAA", "isEuropean" => in_array("CROACIA", $paisesEuropeos)],
            "CUBA" => ["value" => "A9250AAAAA", "isEuropean" => in_array("CUBA", $paisesEuropeos)],
            "DINAMARCA" => ["value" => "A9106AAAAA", "isEuropean" => in_array("DINAMARCA", $paisesEuropeos)],
            "DJIBOUTI" => ["value" => "A9312AAAAA", "isEuropean" => in_array("DJIBOUTI", $paisesEuropeos)],
            "DOMINICA" => ["value" => "A9260AAAAA", "isEuropean" => in_array("DOMINICA", $paisesEuropeos)],
            "ECUADOR" => ["value" => "A9215AAAAA", "isEuropean" => in_array("ECUADOR", $paisesEuropeos)],
            "EGIPTO" => ["value" => "A9313AAAAA", "isEuropean" => in_array("EGIPTO", $paisesEuropeos)],
            "EL SALVADOR" => ["value" => "A9216AAAAA", "isEuropean" => in_array("EL SALVADOR", $paisesEuropeos)],
            "EMIRATOS ARABES UNIDOS" => ["value" => "A9411AAAAA", "isEuropean" => in_array("EMIRATOS ARABES UNIDOS", $paisesEuropeos)],
            "ERITREA" => ["value" => "A9314AAAAA", "isEuropean" => in_array("ERITREA", $paisesEuropeos)],
            "ESLOVAQUIA" => ["value" => "A9137AAAAA", "isEuropean" => in_array("ESLOVAQUIA", $paisesEuropeos)],
            "ESLOVENIA" => ["value" => "A9138AAAAA", "isEuropean" => in_array("ESLOVENIA", $paisesEuropeos)],
            "ESPAÑA" => ["value" => "A9107AAAAA", "isEuropean" => in_array("ESPAÑA", $paisesEuropeos)],
            "ESTADOS UNIDOS" => ["value" => "A9261AAAAA", "isEuropean" => in_array("ESTADOS UNIDOS", $paisesEuropeos)],
            "ESTONIA" => ["value" => "A9139AAAAA", "isEuropean" => in_array("ESTONIA", $paisesEuropeos)],
            "ETIOPIA" => ["value" => "A9315AAAAA", "isEuropean" => in_array("ETIOPIA", $paisesEuropeos)],
            "EUROPA" => ["value" => "A9398AAAAA", "isEuropean" => in_array("EUROPA", $paisesEuropeos)],
            "FIJI" => ["value" => "A9503AAAAA", "isEuropean" => in_array("FIJI", $paisesEuropeos)],
            "FILIPINAS" => ["value" => "A9444AAAAA", "isEuropean" => in_array("FILIPINAS", $paisesEuropeos)],
            "FINLANDIA" => ["value" => "A9108AAAAA", "isEuropean" => in_array("FINLANDIA", $paisesEuropeos)],
            "FRANCIA" => ["value" => "A9109AAAAA", "isEuropean" => in_array("FRANCIA", $paisesEuropeos)],
            "GABON" => ["value" => "A9316AAAAA", "isEuropean" => in_array("GABON", $paisesEuropeos)],
            "GAMBIA" => ["value" => "A9323AAAAA", "isEuropean" => in_array("GAMBIA", $paisesEuropeos)],
            "GEORGIA" => ["value" => "A9145AAAAA", "isEuropean" => in_array("GEORGIA", $paisesEuropeos)],
            "GHANA" => ["value" => "A9322AAAAA", "isEuropean" => in_array("GHANA", $paisesEuropeos)],
            "GRECIA" => ["value" => "A9113AAAAA", "isEuropean" => in_array("GRECIA", $paisesEuropeos)],
            "GUATEMALA" => ["value" => "A9228AAAAA", "isEuropean" => in_array("GUATEMALA", $paisesEuropeos)],
            "GUINEA" => ["value" => "A9325AAA3A", "isEuropean" => in_array("GUINEA", $paisesEuropeos)],
            "GUINEA BISSAU" => ["value" => "A9328AAA1A", "isEuropean" => in_array("GUINEA BISSAU", $paisesEuropeos)],
            "GUINEA ECUATORIAL" => ["value" => "A9324AAAAA", "isEuropean" => in_array("GUINEA ECUATORIAL", $paisesEuropeos)],
            "GUYANA" => ["value" => "A9225AAAAA", "isEuropean" => in_array("GUYANA", $paisesEuropeos)],
            "HAITI" => ["value" => "A9230AAAAA", "isEuropean" => in_array("HAITI", $paisesEuropeos)],
            "HONDURAS" => ["value" => "A9232AAAAA", "isEuropean" => in_array("HONDURAS", $paisesEuropeos)],
            "HONG KONG CHINO" => ["value" => "A9462AAAAA", "isEuropean" => in_array("HONG KONG CHINO", $paisesEuropeos)],
            "HUNGRIA" => ["value" => "A9114AAAAA", "isEuropean" => in_array("HUNGRIA", $paisesEuropeos)],
            "IFNI" => ["value" => "A9395AAAAA", "isEuropean" => in_array("IFNI", $paisesEuropeos)],
            "INDIA" => ["value" => "A9412AAAAA", "isEuropean" => in_array("INDIA", $paisesEuropeos)],
            "INDONESIA" => ["value" => "A9414AAAAA", "isEuropean" => in_array("INDONESIA", $paisesEuropeos)],
            "IRAK" => ["value" => "A9413AAAAA", "isEuropean" => in_array("IRAK", $paisesEuropeos)],
            "IRAN" => ["value" => "A9415AAAAA", "isEuropean" => in_array("IRAN", $paisesEuropeos)],
            "IRLANDA" => ["value" => "A9115AAAAA", "isEuropean" => in_array("IRLANDA", $paisesEuropeos)],
            "ISLANDIA" => ["value" => "A9116AAAAA", "isEuropean" => in_array("ISLANDIA", $paisesEuropeos)],
            "ISLAS MARIANAS NORTE" => ["value" => "A9518AAAAA", "isEuropean" => in_array("ISLAS MARIANAS NORTE", $paisesEuropeos)],
            "ISLAS MARSHALL" => ["value" => "A9520AAAAA", "isEuropean" => in_array("ISLAS MARSHALL", $paisesEuropeos)],
            "ISLAS SALOMON" => ["value" => "A9551AAA1A", "isEuropean" => in_array("ISLAS SALOMON", $paisesEuropeos)],
            "ISRAEL" => ["value" => "A9417AAAAA", "isEuropean" => in_array("ISRAEL", $paisesEuropeos)],
            "ITALIA" => ["value" => "A9117AAAAA", "isEuropean" => in_array("ITALIA", $paisesEuropeos)],
            "JAMAICA" => ["value" => "A9233AAAAA", "isEuropean" => in_array("JAMAICA", $paisesEuropeos)],
            "JAPON" => ["value" => "A9416AAAAA", "isEuropean" => in_array("JAPON", $paisesEuropeos)],
            "JORDANIA" => ["value" => "A9419AAAAA", "isEuropean" => in_array("JORDANIA", $paisesEuropeos)],
            "KAZAJSTAN" => ["value" => "A9465AAAAA", "isEuropean" => in_array("KAZAJSTAN", $paisesEuropeos)],
            "KENIA" => ["value" => "A9336AAAAA", "isEuropean" => in_array("KENIA", $paisesEuropeos)],
            "KIRIBATI" => ["value" => "A9501AAAAA", "isEuropean" => in_array("KIRIBATI", $paisesEuropeos)],
            "KUWAIT" => ["value" => "A9421AAAAA", "isEuropean" => in_array("KUWAIT", $paisesEuropeos)],
            "LAOS" => ["value" => "A9418AAAAA", "isEuropean" => in_array("LAOS", $paisesEuropeos)],
            "LESOTHO" => ["value" => "A9337AAAAA", "isEuropean" => in_array("LESOTHO", $paisesEuropeos)],
            "LETONIA" => ["value" => "A9138AAAAA", "isEuropean" => in_array("LETONIA", $paisesEuropeos)],
            "LIBANO" => ["value" => "A9423AAAAA", "isEuropean" => in_array("LIBANO", $paisesEuropeos)],
            "LIBERIA" => ["value" => "A9342AAAAA", "isEuropean" => in_array("LIBERIA", $paisesEuropeos)],
            "LIBIA" => ["value" => "A9344AAAAA", "isEuropean" => in_array("LIBIA", $paisesEuropeos)],
            "LIECHTENSTEIN" => ["value" => "A9118AAAAA", "isEuropean" => in_array("LIECHTENSTEIN", $paisesEuropeos)],
            "LITUANIA" => ["value" => "A9139AAAAA", "isEuropean" => in_array("LITUANIA", $paisesEuropeos)],
            "LUXEMBURGO" => ["value" => "A9119AAAAA", "isEuropean" => in_array("LUXEMBURGO", $paisesEuropeos)],
            "MACAO" => ["value" => "A9463AAAAA", "isEuropean" => in_array("MACAO", $paisesEuropeos)],
            "MACEDONIA" => ["value" => "A9159AAAAA", "isEuropean" => in_array("MACEDONIA", $paisesEuropeos)],
            "MADAGASCAR" => ["value" => "A9354AAAAA", "isEuropean" => in_array("MADAGASCAR", $paisesEuropeos)],
            "MALASIA" => ["value" => "A9425AAAAA", "isEuropean" => in_array("MALASIA", $paisesEuropeos)],
            "MALAWI" => ["value" => "A9346AAAAA", "isEuropean" => in_array("MALAWI", $paisesEuropeos)],
            "MALDIVAS" => ["value" => "A9436AAAAA", "isEuropean" => in_array("MALDIVAS", $paisesEuropeos)],
            "MALI" => ["value" => "A9347AAAAA", "isEuropean" => in_array("MALI", $paisesEuropeos)],
            "MALTA" => ["value" => "A9120AAAAA", "isEuropean" => in_array("MALTA", $paisesEuropeos)],
            "MARRUECOS" => ["value" => "A9348AAAAA", "isEuropean" => in_array("MARRUECOS", $paisesEuropeos)],
            "MAURICIO" => ["value" => "A9349AAAAA", "isEuropean" => in_array("MAURICIO", $paisesEuropeos)],
            "MAURITANIA" => ["value" => "A9350AAAAA", "isEuropean" => in_array("MAURITANIA", $paisesEuropeos)],
            "MEXICO" => ["value" => "A9234AAA1A", "isEuropean" => in_array("MEXICO", $paisesEuropeos)],
            "MOLDAVIA" => ["value" => "A9148AAAAA", "isEuropean" => in_array("MOLDAVIA", $paisesEuropeos)],
            "MONACO" => ["value" => "A9121AAAAA", "isEuropean" => in_array("MONACO", $paisesEuropeos)],
            "MONGOLIA" => ["value" => "A9427AAAAA", "isEuropean" => in_array("MONGOLIA", $paisesEuropeos)],
            "MONTENEGRO" => ["value" => "A9160AAAAA", "isEuropean" => in_array("MONTENEGRO", $paisesEuropeos)],
            "MOZAMBIQUE" => ["value" => "A9351AAAAA", "isEuropean" => in_array("MOZAMBIQUE", $paisesEuropeos)],
            "MYANMAR" => ["value" => "A9400AAAAA", "isEuropean" => in_array("MYANMAR", $paisesEuropeos)],
            "NAMIBIA" => ["value" => "A9353AAAAA", "isEuropean" => in_array("NAMIBIA", $paisesEuropeos)],
            "NAURU" => ["value" => "A9541AAAAA", "isEuropean" => in_array("NAURU", $paisesEuropeos)],
            "NEPAL" => ["value" => "A9541AAAAA", "isEuropean" => in_array("NEPAL", $paisesEuropeos)],
            "NICARAGUA" => ["value" => "A9236AAAAA", "isEuropean" => in_array("NICARAGUA", $paisesEuropeos)],
            "NIGER" => ["value" => "A9360AAAAA", "isEuropean" => in_array("NIGER", $paisesEuropeos)],
            "NIGERIA" => ["value" => "A9352AAAAA", "isEuropean" => in_array("NIGERIA", $paisesEuropeos)],
            "NORUEGA" => ["value" => "A9122AAAAA", "isEuropean" => in_array("NORUEGA", $paisesEuropeos)],
            "NUEVA ZELANDA" => ["value" => "A9540AAAAA", "isEuropean" => in_array("NUEVA ZELANDA", $paisesEuropeos)],
            "OCEANIA" => ["value" => "A9599AAAAA", "isEuropean" => in_array("OCEANIA", $paisesEuropeos)],
            "OMAN" => ["value" => "A9444AAAAA", "isEuropean" => in_array("OMAN", $paisesEuropeos)],
            "PAISES BAJOS" => ["value" => "A9123AAA1A", "isEuropean" => in_array("PAISES BAJOS", $paisesEuropeos)],
            "PAKISTAN" => ["value" => "A9424AAA1A", "isEuropean" => in_array("PAKISTAN", $paisesEuropeos)],
            "PALESTINA" => ["value" => "A9440AAAAA", "isEuropean" => in_array("PALESTINA", $paisesEuropeos)],
            "PANAMA" => ["value" => "A9238AAAAA", "isEuropean" => in_array("PANAMA", $paisesEuropeos)],
            "PAPUA NUEVA GUINEA" => ["value" => "A9542AAAAA", "isEuropean" => in_array("PAPUA NUEVA GUINEA", $paisesEuropeos)],
            "PARAGUAY" => ["value" => "A9240AAAAA", "isEuropean" => in_array("PARAGUAY", $paisesEuropeos)],
            "PERU" => ["value" => "A9242AAAAA", "isEuropean" => in_array("PERU", $paisesEuropeos)],
            "POLONIA" => ["value" => "A9124AAAAA", "isEuropean" => in_array("POLONIA", $paisesEuropeos)],
            "PORTUGAL" => ["value" => "A9125AAAAA", "isEuropean" => in_array("PORTUGAL", $paisesEuropeos)],
            "PUERTO RICO" => ["value" => "A9244AAAAA", "isEuropean" => in_array("PUERTO RICO", $paisesEuropeos)],
            "QATAR" => ["value" => "A9431AAAAA", "isEuropean" => in_array("QATAR", $paisesEuropeos)],
            "REINO UNIDO" => ["value" => "A9112AAA1A", "isEuropean" => in_array("REINO UNIDO", $paisesEuropeos)],
            "REPUBLICA BENIN" => ["value" => "A9302AAA1A", "isEuropean" => in_array("REPUBLICA BENIN", $paisesEuropeos)],
            "REPUBLICA CENTROAFRICANA" => ["value" => "A9310AAA1A", "isEuropean" => in_array("REPUBLICA CENTROAFRICANA", $paisesEuropeos)],
            "REPUBLICA CHECA" => ["value" => "A9157AAAAA", "isEuropean" => in_array("REPUBLICA CHECA", $paisesEuropeos)],
            "REPUBLICA CONGO" => ["value" => "A9312AAA1A", "isEuropean" => in_array("REPUBLICA CONGO", $paisesEuropeos)],
            "REPUBLICA DEMOCRATICA CONGO" => ["value" => "A9380AAAAA", "isEuropean" => in_array("REPUBLICA DEMOCRATICA CONGO", $paisesEuropeos)],
            "REPUBLICA DOMINICANA" => ["value" => "A9218AAA1A", "isEuropean" => in_array("REPUBLICA DOMINICANA", $paisesEuropeos)],
            "REPUBLICA GRANADA" => ["value" => "A9229AAAAA", "isEuropean" => in_array("REPUBLICA GRANADA", $paisesEuropeos)],
            "REPUBLICA KIRGUISTAN" => ["value" => "A9466AAA1A", "isEuropean" => in_array("REPUBLICA KIRGUISTAN", $paisesEuropeos)],
            "REPUBLICA SUDAN SUR" => ["value" => "A9369AAAAA", "isEuropean" => in_array("REPUBLICA SUDAN SUR", $paisesEuropeos)],
            "RIO MUNI" => ["value" => "A9397AAAAA", "isEuropean" => in_array("RIO MUNI", $paisesEuropeos)],
            "RUANDA" => ["value" => "A9306AAAAA", "isEuropean" => in_array("RUANDA", $paisesEuropeos)],
            "RUMANIA" => ["value" => "A9127AAAAA", "isEuropean" => in_array("RUMANIA", $paisesEuropeos)],
            "RUSIA" => ["value" => "A9149AAAAA", "isEuropean" => in_array("RUSIA", $paisesEuropeos)],
            "SAHARA" => ["value" => "A9398AAAAA", "isEuropean" => in_array("SAHARA", $paisesEuropeos)],
            "SAINT KITTS NEVIS" => ["value" => "A9256AAA1A", "isEuropean" => in_array("SAINT KITTS NEVIS", $paisesEuropeos)],
            "SALVADOR" => ["value" => "A9220AAAAA", "isEuropean" => in_array("SALVADOR", $paisesEuropeos)],
            "SAMOA OCCIDENTAL" => ["value" => "A9552AAAAA", "isEuropean" => in_array("SAMOA OCCIDENTAL", $paisesEuropeos)],
            "SAN MARINO" => ["value" => "A9135AAAAA", "isEuropean" => in_array("SAN MARINO", $paisesEuropeos)],
            "SAN MARTIN" => ["value" => "A9259AAAAA", "isEuropean" => in_array("SAN MARTIN", $paisesEuropeos)],
            "SAN VICENTE GRANADINAS" => ["value" => "A9254AAA1A", "isEuropean" => in_array("SAN VICENTE GRANADINAS", $paisesEuropeos)],
            "SANTA LUCIA" => ["value" => "A9253AAAAA", "isEuropean" => in_array("SANTA LUCIA", $paisesEuropeos)],
            "SANTA SEDE" => ["value" => "A9136AAA2A", "isEuropean" => in_array("SANTA SEDE", $paisesEuropeos)],
            "SANTO TOME PRINCIPE" => ["value" => "A9361AAAAA", "isEuropean" => in_array("SANTO TOME PRINCIPE", $paisesEuropeos)],
            "SENEGAL" => ["value" => "A9362AAAAA", "isEuropean" => in_array("SENEGAL", $paisesEuropeos)],
            "SERBIA" => ["value" => "A9155AAAAA", "isEuropean" => in_array("SERBIA", $paisesEuropeos)],
            "SEYCHELLES" => ["value" => "A9363AAAAA", "isEuropean" => in_array("SEYCHELLES", $paisesEuropeos)],
            "SIERRA LEONA" => ["value" => "A9364AAAAA", "isEuropean" => in_array("SIERRA LEONA", $paisesEuropeos)],
            "SINGAPUR" => ["value" => "A9426AAAAA", "isEuropean" => in_array("SINGAPUR", $paisesEuropeos)],
            "SIRIA" => ["value" => "A9433AAAAA", "isEuropean" => in_array("SIRIA", $paisesEuropeos)],
            "SOMALIA" => ["value" => "A9365AAAAA", "isEuropean" => in_array("SOMALIA", $paisesEuropeos)],
            "SRI LANKA" => ["value" => "A9404AAAAA", "isEuropean" => in_array("SRI LANKA", $paisesEuropeos)],
            "SUDAFRICA" => ["value" => "A9367AAAAA", "isEuropean" => in_array("SUDAFRICA", $paisesEuropeos)],
            "SUDAN" => ["value" => "A9368AAAAA", "isEuropean" => in_array("SUDAN", $paisesEuropeos)],
            "SUDAN SUR" => ["value" => "A9369AAA1A", "isEuropean" => in_array("SUDAN SUR", $paisesEuropeos)],
            "SUECIA" => ["value" => "A9128AAAAA", "isEuropean" => in_array("SUECIA", $paisesEuropeos)],
            "SUIZA" => ["value" => "A9129AAAAA", "isEuropean" => in_array("SUIZA", $paisesEuropeos)],
            "SURINAM" => ["value" => "A9250AAAAA", "isEuropean" => in_array("SURINAM", $paisesEuropeos)],
            "SWAZILANDIA" => ["value" => "A9371AAAAA", "isEuropean" => in_array("SWAZILANDIA", $paisesEuropeos)],
            "TADJIKISTAN" => ["value" => "A9469AAAAA", "isEuropean" => in_array("TADJIKISTAN", $paisesEuropeos)],
            "TAILANDIA" => ["value" => "A9428AAA1A", "isEuropean" => in_array("TAILANDIA", $paisesEuropeos)],
            "TAIWAN TAIPEI" => ["value" => "A9408AAA3A", "isEuropean" => in_array("TAIWAN TAIPEI", $paisesEuropeos)],
            "TANZANIA" => ["value" => "A9370AAAAA", "isEuropean" => in_array("TANZANIA", $paisesEuropeos)],
            "TIMOR ORIENTAL" => ["value" => "A9464AAAAA", "isEuropean" => in_array("TIMOR ORIENTAL", $paisesEuropeos)],
            "TOGO" => ["value" => "A9374AAAAA", "isEuropean" => in_array("TOGO", $paisesEuropeos)],
            "TONGA" => ["value" => "A9554AAAAA", "isEuropean" => in_array("TONGA", $paisesEuropeos)],
            "TRINIDAD TOBAGO" => ["value" => "A9245AAAAA", "isEuropean" => in_array("TRINIDAD TOBAGO", $paisesEuropeos)],
            "TUNEZ" => ["value" => "A9378AAAAA", "isEuropean" => in_array("TUNEZ", $paisesEuropeos)],
            "TURKMENISTAN" => ["value" => "A9467AAA1A", "isEuropean" => in_array("TURKMENISTAN", $paisesEuropeos)],
            "TURQUIA" => ["value" => "A9130AAAAA", "isEuropean" => in_array("TURQUIA", $paisesEuropeos)],
            "TUVALU" => ["value" => "A9560AAAAA", "isEuropean" => in_array("TUVALU", $paisesEuropeos)],
            "UCRANIA" => ["value" => "A9152AAAAA", "isEuropean" => in_array("UCRANIA", $paisesEuropeos)],
            "UGANDA" => ["value" => "A9358AAAAA", "isEuropean" => in_array("UGANDA", $paisesEuropeos)],
            "UNION EUROPEA" => ["value" => "A9190AAA1A", "isEuropean" => in_array("UNION EUROPEA", $paisesEuropeos)],
            "URUGUAY" => ["value" => "A9246AAAAA", "isEuropean" => in_array("URUGUAY", $paisesEuropeos)],
            "UZBEKISTAN" => ["value" => "A9468AAAAA", "isEuropean" => in_array("UZBEKISTAN", $paisesEuropeos)],
            "VANUATU" => ["value" => "A9565AAAAA", "isEuropean" => in_array("VANUATU", $paisesEuropeos)],
            "VENEZUELA" => ["value" => "A9248AAAAA", "isEuropean" => in_array("VENEZUELA", $paisesEuropeos)],
            "VIETNAM" => ["value" => "A9430AAAAA", "isEuropean" => in_array("VIETNAM", $paisesEuropeos)],
            "YEMEN" => ["value" => "A9434AAAAA", "isEuropean" => in_array("YEMEN", $paisesEuropeos)],
            "ZAMBIA" => ["value" => "A9382AAAAA", "isEuropean" => in_array("ZAMBIA", $paisesEuropeos)],
            "ZIMBABWE" => ["value" => "A9357AAAAA", "isEuropean" => in_array("ZIMBABWE", $paisesEuropeos)]
        ];

        $optiones = [
            [
                "codigo" => "P",
                "descripcion" => "PASAPORTE",
            ],
            [
                "codigo" => "I",
                "descripcion" => "CARTA DE IDENTIDAD EXTRANJERA",
            ],
            [
                "codigo" => "N",
                "descripcion" => "NIE O TARJETA ESPAÑOLA DE EXTRANJEROS",
            ],
            [
                "codigo" => "X",
                "descripcion" => "PERMISO DE RESIDENCIA DE ESTADO MIEMBRO DE LA UE",
            ],
            [
                "codigo" => "C",
                "descripcion" => "PERMISO CONDUCIR ESPAÑOL",
            ],
            [
                "codigo" => "D",
                "descripcion" => "DNI",
            ]
        ];
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

    public function obtenerStringDNI($tipo){
        switch ($tipo) {
            case 'D':
                return "DNI";
                break;
            case 'C':
                return "PERMISO CONDUCIR ESPAÑOL";
                break;
            case 'X':
                return "PERMISO DE RESIDENCIA DE ESTADO MIEMBRO DE LA UE";
                break;
            case 'N':
                return "NIE O TARJETA ESPAÑOLA DE EXTRANJEROS";
                break;
            case 'I':
                return "CARTA DE IDENTIDAD EXTRANJERA";
                break;
            case 'P':
                return "PASAPORTE";
                break;
            
            default:
                # code...
                break;
        }
    }

    public function obtenerNacionalidad($tipo){
        $paisesEuropeos = [
            "ALBANIA", "ALEMANIA", "AUSTRIA", "BELGICA", "BULGARIA",
            "CHIPRE", "CROACIA", "DINAMARCA", "ESLOVAQUIA", "ESLOVENIA", "ESPAÑA",
            "ESTONIA", "FINLANDIA", "FRANCIA", "GRECIA", "HUNGRIA", "IRLANDA",
            "ISLANDIA", "ITALIA", "LETONIA", "LITUANIA", "LUXEMBURGO",
            "MALTA", "NORUEGA", "PAISES BAJOS", "POLONIA",
            "PORTUGAL", "REINO UNIDO", "REPUBLICA CHECA", "RUMANIA",
            "SUECIA"
        ];
        $paisesDni = [
            "AFGANISTAN" => ["value" => "A9401AAAAA", "isEuropean" => in_array("AFGANISTAN", $paisesEuropeos)],
            "AFRICA" => ["value" => "A9399AAAAA", "isEuropean" => in_array("AFRICA", $paisesEuropeos)],
            "ALBANIA" => ["value" => "A9102AAAAA", "isEuropean" => in_array("ALBANIA", $paisesEuropeos)],
            "ALEMANIA" => ["value" => "A9103AAAAA", "isEuropean" => in_array("ALEMANIA", $paisesEuropeos)],
            "AMERICA" => ["value" => "A9299AAAAA", "isEuropean" => in_array("AMERICA", $paisesEuropeos)],
            "ANDORRA" => ["value" => "A9133AAAAA", "isEuropean" => in_array("ANDORRA", $paisesEuropeos)],
            "ANGOLA" => ["value" => "A9301AAAAA", "isEuropean" => in_array("ANGOLA", $paisesEuropeos)],
            "ANTIGUA BARBUDA" => ["value" => "A9255AAAAA", "isEuropean" => in_array("ANTIGUA BARBUDA", $paisesEuropeos)],
            "ANTILLAS NEERLANDESAS" => ["value" => "A9200AAAAA", "isEuropean" => in_array("ANTILLAS NEERLANDESAS", $paisesEuropeos)],
            "APATRIDA" => ["value" => "A9600AAAAA", "isEuropean" => in_array("APATRIDA", $paisesEuropeos)],
            "ARABIA SAUDITA" => ["value" => "A9403AAA1A", "isEuropean" => in_array("ARABIA SAUDITA", $paisesEuropeos)],
            "ARGELIA" => ["value" => "A9304AAAAA", "isEuropean" => in_array("ARGELIA", $paisesEuropeos)],
            "ARGENTINA" => ["value" => "A9202AAAAA", "isEuropean" => in_array("ARGENTINA", $paisesEuropeos)],
            "ARMENIA" => ["value" => "A9142AAAAA", "isEuropean" => in_array("ARMENIA", $paisesEuropeos)],
            "ARUBA" => ["value" => "A9257AAAAA", "isEuropean" => in_array("ARUBA", $paisesEuropeos)],
            "ASIA" => ["value" => "A9499AAAAA", "isEuropean" => in_array("ASIA", $paisesEuropeos)],
            "AUSTRALIA" => ["value" => "A9500AAAAA", "isEuropean" => in_array("AUSTRALIA", $paisesEuropeos)],
            "AUSTRIA" => ["value" => "A9104AAAAA", "isEuropean" => in_array("AUSTRIA", $paisesEuropeos)],
            "AZERBAYAN" => ["value" => "A9143AAA2A", "isEuropean" => in_array("AZERBAYAN", $paisesEuropeos)],
            "BAHAMAS" => ["value" => "A9203AAAAA", "isEuropean" => in_array("BAHAMAS", $paisesEuropeos)],
            "BAHREIN" => ["value" => "A9405AAAAA", "isEuropean" => in_array("BAHREIN", $paisesEuropeos)],
            "BANGLADESH" => ["value" => "A9432AAAAA", "isEuropean" => in_array("BANGLADESH", $paisesEuropeos)],
            "BARBADOS" => ["value" => "A9205AAAAA", "isEuropean" => in_array("BARBADOS", $paisesEuropeos)],
            "BELGICA" => ["value" => "A9105AAAAA", "isEuropean" => in_array("BELGICA", $paisesEuropeos)],
            "BELICE" => ["value" => "A9207AAAAA", "isEuropean" => in_array("BELICE", $paisesEuropeos)],
            "BHUTAN" => ["value" => "A9407AAAAA", "isEuropean" => in_array("BHUTAN", $paisesEuropeos)],
            "BIELORRUSIA" => ["value" => "A9144AAAAA", "isEuropean" => in_array("BIELORRUSIA", $paisesEuropeos)],
            "BOLIVIA" => ["value" => "A9204AAAAA", "isEuropean" => in_array("BOLIVIA", $paisesEuropeos)],
            "BOSNIA HERZEGOVINA" => ["value" => "A9156AAAAA", "isEuropean" => in_array("BOSNIA HERZEGOVINA", $paisesEuropeos)],
            "BOTSWANA" => ["value" => "A9305AAAAA", "isEuropean" => in_array("BOTSWANA", $paisesEuropeos)],
            "BRASIL" => ["value" => "A9206AAAAA", "isEuropean" => in_array("BRASIL", $paisesEuropeos)],
            "BRUNEI" => ["value" => "A9409AAAAA", "isEuropean" => in_array("BRUNEI", $paisesEuropeos)],
            "BULGARIA" => ["value" => "A9134AAAAA", "isEuropean" => in_array("BULGARIA", $paisesEuropeos)],
            "BURKINA FASO" => ["value" => "A9303AAAAA", "isEuropean" => in_array("BURKINA FASO", $paisesEuropeos)],
            "BURUNDI" => ["value" => "A9302AAAAA", "isEuropean" => in_array("BURUNDI", $paisesEuropeos)],
            "BUTAN" => ["value" => "A9442AAAAA", "isEuropean" => in_array("BUTAN", $paisesEuropeos)],
            "CABO VERDE" => ["value" => "A9308AAAAA", "isEuropean" => in_array("CABO VERDE", $paisesEuropeos)],
            "CAMERUN" => ["value" => "A9307AAAAA", "isEuropean" => in_array("CAMERUN", $paisesEuropeos)],
            "CANADA" => ["value" => "A9259AAAAA", "isEuropean" => in_array("CANADA", $paisesEuropeos)],
            "CATAR" => ["value" => "A9408AAAAA", "isEuropean" => in_array("CATAR", $paisesEuropeos)],
            "CENTROAMERICA" => ["value" => "A9201AAAAA", "isEuropean" => in_array("CENTROAMERICA", $paisesEuropeos)],
            "CHAD" => ["value" => "A9306AAAAA", "isEuropean" => in_array("CHAD", $paisesEuropeos)],
            "CHECOSLOVAQUIA" => ["value" => "A9114AAAAA", "isEuropean" => in_array("CHECOSLOVAQUIA", $paisesEuropeos)],
            "CHILE" => ["value" => "A9212AAAAA", "isEuropean" => in_array("CHILE", $paisesEuropeos)],
            "CHINA" => ["value" => "A9433AAAAA", "isEuropean" => in_array("CHINA", $paisesEuropeos)],
            "CHIPRE" => ["value" => "A9135AAAAA", "isEuropean" => in_array("CHIPRE", $paisesEuropeos)],
            "COLOMBIA" => ["value" => "A9213AAAAA", "isEuropean" => in_array("COLOMBIA", $paisesEuropeos)],
            "COMORAS" => ["value" => "A9309AAAAA", "isEuropean" => in_array("COMORAS", $paisesEuropeos)],
            "CONGO" => ["value" => "A9311AAAAA", "isEuropean" => in_array("CONGO", $paisesEuropeos)],
            "COREA" => ["value" => "A9450AAAAA", "isEuropean" => in_array("COREA", $paisesEuropeos)],
            "COREA NORTE" => ["value" => "A9451AAAAA", "isEuropean" => in_array("COREA NORTE", $paisesEuropeos)],
            "COREA SUR" => ["value" => "A9452AAAAA", "isEuropean" => in_array("COREA SUR", $paisesEuropeos)],
            "COSTA DE MARFIL" => ["value" => "A9310AAAAA", "isEuropean" => in_array("COSTA DE MARFIL", $paisesEuropeos)],
            "COSTA RICA" => ["value" => "A9214AAAAA", "isEuropean" => in_array("COSTA RICA", $paisesEuropeos)],
            "CROACIA" => ["value" => "A9136AAAAA", "isEuropean" => in_array("CROACIA", $paisesEuropeos)],
            "CUBA" => ["value" => "A9250AAAAA", "isEuropean" => in_array("CUBA", $paisesEuropeos)],
            "DINAMARCA" => ["value" => "A9106AAAAA", "isEuropean" => in_array("DINAMARCA", $paisesEuropeos)],
            "DJIBOUTI" => ["value" => "A9312AAAAA", "isEuropean" => in_array("DJIBOUTI", $paisesEuropeos)],
            "DOMINICA" => ["value" => "A9260AAAAA", "isEuropean" => in_array("DOMINICA", $paisesEuropeos)],
            "ECUADOR" => ["value" => "A9215AAAAA", "isEuropean" => in_array("ECUADOR", $paisesEuropeos)],
            "EGIPTO" => ["value" => "A9313AAAAA", "isEuropean" => in_array("EGIPTO", $paisesEuropeos)],
            "EL SALVADOR" => ["value" => "A9216AAAAA", "isEuropean" => in_array("EL SALVADOR", $paisesEuropeos)],
            "EMIRATOS ARABES UNIDOS" => ["value" => "A9411AAAAA", "isEuropean" => in_array("EMIRATOS ARABES UNIDOS", $paisesEuropeos)],
            "ERITREA" => ["value" => "A9314AAAAA", "isEuropean" => in_array("ERITREA", $paisesEuropeos)],
            "ESLOVAQUIA" => ["value" => "A9137AAAAA", "isEuropean" => in_array("ESLOVAQUIA", $paisesEuropeos)],
            "ESLOVENIA" => ["value" => "A9138AAAAA", "isEuropean" => in_array("ESLOVENIA", $paisesEuropeos)],
            "ESPAÑA" => ["value" => "A9107AAAAA", "isEuropean" => in_array("ESPAÑA", $paisesEuropeos)],
            "ESTADOS UNIDOS" => ["value" => "A9261AAAAA", "isEuropean" => in_array("ESTADOS UNIDOS", $paisesEuropeos)],
            "ESTONIA" => ["value" => "A9139AAAAA", "isEuropean" => in_array("ESTONIA", $paisesEuropeos)],
            "ETIOPIA" => ["value" => "A9315AAAAA", "isEuropean" => in_array("ETIOPIA", $paisesEuropeos)],
            "EUROPA" => ["value" => "A9398AAAAA", "isEuropean" => in_array("EUROPA", $paisesEuropeos)],
            "FIJI" => ["value" => "A9503AAAAA", "isEuropean" => in_array("FIJI", $paisesEuropeos)],
            "FILIPINAS" => ["value" => "A9444AAAAA", "isEuropean" => in_array("FILIPINAS", $paisesEuropeos)],
            "FINLANDIA" => ["value" => "A9108AAAAA", "isEuropean" => in_array("FINLANDIA", $paisesEuropeos)],
            "FRANCIA" => ["value" => "A9109AAAAA", "isEuropean" => in_array("FRANCIA", $paisesEuropeos)],
            "GABON" => ["value" => "A9316AAAAA", "isEuropean" => in_array("GABON", $paisesEuropeos)],
            "GAMBIA" => ["value" => "A9323AAAAA", "isEuropean" => in_array("GAMBIA", $paisesEuropeos)],
            "GEORGIA" => ["value" => "A9145AAAAA", "isEuropean" => in_array("GEORGIA", $paisesEuropeos)],
            "GHANA" => ["value" => "A9322AAAAA", "isEuropean" => in_array("GHANA", $paisesEuropeos)],
            "GRECIA" => ["value" => "A9113AAAAA", "isEuropean" => in_array("GRECIA", $paisesEuropeos)],
            "GUATEMALA" => ["value" => "A9228AAAAA", "isEuropean" => in_array("GUATEMALA", $paisesEuropeos)],
            "GUINEA" => ["value" => "A9325AAA3A", "isEuropean" => in_array("GUINEA", $paisesEuropeos)],
            "GUINEA BISSAU" => ["value" => "A9328AAA1A", "isEuropean" => in_array("GUINEA BISSAU", $paisesEuropeos)],
            "GUINEA ECUATORIAL" => ["value" => "A9324AAAAA", "isEuropean" => in_array("GUINEA ECUATORIAL", $paisesEuropeos)],
            "GUYANA" => ["value" => "A9225AAAAA", "isEuropean" => in_array("GUYANA", $paisesEuropeos)],
            "HAITI" => ["value" => "A9230AAAAA", "isEuropean" => in_array("HAITI", $paisesEuropeos)],
            "HONDURAS" => ["value" => "A9232AAAAA", "isEuropean" => in_array("HONDURAS", $paisesEuropeos)],
            "HONG KONG CHINO" => ["value" => "A9462AAAAA", "isEuropean" => in_array("HONG KONG CHINO", $paisesEuropeos)],
            "HUNGRIA" => ["value" => "A9114AAAAA", "isEuropean" => in_array("HUNGRIA", $paisesEuropeos)],
            "IFNI" => ["value" => "A9395AAAAA", "isEuropean" => in_array("IFNI", $paisesEuropeos)],
            "INDIA" => ["value" => "A9412AAAAA", "isEuropean" => in_array("INDIA", $paisesEuropeos)],
            "INDONESIA" => ["value" => "A9414AAAAA", "isEuropean" => in_array("INDONESIA", $paisesEuropeos)],
            "IRAK" => ["value" => "A9413AAAAA", "isEuropean" => in_array("IRAK", $paisesEuropeos)],
            "IRAN" => ["value" => "A9415AAAAA", "isEuropean" => in_array("IRAN", $paisesEuropeos)],
            "IRLANDA" => ["value" => "A9115AAAAA", "isEuropean" => in_array("IRLANDA", $paisesEuropeos)],
            "ISLANDIA" => ["value" => "A9116AAAAA", "isEuropean" => in_array("ISLANDIA", $paisesEuropeos)],
            "ISLAS MARIANAS NORTE" => ["value" => "A9518AAAAA", "isEuropean" => in_array("ISLAS MARIANAS NORTE", $paisesEuropeos)],
            "ISLAS MARSHALL" => ["value" => "A9520AAAAA", "isEuropean" => in_array("ISLAS MARSHALL", $paisesEuropeos)],
            "ISLAS SALOMON" => ["value" => "A9551AAA1A", "isEuropean" => in_array("ISLAS SALOMON", $paisesEuropeos)],
            "ISRAEL" => ["value" => "A9417AAAAA", "isEuropean" => in_array("ISRAEL", $paisesEuropeos)],
            "ITALIA" => ["value" => "A9117AAAAA", "isEuropean" => in_array("ITALIA", $paisesEuropeos)],
            "JAMAICA" => ["value" => "A9233AAAAA", "isEuropean" => in_array("JAMAICA", $paisesEuropeos)],
            "JAPON" => ["value" => "A9416AAAAA", "isEuropean" => in_array("JAPON", $paisesEuropeos)],
            "JORDANIA" => ["value" => "A9419AAAAA", "isEuropean" => in_array("JORDANIA", $paisesEuropeos)],
            "KAZAJSTAN" => ["value" => "A9465AAAAA", "isEuropean" => in_array("KAZAJSTAN", $paisesEuropeos)],
            "KENIA" => ["value" => "A9336AAAAA", "isEuropean" => in_array("KENIA", $paisesEuropeos)],
            "KIRIBATI" => ["value" => "A9501AAAAA", "isEuropean" => in_array("KIRIBATI", $paisesEuropeos)],
            "KUWAIT" => ["value" => "A9421AAAAA", "isEuropean" => in_array("KUWAIT", $paisesEuropeos)],
            "LAOS" => ["value" => "A9418AAAAA", "isEuropean" => in_array("LAOS", $paisesEuropeos)],
            "LESOTHO" => ["value" => "A9337AAAAA", "isEuropean" => in_array("LESOTHO", $paisesEuropeos)],
            "LETONIA" => ["value" => "A9138AAAAA", "isEuropean" => in_array("LETONIA", $paisesEuropeos)],
            "LIBANO" => ["value" => "A9423AAAAA", "isEuropean" => in_array("LIBANO", $paisesEuropeos)],
            "LIBERIA" => ["value" => "A9342AAAAA", "isEuropean" => in_array("LIBERIA", $paisesEuropeos)],
            "LIBIA" => ["value" => "A9344AAAAA", "isEuropean" => in_array("LIBIA", $paisesEuropeos)],
            "LIECHTENSTEIN" => ["value" => "A9118AAAAA", "isEuropean" => in_array("LIECHTENSTEIN", $paisesEuropeos)],
            "LITUANIA" => ["value" => "A9139AAAAA", "isEuropean" => in_array("LITUANIA", $paisesEuropeos)],
            "LUXEMBURGO" => ["value" => "A9119AAAAA", "isEuropean" => in_array("LUXEMBURGO", $paisesEuropeos)],
            "MACAO" => ["value" => "A9463AAAAA", "isEuropean" => in_array("MACAO", $paisesEuropeos)],
            "MACEDONIA" => ["value" => "A9159AAAAA", "isEuropean" => in_array("MACEDONIA", $paisesEuropeos)],
            "MADAGASCAR" => ["value" => "A9354AAAAA", "isEuropean" => in_array("MADAGASCAR", $paisesEuropeos)],
            "MALASIA" => ["value" => "A9425AAAAA", "isEuropean" => in_array("MALASIA", $paisesEuropeos)],
            "MALAWI" => ["value" => "A9346AAAAA", "isEuropean" => in_array("MALAWI", $paisesEuropeos)],
            "MALDIVAS" => ["value" => "A9436AAAAA", "isEuropean" => in_array("MALDIVAS", $paisesEuropeos)],
            "MALI" => ["value" => "A9347AAAAA", "isEuropean" => in_array("MALI", $paisesEuropeos)],
            "MALTA" => ["value" => "A9120AAAAA", "isEuropean" => in_array("MALTA", $paisesEuropeos)],
            "MARRUECOS" => ["value" => "A9348AAAAA", "isEuropean" => in_array("MARRUECOS", $paisesEuropeos)],
            "MAURICIO" => ["value" => "A9349AAAAA", "isEuropean" => in_array("MAURICIO", $paisesEuropeos)],
            "MAURITANIA" => ["value" => "A9350AAAAA", "isEuropean" => in_array("MAURITANIA", $paisesEuropeos)],
            "MEXICO" => ["value" => "A9234AAA1A", "isEuropean" => in_array("MEXICO", $paisesEuropeos)],
            "MOLDAVIA" => ["value" => "A9148AAAAA", "isEuropean" => in_array("MOLDAVIA", $paisesEuropeos)],
            "MONACO" => ["value" => "A9121AAAAA", "isEuropean" => in_array("MONACO", $paisesEuropeos)],
            "MONGOLIA" => ["value" => "A9427AAAAA", "isEuropean" => in_array("MONGOLIA", $paisesEuropeos)],
            "MONTENEGRO" => ["value" => "A9160AAAAA", "isEuropean" => in_array("MONTENEGRO", $paisesEuropeos)],
            "MOZAMBIQUE" => ["value" => "A9351AAAAA", "isEuropean" => in_array("MOZAMBIQUE", $paisesEuropeos)],
            "MYANMAR" => ["value" => "A9400AAAAA", "isEuropean" => in_array("MYANMAR", $paisesEuropeos)],
            "NAMIBIA" => ["value" => "A9353AAAAA", "isEuropean" => in_array("NAMIBIA", $paisesEuropeos)],
            "NAURU" => ["value" => "A9541AAAAA", "isEuropean" => in_array("NAURU", $paisesEuropeos)],
            "NEPAL" => ["value" => "A9541AAAAA", "isEuropean" => in_array("NEPAL", $paisesEuropeos)],
            "NICARAGUA" => ["value" => "A9236AAAAA", "isEuropean" => in_array("NICARAGUA", $paisesEuropeos)],
            "NIGER" => ["value" => "A9360AAAAA", "isEuropean" => in_array("NIGER", $paisesEuropeos)],
            "NIGERIA" => ["value" => "A9352AAAAA", "isEuropean" => in_array("NIGERIA", $paisesEuropeos)],
            "NORUEGA" => ["value" => "A9122AAAAA", "isEuropean" => in_array("NORUEGA", $paisesEuropeos)],
            "NUEVA ZELANDA" => ["value" => "A9540AAAAA", "isEuropean" => in_array("NUEVA ZELANDA", $paisesEuropeos)],
            "OCEANIA" => ["value" => "A9599AAAAA", "isEuropean" => in_array("OCEANIA", $paisesEuropeos)],
            "OMAN" => ["value" => "A9444AAAAA", "isEuropean" => in_array("OMAN", $paisesEuropeos)],
            "PAISES BAJOS" => ["value" => "A9123AAA1A", "isEuropean" => in_array("PAISES BAJOS", $paisesEuropeos)],
            "PAKISTAN" => ["value" => "A9424AAA1A", "isEuropean" => in_array("PAKISTAN", $paisesEuropeos)],
            "PALESTINA" => ["value" => "A9440AAAAA", "isEuropean" => in_array("PALESTINA", $paisesEuropeos)],
            "PANAMA" => ["value" => "A9238AAAAA", "isEuropean" => in_array("PANAMA", $paisesEuropeos)],
            "PAPUA NUEVA GUINEA" => ["value" => "A9542AAAAA", "isEuropean" => in_array("PAPUA NUEVA GUINEA", $paisesEuropeos)],
            "PARAGUAY" => ["value" => "A9240AAAAA", "isEuropean" => in_array("PARAGUAY", $paisesEuropeos)],
            "PERU" => ["value" => "A9242AAAAA", "isEuropean" => in_array("PERU", $paisesEuropeos)],
            "POLONIA" => ["value" => "A9124AAAAA", "isEuropean" => in_array("POLONIA", $paisesEuropeos)],
            "PORTUGAL" => ["value" => "A9125AAAAA", "isEuropean" => in_array("PORTUGAL", $paisesEuropeos)],
            "PUERTO RICO" => ["value" => "A9244AAAAA", "isEuropean" => in_array("PUERTO RICO", $paisesEuropeos)],
            "QATAR" => ["value" => "A9431AAAAA", "isEuropean" => in_array("QATAR", $paisesEuropeos)],
            "REINO UNIDO" => ["value" => "A9112AAA1A", "isEuropean" => in_array("REINO UNIDO", $paisesEuropeos)],
            "REPUBLICA BENIN" => ["value" => "A9302AAA1A", "isEuropean" => in_array("REPUBLICA BENIN", $paisesEuropeos)],
            "REPUBLICA CENTROAFRICANA" => ["value" => "A9310AAA1A", "isEuropean" => in_array("REPUBLICA CENTROAFRICANA", $paisesEuropeos)],
            "REPUBLICA CHECA" => ["value" => "A9157AAAAA", "isEuropean" => in_array("REPUBLICA CHECA", $paisesEuropeos)],
            "REPUBLICA CONGO" => ["value" => "A9312AAA1A", "isEuropean" => in_array("REPUBLICA CONGO", $paisesEuropeos)],
            "REPUBLICA DEMOCRATICA CONGO" => ["value" => "A9380AAAAA", "isEuropean" => in_array("REPUBLICA DEMOCRATICA CONGO", $paisesEuropeos)],
            "REPUBLICA DOMINICANA" => ["value" => "A9218AAA1A", "isEuropean" => in_array("REPUBLICA DOMINICANA", $paisesEuropeos)],
            "REPUBLICA GRANADA" => ["value" => "A9229AAAAA", "isEuropean" => in_array("REPUBLICA GRANADA", $paisesEuropeos)],
            "REPUBLICA KIRGUISTAN" => ["value" => "A9466AAA1A", "isEuropean" => in_array("REPUBLICA KIRGUISTAN", $paisesEuropeos)],
            "REPUBLICA SUDAN SUR" => ["value" => "A9369AAAAA", "isEuropean" => in_array("REPUBLICA SUDAN SUR", $paisesEuropeos)],
            "RIO MUNI" => ["value" => "A9397AAAAA", "isEuropean" => in_array("RIO MUNI", $paisesEuropeos)],
            "RUANDA" => ["value" => "A9306AAAAA", "isEuropean" => in_array("RUANDA", $paisesEuropeos)],
            "RUMANIA" => ["value" => "A9127AAAAA", "isEuropean" => in_array("RUMANIA", $paisesEuropeos)],
            "RUSIA" => ["value" => "A9149AAAAA", "isEuropean" => in_array("RUSIA", $paisesEuropeos)],
            "SAHARA" => ["value" => "A9398AAAAA", "isEuropean" => in_array("SAHARA", $paisesEuropeos)],
            "SAINT KITTS NEVIS" => ["value" => "A9256AAA1A", "isEuropean" => in_array("SAINT KITTS NEVIS", $paisesEuropeos)],
            "SALVADOR" => ["value" => "A9220AAAAA", "isEuropean" => in_array("SALVADOR", $paisesEuropeos)],
            "SAMOA OCCIDENTAL" => ["value" => "A9552AAAAA", "isEuropean" => in_array("SAMOA OCCIDENTAL", $paisesEuropeos)],
            "SAN MARINO" => ["value" => "A9135AAAAA", "isEuropean" => in_array("SAN MARINO", $paisesEuropeos)],
            "SAN MARTIN" => ["value" => "A9259AAAAA", "isEuropean" => in_array("SAN MARTIN", $paisesEuropeos)],
            "SAN VICENTE GRANADINAS" => ["value" => "A9254AAA1A", "isEuropean" => in_array("SAN VICENTE GRANADINAS", $paisesEuropeos)],
            "SANTA LUCIA" => ["value" => "A9253AAAAA", "isEuropean" => in_array("SANTA LUCIA", $paisesEuropeos)],
            "SANTA SEDE" => ["value" => "A9136AAA2A", "isEuropean" => in_array("SANTA SEDE", $paisesEuropeos)],
            "SANTO TOME PRINCIPE" => ["value" => "A9361AAAAA", "isEuropean" => in_array("SANTO TOME PRINCIPE", $paisesEuropeos)],
            "SENEGAL" => ["value" => "A9362AAAAA", "isEuropean" => in_array("SENEGAL", $paisesEuropeos)],
            "SERBIA" => ["value" => "A9155AAAAA", "isEuropean" => in_array("SERBIA", $paisesEuropeos)],
            "SEYCHELLES" => ["value" => "A9363AAAAA", "isEuropean" => in_array("SEYCHELLES", $paisesEuropeos)],
            "SIERRA LEONA" => ["value" => "A9364AAAAA", "isEuropean" => in_array("SIERRA LEONA", $paisesEuropeos)],
            "SINGAPUR" => ["value" => "A9426AAAAA", "isEuropean" => in_array("SINGAPUR", $paisesEuropeos)],
            "SIRIA" => ["value" => "A9433AAAAA", "isEuropean" => in_array("SIRIA", $paisesEuropeos)],
            "SOMALIA" => ["value" => "A9365AAAAA", "isEuropean" => in_array("SOMALIA", $paisesEuropeos)],
            "SRI LANKA" => ["value" => "A9404AAAAA", "isEuropean" => in_array("SRI LANKA", $paisesEuropeos)],
            "SUDAFRICA" => ["value" => "A9367AAAAA", "isEuropean" => in_array("SUDAFRICA", $paisesEuropeos)],
            "SUDAN" => ["value" => "A9368AAAAA", "isEuropean" => in_array("SUDAN", $paisesEuropeos)],
            "SUDAN SUR" => ["value" => "A9369AAA1A", "isEuropean" => in_array("SUDAN SUR", $paisesEuropeos)],
            "SUECIA" => ["value" => "A9128AAAAA", "isEuropean" => in_array("SUECIA", $paisesEuropeos)],
            "SUIZA" => ["value" => "A9129AAAAA", "isEuropean" => in_array("SUIZA", $paisesEuropeos)],
            "SURINAM" => ["value" => "A9250AAAAA", "isEuropean" => in_array("SURINAM", $paisesEuropeos)],
            "SWAZILANDIA" => ["value" => "A9371AAAAA", "isEuropean" => in_array("SWAZILANDIA", $paisesEuropeos)],
            "TADJIKISTAN" => ["value" => "A9469AAAAA", "isEuropean" => in_array("TADJIKISTAN", $paisesEuropeos)],
            "TAILANDIA" => ["value" => "A9428AAA1A", "isEuropean" => in_array("TAILANDIA", $paisesEuropeos)],
            "TAIWAN TAIPEI" => ["value" => "A9408AAA3A", "isEuropean" => in_array("TAIWAN TAIPEI", $paisesEuropeos)],
            "TANZANIA" => ["value" => "A9370AAAAA", "isEuropean" => in_array("TANZANIA", $paisesEuropeos)],
            "TIMOR ORIENTAL" => ["value" => "A9464AAAAA", "isEuropean" => in_array("TIMOR ORIENTAL", $paisesEuropeos)],
            "TOGO" => ["value" => "A9374AAAAA", "isEuropean" => in_array("TOGO", $paisesEuropeos)],
            "TONGA" => ["value" => "A9554AAAAA", "isEuropean" => in_array("TONGA", $paisesEuropeos)],
            "TRINIDAD TOBAGO" => ["value" => "A9245AAAAA", "isEuropean" => in_array("TRINIDAD TOBAGO", $paisesEuropeos)],
            "TUNEZ" => ["value" => "A9378AAAAA", "isEuropean" => in_array("TUNEZ", $paisesEuropeos)],
            "TURKMENISTAN" => ["value" => "A9467AAA1A", "isEuropean" => in_array("TURKMENISTAN", $paisesEuropeos)],
            "TURQUIA" => ["value" => "A9130AAAAA", "isEuropean" => in_array("TURQUIA", $paisesEuropeos)],
            "TUVALU" => ["value" => "A9560AAAAA", "isEuropean" => in_array("TUVALU", $paisesEuropeos)],
            "UCRANIA" => ["value" => "A9152AAAAA", "isEuropean" => in_array("UCRANIA", $paisesEuropeos)],
            "UGANDA" => ["value" => "A9358AAAAA", "isEuropean" => in_array("UGANDA", $paisesEuropeos)],
            "UNION EUROPEA" => ["value" => "A9190AAA1A", "isEuropean" => in_array("UNION EUROPEA", $paisesEuropeos)],
            "URUGUAY" => ["value" => "A9246AAAAA", "isEuropean" => in_array("URUGUAY", $paisesEuropeos)],
            "UZBEKISTAN" => ["value" => "A9468AAAAA", "isEuropean" => in_array("UZBEKISTAN", $paisesEuropeos)],
            "VANUATU" => ["value" => "A9565AAAAA", "isEuropean" => in_array("VANUATU", $paisesEuropeos)],
            "VENEZUELA" => ["value" => "A9248AAAAA", "isEuropean" => in_array("VENEZUELA", $paisesEuropeos)],
            "VIETNAM" => ["value" => "A9430AAAAA", "isEuropean" => in_array("VIETNAM", $paisesEuropeos)],
            "YEMEN" => ["value" => "A9434AAAAA", "isEuropean" => in_array("YEMEN", $paisesEuropeos)],
            "ZAMBIA" => ["value" => "A9382AAAAA", "isEuropean" => in_array("ZAMBIA", $paisesEuropeos)],
            "ZIMBABWE" => ["value" => "A9357AAAAA", "isEuropean" => in_array("ZIMBABWE", $paisesEuropeos)]
        ];

        // Convertir tipo a mayúsculas para manejar la consistencia
        $tipo = mb_strtoupper($tipo);

        if (array_key_exists($tipo, $paisesDni)) {
            // Devuelve el valor, si es europeo y el nombre del país (índice)
            return [
                'index' => $tipo,
                'value' => $paisesDni[$tipo]['value'],
                'isEuropean' => $paisesDni[$tipo]['isEuropean']
            ];
        } else {
            // Devuelve null o algún valor por defecto si el país no se encuentra
            return null;
        }
    }

    public function store(Request $request)
    {
        // dd($request->all());
        
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
                // dd($request->input('nacionalidad_'.$i));
                
                $cliente = Cliente::where('id', $reserva->cliente_id)->first();
                $resultado = $this->obtenerNacionalidad($request->input('nacionalidad_'.$i));
                // Comprobamos si la reserva ya tiene los dni entregados
                $cliente->nombre = $request->input('nombre_'.$i);
                $cliente->apellido1 = $request->input('apellido1_'.$i);
                $cliente->apellido2 = $request->input('apellido2_'.$i) ? $request->input('apellido2_'.$i) : null;
                $cliente->tipo_documento = $request->input('tipo_documento_'.$i);
                $cliente->tipo_documento_str = $this->obtenerStringDNI($request->input('tipo_documento_'.$i));
                $cliente->num_identificacion = $request->input('num_identificacion_'.$i);
                $cliente->fecha_expedicion_doc = $request->input('fecha_expedicion_doc_'.$i);
                $cliente->fecha_nacimiento = $request->input('fecha_nacimiento_'.$i);
                $cliente->sexo = $request->input('sexo_'.$i);
                $cliente->sexo_str = $request->input('sexo_'.$i) == "Masculino" ? "M" : "F";
                $cliente->email = $request->input('email_'.$i);
                $cliente->nacionalidadStr = $resultado['index'];
                $cliente->nacionalidadCode = $resultado['value'];
                // $cliente->data_dni = true;
                $cliente->save();
                // $data = [
                //     'jsonHiddenComunes'=> null, 
                //     'idHospederia' => $idHospederia,
                //     'nombre' => 'DANI',
                //     'apellido1' => $apellido,
                //     'apellido2' => 'MEFLE',
                //     'nacionalidad' => 'A9109AAAAA',
                //     'nacionalidadStr' => 'ESPAÑA',
                //     'tipoDocumento' => 'D',
                //     'tipoDocumentoStr' => 'DNI',
                //     'numIdentificacion' => '76586766D',
                //     'fechaExpedicionDoc' => '05/01/2022',
                //     'dia' => '23',
                //     'mes' => '11',
                //     'ano' => '2000',
                //     'fechaNacimiento' => '23/11/2000',
                //     'sexo' => 'M',
                //     'sexoStr' => 'MASCULINO',
                //     'fechaEntrada' => '21/12/2023',
                //     '_csrf' => $csrfToken
                // ];
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
                    $resultadoHuesped = $this->obtenerNacionalidad($request->input('nacionalidad_'.$i));

                    // Comprobamos si la reserva ya tiene los dni entregados
                    $huesped->reserva_id = $reserva->id;
                    $huesped->nombre = $request->input('nombre_'.$i);
                    $huesped->primer_apellido = $request->input('apellido1_'.$i);
                    $huesped->segundo_apellido = $request->input('apellido2_'.$i) ? $request->input('apellido2_'.$i) : null;
                    $huesped->tipo_documento = $request->input('tipo_documento_'.$i);
                    $huesped->tipo_documento_str = $this->obtenerStringDNI($request->input('tipo_documento_'.$i));
                    $huesped->numero_identificacion = $request->input('num_identificacion_'.$i);
                    $huesped->fecha_expedicion = $request->input('fecha_expedicion_doc_'.$i);
                    $huesped->fecha_nacimiento = $request->input('fecha_nacimiento_'.$i);
                    $huesped->sexo = $request->input('sexo_'.$i);
                    $huesped->sexo_str = $request->input('sexo_'.$i) == "Masculino" ? "M" : "F";
                    $huesped->pais = $request->input('pais'.$i);
                    $huesped->email = $request->input('email_'.$i);
                    $huesped->contador = $i;
                    $huesped->nacionalidadStr = $resultadoHuesped['index'];
                    $huesped->nacionalidadCode = $resultadoHuesped['value'];
                    $huesped->nacionalidad = $request->input('nacionalidad_'.$i);
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
                        if($request->hasFile('pasaporte_'.$i)){
                            // Imagen Pasaporte
                            $file = $request->file('pasaporte_'.$i);
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
                    $resultadoHuesped = $this->obtenerNacionalidad($request->input('nacionalidad_'.$i));

                    // Comprobamos si la reserva ya tiene los dni entregados
                    $huespedNew = [
                        'nombre' => $request->input('nombre_'.$i),
                        'primer_apellido' => $request->input('apellido1_'.$i),
                        'segundo_apellido' => $request->input('apellido2_'.$i) ? $request->input('apellido2_'.$i) : null,
                        'tipo_documento' => $request->input('tipo_documento_'.$i),
                        'tipo_documento_str' => $this->obtenerStringDNI($request->input('tipo_documento_'.$i)),
                        'numero_identificacion' => $request->input('num_identificacion_'.$i),
                        'fecha_expedicion' => $request->input('fecha_expedicion_doc_'.$i),
                        'fecha_nacimiento' => $request->input('fecha_nacimiento_'.$i),
                        'sexo' => $request->input('sexo_'.$i),
                        'sexo_str' =>$request->input('sexo_'.$i) == "Masculino" ? "M" : "F",
                        'pais' => $request->input('pais'.$i),
                        'email'  => $request->input('email_'.$i),
                        'contador' => $i,
                        'reserva_id' => $reserva->id,
                        'nacionalidadStr' => $resultadoHuesped['index'],
                        'nacionalidadCode' => $resultadoHuesped['value'],
                        'nacionalidad' => $request->input('nacionalidad_'.$i)

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
                        if($request->hasFile('pasaporte_'.$i)){
                            // Imagen Frontal DNI
                            $file = $request->file('pasaporte_'.$i);
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
