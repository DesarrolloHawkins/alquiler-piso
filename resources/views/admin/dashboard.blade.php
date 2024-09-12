@extends('layouts.appAdmin')

@section('bienvenido')
@section('tituloSeccion', 'Dashboard')
@endsection

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
<div class="container px-4">

    <div class="row">
        <div class="col-md-3">
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
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-4 mt-4">
                <form action="{{ route('dashboard.index') }}" method="GET">
                    <label for="mes">Selecciona un mes:</label>
                    <select name="mes" id="mes" class="form-control">
                        <option value="1" {{ (request('mes') == 1) ? 'selected' : '' }}>Enero</option>
                        <option value="2" {{ (request('mes') == 2) ? 'selected' : '' }}>Febrero</option>
                        <option value="3" {{ (request('mes') == 3) ? 'selected' : '' }}>Marzo</option>
                        <option value="4" {{ (request('mes') == 4) ? 'selected' : '' }}>Abril</option>
                        <option value="5" {{ (request('mes') == 5) ? 'selected' : '' }}>Mayo</option>
                        <option value="6" {{ (request('mes') == 6) ? 'selected' : '' }}>Junio</option>
                        <option value="7" {{ (request('mes') == 7) ? 'selected' : '' }}>Julio</option>
                        <option value="8" {{ (request('mes') == 8) ? 'selected' : '' }}>Agosto</option>
                        <option value="9" {{ (request('mes') == 9) ? 'selected' : '' }}>Septiembre</option>
                        <option value="10" {{ (request('mes') == 10) ? 'selected' : '' }}>Octubre</option>
                        <option value="11" {{ (request('mes') == 11) ? 'selected' : '' }}>Noviembre</option>
                        <option value="12" {{ (request('mes') == 12) ? 'selected' : '' }}>Diciembre</option>
                    </select>
                    <button type="submit" class="btn btn-primary mt-3 w-100">Consultar</button>
                </form>
    
                <div class="container mt-4">
                    <h4 class="text-center">Reporte de Reservas para el Año {{ $anio }} @if($mes) y Mes <span class="text-capitalize">{{ $mesNombre }} </span>@endif</h4>
                    <hr>

                    <p class="mb-0 text-center fs-5">Total de Reservas:</p>
                    <p class="fs-4 mb-0 text-center"><strong>{{ $countReservas }}</strong></p>
                    <p class="mb-0 text-center fs-5">Total de Ingresos:</p>
                    <p class="fs-4 mb-0 text-center"><strong>{{ number_format($sumPrecio, 2) }} €</strong></p>
                    <button class="btn btn-warning text-white w-100 mt-3" data-bs-toggle="modal" data-bs-target="#reservasModal">
                        Ver reservas
                    </button>
                </div>
                
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="reservasModal" tabindex="-1" aria-labelledby="reservasModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-flex justify-content-between" id="reservasModalLabel"><span>Reservas</span> <span class="ms-5">Sumatorio: <strong>{{ number_format($sumPrecio, 2) }} €</strong> </span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Tabla para mostrar las reservas -->
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Código de Reserva</th>
                                <th>Origen</th>
                                <th>Fecha Entrada</th>
                                <th>Fecha Salida</th>
                                <th>Precio</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reservas as $reserva)
                                <tr>
                                    <td>{{ $reserva->id }}</td>
                                    <td>{{ $reserva->codigo_reserva }}</td>
                                    <td>{{ $reserva->origen }}</td>
                                    <td>{{ $reserva->fecha_entrada }}</td>
                                    <td>{{ $reserva->fecha_salida }}</td>
                                    <td>{{ number_format($reserva->precio, 2) }} €</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <h6>Sumatorio: <strong>{{ number_format($sumPrecio, 2) }} €</strong> </h6>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
