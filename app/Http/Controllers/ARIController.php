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
            // return [$response->json(), $updates];
            return redirect()->route('ari.index')->with('success', 'Actualización realizada con éxito.');
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
    $startDate = Carbon::now();
    $endDate = $startDate->copy()->addDays(500);
    $allResponses = [];

    // Obtener todos los apartamentos con un id_channex no nulo
    $apartamentos = Apartamento::whereNotNull('id_channex')->with('roomTypes')->get();

    foreach ($apartamentos as $apartamento) {
        $updates = []; // Array de updates solo para este apartamento

        foreach ($apartamento->roomTypes as $roomType) {

            $reservas = Reserva::where('apartamento_id', $apartamento->id)
                ->where('room_type_id', $roomType->id)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('fecha_entrada', [$startDate, $endDate])
                          ->orWhereBetween('fecha_salida', [$startDate, $endDate]);
                })
                ->get();

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $hasReservation = $reservas->contains(function ($reserva) use ($date) {
                    return $date->gte(Carbon::parse($reserva->fecha_entrada)) &&
                           $date->lte(Carbon::parse($reserva->fecha_salida));
                });

                $availability = $hasReservation ? 0 : 1;

                $updates[] = [
                    'property_id' => $apartamento->id_channex,
                    'room_type_id' => $roomType->id_channex,
                    'date' => $date->toDateString(),
                    'availability' => $availability,
                ];
            }
        }

        // Hacer petición por cada apartamento
        $response = Http::withHeaders([
            'user-api-key' => $this->apiToken,
        ])->post("{$this->apiUrl}/availability", [
            'values' => $updates,
        ]);

        $allResponses[] = [
            'apartamento_id' => $apartamento->id,
            'apartamento_nombre' => $apartamento->titulo ?? 'Sin título',
            'status' => $response->status(),
            'success' => $response->successful(),
            'response' => $response->json(),
        ];
    }

    return response()->json([
        'success' => true,
        'message' => 'Sincronización por apartamento completada.',
        'results' => $allResponses
    ]);
}


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
