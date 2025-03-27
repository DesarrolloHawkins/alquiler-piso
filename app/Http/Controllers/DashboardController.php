<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Gastos;
use App\Models\Ingresos;
use App\Models\Cliente;
use App\Models\Apartamento;
use App\Models\CategoriaGastos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request) {

        // **Obtenemos la fecha actual**
        $now = Carbon::now();

        // **Fechas predeterminadas si no se seleccionan**
        $fechaInicio = Carbon::parse($request->input('fecha_inicio', $now->startOfMonth()->toDateString()));
        $fechaFin = Carbon::parse($request->input('fecha_fin', $now->endOfMonth()->toDateString()));

        // **Filtrar reservas por rango de fechas**
        $reservas = Reserva::where('estado_id', '!=', 4)->where(function ($query) use ($fechaInicio, $fechaFin) {
            $query->whereBetween('fecha_entrada', [$fechaInicio, $fechaFin])
                ->orWhereBetween('fecha_salida', [$fechaInicio, $fechaFin])
                ->orWhere(function ($subQuery) use ($fechaInicio, $fechaFin) {
                    $subQuery->where('fecha_entrada', '<=', $fechaInicio)
                            ->where('fecha_salida', '>=', $fechaFin);
                });

        })->get();

        $fechaFin2 = Carbon::parse($request->input('fecha_fin', $now->endOfMonth()->toDateString()));

        // **Calcular ocupación diaria**
        $apartamentosDisponibles = Apartamento::whereNotNull('edificio')->count(); // Total apartamentos disponibles
        $apartamentos = Apartamento::whereNotNull('edificio')->get(); // Total apartamentos disponibles
        //$totalNochesPosibles = $apartamentosDisponibles * ($fechaInicio->diffInDays($fechaFin) + 1); // Capacidad máxima
        // $totalNochesPosibles = $apartamentosDisponibles * ($fechaInicio->diffInDays($fechaFin2) + 1);
        $nochesOcupadas = 0;

        foreach (CarbonPeriod::create($fechaInicio, $fechaFin) as $dia) {
            // Recorrer todos los apartamentos disponibles
            $apartamentos->each(function ($apartamento) use ($reservas, $dia, &$nochesOcupadas) {
                $estaOcupado = $reservas->filter(function ($reserva) use ($apartamento, $dia) {
                    $fechaEntrada = Carbon::parse($reserva->fecha_entrada);
                    $fechaSalida = isset($reserva->fecha_salida)
                        ? Carbon::parse($reserva->fecha_salida)
                        : $fechaEntrada->copy()->addDay(); // Suponemos 1 noche si no hay fecha_salida

                    return $reserva->apartamento_id === $apartamento->id && $dia->between($fechaEntrada, $fechaSalida);
                })->isNotEmpty();

                // Incrementar si el apartamento está ocupado ese día
                if ($estaOcupado) {
                    $nochesOcupadas++;
                }
            });
        }

        // **Calcular noches totales posibles basado en días y apartamentos disponibles**
        $totalNochesPosibles = $apartamentosDisponibles * ($fechaInicio->diffInDays($fechaFin) + 1);
        // **Calcular porcentaje de ocupación**
        $porcentajeOcupacion = ($totalNochesPosibles > 0)
        ? round(($nochesOcupadas / $totalNochesPosibles) * 100, 2)
        : 0;

        // **Otros cálculos (ingresos, gráficos, etc.)**
        $countReservas = $reservas->count();
        $sumPrecio = $reservas->sum('precio');

        // **Ingresos y gastos**
        $ingresos = Ingresos::whereBetween('date', [$fechaInicio, $fechaFin])->sum('quantity');
        $gastos = abs(Gastos::whereBetween('date', [$fechaInicio, $fechaFin])->sum('quantity'));

        // **Gráfico de Nacionalidades**
        $nacionalidades = Reserva::select('clientes.nacionalidad', DB::raw('COUNT(reservas.id) as total'))
            ->join('clientes', 'reservas.cliente_id', '=', 'clientes.id')
            ->whereBetween('reservas.fecha_entrada', [$fechaInicio, $fechaFin])
            ->groupBy('clientes.nacionalidad')
            ->get();

        $nacionalidadesConPorcentaje = $nacionalidades->map(function ($nacionalidad) use ($countReservas) {
            $nacionalidad->porcentaje = $countReservas > 0 ? round(($nacionalidad->total / $countReservas) * 100, 2) : 0;
            return $nacionalidad;
        });

        $labels = $nacionalidadesConPorcentaje->pluck('nacionalidad')->map(fn($nacionalidad) => $nacionalidad ?? 'Sin especificar')->toArray();
        $data = $nacionalidadesConPorcentaje->pluck('porcentaje')->toArray();

        // **Gráfico de Rangos de Edad**
        $rangoDefinido = [
            'Ns-nc' => 0,
            '18-30' => 0,
            '31-45' => 0,
            '46-60' => 0,
            '60+' => 0,
        ];

        $clientes = Cliente::select(DB::raw("
            CASE
                WHEN fecha_nacimiento IS NULL THEN 'Ns-nc'
                WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 18 AND 30 THEN '18-30'
                WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 31 AND 45 THEN '31-45'
                WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 46 AND 60 THEN '46-60'
                WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) > 60 THEN '60+'
            END as rango_edad,
            COUNT(*) as total
        "))
        ->join('reservas', 'clientes.id', '=', 'reservas.cliente_id')
        ->whereBetween('reservas.fecha_entrada', [$fechaInicio, $fechaFin])
        ->groupBy('rango_edad')
        ->pluck('total', 'rango_edad');

        foreach ($clientes as $rango => $total) {
            if (array_key_exists($rango, $rangoDefinido)) {
                $rangoDefinido[$rango] = $total;
            }
        }

        $totalClientes = array_sum($rangoDefinido);
        $edadesPorcentaje = array_map(function ($total) use ($totalClientes) {
            return $totalClientes > 0 ? round(($total / $totalClientes) * 100, 2) : 0;
        }, $rangoDefinido);

        $rangoEdades = array_keys($rangoDefinido);
        $totalesEdades = array_values($edadesPorcentaje);

        // **Gráfico de Ocupantes**
        $ocupantesDefinidos = [
            '01' => 0,
            '02' => 0,
            '03' => 0,
            '04' => 0,
            '05' => 0,
            '06' => 0,
        ];

        $ocupantes = Reserva::select('numero_personas', DB::raw('COUNT(*) as total'))
            ->whereBetween('fecha_entrada', [$fechaInicio, $fechaFin])
            ->groupBy('numero_personas')
            ->pluck('total', 'numero_personas');

        foreach ($ocupantes as $numero => $total) {
            if (array_key_exists(str_pad($numero, 2, '0', STR_PAD_LEFT), $ocupantesDefinidos)) {
                $ocupantesDefinidos[str_pad($numero, 2, '0', STR_PAD_LEFT)] = $total;
            }
        }

        $totalOcupantes = array_sum($ocupantesDefinidos);
        $porcentajesOcupantes = array_map(function ($total) use ($totalOcupantes) {
            return $totalOcupantes > 0 ? round(($total / $totalOcupantes) * 100, 2) : 0;
        }, $ocupantesDefinidos);

        $ocupantesLabels = array_keys($ocupantesDefinidos);
        $ocupantesData = array_values($porcentajesOcupantes);

        // **Gráfico de Sexo**
        $sexoDefinido = [
            'Hombre' => 0,
            'Mujer' => 0,
            'Sin definir' => 0,
        ];

        $clientesSexo = Cliente::select(DB::raw("
            CASE
                WHEN sexo IS NULL THEN 'Sin definir'
                WHEN sexo = 'Masculino' THEN 'Hombre'
                WHEN sexo = 'Femenino' THEN 'Mujer'
            END as genero,
            COUNT(*) as total
        "))
        ->join('reservas', 'clientes.id', '=', 'reservas.cliente_id')
        ->whereBetween('reservas.fecha_entrada', [$fechaInicio, $fechaFin])
        ->groupBy('genero')
        ->pluck('total', 'genero');

        foreach ($clientesSexo as $genero => $total) {
            if (array_key_exists($genero, $sexoDefinido)) {
                $sexoDefinido[$genero] = $total;
            }
        }

        $totalSexo = array_sum($sexoDefinido);
        $porcentajesSexo = array_map(function ($total) use ($totalSexo) {
            return $totalSexo > 0 ? round(($total / $totalSexo) * 100, 2) : 0;
        }, $sexoDefinido);

        $sexoLabels = array_keys($sexoDefinido);
        $sexoData = array_values($porcentajesSexo);

        // **Gráfico de Prescriptores**
        $prescriptoresDefinidos = [
            'Booking' => 0,
            'Airbnb' => 0,
            'Externo' => 0,
        ];

        $prescriptores = Reserva::select('origen', DB::raw('COUNT(*) as total'))
            ->whereBetween('fecha_entrada', [$fechaInicio, $fechaFin])
            ->groupBy('origen')
            ->pluck('total', 'origen');

        foreach ($prescriptores as $origen => $total) {
            if (array_key_exists($origen, $prescriptoresDefinidos)) {
                $prescriptoresDefinidos[$origen] = $total;
            }
        }

        $totalPrescriptores = array_sum($prescriptoresDefinidos);
        $porcentajesPrescriptores = array_map(function ($total) use ($totalPrescriptores) {
            return $totalPrescriptores > 0 ? round(($total / $totalPrescriptores) * 100, 2) : 0;
        }, $prescriptoresDefinidos);

        $prescriptoresLabels = array_keys($prescriptoresDefinidos);
        $prescriptoresData = array_values($porcentajesPrescriptores);

        // **Gráfico de Apartamentos**
        $apartamentos = Apartamento::select('titulo')->get();
        $apartamentosDefinidos = $apartamentos->pluck('titulo')->mapWithKeys(fn($titulo) => [$titulo => 0])->toArray();

        $reservasPorApartamento = Reserva::select('apartamento_id', DB::raw('COUNT(*) as total'))
            ->whereBetween('fecha_entrada', [$fechaInicio, $fechaFin])
            ->groupBy('apartamento_id')
            ->pluck('total', 'apartamento_id');

        foreach ($reservasPorApartamento as $apartamentoId => $total) {
            $apartamento = Apartamento::find($apartamentoId);
            if ($apartamento && array_key_exists($apartamento->titulo, $apartamentosDefinidos)) {
                $apartamentosDefinidos[$apartamento->titulo] = $total;
            }
        }

        $totalReservasPorApartamento = array_sum($apartamentosDefinidos);
        $porcentajesApartamentos = array_map(function ($total) use ($totalReservasPorApartamento) {
            return $totalReservasPorApartamento > 0 ? round(($total / $totalReservasPorApartamento) * 100, 2) : 0;
        }, $apartamentosDefinidos);

        $apartamentosLabels = array_keys($apartamentosDefinidos);
        $apartamentosData = array_values($porcentajesApartamentos);

        // **Gastos por Categoría**
        $gastosPorCategoria = Gastos::select('categoria_gastos.nombre', DB::raw('SUM(ABS(gastos.quantity)) as total'))
            ->join('categoria_gastos', 'gastos.categoria_id', '=', 'categoria_gastos.id')
            ->whereBetween('gastos.date', [$fechaInicio, $fechaFin])
            ->groupBy('categoria_gastos.nombre')
            ->pluck('total', 'nombre');

        $totalGastos = array_sum($gastosPorCategoria->toArray());
        $porcentajesGastos = $gastosPorCategoria->map(function ($total) use ($totalGastos) {
            return $totalGastos > 0 ? round(($total / $totalGastos) * 100, 2) : 0;
        });

        $categoriasLabels = $porcentajesGastos->keys()->toArray();
        $categoriasData = $porcentajesGastos->values()->toArray();
        $hoy = Carbon::today();

        // Obtener apartamentos ocupados hoy
        $apartamentosOcupadosHoy = Reserva::where('estado_id', '!=', 4)
            ->where(function ($query) use ($hoy) {
                $query->where('fecha_entrada', '<=', $hoy)
                    ->where('fecha_salida', '>=', $hoy);
            })
            ->pluck('apartamento_id');

        // Obtener apartamentos libres hoy
        $apartamentosLibresHoy = Apartamento::whereNotIn('id', $apartamentosOcupadosHoy)
            ->whereNotNull('edificio')
            ->get();


        $ingresosLista = Ingresos::whereBetween('date', [$fechaInicio, $fechaFin])->get();
        $gastosLista = Gastos::whereBetween('date', [$fechaInicio, $fechaFin])->get();
        $categoriasGastos = CategoriaGastos::all();

        return view('admin.dashboard', compact(
            'countReservas',
            'sumPrecio',
            'fechaInicio',
            'fechaFin',
            'ingresos',
            'gastos',
            'labels',
            'data',
            'rangoEdades',
            'totalesEdades',
            'ocupantesLabels',
            'ocupantesData',
            'sexoLabels',
            'sexoData',
            'prescriptoresLabels',
            'prescriptoresData',
            'apartamentosLabels',
            'apartamentosData',
            'categoriasLabels',
            'categoriasData',
            'porcentajeOcupacion',
            'nochesOcupadas',
            'totalNochesPosibles',
            'apartamentosLibresHoy',
            'ingresosLista',
            'gastosLista',
            'categoriasGastos',
            'reservas' // ← ¡Añádelo aquí!

            // 'diasDelMes',
            // 'apartamentosDisponibles'
        ));
    }

    // public function index(Request $request) {
    //     $fechaInicio = $request->input('fecha_inicio');
    //     $fechaFin = $request->input('fecha_fin');

    //     $now = Carbon::now();
    //     $anioActual = $now->format('Y');
    //     $mesActual = $now->format('m');
    //     $anioAnterior = date('Y', strtotime('-1 year'));
    //     $anioReturn = $anioActual;
    //     $mesReturn = $mesActual;

    //     // Fechas por defecto si no se proporcionan
    //     $fechaInicio = $fechaInicio ?? $now->startOfMonth()->toDateString();
    //     $fechaFin = $fechaFin ?? $now->endOfMonth()->toDateString();

    //     $anio = $request->input('anio', $anioActual);
    //     $mes = $request->input('mes', null);

    //     // Inicia la consulta para las reservas
    //     $reservas = Reserva::whereYear('fecha_entrada', $anio);
    //     if ($mes) {
    //         $reservas->whereMonth('fecha_entrada', $mes);
    //     }

    //     $countReservas = $reservas->count();
    //     $sumPrecio = $reservas->sum('precio');
    //     $reservas = $reservas->get();
    //     $mesNombre = $mes ? Carbon::create()->month($mes)->locale('es')->monthName : null;

    //     // Obtener datos de ingresos y gastos
    //     $ingresos = Ingresos::whereYear('date', $anio)
    //         ->when($mes, fn($query) => $query->whereMonth('date', $mes))
    //         ->sum('quantity');

    //     $gastos = abs(Gastos::whereYear('date', $anio)
    //         ->when($mes, fn($query) => $query->whereMonth('date', $mes))
    //         ->sum('quantity'));

    //     // Gráfico de nacionalidades
    //     $nacionalidades = Reserva::select('clientes.nacionalidad', DB::raw('COUNT(reservas.id) as total'))
    //         ->join('clientes', 'reservas.cliente_id', '=', 'clientes.id')
    //         ->whereYear('reservas.fecha_entrada', $anio)
    //         ->when($mes, fn($query) => $query->whereMonth('reservas.fecha_entrada', $mes))
    //         ->groupBy('clientes.nacionalidad')
    //         ->get();

    //     $nacionalidadesConPorcentaje = $nacionalidades->map(function ($nacionalidad) use ($countReservas) {
    //         $nacionalidad->porcentaje = $countReservas > 0 ? round(($nacionalidad->total / $countReservas) * 100, 2) : 0;
    //         return $nacionalidad;
    //     });

    //     $labels = $nacionalidadesConPorcentaje->pluck('nacionalidad')->map(fn($nacionalidad) => $nacionalidad ?? 'Sin especificar')->toArray();
    //     $data = $nacionalidadesConPorcentaje->pluck('porcentaje')->toArray();

    //     // Gráfico de rangos de edad
    //     $rangoDefinido = [
    //         'Ns-nc' => 0,
    //         '18-30' => 0,
    //         '31-45' => 0,
    //         '46-60' => 0,
    //         '60+' => 0,
    //     ];

    //     $clientes = Cliente::select(DB::raw("
    //         CASE
    //             WHEN fecha_nacimiento IS NULL THEN 'Ns-nc'
    //             WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 18 AND 30 THEN '18-30'
    //             WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 31 AND 45 THEN '31-45'
    //             WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 46 AND 60 THEN '46-60'
    //             WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) > 60 THEN '60+'
    //         END as rango_edad,
    //         COUNT(*) as total
    //     "))
    //     ->join('reservas', 'clientes.id', '=', 'reservas.cliente_id')
    //     ->whereYear('reservas.fecha_entrada', $anio)
    //     ->when($mes, fn($query) => $query->whereMonth('reservas.fecha_entrada', $mes))
    //     ->groupBy('rango_edad')
    //     ->pluck('total', 'rango_edad');

    //     foreach ($clientes as $rango => $total) {
    //         if (array_key_exists($rango, $rangoDefinido)) {
    //             $rangoDefinido[$rango] = $total;
    //         }
    //     }

    //     $totalClientes = array_sum($rangoDefinido);
    //     $edadesPorcentaje = array_map(function ($total) use ($totalClientes) {
    //         return $totalClientes > 0 ? round(($total / $totalClientes) * 100, 2) : 0;
    //     }, $rangoDefinido);

    //     $rangoEdades = array_keys($rangoDefinido);
    //     $totalesEdades = array_values($edadesPorcentaje);

    //     // **Estadísticas de Ocupantes**
    //     $ocupantesDefinidos = [
    //         '01' => 0,
    //         '02' => 0,
    //         '03' => 0,
    //         '04' => 0,
    //         '05' => 0,
    //         '06' => 0,
    //     ];

    //     $ocupantes = Reserva::select('numero_personas', DB::raw('COUNT(*) as total'))
    //         ->whereYear('fecha_entrada', $anio)
    //         ->when($mes, fn($query) => $query->whereMonth('fecha_entrada', $mes))
    //         ->groupBy('numero_personas')
    //         ->pluck('total', 'numero_personas');

    //     foreach ($ocupantes as $numero => $total) {
    //         if (array_key_exists(str_pad($numero, 2, '0', STR_PAD_LEFT), $ocupantesDefinidos)) {
    //             $ocupantesDefinidos[str_pad($numero, 2, '0', STR_PAD_LEFT)] = $total;
    //         }
    //     }

    //     $totalOcupantes = array_sum($ocupantesDefinidos);
    //     $porcentajesOcupantes = array_map(function ($total) use ($totalOcupantes) {
    //         return $totalOcupantes > 0 ? round(($total / $totalOcupantes) * 100, 2) : 0;
    //     }, $ocupantesDefinidos);

    //     $ocupantesLabels = array_keys($ocupantesDefinidos);
    //     $ocupantesData = array_values($porcentajesOcupantes);

    //     // **Gráfico de Sexo**
    //     $sexoDefinido = [
    //         'Hombre' => 0,
    //         'Mujer' => 0,
    //         'Sin definir' => 0,
    //     ];

    //     $clientesSexo = Cliente::select(DB::raw("
    //         CASE
    //             WHEN sexo IS NULL THEN 'Sin definir'
    //             WHEN sexo = 'Masculino' THEN 'Hombre'
    //             WHEN sexo = 'Femenino' THEN 'Mujer'
    //         END as genero,
    //         COUNT(*) as total
    //     "))
    //     ->join('reservas', 'clientes.id', '=', 'reservas.cliente_id')
    //     ->whereYear('reservas.fecha_entrada', $anio)
    //     ->when($mes, fn($query) => $query->whereMonth('reservas.fecha_entrada', $mes))
    //     ->groupBy('genero')
    //     ->pluck('total', 'genero');

    //     foreach ($clientesSexo as $genero => $total) {
    //         if (array_key_exists($genero, $sexoDefinido)) {
    //             $sexoDefinido[$genero] = $total;
    //         }
    //     }

    //     $totalSexo = array_sum($sexoDefinido);
    //     $porcentajesSexo = array_map(function ($total) use ($totalSexo) {
    //         return $totalSexo > 0 ? round(($total / $totalSexo) * 100, 2) : 0;
    //     }, $sexoDefinido);

    //     $sexoLabels = array_keys($sexoDefinido);
    //     $sexoData = array_values($porcentajesSexo);


    //     // **Gráfico de Prescriptores**
    //     $prescriptoresDefinidos = [
    //         'Booking' => 0,
    //         'Airbnb' => 0,
    //         'Externo' => 0,
    //     ];

    //     $prescriptores = Reserva::select('origen', DB::raw('COUNT(*) as total'))
    //         ->whereYear('fecha_entrada', $anio)
    //         ->when($mes, fn($query) => $query->whereMonth('fecha_entrada', $mes))
    //         ->groupBy('origen')
    //         ->pluck('total', 'origen');

    //     foreach ($prescriptores as $origen => $total) {
    //         if (array_key_exists($origen, $prescriptoresDefinidos)) {
    //             $prescriptoresDefinidos[$origen] = $total;
    //         }
    //     }

    //     $totalPrescriptores = array_sum($prescriptoresDefinidos);
    //     $porcentajesPrescriptores = array_map(function ($total) use ($totalPrescriptores) {
    //         return $totalPrescriptores > 0 ? round(($total / $totalPrescriptores) * 100, 2) : 0;
    //     }, $prescriptoresDefinidos);

    //     $prescriptoresLabels = array_keys($prescriptoresDefinidos);
    //     $prescriptoresData = array_values($porcentajesPrescriptores);



    //     // **Gráfico de Apartamentos**
    //     $apartamentos = Apartamento::select('titulo')->get();
    //     $apartamentosDefinidos = $apartamentos->pluck('titulo')->mapWithKeys(fn($titulo) => [$titulo => 0])->toArray();

    //     $reservasPorApartamento = Reserva::select('apartamento_id', DB::raw('COUNT(*) as total'))
    //         ->whereYear('fecha_entrada', $anio)
    //         ->when($mes, fn($query) => $query->whereMonth('fecha_entrada', $mes))
    //         ->groupBy('apartamento_id')
    //         ->pluck('total', 'apartamento_id');

    //     foreach ($reservasPorApartamento as $apartamentoId => $total) {
    //         $apartamento = Apartamento::find($apartamentoId);
    //         if ($apartamento && array_key_exists($apartamento->titulo, $apartamentosDefinidos)) {
    //             $apartamentosDefinidos[$apartamento->titulo] = $total;
    //         }
    //     }

    //     $totalReservasPorApartamento = array_sum($apartamentosDefinidos);
    //     $porcentajesApartamentos = array_map(function ($total) use ($totalReservasPorApartamento) {
    //         return $totalReservasPorApartamento > 0 ? round(($total / $totalReservasPorApartamento) * 100, 2) : 0;
    //     }, $apartamentosDefinidos);

    //     $apartamentosLabels = array_keys($apartamentosDefinidos);
    //     $apartamentosData = array_values($porcentajesApartamentos);


    //      // Consulta de gastos categorizados
    //     $gastosPorCategoria = Gastos::select('categoria_gastos.nombre', DB::raw('SUM(ABS(gastos.quantity)) as total'))
    //     ->join('categoria_gastos', 'gastos.categoria_id', '=', 'categoria_gastos.id')
    //     ->whereYear('gastos.date', $anio)
    //     ->when($mes, fn($query) => $query->whereMonth('gastos.date', $mes))
    //     ->groupBy('categoria_gastos.nombre')
    //     ->pluck('total', 'nombre');

    //     // Total de gastos
    //     $totalGastos = array_sum($gastosPorCategoria->toArray());

    //     // Calcular porcentajes
    //     $porcentajesGastos = $gastosPorCategoria->map(function ($total) use ($totalGastos) {
    //         return $totalGastos > 0 ? round(($total / $totalGastos) * 100, 2) : 0;
    //     });

    //     $categoriasLabels = $porcentajesGastos->keys()->toArray();
    //     $categoriasData = $porcentajesGastos->values()->toArray();


    //     return view('admin.dashboard', compact(
    //         'countReservas',
    //         'sumPrecio',
    //         'anio',
    //         'mes',
    //         'reservas',
    //         'mesNombre',
    //         'anioActual',
    //         'mesActual',
    //         'anioAnterior',
    //         'mesReturn',
    //         'anioReturn',
    //         'ingresos',
    //         'gastos',
    //         'labels',
    //         'data',
    //         'rangoEdades',
    //         'totalesEdades',
    //         'ocupantesLabels',
    //         'ocupantesData',
    //         'sexoLabels',
    //         'sexoData',
    //         'prescriptoresLabels',
    //         'prescriptoresData',
    //         'apartamentosLabels',
    //         'apartamentosData',
    //         'categoriasLabels',
    //         'categoriasData'
    //     ));
    // }

}
