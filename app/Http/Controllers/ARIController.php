<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\ARIUpdate;
use App\Models\RatePlan;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ARIController extends Controller
{
    private $apiUrl = 'https://staging.channex.io/api/v1';
    private $apiToken = 'uMxPHon+J28pd17nie3qeU+kF7gUulWjb2UF5SRFr4rSIhmLHLwuL6TjY92JGxsx'; // Reemplaza con tu token de acceso

    public function index()
    {
        $properties = Apartamento::all(); // Obtenemos las propiedades
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
            'updates.*.rate_plan_id' => 'required|string',
            'updates.*.date_from' => 'required|date',
            'updates.*.date_to' => 'nullable|date',
            'updates.*.update_type' => 'nullable|string',
            'updates.*.value' => 'nullable|string',
            'updates.*.min_stay' => 'nullable|integer',
            'updates.*.max_stay' => 'nullable|integer',
        ]);

        //dd( $validatedData);
        $urlVariable = 'restrictions';
        $updates = [];
        foreach ($validatedData['updates'] as $update) {
            // $details = [
            //     'property_id' => $update['property_id'],
            //     'rate_plan_id' => $update['rate_plan_id'],
            //     'room_type_id' => $update['room_type_id'],
            //     'date' => $update['date_from'],
            //     'date_to' => $update['date_to'] ?? $update['date_from'],
            //     'rate' => $update['rate'],
            // ];
            $details = [
                'property_id' => $update['property_id'],
                'rate_plan_id' => $update['rate_plan_id'],
                'room_type_id' => $update['room_type_id'],
                // 'rate' => $update['rate'],
            ];

            // Manejo dinámico de la clave 'date' o 'date_from' según el valor de 'date_to'
            if (empty($update['date_to'])) {
                $details['date'] = $update['date_from'];
            } else {
                $details['date_from'] = $update['date_from'];
                $details['date_to'] = $update['date_to'];
            }

            // Manejo de tipos específicos
            switch ($update['update_type']) {
                case 'rate':
                    $details['rate'] = (float) $update['value']; // Convertir a número flotante para Channex
                    break;

                case 'availability':
                    $details['availability'] = 1; // Convertir a entero para Channex
                    $urlVariable = 'availability';
                    break;

                case 'min_stay':
                    $details['min_stay'] = (int) $update['min_stay'];
                    break;

                case 'stop_sell':
                    $details['stop_sell'] = filter_var($update['value'], FILTER_VALIDATE_BOOLEAN);
                    break;

                case 'restrictions':
                    $details['closed_to_arrival'] = isset($update['closed_to_arrival']) ? true : false;
                    $details['closed_to_departure'] = isset($update['closed_to_departure']) ? true : false;
                    $details['min_stay'] = $update['min_stay'] ?? null;
                    $details['max_stay'] = $update['max_stay'] ?? null;
                    break;
            }

            $updates[] = $details;
        }
        //dd($updates);
        // Petición a la API
        $response = Http::withHeaders([
            'user-api-key' => $this->apiToken,
        ])->post("{$this->apiUrl}/".$urlVariable , ['values' => $updates]);
        //dd($response->json());
        if ($response->successful()) {
            return redirect()->route('ari.index')->with('success', 'Actualización realizada con éxito.');
        }

        return redirect()->back()->withErrors(['error' => 'Error: ' . $response->body()])->withInput();
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
