<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\Edificio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class ApartamentosController extends Controller
{
    private $apiUrl = 'https://staging.channex.io/api/v1';
    private $apiToken = 'uMxPHon+J28pd17nie3qeU+kF7gUulWjb2UF5SRFr4rSIhmLHLwuL6TjY92JGxsx';

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
        $apartamentoId = $request->get('apartamento_id');
        
        // Log the search operation
        $this->logRead('APARTAMENTOS', null, [
            'search' => $search,
            'sort' => $sort,
            'order' => $order,
            'edificio_id' => $edificioId,
            'apartamento_id' => $apartamentoId
        ]);
        
        $apartamentoslist = Apartamento::all();
        $apartamentos = Apartamento::when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', '%' . $search . '%')
                      ->orWhere('titulo', 'like', '%' . $search . '%')
                      ->orWhere('id', 'like', '%' . $search . '%');
                });
            })
            ->when($edificioId, function ($query, $edificioId) {
                $query->where('edificio_id', $edificioId);
            })
            ->when($apartamentoId, function ($query, $apartamentoId) {
                $query->where('id', $apartamentoId);
            })
            ->orderBy($sort, $order)
            ->paginate(20);

        $edificios = Edificio::all();

        // Calcular estadísticas para cada apartamento del año actual
        $añoActual = date('Y');
        $estadisticasApartamentos = [];
        
        foreach ($apartamentos as $apartamento) {
            $reservasAño = $apartamento->reservas()->whereYear('fecha_entrada', $añoActual)->get();
            
            $estadisticasApartamentos[$apartamento->id] = [
                'ingresos_año' => $reservasAño->sum('precio'),
                'ocupaciones_año' => $reservasAño->count(),
                'ingresos_netos' => $reservasAño->sum('neto')
            ];
        }

        return view('admin.apartamentos.index', compact('apartamentoslist', 'apartamentos', 'edificios', 'search', 'sort', 'order', 'estadisticasApartamentos', 'añoActual'));
    }

    public function createAdmin()
    {
        $edificios = Edificio::all();
        return view('admin.apartamentos.create', compact('edificios'));
    }

    public function editAdmin($id)
    {
        $apartamento = Apartamento::findOrFail($id);
        $edificios = Edificio::all();
        return view('admin.apartamentos.edit', compact('apartamento','edificios'));
    }

    public function updateAdmin(Request $request, $id)
    {
        $apartamento = Apartamento::findOrFail($id);
        
        // Log the update attempt
        $this->logUpdate('APARTAMENTO', $id, $apartamento->toArray(), $request->all());

        // Reglas de validación completas para Channex
        $rules = [
            'edificio_id' => 'required|exists:edificios,id',
            'titulo' => 'required|string|max:255',
            'claves' => 'required|string|max:255',
            'property_type' => 'required|string|in:apartment,hotel,hostel,villa,guest_house',
            'currency' => 'required|string|size:3',
            'country' => 'required|string|size:2',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'zip_code' => 'required|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'timezone' => 'required|string|max:100',
            'description' => 'nullable|string|max:2000',
            'important_information' => 'nullable|string|max:2000',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:500',
        ];

        // Mensajes de validación personalizados
        $messages = [
            'edificio_id.required' => 'El edificio es obligatorio.',
            'edificio_id.exists' => 'El edificio seleccionado no existe.',
            'titulo.required' => 'El título es obligatorio.',
            'titulo.max' => 'El título no puede tener más de 255 caracteres.',
            'claves.required' => 'Las claves de acceso son obligatorias.',
            'claves.max' => 'Las claves no pueden tener más de 255 caracteres.',
            'property_type.required' => 'El tipo de propiedad es obligatorio.',
            'property_type.in' => 'El tipo de propiedad debe ser válido.',
            'currency.required' => 'La moneda es obligatoria.',
            'currency.size' => 'La moneda debe tener exactamente 3 caracteres.',
            'country.required' => 'El país es obligatorio.',
            'country.size' => 'El código de país debe tener exactamente 2 caracteres.',
            'state.required' => 'El estado/provincia es obligatorio.',
            'city.required' => 'La ciudad es obligatoria.',
            'address.required' => 'La dirección es obligatoria.',
            'zip_code.required' => 'El código postal es obligatorio.',
            'latitude.numeric' => 'La latitud debe ser un número válido.',
            'latitude.between' => 'La latitud debe estar entre -90 y 90.',
            'longitude.numeric' => 'La longitud debe ser un número válido.',
            'longitude.between' => 'La longitud debe estar entre -180 y 180.',
            'timezone.required' => 'La zona horaria es obligatoria.',
            'description.max' => 'La descripción no puede tener más de 2000 caracteres.',
            'important_information.max' => 'La información importante no puede tener más de 2000 caracteres.',
            'email.email' => 'El formato del email no es válido.',
            'website.url' => 'El formato de la URL no es válido.',
        ];

        // Validar los datos del formulario
        $validatedData = $request->validate($rules, $messages);

        try {
            // Actualizar solo los campos que vienen en la request
            if ($request->has('titulo')) {
                $apartamento->titulo = $validatedData['titulo'];
            }
            if ($request->has('claves')) {
                $apartamento->claves = $validatedData['claves'];
            }
            if ($request->has('property_type')) {
                $apartamento->property_type = $validatedData['property_type'];
            }
            if ($request->has('currency')) {
                $apartamento->currency = $validatedData['currency'];
            }
            if ($request->has('country')) {
                $apartamento->country = $validatedData['country'];
            }
            if ($request->has('state')) {
                $apartamento->state = $validatedData['state'];
            }
            if ($request->has('city')) {
                $apartamento->city = $validatedData['city'];
            }
            if ($request->has('address')) {
                $apartamento->address = $validatedData['address'];
            }
            if ($request->has('zip_code')) {
                $apartamento->zip_code = $validatedData['zip_code'];
            }
            if ($request->has('latitude')) {
                $apartamento->latitude = $validatedData['latitude'];
            }
            if ($request->has('longitude')) {
                $apartamento->longitude = $validatedData['longitude'];
            }
            if ($request->has('timezone')) {
                $apartamento->timezone = $validatedData['timezone'];
            }
            if ($request->has('description')) {
                $apartamento->description = $validatedData['description'];
            }
            if ($request->has('important_information')) {
                $apartamento->important_information = $validatedData['important_information'];
            }
            if ($request->has('email')) {
                $apartamento->email = $validatedData['email'];
            }
            if ($request->has('phone')) {
                $apartamento->phone = $validatedData['phone'];
            }
            if ($request->has('website')) {
                $apartamento->website = $validatedData['website'];
            }

            // Actualizar el edificio
            $apartamento->edificio_id = $validatedData['edificio_id'];

            // Guardar los cambios
            $apartamento->save();

            return redirect()->route('apartamentos.admin.index')
                ->with('swal_success', '¡Apartamento actualizado exitosamente!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('swal_error', 'Error al actualizar el apartamento: ' . $e->getMessage());
        }
    }

    public function storeAdmin(Request $request)
    {
        // Reglas de validación completas para Channex
        $rules = [
            'claves' => 'required|string|max:255',
            'edificio_id' => 'required|exists:edificios,id',
            'titulo' => 'required|string|max:255',
            'property_type' => 'required|string|in:apartment,hotel,hostel,villa,guest_house',
            'currency' => 'required|string|size:3',
            'country' => 'required|string|size:2',
            'state' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'zip_code' => 'required|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'timezone' => 'required|string|max:100',
            'description' => 'nullable|string|max:2000',
            'important_information' => 'nullable|string|max:2000',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:500',
        ];

        // Mensajes de validación personalizados
        $messages = [
            'claves.required' => 'Las claves de acceso son obligatorias.',
            'claves.max' => 'Las claves no pueden tener más de 255 caracteres.',
            'edificio_id.required' => 'El edificio es obligatorio.',
            'edificio_id.exists' => 'El edificio seleccionado no existe.',
            'titulo.required' => 'El título es obligatorio.',
            'titulo.max' => 'El título no puede tener más de 255 caracteres.',
            'property_type.required' => 'El tipo de propiedad es obligatorio.',
            'property_type.in' => 'El tipo de propiedad debe ser válido.',
            'currency.required' => 'La moneda es obligatoria.',
            'currency.size' => 'La moneda debe tener exactamente 3 caracteres.',
            'country.required' => 'El país es obligatorio.',
            'country.size' => 'El código de país debe tener exactamente 2 caracteres.',
            'state.required' => 'El estado/provincia es obligatorio.',
            'city.required' => 'La ciudad es obligatoria.',
            'address.required' => 'La dirección es obligatoria.',
            'zip_code.required' => 'El código postal es obligatorio.',
            'latitude.numeric' => 'La latitud debe ser un número válido.',
            'latitude.between' => 'La latitud debe estar entre -90 y 90.',
            'longitude.numeric' => 'La longitud debe ser un número válido.',
            'longitude.between' => 'La longitud debe estar entre -180 y 180.',
            'timezone.required' => 'La zona horaria es obligatoria.',
            'description.max' => 'La descripción no puede tener más de 2000 caracteres.',
            'important_information.max' => 'La información importante no puede tener más de 2000 caracteres.',
            'email.email' => 'El formato del email no es válido.',
            'website.url' => 'El formato de la URL no es válido.',
        ];

        // Validar los datos del formulario
        $validatedData = $request->validate($rules, $messages);

        try {
            // Crear el apartamento con los datos validados
            $apartamento = Apartamento::create($validatedData);

            return redirect()->route('apartamentos.admin.index')
                ->with('swal_success', '¡Apartamento creado exitosamente!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('swal_error', 'Error al crear el apartamento: ' . $e->getMessage());
        }
    }

    public function registrarWebhooks($id){
        try {
            $apartamento = Apartamento::findOrFail($id);

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
                    "request_params" => new \stdClass(),
                    "headers" => new \stdClass(),
                    "is_active" => true,
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
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
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
     * Display the specified apartment in admin panel.
     */
    public function showAdmin(string $id, Request $request)
    {
        try {
            $apartamento = Apartamento::with(['photos', 'tarifas', 'edificioRel', 'reservas.cliente', 'reservas.estado'])->findOrFail($id);
            $edificios = Edificio::all();
            
            // Obtener filtros de fecha
            $año = $request->get('año', date('Y'));
            $mes = $request->get('mes');
            
            // Calcular estadísticas
            $estadisticas = $this->calcularEstadisticasApartamento($apartamento, $año, $mes);
            
            return view('admin.apartamentos.show', compact('apartamento', 'edificios', 'estadisticas', 'año', 'mes'));
        } catch (\Exception $e) {
            return redirect()->route('apartamentos.admin.index')
                ->with('swal_error', 'Apartamento no encontrado: ' . $e->getMessage());
        }
    }

    /**
     * Calcular estadísticas de un apartamento
     */
    private function calcularEstadisticasApartamento($apartamento, $año, $mes = null)
    {
        $query = $apartamento->reservas()->whereYear('fecha_entrada', $año);
        
        if ($mes) {
            $query->whereMonth('fecha_entrada', $mes);
        }
        
        $reservas = $query->get();
        
        return [
            'total_reservas' => $reservas->count(),
            'total_ingresos' => $reservas->sum('precio'),
            'ingresos_netos' => $reservas->sum('neto'),
            'ocupacion_dias' => $reservas->sum(function($reserva) {
                return \Carbon\Carbon::parse($reserva->fecha_entrada)->diffInDays($reserva->fecha_salida);
            }),
            'reservas_activas' => $reservas->where('estado_id', 3)->count(),
            'reservas_completadas' => $reservas->where('estado_id', 4)->count(),
            'reservas_canceladas' => $reservas->where('estado_id', 5)->count(),
            'promedio_por_reserva' => $reservas->count() > 0 ? $reservas->avg('precio') : 0,
            'mes_mas_ocupado' => $this->obtenerMesMasOcupado($apartamento, $año),
            'reservas_por_mes' => $this->obtenerReservasPorMes($apartamento, $año)
        ];
    }

    /**
     * Obtener el mes más ocupado del año
     */
    private function obtenerMesMasOcupado($apartamento, $año)
    {
        $meses = [];
        for ($i = 1; $i <= 12; $i++) {
            $count = $apartamento->reservas()
                ->whereYear('fecha_entrada', $año)
                ->whereMonth('fecha_entrada', $i)
                ->count();
            $meses[$i] = $count;
        }
        
        $mesMasOcupado = array_keys($meses, max($meses))[0];
        return [
            'mes' => $mesMasOcupado,
            'nombre' => \Carbon\Carbon::create($año, $mesMasOcupado, 1)->format('F'),
            'reservas' => $meses[$mesMasOcupado]
        ];
    }

    /**
     * Obtener reservas por mes para el gráfico
     */
    private function obtenerReservasPorMes($apartamento, $año)
    {
        $datos = [];
        for ($i = 1; $i <= 12; $i++) {
            $reservas = $apartamento->reservas()
                ->whereYear('fecha_entrada', $año)
                ->whereMonth('fecha_entrada', $i)
                ->get();
            
            $datos[] = [
                'mes' => \Carbon\Carbon::create($año, $i, 1)->format('M'),
                'reservas' => $reservas->count(),
                'ingresos' => $reservas->sum('precio')
            ];
        }
        return $datos;
    }

    /**
     * Display apartment statistics in admin panel.
     */
    public function estadisticasAdmin(string $id)
    {
        try {
            $apartamento = Apartamento::with([
                'photos', 
                'tarifas', 
                'edificioRel', 
                'reservas.cliente', 
                'reservas.estado'
            ])->findOrFail($id);

            // Estadísticas generales
            $totalReservas = $apartamento->reservas->count();
            $precioPromedio = $apartamento->reservas->avg('precio') ?? 0;
            $totalIngresos = $apartamento->reservas->sum('precio') ?? 0;
            $totalFotos = $apartamento->photos->count();

            // Estadísticas por mes (últimos 12 meses)
            $estadisticasMensuales = [];
            for ($i = 11; $i >= 0; $i--) {
                $fecha = now()->subMonths($i);
                $mes = $fecha->format('M Y');
                
                $reservasMes = $apartamento->reservas()
                    ->whereYear('fecha_entrada', $fecha->year)
                    ->whereMonth('fecha_entrada', $fecha->month)
                    ->get();
                
                $estadisticasMensuales[$mes] = [
                    'reservas' => $reservasMes->count(),
                    'ingresos' => $reservasMes->sum('precio'),
                    'promedio' => $reservasMes->avg('precio') ?? 0
                ];
            }

            // Estadísticas por estado de reserva
            $estadosReservas = $apartamento->reservas
                ->groupBy('estado.nombre')
                ->map(function ($reservas) {
                    return $reservas->count();
                })
                ->toArray();

            // Top clientes
            $topClientes = $apartamento->reservas
                ->groupBy('cliente.nombre')
                ->map(function ($reservas) {
                    return [
                        'nombre' => $reservas->first()->cliente->nombre ?? 'Sin nombre',
                        'total_reservas' => $reservas->count(),
                        'total_gastado' => $reservas->sum('precio'),
                        'ultima_reserva' => $reservas->max('fecha_entrada')
                    ];
                })
                ->sortByDesc('total_gastado')
                ->take(10)
                ->values()
                ->toArray();

            // Estadísticas de ocupación por día de la semana
            $ocupacionSemanal = [];
            $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
            
            foreach ($diasSemana as $index => $dia) {
                $reservasDia = $apartamento->reservas()
                    ->whereRaw('WEEKDAY(fecha_entrada) = ?', [$index])
                    ->get();
                
                $ocupacionSemanal[$dia] = [
                    'reservas' => $reservasDia->count(),
                    'porcentaje' => $totalReservas > 0 ? round(($reservasDia->count() / $totalReservas) * 100, 1) : 0
                ];
            }
            
            // Convertir a array para Chart.js
            $ocupacionSemanal = array_map(function($item) {
                return [
                    'reservas' => $item['reservas'],
                    'porcentaje' => $item['porcentaje']
                ];
            }, $ocupacionSemanal);

            // Estadísticas de temporada
            $reservasTemporadaAlta = $apartamento->reservas()
                ->whereMonth('fecha_entrada', '>=', 6)
                ->whereMonth('fecha_entrada', '<=', 9)
                ->get();
            
            $reservasTemporadaBaja = $apartamento->reservas()
                ->where(function($query) {
                    $query->whereMonth('fecha_entrada', '<=', 3)
                          ->orWhereMonth('fecha_entrada', '>=', 10);
                })
                ->get();

            $estadisticasTemporada = [
                'alta' => [
                    'reservas' => $reservasTemporadaAlta->count(),
                    'ingresos' => $reservasTemporadaAlta->sum('precio'),
                    'promedio' => $reservasTemporadaAlta->avg('precio') ?? 0
                ],
                'baja' => [
                    'reservas' => $reservasTemporadaBaja->count(),
                    'ingresos' => $reservasTemporadaBaja->sum('precio'),
                    'promedio' => $reservasTemporadaBaja->avg('precio') ?? 0
                ]
            ];

            return view('admin.apartamentos.estadisticas', compact(
                'apartamento',
                'totalReservas',
                'precioPromedio',
                'totalIngresos',
                'totalFotos',
                'estadisticasMensuales',
                'estadosReservas',
                'topClientes',
                'ocupacionSemanal',
                'estadisticasTemporada'
            ));

        } catch (\Exception $e) {
            return redirect()->route('apartamentos.admin.index')
                ->with('swal_error', 'Error al cargar estadísticas: ' . $e->getMessage());
        }
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
        try {
            $apartamento = Apartamento::findOrFail($id);
            
            // Log the deletion
            $this->logDelete('APARTAMENTO', $id, $apartamento->toArray());
            
            $apartamento->delete();

            return redirect()->route('apartamentos.admin.index')
                ->with('swal_success', '¡Apartamento eliminado exitosamente!');
        } catch (\Exception $e) {
            // Log the error
            $this->logError('Error al eliminar apartamento', [
                'apartamento_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('swal_error', 'Error al eliminar el apartamento: ' . $e->getMessage());
        }
    }
}
