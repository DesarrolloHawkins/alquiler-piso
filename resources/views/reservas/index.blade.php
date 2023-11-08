@extends('layouts.app')
@section('scriptHead')
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.9/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.9/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/locale/es.js'></script>

    <script>

        document.addEventListener('DOMContentLoaded', function() {

            var calendarEl = document.getElementById('calendar');
            // Mapeo de apartamento_id a colores
            var apartmentColors = {
                1: '#EC7063', // Color para apartamento_id 1
                2: '#C39BD3', // Color para apartamento_id 2
                3: '#7FB3D5', // Color para apartamento_id 3
                4: '#76D7C4', // Color para apartamento_id 3
                5: '#F7DC6F', // Color para apartamento_id 3
                6: '#BFC9CA', // Color para apartamento_id 3
                7: '#D733FF', // Color para apartamento_id 3
                // ... más mapeos de colores para diferentes IDs de apartamento
            };
          var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            events: function(fetchInfo, successCallback, failureCallback) {
                fetch('/get-reservas')
                    .then(response => response.json())
                    .then(data => {
                    var events = data.map(function(reserva) {
                        return {
                        title: reserva.cliente.alias, // o cualquier otro campo que quieras usar como título
                        start: reserva.fecha_entrada,
                        end: reserva.fecha_salida,
                        backgroundColor: apartmentColors[reserva.apartamento_id] || '#378006', // Color por defecto si no se encuentra un mapeo

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
                    {{-- 1: '#EC7063', // Color para apartamento_id 1
                    2: '#C39BD3', // Color para apartamento_id 2
                    3: '#7FB3D5', // Color para apartamento_id 3
                    4: '#76D7C4', // Color para apartamento_id 3
                    5: '#F7DC6F', // Color para apartamento_id 3
                    6: '#BFC9CA', // Color para apartamento_id 3
                    7: '#D733FF', --}}
                    <div class="apartamentos my-2">
                        <div class="d-inline px-2" style="background-color: #EC7063; color:white">
                            Bajo A
                        </div>
                        <div class="d-inline px-2" style="background-color: #C39BD3; color:white">
                            Bajo B
                        </div>
                        <div class="d-inline px-2" style="background-color: #7FB3D5; color:white">
                            Primero A
                        </div>
                        <div class="d-inline px-2" style="background-color: #76D7C4; color:white">
                            Primero B
                        </div>
                        <div class="d-inline px-2" style="background-color: #F7DC6F; color:white">
                            Segundo A
                        </div>
                        <div class="d-inline px-2" style="background-color: #BFC9CA; color:white">
                            Segundo B
                        </div>
                        <div class="d-inline px-2" style="background-color: #D733FF; color:white">
                            Atico
                        </div>
                    </div>
                    <div id='calendar'></div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
