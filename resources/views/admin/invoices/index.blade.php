@extends('layouts.appAdmin')

@section('scriptHead')
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.9/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.9/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.9/locales/es.js'></script>
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
                <div class="col-sm-12 col-md-6">
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
                <div class="col-sm-12 col-md-6">
                    <a href="#"
                       id="generate-zip-button"
                       class="btn bg-color-primero"
                       onclick="generateZip(event)">
                        Descargar Facturas en ZIP
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
                            'order_by' => 'base',
                            'direction' => (request()->get('order_by') == 'base' ? $orderDirection : 'asc'),
                            'search' => request()->get('search'),
                            'perPage' => request()->get('perPage'),
                            'fecha_inicio' => request()->get('fecha_inicio'),
                            'fecha_fin' => request()->get('fecha_fin')
                          ]) }}" class="{{ request('order_by') == 'base' ? 'active-sort' : 'inactive-sort' }}">
                              Base Imponible
                              @if(request('order_by') == 'base')
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
                            'order_by' => 'iva',
                            'direction' => (request()->get('order_by') == 'iva' ? $orderDirection : 'asc'),
                            'search' => request()->get('search'),
                            'perPage' => request()->get('perPage'),
                            'fecha_inicio' => request()->get('fecha_inicio'),
                            'fecha_fin' => request()->get('fecha_fin')
                          ]) }}" class="{{ request('order_by') == 'iva' ? 'active-sort' : 'inactive-sort' }}">
                              IVA
                              @if(request('order_by') == 'iva')
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
                            <td>
                                @php
                                    $nombre = trim($factura->cliente->nombre ?? '');
                                    $apellido1 = trim($factura->cliente->apellido1 ?? '');
                                    $apellido2 = trim($factura->cliente->apellido2 ?? '');

                                    if (!empty($nombre) || !empty($apellido1)) {
                                        echo trim($nombre . ' ' . $apellido1 . ' ' . $apellido2);
                                    } else {
                                        echo $factura->cliente->alias ?? 'Sin información';
                                    }
                                @endphp
                            </td>
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
                            <td><strong>{{ number_format($factura->total, 2, ',', '.') }} €</strong></td>
                            <td>{{ number_format($factura->base ?? 0, 2, ',', '.') }} €</td>
                            <td>{{ number_format($factura->iva ?? 0, 2, ',', '.') }} €</td>
                            <td>{{ $factura->estado->name }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{route('admin.facturas.generatePdf', $factura->id)}}" class="btn btn-sm bg-color-segundo" title="Descargar PDF">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    @if($factura->reserva_id || $factura->budget_id)
                                        <button type="button" 
                                                class="btn btn-sm btn-info recalcular-factura" 
                                                data-invoice-id="{{ $factura->id }}"
                                                title="Recalcular IVA desde precio de reserva o presupuesto">
                                            <i class="fas fa-calculator"></i>
                                        </button>
                                    @endif
                                    <button type="button"
                                            class="btn btn-sm btn-secondary actualizar-fecha-referencia"
                                            data-invoice-id="{{ $factura->id }}"
                                            data-fecha-actual="{{ \Carbon\Carbon::parse($factura->fecha)->format('Y-m-d') }}"
                                            data-referencia-actual="{{ $factura->reference }}"
                                            title="Cambiar fecha de facturación">
                                        <i class="fas fa-calendar-alt"></i>
                                    </button>
                                    @if(!$factura->es_rectificativa && !$factura->tieneRectificativas())
                                        <a href="{{route('admin.facturas.createRectificativa', $factura->id)}}" class="btn btn-sm btn-warning" title="Crear Factura Rectificativa">
                                            <i class="fas fa-undo"></i>
                                        </a>
                                    @elseif($factura->tieneRectificativas())
                                        <a href="{{route('admin.facturas.showRectificativas', $factura->id)}}" class="btn btn-sm btn-info" title="Ver Rectificativas">
                                            <i class="fas fa-list"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
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

function generateZip(event) {
    event.preventDefault();

    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;

    if (!fechaInicio || !fechaFin) {
        alert('Por favor selecciona un rango de fechas válido.');
        return;
    }

    // Redirigir a la ruta con los parámetros de fecha
    const url = `/admin/facturas/download-zip?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
    window.location.href = url;
}
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

                fetch(`/facturas/update-fecha/${id}`, {
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

        // Manejar el recálculo de facturas
        document.querySelectorAll('.recalcular-factura').forEach(button => {
            button.addEventListener('click', function() {
                const invoiceId = this.dataset.invoiceId;
                const button = this;
                
                // Confirmar acción
                if (!confirm('¿Estás seguro de que deseas recalcular esta factura? Esto actualizará la base, IVA y total desde el precio de la reserva o presupuesto.')) {
                    return;
                }

                // Deshabilitar botón mientras se procesa
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                // Crear un AbortController para poder cancelar la petición si tarda mucho
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 segundos de timeout

                fetch(`/facturas/${invoiceId}/recalcular`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    signal: controller.signal
                })
                .then(response => {
                    clearTimeout(timeoutId);
                    // Verificar el status HTTP antes de parsear JSON
                    if (!response.ok) {
                        // Intentar parsear el JSON de error si existe
                        return response.json().then(errorData => {
                            throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                        }).catch(() => {
                            // Si no se puede parsear, lanzar error genérico
                            throw new Error(`HTTP error! status: ${response.status}`);
                        });
                    }
                    // Si todo está bien, parsear el JSON
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        let mensaje = '';
                        
                        // Si solo se actualizó la referencia (rectificativa o tiene rectificativas)
                        if (data.data.solo_referencia) {
                            mensaje = data.message;
                            if (data.data.referencia_generada) {
                                mensaje += `\n\n✅ Referencia asignada: ${data.data.referencia_nueva}`;
                            }
                        } else {
                            // Recálculo completo de IVA
                            mensaje = 'Factura recalculada correctamente.\n\n' +
                                  'Valores anteriores:\n' +
                                  `Base: ${data.data.valores_antiguos.base} €\n` +
                                  `IVA: ${data.data.valores_antiguos.iva} €\n` +
                                  `Total: ${data.data.valores_antiguos.total} €\n\n` +
                                  'Valores nuevos:\n' +
                                  `Base: ${data.data.valores_nuevos.base} €\n` +
                                  `IVA: ${data.data.valores_nuevos.iva} €\n` +
                                  `Total: ${data.data.valores_nuevos.total} €\n\n` +
                                  `Precio ${data.data.tipo_origen === 'reserva' ? 'de reserva' : 'del presupuesto'}: ${data.data.precio_origen} €`;
                            
                            if (data.data.referencia_generada) {
                                mensaje += `\n\n✅ Se ha asignado la referencia: ${data.data.referencia_nueva}`;
                            }
                        }
                        
                        alert(mensaje);
                        // Recargar la página para ver los cambios
                        location.reload();
                    } else {
                        alert('Error al recalcular la factura: ' + (data.message || 'Error desconocido'));
                        button.disabled = false;
                        button.innerHTML = '<i class="fas fa-calculator"></i>';
                    }
                })
                .catch(error => {
                    clearTimeout(timeoutId);
                    console.error('Error:', error);
                    
                    let errorMessage = 'Error en la conexión al recalcular la factura.';
                    if (error.name === 'AbortError') {
                        errorMessage = 'La operación tardó demasiado tiempo. Por favor, intenta de nuevo.';
                    } else if (error.message.includes('504')) {
                        errorMessage = 'El servidor tardó demasiado en responder. Por favor, intenta de nuevo o contacta con el administrador.';
                    }
                    
                    alert(errorMessage);
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-calculator"></i>';
                });
            });
        });

        // Manejar el cambio de fecha (y opcionalmente referencia) de una factura
        document.querySelectorAll('.actualizar-fecha-referencia').forEach(button => {
            button.addEventListener('click', function() {
                const invoiceId = this.dataset.invoiceId;
                const fechaActual = this.dataset.fechaActual;
                const referenciaActual = this.dataset.referenciaActual;
                const btn = this;

                // Paso 1: elegir qué cambiar
                Swal.fire({
                    title: 'Cambiar fecha de facturación',
                    html:
                        `<div style="text-align:left; font-size:14px; margin-bottom:12px;">` +
                            `<div><strong>Fecha actual:</strong> ${fechaActual}</div>` +
                            `<div><strong>Referencia actual:</strong> ${referenciaActual}</div>` +
                        `</div>` +
                        `<div style="margin-top:8px;">¿Qué quieres cambiar?</div>`,
                    icon: 'question',
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonText: 'Solo fecha',
                    denyButtonText: 'Fecha + referencia',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#6C757D',
                    denyButtonColor: '#0d6efd',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        pedirSoloFecha(invoiceId, fechaActual, btn);
                    } else if (result.isDenied) {
                        pedirFechaYReferencia(invoiceId, fechaActual, referenciaActual, btn);
                    }
                });
            });
        });

        // Paso 2a: solo fecha
        function pedirSoloFecha(invoiceId, fechaActual, btn) {
            Swal.fire({
                title: 'Nueva fecha de facturación',
                html:
                    `<input type="date" id="swal-fecha" class="swal2-input" value="${fechaActual}" style="width:80%;">`,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const fecha = document.getElementById('swal-fecha').value;
                    if (!fecha) {
                        Swal.showValidationMessage('Selecciona una fecha');
                        return false;
                    }
                    return { fecha };
                },
            }).then((result) => {
                if (!result.isConfirmed) return;
                enviarCambio({
                    url: `/facturas/update-fecha/${invoiceId}`,
                    body: { fecha: result.value.fecha },
                    btn,
                    modo: 'fecha',
                    // El endpoint legacy /update-fecha/{id} sólo devuelve success/message,
                    // así que rellenamos nosotros el resumen con lo que sabemos.
                    fallback: {
                        fecha_anterior: fechaActual,
                        nueva_fecha: result.value.fecha,
                    },
                });
            });
        }

        // Paso 2b: fecha + referencia manual
        function pedirFechaYReferencia(invoiceId, fechaActual, referenciaActual, btn) {
            Swal.fire({
                title: 'Nueva fecha y referencia',
                html:
                    `<div style="text-align:left; margin-bottom:6px;"><label for="swal-fecha" style="font-size:13px; font-weight:600;">Fecha de facturación</label></div>` +
                    `<input type="date" id="swal-fecha" class="swal2-input" value="${fechaActual}" style="width:80%; margin:0 0 14px;">` +
                    `<div style="text-align:left; margin-bottom:6px;"><label for="swal-referencia" style="font-size:13px; font-weight:600;">Referencia</label></div>` +
                    `<input type="text" id="swal-referencia" class="swal2-input" value="${referenciaActual ?? ''}" placeholder="Ej: 2025/01/000406" style="width:80%; margin:0;">`,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const fecha = document.getElementById('swal-fecha').value;
                    const reference = document.getElementById('swal-referencia').value.trim();
                    if (!fecha) {
                        Swal.showValidationMessage('Selecciona una fecha');
                        return false;
                    }
                    if (!reference) {
                        Swal.showValidationMessage('Introduce la referencia');
                        return false;
                    }
                    return { fecha, reference };
                },
            }).then((result) => {
                if (!result.isConfirmed) return;
                enviarCambio({
                    url: `/facturas/${invoiceId}/update-fecha-referencia-manual`,
                    body: { fecha: result.value.fecha, reference: result.value.reference },
                    btn,
                    modo: 'fecha-referencia',
                    fallback: {
                        fecha_anterior: fechaActual,
                        nueva_fecha: result.value.fecha,
                        referencia_anterior: referenciaActual,
                        nueva_referencia: result.value.reference,
                    },
                });
            });
        }

        // POST al backend y manejo de respuesta
        function enviarCambio({ url, body, btn, modo, fallback = {} }) {
            btn.disabled = true;
            const iconoOriginal = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 30000);

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(body),
                signal: controller.signal,
            })
            .then(async (response) => {
                clearTimeout(timeoutId);
                let data = null;
                try { data = await response.json(); } catch (e) { /* noop */ }
                if (!response.ok) {
                    const msg = (data && data.message) ? data.message : `HTTP error ${response.status}`;
                    throw new Error(msg);
                }
                return data;
            })
            .then((data) => {
                if (data && data.success) {
                    // Preferimos lo que devuelva el backend; si falta algo, usamos el fallback del frontend.
                    const d = Object.assign({}, fallback, data.data || {});
                    let htmlResumen = `<div style="text-align:left; font-size:14px;">` +
                        `<div><strong>Fecha anterior:</strong> ${d.fecha_anterior ?? '-'}</div>` +
                        `<div><strong>Nueva fecha:</strong> ${d.nueva_fecha ?? '-'}</div>`;
                    if (modo === 'fecha-referencia') {
                        htmlResumen +=
                            `<div style="margin-top:8px;"><strong>Referencia anterior:</strong> ${d.referencia_anterior ?? '-'}</div>` +
                            `<div><strong>Nueva referencia:</strong> ${d.nueva_referencia ?? '-'}</div>`;
                    }
                    htmlResumen += `</div>`;

                    Swal.fire({
                        title: '¡Actualizado!',
                        html: htmlResumen,
                        icon: 'success',
                        timer: 2200,
                        showConfirmButton: false,
                    }).then(() => location.reload());
                } else {
                    throw new Error((data && data.message) || 'Error desconocido');
                }
            })
            .catch((error) => {
                clearTimeout(timeoutId);
                console.error('Error:', error);
                let msg = error.message || 'Error en la conexión.';
                if (error.name === 'AbortError') {
                    msg = 'La operación tardó demasiado. Inténtalo de nuevo.';
                }
                Swal.fire({
                    title: 'Error',
                    text: msg,
                    icon: 'error',
                });
                btn.disabled = false;
                btn.innerHTML = iconoOriginal || '<i class="fas fa-calendar-alt"></i>';
            });
        }
    });
</script>
@endsection
