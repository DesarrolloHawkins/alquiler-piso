@extends('layouts.appPersonal')

@section('bienvenido')
    {{-- <h5 class="navbar-brand mb-0 w-auto text-center">Bienvenid@ {{Auth::user()->name}}</h5> --}}
@endsection

@section('content')

    <div class="container">
        <div class="botones mb-3">
            @if(session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if (!$fichajeHoy)
            <!-- Mostrar sólo si no hay jornada activa hoy -->
            <form action="{{ route('fichajes.iniciar') }}" method="POST" class="ml-2 mr-2">
                @csrf
                <button type="submit" class="btn btn-primary w-100">Iniciar Jornada</button>
            </form>
            @else
                @if (!$pausaActiva)
                <!-- Jornada iniciada y no hay pausa activa -->
                <form action="{{ route('fichajes.finalizar') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger w-100">Finalizar Jornada</button>
                </form>
                <form action="{{ route('fichajes.pausa.iniciar') }}" method="POST" style="margin-top: 10px;">
                    @csrf
                    <button type="submit" class="btn btn-warning w-100">Iniciar Pausa</button>
                </form>
                @else
                <!-- Pausa activa -->
                <form action="{{ route('fichajes.pausa.finalizar') }}" method="POST" style="margin-top: 20px;">
                    @csrf
                    <button type="submit" class="btn btn-secondary w-100">Finalizar Pausa</button>
                </form>
                @endif
            @endif
            @if (session('refresh'))
            <script>
                window.location.reload();
            </script>
            @endif
        </div>

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
                                            @php
                                                $routeId = isset($reservaPendiente->id) ? $reservaPendiente->id : 'null - '.$reservaPendiente->apartamento_id;
                                            @endphp
                                            <a class=" list-group-item d-flex justify-content-between align-items-start @if(isset($reservaPendiente->limpieza_fondo)) bg-info text-white @else bg-warning @endif"  href="{{route('gestion.create', $routeId )}}">
                                                <div class="ms-2 me-auto">
                                                    <div class="fw-bold " data-id="{{$reservaPendiente->id}}">{{$reservaPendiente->apartamento->titulo}}</div>
                                                    {{-- <div class="fw-bold">{{$reservaPendiente->id}} - {{$reservaPendiente->apartamento->nombre}} - {{$reservaPendiente->origen}}</div> --}}
                                                    {{-- Fecha Salida: {{$reservaPendiente->fecha_salida}} --}}
                                                    @if ($reservaPendiente->siguienteReserv != null)
                                                        @if ($reservaPendiente->siguienteReserva->numero_personas != null)
                                                            @if (isset($reservaPendiente->limpieza_fondo))
                                                                Limpieza a fondo
                                                            @else
                                                                Numero de Adultos del siguiente Huesped: {{$reservaPendiente->siguienteReserva->numero_personas}}
                                                            @endif
                                                        @else
                                                            @if (isset($reservaPendiente->limpieza_fondo))
                                                                Limpieza a fondo
                                                            @else 
                                                                No tenemos información de la siguiente reserva
                                                            @endif
                                                        @endif
                                                    @else
                                                        @if (isset($reservaPendiente->limpieza_fondo))
                                                            Limpieza a fondo
                                                        @else 
                                                            No tenemos información de la siguiente reserva
                                                        @endif
                                                    @endif
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
                    <div class="accordion" id="accordionEnLimpieza">
                        <div class="accordion-item ">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button text-uppercase fw-bold @if(count($reservasEnLimpieza) < 1) collapsed @endif" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTerminar" aria-expanded="true" aria-controls="collapseTerminar">
                                    {{ __('Apartamentos por Terminar') }}
                                </button>
                            </h2>
                            <div id="collapseTerminar" class="accordion-collapse collapse @if(count($reservasEnLimpieza) > 0) show @endif" aria-labelledby="headingOne" data-bs-parent="#accordionEnLimpieza">
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
                                <button class="accordion-button text-uppercase fw-bold @if(count($reservasLimpieza) < 1) collapsed @endif" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLimpios" aria-expanded="true" aria-controls="collapseLimpios">
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
                                                    <div class="fw-bold">{{$reservaLimpieza->id}} - {{$reservaLimpieza->apartamento->nombre}}</div>
                                                    Fecha Salida: @if(isset($reservaLimpieza->origenReserva->fecha_salida)) {{$reservaLimpieza->origenReserva->fecha_salida}} @endif
                                                </div>
                                            </a>
    
                                            {{-- <span class="badge bg-primary rounded-pill">14</span> --}}
                                        @endforeach
                                    </ol>
                                @else
                                    <h5>No hay apartamentos finalizados</h5>
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
                                <button class="accordion-button text-uppercase fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseManania" aria-expanded="true" aria-controls="collapseManania">
                                    {{ __('Apartamentos previstos Mañana') }}
                                    {{-- <span class="badge bg-primary rounded-pill">{{count($reservasSalida)}}</span>  --}}
                                </button>
                            </h2>
                            <div id="collapseManania" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionManania">
                              <div class="accordion-body">
                                @if (count($reservasSalida) > 0)
                                    <ol class="list-group list-group-numbered">
                                        @foreach ($reservasSalida as $reservaSalida)
                                        <div class="ms-2 me-auto">
                                            <div class="fw-bold">{{$reservaSalida->id }} - {{ $reservaSalida->apartamento->nombre}}</div>
                                            Fecha Salida: {{$reservaSalida->fecha_salida}}
                                        </div>
    
                                            {{-- <span class="badge bg-primary rounded-pill">14</span> --}}
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
    
            </div>
        </div>
    </div>

@endsection
