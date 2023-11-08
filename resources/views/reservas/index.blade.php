@extends('layouts.app')
@section('scriptHead')
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.9/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.9/index.global.min.js'></script>
    <script>

        document.addEventListener('DOMContentLoaded', function() {
          var calendarEl = document.getElementById('calendar');
  
          var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: function(fetchInfo, successCallback, failureCallback) {
      fetch('/get-reservas')
        .then(response => response.json())
        .then(data => {
          var events = data.map(function(reserva) {
            return {
              title: reserva.cliente.alias + ' - ' +reserva.apartamento.nombre, // o cualquier otro campo que quieras usar como título
              start: reserva.fecha_entrada,
              end: reserva.fecha_salida,
              // Aquí puedes añadir más propiedades según necesites
            };
          });
          successCallback(events);
        })
        .catch(error => {
          failureCallback(error);
        });
    }
          });
  
          calendar.render();
        });
  
      </script>
@endsection
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Nuestros Clientes') }}</div>

                <div class="card-body">
                    {{-- @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Apellidos</th>
                                <th scope="col">DNI</th>
                                <th scope="col">Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row">1</th>
                                <td>Mark</td>
                                <td>Otto</td>
                                <td>@mdo</td>
                                <td><a href="" class="btn btn-primary">Editar</a></td>
                            </tr>                 
                        </tbody>
                    </table> --}}
                    <div id='calendar'></div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
