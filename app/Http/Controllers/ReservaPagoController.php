<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\Pago;
use App\Models\IntentoPago;
use App\Models\Huesped;
use App\Models\RoomType;
use App\Models\Cupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

        // Validar y aplicar cupón si existe
        $cupon = null;
        $descuento = 0;
        $precioConDescuento = $precioTotal;
        
        if ($request->has('codigo_cupon') && !empty($request->codigo_cupon)) {
            $cupon = Cupon::where('codigo', strtoupper(trim($request->codigo_cupon)))->first();
            if ($cupon) {
                $clienteLogueado = Auth::guard('cliente')->user();
                $validacion = $cupon->esValido($precioTotal, $apartamento->id, $clienteLogueado?->id);
                
                if ($validacion['valido']) {
                    $descuento = $cupon->calcularDescuento($precioTotal);
                    $precioConDescuento = max(0, $precioTotal - $descuento); // No puede ser negativo
                } else {
                    $cupon = null; // Invalidar cupón si no es válido
                }
            }
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
            'precioConDescuento' => $precioConDescuento,
            'descuento' => $descuento,
            'cupon' => $cupon,
            'codigoCupon' => $request->codigo_cupon ?? null,
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
            'horario_checkin' => 'required|in:temprano,tardio',
        ];
        
        $request->validate($rules);

        try {
            $apartamento = Apartamento::findOrFail($request->apartamento_id);
            $fechaEntrada = Carbon::parse($request->fecha_entrada);
            $fechaSalida = Carbon::parse($request->fecha_salida);
            
            // Calcular precio (antes de la transacción para validar)
            $precioPorNoche = $this->calcularPrecioPorNoche($apartamento, $fechaEntrada, $fechaSalida);
            if (!$precioPorNoche) {
                return back()->with('error', 'No se pudo calcular el precio. Por favor, contacta con nosotros.')->withInput();
            }
            
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

            // Validar y aplicar cupón antes de la transacción
            $cupon = null;
            $descuento = 0;
            $precioConDescuento = $precioTotal;
            $montoOriginal = $precioTotal;
            
            if ($request->has('codigo_cupon') && !empty($request->codigo_cupon)) {
                $codigoCupon = strtoupper(trim($request->codigo_cupon));
                \Log::info('Validando cupón en procesarReserva', [
                    'codigo' => $codigoCupon,
                    'precio_total' => $precioTotal,
                    'apartamento_id' => $apartamento->id
                ]);
                
                $cupon = Cupon::where('codigo', $codigoCupon)->first();
                if ($cupon) {
                    $clienteLogueado = Auth::guard('cliente')->user();
                    $validacion = $cupon->esValido($precioTotal, $apartamento->id, $clienteLogueado?->id);
                    
                    if ($validacion['valido']) {
                        $descuento = $cupon->calcularDescuento($precioTotal);
                        $precioConDescuento = max(0, $precioTotal - $descuento);
                        
                        \Log::info('Cupón aplicado correctamente', [
                            'cupon_id' => $cupon->id,
                            'codigo' => $cupon->codigo,
                            'precio_original' => $precioTotal,
                            'descuento' => $descuento,
                            'precio_con_descuento' => $precioConDescuento
                        ]);
                    } else {
                        \Log::warning('Cupón no válido', [
                            'codigo' => $codigoCupon,
                            'razon' => $validacion['mensaje']
                        ]);
                        return back()->with('error', $validacion['mensaje'])->withInput();
                    }
                } else {
                    \Log::warning('Cupón no encontrado', ['codigo' => $codigoCupon]);
                    return back()->with('error', 'El código de cupón no es válido')->withInput();
                }
            } else {
                \Log::info('No se proporcionó código de cupón');
            }

            // Usar transacción con nivel de aislamiento SERIALIZABLE para evitar condiciones de carrera
            return \DB::transaction(function () use ($request, $apartamento, $fechaEntrada, $fechaSalida, $precioTotal, $precioConDescuento, $montoOriginal, $descuento, $cupon, $noches, $stripeSecret, $clienteLogueado, $esParaMi) {
                // BLOQUEO CRÍTICO: Verificar disponibilidad DENTRO de la transacción con bloqueo de fila
                // Esto previene condiciones de carrera cuando múltiples usuarios reservan simultáneamente
                $reservasSolapadas = \App\Models\Reserva::where('apartamento_id', $apartamento->id)
                    ->whereIn('estado_id', [1, 2, 3]) // Confirmada, Pendiente, En curso
                    ->where(function ($query) use ($fechaEntrada, $fechaSalida) {
                        $query->where(function ($q) use ($fechaEntrada, $fechaSalida) {
                            $q->where('fecha_entrada', '<=', $fechaEntrada)
                              ->where('fecha_salida', '>', $fechaEntrada);
                        })->orWhere(function ($q) use ($fechaEntrada, $fechaSalida) {
                            $q->where('fecha_entrada', '>=', $fechaEntrada)
                              ->where('fecha_entrada', '<', $fechaSalida);
                        });
                    })
                    ->lockForUpdate() // Bloqueo de fila para evitar condiciones de carrera
                    ->exists();
                
                if ($reservasSolapadas) {
                    \Log::warning('Intento de reserva duplicada detectado', [
                        'apartamento_id' => $apartamento->id,
                        'fecha_entrada' => $fechaEntrada->format('Y-m-d'),
                        'fecha_salida' => $fechaSalida->format('Y-m-d'),
                    ]);
                    throw new \Exception('El apartamento ya no está disponible para las fechas seleccionadas. Otra reserva se ha completado simultáneamente.');
                }
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

                // Obtener room_type_id del apartamento (necesario para Channex)
                $roomType = RoomType::where('property_id', $apartamento->id)->first();
                if (!$roomType) {
                    \Log::error('RoomType no encontrado para apartamento', ['apartamento_id' => $apartamento->id]);
                    throw new \Exception('Error de configuración: no se encontró el tipo de habitación para este apartamento.');
                }

                // Obtener horarios de check-in configurables
                $horarioCheckinTemprano = \App\Models\Setting::get('checkin_horario_temprano', '14:00');
                $horarioCheckinTardio = \App\Models\Setting::get('checkin_horario_tardio', '18:00');
                
                // Determinar hora de entrada según el horario seleccionado
                $horarioCheckin = $request->horario_checkin;
                $horaEntrada = $horarioCheckin === 'temprano' ? $horarioCheckinTemprano : $horarioCheckinTardio;

                // Crear reserva con estado "Progreso" (ID 10) desde el inicio
                // Esto previene que el cron de claves envíe mensajes antes de que el pago se complete
                $codigoReserva = 'WEB-' . strtoupper(Str::random(8));
                $reserva = Reserva::create([
                    'cliente_id' => $cliente->id,
                    'apartamento_id' => $apartamento->id,
                    'room_type_id' => $roomType->id,
                    'estado_id' => 10, // Progreso - reserva en proceso de pago
                    'origen' => 'Web',
                    'fecha_entrada' => $fechaEntrada->format('Y-m-d'),
                    'fecha_salida' => $fechaSalida->format('Y-m-d'),
                    'fecha_hora_entrada' => $fechaEntrada->format('Y-m-d') . ' ' . $horaEntrada . ':00',
                    'fecha_hora_salida' => $fechaSalida->format('Y-m-d') . ' 11:00:00',
                    'precio' => $precioTotal,
                    'codigo_reserva' => $codigoReserva,
                    'numero_personas' => $request->adultos + ($request->ninos ?? 0),
                    'numero_ninos' => $request->ninos ?? 0,
                    'horario_checkin' => $horarioCheckin,
                ]);
                
                \Log::info('Reserva creada con estado "Progreso" (en proceso de pago)', [
                    'reserva_id' => $reserva->id,
                    'codigo_reserva' => $reserva->codigo_reserva,
                    'estado_id' => 10
                ]);
                
                // SINCRONIZAR CON CHANNEX: Actualizar disponibilidad para bloquear las fechas
                // Esto previene que otras plataformas (Booking, Airbnb) vendan las mismas fechas
                $this->sincronizarConChannex($reserva);
                
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
                        'fecha_hora_entrada' => $fechaEntrada->format('Y-m-d') . ' ' . $horaEntrada . ':00',
                        'fecha_hora_salida' => $fechaSalida->format('Y-m-d') . ' 11:00:00',
                    ]);
                }

                // Crear registro de pago (usar cliente comprador si existe, sino el cliente)
                $clientePago = $clienteComprador ?? $cliente;
                $pago = Pago::create([
                    'reserva_id' => $reserva->id,
                    'cliente_id' => $clientePago->id,
                    'cupon_id' => $cupon?->id,
                    'metodo_pago' => 'stripe',
                    'estado' => 'pendiente',
                    'monto' => $precioConDescuento, // Precio con descuento aplicado
                    'monto_original' => $montoOriginal, // Precio original antes del descuento
                    'descuento_aplicado' => $descuento, // Descuento aplicado
                    'moneda' => 'EUR',
                    'descripcion' => "Reserva {$codigoReserva} - {$apartamento->titulo}",
                    'metadata' => [
                        'noches' => $noches,
                        'adultos' => $request->adultos,
                        'ninos' => $request->ninos ?? 0,
                        'notas' => $request->notas,
                        'es_para_otro' => $clienteComprador ? true : false,
                        'codigo_cupon' => $cupon?->codigo,
                    ],
                ]);

                // Incrementar contador de usos del cupón si se aplicó
                if ($cupon) {
                    $cupon->incrementarUso();
                }

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
                                    'description' => "Del {$fechaEntrada->format('d/m/Y')} al {$fechaSalida->format('d/m/Y')} ({$noches} noches)" . ($cupon ? " - Cupón: {$cupon->codigo}" : ''),
                                ],
                                'unit_amount' => (int)($precioConDescuento * 100), // Stripe usa centavos (precio con descuento)
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
                        'precio_original' => $montoOriginal,
                        'descuento' => $descuento,
                        'precio_con_descuento' => $precioConDescuento,
                        'cupon_id' => $cupon?->id,
                        'cupon_codigo' => $cupon?->codigo,
                        'stripe_amount' => (int)($precioConDescuento * 100),
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
            \Log::error('Error al procesar reserva: ' . $e->getMessage(), [
                'apartamento_id' => $request->apartamento_id ?? null,
                'fecha_entrada' => $request->fecha_entrada ?? null,
                'fecha_salida' => $request->fecha_salida ?? null,
            ]);
            
            // Mensaje más específico para errores de disponibilidad
            $mensajeError = str_contains($e->getMessage(), 'disponible') 
                ? $e->getMessage() 
                : 'Hubo un error al procesar tu reserva. Por favor, inténtalo de nuevo.';
            
            return back()->with('error', $mensajeError)->withInput();
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
                
                if ($pago) {
                    // Si el pago NO fue exitoso, cancelar la reserva
                    if ($session->payment_status !== 'paid') {
                        if ($pago->reserva && in_array($pago->reserva->estado_id, [2, 10])) { // Pendiente (2) o Progreso (10)
                            // Cancelar la reserva
                            $pago->reserva->estado_id = 4; // Cancelada
                            $pago->reserva->save();
                            
                            // Marcar el pago como fallido
                            $pago->update(['estado' => 'fallido']);
                            
                            // Liberar disponibilidad en Channex
                            $this->liberarDisponibilidadChannex($pago->reserva);
                            
                            \Log::info('Reserva cancelada - pago no exitoso en página de éxito', [
                                'reserva_id' => $pago->reserva->id,
                                'codigo_reserva' => $pago->reserva->codigo_reserva,
                                'payment_status' => $session->payment_status,
                            ]);
                        }
                        
                        return redirect()->route('web.reservas.pago.cancelado', ['reserva_id' => $pago->reserva_id ?? null])
                            ->with('error', 'El pago no se completó correctamente.');
                    }
                    
                    // El pago fue exitoso
                    // El webhook debería haber actualizado esto, pero por si acaso
                    if ($pago->estado !== 'completado') {
                        $pago->update([
                            'estado' => 'completado',
                            'fecha_pago' => now(),
                            'stripe_payment_intent_id' => $session->payment_intent,
                        ]);
                        
                        // Cambiar de "Progreso" (10) a "Pendiente Cliente" (1) cuando el pago se confirma
                        $pago->reserva->update(['estado_id' => 1]); // Pendiente Cliente
                        
                        \Log::info('Reserva confirmada después de pago exitoso', [
                            'reserva_id' => $pago->reserva->id,
                            'codigo_reserva' => $pago->reserva->codigo_reserva,
                            'estado_anterior' => 10,
                            'estado_nuevo' => 1
                        ]);
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
        
        if ($reservaId) {
            try {
                $reserva = Reserva::find($reservaId);
                
                if ($reserva && in_array($reserva->estado_id, [2, 10])) { // Pendiente (2) o Progreso (10)
                    // Cancelar la reserva
                    $reserva->estado_id = 4; // Cancelada
                    $reserva->save();
                    
                    // Liberar disponibilidad en Channex
                    $this->liberarDisponibilidadChannex($reserva);
                    
                    // Marcar el pago como cancelado si existe
                    $pago = Pago::where('reserva_id', $reservaId)
                        ->where('estado', 'pendiente')
                        ->first();
                    
                    if ($pago) {
                        $pago->update(['estado' => 'cancelado']);
                    }
                    
                    \Log::info('Reserva cancelada por usuario - pago no completado', [
                        'reserva_id' => $reservaId,
                        'codigo_reserva' => $reserva->codigo_reserva,
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Error al cancelar reserva en página de cancelación: ' . $e->getMessage(), [
                    'reserva_id' => $reservaId,
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
        
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
    
    /**
     * Sincronizar reserva web con Channex para bloquear disponibilidad
     * Esto previene que otras plataformas (Booking, Airbnb) vendan las mismas fechas
     */
    private function sincronizarConChannex(Reserva $reserva)
    {
        try {
            $apartamento = $reserva->apartamento;
            $roomType = RoomType::find($reserva->room_type_id);
            
            // Verificar que tenemos los datos necesarios para Channex
            if (!$apartamento || !$apartamento->id_channex || !$roomType || !$roomType->id_channex) {
                \Log::warning('No se puede sincronizar con Channex: faltan datos', [
                    'reserva_id' => $reserva->id,
                    'apartamento_id_channex' => $apartamento->id_channex ?? null,
                    'room_type_id' => $reserva->room_type_id ?? null,
                    'room_type_id_channex' => $roomType->id_channex ?? null,
                ]);
                return;
            }
            
            $startDate = Carbon::parse($reserva->fecha_entrada);
            $endDate = Carbon::parse($reserva->fecha_salida)->subDay(); // Restamos un día a la fecha de salida
            
            $update = [
                'property_id' => $apartamento->id_channex,
                'room_type_id' => $roomType->id_channex,
                'date_from' => $startDate->toDateString(),
                'date_to' => $endDate->toDateString(),
                'update_type' => 'availability',
                'availability' => 0, // Bloqueamos la disponibilidad
            ];
            
            // Usar las mismas credenciales que WebhookController para consistencia
            $apiUrl = env('CHANNEX_URL', 'https://app.channex.io/api/v1');
            $apiToken = env('CHANNEX_TOKEN');
            
            if (!$apiToken) {
                \Log::error('Channex API token no configurado en .env (CHANNEX_TOKEN)', [
                    'reserva_id' => $reserva->id,
                ]);
                return;
            }
            
            if (!$apiUrl) {
                \Log::error('Channex API URL no configurada en .env (CHANNEX_URL)', [
                    'reserva_id' => $reserva->id,
                ]);
                return;
            }
            
            // Log de lo que vamos a enviar (sin el token por seguridad)
            \Log::info('Intentando sincronizar reserva web con Channex', [
                'reserva_id' => $reserva->id,
                'codigo_reserva' => $reserva->codigo_reserva,
                'apartamento_id' => $apartamento->id,
                'apartamento_id_channex' => $apartamento->id_channex,
                'room_type_id' => $roomType->id,
                'room_type_id_channex' => $roomType->id_channex,
                'fecha_entrada' => $startDate->toDateString(),
                'fecha_salida' => $endDate->toDateString(),
                'api_url' => $apiUrl,
                'payload' => $update,
            ]);
            
            // Enviar actualización de disponibilidad a Channex
            $response = Http::timeout(10)
                ->withHeaders([
                    'user-api-key' => $apiToken,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$apiUrl}/availability", ['values' => [$update]]);
            
            if ($response->successful()) {
                $responseData = $response->json();
                \Log::info('✅ Reserva web sincronizada exitosamente con Channex - Disponibilidad bloqueada', [
                    'reserva_id' => $reserva->id,
                    'codigo_reserva' => $reserva->codigo_reserva,
                    'apartamento_id' => $apartamento->id,
                    'apartamento_nombre' => $apartamento->nombre,
                    'fechas' => $startDate->toDateString() . ' - ' . $endDate->toDateString(),
                    'channex_response' => $responseData,
                ]);
            } else {
                \Log::error('❌ Error al sincronizar reserva web con Channex', [
                    'reserva_id' => $reserva->id,
                    'codigo_reserva' => $reserva->codigo_reserva,
                    'http_status' => $response->status(),
                    'error_body' => $response->body(),
                    'error_json' => $response->json(),
                    'payload_enviado' => $update,
                ]);
            }
        } catch (\Exception $e) {
            // No lanzar excepción para no interrumpir el flujo de pago
            // Solo loguear el error
            \Log::error('Excepción al sincronizar con Channex', [
                'reserva_id' => $reserva->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Liberar disponibilidad en Channex para una reserva cancelada
     */
    private function liberarDisponibilidadChannex(Reserva $reserva)
    {
        try {
            $apartamento = $reserva->apartamento;
            $roomType = RoomType::find($reserva->room_type_id);
            
            if (!$apartamento || !$apartamento->id_channex || !$roomType || !$roomType->id_channex) {
                \Log::warning('No se puede liberar disponibilidad en Channex: faltan datos', [
                    'reserva_id' => $reserva->id,
                ]);
                return;
            }
            
            $startDate = Carbon::parse($reserva->fecha_entrada);
            $endDate = Carbon::parse($reserva->fecha_salida)->subDay();
            
            $update = [
                'property_id' => $apartamento->id_channex,
                'room_type_id' => $roomType->id_channex,
                'date_from' => $startDate->toDateString(),
                'date_to' => $endDate->toDateString(),
                'update_type' => 'availability',
                'availability' => 1, // Habilitar disponibilidad (liberar)
            ];
            
            $apiUrl = env('CHANNEX_URL', 'https://app.channex.io/api/v1');
            $apiToken = env('CHANNEX_TOKEN');
            
            if (!$apiToken) {
                \Log::error('CHANNEX_TOKEN no configurado para liberar disponibilidad');
                return;
            }
            
            $response = Http::timeout(10)
                ->withHeaders([
                    'user-api-key' => $apiToken,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$apiUrl}/availability", ['values' => [$update]]);
            
            if ($response->successful()) {
                \Log::info('Disponibilidad liberada en Channex - reserva cancelada por pago fallido', [
                    'reserva_id' => $reserva->id,
                    'codigo_reserva' => $reserva->codigo_reserva,
                    'apartamento_id' => $apartamento->id,
                    'fecha_entrada' => $startDate->toDateString(),
                    'fecha_salida' => $endDate->toDateString(),
                ]);
            } else {
                \Log::error('Error al liberar disponibilidad en Channex', [
                    'reserva_id' => $reserva->id,
                    'http_status' => $response->status(),
                    'error_body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Excepción al liberar disponibilidad en Channex', [
                'reserva_id' => $reserva->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Validar cupón desde AJAX
     */
    public function validarCupon(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string',
            'apartamento_id' => 'required|exists:apartamentos,id',
            'precio_total' => 'required|numeric|min:0',
        ]);

        $cupon = Cupon::where('codigo', strtoupper(trim($request->codigo)))->first();
        
        if (!$cupon) {
            return response()->json([
                'valido' => false,
                'mensaje' => 'El código de cupón no es válido'
            ], 200);
        }

        $clienteLogueado = Auth::guard('cliente')->user();
        $validacion = $cupon->esValido($request->precio_total, $request->apartamento_id, $clienteLogueado?->id);

        if (!$validacion['valido']) {
            return response()->json([
                'valido' => false,
                'mensaje' => $validacion['mensaje']
            ], 200);
        }

        $descuento = $cupon->calcularDescuento($request->precio_total);
        $precioFinal = max(0, $request->precio_total - $descuento);

        return response()->json([
            'valido' => true,
            'mensaje' => 'Cupón aplicado correctamente',
            'cupon' => [
                'id' => $cupon->id,
                'codigo' => $cupon->codigo,
                'nombre' => $cupon->nombre,
                'tipo' => $cupon->tipo,
                'valor' => $cupon->valor,
            ],
            'descuento' => round($descuento, 2),
            'precio_original' => round($request->precio_total, 2),
            'precio_final' => round($precioFinal, 2),
        ], 200);
    }
}
