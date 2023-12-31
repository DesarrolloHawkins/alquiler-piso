@extends('layouts.app')

@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                {{-- Titulo --}}
                <div class="card-header">
                    <h4 class="mb-0"><i class="fa-solid fa-info"></i> {{ __('Información de la Reserva :') }} <span class="fs-6 text-primary align-baseline">{{$reserva->codigo_reserva}}</span></h4>
                </div>     
                <div class="card-body">
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
                        </tbody>
                    </table>
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
