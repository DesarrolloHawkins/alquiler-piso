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
                    
                    @if (count($reservasPendientes) > 0)
                        <ol class="list-group list-group-numbered">
                            @foreach ($reservasPendientes as $reservaPendiente)
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">{{$reservaPendiente->apartamento->nombre}} - {{$reservaPendiente->origen}}</div>
                                    Fecha Salida: {{$reservaPendiente->fecha_salida}}
                                </div>
                                {{-- <span class="badge bg-primary rounded-pill">14</span> --}}
                            </li>
                            @endforeach
                        </ol>
                    @else
                        <h5>No hay apartamentos pendientes</h5>
                    @endif
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-header text-white bg-success"><i class="fa-solid fa-circle-check"></i><span class="ms-2 text-uppercase fw-bold">{{ __('Apartamentos Realizados HOY') }}</span></div>

                <div class="card-body">
                    @if (count($reservasLimpieza) > 0)
                        <ol class="list-group list-group-numbered">
                            @foreach ($reservasLimpieza as $reservaLimpieza)
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                <div class="fw-bold">{{$reservaLimpieza->apartamento->nombre}} - {{$reservaLimpieza->origen}}</div>
                                Fecha Limpieza: {{$reservaLimpieza->fecha_limpieza}}
                                </div>
                                {{-- <span class="badge bg-primary rounded-pill">14</span> --}}
                            </li>
                            @endforeach
                        </ol>
                    @else
                        <h5>No hay apartamentos ocupados</h5>
                    @endif                </div>
            </div>
            <div class="card mt-4">
                <div class="card-header text-white bg-danger"><i class="fa-solid fa-hourglass-half"></i><span class="ms-2 text-uppercase fw-bold">{{ __('Apartamentos Ocupados') }}</span></div>

                <div class="card-body">
                    @if (count($reservasOcupados) > 0)
                        <ol class="list-group list-group-numbered">
                            @foreach ($reservasOcupados as $reservaOcupada)
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                <div class="fw-bold">{{$reservaOcupada->apartamento->nombre}} - {{$reservaOcupada->origen}}</div>
                                Fecha Salida: {{$reservaOcupada->fecha_salida}}
                                </div>
                                {{-- <span class="badge bg-primary rounded-pill">14</span> --}}
                            </li>
                            @endforeach
                        </ol>
                    @else
                        <h5>No hay apartamentos ocupados</h5>
                    @endif
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-header text-white bg-info"><i class="fa-solid fa-circle-chevron-right"></i><span class="ms-2 text-uppercase fw-bold">{{ __('Apartamentos con Salida Mañana') }}</span></div>

                <div class="card-body">
                    @if (count($reservasSalida) > 0)
                        <ol class="list-group list-group-numbered">
                            @foreach ($reservasSalida as $reservaSalida)
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                <div class="fw-bold">{{$reservaSalida->apartamento->nombre}} - {{$reservaSalida->origen}}</div>
                                Fecha Salida: {{$reservaSalida->fecha_salida}}
                                </div>
                                {{-- <span class="badge bg-primary rounded-pill">14</span> --}}
                            </li>
                            @endforeach 
                        </ol>                
                    @else
                        <h5>No hay apartamentos con salida mañana</h5>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
