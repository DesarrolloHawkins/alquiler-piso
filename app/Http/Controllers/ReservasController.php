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
        // Convertimos las Request en la data
        $data = $request->all();
        // Almacenamos la peticion en un archivo
        Storage::disk('local')->put($data['codigo_reserva'].'-' . $hoy .'.txt', json_encode($request->all()));
        // Comprobamos si la reserva ya existe
        $comprobarReserva = Reserva::where('codigo_reserva', $data->codigo_reserva)->first();
        // Si la reserva no existe procedemos al registro
        if ($comprobarReserva == null) {
            $crearCliente = Cliente::create([
                'alias' => $data->alias,
                'idiomas' => $data->idiomas,
                'telefono' => $data->telefono,
            ]);

            // $idBookingApartamento = explode('-', $data['apartamento']);

            $apartamento = Apartamento::where('id_booking', $data['apartamento']);

            $crearReserva = Reserva::create([
                'codigo_reserva' => $data['codigo_reserva'],
                'origen' => 'Booking',
                'fecha_entrada' => $data['fecha_entrada'],
                'fecha_salida' => $data['fecha_salida'],
                'precio' => $data['precio'],
                'apartamento_id' => $apartamento->id,
                'cliente_id' => $crearCliente->id,
                'estado_id' => 1

            ]);
            return response(true);
        } else {
            return response(true);
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
