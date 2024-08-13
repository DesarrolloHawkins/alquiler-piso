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

</div>
@endsection
