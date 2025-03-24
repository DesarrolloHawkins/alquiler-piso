<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\ARIUpdate;
use App\Models\RatePlan;
use App\Models\Reserva;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ARIController extends Controller
{
    private $apiUrl;
    private $apiToken;
    public function __construct()
    {
        $this->apiUrl = env('CHANNEX_URL');
        $this->apiToken = env('CHANNEX_TOKEN');
    }
    public function index()
    {
        $properties = Apartamento::where('id_channex','!=', null)->get(); // Obtenemos las propiedades
        $roomTypes = RoomType::all();    // Obtenemos los tipos de habitación
        $ratePlans = RatePlan::all();    // Obtenemos los planes de tarifas

        return view('admin.ari.index', compact('properties', 'roomTypes', 'ratePlans'));
    }



    // public function update(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'updates' => 'required|array',
    //         'updates.*.property_id' => 'required|string',
    //         'updates.*.room_type_id' => 'required|string',
    //         'updates.*.rate_plan_id' => 'required|string',
    //         'updates.*.date_from' => 'required|date',
    //         'updates.*.date_to' => 'nullable|date',
    //         'updates.*.update_type' => 'nullable|string',
    //         'updates.*.value' => 'nullable|string',
    //         'updates.*.min_stay' => 'nullable|integer',
    //         'updates.*.max_stay' => 'nullable|integer',
    //     ]);

    //     //dd( $validatedData);
    //     $urlVariable = 'restrictions';
    //     $updates = [];
    //     foreach ($validatedData['updates'] as $update) {
    //         // $details = [
    //         //     'property_id' => $update['property_id'],
    //         //     'rate_plan_id' => $update['rate_plan_id'],
    //         //     'room_type_id' => $update['room_type_id'],
    //         //     'date' => $update['date_from'],
    //         //     'date_to' => $update['date_to'] ?? $update['date_from'],
    //         //     'rate' => $update['rate'],
    //         // ];
    //         $details = [
    //             'property_id' => $update['property_id'],
    //             'rate_plan_id' => $update['rate_plan_id'],
    //             'room_type_id' => $update['room_type_id'],
    //             // 'rate' => $update['rate'],
    //         ];

    //         // Manejo dinámico de la clave 'date' o 'date_from' según el valor de 'date_to'
    //         if (empty($update['date_to'])) {
    //             $details['date'] = $update['date_from'];
    //         } else {
    //             $details['date_from'] = $update['date_from'];
    //             $details['date_to'] = $update['date_to'];
    //         }

    //         // Manejo de tipos específicos
    //         switch ($update['update_type']) {
    //             case 'rate':
    //                 $details['rate'] = (float) $update['value']; // Convertir a número flotante para Channex
    //                 break;

    //             case 'availability':
    //                 $details['availability'] = 1; // Convertir a entero para Channex
    //                 $urlVariable = 'availability';
    //                 break;

    //             case 'min_stay':
    //                 $details['min_stay_through'] = (int) $update['min_stay'];
    //                 break;

    //             case 'stop_sell':
    //                 $details['stop_sell'] = filter_var($update['value'], FILTER_VALIDATE_BOOLEAN);
    //                 break;

    //             case 'restrictions':
    //                 $details['closed_to_arrival'] = isset($update['closed_to_arrival']) ? true : false;
    //                 $details['closed_to_departure'] = isset($update['closed_to_departure']) ? true : false;
    //                 $details['min_stay_through'] = $update['min_stay'] ?? 0;
    //                 $details['max_stay'] = $update['max_stay'] ?? 0;
    //                 break;
    //         }

    //         $updates[] = $details;
    //     }
    //     // dd($update['update_type']);
    //     // Petición a la API
    //     $response = Http::withHeaders([
    //         'user-api-key' => $this->apiToken,
    //     ])->post("{$this->apiUrl}/".$urlVariable , ['values' => $updates]);
    //     //dd($response->json());
    //     if ($response->successful()) {
    //         return $response->json();
    //         // return redirect()->route('ari.index')->with('success', 'Actualización realizada con éxito.'. $response->json());
    //     }

    //     return redirect()->back()->withErrors(['error' => 'Error: ' . $response->body()])->withInput();
    // }

//     public function update(Request $request)
// {
//     $validatedData = $request->validate([
//         'updates' => 'required|array',
//         'updates.*.property_id' => 'required|string',
//         'updates.*.room_type_id' => 'required|string',
//         'updates.*.rate_plan_id' => 'required|string',
//         'updates.*.date_from' => 'required|date',
//         'updates.*.date_to' => 'nullable|date',
//         'updates.*.update_type' => 'nullable|string',
//         'updates.*.value' => 'nullable|string',
//         'updates.*.min_stay' => 'nullable|integer',
//         'updates.*.max_stay' => 'nullable|integer',
//         'updates.*.exclude_weekends' => 'required|boolean',
//     ]);

//     $urlVariable = 'restrictions';
//     $updates = [];

//     foreach ($validatedData['updates'] as $update) {
//         $startDate = Carbon::parse($update['date_from']);
//         $endDate = empty($update['date_to']) ? $startDate : Carbon::parse($update['date_to']);
//         $excludeWeekends = isset($update['exclude_weekends']) && $update['exclude_weekends'];

//         if ($excludeWeekends) {
//             // Generar rangos semanales excluyendo fines de semana
//             $currentStart = $startDate->copy()->startOfWeek(Carbon::MONDAY); // Inicia el rango en lunes
//             while ($currentStart->lte($endDate)) {
//                 $currentEnd = $currentStart->copy()->endOfWeek(Carbon::FRIDAY); // Termina el rango en viernes
//                 if ($currentEnd->gt($endDate)) {
//                     $currentEnd = $endDate; // Ajustar el rango final si excede la fecha final
//                 }

//                 // Crear un ítem para este rango
//                 $details = [
//                     'property_id' => $update['property_id'],
//                     'rate_plan_id' => $update['rate_plan_id'],
//                     'room_type_id' => $update['room_type_id'],
//                     'date_from' => $currentStart->toDateString(),
//                     'date_to' => $currentEnd->toDateString(),
//                 ];

//                 switch ($update['update_type']) {
//                     case 'rate':
//                         $details['rate'] = (float)$update['value'];
//                         break;
//                     case 'availability':
//                         $details['availability'] = 1;
//                         $urlVariable = 'availability';
//                         break;
//                     case 'min_stay':
//                         $details['min_stay_through'] = (int)$update['min_stay'];
//                         break;
//                     case 'stop_sell':
//                         $details['stop_sell'] = filter_var($update['value'], FILTER_VALIDATE_BOOLEAN);
//                         break;
//                     case 'restrictions':
//                         $details['closed_to_arrival'] = isset($update['closed_to_arrival']) ? true : false;
//                         $details['closed_to_departure'] = isset($update['closed_to_departure']) ? true : false;
//                         $details['min_stay_through'] = $update['min_stay'] ?? 0;
//                         $details['max_stay'] = $update['max_stay'] ?? 0;
//                         break;
//                 }

//                 $updates[] = $details;

//                 // Mover al siguiente lunes
//                 $currentStart->addWeek();
//             }
//         } else {
//             // Si no excluye fines de semana, procesar todo el rango
//             $details = [
//                 'property_id' => $update['property_id'],
//                 'rate_plan_id' => $update['rate_plan_id'],
//                 'room_type_id' => $update['room_type_id'],
//                 'date_from' => $startDate->toDateString(),
//                 'date_to' => $endDate->toDateString(),
//             ];

//             // Manejar los tipos específicos
//             switch ($update['update_type']) {
//                 case 'rate':
//                     $details['rate'] = (float)$update['value'];
//                     break;
//                 case 'availability':
//                     $details['availability'] = 1;
//                     $urlVariable = 'availability';
//                     break;
//                 case 'min_stay':
//                     $details['min_stay_through'] = (int)$update['min_stay'];
//                     break;
//                 case 'stop_sell':
//                     $details['stop_sell'] = filter_var($update['value'], FILTER_VALIDATE_BOOLEAN);
//                     break;
//                 case 'restrictions':
//                     $details['closed_to_arrival'] = isset($update['closed_to_arrival']) ? true : false;
//                     $details['closed_to_departure'] = isset($update['closed_to_departure']) ? true : false;
//                     $details['min_stay_through'] = $update['min_stay'] ?? 0;
//                     $details['max_stay'] = $update['max_stay'] ?? 0;
//                     break;
//             }

//             $updates[] = $details;
//         }
//     }
//     // Petición a la API
//     $response = Http::withHeaders([
//         'user-api-key' => $this->apiToken,
//         ])->post("{$this->apiUrl}/" . $urlVariable, ['values' => $updates]);

//         if ($response->successful()) {
//         return redirect()->route('ari.index')->with('success', 'Actualización realizada con éxito.');
//     }

//     return redirect()->back()->withErrors(['error' => 'Error: ' . $response->body()])->withInput();
// }


    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'updates' => 'required|array',
            'updates.*.property_id' => 'required|string',
            'updates.*.room_type_id' => 'required|string',
            'updates.*.rate_plan_id' => 'nullable|string',
            'updates.*.date_from' => 'required|date',
            'updates.*.date_to' => 'nullable|date',
            'updates.*.update_type' => 'nullable|string',
            'updates.*.value' => 'nullable|string',
            'updates.*.rate' => 'nullable|string',
            'updates.*.min_stay' => 'nullable|integer',
            'updates.*.max_stay' => 'nullable|integer',
            'updates.*.min_stay_through' => 'nullable|integer',
            'updates.*.min_stay_arrival' => 'nullable|integer',
            'updates.*.exclude_weekends' => 'nullable|boolean',
            'updates.*.closed_to_arrival' => 'nullable|boolean',
            'updates.*.closed_to_departure' => 'nullable|boolean',
            'updates.*.stop_sell' => 'nullable|boolean',
            'updates.*.only_weekends' => 'nullable|boolean', // Nuevo check para fines de semana
            'updates.*.weekend_days' => 'nullable|string|in:both,saturday,sunday',
        ]);
        // dd($validatedData);

        $updates = [];

        foreach ($validatedData['updates'] as $update) {
            $startDate = Carbon::parse($update['date_from']);
            $endDate = empty($update['date_to']) ? $startDate : Carbon::parse($update['date_to']);
            $excludeWeekends = isset($update['exclude_weekends']) && $update['exclude_weekends'];
            $onlyWeekends = isset($update['only_weekends']) && $update['only_weekends']; // Nuevo check
            $weekendDays = $update['weekend_days'] ?? null; // "both", "saturday", "sunday"

            if ($onlyWeekends) {
                // Generar ítems solo para fines de semana
                $currentStart = $startDate->copy();
                while ($currentStart->lte($endDate)) {
                    if (
                        ($weekendDays === 'both' && ($currentStart->isSaturday() || $currentStart->isSunday())) ||
                        ($weekendDays === 'saturday' && $currentStart->isSaturday()) ||
                        ($weekendDays === 'sunday' && $currentStart->isSunday())
                    ) {
                        // Crear un ítem por cada día de fin de semana seleccionado
                        $updates[] = $this->createItem($update, $currentStart, $currentStart);
                    }
                    $currentStart->addDay();
                }
            } elseif ($excludeWeekends) {
                // Generar rangos semanales excluyendo sábados y domingos
                $currentStart = $startDate->copy();
                while ($currentStart->lte($endDate)) {
                    $currentEnd = $currentStart->copy()->endOfWeek(Carbon::FRIDAY); // Termina el rango en viernes
                    if ($currentEnd->gt($endDate)) {
                        $currentEnd = $endDate; // Ajustar el rango final si excede la fecha final
                    }

                    // Crear un ítem para este rango
                    $updates[] = $this->createItem($update, $currentStart, $currentEnd);

                    // Mover al siguiente lunes
                    $currentStart = $currentStart->addWeek()->startOfWeek(Carbon::MONDAY);
                }
            } else {
                // Si no se excluyen fines de semana ni se seleccionan solo fines de semana, procesar todo el rango
                $updates[] = $this->createItem($update, $startDate, $endDate);
            }
        }
        //dd($updates);
        if ($update['update_type'] == 'availability') {
            $urlVariable = 'availability';

        }else {
            $urlVariable = 'restrictions';
        }
        //  dd($updates);
        // Petición a la API
        $response = Http::withHeaders([
            'user-api-key' => $this->apiToken,
        ])->post("{$this->apiUrl}/" . $urlVariable, ['values' => $updates]);

        if ($response->successful()) {
            return [$response->json(), $updates];
            //return redirect()->route('ari.index')->with('success', 'Actualización realizada con éxito.');
        }

        return redirect()->back()->withErrors(['error' => 'Error: ' . $response->body()])->withInput();
    }

    private function createItem($update, $startDate, $endDate)
    {
        if($update['update_type'] == 'availability') {
            $details = [
                'property_id' => $update['property_id'],
                'room_type_id' => $update['room_type_id'] ?? null,
            ];

        }else {
            $details = [
                'property_id' => $update['property_id'],
                'rate_plan_id' => $update['rate_plan_id'] ?? null,
            ];
        }


        // if (isset($update['rate_plan_id']) && !empty($update['rate_plan_id'])) {
        //     $details['rate_plan_id'] = $update['rate_plan_id'];
        // }

        if ($startDate->equalTo($endDate)) {
            $details['date'] = $startDate->toDateString();
        } else {
            $details['date_from'] = $startDate->toDateString();
            $details['date_to'] = $endDate->toDateString();
        }

        switch ($update['update_type']) {
            case 'availability':
                $details['availability'] = (int)$update['value'];
                break;

            case 'restrictions':
                if (isset($update['rate']) && $update['rate'] > 0) {
                    $details['rate'] = $update['rate'] /1; // Convierte 5000 a 50.00
                }

                if (isset($update['min_stay']) && $update['min_stay'] > 0) {
                    $details['min_stay'] = (int)$update['min_stay'];
                }

                if (isset($update['max_stay']) && $update['max_stay'] > 0) {
                    $details['max_stay'] = (int)$update['max_stay'];
                }

                if (isset($update['min_stay_through']) && $update['min_stay_through'] > 0) {
                    $details['min_stay_through'] = (int)$update['min_stay_through'];
                }

                if (isset($update['min_stay_arrival']) && $update['min_stay_arrival'] > 0) {
                    $details['min_stay_arrival'] = (int)$update['min_stay_arrival'];
                }

                if (isset($update['stop_sell'])) {
                    $details['stop_sell'] = $update['stop_sell'] === "1" ? true : ($update['stop_sell'] === "0" ? false : null);
                }

                if (isset($update['closed_to_arrival'])) {
                    $details['closed_to_arrival'] = $update['closed_to_arrival'] === "1";
                }

                if (isset($update['closed_to_departure'])) {
                    $details['closed_to_departure'] = $update['closed_to_departure'] === "1";
                }

                break;
        }

        return $details;
    }

public function fullSync()
{
    // Obtener la fecha actual
    $startDate = Carbon::now();
    // Calcular la fecha hasta 500 días en el futuro
    $endDate = $startDate->copy()->addDays(500);

    // Inicializar el array para almacenar todas las actualizaciones
    $updates = [];

    // Obtener todos los apartamentos con un id_channex no nulo
    $apartamentos = Apartamento::whereNotNull('id_channex')->with('roomTypes')->get();

    // Iterar sobre cada apartamento
    foreach ($apartamentos as $apartamento) {
        // Iterar sobre cada tipo de habitación del apartamento
        foreach ($apartamento->roomTypes as $roomType) {
            // Obtener todas las reservas de este roomType en el rango de fechas
            $reservas = Reserva::where('apartamento_id', $apartamento->id)
                ->where('room_type_id', $roomType->id)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('fecha_entrada', [$startDate, $endDate])
                          ->orWhereBetween('fecha_salida', [$startDate, $endDate]);
                })
                ->get();
                // dd($roomType->id);

            // Iterar sobre cada día en el rango de fechas
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                // Verificar si hay una reserva activa en esa fecha
                $hasReservation = $reservas->contains(function ($reserva) use ($date) {
                    return $date->gte(Carbon::parse($reserva->fecha_entrada)) &&
                           $date->lte(Carbon::parse($reserva->fecha_salida));
                });

                //dd($hasReservation);

                // Establecer disponibilidad (0 si hay reserva, 1 si no)
                $availability = $hasReservation ? 0 : 1;

                // Agregar la actualización al array
                $updates[] = [
                    'property_id' => $apartamento->id_channex,
                    'room_type_id' => $roomType->id_channex,
                    'date' => $date->toDateString(),
                    'availability' => $availability,
                ];
                // if ($roomType->id == 3 ) {
                //     dd(end($updates), $hasReservation);

                // }
            }
        }
    }
    // dd($updates);
    // Enviar la petición completa en una sola solicitud
    $response = Http::withHeaders([
        'user-api-key' => $this->apiToken,
    ])->post("{$this->apiUrl}/availability", [
        'values' => $updates,
    ]);

    if ($response->successful()) {
        return response()->json([
            'success' => true,
            'message' => 'Sincronización completada con éxito.',
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Error al sincronizar disponibilidad.',
    ], 500);
    // if ($response->successful()) {
    //     session()->flash('swal_success', 'Sincronización completada con éxito.');
    // } else {
    //     session()->flash('swal_error', 'Error al sincronizar disponibilidad.');
    // }

    // // Manejar la respuesta de la API
    // if ($response->successful()) {
    //     return redirect()->route('reservas.index')->with('success', 'Reserva creada con éxito');

    //     // return response()->json([
    //     //     'success' => true,
    //     //     'message' => 'Sincronización completa realizada con éxito.',
    //     //     'response' => $response->json(),
    //     //     'envio' => $updates,
    //     // ]);
    // }

    // return response()->json([
    //     'success' => false,
    //     'message' => 'Error al sincronizar disponibilidad.',
    //     'error' => $response->body(),
    // ]);
}


//     public function fullSync()
// {
    //     $updates = [];
//     // 1. Obtener apartamentos con id_channex no nulo
//     $apartamentos = Apartamento::whereNotNull('id_channex')->get();

//     // 2. Definir el rango de fechas (500 días desde hoy)
//     $startDate = Carbon::now();
//     $endDate = $startDate->copy()->addDays(500);

//     foreach ($apartamentos as $apartamento) {
//         $roomTypes = $apartamento->roomTypes()->whereNotNull('id_channex')->get();

//         // Verificar que el apartamento tenga roomTypes asociados
//         foreach ($roomTypes as $roomType) {
//             $currentDate = $startDate->copy();

//             while ($currentDate->lte($endDate)) {
//                 // 3. Verificar si hay una reserva para el apartamento y el room type en esa fecha
//                 $hasReservation = Reserva::where('apartamento_id', $apartamento->id)
//                     ->where('room_type_id', $roomType->id)
//                     ->whereDate('fecha_entrada', '<=', $currentDate->toDateString())
//                     ->whereDate('fecha_salida', '>=', $currentDate->toDateString())
//                     ->exists();

//                 // 4. Establecer la disponibilidad
//                 $availability = $hasReservation ? 0 : 1;

//                 // 5. Agregar la actualización
//                 $updates[] = [
//                     'property_id' => $apartamento->id_channex,
//                     'room_type_id' => $roomType->id_channex, // ID correcto del roomType
//                     'date' => $currentDate->toDateString(),
//                     'availability' => $availability,
//                 ];

//                 $currentDate->addDay();
//             }
//         }
//     }

//     // 6. Enviar las actualizaciones en lotes de 1000
//     $chunks = array_chunk($updates, 1000);
//     $results = [];

//     foreach ($chunks as $chunk) {
//         $response = Http::withHeaders([
//             'user-api-key' => $this->apiToken,
//         ])->post("{$this->apiUrl}/availability", ['values' => $chunk]);

//         if ($response->successful()) {
//             $results[] = $response->json();
//         } else {
//             return redirect()->back()->withErrors(['error' => 'Error: ' . $response->body()])->withInput();
//         }
//     }

//     return response()->json([
//         'message' => 'Sincronización completa realizada con éxito.',
//         'results' => $results,
//     ]);
// }


    /**
     * Crear un ítem para el rango de fechas especificado.
     */
    // private function createItem($update, $startDate, $endDate)
    // {
    //     if ($update['update_type'] == 'availability') {
    //         $details = [
    //             'property_id' => $update['property_id'],
    //             'room_type_id' => $update['room_type_id'],
    //         ];
    //     } else {
    //         $details = [
    //             'property_id' => $update['property_id'],
    //         ];
    //     }


    //     if (isset( $update['rate_plan_id'])) {
    //         $details['rate_plan_id'] = $update['rate_plan_id'];

    //     }

    //     // Si el rango es de un solo día, usar `date`
    //     if ($startDate->equalTo($endDate)) {
    //         $details['date'] = $startDate->toDateString();
    //     } else {
    //         $details['date_from'] = $startDate->toDateString();
    //         $details['date_to'] = $endDate->toDateString();
    //     }

    //     // Manejar los tipos específicos
    //     switch ($update['update_type']) {
    //         case 'rate':
    //             $details['rate'] = (float)$update['value'];
    //             break;
    //         case 'availability':
    //             $details['availability'] = (int)$update['value'];
    //             break;
    //         case 'min_stay':
    //             $details['min_stay'] = (int)$update['value'];
    //             break;
    //         case 'stop_sell':

    //             if ($update['value'] == 1) {
    //                 $details['stop_sell'] = true;

    //             }else {
    //                 $details['stop_sell'] = false;

    //             }

    //             //$details['stop_sell'] = filter_var($update['value'], FILTER_VALIDATE_BOOLEAN);
    //             break;
    //         case 'restrictions':
    //             //dd($update);
    //             if ($update['closed_to_arrival'] !== null) {
    //                 $details['closed_to_arrival'] = (bool) $update['closed_to_arrival'];
    //             }

    //             if ($update['closed_to_departure'] !== null) {
    //                 $details['closed_to_departure'] = (bool) $update['closed_to_departure'];
    //             }

    //             if (isset($update['min_stay_through']) && $update['min_stay_through'] > 0) {
    //                 $details['min_stay_through'] = + (int)$update['min_stay_through'];
    //             }

    //             // if (isset($update['min_stay_arrival']) && $update['min_stay_arrival'] > 0) {
    //             //     $details['min_stay_arrival'] = + (int)$update['min_stay_arrival'];
    //             // }
    //             if (isset($update['min_stay_arrival']) && $update['min_stay_arrival'] > 0) {
    //                 $details['min_stay'] = + (int)$update['min_stay_arrival'];
    //             }
    //             if (isset($update['max_stay']) && $update['max_stay'] > 0) {
    //                 $details['max_stay'] = + (int)$update['max_stay'];
    //             }
    //             break;
    //     }

    //     return $details;
    // }

    public function getByProperty($propertyId)
    {
        $apartamento = Apartamento::where('id_channex',  $propertyId)->first();
        $roomTypes = RoomType::where('property_id', $apartamento->id)->get(['id_channex', 'title']);
        return response()->json($roomTypes);
    }

   public function getRatePlans($propertyId, $roomTypeId)
{
    // Validar que la propiedad y el tipo de habitación existen
    $property = Apartamento::where('id_channex', $propertyId)->firstOrFail();
    $roomType = \App\Models\RoomType::where('id_channex', $roomTypeId)->firstOrFail();

    // Buscar los Rate Plans asociados en la base de datos
    $ratePlans = RatePlan::where('property_id', $property->id)
        ->where('room_type_id', $roomType->id)
        ->get(['id_channex', 'title']);

    return response()->json($ratePlans);
}


}
