<?php

namespace App\Http\Controllers;

use App\Models\RatePlan;
use App\Models\RatePlanOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChannexWebController extends Controller
{

    private $apiUrl = 'https://staging.channex.io/api/v1'; // Base URL de la API
    private $apiToken = 'uMxPHon+J28pd17nie3qeU+kF7gUulWjb2UF5SRFr4rSIhmLHLwuL6TjY92JGxsx'; // Reemplaza con tu token de acceso

    // Crear Propiedad
    public function createTestProperty()
    {
        $response = Http::withHeaders([
            'user-api-key' => $this->apiToken,
        ])->post("{$this->apiUrl}/properties", [
                'name' => 'Test Property - Provider Name',
                'currency' => 'USD',
                'timezone' => 'UTC',
            ]);

        if ($response->successful()) {
            return response()->json([
                'message' => 'Propiedad creada con éxito',
                'property' => $response->json(),
            ]);
        }

        return response()->json([
            'message' => 'Error al crear la propiedad',
            'error' => $response->json(),
        ], $response->status());
    }

    public function createRoomTypes($propertyId)
    {
        $roomTypes = [
            ['name' => 'Twin Room', 'occupancy' => 2],
            ['name' => 'Double Room', 'occupancy' => 2],
        ];

        $results = [];
        foreach ($roomTypes as $roomType) {
            $response = Http::withToken($this->apiToken)
                ->post("{$this->apiUrl}/room-types", array_merge($roomType, ['property_id' => $propertyId]));

            if ($response->successful()) {
                $results[] = $response->json();
            } else {
                return response()->json([
                    'message' => 'Error al crear tipo de habitación',
                    'error' => $response->json(),
                ], $response->status());
            }
        }

        return response()->json([
            'message' => 'Tipos de habitación creados con éxito',
            'room_types' => $results,
        ]);
    }

    public function createRatePlans($roomTypeIds)
    {
        $ratePlans = [
            [
                'title' => 'Best Available Rate',
                'currency' => 'USD',
                'room_type_id' => $roomTypeIds['Twin Room'],
                'default_rate' => 100,
            ],
            [
                'title' => 'Bed & Breakfast',
                'currency' => 'USD',
                'room_type_id' => $roomTypeIds['Twin Room'],
                'default_rate' => 120,
            ],
            [
                'title' => 'Best Available Rate',
                'currency' => 'USD',
                'room_type_id' => $roomTypeIds['Double Room'],
                'default_rate' => 100,
            ],
            [
                'title' => 'Bed & Breakfast',
                'currency' => 'USD',
                'room_type_id' => $roomTypeIds['Double Room'],
                'default_rate' => 120,
            ],
        ];

        $results = [];
        foreach ($ratePlans as $ratePlan) {
            $response = Http::withToken($this->apiToken)
                ->post("{$this->apiUrl}/rate-plans", $ratePlan);

            if ($response->successful()) {
                $results[] = $response->json();
            } else {
                return response()->json([
                    'message' => 'Error al crear plan de tarifas',
                    'error' => $response->json(),
                ], $response->status());
            }
        }

        return response()->json([
            'message' => 'Planes de tarifas creados con éxito',
            'rate_plans' => $results,
        ]);
    }

    public function createDistributionChannels($propertyId)
    {
        $channels = [
            [
                'name' => 'Booking.com',
                'property_id' => $propertyId,
                'channel_code' => 'booking_com',
            ],
            [
                'name' => 'Airbnb',
                'property_id' => $propertyId,
                'channel_code' => 'airbnb',
            ],
            // Añadir más canales según sea necesario
        ];

        $results = [];
        foreach ($channels as $channel) {
            $response = Http::withToken($this->apiToken)
                ->post("{$this->apiUrl}/distribution-channels", $channel);

            if ($response->successful()) {
                $results[] = $response->json();
            } else {
                return response()->json([
                    'message' => 'Error al crear canal de distribución',
                    'error' => $response->json(),
                ], $response->status());
            }
        }

        return response()->json([
            'message' => 'Canales de distribución creados con éxito',
            'channels' => $results,
        ]);
    }


    public function createBooking($channelCode, $propertyId, $roomTypeId)
    {
        $bookingData = [
            'property_id' => $propertyId,
            'channel_code' => $channelCode, // Booking.com o Airbnb, por ejemplo
            'room_type_id' => $roomTypeId,
            'check_in' => '2024-12-10', // Fecha de entrada
            'check_out' => '2024-12-15', // Fecha de salida
            'guest_name' => 'John Doe', // Nombre del huésped
            'guest_email' => 'johndoe@example.com', // Email del huésped
            'guest_phone' => '1234567890', // Teléfono del huésped
        ];

        $response = Http::withToken($this->apiToken)
            ->post("{$this->apiUrl}/bookings", $bookingData);

        if ($response->successful()) {
            return response()->json([
                'message' => 'Reserva creada con éxito',
                'booking' => $response->json(),
            ]);
        }

        return response()->json([
            'message' => 'Error al crear reserva',
            'error' => $response->json(),
        ], $response->status());
    }

    public function confirmBooking($bookingId)
    {
        $response = Http::withToken($this->apiToken)
            ->post("{$this->apiUrl}/bookings/{$bookingId}/confirm");

        if ($response->successful()) {
            return response()->json([
                'message' => 'Reserva confirmada con éxito',
                'confirmation' => $response->json(),
            ]);
        }

        return response()->json([
            'message' => 'Error al confirmar reserva',
            'error' => $response->json(),
        ], $response->status());
    }


    public function fullSync(Request $request)
    {
        // Datos necesarios para la solicitud
        $apiKey = 'u0SoUIukKf1fMtagxFQaq7IIJOjKkS4nmB5L/K8j8HsHR6AG6+0mNMWf4INuyoBX'; // Cambia esto por tu API Key real
        $providerCode = 'OpenChannel'; // Cambia esto por tu código de proveedor real
        $hotelCode = '12152494'; // Cambia esto por tu código de hotel real

        // URL de la API
        $url = 'https://staging.channex.io/api/v1/channel_webhooks/open_channel/request_full_sync';

        try {
            // Realizar la solicitud POST
            $response = Http::withHeaders([
                'user-api-key' => $apiKey,
            ])->post($url, [
                'provider_code' => $providerCode,
                'hotel_code' => $hotelCode,
            ]);

            // Verificar el estado de la respuesta
            if ($response->successful()) {
                return view('admin.channex.fullSync', compact(['message' => 'Sincronización iniciada con éxito.']));
            } else {
                return view('admin.channex.fullSync', [
                    'message' => 'Error al iniciar la sincronización.',
                    'error' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            // Manejar errores
            return view('admin.channex.fullSync', [
                'message' => 'Ocurrió un error inesperado.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    // RATE PLANS
    public function ratePlansList(Request $request)
    {
        // Datos necesarios para la solicitud
        $apiKey = 'u0SoUIukKf1fMtagxFQaq7IIJOjKkS4nmB5L/K8j8HsHR6AG6+0mNMWf4INuyoBX'; // Cambia esto por tu API Key real
        $url = 'https://staging.channex.io/api/v1/rate_plans';

        try {
            // Realizar la solicitud GET
            $response = Http::withHeaders([
                'user-api-key' => $apiKey,
            ])->get($url);

            // Verificar el estado de la respuesta y retornar directamente
            if ($response->successful()) {
                //dd($response->json());
                $ratePlans = $response->json('data'); // Obtén los datos relevantes

                foreach ($ratePlans as $plan) {
                    $attributes = $plan['attributes'];
                    $relationships = $plan['relationships'];

                    // Crear o actualizar el RatePlan
                    $ratePlan = RatePlan::updateOrCreate(
                        ['id_rate_plans' => $attributes['id']],
                        [
                            'title' => $attributes['title'],
                            'currency' => $attributes['currency'],
                            'meal_type' => $attributes['meal_type'],
                            'rate_mode' => $attributes['rate_mode'],
                            'sell_mode' => $attributes['sell_mode'],
                            'property_id' => $relationships['property']['data']['id'] ?? null,
                            'room_type_id' => $relationships['room_type']['data']['id'] ?? null,
                        ]
                    );

                    // Eliminar opciones antiguas
                    //$ratePlan->options()->delete();

                    // Guardar las opciones
                    foreach ($attributes['options'] as $option) {
                        RatePlanOption::create([
                            'rate_plan_id' => $ratePlan->id,
                            'rate' => $option['rate'],
                            'occupancy' => $option['occupancy'],
                            'is_primary' => $option['is_primary'] ?? false,
                            'inherit_rate' => $option['inherit_rate'] ?? false,
                        ]);
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'data' => $response->json(),
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error al obtener los planes de tarifas.',
                    'details' => $response->body(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            // Manejar errores y retornar directamente
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error inesperado.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function ratePlansUpdate(Request $request)
    {
        // Datos necesarios para la solicitud
        $apiKey = 'u0SoUIukKf1fMtagxFQaq7IIJOjKkS4nmB5L/K8j8HsHR6AG6+0mNMWf4INuyoBX'; // Cambia esto por tu API Key real
        $url = 'https://staging.channex.io/api/v1/rate_plans';

        try {
            // Realizar la solicitud GET
            $response = Http::withHeaders([
                'user-api-key' => $apiKey,
            ])->get($url);

            // Verificar el estado de la respuesta y retornar directamente
            if ($response->successful()) {
                //dd($response->json());
                $ratePlans = $response->json('data'); // Obtén los datos relevantes

                foreach ($ratePlans as $plan) {
                    $attributes = $plan['attributes'];
                    $relationships = $plan['relationships'];

                    // Crear o actualizar el RatePlan
                    $ratePlan = RatePlan::updateOrCreate(
                        ['id_rate_plans' => $attributes['id']],
                        [
                            'title' => $attributes['title'],
                            'currency' => $attributes['currency'],
                            'meal_type' => $attributes['meal_type'],
                            'rate_mode' => $attributes['rate_mode'],
                            'sell_mode' => $attributes['sell_mode'],
                            'property_id' => $relationships['property']['data']['id'] ?? null,
                            'room_type_id' => $relationships['room_type']['data']['id'] ?? null,
                        ]
                    );

                    // Eliminar opciones antiguas
                    //$ratePlan->options()->delete();

                    // Guardar las opciones
                    foreach ($attributes['options'] as $option) {
                        RatePlanOption::create([
                            'rate_plan_id' => $ratePlan->id,
                            'rate' => $option['rate'],
                            'occupancy' => $option['occupancy'],
                            'is_primary' => $option['is_primary'] ?? false,
                            'inherit_rate' => $option['inherit_rate'] ?? false,
                        ]);
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'data' => $response->json(),
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error al obtener los planes de tarifas.',
                    'details' => $response->body(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            // Manejar errores y retornar directamente
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error inesperado.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
