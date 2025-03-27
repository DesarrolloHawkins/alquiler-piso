@extends('layouts.appAdmin')

@section('title', 'Dashboard')

@section('content')
<style>
    .bg-primero {
        background: rgb(89,188,255);
        background: -moz-linear-gradient(90deg, rgba(89,188,255,1) 0%, rgba(144,223,254,1) 100%);
        background: -webkit-linear-gradient(90deg, rgba(89,188,255,1) 0%, rgba(144,223,254,1) 100%);
        background: linear-gradient(90deg, rgba(89,188,255,1) 0%, rgba(144,223,254,1) 100%);
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#59bcff",endColorstr="#90dffe",GradientType=1);
    }
</style>
<div class="container-fluid">
    <!-- jQuery y DataTables (cargados ANTES de usarlos) -->
    {{-- <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script> --}}

    <h1 class="mb-4">DASHBOARD</h1>
    <form action="{{route('dashboard.index')}}" class="row align-items-end" method="GET">

        <h4>Filtrado por fechas</h4>
        <div class="col-md-3">
            <label for="fecha_inicio">Fecha Inicio</label>
            <input type="text" id="fecha_inicio" name="fecha_inicio" class="form-control flatpickr"
                value="{{ request('fecha_inicio', '') }}" placeholder="Selecciona Fecha Inicio">
        </div>
        <div class="col-md-3">
            <label for="fecha_fin">Fecha Fin</label>
            <input type="text" id="fecha_fin" name="fecha_fin" class="form-control flatpickr"
                value="{{ request('fecha_fin', '') }}" placeholder="Selecciona Fecha Fin">
        </div>
        <div class="col-md-3 col-sm-12 mt-4">
            <button type="submit" class="btn bg-color-primero w-sm-100 w-md-auto text-uppercase">Buscar</button>
        </div>
        <div class="col-md-3">
            <button id="verReservasBtn" class="btn bg-color-segundo w-sm-100 w-md-auto text-uppercase">Ver Reservas</button>
        </div>
    </form>
    <br>
    <div class="row" style="padding: 1rem;">
        <div class="col-12 mb-5">
            <div class="row justify-content-between align-items-stretch">
                <h3 class="text-center mt-5">Información de Gestión</h3>
                <hr>
                <div class="col-xl-4 col-md-6">
                    <div class="row p-3 card m-3 flex-row pe-auto" data-bs-toggle="modal" data-bs-target="#modalLibresHoy" style="cursor: pointer">
                        <div class="col-8">
                            <h4 class="text-start
                            mb-0 fs-5">Apartamentos Libres Hoy</h4>
                        </div>
                        <div class="col-4">
                            <h2 class="text-end mb-0 fs-4"><strong>{{$apartamentosLibresHoy->count()}}</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="row p-3 card m-3 flex-row pe-auto" data-bs-toggle="modal" data-bs-target="#modalReservasTotales" style="cursor: pointer">
                        <div class="col-8">
                            <h4 class="text-start mb-0 fs-5">Total de Reservas</h4>
                        </div>
                        <div class="col-4">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ $countReservas }}</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="row p-3 card m-3 flex-row">
                        <div class="col-7">
                            <h4 class="text-start mb-0 fs-5">Ocupacción %</h4>
                        </div>
                        <div class="col-5">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ $porcentajeOcupacion }} %</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="row p-3 card m-3 flex-row">
                        <div class="col-8">
                            <h4 class="text-start mb-0 fs-5">Ocupación</h4>
                        </div>
                        <div class="col-4">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ $nochesOcupadas }}</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="row p-3 card m-3 flex-row">
                        <div class="col-7">
                            <h4 class="text-start mb-0 fs-5">Ocupación Disponibles</h4>
                        </div>
                        <div class="col-5">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ $totalNochesPosibles }}</strong></h2>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <div class="row justify-content-between align-items-stretch">
                <h3 class="text-center mt-4">Información de Economica</h3>
                <hr>
                <div class="col-xl-4 col-md-6">
                    <div class="row p-3 card m-3 flex-row" data-bs-toggle="modal" data-bs-target="#modalFacturacion" style="cursor:pointer;">
                        <div class="col-5">
                            <h4 class="text-start mb-0 fs-5">Facturación</h4>
                        </div>
                        <div class="col-7">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($sumPrecio, 2) }} €</strong></h2>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="row p-3 card m-3 flex-row" data-bs-toggle="modal" data-bs-target="#modalCobrado" style="cursor:pointer;">
                        <div class="col-5">
                          <h4 class="text-start mb-0 fs-5">Cobrado</h4>
                        </div>
                        <div class="col-7">
                          <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($ingresos, 2) }} €</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="row p-3 card m-3 flex-row">
                        <div class="col-6">
                            <h4 class="text-start mb-0 fs-5">Cash Flow</h4>
                        </div>
                        <div class="col-6">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($ingresos - $gastos, 2) }} €</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="row p-3 card m-3 flex-row" data-bs-toggle="modal" data-bs-target="#modalIngresos" style="cursor:pointer;">
                        <div class="col-6">
                          <h4 class="text-start mb-0 fs-5">Ingresos</h4>
                        </div>
                        <div class="col-6">
                          <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($ingresos, 2) }} €</strong></h2>
                        </div>
                      </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="row p-3 card m-3 flex-row" data-bs-toggle="modal" data-bs-target="#modalGastos" style="cursor:pointer;">
                        <div class="col-6">
                          <h4 class="text-start mb-0 fs-5">Gastos</h4>
                        </div>
                        <div class="col-6">
                          <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($gastos, 2) }} €</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="row p-3 card m-3 flex-row">
                        <div class="col-6">
                            <h4 class="text-start mb-0 fs-5">Beneficio</h4>
                        </div>
                        <div class="col-6">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($sumPrecio - $gastos, 2) }} €</strong></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- <div class="col-md-3">
            <div class="row mx-1 bg-primero p-3 rounded-4">
                <div class="col-9">
                    <h4>{{$countReservas}}</h4>
                    <p>Reservas Año Actual</p>
                </div>
                <div class="col-3"></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="row mx-1 bg-success p-3 rounded-4">
                <div class="col-9">
                    <h4>{{$sumPrecio}} €</h4>
                    <p>Ingresos Año Actual</p>
                </div>
                <div class="col-3"></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="row mx-1 bg-warning p-3">
                <div class="col-9">
                    <h4>800</h4>
                    <p>New Booking</p>
                </div>
                <div class="col-3"></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="row mx-1 bg-danger p-3">
                <div class="col-9">
                    <h4>800</h4>
                    <p>New Booking</p>
                </div>
                <div class="col-3"></div>
            </div>
        </div>
    </div> --}}
    <h3 class="text-center mt-4">Estadisticas</h3>
    <hr>
    <div class="row justify-content-between align-items-stretch ">
        <div class="col-xl-12 col-md-12 rounded-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="chartNacionalidad"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-md-12 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Balance</h2>
                    <div id="chart"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-md-12 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Distribución por Género</h2>
                    <div id="chartSexo"></div>
                </div>

            </div>
        </div>
        <div class="col-md-6 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Distribución por Ocupantes</h2>
                    <div id="chartOcupantes"></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Distribución de Clientes por Rango de Edad</h2>
                    <div id="chartEdad"></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Distribución de Prescriptores</h2>
                    <div id="chartPrescriptores"></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Distribución de Reservas por Apartamento</h2>
                    <div id="chartApartamentos"></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Distribución de Gastos por Categoría</h2>
                    <div id="chartGastos"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODALES --}}
<!-- Modal de Apartamentos Libres Hoy -->
<div class="modal fade" id="modalLibresHoy" tabindex="-1" aria-labelledby="modalLibresHoyLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modalLibresHoyLabel"> {{$apartamentosLibresHoy->count()}} Apartamentos libres para hoy</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <table id="tablaLibresHoy" class="display table table-bordered">
              <thead>
                  <tr>
                      <th>Nombre</th>
                  </tr>
              </thead>
              <tbody>
                  @foreach($apartamentosLibresHoy as $apartamento)
                      <tr>
                          <td>{{ $apartamento->nombre }}</td>
                      </tr>
                  @endforeach
              </tbody>
          </table>
        </div>
      </div>
    </div>
</div>


<!-- Modal de Reservas Totales -->
<div class="modal fade" id="modalReservasTotales" tabindex="-1" aria-labelledby="modalReservasTotalesLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalReservasTotalesLabel">{{ $countReservas }} Reservas entre {{ $fechaInicio->format('d/m/Y') }} y {{ $fechaFin->format('d/m/Y') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="filtroApartamento" class="form-label">Filtrar por Apartamento</label>
                    <select id="filtroApartamento" class="form-select">
                        <option value="">Todos</option>
                        @foreach($reservas->pluck('apartamento.titulo')->unique()->filter()->sort() as $apartamento)
                            <option value="{{ $apartamento }}">{{ $apartamento }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filtroOrigen" class="form-label">Filtrar por Origen</label>
                    <select id="filtroOrigen" class="form-select">
                        <option value="">Todos</option>
                        @foreach($reservas->pluck('origen')->unique()->filter()->sort() as $origen)
                            <option value="{{ $origen }}">{{ $origen }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="searchTable" class="form-label">Buscar</label>
                    <input type="text" id="searchTable" class="form-control" placeholder="Buscar en la tabla...">
                </div>
            </div>
            <h3 id="totalPrecioFiltrado" class="text-end my-3">Total: 0.00 €</h3>

          <table id="tablaReservasTotales" class="display table table-bordered">
              <thead>
                  <tr>
                      <th>Cliente</th>
                      <th>Apartamento</th>
                      <th>Entrada</th>
                      <th>Salida</th>
                      <th>Precio</th>
                      <th>Nº Personas</th>
                      <th>Origen</th>
                  </tr>
              </thead>
              <tbody>
                  @foreach($reservas as $reserva)
                      <tr>
                          <td>{{ $reserva->cliente->nombre ?? $reserva->cliente->alias }}</td>
                          <td>{{ $reserva->apartamento->titulo ?? 'Sin título' }}</td>
                          <td>{{ \Carbon\Carbon::parse($reserva->fecha_entrada)->format('d/m/Y') }}</td>
                          <td>{{ \Carbon\Carbon::parse($reserva->fecha_salida)->format('d/m/Y') }}</td>
                          <td>{{ number_format($reserva->precio, 2) }} €</td>
                          <td>{{ $reserva->numero_personas }}</td>
                          <td>{{ $reserva->origen ?? 'No definido' }}</td>
                      </tr>
                  @endforeach
              </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>


  <!-- Modal de Facturación -->
<div class="modal fade" id="modalFacturacion" tabindex="-1" aria-labelledby="modalFacturacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalFacturacionLabel">Detalle de Facturación</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="filtroApartamentoFacturacion" class="form-label">Filtrar por Apartamento</label>
                    <select id="filtroApartamentoFacturacion" class="form-select">
                        <option value="">Todos</option>
                        @foreach($reservas->pluck('apartamento.titulo')->unique()->filter()->sort() as $apartamento)
                            <option value="{{ $apartamento }}">{{ $apartamento }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filtroOrigenFacturacion" class="form-label">Filtrar por Origen</label>
                    <select id="filtroOrigenFacturacion" class="form-select">
                        <option value="">Todos</option>
                        @foreach($reservas->pluck('origen')->unique()->filter()->sort() as $origen)
                            <option value="{{ $origen }}">{{ $origen }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="searchFacturacion" class="form-label">Buscar</label>
                    <input type="text" id="searchFacturacion" class="form-control" placeholder="Buscar...">
                </div>
            </div>

            <h3>Total filtrado: <span id="totalFiltradoFacturacion">{{ number_format($reservas->sum('precio'), 2) }} €</span></h3>

            <table id="tablaFacturacion" class="display table table-bordered">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Apartamento</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Precio</th>
                        <th>Nº Personas</th>
                        <th>Origen</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservas as $reserva)
                        <tr>
                            <td>{{ $reserva->cliente->nombre ?? $reserva->cliente->alias }}</td>
                            <td>{{ $reserva->apartamento->titulo ?? 'Sin título' }}</td>
                            <td>{{ \Carbon\Carbon::parse($reserva->fecha_entrada)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($reserva->fecha_salida)->format('d/m/Y') }}</td>
                            <td data-precio="{{ $reserva->precio }}">{{ number_format($reserva->precio, 2) }} €</td>
                            <td>{{ $reserva->numero_personas }}</td>
                            <td>{{ $reserva->origen ?? 'No definido' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
      </div>
    </div>
</div>


<!-- Modal de Cobrado -->
<div class="modal fade" id="modalCobrado" tabindex="-1" aria-labelledby="modalCobradoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCobradoLabel">Cobros entre {{ $fechaInicio->format('d/m/Y') }} y {{ $fechaFin->format('d/m/Y') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-md-4">
              <label for="filtroConceptoCobrado" class="form-label">Filtrar por Concepto</label>
              <select id="filtroConceptoCobrado" class="form-select">
                <option value="">Todos</option>
                @foreach($ingresosLista->pluck('concept')->unique()->filter()->sort() as $concepto)
                  <option value="{{ $concepto }}">{{ $concepto }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label for="searchCobrado" class="form-label">Buscar</label>
              <input type="text" id="searchCobrado" class="form-control" placeholder="Buscar en la tabla...">
            </div>
            <div class="col-md-4 text-end">
              <h4>Total Cobrado: <span id="totalFiltradoCobrado">{{ number_format($ingresos, 2) }} €</span></h4>
            </div>
          </div>

          <table id="tablaCobrado" class="display table table-bordered">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Cantidad</th>
              </tr>
            </thead>
            <tbody>
              @foreach($ingresosLista as $ingreso)
                <tr>
                  <td>{{ \Carbon\Carbon::parse($ingreso->date)->format('d/m/Y') }}</td>
                  <td>{{ $ingreso->title }}</td>
                  <td data-cantidad="{{ $ingreso->quantity }}">{{ number_format($ingreso->quantity, 2) }} €</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
</div>

<!-- Modal de Ingresos -->
<div class="modal fade" id="modalIngresos" tabindex="-1" aria-labelledby="modalIngresosLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalIngresosLabel">Ingresos entre {{ $fechaInicio->format('d/m/Y') }} y {{ $fechaFin->format('d/m/Y') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-md-4">
              <label for="filtroConceptoIngresos" class="form-label">Filtrar por Concepto</label>
              <select id="filtroConceptoIngresos" class="form-select">
                <option value="">Todos</option>
                @foreach($ingresosLista->pluck('concept')->unique()->filter()->sort() as $concepto)
                  <option value="{{ $concepto }}">{{ $concepto }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label for="searchIngresos" class="form-label">Buscar</label>
              <input type="text" id="searchIngresos" class="form-control" placeholder="Buscar en la tabla...">
            </div>
            <div class="col-md-4 text-end">
              <h4>Total Ingresos: <span id="totalFiltradoIngresos">{{ number_format($ingresos, 2) }} €</span></h4>
            </div>
          </div>

          <table id="tablaIngresos" class="display table table-bordered">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Cantidad</th>
              </tr>
            </thead>
            <tbody>
              @foreach($ingresosLista as $ingreso)
                <tr>
                  <td>{{ \Carbon\Carbon::parse($ingreso->date)->format('d/m/Y') }}</td>
                  <td>{{ $ingreso->title }}</td>
                  <td data-cantidad="{{ $ingreso->quantity }}">{{ number_format($ingreso->quantity, 2) }} €</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
</div>

<!-- Modal de Gastos -->
<div class="modal fade" id="modalGastos" tabindex="-1" aria-labelledby="modalGastosLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalGastosLabel">Gastos entre {{ $fechaInicio->format('d/m/Y') }} y {{ $fechaFin->format('d/m/Y') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-md-4">
              <label for="filtroCategoriaGastos" class="form-label">Filtrar por Categoría</label>
              <select id="filtroCategoriaGastos" class="form-select">
                <option value="">Todas</option>
                @foreach($categoriasGastos->pluck('nombre')->unique()->filter()->sort() as $categoria)
                  <option value="{{ $categoria }}">{{ $categoria }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label for="searchGastos" class="form-label">Buscar</label>
              <input type="text" id="searchGastos" class="form-control" placeholder="Buscar en la tabla...">
            </div>
            <div class="col-md-4 text-end">
              <h4>Total Gastos: <span id="totalFiltradoGastos">{{ number_format($gastos, 2) }} €</span></h4>
            </div>
          </div>

          <table id="tablaGastos" class="display table table-bordered">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Categoría</th>
                <th>Concepto</th>
                <th>Cantidad</th>
              </tr>
            </thead>
            <tbody>
              @foreach($gastosLista as $gasto)
                <tr>
                  <td>{{ \Carbon\Carbon::parse($gasto->date)->format('d/m/Y') }}</td>
                  <td>{{ $gasto->categoria->nombre }}</td>
                  <td>{{ $gasto->title }}</td>
                  <td data-cantidad="{{ $gasto->quantity }}">{{ number_format($gasto->quantity, 2) }} €</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>





<style>
    #legendNacionalidad {
        font-size: 14px;
        line-height: 1.5;
    }

    #legendNacionalidad div {
        margin-bottom: 5px;
    }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- Incluir Flatpickr y la localización en español -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script><script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
{{-- <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script> --}}

<script>

    window.addEventListener('load', function () {
        const tableLibres = $('#tablaLibresHoy').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            }
        });

        const tableReservas = $('#tablaReservasTotales').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            order: [[2, 'asc']],
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100]
        });

        $('#searchTable').on('keyup', function () {
            tableReservas.search(this.value).draw();
        });

        $('#filtroApartamento').on('change', function () {
            tableReservas.column(1).search(this.value).draw();
        });

        $('#filtroOrigen').on('change', function () {
            tableReservas.column(6).search(this.value).draw();
        });
        $('#searchTable').on('keyup', function () {
            tableReservas.search(this.value).draw();
            actualizarTotalPrecioFiltrado();
        });

        $('#filtroApartamento').on('change', function () {
            tableReservas.column(1).search(this.value).draw();
            actualizarTotalPrecioFiltrado();
        });

        $('#filtroOrigen').on('change', function () {
            tableReservas.column(6).search(this.value).draw();
            actualizarTotalPrecioFiltrado();
        });
        function actualizarTotalPrecioFiltrado() {
            let total = 0;

            // Itera por las filas visibles de la tabla
            tableReservas.rows({ search: 'applied' }).every(function () {
                let data = this.data();
                let precioStr = data[4]; // Columna 4 = Precio
                let precio = parseFloat(precioStr.replace(',', '.').replace(/[^\d.-]/g, ''));

                if (!isNaN(precio)) {
                    total += precio;
                }
            });

            // Mostrar el total con 2 decimales y € al final
            $('#totalPrecioFiltrado').text('Total: ' + total.toFixed(2) + ' €');
        }

        actualizarTotalPrecioFiltrado();
    });

</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableFacturacion = $('#tablaFacturacion').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            order: [[2, 'asc']],
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            drawCallback: updateTotalFiltradoFacturacion
        });

        function updateTotalFiltradoFacturacion() {
            let total = 0;

            // Usar API para obtener todas las filas filtradas, no solo las visibles
            tableFacturacion.rows({ search: 'applied' }).every(function () {
                const row = this.node();
                const precio = parseFloat($(row).find('td[data-precio]').data('precio'));
                if (!isNaN(precio)) total += precio;
            });

            // Mostrar total
            $('#totalFiltradoFacturacion').text(total.toLocaleString('es-ES', {
                style: 'currency',
                currency: 'EUR'
            }));
        }

        $('#filtroApartamentoFacturacion').on('change', function () {
            tableFacturacion.column(1).search(this.value).draw();
        });

        $('#filtroOrigenFacturacion').on('change', function () {
            tableFacturacion.column(6).search(this.value).draw();
        });

        $('#searchFacturacion').on('keyup', function () {
            tableFacturacion.search(this.value).draw();
        });

        // Mostrar total al cargar por primera vez
        updateTotalFiltradoFacturacion();
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
      const tablaCobrado = $('#tablaCobrado').DataTable({
        language: {
          url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        drawCallback: actualizarTotalCobrado
      });

      function actualizarTotalCobrado() {
        let total = 0;
        tablaCobrado.rows({ search: 'applied' }).every(function () {
          const row = this.node();
          const cantidad = parseFloat($(row).find('td[data-cantidad]').data('cantidad'));
          if (!isNaN(cantidad)) total += cantidad;
        });

        $('#totalFiltradoCobrado').text(total.toLocaleString('es-ES', {
          style: 'currency',
          currency: 'EUR'
        }));
      }

      $('#filtroConceptoCobrado').on('change', function () {
        tablaCobrado.column(1).search(this.value).draw();
      });

      $('#searchCobrado').on('keyup', function () {
        tablaCobrado.search(this.value).draw();
      });

      actualizarTotalCobrado();
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
      const tablaIngresos = $('#tablaIngresos').DataTable({
        language: {
          url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        drawCallback: actualizarTotalIngresos
      });

      function actualizarTotalIngresos() {
        let total = 0;
        tablaIngresos.rows({ search: 'applied' }).every(function () {
          const row = this.node();
          const cantidad = parseFloat($(row).find('td[data-cantidad]').data('cantidad'));
          if (!isNaN(cantidad)) total += cantidad;
        });

        $('#totalFiltradoIngresos').text(total.toLocaleString('es-ES', {
          style: 'currency',
          currency: 'EUR'
        }));
      }

      $('#filtroConceptoIngresos').on('change', function () {
        tablaIngresos.column(1).search(this.value).draw();
      });

      $('#searchIngresos').on('keyup', function () {
        tablaIngresos.search(this.value).draw();
      });

      actualizarTotalIngresos();
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
      const tablaGastos = $('#tablaGastos').DataTable({
        language: {
          url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        drawCallback: actualizarTotalGastos
      });

      function actualizarTotalGastos() {
        let total = 0;
        tablaGastos.rows({ search: 'applied' }).every(function () {
          const row = this.node();
          const cantidad = parseFloat($(row).find('td[data-cantidad]').data('cantidad'));
          if (!isNaN(cantidad)) total += cantidad;
        });

        $('#totalFiltradoGastos').text(total.toLocaleString('es-ES', {
          style: 'currency',
          currency: 'EUR'
        }));
      }

      $('#filtroCategoriaGastos').on('change', function () {
        tablaGastos.column(1).search(this.value).draw();
      });

      $('#searchGastos').on('keyup', function () {
        tablaGastos.search(this.value).draw();
      });

      actualizarTotalGastos();
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        flatpickr('.flatpickr', {
            dateFormat: "Y-m-d",
        });

    });
</script>
<script>
    document.getElementById("verReservasBtn").addEventListener("click", function () {
        // Obtener las fechas de entrada y salida desde los inputs
        let fechaEntrada = document.getElementById("fecha_inicio").value;
        let fechaSalida = document.getElementById("fecha_fin").value;

        // Si no hay fechas, dejar los parámetros vacíos
        let url = "/reservas?order_by=fecha_entrada&direction=asc&perPage=&search=";

        if (fechaEntrada) {
            url += `&fecha_entrada=${fechaEntrada}`;
        }
        if (fechaSalida) {
            url += `&fecha_salida=${fechaSalida}`;
        }

        // Abrir en una nueva pestaña
        window.open(url, "_blank");
    });
</script>
<script>
    var ingresos = @json($ingresos);
    var gastos = @json($gastos);

    var options = {
        series: [ingresos, gastos],
        chart: {
            width: 380,
            type: 'pie',
        },
        labels: ['Ingresos', 'Gastos'],
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    var chart = new ApexCharts(document.querySelector("#chart"), options);
    chart.render();
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Datos dinámicos desde el controlador
        var labels = @json($labels); // Nacionalidades
        var data = @json($data); // Porcentajes

        // Asegurarse de que los datos estén bien sincronizados
        console.log("Labels:", labels);
        console.log("Data:", data);

        // Función para generar colores aleatorios
        function generateRandomColors(count) {
            let colors = [];
            for (let i = 0; i < count; i++) {
                colors.push(`#${Math.floor(Math.random() * 16777215).toString(16)}`); // Generar color aleatorio
            }
            return colors;
        }

        // Generar colores en función del número de labels
        // var colors = generateRandomColors(labels.length);
        var colors = [];
        for (let index = 0; index < labels.length; index++) {
            colorVariable = getColorByIndex(index)
            colors.push(colorVariable)
        }

        function getColorByIndex(index, opacity = 1) {
            const r = (index * 137 + 83) % 256; // Números primos para rotación
            const g = (index * 197 + 67) % 256; // Números primos para rotación
            const b = (index * 229 + 47) % 256; // Números primos para rotación
            return `rgba(${r}, ${g}, ${b}, ${opacity})`; // Corrige la sintaxis
        }

        var options = {
            series: [{
                name: 'Porcentaje de Reservas',
                data: data // Datos dinámicos
            }],
            chart: {
                height: 400,
                type: 'bar'
            },
            colors: colors, // Aplicar colores generados dinámicamente
            plotOptions: {
                bar: {
                    columnWidth: '70%', // Ajustar el ancho de las barras
                    distributed: true // Colores únicos para cada barra
                }
            },
            dataLabels: {
                enabled: true, // Mostrar etiquetas
                formatter: function (val) {
                    return val + "%"; // Mostrar como porcentaje
                },
                offsetY: -20, // Ajustar posición
                style: {
                    fontSize: '10px', // Reducir tamaño del texto
                    colors: ["#000"] // Color del texto
                }
            },
            xaxis: {
                categories: labels, // Nacionalidades dinámicas
                labels: {
                    style: {
                        colors: colors, // Aplicar colores a las etiquetas
                        fontSize: '12px' // Ajustar tamaño del texto
                    }
                },
                title: {
                    text: 'Nacionalidades',
                    style: {
                        fontSize: '14px',
                        fontWeight: 'bold'
                    }
                }
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return val + "%"; // Formato del eje Y
                    }
                }
            },
            title: {
                text: 'Porcentaje de Reservas por Nacionalidad',
                align: 'center',
                style: {
                    fontSize: '24px',
                    fontWeight: 'bold',
                    color: '#444',
                    fontFamily: "Nunito"
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#chartNacionalidad"), options);
        chart.render();
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var options = {
            series: @json($totalesEdades), // Porcentajes dinámicos
            chart: {
                type: 'donut',
                height: 350
            },
            labels: @json($rangoEdades), // Rangos dinámicos
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 300
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }],
            title: {
                text: 'Distribución por Rango de Edad',
                align: 'center'
            },
            legend: {
                position: 'right',
                labels: {
                    useSeriesColors: true
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val.toFixed(2) + '%';
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#chartEdad"), options);
        chart.render();
    });

    document.addEventListener('DOMContentLoaded', function () {
        var options = {
            series: @json($ocupantesData), // Porcentajes dinámicos
            chart: {
                height: 390,
                type: 'radialBar',
            },
            plotOptions: {
                radialBar: {
                    offsetY: 0,
                    startAngle: 0,
                    endAngle: 270,
                    hollow: {
                        size: '30%',
                    },
                    dataLabels: {
                        name: {
                            show: true,
                            fontSize: '16px',
                            color: '#000',
                            offsetY: -10
                        },
                        value: {
                            show: true,
                            fontSize: '14px',
                            color: '#333',
                            offsetY: 5
                        }
                    }
                }
            },
            colors: ['#1ab7ea', '#0084ff', '#39539E', '#0077B5', '#F5A623', '#E74C3C'], // Colores personalizados
            labels: @json($ocupantesLabels), // Etiquetas dinámicas
            legend: {
                show: true,
                floating: true, // Leyenda flotante como en la captura
                fontSize: '16px',
                position: 'left',
                offsetX: 10,
                offsetY: 10,
                labels: {
                    useSeriesColors: true // Colores que coincidan con el gráfico
                },
                markers: {
                    size: 8 // Tamaño de los puntos en la leyenda
                },
                formatter: function(seriesName, opts) {
                    return seriesName + ": " + opts.w.globals.series[opts.seriesIndex] + "%";
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 300
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };

        var chart = new ApexCharts(document.querySelector("#chartOcupantes"), options);
        chart.render();
    });
    //
    document.addEventListener('DOMContentLoaded', function () {
        var options = {
            series: @json($sexoData), // Porcentajes dinámicos
            chart: {
                type: 'donut',
                height: 350
            },
            labels: @json($sexoLabels), // Etiquetas dinámicas
            plotOptions: {
                pie: {
                    startAngle: -90,
                    endAngle: 90,
                    offsetY: 10
                }
            },
            grid: {
                padding: {
                    bottom: -100
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 300
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }],
            legend: {
                show: true,
                position: 'right',
                labels: {
                    useSeriesColors: true
                },
                markers: {
                    size: 8
                },
                formatter: function(seriesName, opts) {
                    return seriesName + ": " + opts.w.globals.series[opts.seriesIndex] + "%";
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#chartSexo"), options);
        chart.render();
    });
    // Prescriptores
    document.addEventListener('DOMContentLoaded', function () {
        var options = {
            series: [{
                data: @json($prescriptoresData)
            }],
            chart: {
                type: 'bar',
                height: 350
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: true
                }
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                categories: @json($prescriptoresLabels),
            }
        };

        var chart = new ApexCharts(document.querySelector("#chartPrescriptores"), options);
        chart.render();
    });
    // Reservas por Apartamento
    document.addEventListener('DOMContentLoaded', function () {
        var options = {
            series: [{
                data: @json($apartamentosData)
            }],
            chart: {
                type: 'bar',
                height: 350
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: true
                }
            },
            dataLabels: {
                enabled: true, // Habilitamos las etiquetas de datos
                formatter: function (val) {
                    return val + '%'; // Mostramos el valor con el símbolo de porcentaje
                },
                style: {
                    fontSize: '12px',
                    colors: ['#304758']
                },
                offsetX: 10 // Ajustamos la posición horizontal para mayor claridad
            },
            xaxis: {
                categories: @json($apartamentosLabels),
                labels: {
                    style: {
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return val + '%'; // Opcional: Mostrar porcentaje en los ejes
                    }
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#chartApartamentos"), options);
        chart.render();
    });

    document.addEventListener('DOMContentLoaded', function () {
        var options = {
            series: @json($categoriasData), // Porcentajes de cada categoría
            chart: {
                type: 'donut',
                height: 350
            },
            labels: @json($categoriasLabels), // Etiquetas de las categorías
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };

        var chart = new ApexCharts(document.querySelector("#chartGastos"), options);
        chart.render();
    });
</script>
@endsection
