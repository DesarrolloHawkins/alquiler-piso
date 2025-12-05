<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\Pago;
use App\Models\IntentoPago;
use App\Models\Huesped;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReservaPagoController extends Controller
{
    /**
     * Mostrar formulario de datos del cliente para la reserva
     */
    public function formularioReserva(Request $request, $apartamentoId)
    {
        $apartamento = Apartamento::with(['edificioName', 'photos'])
            ->whereNotNull('id_channex')
            ->findOrFail($apartamentoId);

        // Validar parámetros de búsqueda
        $request->validate([
            'fecha_entrada' => 'required|date|after_or_equal:today',
            'fecha_salida' => 'required|date|after:fecha_entrada',
            'adultos' => 'required|integer|min:1|max:20',
            'ninos' => 'nullable|integer|min:0|max:10',
        ]);

        $fechaEntrada = Carbon::parse($request->fecha_entrada);
        $fechaSalida = Carbon::parse($request->fecha_salida);
        $noches = $fechaEntrada->diffInDays($fechaSalida);
        $adultos = $request->adultos;
        $ninos = $request->ninos ?? 0;

        // Verificar disponibilidad
        $disponible = $this->verificarDisponibilidad($apartamento, $fechaEntrada, $fechaSalida);
        
        if (!$disponible) {
            return redirect()->route('web.reservas.show', $apartamentoId)
                ->with('error', 'El apartamento no está disponible para las fechas seleccionadas.')
                ->withInput();
        }

        // Calcular precio
        $precioPorNoche = $this->calcularPrecioPorNoche($apartamento, $fechaEntrada, $fechaSalida);
        if (!$precioPorNoche) {
            return redirect()->route('web.reservas.show', $apartamentoId)
                ->with('error', 'No se pudo calcular el precio. Por favor, contacta con nosotros.')
                ->withInput();
        }

        $precioTotal = $precioPorNoche * $noches;
        
        // Aplicar impuestos si aplican
        if ($apartamento->tourist_tax && !$apartamento->tourist_tax_included) {
            $precioTotal += ($apartamento->tourist_tax * $noches * ($adultos + $ninos));
        }
        if ($apartamento->city_tax && !$apartamento->city_tax_included) {
            $precioTotal += ($apartamento->city_tax * $noches * ($adultos + $ninos));
        }
        if ($apartamento->cleaning_fee) {
            $precioTotal += $apartamento->cleaning_fee;
        }

        // Verificar si el usuario está logueado
        $clienteLogueado = Auth::guard('cliente')->user();
        $datosFaltantes = [];
        $esParaMi = $request->get('es_para_mi', false);
        
        if ($clienteLogueado && $esParaMi) {
            // Verificar datos MIR necesarios solo si es para él
            $datosFaltantes = $this->verificarDatosMIR($clienteLogueado);
        }
        
        // Guardar parámetros de reserva en sesión para poder recuperarlos después
        if ($clienteLogueado) {
            session([
                'reserva_params' => [
                    'apartamento_id' => $apartamento->id,
                    'fecha_entrada' => $fechaEntrada->format('Y-m-d'),
                    'fecha_salida' => $fechaSalida->format('Y-m-d'),
                    'adultos' => $adultos,
                    'ninos' => $ninos,
                    'es_para_mi' => $esParaMi,
                ]
            ]);
        }

        return view('public.reservas.formulario-reserva', [
            'apartamento' => $apartamento,
            'fechaEntrada' => $fechaEntrada,
            'fechaSalida' => $fechaSalida,
            'noches' => $noches,
            'adultos' => $adultos,
            'ninos' => $ninos,
            'precioPorNoche' => $precioPorNoche,
            'precioTotal' => $precioTotal,
            'clienteLogueado' => $clienteLogueado,
            'datosFaltantes' => $datosFaltantes,
            'esParaMi' => $esParaMi,
        ]);
    }

    /**
     * Procesar formulario y crear sesión de pago Stripe
     */
    public function procesarReserva(Request $request)
    {
        $clienteLogueado = Auth::guard('cliente')->user();
        $esParaMi = $request->get('es_para_mi', false);
        
        // Si está logueado y es para él, validar datos MIR
        if ($clienteLogueado && $esParaMi) {
            $datosFaltantes = $this->verificarDatosMIR($clienteLogueado);
            if (!empty($datosFaltantes)) {
                return back()->with('error', 'Faltan datos necesarios para completar la reserva. Por favor, completa tu perfil.')->withInput();
            }
        }
        
        // Validación base
        $rules = [
            'apartamento_id' => 'required|exists:apartamentos,id',
            'fecha_entrada' => 'required|date|after_or_equal:today',
            'fecha_salida' => 'required|date|after:fecha_entrada',
            'adultos' => 'required|integer|min:1|max:20',
            'ninos' => 'nullable|integer|min:0|max:10',
            // Datos del huésped (obligatorios para MIR)
            'nombre' => 'required|string|max:255',
            'apellido1' => 'required|string|max:255',
            'apellido2' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'telefono' => 'required|string|max:20',
            'tipo_documento' => 'required|string|in:D,P',
            'num_identificacion' => 'required|string|max:20',
            'nacionalidad' => 'required|string|max:3',
            'fecha_nacimiento' => 'required|date|before:today',
            'fecha_expedicion' => 'required|date|before_or_equal:today',
            'fecha_caducidad' => 'required|date|after:today',
            'sexo' => 'required|string|in:Masculino,Femenino',
            'direccion' => 'nullable|string|max:500',
            'localidad' => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|max:10',
            'provincia' => 'required|string|max:255',
            'lugar_nacimiento' => 'nullable|string|max:255',
            'notas' => 'nullable|string|max:1000',
        ];
        
        $request->validate($rules);

        try {
            $apartamento = Apartamento::findOrFail($request->apartamento_id);
            $fechaEntrada = Carbon::parse($request->fecha_entrada);
            $fechaSalida = Carbon::parse($request->fecha_salida);
            
            // Verificar disponibilidad nuevamente
            if (!$this->verificarDisponibilidad($apartamento, $fechaEntrada, $fechaSalida)) {
                return back()->with('error', 'El apartamento ya no está disponible para las fechas seleccionadas.')->withInput();
            }

            // Calcular precio
            $precioPorNoche = $this->calcularPrecioPorNoche($apartamento, $fechaEntrada, $fechaSalida);
            $noches = $fechaEntrada->diffInDays($fechaSalida);
            $precioTotal = $precioPorNoche * $noches;
            
            if ($apartamento->tourist_tax && !$apartamento->tourist_tax_included) {
                $precioTotal += ($apartamento->tourist_tax * $noches * ($request->adultos + ($request->ninos ?? 0)));
            }
            if ($apartamento->city_tax && !$apartamento->city_tax_included) {
                $precioTotal += ($apartamento->city_tax * $noches * ($request->adultos + ($request->ninos ?? 0)));
            }
            if ($apartamento->cleaning_fee) {
                $precioTotal += $apartamento->cleaning_fee;
            }

            // VERIFICAR STRIPE PRIMERO antes de crear nada
            $stripeSecret = config('services.stripe.secret');
            
            if (!$stripeSecret) {
                \Log::error('Stripe secret key no configurada');
                return back()->with('error', 'El sistema de pagos no está configurado. Por favor, contacta con nosotros.')->withInput();
            }
            
            if (!class_exists('\Stripe\Stripe')) {
                \Log::error('Stripe SDK no disponible');
                return back()->with('error', 'El sistema de pagos no está disponible. Por favor, contacta con nosotros.')->withInput();
            }

            // Usar transacción para asegurar que todo se cree correctamente o se revierta
            return \DB::transaction(function () use ($request, $apartamento, $fechaEntrada, $fechaSalida, $precioTotal, $noches, $stripeSecret, $clienteLogueado, $esParaMi) {
                // Determinar cliente y huésped
                if ($clienteLogueado && $esParaMi) {
                    // Es para el cliente logueado
                    $cliente = $clienteLogueado;
                    $clienteComprador = null;
                } else {
                    // Es para otro huésped o cliente no logueado
                    $clienteComprador = $clienteLogueado;
                    
                    // Buscar o crear/actualizar cliente con los datos del formulario
                    $cliente = Cliente::updateOrCreate(
                        ['email' => $request->email],
                        [
                            'nombre' => $request->nombre,
                            'apellido1' => $request->apellido1,
                            'apellido2' => $request->apellido2 ?? '',
                            'telefono' => $request->telefono,
                            'telefono_movil' => $request->telefono,
                            'tipo_documento' => $request->tipo_documento,
                            'num_identificacion' => $request->num_identificacion,
                            'nacionalidad' => $request->nacionalidad,
                            'fecha_nacimiento' => $request->fecha_nacimiento,
                            'fecha_expedicion_doc' => $request->fecha_expedicion,
                            'sexo' => $request->sexo,
                            'direccion' => $request->direccion,
                            'localidad' => $request->localidad,
                            'codigo_postal' => $request->codigo_postal,
                            'provincia' => $request->provincia,
                            'lugar_nacimiento' => $request->lugar_nacimiento,
                            'alias' => $request->nombre . ' ' . $request->apellido1,
                            'tipo_cliente' => 'particular',
                            'idioma' => 'es',
                            'inactivo' => false,
                        ]
                    );
                }

                // Crear reserva temporal (pendiente de pago)
                $codigoReserva = 'WEB-' . strtoupper(Str::random(8));
                $reserva = Reserva::create([
                    'cliente_id' => $cliente->id,
                    'apartamento_id' => $apartamento->id,
                    'estado_id' => 2, // Pendiente
                    'origen' => 'Web',
                    'fecha_entrada' => $fechaEntrada->format('Y-m-d'),
                    'fecha_salida' => $fechaSalida->format('Y-m-d'),
                    'fecha_hora_entrada' => $fechaEntrada->format('Y-m-d') . ' 15:00:00',
                    'fecha_hora_salida' => $fechaSalida->format('Y-m-d') . ' 11:00:00',
                    'precio' => $precioTotal,
                    'codigo_reserva' => $codigoReserva,
                    'numero_personas' => $request->adultos + ($request->ninos ?? 0),
                    'numero_ninos' => $request->ninos ?? 0,
                ]);
                
                // Si es para otro huésped, crear registro en tabla huespedes
                if ($clienteComprador) {
                    Huesped::create([
                        'reserva_id' => $reserva->id,
                        'cliente_comprador_id' => $clienteComprador->id,
                        'nombre' => $request->nombre,
                        'primer_apellido' => $request->apellido1,
                        'segundo_apellido' => $request->apellido2,
                        'fecha_nacimiento' => $request->fecha_nacimiento,
                        'lugar_nacimiento' => $request->lugar_nacimiento,
                        'nacionalidad' => $request->nacionalidad,
                        'tipo_documento' => $request->tipo_documento,
                        'numero_identificacion' => $request->num_identificacion,
                        'fecha_expedicion' => $request->fecha_expedicion,
                        'fecha_caducidad' => $request->fecha_caducidad,
                        'sexo' => $request->sexo,
                        'email' => $request->email,
                        'telefono_movil' => $request->telefono,
                        'direccion' => $request->direccion,
                        'localidad' => $request->localidad,
                        'codigo_postal' => $request->codigo_postal,
                        'provincia' => $request->provincia,
                        'fecha_hora_entrada' => $fechaEntrada->format('Y-m-d') . ' 15:00:00',
                        'fecha_hora_salida' => $fechaSalida->format('Y-m-d') . ' 11:00:00',
                    ]);
                }

                // Crear registro de pago (usar cliente comprador si existe, sino el cliente)
                $clientePago = $clienteComprador ?? $cliente;
                $pago = Pago::create([
                    'reserva_id' => $reserva->id,
                    'cliente_id' => $clientePago->id,
                    'metodo_pago' => 'stripe',
                    'estado' => 'pendiente',
                    'monto' => $precioTotal,
                    'moneda' => 'EUR',
                    'descripcion' => "Reserva {$codigoReserva} - {$apartamento->titulo}",
                    'metadata' => [
                        'noches' => $noches,
                        'adultos' => $request->adultos,
                        'ninos' => $request->ninos ?? 0,
                        'notas' => $request->notas,
                        'es_para_otro' => $clienteComprador ? true : false,
                    ],
                ]);

                // Crear sesión de Stripe Checkout
                try {
                    \Stripe\Stripe::setApiKey($stripeSecret);
                    
                    $checkoutSession = \Stripe\Checkout\Session::create([
                        'payment_method_types' => ['card'],
                        'line_items' => [[
                            'price_data' => [
                                'currency' => 'eur',
                                'product_data' => [
                                    'name' => "Reserva: {$apartamento->titulo}",
                                    'description' => "Del {$fechaEntrada->format('d/m/Y')} al {$fechaSalida->format('d/m/Y')} ({$noches} noches)",
                                ],
                                'unit_amount' => (int)($precioTotal * 100), // Stripe usa centavos
                            ],
                            'quantity' => 1,
                        ]],
                        'mode' => 'payment',
                        'success_url' => route('web.reservas.pago.exito') . '?session_id={CHECKOUT_SESSION_ID}',
                        'cancel_url' => route('web.reservas.pago.cancelado') . '?reserva_id=' . $reserva->id,
                        'customer_email' => $cliente->email,
                        'metadata' => [
                            'reserva_id' => $reserva->id,
                            'pago_id' => $pago->id,
                            'codigo_reserva' => $codigoReserva,
                        ],
                    ]);

                    // Actualizar pago con session ID
                    $pago->update([
                        'stripe_checkout_session_id' => $checkoutSession->id,
                    ]);

                    // Registrar intento de pago
                    IntentoPago::create([
                        'pago_id' => $pago->id,
                        'reserva_id' => $reserva->id,
                        'stripe_checkout_session_id' => $checkoutSession->id,
                        'estado' => 'iniciado',
                        'monto' => $precioTotal,
                        'moneda' => 'EUR',
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'fecha_intento' => now(),
                    ]);

                    \Log::info('Redirigiendo a Stripe Checkout', [
                        'session_id' => $checkoutSession->id,
                        'reserva_id' => $reserva->id,
                        'pago_id' => $pago->id,
                    ]);

                    // Si llegamos aquí, todo está bien, redirigir a Stripe
                    // La transacción se confirma automáticamente
                    return redirect($checkoutSession->url);
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    \Log::error('Error al crear sesión de Stripe: ' . $e->getMessage(), [
                        'reserva_id' => $reserva->id ?? null,
                        'error' => $e->getMessage(),
                    ]);
                    
                    // Lanzar excepción para que la transacción se revierta
                    throw new \Exception('Error al crear sesión de pago: ' . $e->getMessage());
                }
            });

        } catch (\Exception $e) {
            \Log::error('Error al procesar reserva: ' . $e->getMessage());
            return back()->with('error', 'Hubo un error al procesar tu reserva. Por favor, inténtalo de nuevo.')->withInput();
        }
    }

    /**
     * Página de éxito después del pago
     */
    public function exito(Request $request)
    {
        $sessionId = $request->get('session_id');
        
        if (!$sessionId) {
            return redirect()->route('web.index')->with('error', 'Sesión de pago no válida.');
        }

        try {
            if (class_exists('\Stripe\Stripe')) {
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                $session = \Stripe\Checkout\Session::retrieve($sessionId);
                
                $pago = Pago::where('stripe_checkout_session_id', $sessionId)->first();
                
                if ($pago && $session->payment_status === 'paid') {
                    // El webhook debería haber actualizado esto, pero por si acaso
                    if ($pago->estado !== 'completado') {
                        $pago->update([
                            'estado' => 'completado',
                            'fecha_pago' => now(),
                            'stripe_payment_intent_id' => $session->payment_intent,
                        ]);
                        
                        $pago->reserva->update(['estado_id' => 1]); // Confirmada
                    }
                    
                    return view('public.reservas.reserva-exitosa', [
                        'reserva' => $pago->reserva,
                        'pago' => $pago,
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error al verificar pago exitoso: ' . $e->getMessage());
        }

        return redirect()->route('web.index')->with('error', 'No se pudo verificar el pago.');
    }

    /**
     * Página de cancelación
     */
    public function cancelado(Request $request)
    {
        $reservaId = $request->get('reserva_id');
        
        return view('public.reservas.reserva-cancelada', [
            'reserva_id' => $reservaId,
        ]);
    }

    // Métodos auxiliares (copiados de PublicReservasController)
    private function calcularPrecioPorNoche($apartamento, $fechaEntrada, $fechaSalida)
    {
        $tarifasAsignadas = $apartamento->tarifas()
            ->wherePivot('activo', true)
            ->where('tarifas.activo', true)
            ->get();
        
        if ($tarifasAsignadas->isEmpty()) {
            return null;
        }
        
        $tarifaVigente = $tarifasAsignadas->first(function ($tarifa) use ($fechaEntrada, $fechaSalida) {
            $fechaInicioTarifa = Carbon::parse($tarifa->fecha_inicio);
            $fechaFinTarifa = Carbon::parse($tarifa->fecha_fin);
            return $fechaInicioTarifa->lte($fechaEntrada) && $fechaFinTarifa->gte($fechaSalida);
        });
        
        if ($tarifaVigente) {
            return floatval($tarifaVigente->precio);
        }
        
        return null;
    }

    private function verificarDisponibilidad($apartamento, $fechaEntrada, $fechaSalida)
    {
        $reservasSolapadas = \App\Models\Reserva::where('apartamento_id', $apartamento->id)
            ->whereIn('estado_id', [1, 2, 3])
            ->where(function ($query) use ($fechaEntrada, $fechaSalida) {
                $query->where(function ($q) use ($fechaEntrada, $fechaSalida) {
                    $q->where('fecha_entrada', '<=', $fechaEntrada)
                      ->where('fecha_salida', '>', $fechaEntrada);
                })->orWhere(function ($q) use ($fechaEntrada, $fechaSalida) {
                    $q->where('fecha_entrada', '>=', $fechaEntrada)
                      ->where('fecha_entrada', '<', $fechaSalida);
                });
            })
            ->exists();
        
        return !$reservasSolapadas;
    }
    
    /**
     * Verificar que el cliente tenga todos los datos necesarios para MIR
     */
    private function verificarDatosMIR($cliente)
    {
        $datosFaltantes = [];
        
        // Campos obligatorios para MIR
        $camposRequeridos = [
            'nombre' => 'Nombre',
            'apellido1' => 'Primer Apellido',
            'fecha_nacimiento' => 'Fecha de Nacimiento',
            'nacionalidad' => 'Nacionalidad',
            'tipo_documento' => 'Tipo de Documento',
            'num_identificacion' => 'Número de Identificación',
            'fecha_expedicion_doc' => 'Fecha de Expedición del Documento',
            'sexo' => 'Sexo',
            'email' => 'Email',
            'telefono_movil' => 'Teléfono Móvil',
            'provincia' => 'Provincia',
        ];
        
        foreach ($camposRequeridos as $campo => $nombre) {
            if (empty($cliente->$campo)) {
                $datosFaltantes[] = $nombre;
            }
        }
        
        // Nota: La fecha de caducidad del documento no se almacena en clientes,
        // se solicitará al momento de hacer la reserva si es necesario
        
        return $datosFaltantes;
    }
}
