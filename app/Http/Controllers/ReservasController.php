<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\Cliente;
use App\Models\Huesped;
use App\Models\MensajeAuto;
use App\Models\Photo;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReservasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $orderBy = $request->get('order_by', 'fecha_entrada');
    $direction = $request->get('direction', 'asc');
    $perPage = $request->get('perPage', 10); // Valor por defecto de 10 si no se especifica
    $searchTerm = $request->get('search', '');

    $fechaEntrada = $request->get('fecha_entrada', '');
    $fechaSalida = $request->get('fecha_salida', '');

    $perPage == '' ? $perPage = 10 : $perPage;
    $direction == '' ? $direction = 'asc' : $direction;
    $orderBy == '' ? $orderBy = 'fecha_entrada' : $orderBy;

    $query = Reserva::with('cliente'); // Asegúrate de que 'cliente' sea el nombre de la relación en el modelo Reserva

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

    // Filtrar por fechas de entrada y salida
    if (!empty($fechaEntrada)) {
        $query->whereDate('fecha_entrada', '>=', $fechaEntrada);
    } else {
        $query->whereDate('fecha_entrada', '>=', now()->format('Y-m-d'));
    }

    if (!empty($fechaSalida)) {
        $query->whereDate('fecha_salida', '<=', $fechaSalida);
    } else {
        $query->whereDate('fecha_salida', '<=', now()->addDay()->format('Y-m-d'));
    }

    // Utiliza el valor de $perPage en la función paginate()
    $reservas = $query->orderBy($orderBy, $direction)->paginate($perPage)->appends([
        'order_by' => $orderBy,
        'direction' => $direction,
        'search' => $searchTerm,
        'perPage' => $perPage, // Asegúrate de adjuntar 'perPage' para mantenerlo durante la paginación
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
            'origen' => 'nullable|string',
            'fecha_entrada' => 'required|date',
            'fecha_salida' => 'required|date',
            'precio' => 'nullable|string',
            'verificado' => 'nullable|integer',
            'dni_entregado' => 'nullable|integer',
            'enviado_webpol' => 'nullable|integer',
            'codigo_reserva' => 'nullable|string',
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
        return view('reservas.show', compact('reserva', 'mensajes', 'photos','huespedes'));

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
    public function update(Request $request, string $id)
    {
        //
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
}

