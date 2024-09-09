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
      background-color: #def7df !important; /* Color de fondo verde para el día de hoy */
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

    /* Drag handles for resizing */
    .drag-right, .drag-left {
        width: 10px;
        height: 100%;
        position: absolute;
        top: 0;
        cursor: ew-resize;
        z-index: 5;
    }

    .drag-right {
        right: 0;
        background-color: rgba(0, 0, 0, 0.1);
    }

    .drag-left {
        left: 0;
        background-color: rgba(0, 0, 0, 0.1);
    }

    /* Establecer el scroll horizontal */
    .table-responsive {
        overflow-x: auto;
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
                          @endphp
                  
                          {{-- Buscar si hay una reserva que coincida con este día --}}
                          @foreach ($apartamento->reservas as $itemReserva)
                              @php
                                  // Obtener la fecha de la reserva en formato Carbon
                                  $fechaEntrada = \Carbon\Carbon::parse($itemReserva->fecha_entrada);
                                  $fechaSalida = \Carbon\Carbon::parse($itemReserva->fecha_salida);
                                  $diasDiferencia = $fechaEntrada->diffInDays($fechaSalida) + 1; // Asegurarse de incluir el último día
                              @endphp
                  
                              {{-- Si el día coincide con el inicio de la reserva --}}
                              @if ($day == $fechaEntrada->day)
                                  @php
                                      $claseBoton = ($fechaEntrada->isToday()) ? 'btn-success' : ($fechaEntrada->isPast() ? 'btn-warning' : 'btn-info');
                                  @endphp
                  
                                  {{-- Renderizar la celda con colspan para cubrir todo el rango de la reserva --}}
                                  <td colspan="{{ $diasDiferencia }}" class="p-0 {{ $claseDiaHoy }} position-relative">
                                      <div class="w-100 d-flex justify-content-between align-items-center">
                                          {{-- Botón con detalles de la reserva --}}
                                          <button type="button" class="w-100 rounded-0 btn {{ $claseBoton }}" data-bs-toggle="modal" data-bs-target="#modalReserva{{ $itemReserva->id }}">
                                              {{ $fechaEntrada->format('d') }} - {{ $fechaSalida->format('d') }}
                                          </button>
                  
                                          {{-- Drag handle para modificar la fecha de salida (lado derecho) --}}
                                          <div class="drag-right" data-reserva-id="{{ $itemReserva->id }}" draggable="true" ondragstart="startDrag(event, 'end')"></div>
                                          
                                          {{-- Drag handle para modificar la fecha de entrada (lado izquierdo) --}}
                                          <div class="drag-left" data-reserva-id="{{ $itemReserva->id }}" draggable="true" ondragstart="startDrag(event, 'start')"></div>
                                      </div>
                                  </td>
                  
                                  @php
                                      // Saltar los días cubiertos por el colspan
                                      $day += $diasDiferencia - 1; 
                                      $found = true;
                                  @endphp
                                  @break  {{-- Salir del bucle de reservas si ya encontramos una para este día --}}
                              @endif
                          @endforeach
                  
                          {{-- Si no se encontró ninguna reserva, agregar una celda vacía con funcionalidad de crear reserva --}}
                          @if (!$found)
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
let dragType = '';  // Para saber si estamos ajustando la fecha de entrada o de salida
let reservaId = ''; // Para saber qué reserva estamos ajustando
let originalDay = ''; // Día original de la fecha de salida o entrada
let dragging = false; // Variable para saber si estamos arrastrando

// Función que se ejecuta cuando comenzamos a arrastrar
function startDrag(event, type) {
    console.log('startDrag event:', event, 'type:', type); // Debug
    event.dataTransfer.effectAllowed = 'move';
    dragType = type;  // Puede ser 'start' para fecha de entrada o 'end' para fecha de salida
    reservaId = event.target.getAttribute('data-reserva-id');
    originalDay = event.target.closest('td').getAttribute('data-dia'); // Guardar el día original
    dragging = true; // Estamos arrastrando
    console.log('dragType:', dragType, 'reservaId:', reservaId, 'originalDay:', originalDay); // Debug
}

// Función para permitir el evento drop en los divs invisibles
function allowDrop(event) {
    event.preventDefault();
    console.log('allowDrop event:', event.target); // Debug para ver el elemento
}

// Función para manejar el evento drop cuando se suelta en un div invisible
function handleDrop(event, newDay) {
    event.preventDefault();
    console.log('handleDrop event:', event, 'newDay:', newDay); // Debug

    if (!dragging) return; // Verificamos si estamos arrastrando

    // Generar la fecha completa con año y mes
    const year = {{ \Carbon\Carbon::createFromFormat('Y-m', $date)->year }};
    const month = {{ \Carbon\Carbon::createFromFormat('Y-m', $date)->month }};
    const fechaCompleta = `${year}-${String(month).padStart(2, '0')}-${String(newDay).padStart(2, '0')}`;

    // Debug: Verificar la fecha antes de enviar
    console.log("Nueva fecha: ", fechaCompleta, "Reserva ID: ", reservaId, "Drag Type: ", dragType);

    // Verificar si estamos adelantando o reduciendo la fecha de salida
    if (dragType === 'end') {
        if (parseInt(newDay) < parseInt(originalDay)) {
            console.log("Estamos reduciendo la fecha de salida");
        } else {
            console.log("Estamos aumentando la fecha de salida");
        }
    } else if (dragType === 'start') {
        console.log("Cambiando la fecha de entrada");
    }

    // Hacer una llamada AJAX para actualizar la fecha de la reserva
    let url = `/reservas/update/${reservaId}`;
    let data = {
        '_token': '{{ csrf_token() }}',
        'reserva_id': reservaId,
        'new_date': fechaCompleta,  // Enviar la fecha completa (año-mes-día)
        'drag_type': dragType  // 'start' para cambiar la fecha de entrada, 'end' para cambiar la fecha de salida
    };

    // Realizamos la solicitud AJAX para actualizar la fecha
    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Recargar la página para reflejar los cambios
            location.reload();
        } else {
            alert('Error al actualizar la reserva: ' + data.message);
        }
    });

    dragging = false; // Reiniciar la variable de arrastre
}


// Evitar el comportamiento por defecto en el dragover
document.addEventListener('dragover', function(event) {
    event.preventDefault();
    console.log('dragover event:', event); // Debug
});


</script>
<script>
    


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
