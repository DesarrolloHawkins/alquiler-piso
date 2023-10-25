<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\Cliente;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReservasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('reservas.index');
        
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
    public function show(string $id)
    {
        //
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

    public function agregarReserva(Request $request){


        $hoy = Carbon::now();
        $cliente;
        $reserva;
        // Convertimos las Request en la data
        $data = $request->all();
        // Almacenamos la peticion en un archivo
        Storage::disk('local')->put($data['codigo_reserva'].'-' . $hoy .'.txt', json_encode($request->all()));
        // Comprobamos si la reserva ya existe
        $comprobarReserva = Reserva::where('codigo_reserva', $data['codigo_reserva'])->first();
        // Si la reserva no existe procedemos al registro
        if ($comprobarReserva == null) {
            $verificarCliente = Cliente::where('telefono',$data['telefono'] )->first();
            if ($verificarCliente == null) {
				if (preg_match('/^(.*?)\n(\d+)\s*adulto(?:s)?/', $data['alias'], $matches)) {
					$nombre = trim($matches[1]);
					$num_adultos = $matches[2];


					$crearCliente = Cliente::create([
						'alias' => $nombre,
						'idiomas' => $data['idiomas'],
						'telefono' => $data['telefono'],
						'identificador' => $data['email'],
					]);
					$cliente = $crearCliente;
					
				}else {
					$crearCliente = Cliente::create([
						'alias' => $data['alias'],
						'idiomas' => $data['idiomas'],
						'telefono' => $data['telefono'],
						'identificador' => $data['email'],
					]);
					$cliente = $crearCliente;
				}
				
				
            }else {
                $cliente = $verificarCliente;
            }
            $locale = 'es'; // Establece el idioma a español para reconocer 'jue' como 'jueves' y 'sep' como 'septiembre'

			Carbon::setLocale($locale);
           	$fecha_entrada = Carbon::createFromFormat('Y-m-d', $data['fecha_entrada']);
			$fecha_salida = Carbon::createFromFormat('Y-m-d', $data['fecha_salida']);



			//return $fecha_salida[1];

            //$apartamento = Apartamento::where('id_booking', $data['codigo_reserva'])->first();

            $verificarReserva = Reserva::where('codigo_reserva',$data['codigo_reserva'] )->first();
            if ($data['origen'] == 'Booking') {
                $apartamento = Apartamento::where('id_booking', $data['apartamento'])->first();
                # code...
            }else if($data['origen'] == 'Airbnb'){
                switch ($data['apartamento']) {
                    case 'Ático nuevo en pleno centro. Plaza alta.':
                        $apartamento = ['id'=> 1];
                        break;
                    case 'Apartamento interior Algeciras 2a':
                        $apartamento = ['id'=> 2];
                        break;
                    case 'Apartamento en el absoluto centro 2b':
                        $apartamento = ['id'=> 3];
                        break;
                    case 'Apartamento interior centro en Algeciras 1º A':
                        $apartamento = ['id'=> 4];
                        break;
                    case 'Apartamento en absoluto centro. 1ºB':
                        $apartamento = ['id'=> 5];
                        break;
                    case 'Apartamento en absoluto centro. ba':
                        $apartamento = ['id'=> 6];
                        break;
                    case 'ÁApartamento BB Centro Algeciras':
                        $apartamento = ['id'=> 7];
                        break;

                    
                    default:
                        $apartamento = ['id'=> null];
                        break;
                }
            }

            if ($verificarReserva == null) {
                $crearReserva = Reserva::create([
                    'codigo_reserva' => $data['codigo_reserva'],
                    'origen' => $data['origen'],
                    'fecha_entrada' =>  $fecha_entrada,
                    'fecha_salida' => $fecha_salida,
                    'precio' => $data['precio'],
                    'apartamento_id' => $apartamento->id,
                    'cliente_id' => $cliente->id,
                    'estado_id' => 1
    
                ]);
                $reserva = $crearReserva;

            } else {
                $reserva = $verificarReserva;

            }
            
            return response('Registrado');
        } else {
            return response('Ya existe esa reserva');
        }

    }

    public function verificarReserva(Request $request){
		return 'ok';
        $data = $request->all();

        $reserva = Reserva::where('codigo_reserva', $data['codigo_reserva'])->first();

        if ($reserva != null) {
            return response(true);
        }
        
        return response(false);
    }
	
	public function cancelarAirBnb($reserva){
		$reserva = Reserva::where('codigo_reserva', $reserva)->first();
		$reserva->estado_id = 4;
		$reserva->save();
		
	}
}
