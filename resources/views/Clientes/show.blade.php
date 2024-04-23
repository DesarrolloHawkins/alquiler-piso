@extends('layouts.appAdmin')

@section('content')

<div class="container-fluid">
    <h2 class="mb-3">{{ __('Información del Cliente :') }} {{ $cliente->nombre != null || $cliente->nombre != '' ? $cliente->nombre : $cliente->alias }}</h2>
    <hr class="mb-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <table class="table">
                <tbody>
                    <tr>
                        <th style="width: 140px" scope="row">Alias</th>
                        <td>
                            {{$cliente->alias}}
                    </tr>
                    <tr>
                        <th style="width: 140px" scope="row">Nombre</th>
                        <td>{{$cliente->nombre}}</td>
                    </tr>
                    <tr>
                        <th style="width: 140px" scope="row">Primer Apellido</th>
                        <td>{{$cliente->apellido1}}</td>
                    </tr>
                    <tr>
                        <th style="width: 140px" scope="row">Segundo Apellido</th>
                        <td>{{$cliente->apellido2}}</td>
                    </tr>
                    <tr>
                        <th style="width: 140px" scope="row">Nacionalidad</th>
                        <td>{{$cliente->nacionalidad}}</td>
                    </tr>
                    <tr>
                        <th style="width: 140px" scope="row">Tipo de Documento</th>
                        <td>
                            @if ($cliente->tipo_documento == 1)
                                DNI
                            @elseif ($cliente->tipo_documento == 2)
                                Pasaporte
                            @else
                                No especificado
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th style="width: 140px" scope="row">Numero de Identificación</th>
                        <td>{{$cliente->num_identificacion}}</td>
                    </tr>
                    <tr>
                        <th style="width: 140px" scope="row">Fecha de Expedición</th>
                        <td>{{$cliente->fecha_expedicion_doc}}</td>
                    </tr>
                    <tr>
                        <th style="width: 140px" scope="row">Fecha de Nacimiento</th>
                        <td>{{$cliente->fecha_nacimiento}}</td>
                    </tr>
                    <tr>
                        <th style="width: 140px" scope="row">Sexo</th>
                        <td>{{$cliente->sexo}}</td>
                    </tr>
                    <tr>
                        <th style="width: 140px" scope="row">Teléfono</th>
                        <td>{{$cliente->telefono}}</td>
                    </tr>
                    <tr>
                        <th style="width: 140px" scope="row">Email</th>
                        <td>{{$cliente->email}}</td>
                    </tr>


                </tbody>
            </table>
            <div class="card">
                <div class="card-body">
                    <h4><i class="fa-regular fa-address-book"></i> Reservas realizadas</h4>
                    <hr>
                    <table class="table">
                        <thead>
                            <tr>
                            <th scope="col">Apartamento</th>
                            <th scope="col">Fecha de entrada</th>
                            <th scope="col">Fecha de salida</th>
                            <th scope="col">Origen</th>
                            <th scope="col">Precio</th>
                            <th scope="col">Codigo de reserva</th>
                            </tr>
                        </thead>
                        <tbody>
                            @isset($reservas)
                                @if (count($reservas) > 0)
                                    @foreach ($reservas as $reserva)
                                    <tr>
                                        <th scope="row">{{$reserva->apartamento->nombre}}</th>
                                        <td>{{$reserva->fecha_entrada}}</td>
                                        <td>{{$reserva->fecha_salida}}</td>
                                        <td>{{$reserva->origen}}</td>
                                        <td>{{$reserva->precio}} €</td>
                                        <td>{{$reserva->codigo_reserva}}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endisset
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-body">
                    <h4><i class="fa-regular fa-comment"></i> Mensajes enviado</h4>
                    <hr>
                    <table class="table">
                        <thead>
                            <tr>
                            <th scope="col">Reserva</th>
                            <th scope="col">Fecha de Envio</th>
                            <th scope="col">Categoria del Mensaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            @isset($mensajes)
                                @if (count($mensajes) > 0)
                                    @foreach ($mensajes as $mensaje)
                                    <tr>
                                        <th scope="row">{{$mensaje->reserva_id}}</th>
                                        <td>{{$mensaje->fecha_envio}}</td>
                                        <td>{{$mensaje->categoria->nombre}}</td>
                                        <td><a href="{{route('reservas.show', $mensaje->reserva_id)}}" class="btn btn-primary"><i class="fa-regular fa-eye"></i></a></td>
                                    </tr>
                                    @endforeach
                                @endif
                            @endisset
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-body">
                    @isset($photos)
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
                    @else
                        <h4><i class="fa-regular fa-address-card"></i> DNI o Pasaporte</h4>
                        <hr>
                        <p>No se subieron ninguna fotos.</p>
                    @endisset
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <h4 class="mt-4 text-center">Acciones</h4>
                <div class="card-body">
                    <a class="btn btn-secundario w-100 fs-4"><i class="fa-regular fa-pen-to-square"></i> Editar</a>
                    <a class="btn btn-secundario w-100 fs-4 mt-2"><i class="fa-regular fa-pen-to-square"></i> Editar</a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
