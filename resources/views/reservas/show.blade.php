@extends('layouts.app')

@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Rerserva ') . $reserva->codigo_reserva }}</div>
                
                <div class="card-body">
                    <table class="table">
                        <tbody>
                          <tr>
                            <th style="width: 140px" scope="row">Apartamento</th>
                            <td>{{$reserva->apartamento->nombre}}</td>
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
                            <td>@if($reserva->dni_entregado == 1) <span class="badge text-bg-success">Entregado</span> @else <span class="badge text-bg-danger">No entregado</span>@endif</td>
                          </tr>
                          <tr>
                            <th style="width: 140px" scope="row">Cliente</th>
                            <td>{{$reserva->cliente->alias}}</td>
                          </tr>
                          <tr>
                            <th style="width: 140px" scope="row">Fecha Limpieza</th>
                            <td>{{$reserva->fecha_limpieza}}</td>
                          </tr>
                          
                        </tbody>
                      </table>
                      <h4>Mensajes enviado</h4>
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
        </div>
    </div>
</div>
  
@endsection
