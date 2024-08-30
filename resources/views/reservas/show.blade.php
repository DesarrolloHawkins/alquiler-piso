@extends('layouts.appAdmin')

@section('content')
<!-- Incluir el CSS de Lightbox2 desde un CDN confiable -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet">

<!-- Incluir el JavaScript de Lightbox2 desde un CDN confiable -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>


<div class="container-fluid">
    <div class="d-flex flex-colum mb-3">
        <h2 class="mb-0 me-3 encabezado_top">{{ __('Información de la Reserva: ') }}<span class="text-primary align-baseline">{{$reserva->codigo_reserva}}</span></h2>
    </div>
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
                                {{$reserva->apartamento->titulo}}
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Edificio</th>
                            <td>
                                {{$reserva->apartamento->edificioName->nombre}}
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
                            <td>{{$reserva->cliente->alias}} <a href="{{route('clientes.show', $reserva->cliente_id)}}" class="btn bg-color-quinto ms-3"><i class="fa-regular fa-eye"></i> </a></td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Huespeds</th>
                            {{-- {{dd($huespedes)}} --}}
                            @foreach ($huespedes as $index => $huesped)
                                <td>Huesped {{$index+1}} <a href="{{route('huespedes.show', $huesped->id)}}" class="btn bg-color-quinto ms-3"><i class="fa-regular fa-eye"></i> </a></td>
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
                                    <a href="{{ asset($photos[0]->url) }}" data-lightbox="dni-gallery">
                                        <img src="{{ asset($photos[0]->url) }}" alt="" style="object-fit: cover; max-height: 200px; width: 100%;">
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="{{ asset($photos[1]->url) }}" data-lightbox="dni-gallery">
                                        <img src="{{ asset($photos[1]->url) }}" alt="" style="object-fit: cover; max-height: 200px; width: 100%;">
                                    </a>
                                </div>
                            </div>
                        @elseif (count($photos) == 1)
                            <h4><i class="fa-regular fa-address-card"></i> Pasaporte</h4>
                            <hr>
                            <a href="{{ asset($photos[0]->url) }}" data-lightbox="passport">
                                <img src="{{ asset($photos[0]->url) }}" alt="" style="object-fit: cover; max-height: 200px; width: 100%;">
                            </a>
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
