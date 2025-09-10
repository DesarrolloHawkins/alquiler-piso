@extends('layouts.appPersonal')

@section('title', 'Mis Vacaciones')

@section('bienvenido')
    <h5 class="navbar-brand mb-0 w-auto text-center text-white">Mis Vacaciones</h5>
@endsection

@section('content')
<div class="limpiadora-vacaciones-container">
    <!-- Header de la Página -->
    <div class="apple-card header-card">
        <div class="apple-card-header">
            <div class="header-icon">
                <i class="fas fa-umbrella-beach"></i>
            </div>
            <div class="header-content">
                <h1 class="apple-title">Mis Vacaciones</h1>
                <p class="apple-subtitle">Gestiona tus solicitudes de vacaciones</p>
            </div>
        </div>
    </div>

    <!-- Botón de Nueva Petición -->
    <div class="action-section">
        <a href="{{ route('holiday.create') }}" class="apple-btn apple-btn-primary">
            <i class="fas fa-plus me-2"></i>
            Nueva Petición de Vacaciones
        </a>
    </div>

    <!-- Información de Vacaciones -->
    <div class="stats-section">
        <div class="apple-list-item">
            <div class="apple-list-item-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="apple-list-item-content">
                <div class="apple-list-item-title">Días Disponibles</div>
                <div class="apple-list-item-subtitle">{{ $userHolidaysQuantity ? $userHolidaysQuantity->quantity : 0 }} días restantes</div>
            </div>
            <div class="apple-list-item-value">
                <span class="apple-badge apple-badge-primary">{{ $userHolidaysQuantity ? $userHolidaysQuantity->quantity : 0 }}</span>
            </div>
        </div>

        <div class="apple-list-item">
            <div class="apple-list-item-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="apple-list-item-content">
                <div class="apple-list-item-title">Peticiones Pendientes</div>
                <div class="apple-list-item-subtitle">Esperando aprobación</div>
            </div>
            <div class="apple-list-item-value">
                <span class="apple-badge apple-badge-warning">{{ $numberOfHolidayPetitions ?? 0 }}</span>
            </div>
        </div>
    </div>

    <!-- Estados de Vacaciones -->
    <div class="apple-card">
        <div class="apple-card-header">
            <h3 class="apple-title">
                <i class="fas fa-info-circle me-2"></i>
                Estados de las Peticiones
            </h3>
        </div>
        <div class="apple-card-body">
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
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="apple-card">
        <div class="apple-card-header">
            <h3 class="apple-title">
                <i class="fas fa-filter me-2"></i>
                Filtros y Búsqueda
            </h3>
        </div>
        <div class="apple-card-body">
            <form method="GET" action="{{ route('holiday.index') }}" class="filters-form">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="perPage" class="apple-label">Elementos por página</label>
                        <select name="perPage" id="perPage" class="apple-input" onchange="this.form.submit()">
                            <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                            <option value="all" {{ request('perPage') == 'all' ? 'selected' : '' }}>Todo</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="buscar" class="apple-label">Buscar</label>
                        <input type="text" name="buscar" id="buscar" class="apple-input"
                               value="{{ request('buscar') }}" placeholder="Buscar peticiones...">
                    </div>

                    <div class="filter-group">
                        <label for="estado" class="apple-label">Estado</label>
                        <select name="estado" id="estado" class="apple-input" onchange="this.form.submit()">
                            <option value="" {{ request('estado') == '' ? 'selected' : '' }}>Todos</option>
                            <option value="1" {{ request('estado') == '1' ? 'selected' : '' }}>Aceptada</option>
                            <option value="2" {{ request('estado') == '2' ? 'selected' : '' }}>Denegada</option>
                            <option value="3" {{ request('estado') == '3' ? 'selected' : '' }}>Pendiente</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Peticiones -->
    @if ($holidays->count())
        <div class="apple-card">
            <div class="apple-card-header">
                <h3 class="apple-title">
                    <i class="fas fa-list me-2"></i>
                    Mis Peticiones de Vacaciones
                </h3>
            </div>
            <div class="apple-card-body">
                <!-- Versión Desktop -->
                <div class="holidays-table desktop-only">
                    <table class="apple-table">
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
                                        <i class="fas fa-calendar me-2"></i>
                                        {{ Carbon\Carbon::parse($holiday->from)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($holiday->to)->format('d/m/Y') }}
                                    </td>
                                    <td class="half-day">
                                        @if($holiday->half_day)
                                            <span class="apple-badge apple-badge-success"><i class="fas fa-check me-1"></i>Sí</span>
                                        @else
                                            <span class="apple-badge apple-badge-secondary"><i class="fas fa-times me-1"></i>No</span>
                                        @endif
                                    </td>
                                    <td class="total-days">
                                        <span class="days-number">{{ $holiday->total_days }}</span>
                                    </td>
                                    <td class="status">
                                        @if($holiday->holidays_status_id == 1)
                                            <span class="apple-badge apple-badge-success">Aceptada</span>
                                        @elseif($holiday->holidays_status_id == 2)
                                            <span class="apple-badge apple-badge-danger">Denegada</span>
                                        @elseif($holiday->holidays_status_id == 3)
                                            <span class="apple-badge apple-badge-warning">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="created-date">
                                        <i class="fas fa-clock me-2"></i>
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
                        <div class="apple-list-item holiday-card status-{{ $holiday->holidays_status_id }}">
                            <div class="apple-list-item-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="apple-list-item-content">
                                <div class="apple-list-item-title">
                                    {{ Carbon\Carbon::parse($holiday->from)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($holiday->to)->format('d/m/Y') }}
                                </div>
                                <div class="apple-list-item-subtitle">
                                    {{ $holiday->total_days }} días • {{ $holiday->half_day ? 'Medio día' : 'Día completo' }}
                                </div>
                                <div class="apple-list-item-detail">
                                    <i class="fas fa-paper-plane me-1"></i>
                                    {{ Carbon\Carbon::parse($holiday->created_at)->format('d/m/Y H:i') }}
                                </div>
                            </div>
                            <div class="apple-list-item-value">
                                @if($holiday->holidays_status_id == 1)
                                    <span class="apple-badge apple-badge-success">Aceptada</span>
                                @elseif($holiday->holidays_status_id == 2)
                                    <span class="apple-badge apple-badge-danger">Denegada</span>
                                @elseif($holiday->holidays_status_id == 3)
                                    <span class="apple-badge apple-badge-warning">Pendiente</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Paginación -->
        <div class="pagination-section">
            {{ $holidays->appends(request()->all())->links() }}
        </div>

    @else
        <!-- Estado Vacío -->
        <div class="apple-card">
            <div class="apple-card-body text-center">
                <div class="empty-icon">
                    <i class="fas fa-umbrella-beach"></i>
                </div>
                <h3 class="apple-title">No tienes peticiones de vacaciones</h3>
                <p class="apple-subtitle">Cuando hagas una petición, aparecerá aquí</p>
                <a href="{{ route('holiday.create') }}" class="apple-btn apple-btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Hacer Primera Petición
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/gestion-buttons.css') }}">
<link rel="stylesheet" href="{{ asset('css/holidays-index.css') }}">
@endpush

@section('scripts')
    @include('partials.toast')
@endsection

