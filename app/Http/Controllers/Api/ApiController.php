<?php

namespace App\Http\Controllers\Api;

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
use App\Http\Controllers\Controller; // Asegúrate de que esta línea esté presente
use App\Models\Reparaciones;

class ApiController extends Controller
{
    /**
     * Obtener las reservas para hoy
     */
    // public function obtenerReservasHoy(Request $request)
    // {
    //     // Obtener la fecha y la hora actual
    //     $hoy = Carbon::now();
    //     $horaLimite = Carbon::createFromTime(14, 0, 0); // Hora límite: 14:00

    //     // Filtrar las reservas cuya fecha de entrada sea hoy
    //     $reservas = Reserva::whereDate('fecha_entrada', $hoy->toDateString())
    //         ->where(function ($query) use ($hoy, $horaLimite) {
    //             // Excluir las reservas cuya fecha de salida sea hoy y la hora sea mayor a las 14:00
    //             $query->whereDate('fecha_salida', '>', $hoy->toDateString())
    //                 ->orWhere(function ($query) use ($hoy, $horaLimite) {
    //                     $query->whereDate('fecha_salida', $hoy->toDateString())
    //                         ->whereTime('fecha_salida', '<', $horaLimite->toTimeString());
    //                 });
    //         })
    //         ->get();

    //     // Verificar si hay reservas y formatear los datos para la respuesta
    //     if ($reservas->isNotEmpty()) {
    //         $data = $reservas->map(function ($reserva) {
    //             return [
    //                 'codigo_reserva' => $reserva->codigo_reserva,
    //                 'cliente' => $reserva->cliente['nombre'] == null ? $reserva->cliente->alias : $reserva->cliente['nombre'] . ' ' . $reserva->cliente['apellido1'],
    //                 'apartamento' => $reserva->apartamento->titulo,
    //                 'edificio' => isset($reserva->apartamento->edificioName->nombre) ? $reserva->apartamento->edificioName->nombre : 'Edificio Hawkins Suite',
    //                 'fecha_entrada' => $reserva->fecha_entrada,
    //                 'clave' => $reserva->apartamento->claves
    //             ];
    //         });

    //         return response()->json($data, 200);
    //     } else {
    //         return response()->json('Error, no se encontraron reservas para hoy', 400);
    //     }
    // }

    public function obtenerReservasHoy(Request $request)
    {
        // Obtener la fecha y hora actual
        $hoy = Carbon::now();

        // Filtrar las reservas activas
        $reservas = Reserva::where('fecha_entrada', '<=', $hoy) // La reserva ya inició
            ->where(function ($query) use ($hoy) {
                $query->where('fecha_salida', '>', $hoy) // Aún no ha salido
                    ->orWhere(function ($query) use ($hoy) {
                        $query->whereDate('fecha_salida', $hoy->toDateString())
                              ->whereTime('fecha_salida', '>', $hoy->toTimeString()); // Salida más tarde hoy
                    });
            })
            ->get();

        // Verificar si hay reservas y formatear los datos para la respuesta
        if ($reservas->isNotEmpty()) {
            $data = $reservas->map(function ($reserva) {
                return [
                    'codigo_reserva' => $reserva->codigo_reserva,
                    'cliente' => $reserva->cliente['nombre'] == null ? $reserva->cliente->alias : $reserva->cliente['nombre'] . ' ' . $reserva->cliente['apellido1'],
                    'apartamento' => $reserva->apartamento->titulo,
                    'edificio' => isset($reserva->apartamento->edificioName->nombre) ? $reserva->apartamento->edificioName->nombre : 'Edificio Hawkins Suite',
                    'fecha_entrada' => $reserva->fecha_entrada,
                    'fecha_salida' => $reserva->fecha_salida,
                    'clave' => $reserva->apartamento->claves
                ];
            });

            return response()->json($data, 200);
        } else {
            return response()->json('No hay reservas activas', 400);
        }
    }


    /**
     * Obtener los apartamentos
     */
    public function obtenerApartamentos()
    {
        $apartamentos = Apartamento::all();
        return response()->json($apartamentos);
    }

    /**
     * Obtener los apartamentos disponibles
     */
    public function obtenerApartamentosDisponibles(Request $request)
    {
        // Obtener la fecha y la hora actual
        $hoy = Carbon::now();

        // Obtener los IDs de los apartamentos que están reservados hoy
        $reservasHoy = Reserva::whereDate('fecha_entrada', '<=', $hoy->toDateString())
            ->whereDate('fecha_salida', '>=', $hoy->toDateString())
            ->pluck('apartamento_id');

        // Obtener los apartamentos que no están en las reservas de hoy
        $apartamentosDisponibles = Apartamento::whereNotIn('id', $reservasHoy)->where('id_booking', '!=', 1)->get();

        // Formatear los datos para la respuesta
        $data = $apartamentosDisponibles->map(function ($apartamento) {
            return [
                'id' => $apartamento->id,
                'titulo' => $apartamento->titulo,
                'descripcion' => $apartamento->descripcion, // Asegúrate de que este campo existe en tu modelo
                'edificio' => $apartamento->edificioName->nombre ?? 'Edificio Hawkins Suite', // Agregar el nombre del edificio
                'claves' => $apartamento->claves,
                // Agrega más campos según lo necesites
            ];
        });

        return response()->json($data, 200);
    }


   /**
     * Averias tecnico
     */
    public function averiasTecnico(Request $request)
    {
        $manitas = Reparaciones::all();

        $phone = $request->phone;

        // Guardar la solicitud en un archivo .txt
        $data = "Averias tecnico: " . json_encode($request->all()) . "\n";
        file_put_contents(storage_path('app/averias_tecnico.txt'), $data, FILE_APPEND);

        return response()->json('Averias tecnico enviada', 200);
    }

    /**
     * Equipo de limpieza
     */
    public function equipoLimpieza(Request $request)
    {
        $phone = $request->phone;

        // Guardar la solicitud en un archivo .txt
        $data = "Equipo de limpieza: " . json_encode($request->all()) . "\n";
        file_put_contents(storage_path('app/equipo_limpieza.txt'), $data, FILE_APPEND);

        return response()->json('Equipo de limpieza enviada', 200);
    }

    public function mensajesPlantillaAverias($nombreManita, $apartamento, $edificio, $mensaje, $telefono, $telefonoManitas, $idioma = 'es'){
        $token = env('TOKEN_WHATSAPP', 'valorPorDefecto');

        $mensajePersonalizado = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $telefonoManitas,
            "type" => "template",
            "template" => [
                "name" => 'reparaciones',
                "language" => ["code" => $idioma],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            ["type" => "text", "text" => $nombreManita],
                            ["type" => "text", "text" => $apartamento],
                            ["type" => "text", "text" => $edificio],
                            ["type" => "text", "text" => $mensaje],
                            ["type" => "text", "text" => $telefono],
                        ],
                    ],
                ],
            ],
        ];

        $urlMensajes = 'https://graph.facebook.com/v16.0/102360642838173/messages';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $urlMensajes,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($mensajePersonalizado),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$token
            ),

        ));

        $response = curl_exec($curl);
        curl_close($curl);
        // $responseJson = json_decode($response);
        return $response;

    }


    public function mensajesPlantillaLimpiadora($apartamento, $edificio, $mensaje, $telefono, $telefonoLimpiadora, $idioma = 'es'){
        $token = env('TOKEN_WHATSAPP', 'valorPorDefecto');

        $mensajePersonalizado = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $telefonoLimpiadora,
            "type" => "template",
            "template" => [
                "name" => '',
                "language" => ["code" => $idioma],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            ["type" => "text", "text" => $apartamento],
                            ["type" => "text", "text" => $edificio],
                            ["type" => "text", "text" => $mensaje],
                            ["type" => "text", "text" => $telefono],
                        ],
                    ],
                ],
            ],
        ];

        $urlMensajes = 'https://graph.facebook.com/v16.0/102360642838173/messages';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $urlMensajes,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($mensajePersonalizado),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$token
            ),

        ));

        $response = curl_exec($curl);
        curl_close($curl);
        // $responseJson = json_decode($response);
        return $response;

    }


}

