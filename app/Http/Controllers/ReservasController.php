<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\ChatGpt;
use App\Models\Cliente;
use App\Models\Huesped;
use App\Models\Invoices;
use App\Models\InvoicesReferenceAutoincrement;
use App\Models\MensajeAuto;
use App\Models\Photo;
use App\Models\Reserva;
use App\Services\ChatGptService;
use Carbon\Carbon;
use Carbon\Cli\Invoker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReservasController extends Controller
{
    protected $chatGptService;

    public function __construct(ChatGptService $ChatGptService)
    {
        $this->chatGptService = $ChatGptService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $orderBy = $request->get('order_by', 'fecha_entrada');
    $direction = $request->get('direction', 'asc');
    $perPage = $request->get('perPage', 10);
    $searchTerm = $request->get('search', '');

    // Obtener fechas del request, usando null como predeterminado si no se especifican
    $fechaEntrada = $request->get('fecha_entrada');
    $fechaSalida = $request->get('fecha_salida');

    $query = Reserva::with('cliente')->where('estado_id', '!=', 4);

    if (!empty($searchTerm)) {
        $query->where(function($subQuery) use ($searchTerm) {
            $subQuery->whereHas('cliente', function($q) use ($searchTerm) {
                $q->where('alias', 'LIKE', '%' . $searchTerm . '%');
            })
            ->orWhere('codigo_reserva', 'LIKE', '%' . $searchTerm . '%')
            ->orWhere('fecha_entrada', 'LIKE', '%' . $searchTerm . '%')
            ->orWhere('fecha_salida', 'LIKE', '%' . $searchTerm . '%')
            ->orWhere('origen', 'LIKE', '%' . $searchTerm . '%');
        });
    }

    // Aplicar filtros de fechas solo si se proporcionan
    if (!empty($fechaEntrada)) {
        $query->whereDate('fecha_entrada', '=', $fechaEntrada);
    }
    if (!empty($fechaSalida)) {
        $query->whereDate('fecha_salida', '=', $fechaSalida);
    }

    $reservas = $query->orderBy($orderBy, $direction)->paginate($perPage)->appends([
        'order_by' => $orderBy,
        'direction' => $direction,
        'search' => $searchTerm,
        'perPage' => $perPage,
        'fecha_entrada' => $fechaEntrada,
        'fecha_salida' => $fechaSalida,
    ]);

    return view('reservas.index', compact('reservas'));
}


    /**
     * Display a listing of the resource.
     */
    public function calendar()
    {
        return view('reservas.calendar');

    }

    public function getReservas()
    {
        $reservas = Reserva::all();
        foreach($reservas as $reserva){
            $cliente = Cliente::find($reserva->cliente_id);
            $reserva['cliente'] = $cliente;
            $apartamento = Apartamento::find($reserva->apartamento_id);
            $reserva['apartamento'] = $apartamento;

        }
        return response()->json($reservas);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {   
        $clientes = Cliente::all();
        $apartamentos = Apartamento::all();
        return view('reservas.create', compact('clientes','apartamentos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|integer',
            'apartamento_id' => 'required|integer',
            'estado_id' => 'required|integer',
            'origen' => 'required|string',
            'fecha_entrada' => 'required|date',
            'fecha_salida' => 'required|date',
            'codigo_reserva' => 'required|string',
            'precio' => 'required|string',
            'verificado' => 'nullable|integer',
            'dni_entregado' => 'nullable|integer',
            'enviado_webpol' => 'nullable|integer',
            'fecha_limpieza' => 'nullable|date'
        ]);
    
        $reserva = new Reserva($request->all());
        $reserva->save();
    
        return redirect()->route('reservas.index')->with('success', 'Reserva creada con éxito');
    }

    /**
     * Display the specified resource.
     */
    public function show(Reserva $reserva)
    {
        $huespedes = Huesped::where('reserva_id', $reserva->id)->get();
        $mensajes = MensajeAuto::where('reserva_id', $reserva->id)->get();
        $photos = Photo::where('reserva_id', $reserva->id)->get();
        $factura = Invoices::where('reserva_id', $reserva->id)->first();
        return view('reservas.show', compact('reserva', 'mensajes', 'photos','huespedes', 'factura'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Buscar la reserva por su ID
        $reserva = Reserva::find($id);
        
        // Validar que la reserva existe
        if (!$reserva) {
            return response()->json(['success' => false, 'message' => 'Reserva no encontrada'], 404);
        }
        
        // Intentar parsear la nueva fecha
        try {
            $newDate = Carbon::createFromFormat('Y-m-d', $request->new_date);
        } catch (\Carbon\Exceptions\InvalidFormatException $e) {
            return response()->json(['success' => false, 'message' => 'Formato de fecha inválido'], 400);
        }
    
        // Revisar si estamos actualizando la fecha de entrada o de salida
        if ($request->drag_type == 'start') {
            // Actualizar la fecha de entrada (se puede sumar o restar días)
            // Asegúrate de que la nueva fecha de entrada no sea posterior a la fecha de salida
            if ($newDate->lessThanOrEqualTo($reserva->fecha_salida)) {
                $reserva->fecha_entrada = $newDate;
            } else {
                return response()->json(['success' => false, 'message' => 'La fecha de entrada no puede ser posterior a la fecha de salida'], 400);
            }
        } elseif ($request->drag_type == 'end') {
            // Actualizar la fecha de salida (se puede sumar o restar días)
            // Asegúrate de que la nueva fecha de salida no sea anterior a la fecha de entrada
            if ($newDate->greaterThanOrEqualTo($reserva->fecha_entrada)) {
                $reserva->fecha_salida = $newDate;
            } else {
                return response()->json(['success' => false, 'message' => 'La fecha de salida no puede ser anterior a la fecha de entrada'], 400);
            }
        }
    
        // Guardar los cambios en la base de datos
        $reserva->save();
    
        // Devolver una respuesta JSON indicando éxito
        return response()->json(['success' => true, 'message' => 'Reserva actualizada correctamente']);
    }
    


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    /**
     * Remove the specified resource from storage.
     */
    public function actualizarBooking($reserva, Request $request)
    {
		$reserva = Reserva::where('codigo_reserva', $reserva)->first();
        $reserva->fecha_salida = $request->fecha_salida;
        $reserva->save();
        return response('La reserva de ha actualizado', 200);

    }
    /**
     * Remove the specified resource from storage.
     */
    public function actualizarAirbnb($reserva, Request $request)
    {
		$reserva = Reserva::where('codigo_reserva', $reserva)->first();
        $reserva->fecha_salida = $request->fecha_salida;
        $reserva->save();
        return response('La reserva de ha actualizado', 200);

    }

    public function agregarReserva(Request $request){

        // Obtenemos la Fecha de Hoy
        $hoy = Carbon::now();
        // Declaramos Variables
        $cliente;
        $reserva;
        $num_adultos;
        // Convertimos las Request en la data
        $data = $request->all();
        // Almacenamos la peticion en un archivo
        Storage::disk('local')->put($data['codigo_reserva'].'-' . $hoy .'.txt', json_encode($request->all()));

        // Comprobamos si la reserva ya existe
        $comprobarReserva = Reserva::where('codigo_reserva', $data['codigo_reserva'])->first();
        // Si la reserva no existe procedemos al registro
        if ($comprobarReserva == null) {
            // Obtenemos el Cliente si existe por el numero de telefono
            $verificarCliente = Cliente::where('telefono', $data['telefono'] )->first();
            // Validamos si existe el cliente
            if ($verificarCliente == null) {
                // Si no existe separamos el nombre y el numero de personas para el apartamento
				if (preg_match('/^(.*?)\n(\d+)\s*adulto(?:s)?/', $data['alias'], $matches)) {
                    // Establecemos el nombre y numero de adultos
					$nombre = trim($matches[1]);
					$num_adultos = $matches[2];

                    // Creamos el cliente
					$crearCliente = Cliente::create([
						'alias' => $nombre,
						'idiomas' => $data['idiomas'],
						'telefono' => $data['telefono'],
						'email_secundario' => $data['email'],
					]);
					$cliente = $crearCliente;

				}else {
                    // Si existe creamos al cliente
					$crearCliente = Cliente::create([
						'alias' => $data['alias'],
						'idiomas' => $data['idiomas'],
						'telefono' => $data['telefono'],
						'email_secundario' => $data['email'],
					]);
					$cliente = $crearCliente;
				}


            }else {
                // En caso que el cliente ya existe
                $cliente = $verificarCliente;
            }
            // Establece el idioma a español para reconocer 'jue' como 'jueves' y 'sep' como 'septiembre'
            $locale = 'es';
			Carbon::setLocale($locale);
            // Parseamos las Fechas
           	$fecha_entrada = Carbon::createFromFormat('Y-m-d', $data['fecha_entrada']);
			$fecha_salida = Carbon::createFromFormat('Y-m-d', $data['fecha_salida']);

            // Verificamos si la reserva existe por el codigo de reserva
            $verificarReserva = Reserva::where('codigo_reserva',$data['codigo_reserva'] )->first();
            // Si la reserva no existe
            if ($verificarReserva == null) {
                // Comprobamos el origen para obtener el ID del apartamento
                if ($data['origen'] == 'Booking') {
                    // Si es booking lo obtenemos por el id del apartamento en booking
                    $apartamento = Apartamento::where('id_booking', $data['apartamento'])->first();
                }
                else if($data['origen'] == 'Airbnb'){
                    // Si es de Airbnb lo obtenemos por el nombre del apartamento
                    $searchQuery = $request->input('apartamento');
                    $bestMatch = $this->findClosestMatch($searchQuery);
                
                    if ($bestMatch) {
                        $apartamento = $bestMatch;
                        // dd($apartamento);
                        // return response()->json([
                        //     'success' => true,
                        //     'message' => 'Apartamento encontrado',
                        //     'data' => $bestMatch
                        // ]);
                    } 
                    // $apartamentoEncontrado = Apartamento::where('nombre', $data['apartamento'])->first();
                    // dd($apartamentoEncontrado);
                    // switch ($data['apartamento']) {
                    //     case 'Atico nueva contruccion en el centro de Algeciras':
                    //         $apartamento = (object) ['id'=> 1];
                    //         break;
                    //     case 'Apartamento interior en el centro de Algeciras 2A':
                    //         $apartamento = (object) ['id'=> 2];
                    //         break;
                    //     case 'Apartamento en el absoluto centro 2B':
                    //         $apartamento = (object) ['id'=> 3];
                    //         break;
                    //     case 'Apartamento interior centro en Algeciras 1º A':
                    //         $apartamento = (object) ['id'=> 4];
                    //         break;
                    //     case 'Apartamento de 2020 a estrenar en pleno centro1B':
                    //         $apartamento = (object) ['id'=> 5];
                    //         break;
                    //     case 'Apartamento interior en el absoluto centro BA':
                    //         $apartamento = (object) ['id'=> 6];
                    //         break;
                    //     case 'Apartamento BB Centro Algeciras':
                    //         $apartamento = (object) ['id'=> 7];
                    //         break;


                    //     default:
                    //         $apartamento = (object) ['id'=> null];
                    //         break;
                    // }
                } else {
                    $apartamento = Apartamento::where('id_web', $data['apartamento'])->first();

                }
                // Formateamos el precio
                $precioOriginal = $data['precio'];
                $precioSinSimbolo = preg_replace('/[€\s]/', '', $precioOriginal);
                $precio = floatval($precioSinSimbolo);

                // Creamos la Reserva
                $crearReserva = Reserva::create([
                    'codigo_reserva' => $data['codigo_reserva'],
                    'origen' => $data['origen'],
                    'fecha_entrada' =>  $fecha_entrada,
                    'fecha_salida' => $fecha_salida,
                    'precio' => $precio,
                    'apartamento_id' => $apartamento->id,
                    'cliente_id' => $cliente->id,
                    'estado_id' => 1,
                    //'numero_personas_plataforma' => $data['adultos'],
                    // 'numero_personas' => $data['numero_personas']
                ]);
                $reserva = $crearReserva;
                return response('Registrado', 200);

            } else {
                // Si ya existe la reserva
                $reserva = $verificarReserva;
                return response('Ya existe la Reserva', 200);

            }

        } else {
            return response('Ya existe la Reserva', 200);
        }

    }
    function levenshteinDistance($str1, $str2) {
        $len1 = strlen($str1);
        $len2 = strlen($str2);
    
        $matrix = [];
    
        for ($i = 0; $i <= $len1; $i++) {
            $matrix[$i][0] = $i;
        }
    
        for ($j = 0; $j <= $len2; $j++) {
            $matrix[0][$j] = $j;
        }
    
        for ($i = 1; $i <= $len1; $i++) {
            for ($j = 1; $j <= $len2; $j++) {
                if ($str1[$i - 1] == $str2[$j - 1]) {
                    $cost = 0;
                } else {
                    $cost = 1;
                }
                $matrix[$i][$j] = min(
                    $matrix[$i - 1][$j] + 1,      // deletion
                    $matrix[$i][$j - 1] + 1,      // insertion
                    $matrix[$i - 1][$j - 1] + $cost  // substitution
                );
            }
        }
    
        return $matrix[$len1][$len2];
    }
    public function findClosestMatch($searchQuery) {
        // Obtener todos los nombres de apartamentos de la base de datos
        $apartments = Apartamento::all();
    
        $closestMatch = null;
        $shortestDistance = PHP_INT_MAX;
    
        foreach ($apartments as $apartment) {
            $distance = $this->levenshteinDistance($searchQuery, $apartment->nombre);
            if ($distance < $shortestDistance) {
                $shortestDistance = $distance;
                $closestMatch = $apartment;
            }
        }
    
        return $closestMatch;
    }

	public function cancelarAirBnb($reserva){
        // Conprobamos la reserva con el codigo de reserva
		$reserva = Reserva::where('codigo_reserva', $reserva)->first();
        // Si la reserva no existe
        if ($reserva== null) {
            return response('La reserva no existe', 404);
        }
        // Si la reserva existe
        // Cambiamos el estado a CAncelado
		$reserva->estado_id = 4;
		$reserva->save();

        return response('La reserva de ha cancelado', 200);

	}
	public function cancelarBooking($reserva){
        // return $reserva;
        // Conprobamos la reserva con el codigo de reserva
		$reservaCancelar = Reserva::where('codigo_reserva', $reserva)->first();

        if ($reservaCancelar != null && $reservaCancelar->estado_id === 4) {
            return response('La reserva ya esta cancelada', 201);
        }

        // Si la reserva no existe
        if ($reservaCancelar== null) {
            return response('La reserva no existe', 404);
        } else {
            // Cambiamos el estado a CAncelado
            $reservaCancelar->estado_id = 4;
            $reservaCancelar->save();
            return response('La reserva de ha cancelado', 200);
        }
	}


    public function getData() {
        $hoy = Carbon::now();
        $reservas = Reserva::whereDate('fecha_entrada', $hoy)
                          ->where(function($query) {
                              $query->where('dni_entregado', true);
                          })
                          ->where(function($query) {
                              $query->where('enviado_webpol', false)
                                    ->orWhereNull('enviado_webpol');
                          })
                          ->get();
        if (count($reservas) > 0) {
            foreach($reservas as $reserva){
                $reserva['cliente'] = $reserva->cliente;
            }
        }

        return response()->json($reservas, 200);
    }

    public function changeState(Request $request) {
        if (isset($request->id)) {

            $id = $request->id;
            $reserva = Reserva::find($id);
            $reserva->enviado_webpol = 1;
            $reserva->save();

        } else{

            return response()->json('No se encontro la propiedad ID en la petición.', 400);
        }


        return response()->json('Se actualizo el estado correctamente', 200);
    }

    public function facturarReservas(){
        
        $hoy = Carbon::now()->subDay(1); // La fecha actual
        $juevesPasado = Carbon::now()->subDays(5); // Restar 5 días para obtener el jueves de la semana pasada
        
        // Obtener reservas desde el jueves pasado hasta hoy (inclusive)
        $reservas = Reserva::whereDate('fecha_salida', '>=', $juevesPasado)
            ->whereDate('fecha_salida', '<=', $hoy)
            ->whereNotIn('estado_id', [5, 6]) // Filtrar estado_id diferente de 5 o 6
            ->get();
        foreach( $reservas as $reserva){
            $data = [
                'budget_id' => null,
                'cliente_id' => $reserva->cliente_id,
                'reserva_id' => $reserva->id,
                // 'invoice_status_id' => 1,
                'concepto' => 'Estancia en apartamento: '. $reserva->apartamento->titulo,
                'description' => '',
                'fecha' => $reserva->fecha_salida,
                'fecha_cobro' => null,
                'base' => $reserva->precio - ($reserva->precio * 0.10),
                'iva' => $reserva->precio * 0.10,
                'descuento' => null,
                'total' => $reserva->precio,
            ];
            $crear = Invoices::create($data);
            $referencia = $this->generateBudgetReference($crear);
            $crear->reference = $referencia['reference'];
            $crear->reference_autoincrement_id = $referencia['id'];
            $crear->invoice_status_id = 1;
            // $crear->budget_status_id = 3;
            $crear->save();    
            $reserva->estado_id = 5;
            $reserva->save();
            // return;

        }
        return response()->json($reservas);
    }
    
    public function generateReferenceTemp($reference){

        // Extrae los dos dígitos del final de la cadena usando expresiones regulares
        preg_match('/temp_(\d{2})/', $reference, $matches);
       // Incrementa el número primero
       if(count($matches) >= 1){
           $incrementedNumber = intval($matches[1]) + 1;
           // Asegura que el número tenga dos dígitos
           $formattedNumber = str_pad($incrementedNumber, 2, '0', STR_PAD_LEFT);
           // Concatena con la cadena "temp_"
           return "temp_" . $formattedNumber;
       }
   }

   private function generateReferenceDelete($reference){
        // Extrae los dos dígitos del final de la cadena usando expresiones regulares
        preg_match('/delete_(\d{2})/', $reference, $matches);
       // Incrementa el número primero
       if(count($matches) >= 1){
           $incrementedNumber = intval($matches[1]) + 1;
           // Asegura que el número tenga dos dígitos
           $formattedNumber = str_pad($incrementedNumber, 2, '0', STR_PAD_LEFT);
           // Concatena con la cadena "temp_"
           return "delete_" . $formattedNumber;
       }
   }

    public function generateBudgetReference(Invoices $invoices) {

       // Obtener la fecha actual del presupuesto
       $budgetCreationDate = $invoices->created_at ?? now();
       $datetimeBudgetCreationDate = new \DateTime($budgetCreationDate);

       // Formatear la fecha para obtener los componentes necesarios
       $year = $datetimeBudgetCreationDate->format('Y');
       $monthNum = $datetimeBudgetCreationDate->format('m');

       //dd($year, $monthNum, $budgetCreationDate, $datetimeBudgetCreationDate);
       // Buscar la última referencia autoincremental para el año y mes actual
       $latestReference = InvoicesReferenceAutoincrement::where('year', $year)
                           ->where('month_num', $monthNum)
                           ->orderBy('id', 'desc')
                           ->first();
        //dd($latestReference->reference_autoincrement);
       // Si no existe, empezamos desde 1, de lo contrario, incrementamos
       $newReferenceAutoincrement = $latestReference ? $latestReference->reference_autoincrement + 1 : 1;

       // Formatear el número autoincremental a 6 dígitos
       $formattedAutoIncrement = str_pad($newReferenceAutoincrement, 6, '0', STR_PAD_LEFT);

       // Crear la referencia
       $reference = $year . '/' . $monthNum . '/' . $formattedAutoIncrement;

       // Guardar o actualizar la referencia autoincremental en BudgetReferenceAutoincrement
       $referenceToSave = new InvoicesReferenceAutoincrement([
           'reference_autoincrement' => $newReferenceAutoincrement,
           'year' => $year,
           'month_num' => $monthNum,
           // Otros campos pueden ser asignados si son necesarios
       ]);
       $referenceToSave->save();

       // Devolver el resultado
       return [
           'id' => $referenceToSave->id,
           'reference' => $reference,
           'reference_autoincrement' => $newReferenceAutoincrement,
           'budget_reference_autoincrements' => [
               'year' => $year,
               'month_num' => $monthNum,
               // Añade aquí más si es necesario
           ],
       ];
   }

   public function getReservaIA($codigo){
        $reserva = Reserva::where('codigo_reserva', $codigo)->first();
        $data = [
            'codigo_reserva' => $reserva->codigo_reserva,
            'cliente' => $reserva->cliente->nombre == null ? $reserva->cliente->alias : $reserva->cliente->nombre .' ' . $reserva->cliente->apellido1,
            'apartamento' => $reserva->apartamento->titulo,
            'edificio' => $reserva->apartamento->edificioName->nombre,
            'fecha_entrada' => $reserva->fecha_entrada,
            'fecha_salida' => $reserva->fecha_salida,
        ];


        return response()->json($data);
   }


    // PRUEBAS CON LA INTELIGENCIA
    // PRUEBAS CON LA INTELIGENCIA
    public function probarIA(Request $request) {
        $mensaje = $request->input('texto');
        $contestacion = '';
        $response = '';

        // Verificar si el archivo de la conversación ya existe
        $filePath = storage_path('conversations/conversation.json');
        if (Storage::exists('conversations/conversation.json')) {
            $conversation = json_decode(Storage::get('conversations/conversation.json'), true);
        } else {
            $conversation = [];
        }

        if (isset($mensaje)) {
            $phone = '34622440984';

            // Obtener respuesta del servicio de IA
            $respuesta = $this->chatGptService->enviarMensajeAsistente($mensaje, $phone);

            // Añadir la nueva pregunta y respuesta al archivo JSON
            $conversation[] = [
                'pregunta' => $mensaje,
                'respuesta' => $respuesta
            ];

            // Guardar el archivo JSON actualizado
            Storage::put('conversations/conversation.json', json_encode($conversation));

            // Pasar la conversación a la vista junto con la respuesta
            return view('pruebasIA', compact('contestacion', 'response', 'conversation'));
        }

        // En caso de que no haya un mensaje, devolver la conversación previa
        return view('pruebasIA', compact('contestacion', 'conversation'));
    }
    public function mostrarInstrucciones()
    {
        // Verificar si el archivo existe
        if (Storage::exists('instrucciones.txt')) {
            $instrucciones = Storage::get('instrucciones.txt');
        } else {
            // Si no existe, creamos un archivo de ejemplo
            $instrucciones = "No hay instrucciones disponibles.";
            Storage::put('instrucciones.txt', $instrucciones);
        }

        // Retornar la vista con las instrucciones cargadas
        return response()->json(['instrucciones' => $instrucciones]);
    }

    public function guardarInstrucciones(Request $request)
    {
        $nuevasInstrucciones = $request->input('instrucciones');

        // Guardar las nuevas instrucciones en el archivo
        Storage::put('instrucciones.txt', $nuevasInstrucciones);

        return response()->json(['status' => 'Instrucciones actualizadas correctamente.']);
    }

    // public function chatGpt($mensaje, $id, $phone = null, $idMensaje)
    // {
    //     ini_set('max_execution_time', 200); // 300 segundos (5 minutos)

    //     $existeHilo = ChatGpt::find($idMensaje);
	// 	$mensajeAnterior = ChatGpt::where('remitente', $existeHilo->remitente)->get();
		
    //     if ($mensajeAnterior[1]->id_three == null) {
    //         //dd($existeHilo);
    //         $three_id = $this->crearHilo();
    //         //dd($three_id);
    //         $existeHilo->id_three = $three_id['id'];
    //         $existeHilo->save();
    //         $mensajeAnterior[1]->id_three = $three_id['id'];
    //         $mensajeAnterior[1]->save();
    //         //dd($existeHilo);
    //     } else {
    //         $three_id['id'] = $mensajeAnterior[1]->id_three;
    //         $existeHilo->id_three = $mensajeAnterior[1]->id_three;
    //         $existeHilo->save();
    //         $three_id['id'] = $existeHilo->id_three;
    //     }
                    

    //     $hilo = $this->mensajeHilo($three_id['id'], $mensaje);
    //     // Independientemente de si el hilo es nuevo o existente, inicia la ejecución
    //     $ejecuccion = $this->ejecutarHilo($three_id['id']);
    //     // dd($ejecuccion);
    //     $ejecuccionStatus = $this->ejecutarHiloStatus($three_id['id'], $ejecuccion['id']);
    //     // dd($ejecuccionStatus,$ejecuccion);
        
    //     //dd($ejecuccionStatus);
    //     // Inicia un bucle para esperar hasta que el hilo se complete
    //     while (true) {
    //         //$ejecuccion = $this->ejecutarHilo($three_id['id']);

    //         if ($ejecuccionStatus['status'] === 'in_progress') {
    //             // Espera activa antes de verificar el estado nuevamente
    //             sleep(7); // Ajusta este valor según sea necesario

    //             // Verifica el estado del paso actual del hilo
    //             $pasosHilo = $this->ejecutarHiloISteeps($three_id['id'], $ejecuccion['id']);
    //             if ($pasosHilo['data'][0]['status'] === 'completed') {
    //                 // Si el paso se completó, verifica el estado general del hilo
    //                 $ejecuccionStatus = $this->ejecutarHiloStatus($three_id['id'],$ejecuccion['id']);
    //             }
    //         } elseif ($ejecuccionStatus['status'] === 'completed') {
    //             // El hilo ha completado su ejecución, obtiene la respuesta final
    //             $mensajes = $this->listarMensajes($three_id['id']);
    //             // dd($mensajes);
    //             if(count($mensajes['data']) > 0){
    //                 return $mensajes['data'][0]['content'][0]['text']['value'];
    //                 // return $mensajes['data'][0]['content'][0]['text'];
    //                 // return $mensajes;
    //                 // return json_encode($mensajes);
    //             }
    //         } else {
    //             // Maneja otros estados, por ejemplo, errores
    //             //dd($ejecuccionStatus);
    //             //return; // Sale del bucle si se encuentra un estado inesperado
    //         }
    //     }
    // }

    public function chatGpt($mensaje, $id, $phone = null, $idMensaje)
{
    ini_set('max_execution_time', 300); // Extiende el tiempo de ejecución

    $existeHilo = ChatGpt::find($idMensaje);
    $mensajeAnterior = ChatGpt::where('remitente', $existeHilo->remitente)->get();
    
    if ($mensajeAnterior[1]->id_three == null) {
        $three_id = $this->crearHilo();
        $existeHilo->id_three = $three_id['id'];
        $existeHilo->save();
        $mensajeAnterior[1]->id_three = $three_id['id'];
        $mensajeAnterior[1]->save();
    } else {
        $three_id['id'] = $mensajeAnterior[1]->id_three;
        $existeHilo->id_three = $mensajeAnterior[1]->id_three;
        $existeHilo->save();
        $three_id['id'] = $existeHilo->id_three;
    }

    $hilo = $this->mensajeHilo($three_id['id'], $mensaje);
    $ejecuccion = $this->ejecutarHilo($three_id['id']);
    $ejecuccionStatus = $this->ejecutarHiloStatus($three_id['id'], $ejecuccion['id']);
    
    $maxRetries = 5; // Número máximo de reintentos
    $retryCount = 0; // Contador de reintentos
    $timeoutSeconds = 180; // Máximo tiempo en segundos (3 minutos)
    $startTime = time(); // Tiempo de inicio
    
    // Bucle while con límite de tiempo y reintentos
    while (true) {
        if ((time() - $startTime) > $timeoutSeconds) {
            // Enviar una respuesta provisional al cliente
            return [
                "aviso" => false,
                "mensaje" => "Su consulta está siendo procesada, por favor espere unos momentos."
            ];
        }

        if ($retryCount >= $maxRetries) {
            // Enviar una respuesta provisional si se exceden los reintentos
            return [
                "aviso" => false,
                "mensaje" => "Estamos experimentando demoras, su respuesta llegará en breve."
            ];

             //"Estamos experimentando demoras, su respuesta llegará en breve.";
        }

        if ($ejecuccionStatus['status'] === 'in_progress') {
            sleep(7); // Pausa antes de la siguiente verificación
            $pasosHilo = $this->ejecutarHiloISteeps($three_id['id'], $ejecuccion['id']);
            
            if ($pasosHilo['data'][0]['status'] === 'completed') {
                $ejecuccionStatus = $this->ejecutarHiloStatus($three_id['id'], $ejecuccion['id']);
            }
        } elseif ($ejecuccionStatus['status'] === 'completed') {
            $mensajes = $this->listarMensajes($three_id['id']);
            if (count($mensajes['data']) > 0) {
                return $mensajes['data'][0]['content'][0]['text']['value'];
            }
        } else {
            // Incrementa el contador de reintentos
            $retryCount++;
            sleep(3); // Pausa entre reintentos
        }
    }
}

    public function crearHilo()
    {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        $url = 'https://api.openai.com/v1/threads';

        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer '. $token,
            "OpenAI-Beta: assistants=v1"
        );

        // Inicializar cURL y configurar las opciones
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // Ejecutar la solicitud y obtener la respuesta
        $response = curl_exec($curl);
        curl_close($curl);

        // Procesar la respuesta
        if ($response === false) {
            $response_data = json_decode($response, true);
            $error = [
            'status' => 'error',
            'messages' => 'Error al realizar la solicitud: '.$response_data
            ];
            return $error;

        } else {
            $response_data = json_decode($response, true);
            //Storage::disk('local')->put('Respuesta_Peticion_ChatGPT-'.$id.'.txt', $response );
            return $response_data;
        }
    }
    public function recuperarHilo($id_thread)
    {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        $url = 'https://api.openai.com/v1/threads/'.$id_thread;

        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer '. $token,
            "OpenAI-Beta: assistants=v1"
        );

        // Inicializar cURL y configurar las opciones
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
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

        } else {
            $response_data = json_decode($response, true);
            // Storage::disk('local')->put('Respuesta_Peticion_ChatGPT-'.$id.'.txt', $response );
            return $response_data;
        }
    }
    public function ejecutarHilo($id_thread)
    {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        $url = 'https://api.openai.com/v1/threads/'.$id_thread.'/runs';

        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer '. $token,
            "OpenAI-Beta: assistants=v1"
        );

        $body = [
            "response_format" => [
                "type" => "json_object" // Especifica que quieres el formato de respuesta como JSON
            ],
            "assistant_id" => 'asst_tm1HTdOUuMtN20JhP9PDmUb2'
        ];
        // "assistant_id" => 'asst_zYokKNRE98fbjUsKpkSzmU9Y'
        // Inicializar cURL y configurar las opciones
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($body));

        // Ejecutar la solicitud y obtener la respuesta
        $response = curl_exec($curl);
        curl_close($curl);
        // Procesar la respuesta
        if ($response === false) {
            $error = [
            'status' => 'error',
            'messages' => 'Error al realizar la solicitud'
            ];

        } else {
            $response_data = json_decode($response, true);
            return $response_data;
        }
    }
    public function mensajeHilo($id_thread, $pregunta)
    {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        $url = 'https://api.openai.com/v1/threads/'.$id_thread.'/messages';

        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer '. $token,
            "OpenAI-Beta: assistants=v1"
        );
        $body = [
            "role" => "user",
            "content" => $pregunta,
            // "response_format" => [
            //     "type" => "json_object" // Forzar que la respuesta sea en formato JSON
            // ]
        ];

        // Inicializar cURL y configurar las opciones
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($body));


        // Ejecutar la solicitud y obtener la respuesta
        $response = curl_exec($curl);
        curl_close($curl);
        // dd($response);
        // Procesar la respuesta
        if ($response === false) {
            $response_data = json_decode($response, true);
            $error = [
            'status' => 'error',
            'messages' => 'Error al realizar la solicitud: '.$response_data
            ];
            return $error;

        } else {
            $response_data = json_decode($response, true);
            //Storage::disk('local')->put('Respuesta_Peticion_ChatGPT-'.$id.'.txt', $response );
            return $response_data;
        }
    }
    public function ejecutarHiloStatus($id_thread, $id_runs)
    {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        $url = 'https://api.openai.com/v1/threads/'. $id_thread .'/runs/'.$id_runs;

        $headers = array(
            'Authorization: Bearer '. $token,
            "OpenAI-Beta: assistants=v1"
        );

        // Inicializar cURL y configurar las opciones
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, false);
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

        } else {
            $response_data = json_decode($response, true);
            return $response_data;
        }
    }
    public function ejecutarHiloISteeps($id_thread, $id_runs)
    {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        $url = 'https://api.openai.com/v1/threads/'.$id_thread. '/runs/' .$id_runs. '/steps';

        $headers = array(
            'Authorization: Bearer '. $token,
            "OpenAI-Beta: assistants=v1"
        );

        // Inicializar cURL y configurar las opciones
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, false);
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

        } else {
            $response_data = json_decode($response, true);
            return $response_data;
        }
    }
    public function listarMensajes($id_thread)
    {
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        $url = 'https://api.openai.com/v1/threads/'. $id_thread .'/messages';

        $headers = array(
            'Authorization: Bearer '. $token,
            "OpenAI-Beta: assistants=v1"
        );

        // Inicializar cURL y configurar las opciones
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // Ejecutar la solicitud y obtener la respuesta
        $response = curl_exec($curl);
        curl_close($curl);
        // dd($response);

        // Procesar la respuesta
        if( $response === false ){
            $error = [
            'status' => 'error',
            'messages' => 'Error al realizar la solicitud'
            ];

        } else {
            $response_data = json_decode( $response, true );
            return $response_data;
        }
    }
    public function enviarMensajeAsistente($mensaje, $asistenteId)
    {
        try {
            // Enviar el mensaje al asistente con el ID específico
            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo', // Modelo subyacente, aunque el asistente ya está preconfigurado
                'assistant' => $asistenteId, // ID del asistente
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $mensaje,
                    ],
                ],
            ]);

            return $this->parseResponse($response);

        } catch (\Exception $e) {
            // Manejo de errores
            return "Lo siento, ocurrió un error al procesar tu solicitud: " . $e->getMessage();
        }
    }

    public function reservasCobradas(Request $request){
        
        $codigoReserva = $request->input('codigo_reserva');
        $reserva = Reserva::where('codigo_reserva', $codigoReserva)->first();

        $factura = Invoices::where('reserva_id', $reserva->id)->first();

        if ($factura !== null) {
            if ( $factura->invoice_status_id == 6) {
                return response()->json('Reserva ya esta en cobrada',200);
            }
            $factura->invoice_status_id = 6;
            $factura->fecha_cobro = Carbon::now();
            $factura->save();
            return response()->json('Añadido correctamente',200);
        }else {
            
            $data = [
                'budget_id' => null,
                'cliente_id' => $reserva->cliente_id,
                'reserva_id' => $reserva->reserva_id,
                'invoice_status_id' => 1,
                'concepto' => "Apartamento: ". $reserva->apartamento->titulo,
                'description' => null,
                'fecha' => $reserva->fecha_entrada,
                'fecha_cobro' => Carbon::now(),
                'base' => $reserva->precio,
                'iva' => $reserva->precio * 0.10,
                'descuento' => isset($reserva->descuento) ? $reserva->descuento : null,
                'total' => $reserva->precio,
            ];
            
            $crear = Invoices::create($data);
            $referencia = $this->generateBudgetReference($crear);
            $crear->reference = $referencia['reference'];
            $crear->reference_autoincrement_id = $referencia['id'];
            $crear->invoice_status_id = 6;
            $crear->save();
            
            return response()->json('Añadido correctamente',200);

        }
    }


    public function obtenerReservas(Request $request){
        $codigo = $request->codigo_reserva;
        $reserva = Reserva::where('codigo_reserva', $codigo)->first();
        if ($reserva) {
            $data = [
                'codigo_reserva' => $reserva->codigo_reserva,
                'cliente' => $reserva->cliente['nombre'] == null ? $reserva->cliente->alias : $reserva->cliente['nombre'] .' ' . $reserva->cliente['apellido1'],
                'apartamento' => $reserva->apartamento->titulo,
                'edificio' => isset($reserva->apartamento->edificioName->nombre) ? $reserva->apartamento->edificioName->nombre : 'Edificio Hawkins Suite',
                'fecha_entrada' => $reserva->fecha_entrada,
                'clave' => $reserva->apartamento->claves
            ];
        }

        return response()->json($data, 200);


    }
    
  
}

