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
            $verificarCliente = Cliente::where('identificador',$data['email'] )->first();
            if ($verificarCliente == null) {
                $crearCliente = Cliente::create([
                    'alias' => $data['alias'],
                    'idiomas' => $data['idiomas'],
                    'telefono' => $data['telefono'],
                    'identificador' => $data['email'],
                ]);
                $cliente = $crearCliente;
            }else {
                $cliente = $verificarCliente;
            }
            $locale = 'es'; // Establece el idioma a español para reconocer 'jue' como 'jueves' y 'sep' como 'septiembre'

			Carbon::setLocale($locale);
            $fecha_entrada = explode(',', $data['fecha_entrada']);
            $fecha_salida = explode(',', $data['fecha_salida']);
			//return $fecha_salida[1];

            $apartamento = Apartamento::where('id_booking', $data['apartamento'])->first();

            $verificarReserva = Reserva::where('codigo_reserva',$data['codigo_reserva'] )->first();
			
            if ($verificarReserva == null) {
                $crearReserva = Reserva::create([
                    'codigo_reserva' => $data['codigo_reserva'],
                    'origen' => 'Booking',
                    'fecha_entrada' => Carbon::createFromFormat(' d M Y', $fecha_entrada[1]),
                    'fecha_salida' => Carbon::createFromFormat(' d M Y', $fecha_salida[1]),
                    'precio' => $data['precio'],
                    'apartamento_id' => $apartamento->id,
                    'cliente_id' => $cliente->id,
                    'estado_id' => 1
    
                ]);
                $reserva = $crearReserva;

            } else {
                $reserva = $verificarReserva;

            }
            
            return response(true);
        } else {
            return response('Ya existe esa reserva');
        }

    }

    public function verificarReserva(Request $request){
        $data = $request->all();

        $reserva = Reserva::where('codigo_reserva', $data['codigo_reserva'])->first();

        if ($reserva != null) {
            return response(true);
        }
        
        return response(false);
    }
}
