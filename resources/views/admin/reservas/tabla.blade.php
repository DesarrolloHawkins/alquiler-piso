@extends('layouts.appAdmin')

@section('content')

<style>
    .inactive-sort {
        color: #0F1739;
        text-decoration: none;
    }
    .active-sort {
        color: #757191;
    }
    body { font-family: Arial, sans-serif; }
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background-color: #f2f2f2; }
    .header { background-color: #0f1739; color: white; padding: 20px 20px; margin-bottom: 1rem }
    .fondo_verde {
        background-color: #4CAF50 !important; /* Color verde para el día de hoy */
        color: white;
    }
    .fondo_naranja {
        background-color: #FFA500 !important; /* Color naranja para fechas pasadas */
        color: white;
    }
    .fondo_celeste {
        background-color: #00CFFF !important; /* Color celeste para fechas futuras */
        color: white;
    }
    /* Hacer sticky la primera columna (Apartamentos) */
    .apartments-column {
        white-space: nowrap;
        width: auto;
        position: sticky;
        left: 0;
        z-index: 10;
        background-color: white;
    }

    /* CSS para celdas con división diagonal */
    .diagonal-cell {
        position: relative;
        height: 100%;
        padding: 0;
        background-color: white;
        overflow: hidden;
    }

    .diagonal-cell::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: white;
        clip-path: polygon(0 0, 100% 0, 0 100%);
    }

    .diagonal-cell-content {
        position: absolute;
        width: 50%;
        height: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        padding: 5px;
    }

    .diagonal-cell-content-top-left {
        top: 0;
        left: 0;
        background-color: #FFA500; /* Naranja para fechas pasadas */
        color: white;
    }

    .diagonal-cell-content-bottom-right {
        bottom: 0;
        right: 0;
        background-color: #00CFFF; /* Celeste para fechas futuras */
        color: white;
    }
</style>

<div class="container-fluid">
    <div class="d-flex flex-colum mb-3">
        <h2 class="mb-0 me-3 encabezado_top">{{ __('Tabla de Reservas') }}</h2>
    </div>
    <hr class="mb-5">
    <div class="row justify-content-center">
        <div class="header d-flex justify-content-between align-items-center">
            <a href="{{ route('admin.tablaReservas.index', ['date' => \Carbon\Carbon::createFromFormat('Y-m', $date)->subMonth()->format('Y-m')]) }}" class="btn bg-color-quinto">Mes Anterior</a>
            <h3>{{ $monthName }}</h3>
            <a href="{{ route('admin.tablaReservas.index', ['date' => \Carbon\Carbon::createFromFormat('Y-m', $date)->addMonth()->format('Y-m')]) }}" class="btn bg-color-quinto">Mes Siguiente</a>
        </div>

        @if ($apartamentos)
        <!-- Contenedor con scroll horizontal -->
        <div class="table-responsive p-0">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="apartments-column">Apartamentos</th>
                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $fechaHoy = \Carbon\Carbon::now(); // Obtener la fecha actual
                                $claseDiaHoy = $day == $fechaHoy->day ? 'fondo_verde' : ''; // Agregar la clase si es el día de hoy
                            @endphp
                            <th class="{{ $claseDiaHoy }}">{{ $day }}</th> <!-- Encabezado del día -->
                        @endfor
                    </tr>
                </thead>
                <tbody>
                  @foreach ($apartamentos as $apartamento)
                  <tr>
                      <td class="apartments-column">{{ $apartamento->titulo }}</td>

                      @for ($day = 1; $day <= $daysInMonth; $day++)
                          @php
                              $found = false;
                              $claseDiaHoy = $day == $fechaHoy->day ? 'fondo_verde' : ''; // Aplicar la clase en las celdas de hoy
                              $reservaSalida = null; // Para la reserva que sale ese día
                              $reservaEntrada = null; // Para la reserva que entra ese día
                          @endphp

                          {{-- Buscar si hay una reserva que coincida con este día --}}
                          @foreach ($apartamento->reservas as $itemReserva)
                              @php
                                  $fechaEntrada = \Carbon\Carbon::parse($itemReserva->fecha_entrada);
                                  $fechaSalida = \Carbon\Carbon::parse($itemReserva->fecha_salida);
                              @endphp

                              {{-- Caso 1: Día coincide con la fecha de salida de una reserva --}}
                              @if ($day == $fechaSalida->day)
                                  @php
                                      $reservaSalida = $itemReserva;
                                      $found = true;
                                  @endphp
                              @endif

                              {{-- Caso 2: Día coincide con la fecha de entrada de otra reserva --}}
                              @if ($day == $fechaEntrada->day)
                                  @php
                                      $reservaEntrada = $itemReserva;
                                      $found = true;
                                  @endphp
                              @endif
                          @endforeach

                          {{-- Si hay coincidencia de entrada y salida el mismo día, se muestra la celda dividida --}}
                          @if ($reservaSalida && $reservaEntrada)
                              <td class="diagonal-cell {{ $claseDiaHoy }}">
                                  <div class="diagonal-cell-content diagonal-cell-content-top-left">
                                      {{ $reservaSalida->cliente->nombre }}
                                  </div>
                                  <div class="diagonal-cell-content diagonal-cell-content-bottom-right">
                                      {{ $reservaEntrada->cliente->nombre }}
                                  </div>
                              </td>
                          @elseif ($reservaEntrada)
                              {{-- Caso normal de reserva de entrada --}}
                              <td class="p-0 {{ $claseDiaHoy }}">
                                  <div class="w-100 d-flex justify-content-between align-items-center">
                                      @php
                                          $claseBoton = ($fechaEntrada->isToday()) ? 'btn-success fondo_verde' : ($fechaEntrada->isPast() ? 'btn-warning fondo_naranja' : 'btn-info fondo_celeste');
                                      @endphp
                                      <button type="button" class="w-100 rounded-0 btn {{ $claseBoton }}" data-bs-toggle="modal" data-bs-target="#modalReserva{{ $reservaEntrada->id }}">
                                          {{ $reservaEntrada->fecha_entrada->format('d') }} - {{ $reservaEntrada->fecha_salida->format('d') }}
                                      </button>
                                  </div>
                              </td>
                          @elseif ($reservaSalida)
                              {{-- Caso normal de reserva de salida --}}
                              <td class="p-0 {{ $claseDiaHoy }}">
                                  <div class="w-100 d-flex justify-content-between align-items-center">
                                      @php
                                          $claseBoton = ($fechaSalida->isToday()) ? 'btn-success fondo_verde' : ($fechaSalida->isPast() ? 'btn-warning fondo_naranja' : 'btn-info fondo_celeste');
                                      @endphp
                                      <button type="button" class="w-100 rounded-0 btn {{ $claseBoton }}" data-bs-toggle="modal" data-bs-target="#modalReserva{{ $reservaSalida->id }}">
                                          {{ $reservaSalida->fecha_entrada->format('d') }} - {{ $reservaSalida->fecha_salida->format('d') }}
                                      </button>
                                  </div>
                              </td>
                          @else
                              {{-- Celda vacía --}}
                              <td data-apartamento="{{ $apartamento->id }}" data-dia="{{ $day }}" class="{{ $claseDiaHoy }}" data-bs-toggle="modal" data-bs-target="#modalCrearReserva" onclick="openCrearReservaModal({{ $apartamento->id }}, {{ $day }})"></td>
                          @endif
                      @endfor
                  </tr>
                  @endforeach
                </tbody>
            </table>
        </div>

        {{-- Modal para crear nueva reserva --}}
        <div class="modal fade" id="modalCrearReserva" tabindex="-1" aria-labelledby="modalCrearReservaLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCrearReservaLabel">Crear Nueva Reserva</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formCrearReserva" method="POST" action="{{ route('reservas.store') }}">
                            @csrf
                            <input type="hidden" id="apartamentoId" name="apartamento_id" value="">
                            <input type="hidden" id="fechaReserva" name="fecha_reserva" value="">

                            <div class="mb-3">
                                <label for="cliente" class="form-label">Cliente</label>
                                <input type="text" class="form-control" id="cliente" name="cliente" required>
                            </div>

                            <div class="mb-3">
                                <label for="fecha_entrada" class="form-label">Fecha de Entrada</label>
                                <input type="date" class="form-control" id="fecha_entrada" name="fecha_entrada" required>
                            </div>

                            <div class="mb-3">
                                <label for="fecha_salida" class="form-label">Fecha de Salida</label>
                                <input type="date" class="form-control" id="fecha_salida" name="fecha_salida" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Crear Reserva</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modales para cada reserva --}}
        @foreach ($apartamentos as $apartamento)
            @foreach ($apartamento->reservas as $itemReserva)
              <!-- Modal para visualizar reserva existente -->
              <div class="modal fade" id="modalReserva{{ $itemReserva->id }}" tabindex="-1" aria-labelledby="modalReserva{{ $itemReserva->id }}" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="modalLabel{{ $itemReserva->id }}">Detalles de la Reserva</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <p><strong>Cliente:</strong> {{ $itemReserva->cliente->nombre }}</p>
                      <p><strong>Fecha de Entrada:</strong> {{ $itemReserva->fecha_entrada->format('d/m/Y') }}</p>
                      <p><strong>Fecha de Salida:</strong> {{ $itemReserva->fecha_salida->format('d/m/Y') }}</p>
                      <p><strong>Detalles adicionales:</strong> {{ $itemReserva->detalles }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
        @endforeach
      @endif
    </div>
</div>

<script>
    const reservas = @json($apartamentos);
    console.log(reservas);

    // Función para abrir el modal de crear reserva con los datos de apartamento y día
    function openCrearReservaModal(apartamentoId, dia) {
        const year = {{ \Carbon\Carbon::createFromFormat('Y-m', $date)->year }};
        const month = {{ \Carbon\Carbon::createFromFormat('Y-m', $date)->month }};
        
        // Generar la fecha completa
        const fechaCompleta = `${year}-${String(month).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
        
        // Setear los valores en los inputs ocultos del formulario
        document.getElementById('apartamentoId').value = apartamentoId;
        document.getElementById('fechaReserva').value = fechaCompleta;
        document.getElementById('fecha_entrada').value = fechaCompleta;

        // Abrir el modal
        $('#modalCrearReserva').modal('show');
    }
</script>

@endsection
