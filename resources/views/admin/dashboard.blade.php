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
    .clickable-card:hover {
        background-color: #f0f8ff; /* o el color que prefieras */
        transition: background-color 0.3s;
    }

    .disabled-card {
        opacity: 0.6;
        pointer-events: none;
        background-color: #f8f9fa; /* color gris claro por ejemplo */
        cursor: not-allowed;
    }

/* Estilos globales para todos los modales */
.modal-fullscreen .modal-content {
    border-radius: 0;
    border: none;
}

.modal-fullscreen .modal-header {
    border-bottom: 2px solid #dee2e6;
    padding: 1rem 1.5rem;
}

.modal-fullscreen .modal-body {
    padding: 1.5rem;
}

/* Estilos para todas las tablas de modales */
.modal .table {
    width: 100% !important;
    table-layout: fixed;
}

.modal .table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    background-color: #343a40 !important;
    border-bottom: 2px solid #495057;
    padding: 12px 8px;
    vertical-align: middle;
}

.modal .table td {
    padding: 12px 8px;
    vertical-align: middle;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.modal .table tbody tr:hover {
    background-color: #f8f9fa !important;
    transform: scale(1.01);
    transition: all 0.2s ease;
}

/* Colores específicos para estados */
.badge.bg-success {
    background-color: #28a745 !important;
    color: white !important;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

.badge.bg-danger {
    background-color: #dc3545 !important;
    color: white !important;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
    color: white !important;
}

/* Estilos para filtros */
.modal .form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.modal .form-select,
.modal .form-control {
    border-radius: 0.375rem;
    border: 1px solid #ced4da;
}

.modal .form-select:focus,
.modal .form-control:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Responsive para pantallas pequeñas */
@media (max-width: 768px) {
    .modal .table {
        font-size: 0.8rem;
    }
    
    .modal .table th,
    .modal .table td {
        padding: 8px 4px;
    }
    
    .modal .modal-body {
        padding: 1rem;
    }
}
</style>
<div class="container-fluid">
    <!-- jQuery y DataTables (cargados ANTES de usarlos) -->
    {{-- <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script> --}}

    <div class="d-flex align-items-end">
        <div class="col-md-6">
            <h2 class="mb-0">DASHBOARD</h2>
        </div>
        <div class="col-md-6 text-end">
            <form action="{{route('dashboard.index')}}" class="row align-items-end" method="GET" >
                <h6 class="text-black-50" style="font-size: 12px"><i class="fa-solid fa-filter me-2"></i>Filtrado por fechas</h6>
                <div class="col-md-12 d-flex align-items-end justify-content-end">
                    <input type="text" id="fecha_inicio" name="fecha_inicio" class="form-control flatpickr"
                        value="{{ request('fecha_inicio', '') }}" placeholder="Fecha Inicio" style="font-size: 10px; width: fit-content; margin-right: 11px;">
                    <input type="text" id="fecha_fin" name="fecha_fin" class="form-control flatpickr"
                        value="{{ request('fecha_fin', '') }}" placeholder="Fecha Fin" style="font-size: 10px; width: fit-content; margin-right: 11px;">
                    <button type="submit" class="btn btn-guardar text-uppercase" style="font-size: 10px; width: fit-content; margin-right: 11px; max-width: 200px;">Buscar</button>
                    <button id="verReservasBtn" class="btn bg-color-segundo text-uppercase" style="font-size: 10px; width: fit-content; max-width: 280px;">Ver Reservas</button>
                </div>
            </form>
        </div>
    </div>

    <br>
    <div class="row" style="padding: 1rem;">
        <div class="col-12 mb-5">
            <div class="row justify-content-between align-items-stretch ">
                <h5 class="text-left mt-1">Información de Gestión</h5>
                <hr>
                <div class="col-xl-3 col-md-6">
                    <div class="row p-3 card m-3 flex-row pe-auto align-items-center clickable-card" data-bs-toggle="modal" data-bs-target="#modalLibresHoy" style="cursor: pointer">
                        <div class="col-8">
                            <h5 class="text-start
                            mb-0 fs-6">Apts. Libres Hoy</h5>
                        </div>
                        <div class="col-4">
                            <h2 class="text-end mb-0 fs-4"><strong>{{$apartamentosLibresHoy->count()}}</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="row p-3 card m-3 flex-row pe-auto align-items-center clickable-card" data-bs-toggle="modal" data-bs-target="#modalReservasTotales" style="cursor: pointer">
                        <div class="col-8">
                            <h5 class="text-start mb-0 fs-6">Total de Reservas</h5>
                        </div>
                        <div class="col-4">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ $countReservas }}</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="row p-3 card m-3 flex-row align-items-center disabled-card">
                        <div class="col-7">
                            <h5 class="text-start mb-0 fs-6">Ocupacción %</h5>
                        </div>
                        <div class="col-5">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ $porcentajeOcupacion }} %</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="row p-3 card m-3 flex-row align-items-center disabled-card">
                        <div class="col-8">
                            <h5 class="text-start mb-0 fs-6">Ocupación</h5>
                        </div>
                        <div class="col-4">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ $nochesOcupadas }}</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="row p-3 card m-3 flex-row align-items-center disabled-card">
                        <div class="col-8">
                            <h5 class="text-start mb-0 fs-6">Ocupación Disponibles</h5>
                        </div>
                        <div class="col-4">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ $totalNochesPosibles }}</strong></h2>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <div class="row justify-content-start align-items-stretch">
                <h5 class="text-left mt-1">Información de Economica</h5>
                <hr>
                <div class="col-xl-3 col-md-6">
                    <div class="row p-3 card m-3 flex-row align-items-center clickable-card" data-bs-toggle="modal" data-bs-target="#modalFacturacion" style="cursor:pointer;">
                        <div class="col-5">
                            <h5 class="text-start mb-0 fs-6">Facturación</h5>
                        </div>
                        <div class="col-7">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($sumPrecio, 2) }} €</strong></h2>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="row p-3 card m-3 flex-row align-items-center clickable-card" data-bs-toggle="modal" data-bs-target="#modalCobrado" style="cursor:pointer;">
                        <div class="col-5">
                          <h4 class="text-start mb-0 fs-6">Cobrado</h4>
                        </div>
                        <div class="col-7">
                          <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($ingresos, 2) }} €</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="row p-3 card m-3 flex-row align-items-center disabled-card">
                        <div class="col-6">
                            <h4 class="text-start mb-0 fs-6">Cash Flow</h4>
                        </div>
                        <div class="col-6">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($ingresos - $gastos, 2) }} €</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="row p-3 card m-3 flex-row align-items-center clickable-card" data-bs-toggle="modal" data-bs-target="#modalIngresos" style="cursor:pointer;">
                        <div class="col-6">
                          <h4 class="text-start mb-0 fs-6">Ingresos</h4>
                        </div>
                        <div class="col-6">
                          <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($ingresos, 2) }} €</strong></h2>
                        </div>
                      </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="row p-3 card m-3 flex-row align-items-center clickable-card" data-bs-toggle="modal" data-bs-target="#modalGastos" style="cursor:pointer;">
                        <div class="col-6">
                          <h4 class="text-start mb-0 fs-6">Gastos</h4>
                        </div>
                        <div class="col-6">
                          <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($gastos, 2) }} €</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="row p-3 card m-3 flex-row align-items-center disabled-card">
                        <div class="col-6">
                            <h4 class="text-start mb-0 fs-6">Beneficio</h4>
                        </div>
                        <div class="col-6">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($ingresosBeneficio - $gastosBeneficio, 2) }} €</strong></h2>
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
    <h5 class="text-left mt-1">Estadisticas</h5>
    <hr>
    <div class="row justify-content-between align-items-stretch ">
        <div class="col-xl-4 col-md-4 rounded-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                    <h2 class="text-center">Reservas por Nacionalidad</h2>

                            <div id="chartNacionalidad"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Balance</h2>
                    <div id="chart"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Distribución por Género</h2>
                    <div id="chartSexo"></div>
                </div>

            </div>
        </div>
        <div class="col-md-4 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Distribución por Ocupantes</h2>
                    <div id="chartOcupantes"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Distribución de Clientes por Rango de Edad</h2>
                    <div id="chartEdad"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Distribución de Prescriptores</h2>
                    <div id="chartPrescriptores"></div>
                </div>
            </div>
        </div>

        <div class="col-md-4 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Distribución de Reservas por Apartamento</h2>
                    <div id="chartApartamentos"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Distribución de Gastos por Categoría</h2>
                    <div id="chartGastos"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Reservas Activas por Mes - Comparativa {{ $anioActual }} vs {{ $anioAnterior }}</h2>
                    <div id="chartReservasPorMes"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Noches Reservadas por Mes - Comparativa {{ $anioActual }} vs {{ $anioAnterior }}</h2>
                    <div id="chartNochesPorMes"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Beneficio por Mes - Comparativa {{ $anioActual }} vs {{ $anioAnterior }}</h2>
                    <div id="chartBeneficioPorMes"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Disponibilidad Mensual de Apartamentos</h2>
                    <div id="chartDisponibilidadMensual"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SECCIÓN DE ACCIONES RÁPIDAS -->
<div class="row mt-4">
    <div class="col-12">
        <h5 class="text-left mb-3">
            <i class="fas fa-bolt me-2"></i>Acciones Rápidas
        </h5>
        <hr>
    </div>
</div>

<div class="row justify-content-between align-items-stretch mb-4">
    <!-- Tarjeta de Descuentos -->
    <div class="col-md-3">
        <div class="card h-100 border-primary clickable-card" onclick="window.location.href='{{ route('configuracion-descuentos.index') }}'">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-percentage fa-3x text-primary"></i>
                </div>
                <h5 class="card-title text-primary">Gestión de Descuentos</h5>
                <p class="card-text">Configurar y aplicar descuentos automáticos de temporada baja</p>
                <div class="mt-3">
                    <button class="btn btn-primary btn-sm me-2" onclick="event.stopPropagation(); ejecutarComandoDescuentos('analizar')">
                        <i class="fas fa-search me-1"></i>Analizar
                    </button>
                    <button class="btn btn-success btn-sm" onclick="event.stopPropagation(); ejecutarComandoDescuentos('aplicar')">
                        <i class="fas fa-play me-1"></i>Aplicar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Tarifas -->
    <div class="col-md-3">
        <div class="card h-100 border-warning clickable-card" onclick="window.location.href='{{ route('tarifas.index') }}'">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-tags fa-3x text-warning"></i>
                </div>
                <h5 class="card-title text-warning">Gestión de Tarifas</h5>
                <p class="card-text">Configurar tarifas por temporada y asignar a apartamentos</p>
                <div class="mt-3">
                    <a href="{{ route('tarifas.create') }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-plus me-1"></i>Nueva Tarifa
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Reservas -->
    <div class="col-md-3">
        <div class="card h-100 border-info clickable-card" onclick="window.location.href='{{ route('reservas.index') }}'">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-calendar-check fa-3x text-info"></i>
                </div>
                <h5 class="card-title text-info">Gestión de Reservas</h5>
                <p class="card-text">Ver y gestionar todas las reservas del sistema</p>
                <div class="mt-3">
                    <a href="{{ route('reservas.create') }}" class="btn btn-info btn-sm">
                        <i class="fas fa-plus me-1"></i>Nueva Reserva
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Apartamentos -->
    <div class="col-md-3">
        <div class="card h-100 border-success clickable-card" onclick="window.location.href='{{ route('apartamentos.admin.index') }}'">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-home fa-3x text-success"></i>
                </div>
                <h5 class="card-title text-success">Gestión de Apartamentos</h5>
                <p class="card-text">Administrar apartamentos y sus configuraciones</p>
                <div class="mt-3">
                    <a href="{{ route('apartamentos.admin.create') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus me-1"></i>Nuevo Apartamento
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Nueva fila de tarjetas de gestión -->
<div class="row justify-content-between align-items-stretch mb-4">
    <!-- Tarjeta de Incidencias -->
    <div class="col-md-3">
        <div class="card h-100 border-danger clickable-card" onclick="window.location.href='{{ route('admin.incidencias.index') }}'">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                </div>
                <h5 class="card-title text-danger">Gestión de Incidencias</h5>
                <p class="card-text">Gestionar y resolver incidencias reportadas por el personal</p>
                <div class="mt-3">
                    <span class="badge bg-danger fs-6" id="incidenciasPendientes">
                        <i class="fas fa-clock me-1"></i>Cargando...
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Limpiezas -->
    <div class="col-md-3">
        <div class="card h-100 border-info clickable-card" onclick="window.location.href='{{ route('admin.limpiezas.index') }}'">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-broom fa-3x text-info"></i>
                </div>
                <h5 class="card-title text-info">Gestión de Limpiezas</h5>
                <p class="card-text">Supervisar y gestionar las tareas de limpieza</p>
                <div class="mt-3">
                    <a href="{{ route('admin.limpiezas.index') }}" class="btn btn-info btn-sm">
                        <i class="fas fa-eye me-1"></i>Ver Limpiezas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Personal -->
    <div class="col-md-3">
        <div class="card h-100 border-warning clickable-card" onclick="window.location.href='{{ route('admin.empleados.index') }}'">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-users-cog fa-3x text-warning"></i>
                </div>
                <h5 class="card-title text-warning">Gestión de Personal</h5>
                <p class="card-text">Administrar empleados, jornadas y vacaciones</p>
                <div class="mt-3">
                    <a href="{{ route('admin.empleados.index') }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-users me-1"></i>Ver Personal
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Clientes -->
    <div class="col-md-3">
        <div class="card h-100 border-success clickable-card" onclick="window.location.href='{{ route('clientes.index') }}'">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-user-friends fa-3x text-success"></i>
                </div>
                <h5 class="card-title text-success">Gestión de Clientes</h5>
                <p class="card-text">Administrar la base de datos de clientes</p>
                <div class="mt-3">
                    <a href="{{ route('clientes.create') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus me-1"></i>Nuevo Cliente
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODALES --}}
<!-- Modal de Libres Hoy -->
<div class="modal fade" id="modalLibresHoy" tabindex="-1" aria-labelledby="modalLibresHoyLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalLibresHoyLabel">
                    <i class="fas fa-home me-2"></i>Apartamentos Libres Hoy
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive">
                    <table id="tablaLibresHoy" class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 15%;">APARTAMENTO</th>
                                <th style="width: 15%;">ESTADO</th>
                                <th style="width: 15%;">ÚLTIMA RESERVA</th>
                                <th style="width: 15%;">PRÓXIMA RESERVA</th>
                                <th style="width: 20%;">NOTAS</th>
                                <th style="width: 20%;">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($apartamentosLibresHoy as $apartamento)
                                <tr>
                                    <td>{{ $apartamento->nombre }}</td>
                                    <td>
                                        <span class="badge bg-success">Libre</span>
                                    </td>
                                    <td>{{ $apartamento->ultima_reserva ?? 'N/A' }}</td>
                                    <td>{{ $apartamento->proxima_reserva ?? 'N/A' }}</td>
                                    <td>{{ $apartamento->notas ?? 'Sin notas' }}</td>
                                    <td>
                                        <a href="{{ route('apartamentos.admin.edit', $apartamento->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Reservas Totales -->
<div class="modal fade" id="modalReservasTotales" tabindex="-1" aria-labelledby="modalReservasTotalesLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalReservasTotalesLabel">
                    <i class="fas fa-calendar-check me-2"></i>Reservas Totales
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="filtroApartamento" class="form-label fw-bold">Filtrar por Apartamento</label>
                        <select id="filtroApartamento" class="form-select">
                            <option value="">Todos los apartamentos</option>
                            @foreach($apartamentos as $apartamento)
                                <option value="{{ $apartamento->nombre }}">{{ $apartamento->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="searchReservas" class="form-label fw-bold">Buscar en la tabla:</label>
                        <input type="text" id="searchReservas" class="form-control" placeholder="Buscar...">
                    </div>
                    <div class="col-md-4">
                        <label for="filtroEstadoReservas" class="form-label fw-bold">Filtrar por Estado</label>
                        <select id="filtroEstadoReservas" class="form-select">
                            <option value="">Todos los estados</option>
                            @foreach($estados as $estado)
                                <option value="{{ $estado->nombre }}">{{ $estado->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="tablaReservasTotales" class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 12%;">CLIENTE</th>
                                <th style="width: 20%;">APARTAMENTO</th>
                                <th style="width: 10%;">ENTRADA</th>
                                <th style="width: 10%;">SALIDA</th>
                                <th style="width: 12%;">€ PRECIO</th>
                                <th style="width: 8%;">PERSONAS</th>
                                <th style="width: 12%;">ORIGEN</th>
                                <th style="width: 16%;">ESTADO</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reservas as $reserva)
                                <tr data-id="{{ $reserva->id }}" style="cursor: pointer;">
                                    <td>{{ $reserva->cliente->nombre ?? 'N/A' }}</td>
                                    <td>{{ $reserva->apartamento->nombre ?? 'N/A' }}</td>
                                    <td>{{ $reserva->fecha_entrada ? \Carbon\Carbon::parse($reserva->fecha_entrada)->format('d/m/Y') : 'N/A' }}</td>
                                    <td>{{ $reserva->fecha_salida ? \Carbon\Carbon::parse($reserva->fecha_salida)->format('d/m/Y') : 'N/A' }}</td>
                                    <td>{{ number_format($reserva->precio, 2, ',', '.') }} €</td>
                                    <td>{{ $reserva->numero_personas ?? 'N/A' }}</td>
                                    <td>{{ $reserva->origen ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $estadoClass = '';
                                            $estadoNombre = $reserva->estado->nombre ?? 'N/A';
                                            
                                            if (str_contains(strtolower($estadoNombre), 'facturado')) {
                                                $estadoClass = 'bg-success';
                                            } elseif (str_contains(strtolower($estadoNombre), 'pendiente')) {
                                                $estadoClass = 'bg-warning text-dark';
                                            } elseif (str_contains(strtolower($estadoNombre), 'cancelada')) {
                                                $estadoClass = 'bg-danger';
                                            } else {
                                                $estadoClass = 'bg-secondary';
                                            }
                                        @endphp
                                        <span class="badge {{ $estadoClass }}">
                                            {{ $estadoNombre }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#tablaReservasTotales tbody').on('click', 'tr', function () {
        var idReserva = $(this).data('id');
        if (idReserva) {
            window.open('/reservas/' + idReserva + '/show', '_blank');
        }
    });
});
</script>


<!-- Modal de Facturación -->
<div class="modal fade" id="modalFacturacion" tabindex="-1" aria-labelledby="modalFacturacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalFacturacionLabel">
                    <i class="fas fa-chart-line me-2"></i>Detalle de Facturación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Filtros -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label for="filtroApartamentoFacturacion" class="form-label fw-bold">Apartamento:</label>
                        <select id="filtroApartamentoFacturacion" class="form-select">
                            <option value="">Todos los apartamentos</option>
                            @foreach($apartamentos as $apartamento)
                                <option value="{{ $apartamento->nombre }}">{{ $apartamento->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filtroOrigenFacturacion" class="form-label fw-bold">Origen:</label>
                        <select id="filtroOrigenFacturacion" class="form-select">
                            <option value="">Todos los orígenes</option>
                            @foreach($origenes as $origen)
                                <option value="{{ $origen }}">{{ $origen }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filtroEstadoFacturacion" class="form-label fw-bold">Estado:</label>
                        <select id="filtroEstadoFacturacion" class="form-select">
                            <option value="">Todos los estados</option>
                            @foreach($estados as $estado)
                                <option value="{{ $estado->nombre }}">{{ $estado->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="searchFacturacion" class="form-label fw-bold">Buscar en la tabla:</label>
                        <input type="text" id="searchFacturacion" class="form-control" placeholder="Buscar...">
                    </div>
                </div>

                <!-- Total -->
                <div class="alert alert-info mb-4">
                    <strong>Total Facturación Filtrada: <span id="totalFacturacion">{{ number_format($totalFacturacion, 2, ',', '.') }} €</span></strong>
                </div>

                <!-- Tabla -->
                <div class="table-responsive">
                    <table id="tablaFacturacion" class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 12%;">CLIENTE</th>
                                <th style="width: 20%;">APARTAMENTO</th>
                                <th style="width: 10%;">ENTRADA</th>
                                <th style="width: 10%;">SALIDA</th>
                                <th style="width: 12%;">€ PRECIO</th>
                                <th style="width: 8%;">PERSONAS</th>
                                <th style="width: 12%;">ORIGEN</th>
                                <th style="width: 16%;">ESTADO</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reservas as $reserva)
                                <tr data-id="{{ $reserva->id }}" style="cursor: pointer;">
                                    <td>{{ $reserva->cliente->nombre ?? 'N/A' }}</td>
                                    <td>{{ $reserva->apartamento->nombre ?? 'N/A' }}</td>
                                    <td>{{ $reserva->fecha_entrada ? \Carbon\Carbon::parse($reserva->fecha_entrada)->format('d/m/Y') : 'N/A' }}</td>
                                    <td>{{ $reserva->fecha_salida ? \Carbon\Carbon::parse($reserva->fecha_salida)->format('d/m/Y') : 'N/A' }}</td>
                                    <td>{{ number_format($reserva->precio, 2, ',', '.') }} €</td>
                                    <td>{{ $reserva->numero_personas ?? 'N/A' }}</td>
                                    <td>{{ $reserva->origen ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $estadoClass = '';
                                            $estadoNombre = $reserva->estado->nombre ?? 'N/A';
                                            
                                            if (str_contains(strtolower($estadoNombre), 'facturado')) {
                                                $estadoClass = 'bg-success';
                                            } elseif (str_contains(strtolower($estadoNombre), 'pendiente')) {
                                                $estadoClass = 'bg-warning text-dark';
                                            } elseif (str_contains(strtolower($estadoNombre), 'cancelada')) {
                                                $estadoClass = 'bg-danger';
                                            } else {
                                                $estadoClass = 'bg-secondary';
                                            }
                                        @endphp
                                        <span class="badge {{ $estadoClass }}">
                                            {{ $estadoNombre }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos para la tabla */
#tablaFacturacion {
    width: 100% !important;
    table-layout: fixed;
}

#tablaFacturacion th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    background-color: #343a40 !important;
    border-bottom: 2px solid #495057;
    padding: 12px 8px;
    vertical-align: middle;
}

#tablaFacturacion td {
    padding: 12px 8px;
    vertical-align: middle;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

#tablaFacturacion tbody tr:hover {
    background-color: #f8f9fa !important;
    transform: scale(1.01);
    transition: all 0.2s ease;
}

/* Colores específicos para estados */
.badge.bg-success {
    background-color: #28a745 !important;
    color: white !important;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

.badge.bg-danger {
    background-color: #dc3545 !important;
    color: white !important;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
    color: white !important;
}

/* Responsive para pantallas pequeñas */
@media (max-width: 768px) {
    #tablaFacturacion {
        font-size: 0.8rem;
    }
    
    #tablaFacturacion th,
    #tablaFacturacion td {
        padding: 8px 4px;
    }
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Verificar si la tabla ya está inicializada
        if ($.fn.DataTable.isDataTable('#tablaFacturacion')) {
            $('#tablaFacturacion').DataTable().destroy();
        }

        const tableFacturacion = $('#tablaFacturacion').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            pageLength: 25,
            order: [[0, 'asc']],
            responsive: true,
            autoWidth: true,
            columnDefs: [
                {
                    targets: '_all',
                    className: 'text-center'
                }
            ],
            initComplete: function () {
                // Recalcular anchos después de la inicialización
                this.api().columns.adjust();
            }
        });

        // Recalcular anchos cuando cambie el tamaño de la ventana
        $(window).on('resize', function () {
            tableFacturacion.columns.adjust();
        });

        // Recalcular anchos cuando se muestre el modal
        $('#modalFacturacion').on('shown.bs.modal', function () {
            tableFacturacion.columns.adjust();
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

        $('#filtroEstadoFacturacion').on('change', function () {
            tableFacturacion.column(7).search(this.value).draw();
        });

        $('#searchFacturacion').on('keyup', function () {
            tableFacturacion.search(this.value).draw();
        });

        $('#tablaFacturacion tbody').on('click', 'tr', function () {
            var idReserva = $(this).data('id');
            if (idReserva) {
                window.open('/reservas/' + idReserva + '/show', '_blank');
            }
        });
    });
</script>

<!-- Modal de Cobrado -->
<div class="modal fade" id="modalCobrado" tabindex="-1" aria-labelledby="modalCobradoLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalCobradoLabel">
                    <i class="fas fa-money-bill-wave me-2"></i>Cobros entre {{ $fechaInicio->format('d/m/Y') }} y {{ $fechaFin->format('d/m/Y') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="filtroConceptoCobrado" class="form-label fw-bold">Filtrar por Concepto</label>
                        <select id="filtroConceptoCobrado" class="form-select">
                            <option value="">Todos</option>
                            @foreach($ingresosLista->pluck('concept')->unique()->filter()->sort() as $concepto)
                                <option value="{{ $concepto }}">{{ $concepto }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="searchCobrado" class="form-label fw-bold">Buscar en la tabla:</label>
                        <input type="text" id="searchCobrado" class="form-control" placeholder="Buscar...">
                    </div>
                    <div class="col-md-4">
                        <div class="alert alert-info mb-0">
                            <strong>Total Cobrado: <span id="totalFiltradoCobrado">{{ number_format($ingresos, 2) }} €</span></strong>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="tablaCobrado" class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 20%;">FECHA</th>
                                <th style="width: 50%;">CONCEPTO</th>
                                <th style="width: 30%;">CANTIDAD</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ingresosLista as $ingreso)
                                <tr data-id="{{ $ingreso->id }}" style="cursor: pointer">
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#tablaCobrado tbody').on('click', 'tr', function () {
        var idCobrado = $(this).data('id');
        if (idCobrado) {
            window.open('/ingresos/' + idCobrado + '/edit', '_blank');
        }
    });
});
</script>

<!-- Modal de Ingresos -->
<div class="modal fade" id="modalIngresos" tabindex="-1" aria-labelledby="modalIngresosLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalIngresosLabel">
                    <i class="fas fa-chart-line me-2"></i>Ingresos entre {{ $fechaInicio->format('d/m/Y') }} y {{ $fechaFin->format('d/m/Y') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="filtroConceptoIngresos" class="form-label fw-bold">Filtrar por Concepto</label>
                        <select id="filtroConceptoIngresos" class="form-select">
                            <option value="">Todos</option>
                            @foreach($ingresosLista->pluck('concept')->unique()->filter()->sort() as $concepto)
                                <option value="{{ $concepto }}">{{ $concepto }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="searchIngresos" class="form-label fw-bold">Buscar en la tabla:</label>
                        <input type="text" id="searchIngresos" class="form-control" placeholder="Buscar...">
                    </div>
                    <div class="col-md-4">
                        <div class="alert alert-info mb-0">
                            <strong>Total Ingresos: <span id="totalFiltradoIngresos">{{ number_format($ingresos, 2) }} €</span></strong>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="tablaIngresos" class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 20%;">FECHA</th>
                                <th style="width: 50%;">CONCEPTO</th>
                                <th style="width: 30%;">CANTIDAD</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ingresosLista as $ingreso)
                                <tr data-id="{{ $ingreso->id }}" style="cursor: pointer">
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#tablaIngresos tbody').on('click', 'tr', function () {
        var idIngreso = $(this).data('id');
        if (idIngreso) {
            window.open('/ingresos/' + idIngreso + '/edit', '_blank');
        }
    });
});
</script>

<!-- Modal de Gastos -->
<div class="modal fade" id="modalGastos" tabindex="-1" aria-labelledby="modalGastosLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalGastosLabel">
                    <i class="fas fa-receipt me-2"></i>Gastos entre {{ $fechaInicio->format('d/m/Y') }} y {{ $fechaFin->format('d/m/Y') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="filtroCategoriaGastos" class="form-label fw-bold">Filtrar por Categoría</label>
                        <select id="filtroCategoriaGastos" class="form-select">
                            <option value="">Todas las categorías</option>
                            @foreach($categoriasGastos as $categoria)
                                <option value="{{ $categoria->nombre }}">{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="searchGastos" class="form-label fw-bold">Buscar en la tabla:</label>
                        <input type="text" id="searchGastos" class="form-control" placeholder="Buscar...">
                    </div>
                    <div class="col-md-4">
                        <div class="alert alert-danger mb-0">
                            <strong>Total Gastos: <span id="totalFiltradoGastos">{{ number_format($gastos, 2) }} €</span></strong>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="tablaGastos" class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 15%;">FECHA</th>
                                <th style="width: 25%;">CATEGORÍA</th>
                                <th style="width: 35%;">CONCEPTO</th>
                                <th style="width: 25%;">CANTIDAD</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gastosLista as $gasto)
                                <tr data-id="{{ $gasto->id }}" style="cursor: pointer">
                                    <td>{{ \Carbon\Carbon::parse($gasto->date)->format('d/m/Y') }}</td>
                                    <td>{{ $gasto->categoria->nombre ?? 'N/A' }}</td>
                                    <td>{{ $gasto->title }}</td>
                                    <td data-cantidad="{{ abs($gasto->quantity) }}">{{ number_format(abs($gasto->quantity), 2) }} €</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    $('#tablaGastos tbody').on('click', 'tr', function () {
        var idGasto = $(this).data('id');
        if (idGasto) {
            window.open('/gastos/' + idGasto + '/edit', '_blank');
        }
    });
});
</script>




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
        // Verificar si la tabla ya está inicializada
        if ($.fn.DataTable.isDataTable('#tablaFacturacion')) {
            $('#tablaFacturacion').DataTable().destroy();
        }

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

        $('#filtroEstadoFacturacion').on('change', function () {
            tableFacturacion.column(7).search(this.value).draw();
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
            locale: 'es' // 👈 AÑADE ESTO para español

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



        var options = {
            series: [{
                data: data // Datos dinámicos
            }],
            chart: {
                type: 'bar',
                height: 500
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: true,
                    barHeight: '60%'
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
                categories: labels, // Nacionalidades dinámicas
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
                height: 500
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: true,
                    barHeight: '60%'
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

    // Gráfico de Reservas por Mes
    document.addEventListener('DOMContentLoaded', function () {
        var options = {
            series: [{
                name: '{{ $anioActual }}',
                data: @json($reservasAnioActual)
            }, {
                name: '{{ $anioAnterior }}',
                data: @json($reservasAnioAnterior)
            }],
            chart: {
                type: 'line',
                height: 350,
                zoom: {
                    enabled: false
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val;
                },
                style: {
                    fontSize: '12px',
                    colors: ['#304758']
                }
            },
            stroke: {
                curve: 'straight',
                width: 3
            },
            colors: ['#008FFB', '#FF4560'],
            xaxis: {
                categories: @json($meses),
                labels: {
                    style: {
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                title: {
                    text: 'Número de Reservas Activas'
                },
                labels: {
                    formatter: function (val) {
                        return Math.round(val);
                    }
                }
            },
            grid: {
                row: {
                    colors: ['#f3f3f3', 'transparent'],
                    opacity: 0.5
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'center'
            },
            title: {
                text: 'Comparativa de Reservas Activas por Mes',
                align: 'center',
                style: {
                    fontSize: '16px',
                    fontWeight: 'bold'
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#chartReservasPorMes"), options);
        chart.render();
    });

    // Gráfico de Beneficio por Mes
    document.addEventListener('DOMContentLoaded', function () {
        var options = {
            series: [{
                name: '{{ $anioActual }}',
                data: @json($beneficiosAnioActual)
            }, {
                name: '{{ $anioAnterior }}',
                data: @json($beneficiosAnioAnterior)
            }],
            chart: {
                type: 'line',
                height: 350,
                zoom: {
                    enabled: false
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val.toLocaleString('es-ES', {
                        style: 'currency',
                        currency: 'EUR',
                        minimumFractionDigits: 0
                    });
                },
                style: {
                    fontSize: '11px',
                    colors: ['#304758']
                }
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            colors: ['#00E396', '#775DD0'],
            xaxis: {
                categories: @json($meses),
                labels: {
                    style: {
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                title: {
                    text: 'Beneficio (€)'
                },
                labels: {
                    formatter: function (val) {
                        return val.toLocaleString('es-ES', {
                            style: 'currency',
                            currency: 'EUR',
                            minimumFractionDigits: 0
                        });
                    }
                }
            },
            grid: {
                row: {
                    colors: ['#f3f3f3', 'transparent'],
                    opacity: 0.5
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'center'
            },
            title: {
                text: 'Comparativa del Beneficio por Mes',
                align: 'center',
                style: {
                    fontSize: '16px',
                    fontWeight: 'bold'
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#chartBeneficioPorMes"), options);
        chart.render();
    });

    // Gráfico de Noches Reservadas por Mes
    document.addEventListener('DOMContentLoaded', function () {
        var options = {
            series: [{
                name: '{{ $anioActual }}',
                data: @json($nochesReservadasAnioActual)
            }, {
                name: '{{ $anioAnterior }}',
                data: @json($nochesReservadasAnioAnterior)
            }],
            chart: {
                type: 'line',
                height: 350,
                zoom: {
                    enabled: false
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return Math.round(val);
                },
                style: {
                    fontSize: '11px',
                    colors: ['#304758']
                }
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            colors: ['#FF6B6B', '#4ECDC4'],
            xaxis: {
                categories: @json($meses),
                labels: {
                    style: {
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                title: {
                    text: 'Número de Noches Reservadas'
                },
                labels: {
                    formatter: function (val) {
                        return Math.round(val);
                    }
                }
            },
            grid: {
                row: {
                    colors: ['#f3f3f3', 'transparent'],
                    opacity: 0.5
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'center'
            },
            title: {
                text: 'Comparativa de Noches Reservadas por Mes',
                align: 'center',
                style: {
                    fontSize: '16px',
                    fontWeight: 'bold'
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#chartNochesPorMes"), options);
        chart.render();
    });

    // Gráfico de Disponibilidad Mensual
    document.addEventListener('DOMContentLoaded', function () {
        var disponibilidadData = @json($disponibilidadMensual);
        
        var options = {
            series: [{
                name: 'Disponibilidad (%)',
                data: disponibilidadData.map(item => item.porcentaje_disponibilidad)
            }, {
                name: 'Ocupación (%)',
                data: disponibilidadData.map(item => item.porcentaje_ocupacion)
            }],
            chart: {
                type: 'bar',
                height: 350,
                stacked: true,
                toolbar: {
                    show: true
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return Math.round(val) + '%';
                },
                style: {
                    fontSize: '11px',
                    colors: ['#fff']
                }
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            colors: ['#28a745', '#dc3545'],
            xaxis: {
                categories: disponibilidadData.map(item => item.mes),
                labels: {
                    style: {
                        fontSize: '12px'
                    },
                    rotate: -45,
                    rotateAlways: false
                }
            },
            yaxis: {
                title: {
                    text: 'Porcentaje (%)'
                },
                labels: {
                    formatter: function (val) {
                        return Math.round(val) + '%';
                    }
                },
                max: 100
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                y: [{
                    formatter: function (val) {
                        return Math.round(val) + '%';
                    }
                }, {
                    formatter: function (val) {
                        return Math.round(val) + '%';
                    }
                }]
            },
            legend: {
                position: 'top',
                horizontalAlign: 'center'
            },
            title: {
                text: 'Disponibilidad vs Ocupación Mensual',
                align: 'center',
                style: {
                    fontSize: '16px',
                    fontWeight: 'bold'
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#chartDisponibilidadMensual"), options);
        chart.render();
    });

    // Cargar incidencias pendientes
    document.addEventListener('DOMContentLoaded', function () {
        cargarIncidenciasPendientes();
    });

    function cargarIncidenciasPendientes() {
        fetch('{{ route("admin.incidencias.pendientes") }}')
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('incidenciasPendientes');
                if (data.length > 0) {
                    badge.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i>${data.length} Pendientes`;
                    badge.className = 'badge bg-danger fs-6';
                } else {
                    badge.innerHTML = `<i class="fas fa-check me-1"></i>0 Pendientes`;
                    badge.className = 'badge bg-success fs-6';
                }
            })
            .catch(error => {
                console.error('Error al cargar incidencias:', error);
                const badge = document.getElementById('incidenciasPendientes');
                badge.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i>Error`;
                badge.className = 'badge bg-secondary fs-6';
            });
    }
</script>
@endsection
