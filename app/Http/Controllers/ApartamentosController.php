<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\Edificio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApartamentosController extends Controller
{
    private $apiUrl = 'https://staging.channex.io/api/v1';
    private $apiToken = 'uMxPHon+J28pd17nie3qeU+kF7gUulWjb2UF5SRFr4rSIhmLHLwuL6TjY92JGxsx'; // Reemplaza con tu token de acceso
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pisos = Apartamento::all();
        return view('apartamentos.index', compact('pisos'));
    }

    public function indexAdmin(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'id'); // Default sort column
        $order = $request->get('order', 'asc'); // Default sort order
        $edificioId = $request->get('edificio_id'); // Obtener el ID del edificio seleccionado

        $apartamentos = Apartamento::where(function ($query) use ($search) {
            $query->where('nombre', 'like', '%' . $search . '%');
        })
        ->when($edificioId, function ($query, $edificioId) {
            $query->where('edificio_id', $edificioId); // Filtrar por edificio si está seleccionado
        })
        ->orderBy($sort, $order)
        ->paginate(30);

        $edificios = Edificio::all(); // Obtener todos los edificios para el dropdown

        return view('admin.apartamentos.index', compact('apartamentos', 'edificios'));
    }

    public function createAdmin()
    {
        $edificios = Edificio::all();

        return view('admin.apartamentos.create', compact('edificios'));
    }

    public function editAdmin($id)
    {
        $apartamento = Apartamento::find($id);
        $edificios = Edificio::all();
        return view('admin.apartamentos.edit', compact('apartamento','edificios'));
    }

    public function updateAdmin(Request $request, $id)
    {
        $apartamento = Apartamento::findOrFail($id);
        $rules = [
                'nombre' => 'required|string|max:255',
                'id_booking' => 'required|string|max:255',
                'id_web' => 'required|string|max:255',
                'titulo' => 'required|string|max:255',
                'edificio_id' => 'required'
        ];

        // Validar los datos del formulario
        $validatedData = $request->validate($rules);
        //    dd($apartamento->titulo);
        $apartamento->titulo = $validatedData['titulo'];
        $apartamento->id_booking = $validatedData['id_booking'];
        $apartamento->id_web = $validatedData['id_web'];
        $apartamento->nombre = $validatedData['nombre'];
        $apartamento->claves = $request['claves'];
        $apartamento->edificio_id = $request['edificio_id'];
        $apartamento->save();
       // Actualizar el cliente con los datos validados
        //    $apartamento->update($validatedData);

       // Redireccionar a una ruta de éxito o devolver una respuesta
       return redirect()->route('apartamentos.admin.index')->with('status', 'Apartamento actualizado con éxito!');
    }
    public function storeAdmin(Request $request)
    {
        $rules = [
                'nombre' => 'required|string|max:255',
                'id_booking' => 'required|string|max:255',
                'id_web' => 'required|string|max:255',
                'titulo' => 'required|string|max:255',
                'claves' => 'required|string|max:255',
                'edificio_id' => 'required'
        ];

        // Validar los datos del formulario
        $validatedData = $request->validate($rules);
        $apartamento = Apartamento::create($validatedData);

       // Redireccionar a una ruta de éxito o devolver una respuesta
       return redirect()->route('apartamentos.admin.index')->with('status', 'Apartamento actualizado con éxito!');
    }

    public function registrarWebhooks($id){
        $apartamento = Apartamento::find($id);

        $url = 'https://staging.channex.io/api/v1/webhooks';

        $masks = [
            [
                'nombre' => 'ari',
                'url' => 'ari-changes'
            ],
            [
                'nombre' => 'booking',
                'url' => 'booking-any'
            ],
            [
                'nombre' => 'booking_unmapped_room',
                'url' => 'booking-unmapped-room'
            ],
            [
                'nombre' => 'booking_unmapped_rate',
                'url' => 'booking-unmapped-rate'
            ],
            [
                'nombre' => 'message',
                'url' => 'message'
            ],
            [
                'nombre' => 'sync_error',
                'url' => 'sync-error'
            ],
            [
                'nombre' => 'reservation_request',
                'url' => 'reservation-request'
            ],
            [
                'nombre' => 'alteration_request',
                'url' => 'alteration_request'
            ],
            [
                'nombre' => 'review',
                'url' => 'review'
            ],
        ];
        $responses = [];

        foreach ($masks as $mask) {

            $data = [
                "property_id" => $apartamento->id_channex,
                "callback_url" => "https://crm.apartamentosalgeciras.com/api/" . $mask['url'],
                "event_mask" => $mask['nombre'],
                "request_params" => [],
                "headers" => [],
                "is_active" => true, // Espacio extra eliminado
                "send_data" => true,
            ];

            // Petición a la API
            $response = Http::withHeaders([
                'user-api-key' => $this->apiToken,
            ])->post($url, ['webhook' => $data]);

            // Manejo de respuesta
            if ($response->successful()) {
                $responses[] = [
                    'status' => 'success',
                    'message' => 'Webhook registrado con éxito',
                    'data' => $response->json(),
                ];
            } else {
                $responses[] = [
                    'status' => 'error',
                    'message' => 'Error al registrar webhook',
                    'data' => $response->json(),
                ];
            }

        }
        return response()->json($responses);
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
}
