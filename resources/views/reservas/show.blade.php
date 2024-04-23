@extends('layouts.appAdmin')

@section('content')

<div class="container-fluid">
    <h2 class="mb-3">{{ __('Información de la Reserva :') }} <span class="fs-6 text-primary align-baseline">{{$reserva->codigo_reserva}}</span></h2>
    <hr class="mb-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
                {{-- Titulo --}}
                {{-- <div class="card-header">
                    <h4 class="mb-0"><i class="fa-solid fa-info"></i> {{ __('Información de la Reserva :') }} </h4>
                </div>      --}}
                <table class="table">
                    <tbody>
                        <tr>
                            <th style="width: 140px" scope="row">Apartamento</th>
                            <td>
                                {{$reserva->apartamento->nombre}}
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Origen</th>
                            <td>{{$reserva->origen}}</td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Fecha Entrada</th>
                            <td>{{$reserva->fecha_entrada}}</td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Fecha Salida</th>
                            <td>{{$reserva->fecha_salida}}</td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Dni Entregado</th>
                            <td>
                                @if($reserva->dni_entregado == 1) <span class="badge text-bg-success">Entregado</span> @else <span class="badge text-bg-danger">No entregado</span>@endif
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Cliente</th>
                            <td>{{$reserva->cliente->alias}} <a href="{{route('clientes.show', $reserva->cliente_id)}}" class="btn btn-primary ms-3"><i class="fa-regular fa-eye"></i> </a></td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Huespeds</th>
                            {{-- {{dd($huespedes)}} --}}
                            @foreach ($huespedes as $index => $huesped)
                                <td>Huesped {{$index+1}} <a href="{{route('huespedes.show', $huesped->id)}}" class="btn btn-primary ms-3"><i class="fa-regular fa-eye"></i> </a></td>
                            @endforeach
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Fecha Limpieza</th>
                            <td>{{$reserva->fecha_limpieza}}</td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Numero de Adultos</th>
                            <td>{{$reserva->numero_personas}}</td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Enviado a Webpol</th>
                            <td>
                                @if($reserva->enviado_webpol == 1)
                                    <span class="badge text-bg-success">Entregado</span>
                                @else
                                    <span class="badge text-bg-danger">No entregado</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Enlace para DNI</th>
                            <td><a href="http://crm.apartamentosalgeciras.com/dni-user/{{$reserva->token}}">http://crm.apartamentosalgeciras.com/dni-user/{{$reserva->token}}</a></td>
                        </tr>
                    </tbody>
                </table>

                <div class="card mb-4">
                    <div class="card-body">
                        <h4><i class="fa-regular fa-comment"></i> Mensajes enviado</h4>
                        <hr>
                        <table class="table">
                            <thead>
                                <tr>
                                <th scope="col">Fecha de Envio</th>
                                <th scope="col">Categoria del Mensaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (count($mensajes) > 0)
                                    @foreach ($mensajes as $mensaje)
                                    <tr>
                                        <th scope="row">{{$mensaje->fecha_envio}}</th>
                                        <td>{{$mensaje->categoria->nombre}}</td>
                                        </tr>
                                    @endforeach
                                @endif


                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        @if (count($photos) > 1)
                            <h4><i class="fa-regular fa-address-card"></i> DNI</h4>
                            <hr>
                            <div class="row">
                                <div class="col-6">
                                    <img src="{{asset($photos[0]->url)}}" alt="" style="width: 100%">
                                </div>
                                <div class="col-6">
                                    <img src="{{asset($photos[1]->url)}}" alt="" style="width: 100%">
                                </div>
                            </div>
                        @elseif (count($photos) == 1)
                            <h4><i class="fa-regular fa-address-card"></i> Pasaporte</h4>
                            <hr>

                            <img src="{{asset($photos[0]->url)}}" alt="" style="width: 100%">
                        @else
                            <h4><i class="fa-regular fa-address-card"></i> DNI o Pasaporte</h4>
                            <hr>
                            <p>No se subieron ninguna fotos.</p>
                        @endif
                    </div>
                </div>
        </div>
    </div>
</div>

@endsection
