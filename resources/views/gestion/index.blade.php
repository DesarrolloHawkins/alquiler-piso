@extends('layouts.appPersonal')

@section('bienvenido')
    {{-- <h5 class="navbar-brand mb-0 w-auto text-center">Bienvenid@ {{Auth::user()->name}}</h5> --}}
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="accordion" id="accordionExample">
                    <div class="accordion-item ">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button text-uppercase fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                {{ __('Apartamentos para limpiar HOY') }}
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                          <div class="accordion-body">
                            @if ($reservasPendientes != null)
                                <ol class="list-group list-group-numbered">
                                    @foreach ($reservasPendientes as $reservaPendiente)
                                        <a class=" list-group-item d-flex justify-content-between align-items-start" href="{{route('gestion.create', $reservaPendiente->id)}}">
                                            <div class="ms-2 me-auto">
                                                <div class="fw-bold">{{$reservaPendiente->id}} - {{$reservaPendiente->apartamento->nombre}} - {{$reservaPendiente->origen}}</div>
                                                Fecha Salida: {{$reservaPendiente->fecha_salida}}
                                            </div>
                                        </a>

                                        {{-- <span class="badge bg-primary rounded-pill">14</span> --}}
                                    @endforeach
                                </ol>
                            @else
                                <h5>No hay apartamentos pendientes</h5>
                            @endif
                          </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mt-4">
                <div class="accordion" id="accordionLimpio">
                    <div class="accordion-item ">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button text-uppercase fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTerminar" aria-expanded="true" aria-controls="collapseTerminar">
                                {{ __('Apartamentos por Terminar') }}
                            </button>
                        </h2>
                        <div id="collapseTerminar" class="accordion-collapse collapse @if(count($reservasEnLimpieza) > 0) show @endif" aria-labelledby="headingOne" data-bs-parent="#accordionLimpio">
                          <div class="accordion-body">
                            @if (count($reservasEnLimpieza) > 0)
                                <ol class="list-group list-group-numbered">
                                    @foreach ($reservasEnLimpieza as $reservaEnLimpieza)
                                        <a class=" list-group-item d-flex justify-content-between align-items-start" href="{{route('gestion.edit', $reservaEnLimpieza->id)}}">
                                            <div class="ms-2 me-auto">
                                                <div class="fw-bold">{{$reservaEnLimpieza->id}} - {{$reservaEnLimpieza->apartamento->nombre}}</div>
                                                Fecha Comienzo: {{$reservaEnLimpieza->fecha_comienzo}}
                                            </div>
                                        </a>

                                        {{-- <span class="badge bg-primary rounded-pill">14</span> --}}
                                    @endforeach
                                </ol>
                            @else
                                <h5>No hay apartamentos en limpieza</h5>
                            @endif
                          </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mt-4">
                <div class="accordion" id="accordionLimpio">
                    <div class="accordion-item ">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button text-uppercase fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLimpios" aria-expanded="true" aria-controls="collapseLimpios">
                                {{ __('Apartamentos Limpiados HOY') }}
                            </button>
                        </h2>
                        <div id="collapseLimpios" class="accordion-collapse collapse @if(count($reservasLimpieza) > 0) show @endif" aria-labelledby="headingOne" data-bs-parent="#accordionLimpio">
                          <div class="accordion-body">
                            @if (count($reservasLimpieza) > 0)
                                <ol class="list-group list-group-numbered">
                                    @foreach ($reservasLimpieza as $reservaLimpieza)
                                    {{-- href="{{route('gestion.create', $reservaLimpieza->id)}}" --}}
                                        <a data-id="{{$reservaLimpieza->id}}" class=" list-group-item d-flex justify-content-between align-items-start">
                                            <div class="ms-2 me-auto">
                                                <div class="fw-bold">{{$reservaLimpieza->id}} - {{$reservaLimpieza->apartamento->nombre}} - {{$reservaLimpieza->origenReserva->origen}}</div>
                                                Fecha Salida: {{$reservaLimpieza->origenReserva->fecha_salida}}
                                            </div>
                                        </a>

                                        {{-- <span class="badge bg-primary rounded-pill">14</span> --}}
                                    @endforeach
                                </ol>
                            @else
                                <h5>No hay apartamentos pendientes</h5>
                            @endif
                          </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mt-4">
                <div class="accordion" id="accordionManania">
                    <div class="accordion-item ">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button text-uppercase fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseManania" aria-expanded="true" aria-controls="collapseManania">
                                {{ __('Apartamentos previstos Mañana') }}
                                {{-- <span class="badge bg-primary rounded-pill">{{count($reservasSalida)}}</span>  --}}
                            </button>
                        </h2>
                        <div id="collapseManania" class="accordion-collapse collapse @if(count($reservasSalida) > 0) {{'show'}} @endif" aria-labelledby="headingOne" data-bs-parent="#accordionManania">
                          <div class="accordion-body">
                            @if (count($reservasSalida) > 0)
                                <ol class="list-group list-group-numbered">
                                    @foreach ($reservasSalida as $reservaSalida)
                                    <div class="ms-2 me-auto">
                                        <div class="fw-bold">{{$reservaSalida->id }} - {{ $reservaSalida->apartamento->nombre}} - {{$reservaSalida->origen}}</div>
                                        Fecha Salida: {{$reservaSalida->fecha_salida}}
                                    </div>

                                        {{-- <span class="badge bg-primary rounded-pill">14</span> --}}
                                    @endforeach
                                </ol>
                            @else
                                <h5>No hay apartamentos pendientes</h5>
                            @endif
                          </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
