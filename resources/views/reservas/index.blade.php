@extends('layouts.appAdmin')
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
<!-- Incluir el CSS de Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- Incluir Flatpickr y la localización en español -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<style>
    .inactive-sort {
        color: #ffffff;
        text-decoration: none;
    }
    .active-sort {
        color: #ffa3fa;
        font-weight: bold;
        text-decoration: none;
    }
</style>
<div class="container-fluid">
    <div class="d-flex flex-colum mb-3">
        <h2 class="mb-0 me-3 encabezado_top">{{ __('Reservas') }}</h2>
        <a href="{{route('reservas.create')}}" class="btn bg-color-sexto text-uppercase">
            <i class="fa-solid fa-plus me-2"></i>
            Crear Reserva
        </a>
    </div>
    <hr class="mb-3">
    <div class="row justify-content-center">
        <div class="col-md-12">
                {{-- <div class="card-header">{{ __('Nuestros Clientes') }}</div> --}}
                @php
                    $orderDirection = request()->get('direction', 'asc') == 'asc' ? 'desc' : 'asc';
                @endphp
                @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif
                <h6 class="text-uppercase"><i class="fa-solid fa-filter me-1"></i> Filtros</h6>
                <div class="row mb-4 align-items-end">
                    <div class="col-md-2">
                        <div class="mb-3">
                            <form action="{{ route('reservas.index') }}" method="GET">
                                <div class="form-group">
                                        <!-- Otros parámetros como campos ocultos -->
                                    <input type="hidden" name="order_by" value="{{ request()->get('order_by') }}">
                                    <input type="hidden" name="direction" value="{{ request()->get('direction') }}">
                                    <input type="hidden" name="search" value="{{ request()->get('search') }}">

                                    <label for="perPage">Registros por página:</label>
                                    <select name="perPage" id="perPage" class="form-control" onchange="this.form.submit()">
                                        <option value="10" {{ request()->get('perPage') == 10 ? 'selected' : '' }}>10</option>
                                        <option value="20" {{ request()->get('perPage') == 20 ? 'selected' : '' }}>20</option>
                                        <option value="50" {{ request()->get('perPage') == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request()->get('perPage') == 100 ? 'selected' : '' }}>100</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div class="mb-3">
                            <form action="{{ route('reservas.index') }}" method="GET">
                                <!-- Campos ocultos para mantener el orden y la dirección -->
                                <input type="hidden" name="order_by" value="{{ request()->get('order_by', 'fecha_entrada') }}">
                                <input type="hidden" name="direction" value="{{ request()->get('direction', 'asc') }}">
                                <input type="hidden" name="perPage" value="{{ request()->get('perPage') }}">

                                <div class="d-flex align-items-center">
                                    <input type="text" class="form-control me-2" id="search" name="search" placeholder="Buscar..." value="{{ request()->get('search') }}">
                                    <div class="input-group me-2">
                                        <label class="input-group-text" for="filtro_apartamento">Apartamento</label>
                                        <select class="form-select min-width-apto" name="filtro_apartamento" id="filtro_apartamento" >
                                            <option value="">Todos</option>
                                            @foreach($apartamentos as $apartamento)
                                                <option value="{{ $apartamento->id }}"
                                                    {{ request()->get('filtro_apartamento') == $apartamento->id ? 'selected' : '' }}>
                                                    {{ $apartamento->titulo }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!-- Fecha de Entrada -->
                                    <div class="input-group me-2">
                                        <label class="input-group-text" for="fecha_entrada" id="label_fecha_entrada">Fecha de Inicio</label>
                                        <input type="text" class="form-control" id="fecha_entrada" name="fecha_entrada" value="{{ request()->get('fecha_entrada') }}">
                                    </div>

                                    <!-- Fecha de Salida -->
                                    <div class="input-group me-2">
                                        <label class="input-group-text" for="fecha_salida" id="label_fecha_salida">Fecha de Fin</label>
                                        <input type="text" class="form-control" id="fecha_salida" name="fecha_salida" value="{{ request()->get('fecha_salida') }}">
                                    </div>

                                    <!-- Botones -->
                                    <button type="button" id="limpiarFiltros" class="btn bg-color-segundo me-2"><i class="fa-solid fa-trash"></i></button>
                                    <button type="submit" class="btn bg-color-primero">Buscar</button>
                                </div>
                            </form>



                        </div>
                    </div>
                </div>

                <table class="table table-striped table-hover">
                    <thead>
                        <tr class="bg-color-primero-table">
                            <th scope="col">
                                <a href="{{ route('reservas.index', ['order_by' => 'id', 'direction' => (request()->get('order_by') == 'id' ? $orderDirection : 'asc'), 'search' => request()->get('search'),'perPage' => request()->get('perPage'), 'fecha_entrada' => request()->get('fecha_entrada'), 'fecha_salida' => request()->get('fecha_salida')]) }}" class="{{ request('order_by') == 'id' ? 'active-sort' : 'inactive-sort' }}">
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
                                <a href="{{ route('reservas.index', ['order_by' => 'apartamento_id', 'direction' => (request()->get('order_by') == 'apartamento_id' ? $orderDirection : 'asc'), 'search' => request()->get('search'),'perPage' => request()->get('perPage'), 'fecha_entrada' => request()->get('fecha_entrada'), 'fecha_salida' => request()->get('fecha_salida')]) }}" class="{{ request('order_by') == 'apartamento_id' ? 'active-sort' : 'inactive-sort' }}">
                                    Apartamento
                                    @if(request()->get('order_by') == 'apartamento_id')
                                        @if(request()->get('direction') == 'asc')
                                            &#9650; {{-- Icono de flecha hacia arriba --}}
                                        @else
                                            &#9660; {{-- Icono de flecha hacia abajo --}}
                                        @endif
                                    @endif
                                </a>
                            </th>
                            <th scope="col">
                                <a href="{{ route('reservas.index', ['order_by' => 'cliente_id', 'direction' => (request()->get('order_by') == 'cliente_id' ? $orderDirection : 'asc'), 'search' => request()->get('search'),'perPage' => request()->get('perPage'), 'fecha_entrada' => request()->get('fecha_entrada'), 'fecha_salida' => request()->get('fecha_salida')]) }}" class="{{ request('order_by') == 'cliente_id' ? 'active-sort' : 'inactive-sort' }}">
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
                                <a href="{{ route('reservas.index', ['order_by' => 'dni_entregado', 'direction' => (request()->get('order_by') == 'dni_entregado' ? $orderDirection : 'asc'), 'search' => request()->get('search'),'perPage' => request()->get('perPage'), 'fecha_entrada' => request()->get('fecha_entrada'), 'fecha_salida' => request()->get('fecha_salida')]) }}" class="{{ request('order_by') == 'dni_entregado' ? 'active-sort' : 'inactive-sort' }}">
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
                                <a href="{{ route('reservas.index', ['order_by' => 'fecha_entrada', 'direction' => (request()->get('order_by') == 'fecha_entrada' ? $orderDirection : 'asc'), 'search' => request()->get('search'),'perPage' => request()->get('perPage'), 'fecha_entrada' => request()->get('fecha_entrada'), 'fecha_salida' => request()->get('fecha_salida')]) }}" class="{{ request('order_by') == 'fecha_entrada' ? 'active-sort' : 'inactive-sort' }}">
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
                                <a href="{{ route('reservas.index', ['order_by' => 'fecha_salida', 'direction' => (request()->get('order_by') == 'fecha_salida' ? $orderDirection : 'asc'), 'search' => request()->get('search'),'perPage' => request()->get('perPage'), 'fecha_entrada' => request()->get('fecha_entrada'), 'fecha_salida' => request()->get('fecha_salida')]) }}" class="{{ request('order_by') == 'fecha_salida' ? 'active-sort' : 'inactive-sort' }}">
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
                                <a href="{{ route('reservas.index', ['order_by' => 'origen', 'direction' => (request()->get('order_by') == 'origen' ? $orderDirection : 'asc'), 'search' => request()->get('search'),'perPage' => request()->get('perPage'), 'fecha_entrada' => request()->get('fecha_entrada'), 'fecha_salida' => request()->get('fecha_salida')]) }}" class="{{ request('order_by') == 'origen' ? 'active-sort' : 'inactive-sort' }}">
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
                                <a href="{{ route('reservas.index', ['order_by' => 'codigo_reserva', 'direction' => (request()->get('order_by') == 'codigo_reserva' ? $orderDirection : 'asc'), 'search' => request()->get('search'),'perPage' => request()->get('perPage'), 'fecha_entrada' => request()->get('fecha_entrada'), 'fecha_salida' => request()->get('fecha_salida')]) }}" class="{{ request('order_by') == 'codigo_reserva' ? 'active-sort' : 'inactive-sort' }}">
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
                                Precio
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
                                <td>{{$reserva->apartamento->titulo}}</td>
                                <td>{{$reserva->cliente->alias}}</td>
                                <td>@if($reserva->dni_entregado == 1) <span class="badge text-bg-success">Entregado</span> @else <span class="badge text-bg-danger">No entregado</span>@endif</td>
                                <td>{{$reserva->fecha_entrada}}</td>
                                <td>{{$reserva->fecha_salida}}</td>
                                <td>{{$reserva->origen}}</td>
                                <td>{{$reserva->codigo_reserva}}</td>
                                <td>{{ number_format($reserva->precio, 2) }} €</td>
                                <td>
                                    <a href="{{route('reservas.show', $reserva->id)}}" class="btn bg-color-quinto">Ver Reserva</a>
                                    <a href="{{route('reservas.edit', $reserva->id)}}" class="btn bg-color-tercero">Editar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Paginación links -->
                {{ $reservas->appends(request()->except('page'))->links() }}

        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar Flatpickr en los campos de fecha con localización en español
        flatpickr("#fecha_entrada", {
            dateFormat: "Y-m-d",
            locale: "es", // Configurar el idioma español usando "es"
            onChange: function(selectedDates, dateStr, instance) {
                document.getElementById('fecha_entrada').value = dateStr; // Actualizar el valor del input
            },
            onReady: function(selectedDates, dateStr, instance) {
                document.getElementById('label_fecha_entrada').addEventListener('click', function() {
                    instance.open(); // Abrir calendario al hacer clic en la etiqueta
                });
            }
        });

        flatpickr("#fecha_salida", {
            dateFormat: "Y-m-d",
            locale: "es", // Configurar el idioma español usando "es"
            onChange: function(selectedDates, dateStr, instance) {
                document.getElementById('fecha_salida').value = dateStr; // Actualizar el valor del input
            },
            onReady: function(selectedDates, dateStr, instance) {
                document.getElementById('label_fecha_salida').addEventListener('click', function() {
                    instance.open(); // Abrir calendario al hacer clic en la etiqueta
                });
            }
        });
    });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('limpiarFiltros').addEventListener('click', function () {
                document.getElementById('search').value = '';
                document.getElementById('fecha_entrada').value = '';
                document.getElementById('fecha_salida').value = '';
                window.location.href = "{{ route('reservas.index') }}";
            });
        });
        </script>

@endsection
