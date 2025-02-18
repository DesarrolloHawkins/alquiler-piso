@extends('layouts.appPersonal')

@section('title')
    {{ __('Mis Vacaciones - ') }}
@endsection

@section('bienvenido')
    <h5 class="navbar-brand mb-0 w-auto text-center text-white">Mis Vacaciones</h5>
@endsection

@section('css')
    <link rel="stylesheet" href="assets/vendors/simple-datatables/style.css">
    <link rel="stylesheet" href="{{asset('assets/vendors/choices.js/choices.min.css')}}" />
@endsection

@section('content')
    <div class="container" style="padding-right: 1.5rem !important; padding-left: 1.5rem !important;">
        <div class="row">
            <div class="col-sm-12 col-md-12 mb-3">
                <h3 class="text-center" style="width: 100%"><i class="fa-solid fa-umbrella-beach"></i> Mis Vacaciones</h3>
            </div>

            <div class="col-sm-12 col-md-12 mb-3 justify-content-center d-flex">
                <a class="btn bg-color-segundo fs-4 width-auto" href="{{route('holiday.create')}}">
                    <i class="fa-solid fa-plus"></i> Petición de vacaciones
                </a>
            </div>
        </div>
        <div class="page-heading mt-4" style="box-shadow: none !important">
            <div class="row align-items-start">
                {{-- DIAS DISPONIBLES Y PETICIONES --}}
                <div class="col-sm-12 col-md-6" style="text-align: center">
                    <div class="row justify-content-center align-items-center g-2">
                        <div class="col-sm-12 col-md-6">
                            <p for="status"><strong>DIAS DISPONIBLES</strong></p>
                            @if($userHolidaysQuantity)
                                @if($userHolidaysQuantity->quantity == 1)
                                    <p for="have">Tienes <span style="color:green"><strong>{{$userHolidaysQuantity->quantity}}</strong></span> día de vacaciones</p>
                                @endif
                                @if($userHolidaysQuantity->quantity >1 )
                                    <p for="have">Tienes <span style="color:green"><strong>{{$userHolidaysQuantity->quantity}}</strong></span> días de vacaciones</p>
                                @endif
                            @else
                                <p for="have">No tienes días de vacaciones</p>
                            @endif
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <p for="status"><strong>PETICIONES</strong></p>
                            @if($numberOfHolidayPetitions)
                                @if($numberOfHolidayPetitions == 1)
                                    <p for="pendant">Tienes <span style="color:orange"><strong>{{$numberOfHolidayPetitions}}</strong></span> petición pendiente</p>
                                @endif
                                @if($numberOfHolidayPetitions >1 )
                                    <p for="pendant">Tienes <span style="color:orange"><strong>{{$numberOfHolidayPetitions}}</strong></span> peticiones pendientes</p>
                                @endif
                            @else
                                <p for="pendant">No tienes peticiones pendientes</p>
                            @endif
                        </div>
                    </div>
                </div>
                {{-- ESTADOS --}}
                <div class="col-sm-12 col-md-6">
                    <div class="card2">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12" style="text-align: center">
                                    <p for="status"><strong>ESTADOS</strong></p>
                                    <p for="pendant">
                                        <i class="fa fa-square" aria-hidden="true" style="color:#FFDD9E"></i>&nbsp;&nbsp;PENDIENTE
                                        <i class="fa fa-square" aria-hidden="true" style="margin-left:5%;color:#C3EBC4"></i>&nbsp;&nbsp;ACEPTADA
                                        <i class="fa fa-square" aria-hidden="true" style="margin-left:5%;color:#FBC4C4"></i>&nbsp;&nbsp;DENEGADA
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <form method="GET" action="{{ route('holiday.index') }}" class="mb-4">
                    <div class="row">
                        <!-- Selector de número de elementos por página -->
                        <div class="col-md-3 col-sm-12">
                            <label for="perPage">Nº</label>
                            <select name="perPage" id="perPage" class="form-select" onchange="this.form.submit()">
                                <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                                <option value="all" {{ request('perPage') == 'all' ? 'selected' : '' }}>Todo</option>
                            </select>
                        </div>

                        <!-- Campo de búsqueda -->
                        <div class="col-md-5 col-sm-12">
                            <label for="buscar">Buscar</label>
                            <input type="text" name="buscar" id="buscar" class="form-control"
                                   value="{{ request('buscar') }}" placeholder="Escriba la palabra a buscar...">
                        </div>

                        <!-- Filtro por estado -->
                        <div class="col-md-4 col-sm-12">
                            <label for="estado">Estado</label>
                            <select name="estado" id="estado" class="form-select" onchange="this.form.submit()">
                                <option value="" {{ request('estado') == '' ? 'selected' : '' }}>Todos</option>
                                <option value="1" {{ request('estado') == '1' ? 'selected' : '' }}>Aceptada</option>
                                <option value="2" {{ request('estado') == '2' ? 'selected' : '' }}>Denegada</option>
                                <option value="3" {{ request('estado') == '3' ? 'selected' : '' }}>Pendiente</option>
                            </select>
                        </div>
                    </div>
                </form>


                <!-- Tabla de resultados -->
                @if ($holidays->count())
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    @foreach ([
                                        'from' => 'DÍA/S PEDIDOS',
                                        'half_day' => 'MEDIO DÍA',
                                        'total_days' => 'DÍAS EN TOTAL',
                                        'holidays_status_id' => 'ESTADO',
                                        'created_at' => 'FECHA DE PETICIÓN',
                                    ] as $field => $label)
                                        <th>
                                            <a href="{{ route('holiday.index', array_merge(request()->all(), ['sortColumn' => $field, 'sortDirection' => request('sortDirection') === 'asc' ? 'desc' : 'asc'])) }}">
                                                {{ $label }}
                                                @if (request('sortColumn') === $field)
                                                    <span>{!! request('sortDirection') === 'asc' ? '&#9650;' : '&#9660;' !!}</span>
                                                @endif
                                            </a>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($holidays as $holiday)
                                    <tr
                                        @if($holiday->holidays_status_id == 3)
                                            class="table-warning" style="background-color:#FFDD9E"
                                        @elseif($holiday->holidays_status_id == 1)
                                            class="table-success" style="background-color:#C3EBC4"
                                        @elseif($holiday->holidays_status_id == 2)
                                            class="table-danger" style="background-color:#FBC4C4"
                                        @endif
                                    >
                                        <td>{{ Carbon\Carbon::parse($holiday->from)->format('d/m/Y') . ' - ' . Carbon\Carbon::parse($holiday->to)->format('d/m/Y') }}</td>
                                        <td>{!! $holiday->half_day ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>' !!}</td>
                                        <td>{{ $holiday->total_days }}</td>
                                        <td>
                                            @if($holiday->holidays_status_id == 1)
                                                Aceptada
                                            @elseif($holiday->holidays_status_id == 2)
                                                Denegada
                                            @elseif($holiday->holidays_status_id == 3)
                                                Pendiente
                                            @endif
                                        </td>
                                        <td>{{ Carbon\Carbon::parse($holiday->created_at)->format('d/m/Y H:i:s') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Paginación -->
                        {{ $holidays->appends(request()->all())->links() }}
                    </div>

                    <!-- Versión móvil -->
                    <div class="d-md-none">
                        @foreach ($holidays as $holiday)
                            <div
                                class="card mb-3
                                    @if($holiday->holidays_status_id == 3)
                                        table-warning
                                    @elseif($holiday->holidays_status_id == 1)
                                        table-success
                                    @elseif($holiday->holidays_status_id == 2)
                                        table-danger
                                    @endif"
                                style="
                                    @if($holiday->holidays_status_id == 3)
                                        background-color:#FFDD9E;
                                    @elseif($holiday->holidays_status_id == 1)
                                        background-color:#C3EBC4;
                                    @elseif($holiday->holidays_status_id == 2)
                                        background-color:#FBC4C4;
                                    @endif"
                            >
                                <div class="card-body">
                                    <h5 class="card-title">
                                        Días pedidos: {{ Carbon\Carbon::parse($holiday->from)->format('d/m/Y') . ' - ' . Carbon\Carbon::parse($holiday->to)->format('d/m/Y') }}
                                    </h5>
                                    <p class="card-text">
                                        <strong>Medio Día:</strong> {!! $holiday->half_day ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>' !!}<br>
                                        <strong>Días en Total:</strong> {{ $holiday->total_days }}<br>
                                        <strong>Estado:</strong>
                                        @if($holiday->holidays_status_id == 1)
                                            Aceptada
                                        @elseif($holiday->holidays_status_id == 2)
                                            Denegada
                                        @elseif($holiday->holidays_status_id == 3)
                                            Pendiente
                                        @endif
                                        <br>
                                        <strong>Fecha de Petición:</strong> {{ Carbon\Carbon::parse($holiday->created_at)->format('d/m/Y H:i:s') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach

                        <!-- Paginación -->
                        {{ $holidays->appends(request()->all())->links() }}
                    </div>

                @else
                    <div class="text-center py-4">
                        <h3>No se encontraron registros de <strong>Vacaciones</strong></h3>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')

    @include('partials.toast')

@endsection

