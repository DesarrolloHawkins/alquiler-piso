@extends('layouts.app')
@section('scriptHead')
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.9/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.9/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/locale/es.js'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>

        document.addEventListener('DOMContentLoaded', function() {

            var calendarEl = document.getElementById('calendar');
            // Mapeo de apartamento_id a colores
            var apartmentColors = {
                1: ['#769ECB', 'white'], // Color para apartamento_id 1
                2: ['#9DBAD5', 'white'], // Color para apartamento_id 2
                3: ['#FAF3DD', 'black'], // Color para apartamento_id 3
                4: ['#C8D6B9', 'black'], // Color para apartamento_id 3
                5: ['#DFD8C0', 'black'], // Color para apartamento_id 3
                6: ['#8FC1A9', 'white'], // Color para apartamento_id 3
                7: ['#7CAA98', 'white'], // Color para apartamento_id 3
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
                            console.log(apartmentColors[reserva.apartamento_id][1])
                            return {
                            title: reserva.cliente.alias, // o cualquier otro campo que quieras usar como título
                            start: reserva.fecha_entrada,
                            end: reserva.fecha_salida,
                            backgroundColor: apartmentColors[reserva.apartamento_id][0] || '#378006', // Color por defecto si no se encuentra un mapeo
                            borderColor: apartmentColors[reserva.apartamento_id][0] || '#378006', // Color por defecto si no se encuentra un mapeo
                            textColor: apartmentColors[reserva.apartamento_id][1] || '#378006', // Color por defecto si no se encuentra un mapeo
                            ...reserva
                            // Aquí puedes añadir más propiedades según necesites
                            };
                        });
                        successCallback(events);
                    })
                    .catch(error => {
                        failureCallback(error);
                    });
            },
            eventClick: function(info) {
                // info.event contiene la información del evento clickeado
                var eventObj = info.event;
                console.log(eventObj);

                // Función para formatear la fecha en formato YYYY-MM-DD
                function formatDate(date) {
                    var d = new Date(date),
                        month = '' + (d.getMonth() + 1),
                        day = '' + d.getDate(),
                        year = d.getFullYear();

                    if (month.length < 2) month = '0' + month;
                    if (day.length < 2) day = '0' + day;

                    return [day, month, year].join('-');
                }

                // Llena la información del modal
                var modal = $('#eventModal');
                modal.find('.modal-body').html(''); // Limpia el contenido anterior
                // Agrega la información del evento al cuerpo del modal. Puedes personalizar esto como quieras.
                modal.find('.modal-body').append('<ul class="list-group">');
                modal.find('.modal-body').append('<li class="list-group-item"><strong>Cliente:</strong> ' + eventObj.title + '</li>');
                modal.find('.modal-body').append('<li class="list-group-item"><strong>Apartamento:</strong> ' + eventObj.extendedProps.apartamento.nombre + '</li>');
                modal.find('.modal-body').append('<li class="list-group-item"><strong>Codigo de la reserva:</strong> ' + eventObj.extendedProps.codigo_reserva + '</li>');
                modal.find('.modal-body').append('<li class="list-group-item"><strong>Fecha de Entrada:</strong> ' + formatDate(eventObj.start) + '</li>');
                modal.find('.modal-body').append('<li class="list-group-item"><strong>Fecha de Salida:</strong> ' + formatDate(eventObj.end) + '</li>');
                modal.find('.modal-body').append('<li class="list-group-item"><strong>Origen:</strong> ' + eventObj.extendedProps.origen + '</li>');
                modal.find('.modal-body').append('</ul>');
                // ... Agrega más campos como necesites

                // Muestra el modal
                modal.modal('show');
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
                @php
                    $orderDirection = request()->get('direction', 'asc') == 'asc' ? 'desc' : 'asc';
                @endphp
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th scope="col">
                                    <a href="{{ route('reservas.index', ['order_by' => 'id', 'direction' => (request()->get('order_by') == 'id' ? $orderDirection : 'asc')]) }}">
                                        ID
                                        @if(request()->get('order_by') == 'id')
                                            @if(request()->get('direction') == 'asc')
                                                &#9650; {{-- Icono de flecha hacia arriba --}}
                                            @else
                                                &#9660; {{-- Icono de flecha hacia abajo --}}
                                            @endif
                                        @endif
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="{{ route('reservas.index', ['order_by' => 'cliente_id', 'direction' => (request()->get('order_by') == 'cliente_id' ? $orderDirection : 'asc')]) }}">
                                        Nombre
                                        @if(request()->get('order_by') == 'cliente_id')
                                            @if(request()->get('direction') == 'asc')
                                                &#9650; {{-- Icono de flecha hacia arriba --}}
                                            @else
                                                &#9660; {{-- Icono de flecha hacia abajo --}}
                                            @endif
                                        @endif
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="{{ route('reservas.index', ['order_by' => 'dni_entregado', 'direction' => (request()->get('order_by') == 'dni_entregado' ? $orderDirection : 'asc')]) }}">
                                        DNI Entregado
                                        @if(request()->get('order_by') == 'dni_entregado')
                                            @if(request()->get('direction') == 'asc')
                                                &#9650; {{-- Icono de flecha hacia arriba --}}
                                            @else
                                                &#9660; {{-- Icono de flecha hacia abajo --}}
                                            @endif
                                        @endif
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="{{ route('reservas.index', ['order_by' => 'fecha_entrada', 'direction' => (request()->get('order_by') == 'fecha_entrada' ? $orderDirection : 'asc')]) }}">
                                        Fecha de Entrada
                                        @if(request()->get('order_by') == 'fecha_entrada')
                                            @if(request()->get('direction') == 'asc')
                                                &#9650; {{-- Icono de flecha hacia arriba --}}
                                            @else
                                                &#9660; {{-- Icono de flecha hacia abajo --}}
                                            @endif
                                        @endif
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="{{ route('reservas.index', ['order_by' => 'fecha_salida', 'direction' => (request()->get('order_by') == 'fecha_salida' ? $orderDirection : 'asc')]) }}">
                                        Fecha de Salida
                                        @if(request()->get('order_by') == 'fecha_salida')
                                            @if(request()->get('direction') == 'asc')
                                                &#9650; {{-- Icono de flecha hacia arriba --}}
                                            @else
                                                &#9660; {{-- Icono de flecha hacia abajo --}}
                                            @endif
                                        @endif
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="{{ route('reservas.index', ['order_by' => 'origen', 'direction' => (request()->get('order_by') == 'origen' ? $orderDirection : 'asc')]) }}">
                                        Origen
                                        @if(request()->get('order_by') == 'origen')
                                            @if(request()->get('direction') == 'asc')
                                                &#9650; {{-- Icono de flecha hacia arriba --}}
                                            @else
                                                &#9660; {{-- Icono de flecha hacia abajo --}}
                                            @endif
                                        @endif
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="{{ route('reservas.index', ['order_by' => 'codigo_reserva', 'direction' => (request()->get('order_by') == 'codigo_reserva' ? $orderDirection : 'asc')]) }}">
                                        Codigo de Reserva
                                        @if(request()->get('order_by') == 'codigo_reserva')
                                            @if(request()->get('direction') == 'asc')
                                                &#9650; {{-- Icono de flecha hacia arriba --}}
                                            @else
                                                &#9660; {{-- Icono de flecha hacia abajo --}}
                                            @endif
                                        @endif
                                    </a>
                                </th>
                                <th scope="col">
                                        Accion
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reservas as $reserva)
                                <tr>
                                    <th scope="row">{{$reserva->id}}</th>
                                    <td>{{$reserva->cliente->alias}}</td>
                                    <td>@if($reserva->dni_entregado == 1) <span class="badge text-bg-success">Entregado</span> @else <span class="badge text-bg-danger">No entregado</span>@endif</td>
                                    <td>{{$reserva->fecha_entrada}}</td>
                                    <td>{{$reserva->fecha_salida}}</td>
                                    <td>{{$reserva->origen}}</td>
                                    <td>{{$reserva->codigo_reserva}}</td>
                                    <td><a href="{{route('reservas.show', $reserva->id)}}" class="btn btn-primary">Ver</a></td>
                                </tr>                 
                            @endforeach
                        </tbody>
                    </table>
                    <!-- Paginación links -->
                    {{-- {!! $reservas->links('pagination::bootstrap-5') !!}                 --}}
                    {{ $reservas->appends(['order_by' => request()->get('order_by'), 'direction' => request()->get('direction')])->links() }}

                </div>
            </div>
        </div>
    </div>
</div>
  
@endsection
