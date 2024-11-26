@extends('layouts.appAdmin')

@section('scriptHead')
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.9/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.9/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@3.10.2/dist/locale/es.js'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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
        <h2 class="mb-0 me-3 encabezado_top">{{ __('Facturas') }}</h2>
    </div>
    <hr class="mb-3">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <h6 class="text-uppercase"><i class="fa-solid fa-filter me-1"></i> Filtros</h6>

            <div class="row mb-4 align-items-end">
                <div class="col-md-2">
                    <div class="mb-3">
                        <form action="{{ route('admin.facturas.index') }}" method="GET">
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
                        <form action="{{ route('admin.facturas.index') }}" method="GET">
                            <input type="hidden" name="order_by" value="{{ request()->get('order_by', 'fecha') }}">
                            <input type="hidden" name="direction" value="{{ request()->get('direction', 'asc') }}">
                            <input type="hidden" name="perPage" value="{{ request()->get('perPage') }}">

                            <div class="d-flex align-items-center">
                                <input type="text" class="form-control me-2" id="search" name="search" placeholder="Buscar..." value="{{ request()->get('search') }}">

                                <!-- Fecha de inicio -->
                                <div class="input-group me-2">
                                    <label class="input-group-text" for="fecha_inicio" id="label_fecha_inicio">Fecha Inicio</label>
                                    <input type="text" class="form-control" id="fecha_inicio" name="fecha_inicio" value="{{ request()->get('fecha_inicio') }}">
                                </div>

                                <!-- Fecha de fin -->
                                <div class="input-group me-2">
                                    <label class="input-group-text" for="fecha_fin" id="label_fecha_fin">Fecha Fin</label>
                                    <input type="text" class="form-control" id="fecha_fin" name="fecha_fin" value="{{ request()->get('fecha_fin') }}">
                                </div>

                                <!-- Botones -->
                                <button type="button" class="btn bg-color-segundo me-2"><i class="fa-solid fa-trash"></i></button>
                                <button type="submit" class="btn bg-color-primero">Buscar</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-12">
                    <a href="{{ route('admin.facturas.export', [
                            'order_by' => request()->get('order_by'),
                            'direction' => request()->get('direction'),
                            'search' => request()->get('search'),
                            'perPage' => request()->get('perPage'),
                            'fecha_inicio' => request()->get('fecha_inicio'),
                            'fecha_fin' => request()->get('fecha_fin')
                        ]) }}" class="btn bg-color-primero">
                        Exportar a Excel
                    </a>
                </div>
            </div>
            @php
              $orderDirection = request()->get('direction', 'asc') == 'asc' ? 'desc' : 'asc';
            @endphp
            <table class="table table-striped table-hover">
              <thead>
                  <tr class="bg-color-primero-table">
                      <th scope="col">
                          <a href="{{ route('admin.facturas.index', [
                                'order_by' => 'reference',
                                'direction' => (request()->get('order_by') == 'reference' ? $orderDirection : 'asc'),
                                'search' => request()->get('search'),
                                'perPage' => request()->get('perPage'),
                                'fecha_inicio' => request()->get('fecha_inicio'),
                                'fecha_fin' => request()->get('fecha_fin')
                            ]) }}"
                            class="{{ request('order_by') == 'reference' ? 'active-sort' : 'inactive-sort' }}">
                                Referencia
                              @if(request()->get('order_by') == 'reference')
                                  @if(request()->get('direction') == 'asc')
                                      &#9650; {{-- Icono de flecha hacia arriba --}}
                                  @else
                                      &#9660; {{-- Icono de flecha hacia abajo --}}
                                  @endif
                              @endif
                          </a>
                      </th>

                      <th scope="col">
                          <a href="{{ route('admin.facturas.index', [
                            'order_by' => 'cliente_id',
                            'direction' => (request()->get('order_by') == 'cliente_id' ? $orderDirection : 'asc'),
                            'search' => request()->get('search'),
                            'perPage' => request()->get('perPage'),
                            'fecha_inicio' => request()->get('fecha_inicio'),
                            'fecha_fin' => request()->get('fecha_fin')
                          ]) }}"
                          class="{{ request('order_by') == 'cliente_id' ? 'active-sort' : 'inactive-sort' }}">
                              Cliente
                              @if(request()->get('order_by') == 'cliente_id')
                                  @if(request()->get('direction') == 'asc')
                                      &#9650;
                                  @else
                                      &#9660;
                                  @endif
                              @endif
                          </a>
                      </th>

                      <th scope="col">Número de Identificación</th> <!-- Nueva columna -->

                      <th scope="col">
                          <a href="{{ route('admin.facturas.index', [
                            'order_by' => 'concepto',
                            'direction' => (request()->get('order_by') == 'concepto' ? $orderDirection : 'asc'),
                            'search' => request()->get('search'),
                            'perPage' => request()->get('perPage'),
                            'fecha_inicio' => request()->get('fecha_inicio'),
                            'fecha_fin' => request()->get('fecha_fin')
                            ]) }}"
                          class="{{ request('order_by') == 'concepto' ? 'active-sort' : 'inactive-sort' }}">
                              Concepto
                              @if(request('order_by') == 'concepto')
                                  @if(request('direction') == 'asc')
                                      &#9650;
                                  @else
                                      &#9660;
                                  @endif
                              @endif
                          </a>
                      </th>

                      <th scope="col">Fecha de Entrada</th> <!-- Nueva columna -->
                      <th scope="col">Fecha de Salida</th> <!-- Nueva columna -->

                      <th scope="col">
                          <a href="{{ route('admin.facturas.index', [
                            'order_by' => 'fecha',
                            'direction' => (request()->get('order_by') == 'fecha' ? $orderDirection : 'asc'),
                            'search' => request()->get('search'),
                            'perPage' => request()->get('perPage'),
                            'fecha_inicio' => request()->get('fecha_inicio'),
                            'fecha_fin' => request()->get('fecha_fin')
                            ]) }}"
                          class="{{ request('order_by') == 'fecha' ? 'active-sort' : 'inactive-sort' }}">
                              Fecha de Facturación
                              @if(request()->get('order_by') == 'fecha')
                                  @if(request()->get('direction') == 'asc')
                                      &#9650;
                                  @else
                                      &#9660;
                                  @endif
                              @endif
                          </a>
                      </th>
                      <th scope="col">
                        <a href="{{ route('admin.facturas.index', [
                            'order_by' => 'total',
                            'direction' => (request()->get('order_by') == 'total' ? $orderDirection : 'asc'),
                            'search' => request()->get('search'),
                            'perPage' => request()->get('perPage'),
                            'fecha_inicio' => request()->get('fecha_inicio'),
                            'fecha_fin' => request()->get('fecha_fin')                            ]) }}"
                        class="{{ request('order_by') == 'total' ? 'active-sort' : 'inactive-sort' }}">
                            Total
                            @if(request('order_by') == 'total')
                                @if(request('direction') == 'asc')
                                    &#9650;
                                @else
                                    &#9660;
                                @endif
                            @endif
                        </a>
                    </th>

                      <th scope="col">
                          <a href="{{ route('admin.facturas.index', [
                            'order_by' => 'invoice_status_id',
                            'direction' => (request()->get('order_by') == 'invoice_status_id' ? $orderDirection : 'asc'),
                            'search' => request()->get('search'),
                            'perPage' => request()->get('perPage'),
                            'fecha_inicio' => request()->get('fecha_inicio'),
                            'fecha_fin' => request()->get('fecha_fin')
                          ]) }}" class="{{ request('order_by') == 'invoice_status_id' ? 'active-sort' : 'inactive-sort' }}">
                              Estado
                              @if(request('order_by') == 'invoice_status_id')
                                  @if(request('direction') == 'asc')
                                      &#9650;
                                  @else
                                      &#9660;
                                  @endif
                              @endif
                          </a>
                      </th>
                      <th scope="col">Acción</th>
                  </tr>
              </thead>
              <tbody>
                  @foreach ($facturas as $factura)
                      <tr>
                            <th scope="row">{{ $factura->reference }}</th>
                            <td>{{ $factura->cliente->nombre == '' ? $factura->cliente->alias : $factura->cliente->nombre}}</td>
                            <td>{{ $factura->cliente->num_identificacion ?? 'N/A' }}</td> <!-- Mostrar Número de Identificación -->
                            <td>{{ $factura->concepto }}</td>
                            <td>{{ $factura->reserva->fecha_entrada ?? 'N/A' }}</td> <!-- Mostrar Fecha de Entrada -->
                            <td>{{ $factura->reserva->fecha_salida ?? 'N/A' }}</td> <!-- Mostrar Fecha de Salida -->
                            {{-- <td>{{ \Carbon\Carbon::parse($factura->created_at)->format('d/m/Y') }}</td> --}}
                            <td>
                                <span class="fecha-text" data-id="{{ $factura->id }}">
                                    {{ \Carbon\Carbon::parse($factura->fecha)->format('Y-m-d') }}
                                </span>
                                <input type="date" class="fecha-input d-none" data-id="{{ $factura->id }}" value="{{ \Carbon\Carbon::parse($factura->fecha)->format('Y-m-d') }}">
                            </td>
                            <td><strong>{{ $factura->total }} €</strong></td>
                            <td>{{ $factura->estado->name }}</td>
                            <td><a href="{{route('admin.facturas.generatePdf', $factura->id)}}" class="btn bg-color-segundo">Descargar PDF</a></td>
                      </tr>
                  @endforeach
              </tbody>
          </table>
          <h3 class="text-center"><strong>{{$sumatorio}} €</strong></h3>
          <!-- Paginación links -->
          {{ $facturas->appends(request()->except('page'))->links() }}

        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    flatpickr("#fecha_inicio", {
        dateFormat: "Y-m-d",
        locale: "es",
        onChange: function(selectedDates, dateStr, instance) {
            document.getElementById('fecha_inicio').value = dateStr;
        },
        onReady: function(selectedDates, dateStr, instance) {
            document.getElementById('label_fecha_inicio').addEventListener('click', function() {
                instance.open();
            });
        }
    });

    flatpickr("#fecha_fin", {
        dateFormat: "Y-m-d",
        locale: "es",
        onChange: function(selectedDates, dateStr, instance) {
            document.getElementById('fecha_fin').value = dateStr;
        },
        onReady: function(selectedDates, dateStr, instance) {
            document.getElementById('label_fecha_fin').addEventListener('click', function() {
                instance.open();
            });
        }
    });
});


document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = '{{ csrf_token() }}';

        // Mostrar el input al hacer clic en la fecha
        document.querySelectorAll('.fecha-text').forEach(span => {
            span.addEventListener('click', function () {
                const input = document.querySelector(`.fecha-input[data-id="${this.dataset.id}"]`);
                this.classList.add('d-none');
                input.classList.remove('d-none');
                input.focus();
            });
        });

        // Manejar el cambio de la fecha
        document.querySelectorAll('.fecha-input').forEach(input => {
            input.addEventListener('blur', function () {
                const id = this.dataset.id;
                const nuevaFecha = this.value;

                fetch(`/admin/facturas/update-fecha/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ fecha: nuevaFecha })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const span = document.querySelector(`.fecha-text[data-id="${id}"]`);
                        span.textContent = nuevaFecha;
                        span.classList.remove('d-none');
                        this.classList.add('d-none');
                        alert('Fecha actualizada correctamente.');
                    } else {
                        alert('Error al actualizar la fecha.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error en la conexión.');
                });
            });
        });
    });
</script>
@endsection
