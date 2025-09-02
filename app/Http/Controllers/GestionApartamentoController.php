<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\ApartamentoLimpieza;
use App\Models\Fichaje;
use App\Models\Pausa;
use App\Models\GestionApartamento;
use App\Models\LimpiezaFondo;
use App\Models\Reserva;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Auth; // Añade esta línea
use App\Models\Checklist;
use App\Models\ApartamentoLimpiezaItem;
use App\Services\AlertService;

class GestionApartamentoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */


    public function index()
    {
        $reservasPendientes = Reserva::apartamentosPendiente();
        
        // Cargar la relación siguienteReserva con campos de niños para cada reserva pendiente
        foreach ($reservasPendientes as $reserva) {
            if (!$reserva->limpieza_fondo) {
                // Solo para reservas reales, no para limpiezas de fondo
                // Verificar que la relación existe antes de cargarla
                if (method_exists($reserva, 'siguienteReserva')) {
                    try {
                        $reserva->load(['siguienteReserva' => function($query) {
                            $query->select('id', 'apartamento_id', 'fecha_entrada', 'fecha_salida', 'numero_personas', 'numero_ninos', 'edades_ninos', 'notas_ninos', 'codigo_reserva');
                        }]);
                    } catch (Exception $e) {
                        // Si hay error al cargar la relación, continuar sin ella
                        $reserva->siguienteReserva = null;
                    }
                }
                
                // Obtener manualmente la reserva que entra hoy si es la misma fecha
                try {
                    $reservaEntraHoy = \App\Models\Reserva::where('apartamento_id', $reserva->apartamento_id)
                        ->where('fecha_entrada', $reserva->fecha_salida)
                        ->where('id', '!=', $reserva->id)
                        ->where(function($query) {
                            $query->where('estado_id', '!=', 4)
                                  ->orWhereNull('estado_id');
                        })
                        ->select('id', 'apartamento_id', 'fecha_entrada', 'fecha_salida', 'numero_personas', 'numero_ninos', 'edades_ninos', 'notas_ninos', 'codigo_reserva')
                        ->first();
                    
                    if ($reservaEntraHoy) {
                        $reserva->reserva_entra_hoy = $reservaEntraHoy;
                    }
                } catch (Exception $e) {
                    // Si hay error en la consulta, continuar sin esta información
                    $reserva->reserva_entra_hoy = null;
                }
            }
        }
        
        $reservasOcupados = Reserva::apartamentosOcupados();
        $reservasSalida = Reserva::apartamentosSalida();
        // $reservasLimpieza = Reserva::apartamentosLimpiados();
        $reservasLimpieza = ApartamentoLimpieza::apartamentosLimpiados()->with(['apartamento', 'zonaComun'])->get();
        $reservasEnLimpieza = ApartamentoLimpieza::apartamentosEnLimpiados()->with(['apartamento', 'zonaComun'])->get();

        // Obtener información de la siguiente reserva para las limpiezas en proceso
        foreach ($reservasEnLimpieza as $limpieza) {
            try {
                // Buscar la siguiente reserva para este apartamento
                $siguienteReserva = \App\Models\Reserva::where('apartamento_id', $limpieza->apartamento_id)
                    ->where('fecha_entrada', '>', now()->toDateString())
                    ->where(function($query) {
                        $query->where('estado_id', '!=', 4)
                              ->orWhereNull('estado_id');
                    })
                    ->orderBy('fecha_entrada', 'asc')
                    ->select('id', 'apartamento_id', 'fecha_entrada', 'fecha_salida', 'numero_personas', 'numero_ninos', 'edades_ninos', 'notas_ninos', 'codigo_reserva')
                    ->first();
                
                if ($siguienteReserva) {
                    $limpieza->siguiente_reserva = $siguienteReserva;
                }
                
                // También buscar si hay una reserva que entra hoy
                $reservaEntraHoy = \App\Models\Reserva::where('apartamento_id', $limpieza->apartamento_id)
                    ->where('fecha_entrada', now()->toDateString())
                    ->where(function($query) {
                        $query->where('estado_id', '!=', 4)
                              ->orWhereNull('estado_id');
                    })
                    ->select('id', 'apartamento_id', 'fecha_entrada', 'fecha_salida', 'numero_personas', 'numero_ninos', 'edades_ninos', 'notas_ninos', 'codigo_reserva')
                    ->first();
                
                if ($reservaEntraHoy) {
                    $limpieza->reserva_entra_hoy = $reservaEntraHoy;
                }
            } catch (Exception $e) {
                // Si hay error, continuar sin esta información
                $limpieza->siguiente_reserva = null;
                $limpieza->reserva_entra_hoy = null;
            }
        }

        // Obtener información de la siguiente reserva para las limpiezas completadas
        foreach ($reservasLimpieza as $limpieza) {
            try {
                // Buscar la siguiente reserva para este apartamento
                $siguienteReserva = \App\Models\Reserva::where('apartamento_id', $limpieza->apartamento_id)
                    ->where('fecha_entrada', '>', now()->toDateString())
                    ->where(function($query) {
                        $query->where('estado_id', '!=', 4)
                              ->orWhereNull('estado_id');
                    })
                    ->orderBy('fecha_entrada', 'asc')
                    ->select('id', 'apartamento_id', 'fecha_entrada', 'fecha_salida', 'numero_personas', 'numero_ninos', 'edades_ninos', 'notas_ninos', 'codigo_reserva')
                    ->first();
                
                if ($siguienteReserva) {
                    $limpieza->siguiente_reserva = $siguienteReserva;
                }
                
                // También buscar si hay una reserva que entra hoy
                $reservaEntraHoy = \App\Models\Reserva::where('apartamento_id', $limpieza->apartamento_id)
                    ->where('fecha_entrada', now()->toDateString())
                    ->where(function($query) {
                        $query->where('estado_id', '!=', 4)
                              ->orWhereNull('estado_id');
                    })
                    ->select('id', 'apartamento_id', 'fecha_entrada', 'fecha_salida', 'numero_personas', 'numero_ninos', 'edades_ninos', 'notas_ninos', 'codigo_reserva')
                    ->first();
                
                if ($reservaEntraHoy) {
                    $limpieza->reserva_entra_hoy = $reservaEntraHoy;
                }
            } catch (Exception $e) {
                // Si hay error, continuar sin esta información
                $limpieza->siguiente_reserva = null;
                $limpieza->reserva_entra_hoy = null;
            }
        }

        // Obtener apartamentos previstos para mañana (los que SALEN mañana para limpiar)
        $reservasManana = Reserva::where('fecha_salida', now()->addDay()->toDateString())
            ->where(function($query) {
                $query->where('estado_id', '!=', 4)
                      ->orWhereNull('estado_id');
            })
            ->with(['apartamento'])
            ->orderBy('apartamento_id')
            ->get();

        // Para cada apartamento que sale mañana, obtener información de la siguiente reserva
        foreach ($reservasManana as $reserva) {
            try {
                // Buscar la siguiente reserva para este apartamento
                $siguienteReserva = \App\Models\Reserva::where('apartamento_id', $reserva->apartamento_id)
                    ->where('fecha_entrada', '>', $reserva->fecha_salida)
                    ->where(function($query) {
                        $query->where('estado_id', '!=', 4)
                              ->orWhereNull('estado_id');
                    })
                    ->orderBy('fecha_entrada', 'asc')
                    ->select('id', 'apartamento_id', 'fecha_entrada', 'fecha_salida', 'numero_personas', 'numero_ninos', 'edades_ninos', 'notas_ninos', 'codigo_reserva')
                    ->first();
                
                if ($siguienteReserva) {
                    $reserva->siguiente_reserva = $siguienteReserva;
                }
                
                // También buscar si hay una reserva que entra mañana mismo
                $reservaEntraManana = \App\Models\Reserva::where('apartamento_id', $reserva->apartamento_id)
                    ->where('fecha_entrada', now()->addDay()->toDateString())
                    ->where(function($query) {
                        $query->where('estado_id', '!=', 4)
                              ->orWhereNull('estado_id');
                    })
                    ->select('id', 'apartamento_id', 'fecha_entrada', 'fecha_salida', 'numero_personas', 'numero_ninos', 'edades_ninos', 'notas_ninos', 'codigo_reserva')
                    ->first();
                
                if ($reservaEntraManana) {
                    $reserva->reserva_entra_manana = $reservaEntraManana;
                }
            } catch (Exception $e) {
                // Si hay error, continuar sin esta información
                $reserva->siguiente_reserva = null;
                $reserva->reserva_entra_manana = null;
            }
        }

        // Obtener zonas comunes activas que NO estén EN PROCESO de limpieza
        // Las zonas ya limpiadas hoy SÍ pueden aparecer para limpiar de nuevo
        $zonasComunesIdsEnLimpieza = $reservasEnLimpieza->pluck('zona_comun_id')->filter()->toArray();
        
        $zonasComunes = \App\Models\ZonaComun::activas()
            ->ordenadas()
            ->whereNotIn('id', $zonasComunesIdsEnLimpieza) // Solo excluir las EN PROCESO
            ->get();

        $hoy = now()->toDateString();
        $limpiezaFondo = LimpiezaFondo::whereDate('fecha', $hoy)->get();

        // Obtener amenities de consumo para todas las secciones
        $amenities = \App\Models\Amenity::activos()
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get()
            ->groupBy('categoria');

        // Obtener consumos existentes para todas las limpiezas de hoy
        $limpiezaIds = collect([$reservasPendientes, $reservasEnLimpieza, $reservasLimpieza])
            ->flatten()
            ->pluck('id')
            ->filter()
            ->toArray();

        $consumosExistentes = \App\Models\AmenityConsumo::whereIn('limpieza_id', $limpiezaIds)
            ->with('amenity')
            ->get()
            ->groupBy('limpieza_id');

        return view('gestion.index', compact(
            'reservasPendientes',
            'reservasOcupados',
            'reservasSalida',
            'reservasLimpieza',
            'reservasEnLimpieza', 
            'limpiezaFondo', 
            'zonasComunes', 
            'reservasManana',
            'amenities',
            'consumosExistentes'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create_fondo($id)
    {

        $apartamentoLimpio = ApartamentoLimpieza::where('fecha_fin', null)
            ->where('apartamento_id', explode(' - ', $id)[1])
            ->first();
        $reserva = Reserva::find($id);
            if ($reserva == null) {
                $apartamentoId = explode(' - ', $id)[1];
                $id = null;
            } else {
                $apartamentoId = $reserva->apartamento_id;
            }
            if ($apartamentoLimpio == null) {
                $apartamentoLimpieza = ApartamentoLimpieza::create([
                    'apartamento_id' => $apartamentoId,
                    'fecha_comienzo' => Carbon::now(),
                    'status_id' => 2,
                    'reserva_id' => $id,
                    'user_id' => Auth::user()->id
                ]);
                $apartamentoLimpieza->save();
                if ($reserva != null) {
                    $reserva->fecha_limpieza = Carbon::now();
                    $reserva->save();
                }
            } else {
                $apartamentoLimpieza = $apartamentoLimpio;
            }




        // Verificar que el apartamento existe
        $apartamento = Apartamento::find($apartamentoId);
        if (!$apartamento) {
            abort(404, 'Apartamento no encontrado');
        }
        
        $edificioId = $apartamento->edificio_id;
        
        // Verificar que el edificio existe
        if (!$edificioId) {
            abort(404, 'Edificio no encontrado para este apartamento');
        }

        $checklists = Checklist::with('items')->where('edificio_id', $edificioId)->get();
        $item_check = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)->get();
        $itemsExistentes = $item_check->pluck('estado', 'item_id')->toArray();

        // Obtener amenities para esta limpieza
        $amenities = \App\Models\Amenity::activos()
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get()
            ->groupBy('categoria');
        
        // Obtener consumos existentes para esta limpieza
        $consumosExistentes = \App\Models\AmenityConsumo::where('limpieza_id', $apartamentoLimpieza->id)
            ->with('amenity')
            ->get()
            ->keyBy('amenity_id');
        
        // Calcular cantidades recomendadas para cada amenity
        $amenitiesConRecomendaciones = [];
        foreach ($amenities as $categoria => $amenitiesCategoria) {
            foreach ($amenitiesCategoria as $amenity) {
                $cantidadRecomendada = $this->calcularCantidadRecomendadaAmenity($amenity, $reserva, $apartamentoLimpieza->apartamento);
                $consumoExistente = $consumosExistentes->get($amenity->id);
                
                $amenitiesConRecomendaciones[$categoria][] = [
                    'amenity' => $amenity,
                    'cantidad_recomendada' => $cantidadRecomendada,
                    'consumo_existente' => $consumoExistente,
                    'stock_disponible' => $amenity->stock_actual
                ];
            }
        }

        return view('gestion.edit', compact('apartamentoLimpieza', 'id', 'checklists', 'itemsExistentes', 'amenitiesConRecomendaciones', 'consumosExistentes'));
    }

    public function create($id)
    {
        if (isset(explode(' - ', $id)[1])) {
            return redirect()->route('gestion.create_fondo', $id);
        } else {

        $reserva = Reserva::find($id);
        if (!$reserva) {
            Alert::error('Error', 'Reserva no encontrada');
            return redirect()->route('gestion.index');
        }

        $apartamentoLimpio = ApartamentoLimpieza::where('fecha_fin', null)
            ->where('apartamento_id', $reserva->apartamento_id)
            ->first();

        if ($apartamentoLimpio == null) {
            $apartamentoLimpieza = ApartamentoLimpieza::create([
                'apartamento_id' => $reserva->apartamento_id,
                'fecha_comienzo' => Carbon::now(),
                'status_id' => 2,
                'reserva_id' => $id,
                'user_id' => Auth::user()->id
            ]);
            $reserva->fecha_limpieza = Carbon::now();
            $reserva->save();
        } else {
            $apartamentoLimpieza = $apartamentoLimpio;
        }
        $apartamentoId = $reserva->apartamento_id;

        // Verificar que el apartamento existe
        $apartamento = Apartamento::find($apartamentoId);
        if (!$apartamento) {
            abort(404, 'Apartamento no encontrado');
        }
        
        $edificioId = $apartamento->edificio_id;
        
        // Verificar que el edificio existe
        if (!$edificioId) {
            abort(404, 'Edificio no encontrado para este apartamento');
        }

        $checklists = Checklist::with('items')->where('edificio_id', $edificioId)->get();
        $item_check = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)->get();
        $itemsExistentes = $item_check->pluck('estado', 'item_id')->toArray();

        // Obtener amenities para esta limpieza
        $amenities = \App\Models\Amenity::activos()
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get()
            ->groupBy('categoria');
        
        // Obtener consumos existentes para esta limpieza
        $consumosExistentes = \App\Models\AmenityConsumo::where('limpieza_id', $apartamentoLimpieza->id)
            ->with('amenity')
            ->get()
            ->keyBy('amenity_id');
        
        // Calcular cantidades recomendadas para cada amenity
        $amenitiesConRecomendaciones = [];
        foreach ($amenities as $categoria => $amenitiesCategoria) {
            foreach ($amenitiesCategoria as $amenity) {
                $cantidadRecomendada = $this->calcularCantidadRecomendadaAmenity($amenity, $reserva, $apartamentoLimpieza->apartamento);
                $consumoExistente = $consumosExistentes->get($amenity->id);
                
                $amenitiesConRecomendaciones[$categoria][] = [
                    'amenity' => $amenity,
                    'cantidad_recomendada' => $cantidadRecomendada,
                    'consumo_existente' => $consumoExistente,
                    'stock_disponible' => $amenity->stock_actual
                ];
            }
        }

        return view('gestion.edit', compact(
            'apartamentoLimpieza', 
            'id', 
            'checklists', 
            'itemsExistentes',
            'amenitiesConRecomendaciones',
            'consumosExistentes'
        ));
    }
    }


    public function store(Request $request)
    {
        $id = $request->id;
        $apartamento = ApartamentoLimpieza::find($id);

        if (!$apartamento) {
            Alert::error('Error', 'Apartamento no encontrado');
            return redirect()->route('gestion.index');
        }

        // Eliminar registros anteriores para este apartamento y limpieza
        ApartamentoLimpiezaItem::where('id_limpieza', $apartamento->id)->delete();

        // Guardar los nuevos ítems marcados en el formulario
        if ($request->has('items')) {
            foreach ($request->items as $itemId => $estado) {
                ApartamentoLimpiezaItem::create([
                    'id_limpieza' => $apartamento->id,
                    'id_reserva' => $apartamento->reserva_id,
                    'item_id' => $itemId,
                    'estado' => $estado == 1 ? 1 : 0,
                ]);
            }
            foreach ($request->checklist as $checklistId => $estado) {
                ApartamentoLimpiezaItem::create([
                    'id_limpieza' => $apartamento->id,
                    'id_reserva' => $apartamento->reserva_id,
                    'estado' => $estado == 1 ? 1 : 0,
                    'checklist_id' => $checklistId
                ]);
            }
        }

        // Guardar observación
        $apartamento->observacion = $request->observacion;

        // Asignar el usuario si no existe
        if (empty($apartamento->user_id)) {
            $apartamento->user_id = Auth::user()->id;
        }

        $apartamento->save();

        Alert::success('Guardado con Éxito', 'Apartamento actualizado correctamente');
        return redirect()->route('gestion.index');
    }


    /**
     * Display the specified resource.
     */
    public function storeColumn(Request $request)
    {
        $apartamento = ApartamentoLimpieza::find($request->id);

        if ($apartamento) {
            $columna = $request->name;
            $apartamento->$columna = $request->checked == 'true' ? true : false;
            $apartamento->save();
            Alert::toast('Actualizado', 'success');
            return true;

        }
        Alert::toast('Error, intentelo mas tarde', 'error');

        return false;
    }

    /**
     * Display the specified resource.
     */
    public function show(GestionApartamento $gestionApartamento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ApartamentoLimpieza $apartamentoLimpieza)
    {
        // Verificar que el apartamentoLimpieza existe y tiene los datos necesarios
        if (!$apartamentoLimpieza) {
            abort(404, 'Limpieza no encontrada');
        }

        $id = $apartamentoLimpieza->id;
        
        // Debug temporal - ver en consola del navegador
        if (app()->environment('local')) {
            error_log("Debug ApartamentoLimpieza ID: {$apartamentoLimpieza->id}, Tipo: {$apartamentoLimpieza->tipo_limpieza}, ZonaComunID: {$apartamentoLimpieza->zona_comun_id}, ApartamentoID: {$apartamentoLimpieza->apartamento_id}");
        }
        
        // Determinar si es una zona común o un apartamento
        if ($apartamentoLimpieza->tipo_limpieza === 'zona_comun') {
            // Es una zona común
            $zonaComun = $apartamentoLimpieza->zonaComun;
            if (!$zonaComun) {
                abort(404, 'Zona común no encontrada');
            }
            
            // Obtener checklists específicos para zonas comunes
            $checklists = \App\Models\ChecklistZonaComun::activos()->ordenados()->with('items')->get();
            
            // Obtener items marcados para esta limpieza
            $item_check = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)->get();
            $itemsExistentes = $item_check->pluck('estado', 'item_id')->toArray();
            $checklist_check = $item_check->whereNotNull('checklist_zona_comun_id')->filter(function ($item) {
                return $item->estado == 1;
            });
            $checklistsExistentes = $checklist_check->pluck('estado', 'checklist_zona_comun_id')->toArray();
            
            return view('gestion.edit-zona-comun', compact(
                'apartamentoLimpieza',
                'zonaComun',
                'id', 
                'checklists', 
                'itemsExistentes', 
                'checklistsExistentes'
            ));
            
        } else {
            // Es un apartamento
            $apartamentoId = $apartamentoLimpieza->apartamento_id;
            
            // Verificar que el apartamento existe
            $apartamento = Apartamento::find($apartamentoId);
            if (!$apartamento) {
                abort(404, 'Apartamento no encontrado');
            }
            
            $edificioId = $apartamento->edificio_id;
            
            // Verificar que el edificio existe
            if (!$edificioId) {
                abort(404, 'Edificio no encontrado para este apartamento');
            }

            $checklists = Checklist::with('items')->where('edificio_id', $edificioId)->get();
            $item_check = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)->get();
            $itemsExistentes = $item_check->pluck('estado', 'item_id')->toArray();
            $checklist_check = $item_check->whereNotNull('checklist_id')->filter(function ($item) {
                return $item->estado == 1;
            });

            $checklistsExistentes = $checklist_check->pluck('estado', 'checklist_id')->toArray();
            
            // Obtener amenities para esta limpieza
            $amenities = \App\Models\Amenity::activos()
                ->orderBy('categoria')
                ->orderBy('nombre')
                ->get()
                ->groupBy('categoria');
            
            // Obtener consumos existentes para esta limpieza
            $consumosExistentes = \App\Models\AmenityConsumo::where('limpieza_id', $apartamentoLimpieza->id)
                ->with('amenity')
                ->get()
                ->keyBy('amenity_id');
            
            // Calcular cantidades recomendadas para cada amenity
            $amenitiesConRecomendaciones = [];
            foreach ($amenities as $categoria => $amenitiesCategoria) {
                foreach ($amenitiesCategoria as $amenity) {
                    $cantidadRecomendada = $this->calcularCantidadRecomendadaAmenity($amenity, $apartamentoLimpieza->origenReserva, $apartamentoLimpieza->apartamento);
                    $consumoExistente = $consumosExistentes->get($amenity->id);
                    
                    $amenitiesConRecomendaciones[$categoria][] = [
                        'amenity' => $amenity,
                        'cantidad_recomendada' => $cantidadRecomendada,
                        'consumo_existente' => $consumoExistente,
                        'stock_disponible' => $amenity->stock_actual
                    ];
                }
            }

            // Añadir amenities automáticos para niños si la siguiente reserva tiene niños
            $siguienteReserva = $this->obtenerSiguienteReserva($apartamento->id);
            if ($siguienteReserva && $siguienteReserva->numero_ninos > 0) {
                $amenitiesNinos = \App\Models\Amenity::paraNinos()->activos()->get();
                
                foreach ($amenitiesNinos as $amenityNino) {
                    $cantidadParaNinos = $amenityNino->calcularCantidadParaNinos($siguienteReserva->numero_ninos, $siguienteReserva->edades_ninos ?? []);
                    
                    if ($cantidadParaNinos > 0) {
                        $categoria = $amenityNino->categoria;
                        if (!isset($amenitiesConRecomendaciones[$categoria])) {
                            $amenitiesConRecomendaciones[$categoria] = [];
                        }
                        
                        // Verificar si ya existe este amenity
                        $existe = false;
                        foreach ($amenitiesConRecomendaciones[$categoria] as $amenityExistente) {
                            if ($amenityExistente['amenity']->id === $amenityNino->id) {
                                $amenityExistente['cantidad_recomendada'] += $cantidadParaNinos;
                                $amenityExistente['es_automatico_ninos'] = true;
                                $amenityExistente['motivo_ninos'] = "Automático para {$siguienteReserva->numero_ninos} niño(s)";
                                $existe = true;
                                break;
                            }
                        }
                        
                        if (!$existe) {
                            $consumoExistente = $consumosExistentes->get($amenityNino->id);
                            $amenitiesConRecomendaciones[$categoria][] = [
                                'amenity' => $amenityNino,
                                'cantidad_recomendada' => $cantidadParaNinos,
                                'consumo_existente' => $consumoExistente,
                                'stock_disponible' => $amenityNino->stock_actual,
                                'es_automatico_ninos' => true,
                                'motivo_ninos' => "Automático para {$siguienteReserva->numero_ninos} niño(s)"
                            ];
                        }
                    }
                }
            }
            
            // Obtener mensaje de amenities del session flash si existe
            $mensajeAmenities = session('mensajeAmenities');
            
            return view('gestion.edit', compact(
                'apartamentoLimpieza',
                'id', 
                'checklists', 
                'itemsExistentes', 
                'checklistsExistentes',
                'amenitiesConRecomendaciones',
                'consumosExistentes',
                'mensajeAmenities'
            ));
        }
    }

    /**
     * Obtener la siguiente reserva para un apartamento
     */
    private function obtenerSiguienteReserva($apartamentoId)
    {
        return \App\Models\Reserva::where('apartamento_id', $apartamentoId)
            ->where('fecha_entrada', '>', now()->toDateString())
            ->where(function($query) {
                $query->where('estado_id', '!=', 4)
                      ->orWhereNull('estado_id');
            })
            ->orderBy('fecha_entrada', 'asc')
            ->select('id', 'apartamento_id', 'fecha_entrada', 'fecha_salida', 'numero_personas', 'numero_ninos', 'edades_ninos', 'notas_ninos', 'codigo_reserva')
            ->first();
    }


    public function update(Request $request, ApartamentoLimpieza $apartamentoLimpieza)
{
    // Eliminar ítems anteriores para este registro
    ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)->delete();

    // Guardar nuevos ítems desde los checkboxes de ítems
    if ($request->has('items')) {
        foreach ($request->items as $itemId => $estado) {
            ApartamentoLimpiezaItem::create([
                'id_limpieza'   => $apartamentoLimpieza->id,
                'id_reserva'    => $apartamentoLimpieza->reserva_id,
                'item_id'       => $itemId,
                'estado'        => $estado == 1 ? 1 : 0,
            ]);
        }
    }

    // Guardar nuevos ítems desde los checkboxes de checklist
    if ($request->has('checklist')) {
        foreach ($request->checklist as $checklistId => $estado) {
            ApartamentoLimpiezaItem::create([
                'id_limpieza'   => $apartamentoLimpieza->id,
                'id_reserva'    => $apartamentoLimpieza->reserva_id,
                'checklist_id'  => $checklistId,
                'estado'        => $estado == 1 ? 1 : 0,
            ]);
        }
    }

    // Guardar amenities de consumo
    if ($request->has('amenities')) {
        // Debug: Log de los datos recibidos
        Log::info('Amenities recibidos:', $request->amenities);
        
        $amenitiesGuardados = 0;
        $amenitiesCreados = 0;
        $amenitiesActualizados = 0;
        
        foreach ($request->amenities as $amenityId => $amenityData) {
            try {
                // Validar datos antes de insertar
                $cantidadDejada = intval($amenityData['cantidad_dejada'] ?? 0);
                $observaciones = $amenityData['observaciones'] ?? null;
                
                // Solo procesar si hay cantidad dejada
                if ($cantidadDejada > 0) {
                    // Buscar si ya existe un consumo para este amenity
                    $consumoExistente = \App\Models\AmenityConsumo::where('limpieza_id', $apartamentoLimpieza->id)
                        ->where('amenity_id', $amenityId)
                        ->first();
                    
                    if ($consumoExistente) {
                        // ACTUALIZAR el consumo existente
                        $cantidadAnterior = $consumoExistente->cantidad_consumida;
                        $diferencia = $cantidadDejada - $cantidadAnterior;
                        
                        $consumoExistente->update([
                            'cantidad_consumida' => $cantidadDejada,
                            'cantidad_anterior' => $cantidadAnterior,
                            'cantidad_actual' => $consumoExistente->cantidad_actual + $diferencia,
                            'observaciones' => $observaciones,
                            'fecha_consumo' => now()
                        ]);
                        
                        // Actualizar el stock del amenity usando el método del modelo
                        $amenity = \App\Models\Amenity::find($amenityId);
                        if ($amenity) {
                            \Log::info("ANTES de ajustar stock - Amenity {$amenityId}: stock_actual = {$amenity->stock_actual}");
                            
                            // Siempre ajustar el stock basado en la diferencia
                            $amenity->ajustarStock($cantidadAnterior, $cantidadDejada);
                            
                            \Log::info("DESPUÉS de ajustar stock - Amenity {$amenityId}: stock_actual = {$amenity->stock_actual}");
                            \Log::info("Stock del amenity {$amenityId} ajustado: diferencia {$diferencia} (de {$cantidadAnterior} a {$cantidadDejada})");
                            
                            // Verificar si el stock está bajo después del ajuste
                            if ($amenity->verificarStockBajo()) {
                                Alert::warning('Stock Bajo', "El amenity '{$amenity->nombre}' tiene stock bajo (actual: {$amenity->stock_actual})");
                            }
                        } else {
                            \Log::error("No se pudo encontrar el amenity {$amenityId} para actualizar stock");
                        }
                        
                        $amenitiesActualizados++;
                        \Log::info("Amenity {$amenityId} ACTUALIZADO con cantidad {$cantidadDejada}");
                    } else {
                        // CREAR nuevo consumo solo si no existe
                        \App\Models\AmenityConsumo::create([
                            'amenity_id' => $amenityId,
                            'limpieza_id' => $apartamentoLimpieza->id,
                            'reserva_id' => $apartamentoLimpieza->reserva_id,
                            'apartamento_id' => $apartamentoLimpieza->apartamento_id,
                            'user_id' => auth()->id(),
                            'tipo_consumo' => 'limpieza',
                            'cantidad_consumida' => $cantidadDejada,
                            'cantidad_anterior' => 0,
                            'cantidad_actual' => $cantidadDejada,
                            'costo_unitario' => 0,
                            'costo_total' => 0,
                            'observaciones' => $observaciones,
                            'fecha_consumo' => now()
                        ]);
                        
                        // Actualizar el stock del amenity usando el método del modelo
                        $amenity = \App\Models\Amenity::find($amenityId);
                        if ($amenity) {
                            \Log::info("ANTES de descontar stock - Amenity {$amenityId}: stock_actual = {$amenity->stock_actual}");
                            $amenity->descontarStock($cantidadDejada);
                            \Log::info("DESPUÉS de descontar stock - Amenity {$amenityId}: stock_actual = {$amenity->stock_actual}");
                            \Log::info("Stock del amenity {$amenityId} descontado: -{$cantidadDejada} (nuevo consumo)");
                            
                            // Verificar si el stock está bajo después del descuento
                            if ($amenity->verificarStockBajo()) {
                                Alert::warning('Stock Bajo', "El amenity '{$amenity->nombre}' tiene stock bajo (actual: {$amenity->stock_actual})");
                            }
                        } else {
                            \Log::error("No se pudo encontrar el amenity {$amenityId} para descontar stock");
                        }
                        
                        $amenitiesCreados++;
                        \Log::info("Amenity {$amenityId} CREADO con cantidad {$cantidadDejada}");
                    }
                    
                    $amenitiesGuardados++;
                }
            } catch (\Exception $e) {
                \Log::error("Error guardando amenity {$amenityId}: " . $e->getMessage());
                \Log::error("Datos del amenity: " . json_encode($amenityData));
            }
        }
        
        if ($amenitiesGuardados > 0) {
            $mensaje = "Se han procesado {$amenitiesGuardados} amenities: ";
            if ($amenitiesCreados > 0) {
                $mensaje .= "{$amenitiesCreados} creados";
            }
            if ($amenitiesActualizados > 0) {
                if ($amenitiesCreados > 0) $mensaje .= ", ";
                $mensaje .= "{$amenitiesActualizados} actualizados";
            }
            $mensaje .= " correctamente";
            
            // En lugar de Alert::success, pasamos el mensaje a la vista
            $mensajeAmenities = $mensaje;
        }
    }

    // Guardar observación
    $apartamentoLimpieza->observacion = $request->observacion;
    $apartamentoLimpieza->save();

    $id = $apartamentoLimpieza->id;
    Alert::success('Guardado con Éxito', 'Apartamento actualizado correctamente');

    $apartamentoId = $apartamentoLimpieza->apartamento_id;
    
    // Verificar que el apartamento existe
    $apartamento = Apartamento::find($apartamentoId);
    if (!$apartamento) {
        abort(404, 'Apartamento no encontrado');
    }
    
    $edificioId = $apartamento->edificio_id;
    
    // Verificar que el edificio existe
    if (!$edificioId) {
        abort(404, 'Edificio no encontrado para este apartamento');
    }

    $checklists = Checklist::with('items')->where('edificio_id', $edificioId)->get();
    $item_check = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)->get();

    $itemsExistentes = $item_check->pluck('estado', 'item_id')->toArray();
    $checklist_check = $item_check->whereNotNull('checklist_id')->filter(function ($item) {
        return $item->estado == 1;
    });
    $checklistsExistentes = $checklist_check->pluck('estado', 'checklist_id')->toArray();

    // Obtener amenities para esta limpieza
    $amenities = \App\Models\Amenity::activos()
        ->orderBy('categoria')
        ->orderBy('nombre')
        ->get()
        ->groupBy('categoria');
    
    // Obtener consumos existentes para esta limpieza
    $consumosExistentes = \App\Models\AmenityConsumo::where('limpieza_id', $apartamentoLimpieza->id)
        ->with('amenity')
        ->get()
        ->keyBy('amenity_id');
    
    // Calcular cantidades recomendadas para cada amenity
    $amenitiesConRecomendaciones = [];
    foreach ($amenities as $categoria => $amenitiesCategoria) {
        foreach ($amenitiesCategoria as $amenity) {
            $cantidadRecomendada = $this->calcularCantidadRecomendadaAmenity($amenity, $apartamentoLimpieza->origenReserva, $apartamentoLimpieza->apartamento);
            $consumoExistente = $consumosExistentes->get($amenity->id);
            
            $amenitiesConRecomendaciones[$categoria][] = [
                'amenity' => $amenity,
                'cantidad_recomendada' => $cantidadRecomendada,
                'consumo_existente' => $consumoExistente,
                'stock_disponible' => $amenity->stock_actual
            ];
        }
    }

    return redirect()->route('gestion.edit', $apartamentoLimpieza)->with('mensajeAmenities', $mensajeAmenities ?? null);
}

/**
 * Actualizar limpieza de zona común
 */
public function updateZonaComun(Request $request, ApartamentoLimpieza $apartamentoLimpieza)
{
    // Verificar que sea una limpieza de zona común
    if ($apartamentoLimpieza->tipo_limpieza !== 'zona_comun') {
        abort(400, 'Esta función solo es válida para zonas comunes');
    }
    
    // Guardar observación
    $apartamentoLimpieza->observacion = $request->observacion;
    $apartamentoLimpieza->save();
    
    Alert::success('Guardado con Éxito', 'Zona común actualizada correctamente');
    
    return redirect()->route('gestion.edit', $apartamentoLimpieza);
}



    /**
     * Remove the specified resource from storage.
     */
    public function finalizar(Request $request, ApartamentoLimpieza $apartamentoLimpieza)
    {
        // Verificar que todos los checklists estén marcados
        $apartamentoId = $apartamentoLimpieza->apartamento_id;
        
        // Verificar que el apartamento existe
        $apartamento = Apartamento::find($apartamentoId);
        if (!$apartamento) {
            abort(404, 'Apartamento no encontrado');
        }
        
        $edificioId = $apartamento->edificio_id;
        
        // Verificar que el edificio existe
        if (!$edificioId) {
            abort(404, 'Edificio no encontrado para este apartamento');
        }
        
        $checklists = Checklist::where('edificio_id', $edificioId)->get();
        
        // Obtener los checklists marcados
        $checklistsMarcados = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)
            ->whereNotNull('checklist_id')
            ->where('estado', 1)
            ->pluck('checklist_id')
            ->toArray();
        
        // Verificar si faltan checklists por marcar
        $checklistsFaltantes = $checklists->whereNotIn('id', $checklistsMarcados);
        
        // Si faltan checklists y no hay consentimiento, mostrar error
        if ($checklistsFaltantes->count() > 0) {
            $consentimiento = $request->input('consentimiento_finalizacion');
            
            if ($consentimiento !== 'true') {
                $nombresFaltantes = $checklistsFaltantes->pluck('nombre')->implode(', ');
                Alert::error('No se puede finalizar', 'Debes completar todos los checklists antes de finalizar: ' . $nombresFaltantes . ' O marcar el consentimiento de finalización.');
                
                // Redirigir de vuelta al formulario de edición
                return redirect()->route('gestion.edit', $apartamentoLimpieza);
            }
            
            // Si hay consentimiento, guardar la información del consentimiento
            $apartamentoLimpieza->consentimiento_finalizacion = true;
            $apartamentoLimpieza->motivo_consentimiento = $request->input('motivo_consentimiento', 'Usuario confirmó que puede finalizar sin completar todos los checklists');
            $apartamentoLimpieza->fecha_consentimiento = now();
            $apartamentoLimpieza->user_id_consentimiento = auth()->id();
            $apartamentoLimpieza->save();
            
            // Mostrar advertencia pero permitir continuar
            $nombresFaltantes = $checklistsFaltantes->pluck('nombre')->implode(', ');
            Alert::warning('Finalización con Checklists Incompletos', 'Has confirmado que puedes finalizar sin completar todos los checklists. Checklists faltantes: ' . $nombresFaltantes);
        }
        
        $hoy = Carbon::now();
        $apartamentoLimpieza->status_id = 3;
        $apartamentoLimpieza->fecha_fin = $hoy;
        $apartamentoLimpieza->save();
        
        $reserva = Reserva::find($apartamentoLimpieza->reserva_id);
        if ($reserva != null) {
            $reserva->fecha_limpieza = $hoy;
            $reserva->save();
        }

        // DESCUENTO AUTOMÁTICO DE AMENITIES DE LIMPIEZA
        $this->descontarAmenitiesLimpieza($apartamentoLimpieza);

        // Crear alerta si hay observaciones al finalizar la limpieza
        if (!empty($apartamentoLimpieza->observacion)) {
            $apartamentoNombre = $apartamentoLimpieza->apartamento->nombre ?? 'Apartamento';
            AlertService::createCleaningObservationAlert(
                $apartamentoLimpieza->id,
                $apartamentoNombre,
                $apartamentoLimpieza->observacion
            );
        }

        Alert::success('Finalizado con Éxito', 'Apartamento Finalizado correctamente');

        return redirect()->route('gestion.index');
    }
    
    /**
     * Finalizar limpieza de zona común
     */
    public function finalizarZonaComun(ApartamentoLimpieza $apartamentoLimpieza)
    {
        // Verificar que sea una limpieza de zona común
        if ($apartamentoLimpieza->tipo_limpieza !== 'zona_comun') {
            abort(400, 'Esta función solo es válida para zonas comunes');
        }
        
        $hoy = Carbon::now();
        $apartamentoLimpieza->status_id = 3; // Finalizado
        $apartamentoLimpieza->fecha_fin = $hoy;
        $apartamentoLimpieza->save();
        
        // DESCUENTO AUTOMÁTICO DE AMENITIES DE LIMPIEZA
        $this->descontarAmenitiesLimpieza($apartamentoLimpieza);
        
        // Crear alerta si hay observaciones al finalizar la limpieza
        if (!empty($apartamentoLimpieza->observacion)) {
            $zonaComunNombre = $apartamentoLimpieza->zonaComun->nombre ?? 'Zona Común';
            AlertService::createCleaningObservationAlert(
                $apartamentoLimpieza->id,
                $zonaComunNombre,
                $apartamentoLimpieza->observacion
            );
        }
        
        Alert::success('Finalizado con Éxito', 'Zona Común finalizada correctamente');
        
        return redirect()->route('gestion.index');
    }

    public function limpiezaFondo(Request $request) {
        $apartamentos = LimpiezaFondo::all();
        return view('admin.limpieza.index', compact('apartamentos'));

    }

    public function limpiezaFondoDestroy($id) {
        $limpieza = LimpiezaFondo::find($id);
        $limpieza->delete();
        return redirect(route('admin.limpiezaFondo.index'));

    }

    public function limpiezaCreate(Request $request) {
        $apartamentos = Apartamento::all();
        return view('admin.limpieza.create', compact('apartamentos'));

    }
    public function limpiezaFondoEdit($id) {
        $limpieza = LimpiezaFondo::find($id);
        $apartamentos = Apartamento::all();

        return view('admin.limpieza.edit', compact('apartamentos', 'limpieza'));

    }

    public function limpiezaFondoStore(Request $request) {
        $rules = [
            'fecha' => 'required|date',
            'apartamento_id' => 'required'
        ];

        // Validar los datos del formulario
        $validatedData = $request->validate($rules);
        $limpiezaAFondo = LimpiezaFondo::create([
            'apartamento_id' => $request->apartamento_id,
            'fecha' => $request->fecha
        ]);
        Alert::success('Fizalizado con Exito', 'Apartamento Fizalizado correctamente');

        return redirect()->route('admin.limpiezaFondo.index');
    }

    /**
     * Update checkbox state via AJAX
     */
    public function updateCheckbox(Request $request)
    {
        try {
            $type = $request->input('type');
            $id = $request->input('id');
            $checked = $request->input('checked');
            $limpiezaId = $request->input('limpieza_id');

            $apartamentoLimpieza = ApartamentoLimpieza::find($limpiezaId);
            if (!$apartamentoLimpieza) {
                return response()->json(['success' => false, 'message' => 'Limpieza no encontrada'], 404);
            }

            // Siempre usar el reserva_id del registro padre
            $idReserva = $apartamentoLimpieza->reserva_id;

            if ($type === 'checklist') {
                // Actualizar estado del checklist
                $limpiezaItem = ApartamentoLimpiezaItem::where('id_limpieza', $limpiezaId)
                    ->where('checklist_id', $id)
                    ->first();

                if (!$limpiezaItem) {
                    // Si no existe, crear nuevo registro
                    $limpiezaItem = new ApartamentoLimpiezaItem([
                        'id_limpieza' => $limpiezaId,
                        'checklist_id' => $id,
                        'id_reserva' => $idReserva,
                        'estado' => $checked
                    ]);
                } else {
                    $limpiezaItem->estado = $checked;
                }

                $limpiezaItem->save();
            } else if ($type === 'item') {
                // Actualizar estado del item
                $limpiezaItem = ApartamentoLimpiezaItem::where('id_limpieza', $limpiezaId)
                    ->where('item_id', $id)
                    ->first();

                if (!$limpiezaItem) {
                    // Si no existe, crear nuevo registro
                    $limpiezaItem = new ApartamentoLimpiezaItem([
                        'id_limpieza' => $limpiezaId,
                        'item_id' => $id,
                        'id_reserva' => $idReserva,
                        'estado' => $checked
                    ]);
                } else {
                    $limpiezaItem->estado = $checked;
                }

                $limpiezaItem->save();
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get checklist status for AJAX requests
     */
    public function checklistStatus(ApartamentoLimpieza $apartamentoLimpieza)
    {
        $apartamentoId = $apartamentoLimpieza->apartamento_id;
        
        // Verificar que el apartamento existe
        $apartamento = Apartamento::find($apartamentoId);
        if (!$apartamento) {
            return response()->json(['error' => 'Apartamento no encontrado'], 404);
        }
        
        $edificioId = $apartamento->edificio_id;
        
        // Verificar que el edificio existe
        if (!$edificioId) {
            return response()->json(['error' => 'Edificio no encontrado para este apartamento'], 404);
        }
        
        $checklists = Checklist::where('edificio_id', $edificioId)->get();
        
        $checklistsMarcados = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)
            ->whereNotNull('checklist_id')
            ->where('estado', 1)
            ->pluck('checklist_id')
            ->toArray();
        
        $checklistsFaltantes = $checklists->whereNotIn('id', $checklistsMarcados);
        
        return response()->json([
            'total' => $checklists->count(),
            'completados' => count($checklistsMarcados),
            'faltantes' => $checklistsFaltantes->pluck('nombre')->toArray(),
            'puedeFinalizar' => $checklistsFaltantes->count() === 0
        ]);
    }

    /**
     * Editar limpieza de zona común
     */
    public function editZonaComun($id)
    {
        $apartamentoLimpieza = ApartamentoLimpieza::with(['zonaComun', 'empleada', 'status'])
            ->where('id', $id)
            ->where('tipo_limpieza', 'zona_comun')
            ->firstOrFail();
        
        $zonaComun = $apartamentoLimpieza->zonaComun;
        if (!$zonaComun) {
            abort(404, 'Zona común no encontrada');
        }
        
        // Obtener checklists para zonas comunes
        $checklists = \App\Models\ChecklistZonaComun::activos()->ordenados()->get();
        
        // Obtener items existentes para esta limpieza
        $item_check = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)->get();
        $itemsExistentes = $item_check->pluck('estado', 'item_id')->toArray();
        
        // Obtener checklists marcados
        $checklist_check = $item_check->whereNotNull('checklist_zona_comun_id')->filter(function ($item) {
            return $item->estado == 1;
        });
        $checklistsExistentes = $checklist_check->pluck('estado', 'checklist_zona_comun_id')->toArray();
        
        return view('gestion.edit-zona-comun', compact(
            'apartamentoLimpieza',
            'zonaComun',
            'id', 
            'checklists', 
            'itemsExistentes', 
            'checklistsExistentes'
        ));
    }

    /**
     * Crear limpieza para zona común
     */
    public function createZonaComun($id)
    {
        $zonaComun = \App\Models\ZonaComun::findOrFail($id);
        
        // Verificar si ya existe una limpieza activa para esta zona
        $limpiezaExistente = ApartamentoLimpieza::where('zona_comun_id', $id)
            ->where('tipo_limpieza', 'zona_comun')
            ->whereNull('fecha_fin')
            ->first();

        if ($limpiezaExistente) {
            Alert::warning('Atención', 'Ya existe una limpieza activa para esta zona común.');
            return redirect()->route('gestion.index');
        }

        // Crear nueva limpieza para zona común
        $apartamentoLimpieza = ApartamentoLimpieza::create([
            'zona_comun_id' => $id,
            'tipo_limpieza' => 'zona_comun',
            'fecha_comienzo' => Carbon::now(),
            'status_id' => 2, // En proceso
            'empleada_id' => Auth::user()->id,
            'user_id' => Auth::user()->id
        ]);

        Alert::success('Éxito', 'Limpieza de zona común iniciada correctamente.');
        return redirect()->route('gestion.edit', $apartamentoLimpieza->id);
    }

    /**
     * Get checklist status for zona común AJAX requests
     */
    public function checklistStatusZonaComun(ApartamentoLimpieza $apartamentoLimpieza)
    {
        if ($apartamentoLimpieza->tipo_limpieza !== 'zona_comun') {
            return response()->json(['error' => 'No es una limpieza de zona común'], 400);
        }

        $checklists = \App\Models\ChecklistZonaComun::activos()->ordenados()->get();
        
        $checklistsMarcados = ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)
            ->whereNotNull('checklist_zona_comun_id')
            ->where('estado', 1)
            ->pluck('checklist_zona_comun_id')
            ->toArray();
        
        $checklistsFaltantes = $checklists->whereNotIn('id', $checklistsMarcados);
        
        return response()->json([
            'total' => $checklists->count(),
            'completados' => count($checklistsMarcados),
            'faltantes' => $checklistsFaltantes->pluck('nombre')->toArray(),
            'puedeFinalizar' => $checklistsFaltantes->count() === 0
        ]);
    }
    
    /**
     * Calcular cantidad recomendada para un amenity según las reglas de consumo
     */
    private function calcularCantidadRecomendadaAmenity($amenity, $reserva, $apartamento)
    {
        $numeroPersonas = $reserva ? $reserva->numero_personas : 1;
        $dias = $reserva ? \Carbon\Carbon::parse($reserva->fecha_entrada)->diffInDays($reserva->fecha_salida) : 1;
        
        switch ($amenity->tipo_consumo) {
            case 'por_reserva':
                // Para amenities por reserva (ej: gafas, toallas, etc.)
                $cantidad = $amenity->consumo_por_reserva ?? 1;
                
                // Aplicar límites mínimo y máximo si están configurados
                if ($amenity->consumo_minimo_reserva) {
                    $cantidad = max($cantidad, $amenity->consumo_minimo_reserva);
                }
                if ($amenity->consumo_maximo_reserva) {
                    $cantidad = min($cantidad, $amenity->consumo_maximo_reserva);
                }
                
                return $cantidad;
                
            case 'por_tiempo':
                // Para amenities por tiempo (ej: ambientador cada X días)
                if ($amenity->duracion_dias && $amenity->duracion_dias > 0) {
                    $cantidad = ceil($dias / $amenity->duracion_dias);
                    return max(1, $cantidad); // Mínimo 1
                }
                return 1;
                
            case 'por_persona':
                // Para amenities por persona por día (ej: champú, gel, etc.)
                $cantidadPorPersonaPorDia = $amenity->consumo_por_persona ?? 1;
                $cantidad = $cantidadPorPersonaPorDia * $numeroPersonas * $dias;
                
                // Aplicar límites mínimo y máximo si están configurados
                if ($amenity->consumo_minimo_reserva) {
                    $cantidad = max($cantidad, $amenity->consumo_minimo_reserva);
                }
                if ($amenity->consumo_maximo_reserva) {
                    $cantidad = min($cantidad, $amenity->consumo_maximo_reserva);
                }
                
                return ceil($cantidad);
                
            default:
                return 1;
        }
    }

    /**
     * Mostrar información de una reserva
     */
    public function mostrarInfoReserva($id)
    {
        $reserva = Reserva::with(['apartamento', 'cliente'])->findOrFail($id);
        return view('gestion.reserva-info', compact('reserva'));
    }

    /**
     * Ver limpieza completada (solo lectura)
     */
    public function verLimpiezaCompletada($id)
    {
        $apartamentoLimpieza = ApartamentoLimpieza::with([
            'apartamento.edificio', 
            'zonaComun', 
            'empleada', 
            'estado',
            'fotos' => function($query) {
                $query->orderBy('created_at', 'desc');
            }
        ])->findOrFail($id);

        // Obtener fotos desde ApartamentoLimpiezaItem
        $fotos = \App\Models\ApartamentoLimpiezaItem::where('id_limpieza', $id)
            ->whereNotNull('photo_url')
            ->orderBy('created_at', 'desc')
            ->get();

        // Combinar fotos de ambas fuentes
        $todasLasFotos = $apartamentoLimpieza->fotos->merge($fotos);

        // Obtener amenities de consumo para esta limpieza
        $amenities = \App\Models\Amenity::activos()
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get()
            ->groupBy('categoria');
        
        // Obtener consumos existentes para esta limpieza
        $consumosExistentes = \App\Models\AmenityConsumo::where('limpieza_id', $apartamentoLimpieza->id)
            ->with('amenity')
            ->get()
            ->keyBy('amenity_id');

        // Obtener checklists con sus items si existen
        $checklists = [];
        if ($apartamentoLimpieza->apartamento && $apartamentoLimpieza->apartamento->edificio_id) {
            $checklists = \App\Models\Checklist::with('items')->where('edificio_id', $apartamentoLimpieza->apartamento->edificio_id)->get();
        }

        // Obtener items existentes de la limpieza
        $itemsExistentes = [];
        if ($apartamentoLimpieza->id) {
            $itemsExistentes = \App\Models\ApartamentoLimpiezaItem::where('id_limpieza', $apartamentoLimpieza->id)->get();
        }

        return view('gestion.ver-limpieza', compact(
            'apartamentoLimpieza', 
            'checklists', 
            'itemsExistentes', 
            'todasLasFotos',
            'amenities',
            'consumosExistentes'
        ));
    }

    /**
     * DESCUENTO AUTOMÁTICO DE AMENITIES DE LIMPIEZA
     */
    private function descontarAmenitiesLimpieza(ApartamentoLimpieza $apartamentoLimpieza)
    {
        try {
            \Log::info('Iniciando descuento automático de amenities para limpieza ID: ' . $apartamentoLimpieza->id);
            
            // Obtener amenities de limpieza activos
            $amenitiesLimpieza = \App\Models\Amenity::where('categoria', 'Limpieza')
                ->where('activo', true)
                ->get();

            \Log::info('Amenities de limpieza encontrados: ' . $amenitiesLimpieza->count());
            
            $totalGasto = 0;
            $amenitiesUsados = [];

            foreach ($amenitiesLimpieza as $amenity) {
                \Log::info('Procesando amenity: ' . $amenity->nombre . ' (Stock: ' . $amenity->stock_actual . ')');
                
                // Calcular cantidad recomendada para esta limpieza
                $cantidadRecomendada = $this->calcularCantidadRecomendadaAmenity($amenity, $apartamentoLimpieza->reserva, $apartamentoLimpieza->apartamento);
                
                \Log::info('Cantidad recomendada calculada: ' . $cantidadRecomendada);
                
                if ($cantidadRecomendada > 0) {
                    \Log::info('Cantidad > 0, verificando stock...');
                    
                    // Verificar stock disponible
                    if ($amenity->stock_actual >= $cantidadRecomendada) {
                        \Log::info('Stock suficiente, procediendo con descuento...');
                        
                        // Descontar del stock
                        $stockAnterior = $amenity->stock_actual;
                        $amenity->stock_actual -= $cantidadRecomendada;
                        $amenity->save();
                        
                        \Log::info('Stock actualizado: ' . $stockAnterior . ' -> ' . $amenity->stock_actual);

                        // Calcular costo
                        $costoTotal = $cantidadRecomendada * $amenity->precio_compra;
                        $totalGasto += $costoTotal;
                        
                        \Log::info('Costo calculado: €' . $costoTotal);

                        // Registrar el consumo
                        \Log::info('Creando registro de consumo...');
                        \App\Models\AmenityConsumo::create([
                            'amenity_id' => $amenity->id,
                            'reserva_id' => $apartamentoLimpieza->reserva_id,
                            'apartamento_id' => $apartamentoLimpieza->apartamento_id,
                            'limpieza_id' => $apartamentoLimpieza->id,
                            'user_id' => auth()->id(),
                            'tipo_consumo' => 'limpieza',
                            'cantidad_consumida' => $cantidadRecomendada,
                            'cantidad_anterior' => $stockAnterior,
                            'cantidad_actual' => $amenity->stock_actual,
                            'costo_unitario' => $amenity->precio_compra,
                            'costo_total' => $costoTotal,
                            'observaciones' => 'Descuento automático al finalizar limpieza',
                            'fecha_consumo' => now()
                        ]);
                        
                        \Log::info('Consumo registrado exitosamente');

                        $amenitiesUsados[] = [
                            'nombre' => $amenity->nombre,
                            'cantidad' => $cantidadRecomendada,
                            'unidad' => $amenity->unidad_medida,
                            'costo' => $costoTotal
                        ];

                        // Verificar si el stock está bajo después del descuento
                        if ($amenity->stock_actual <= $amenity->stock_minimo) {
                            \Alert::warning('Stock Bajo', "El amenity '{$amenity->nombre}' tiene stock bajo (actual: {$amenity->stock_actual} {$amenity->unidad_medida})");
                        }
                    } else {
                        // Stock insuficiente
                        \Alert::error('Stock Insuficiente', "No hay suficiente stock de '{$amenity->nombre}' para esta limpieza. Stock disponible: {$amenity->stock_actual} {$amenity->unidad_medida}, Necesario: {$cantidadRecomendada} {$amenity->unidad_medida}");
                    }
                }
            }

            // Mostrar resumen de amenities utilizados
            if (!empty($amenitiesUsados)) {
                $mensaje = "Amenities de limpieza utilizados:\n";
                foreach ($amenitiesUsados as $amenity) {
                    $mensaje .= "• {$amenity['nombre']}: {$amenity['cantidad']} {$amenity['unidad']} (€{$amenity['costo']})\n";
                }
                $mensaje .= "\nTotal gasto en amenities: €{$totalGasto}";
                
                \Alert::info('Amenities Aplicados', $mensaje);
            }

        } catch (\Exception $e) {
            \Log::error('Error al descontar amenities de limpieza: ' . $e->getMessage());
            \Alert::error('Error', 'Error al procesar amenities de limpieza: ' . $e->getMessage());
        }
    }
}
