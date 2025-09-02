@extends('layouts.appPersonal')

@section('title', 'Mis Vacaciones')

@section('bienvenido')
    <h5 class="navbar-brand mb-0 w-auto text-center text-white">Mis Vacaciones</h5>
@endsection

@section('content')
<div class="holidays-index-container">
    <!-- Header de la Página -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-umbrella-beach"></i>
            </div>
            <div class="header-text">
                <h1>Mis Vacaciones</h1>
                <p>Gestiona tus solicitudes de vacaciones</p>
            </div>
        </div>
    </div>

    <!-- Botón de Nueva Petición -->
    <div class="action-section">
        <a href="{{ route('holiday.create') }}" class="new-request-btn">
            <i class="fas fa-plus"></i>
            <span>Nueva Petición de Vacaciones</span>
        </a>
    </div>

    <!-- Información de Vacaciones -->
    <div class="info-section">
        <div class="info-cards">
            <!-- Días Disponibles -->
            <div class="info-card primary">
                <div class="card-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="card-content">
                    <div class="card-number">{{ $userHolidaysQuantity ? $userHolidaysQuantity->quantity : 0 }}</div>
                    <div class="card-label">Días Disponibles</div>
                </div>
            </div>

            <!-- Peticiones Pendientes -->
            <div class="info-card warning">
                <div class="card-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="card-content">
                    <div class="card-number">{{ $numberOfHolidayPetitions ?? 0 }}</div>
                    <div class="card-label">Peticiones Pendientes</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estados de Vacaciones -->
    <div class="status-section">
        <h3 class="section-title">
            <i class="fas fa-info-circle"></i>
            Estados de las Peticiones
        </h3>
        <div class="status-legend">
            <div class="status-item">
                <div class="status-color pending"></div>
                <span>Pendiente</span>
            </div>
            <div class="status-item">
                <div class="status-color approved"></div>
                <span>Aceptada</span>
            </div>
            <div class="status-item">
                <div class="status-color denied"></div>
                <span>Denegada</span>
            </div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="filters-section">
        <form method="GET" action="{{ route('holiday.index') }}" class="filters-form">
            <div class="filters-grid">
                <div class="filter-group">
                    <label for="perPage">Elementos por página</label>
                    <select name="perPage" id="perPage" class="form-control" onchange="this.form.submit()">
                        <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                        <option value="all" {{ request('perPage') == 'all' ? 'selected' : '' }}>Todo</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="buscar">Buscar</label>
                    <input type="text" name="buscar" id="buscar" class="form-control"
                           value="{{ request('buscar') }}" placeholder="Buscar peticiones...">
                </div>

                <div class="filter-group">
                    <label for="estado">Estado</label>
                    <select name="estado" id="estado" class="form-control" onchange="this.form.submit()">
                        <option value="" {{ request('estado') == '' ? 'selected' : '' }}>Todos</option>
                        <option value="1" {{ request('estado') == '1' ? 'selected' : '' }}>Aceptada</option>
                        <option value="2" {{ request('estado') == '2' ? 'selected' : '' }}>Denegada</option>
                        <option value="3" {{ request('estado') == '3' ? 'selected' : '' }}>Pendiente</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- Lista de Peticiones -->
    @if ($holidays->count())
        <!-- Versión Desktop -->
        <div class="holidays-table desktop-only">
            <table class="table">
                <thead>
                    <tr>
                        @foreach ([
                            'from' => 'Días Pedidos',
                            'half_day' => 'Medio Día',
                            'total_days' => 'Total Días',
                            'holidays_status_id' => 'Estado',
                            'created_at' => 'Fecha Petición',
                        ] as $field => $label)
                            <th>
                                <a href="{{ route('holiday.index', array_merge(request()->all(), ['sortColumn' => $field, 'sortDirection' => request('sortDirection') === 'asc' ? 'desc' : 'asc'])) }}" class="sort-link">
                                    {{ $label }}
                                    @if (request('sortColumn') === $field)
                                        <i class="fas fa-sort-{{ request('sortDirection') === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($holidays as $holiday)
                        <tr class="holiday-row status-{{ $holiday->holidays_status_id }}">
                            <td class="date-range">
                                <i class="fas fa-calendar"></i>
                                {{ Carbon\Carbon::parse($holiday->from)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($holiday->to)->format('d/m/Y') }}
                            </td>
                            <td class="half-day">
                                @if($holiday->half_day)
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Sí</span>
                                @else
                                    <span class="badge badge-secondary"><i class="fas fa-times"></i> No</span>
                                @endif
                            </td>
                            <td class="total-days">
                                <span class="days-number">{{ $holiday->total_days }}</span>
                            </td>
                            <td class="status">
                                @if($holiday->holidays_status_id == 1)
                                    <span class="status-badge approved">Aceptada</span>
                                @elseif($holiday->holidays_status_id == 2)
                                    <span class="status-badge denied">Denegada</span>
                                @elseif($holiday->holidays_status_id == 3)
                                    <span class="status-badge pending">Pendiente</span>
                                @endif
                            </td>
                            <td class="created-date">
                                <i class="fas fa-clock"></i>
                                {{ Carbon\Carbon::parse($holiday->created_at)->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Versión Móvil -->
        <div class="holidays-cards mobile-only">
            @foreach ($holidays as $holiday)
                <div class="holiday-card status-{{ $holiday->holidays_status_id }}">
                    <div class="card-header">
                        <div class="date-range">
                            <i class="fas fa-calendar"></i>
                            {{ Carbon\Carbon::parse($holiday->from)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($holiday->to)->format('d/m/Y') }}
                        </div>
                        <div class="status-badge">
                            @if($holiday->holidays_status_id == 1)
                                <span class="approved">Aceptada</span>
                            @elseif($holiday->holidays_status_id == 2)
                                <span class="denied">Denegada</span>
                            @elseif($holiday->holidays_status_id == 3)
                                <span class="pending">Pendiente</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="card-info">
                            <div class="info-item">
                                <i class="fas fa-clock"></i>
                                <span>Medio día: {{ $holiday->half_day ? 'Sí' : 'No' }}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-calendar-day"></i>
                                <span>Total: {{ $holiday->total_days }} días</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-paper-plane"></i>
                                <span>Solicitado: {{ Carbon\Carbon::parse($holiday->created_at)->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Paginación -->
        <div class="pagination-section">
            {{ $holidays->appends(request()->all())->links() }}
        </div>

    @else
        <!-- Estado Vacío -->
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-umbrella-beach"></i>
            </div>
            <h3>No tienes peticiones de vacaciones</h3>
            <p>Cuando hagas una petición, aparecerá aquí</p>
            <a href="{{ route('holiday.create') }}" class="new-request-btn">
                <i class="fas fa-plus"></i>
                <span>Hacer Primera Petición</span>
            </a>
        </div>
    @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/holidays-index.css') }}">
@endpush

@section('scripts')
    @include('partials.toast')
@endsection

