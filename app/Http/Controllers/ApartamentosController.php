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
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'asc');
        $edificioId = $request->get('edificio_id');
        $apartamentoId = $request->get('apartamento_id'); // <-- lo recogemos también
        $apartamentoslist = Apartamento::all();
        $apartamentos = Apartamento::when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', '%' . $search . '%')
                      ->orWhere('id', 'like', '%' . $search . '%');
                });
            })
            ->when($edificioId, function ($query, $edificioId) {
                $query->where('edificio_id', $edificioId);
            })
            ->when($apartamentoId, function ($query, $apartamentoId) {
                $query->where('id', $apartamentoId); // <- aquí filtras por ID exacto
            })
            ->orderBy($sort, $order)
            ->paginate(30);

        $edificios = Edificio::all();

        return view('admin.apartamentos.index', compact('apartamentoslist', 'apartamentos', 'edificios'));
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

        // Reglas de validación, puedes agregar más según sea necesario
        $rules = [
            'edificio_id' => 'required|exists:edificios,id' // ejemplo de validación
        ];

        // Validar los datos del formulario
        $validatedData = $request->validate($rules);

        // Asignar solo si no vienen como null
        if ($request->has('titulo')) {
            $apartamento->titulo = $request->input('titulo');
        }

        if ($request->has('id_booking')) {
            $apartamento->id_booking = $request->input('id_booking');
        }

        if ($request->has('id_web')) {
            $apartamento->id_web = $request->input('id_web');
        }

        if ($request->has('nombre')) {
            $apartamento->nombre = $request->input('nombre');
        }

        if ($request->has('claves')) {
            $apartamento->claves = $request->input('claves');
        }

        // Actualizar solo si el edificio_id está presente
        $apartamento->edificio_id = $validatedData['edificio_id'];

        // Guardar los cambios
        $apartamento->save();

        // Redireccionar con mensaje de éxito
        return redirect()->route('apartamentos.admin.index')->with('status', 'Apartamento actualizado con éxito!');
    }

    public function storeAdmin(Request $request)
    {
        $rules = [
                // 'nombre' => 'required|string|max:255',
                // 'id_booking' => 'required|string|max:255',
                // 'id_web' => 'required|string|max:255',
                // 'titulo' => 'required|string|max:255',
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
                "callback_url" => "https://crm.apartamentosalgeciras.com/api/webhooks/". $apartamento->id ."/" . $mask['url'],
                "event_mask" => $mask['nombre'],
                "request_params" => new \stdClass(), // Según la documentación, debe ser un objeto vacío si no se usan parámetros
                "headers" => new \stdClass(),       // Según la documentación, debe ser un objeto vacío si no se usan encabezados
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
