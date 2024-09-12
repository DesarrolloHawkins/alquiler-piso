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
                        <option value="1">Enero</option>
                        <option value="2">Febrero</option>
                        <option value="3">Marzo</option>
                        <option value="4">Abril</option>
                        <option value="5">Mayo</option>
                        <option value="6">Junio</option>
                        <option value="7">Julio</option>
                        <option value="8">Agosto</option>
                        <option value="9">Septiembre</option>
                        <option value="10">Octubre</option>
                        <option value="11">Noviembre</option>
                        <option value="12">Diciembre</option>
                    </select>
                    <button type="submit" class="btn btn-primary mt-3 w-100">Consultar</button>
                </form>
                <div class="container mt-4">
                    <h4 class="text-center">Reporte de Reservas para el Año {{ $anio }} @if($mes) y Mes {{ $mes }} @endif</h4>
                    <hr>

                    <p class="mb-0 text-center fs-5">Total de Reservas:</p>
                    <p class="fs-4 mb-0 text-center"><strong>{{ $countReservas }}</strong></p>
                    <p class="mb-0 text-center fs-5">Total de Ingresos:</p>
                    <p class="fs-4 mb-0 text-center"><strong>{{ number_format($sumPrecio, 2) }} €</strong></p>

                </div>
                
            </div>
        </div>
    </div>
    
</div>
@endsection
