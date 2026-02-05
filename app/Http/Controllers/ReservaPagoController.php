<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\ReservaHold;
use App\Models\Pago;
use App\Models\IntentoPago;
use App\Models\Huesped;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReservaPagoController extends Controller
{
    private $apiUrl;
    private $apiToken;

    public function __construct()
    {
        $this->apiUrl = env('CHANNEX_URL', 'https://staging.channex.io/api/v1');
        $this->apiToken = env('CHANNEX_TOKEN');
    }

    /**
     * Mostrar formulario de datos del cliente para la reserva
     */
    public function formularioReserva(Request $request, $apartamentoId)
    {
        Log::info('[ReservaWeb] formularioReserva: inicio', [
            'apartamento_id' => $apartamentoId,
            'fecha_entrada' => $request->get('fecha_entrada'),
            'fecha_salida' => $request->get('fecha_salida'),
            'adultos' => $request->get('adultos'),
            'ninos' => $request->get('ninos'),
        ]);

        // Si las reservas web están deshabilitadas, mostrar mensaje y no permitir continuar
        if (!config('app.web_reservas_enabled', false)) {
            Log::info('[ReservaWeb] formularioReserva: reservas web deshabilitadas, mostrando vista no-disponible');
            return response()->view('public.reservas.no-disponible', [], 200);
        }

        $apartamento = Apartamento::with(['edificioName', 'photos'])
            ->whereNotNull('id_channex')
            ->findOrFail($apartamentoId);

        Log::info('[ReservaWeb] formularioReserva: apartamento cargado', [
            'apartamento_id' => $apartamento->id,
            'titulo' => $apartamento->titulo,
            'id_channex' => $apartamento->id_channex,
        ]);

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

        // Verificar disponibilidad en reservas confirmadas/pendientes
        $disponible = $this->verificarDisponibilidad($apartamento, $fechaEntrada, $fechaSalida);
        Log::info('[ReservaWeb] formularioReserva: verificación disponibilidad', [
            'apartamento_id' => $apartamento->id,
            'fecha_entrada' => $fechaEntrada->toDateString(),
            'fecha_salida' => $fechaSalida->toDateString(),
            'disponible' => $disponible,
        ]);

        if (!$disponible) {
            Log::info('[ReservaWeb] formularioReserva: no disponible, redirigiendo a ficha');
            return redirect()->route('web.reservas.show', $apartamentoId)
                ->with('error', 'El apartamento no está disponible para las fechas seleccionadas.')
                ->withInput();
        }

        // Verificar si ya existe un hold activo para estas fechas (otro cliente)
        $holdActivo = ReservaHold::where('apartamento_id', $apartamento->id)
            ->where('fecha_entrada', $fechaEntrada->toDateString())
            ->where('fecha_salida', $fechaSalida->toDateString())
            ->where('estado', 'activo')
            ->where('expires_at', '>', now())
            ->exists();

        Log::info('[ReservaWeb] formularioReserva: comprobación hold activo', [
            'apartamento_id' => $apartamento->id,
            'fechas' => [$fechaEntrada->toDateString(), $fechaSalida->toDateString()],
            'hold_activo_existe' => $holdActivo,
        ]);

        if ($holdActivo) {
            Log::info('[ReservaWeb] formularioReserva: hold activo de otro cliente, redirigiendo');
            return redirect()->route('web.reservas.show', $apartamentoId)
                ->with('error', 'En este momento otro cliente está completando una reserva para estas fechas. Por favor, prueba con otras fechas.')
                ->withInput();
        }

        // Calcular precio
        $precioPorNoche = $this->calcularPrecioPorNoche($apartamento, $fechaEntrada, $fechaSalida);
        if (!$precioPorNoche) {
            Log::warning('[ReservaWeb] formularioReserva: no se pudo calcular precio', [
                'apartamento_id' => $apartamento->id,
                'fecha_entrada' => $fechaEntrada->toDateString(),
                'fecha_salida' => $fechaSalida->toDateString(),
            ]);
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

        Log::info('[ReservaWeb] formularioReserva: precio calculado', [
            'apartamento_id' => $apartamento->id,
            'precio_por_noche' => $precioPorNoche,
            'noches' => $noches,
            'precio_total' => $precioTotal,
        ]);

        // Crear hold temporal en Channex mientras el cliente completa el formulario
        $holdToken = $this->crearHoldTemporal($apartamento, $fechaEntrada, $fechaSalida);

        if (!$holdToken) {
            Log::warning('[ReservaWeb] formularioReserva: fallo al crear hold temporal');
            return redirect()->route('web.reservas.show', $apartamentoId)
                ->with('error', 'No se pudo bloquear temporalmente el apartamento para completar la reserva. Por favor, inténtalo de nuevo.')
                ->withInput();
        }

        Log::info('[ReservaWeb] formularioReserva: hold creado, mostrando formulario', [
            'apartamento_id' => $apartamento->id,
            'hold_token_prefijo' => substr($holdToken, 0, 8) . '...',
            'expires_at_minutos' => config('app.web_reservas_hold_minutes', 10),
        ]);

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
            'holdToken' => $holdToken,
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
        Log::info('[ReservaWeb] procesarReserva: inicio', [
            'apartamento_id' => $request->input('apartamento_id'),
            'fecha_entrada' => $request->input('fecha_entrada'),
            'fecha_salida' => $request->input('fecha_salida'),
            'hold_token_prefijo' => $request->input('hold_token') ? substr($request->input('hold_token'), 0, 8) . '...' : null,
        ]);

        // Si las reservas web están deshabilitadas, bloquear procesamiento
        if (!config('app.web_reservas_enabled', false)) {
            Log::info('[ReservaWeb] procesarReserva: reservas web deshabilitadas, rechazando');
            return back()
                ->with('error', 'En este momento no se pueden realizar reservas online. Por favor, contacta con nosotros para reservar.')
                ->withInput();
        }

        $clienteLogueado = Auth::guard('cliente')->user();
        $esParaMi = $request->get('es_para_mi', false);

        // Validar hold temporal
        $holdToken = $request->input('hold_token');

        $hold = ReservaHold::where('hold_token', $holdToken)
            ->where('estado', 'activo')
            ->where('expires_at', '>', now())
            ->first();

        if (!$hold) {
            $holdExiste = ReservaHold::where('hold_token', $holdToken)->first();
            Log::warning('[ReservaWeb] procesarReserva: hold no válido o expirado', [
                'hold_token_prefijo' => $holdToken ? substr($holdToken, 0, 8) . '...' : null,
                'hold_encontrado' => (bool) $holdExiste,
                'estado' => $holdExiste ? $holdExiste->estado : null,
                'expires_at' => $holdExiste ? $holdExiste->expires_at?->toIso8601String() : null,
            ]);
            return back()
                ->with('error', 'Tu sesión de reserva ha caducado. Por favor, vuelve a buscar disponibilidad y selecciona de nuevo el apartamento.')
                ->withInput();
        }

        Log::info('[ReservaWeb] procesarReserva: hold válido', [
            'hold_id' => $hold->id,
            'apartamento_id' => $hold->apartamento_id,
            'fecha_entrada' => $hold->fecha_entrada,
            'fecha_salida' => $hold->fecha_salida,
            'expires_at' => $hold->expires_at->toIso8601String(),
        ]);

        // Validar que el hold corresponde con los datos enviados
        if (
            (int) $request->apartamento_id !== (int) $hold->apartamento_id ||
            $request->fecha_entrada !== $hold->fecha_entrada ||
            $request->fecha_salida !== $hold->fecha_salida
        ) {
            Log::warning('[ReservaWeb] procesarReserva: datos formulario no coinciden con hold', [
                'hold_id' => $hold->id,
                'request_apartamento_id' => $request->apartamento_id,
                'hold_apartamento_id' => $hold->apartamento_id,
                'request_fechas' => [$request->fecha_entrada, $request->fecha_salida],
                'hold_fechas' => [$hold->fecha_entrada, $hold->fecha_salida],
            ]);
            return back()
                ->with('error', 'Los datos de la reserva no coinciden con el bloqueo temporal. Por favor, vuelve a empezar el proceso.')
                ->withInput();
        }

        // Si está logueado y es para él, validar datos MIR
        if ($clienteLogueado && $esParaMi) {
            $datosFaltantes = $this->verificarDatosMIR($clienteLogueado);
            if (!empty($datosFaltantes)) {
                Log::info('[ReservaWeb] procesarReserva: datos MIR incompletos', [
                    'cliente_id' => $clienteLogueado->id,
                    'datos_faltantes' => $datosFaltantes,
                ]);
                return back()->with('error', 'Faltan datos necesarios para completar la reserva. Por favor, completa tu perfil.')->withInput();
            }
        }

        Log::info('[ReservaWeb] procesarReserva: validando datos del formulario');

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
            $disponible = $this->verificarDisponibilidad($apartamento, $fechaEntrada, $fechaSalida);
            Log::info('[ReservaWeb] procesarReserva: recheck disponibilidad', [
                'apartamento_id' => $apartamento->id,
                'disponible' => $disponible,
            ]);
            if (!$disponible) {
                Log::warning('[ReservaWeb] procesarReserva: ya no disponible al procesar');
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
                Log::error('[ReservaWeb] procesarReserva: Stripe secret key no configurada');
                return back()->with('error', 'El sistema de pagos no está configurado. Por favor, contacta con nosotros.')->withInput();
            }

            if (!class_exists('\Stripe\Stripe')) {
                Log::error('[ReservaWeb] procesarReserva: Stripe SDK no disponible');
                return back()->with('error', 'El sistema de pagos no está disponible. Por favor, contacta con nosotros.')->withInput();
            }

            Log::info('[ReservaWeb] procesarReserva: iniciando transacción (reserva, pago, hold, Stripe)');

            // Usar transacción para asegurar que todo se cree correctamente o se revierta
            return DB::transaction(function () use ($request, $apartamento, $fechaEntrada, $fechaSalida, $precioTotal, $noches, $stripeSecret, $clienteLogueado, $esParaMi, $hold) {
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
                Log::info('[ReservaWeb] procesarReserva: creando reserva y pago', [
                    'codigo_reserva' => $codigoReserva,
                    'cliente_id' => $cliente->id,
                    'apartamento_id' => $apartamento->id,
                    'precio_total' => $precioTotal,
                    'noches' => $noches,
                ]);
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

                // Asociar el hold a la reserva y marcarlo como confirmado
                $hold->reserva_id = $reserva->id;
                $hold->estado = 'confirmado';
                $hold->save();
                Log::info('[ReservaWeb] procesarReserva: hold confirmado', [
                    'hold_id' => $hold->id,
                    'reserva_id' => $reserva->id,
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

                    Log::info('[ReservaWeb] procesarReserva: sesión Stripe creada, redirigiendo', [
                        'session_id' => $checkoutSession->id,
                        'reserva_id' => $reserva->id,
                        'codigo_reserva' => $codigoReserva,
                        'pago_id' => $pago->id,
                        'checkout_url' => $checkoutSession->url ? 'present' : 'null',
                    ]);

                    // Si llegamos aquí, todo está bien, redirigir a Stripe
                    // La transacción se confirma automáticamente
                    return redirect($checkoutSession->url);
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    Log::error('[ReservaWeb] procesarReserva: error Stripe al crear sesión', [
                        'reserva_id' => $reserva->id ?? null,
                        'pago_id' => $pago->id ?? null,
                        'error' => $e->getMessage(),
                        'stripe_code' => $e->getStripeCode() ?? null,
                    ]);

                    // Lanzar excepción para que la transacción se revierta
                    throw new \Exception('Error al crear sesión de pago: ' . $e->getMessage());
                }
            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::info('[ReservaWeb] procesarReserva: validación fallida', [
                'errors' => $e->errors(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('[ReservaWeb] procesarReserva: excepción', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return back()->with('error', 'Hubo un error al procesar tu reserva. Por favor, inténtalo de nuevo.')->withInput();
        }
    }

    /**
     * Página de éxito después del pago
     */
    public function exito(Request $request)
    {
        $sessionId = $request->get('session_id');

        Log::info('[ReservaWeb] exito: entrada', [
            'session_id' => $sessionId ? substr($sessionId, 0, 20) . '...' : null,
        ]);

        if (!$sessionId) {
            Log::warning('[ReservaWeb] exito: sin session_id');
            return redirect()->route('web.index')->with('error', 'Sesión de pago no válida.');
        }

        try {
            if (class_exists('\Stripe\Stripe')) {
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                $session = \Stripe\Checkout\Session::retrieve($sessionId);

                $pago = Pago::where('stripe_checkout_session_id', $sessionId)->first();

                if (!$pago) {
                    Log::warning('[ReservaWeb] exito: pago no encontrado para session_id', [
                        'session_id_prefijo' => substr($sessionId, 0, 24),
                    ]);
                    return redirect()->route('web.index')->with('error', 'No se pudo verificar el pago.');
                }

                Log::info('[ReservaWeb] exito: pago encontrado', [
                    'pago_id' => $pago->id,
                    'reserva_id' => $pago->reserva_id,
                    'payment_status' => $session->payment_status ?? 'unknown',
                    'pago_estado' => $pago->estado,
                ]);

                if ($pago && $session->payment_status === 'paid') {
                    // El webhook debería haber actualizado esto, pero por si acaso
                    if ($pago->estado !== 'completado') {
                        $pago->update([
                            'estado' => 'completado',
                            'fecha_pago' => now(),
                            'stripe_payment_intent_id' => $session->payment_intent,
                        ]);
                        $pago->reserva->update(['estado_id' => 1]); // Confirmada
                        Log::info('[ReservaWeb] exito: pago y reserva actualizados en vista éxito', [
                            'pago_id' => $pago->id,
                            'reserva_id' => $pago->reserva_id,
                            'codigo_reserva' => $pago->reserva->codigo_reserva,
                        ]);
                    }

                    return view('public.reservas.reserva-exitosa', [
                        'reserva' => $pago->reserva,
                        'pago' => $pago,
                    ]);
                }

                Log::warning('[ReservaWeb] exito: payment_status no paid', [
                    'payment_status' => $session->payment_status ?? 'null',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('[ReservaWeb] exito: excepción', [
                'message' => $e->getMessage(),
                'session_id_prefijo' => $sessionId ? substr($sessionId, 0, 24) : null,
            ]);
        }

        return redirect()->route('web.index')->with('error', 'No se pudo verificar el pago.');
    }

    /**
     * Página de cancelación
     */
    public function cancelado(Request $request)
    {
        $reservaId = $request->get('reserva_id');

        Log::info('[ReservaWeb] cancelado: usuario volvió sin pagar', [
            'reserva_id' => $reservaId,
        ]);

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
     * Envía la actualización de disponibilidad a Channex después de crear una reserva.
     */
    private function updateChannexAvailability(Reserva $reserva)
    {
        $startDate = Carbon::parse($reserva->fecha_entrada);
        $endDate = Carbon::parse($reserva->fecha_salida)->subDay(); // Restamos un día a la fecha de salida

        $apartamento = Apartamento::with('roomTypes')->find($reserva->apartamento_id);

        if (!$apartamento || !$apartamento->id_channex) {
            Log::warning('[ReservaWeb] updateChannexAvailability: apartamento sin id_channex', [
                'reserva_id' => $reserva->id,
                'apartamento_id' => $reserva->apartamento_id,
            ]);
            return;
        }

        $roomType = null;

        // Si la reserva ya tiene room_type_id, verificar que pertenezca al apartamento
        if ($reserva->room_type_id) {
            $roomType = RoomType::where('id', $reserva->room_type_id)
                ->where('property_id', $reserva->apartamento_id)
                ->whereNotNull('id_channex')
                ->first();
        }

        // Si no hay room_type válido, obtener el primero del apartamento con id_channex
        if (!$roomType) {
            $roomType = $apartamento->roomTypes()
                ->whereNotNull('id_channex')
                ->first();

            // Si encontramos un room_type, actualizar la reserva
            if ($roomType) {
                $reserva->room_type_id = $roomType->id;
                $reserva->save();
            }
        }

        if (!$roomType || !$roomType->id_channex) {
            Log::warning('[ReservaWeb] updateChannexAvailability: room_type sin id_channex', [
                'reserva_id' => $reserva->id,
                'apartamento_id' => $reserva->apartamento_id,
                'room_type_id' => $reserva->room_type_id,
            ]);
            return;
        }

        $values = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $values[] = [
                'property_id' => $apartamento->id_channex,
                'room_type_id' => $roomType->id_channex,
                'date' => $date->toDateString(),
                'availability' => 0, // Bloqueamos la disponibilidad
            ];
        }

        Log::info('[ReservaWeb] updateChannexAvailability: enviando bloqueo a Channex', [
            'reserva_id' => $reserva->id,
            'apartamento_id' => $apartamento->id,
            'room_type_id' => $roomType->id,
            'fecha_entrada' => $reserva->fecha_entrada,
            'fecha_salida' => $reserva->fecha_salida,
            'dias' => count($values),
        ]);

        // Enviar actualización a Channex (sin verificación SSL)
        $response = Http::withoutVerifying()->withHeaders([
            'user-api-key' => $this->apiToken,
        ])->post("{$this->apiUrl}/availability", ['values' => $values]);

        if (!$response->successful()) {
            Log::error('[ReservaWeb] updateChannexAvailability: error Channex', [
                'reserva_id' => $reserva->id,
                'http_status' => $response->status(),
                'body' => $response->body(),
                'values' => $values,
            ]);
        } else {
            Log::info('[ReservaWeb] updateChannexAvailability: OK', [
                'reserva_id' => $reserva->id,
                'apartamento_id' => $apartamento->id,
                'apartamento_titulo' => $apartamento->titulo,
                'room_type_id' => $roomType->id,
                'response_body' => $response->body(),
            ]);
        }
    }

    /**
     * Crea un hold temporal en BD y bloquea disponibilidad en Channex.
     */
    private function crearHoldTemporal(Apartamento $apartamento, Carbon $fechaEntrada, Carbon $fechaSalida): ?string
    {
        Log::info('[ReservaWeb] crearHoldTemporal: inicio', [
            'apartamento_id' => $apartamento->id,
            'titulo' => $apartamento->titulo,
            'fecha_entrada' => $fechaEntrada->toDateString(),
            'fecha_salida' => $fechaSalida->toDateString(),
        ]);

        if (!$apartamento->id_channex) {
            Log::warning('[ReservaWeb] crearHoldTemporal: apartamento sin id_channex', [
                'apartamento_id' => $apartamento->id,
            ]);
            return null;
        }

        // Obtener un room_type válido para Channex
        $roomType = $apartamento->roomTypes()
            ->whereNotNull('id_channex')
            ->first();

        if (!$roomType) {
            Log::warning('[ReservaWeb] crearHoldTemporal: sin room_type con id_channex', [
                'apartamento_id' => $apartamento->id,
            ]);
            return null;
        }

        $startDate = $fechaEntrada->copy();
        $endDate = $fechaSalida->copy()->subDay();

        $values = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $values[] = [
                'property_id' => $apartamento->id_channex,
                'room_type_id' => $roomType->id_channex,
                'date' => $date->toDateString(),
                'availability' => 0, // Bloquear temporalmente
            ];
        }

        if (!$this->apiUrl || !$this->apiToken) {
            Log::error('[ReservaWeb] crearHoldTemporal: falta CHANNEX_URL o CHANNEX_TOKEN');
            return null;
        }

        $url = rtrim($this->apiUrl, '/') . '/availability';
        Log::info('[ReservaWeb] crearHoldTemporal: enviando POST a Channex', [
            'url' => $url,
            'dias' => count($values),
            'property_id' => $apartamento->id_channex,
            'room_type_id' => $roomType->id_channex,
        ]);

        $response = Http::withHeaders([
            'user-api-key' => $this->apiToken,
        ])->post($url, [
            'values' => $values,
        ]);

        if (!$response->successful()) {
            Log::error('[ReservaWeb] crearHoldTemporal: Channex rechazó la petición', [
                'apartamento_id' => $apartamento->id,
                'http_status' => $response->status(),
                'body' => $response->body(),
                'values' => $values,
            ]);
            return null;
        }

        $holdToken = Str::uuid()->toString();
        $expiresAt = now()->addMinutes(config('app.web_reservas_hold_minutes', 10));

        $hold = ReservaHold::create([
            'apartamento_id' => $apartamento->id,
            'room_type_id' => $roomType->id,
            'fecha_entrada' => $fechaEntrada->toDateString(),
            'fecha_salida' => $fechaSalida->toDateString(),
            'hold_token' => $holdToken,
            'estado' => 'activo',
            'expires_at' => $expiresAt,
        ]);

        Log::info('[ReservaWeb] crearHoldTemporal: hold creado', [
            'hold_id' => $hold->id,
            'apartamento_id' => $apartamento->id,
            'hold_token_prefijo' => substr($holdToken, 0, 8) . '...',
            'expires_at' => $expiresAt->toIso8601String(),
            'channex_response' => $response->body(),
        ]);

        return $holdToken;
    }
}
