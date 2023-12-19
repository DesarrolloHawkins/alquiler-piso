<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\Cliente;
use App\Models\MensajeAuto;
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

        $reservas = Reserva::orderBy($orderBy, $direction)->paginate(10);
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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Reserva $reserva)
    {
        $mensajes = MensajeAuto::where('reserva_id', $reserva->id)->get();
        return view('reservas.show', compact('reserva', 'mensajes'));
        
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
						'identificador' => $data['email'],
					]);
					$cliente = $crearCliente;
					
				}else {
                    // Si existe creamos al cliente
					$crearCliente = Cliente::create([
						'alias' => $data['alias'],
						'idiomas' => $data['idiomas'],
						'telefono' => $data['telefono'],
						'identificador' => $data['email'],
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
                }else if($data['origen'] == 'Airbnb'){
                    // Si es de Airbnb lo obtenemos por el nombre del apartamento
                    switch ($data['apartamento']) {
                        case 'Ático nuevo en pleno centro. Plaza alta.':
                            $apartamento = (object) ['id'=> 1];
                            break;
                        case 'Apartamento interior Algeciras 2a':
                            $apartamento = (object) ['id'=> 2];
                            break;
                        case 'Apartamento en el absoluto centro 2b':
                            $apartamento = (object) ['id'=> 3];
                            break;
                        case 'Apartamento interior centro en Algeciras 1º A':
                            $apartamento = (object) ['id'=> 4];
                            break;
                        case 'Apartamento en absoluto centro. 1ºB':
                            $apartamento = (object) ['id'=> 5];
                            break;
                        case 'Apartamento en absoluto centro. ba':
                            $apartamento = (object) ['id'=> 6];
                            break;
                        case 'ÁApartamento BB Centro Algeciras':
                            $apartamento = (object) ['id'=> 7];
                            break;
    
                        
                        default:
                            $apartamento = (object) ['id'=> null];
                            break;
                    }
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

