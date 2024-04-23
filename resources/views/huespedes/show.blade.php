@extends('layouts.appAdmin')

@section('content')

<div class="container-fluid">
    <h2 class="mb-3">{{ __('Información del Cliente :') }} {{ $huesped->nombre != null || $huesped->nombre != '' ? $huesped->nombre : $huesped->alias }}</h2>
    <hr class="mb-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <table class="table">
                <tbody>
                    {{-- <tr>
                        <th style="width: 140px" scope="row">Alias</th>
                        <td>
                            {{$huesped->alias}}
                    </tr> --}}
                    <tr>
                        <th style="width: 140px" scope="row">Nombre</th>
                        <td>{{$huesped->nombre}}</td>
                    </tr>
                    <tr>
                        <th style="width: 140px" scope="row">Primer Apellido</th>
                        <td>{{$huesped->primer_apellido}}</td>
                    </tr>
                    <tr>
                        <th style="width: 140px" scope="row">Segundo Apellido</th>
                        <td>{{$huesped->segundo_apellido}}</td>
                    </tr>
                    {{-- <tr>
                        <th style="width: 140px" scope="row">Nacionalidad</th>
                        <td>{{$huesped->nacionalidad}}</td>
                    </tr> --}}
                    <tr>
                        <th style="width: 140px" scope="row">Tipo de Documento</th>
                        <td>
                            @if ($huesped->tipo_documento == 1)
                                DNI
                            @elseif ($huesped->tipo_documento == 2)
                                Pasaporte
                            @else
                                No especificado
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th style="width: 140px" scope="row">Numero de Identificación</th>
                        <td>{{$huesped->numero_identificacion}}</td>
                    </tr>
                    <tr>
                        <th style="width: 140px" scope="row">Fecha de Expedición</th>
                        <td>{{$huesped->fecha_expedicion}}</td>
                    </tr>
                    <tr>
                        <th style="width: 140px" scope="row">Fecha de Nacimiento</th>
                        <td>{{$huesped->fecha_nacimiento}}</td>
                    </tr>
                    <tr>
                        <th style="width: 140px" scope="row">Sexo</th>
                        <td>{{$huesped->sexo}}</td>
                    </tr>
                    {{-- <tr>
                        <th style="width: 140px" scope="row">Teléfono</th>
                        <td>{{$huesped->telefono}}</td>
                    </tr> --}}
                    <tr>
                        <th style="width: 140px" scope="row">Email</th>
                        <td>{{$huesped->email}}</td>
                    </tr>


                </tbody>
            </table>

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
