@extends('layouts.appPersonal')
@section('bienvenido')
<h5 class="navbar-brand mb-0 w-auto text-center">Bienvenid@ {{Auth::user()->name}}</h5>
@endsection
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-white bg-warning"><i class="fa-solid fa-circle-info"></i><span class="ms-2 text-uppercase fw-bold">{{ __('Apartamentos Pendientes') }}</span></div>

                <div class="card-body">
                    <h2>Bienvenido {{Auth::user()->name}}</h2>
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-header text-white bg-success"><i class="fa-solid fa-circle-check"></i><span class="ms-2 text-uppercase fw-bold">{{ __('Apartamentos Realizados HOY') }}</span></div>

                <div class="card-body">
                    <h2>Bienvenido {{Auth::user()->name}}</h2>
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-header text-white bg-danger"><i class="fa-solid fa-hourglass-half"></i><span class="ms-2 text-uppercase fw-bold">{{ __('Apartamentos Ocupados') }}</span></div>

                <div class="card-body">
                    <h2>Bienvenido {{Auth::user()->name}}</h2>
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-header text-white bg-info"><i class="fa-solid fa-circle-chevron-right"></i><span class="ms-2 text-uppercase fw-bold">{{ __('Apartamentos con Salida Mañana') }}</span></div>

                <div class="card-body">
                    <h2>Bienvenido {{Auth::user()->name}}</h2>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
