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
    .header { background-color: #0f1739; color: white; padding: 20px 10px; margin-bottom: 1rem }
    .fondo_verde {
      background-color: #def7df !important; /* Color de fondo verde para el día de hoy */
    }
    /* Evitar salto de línea en la columna de apartamentos */
    .apartments-column {
        white-space: nowrap;
        width: auto;
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
                                    @if ($itemReserva->fecha_entrada->day == $day)
                                        @php
                                            // Obtener la fecha de la reserva en formato Carbon
                                            $fechaEntrada = \Carbon\Carbon::parse($itemReserva->fecha_entrada);
                                            $fechaSalida = \Carbon\Carbon::parse($itemReserva->fecha_salida);

                                            // Calcular la diferencia en días entre la entrada y la salida
                                            $diasDiferencia = $fechaEntrada->diffInDays($fechaSalida);

                                            // Definir el color del botón según la fecha de reserva
                                            $claseBoton = '';
                                            if ($fechaEntrada->isPast() && !$fechaEntrada->isToday()) {
                                                $claseBoton = 'btn-warning'; // Pasado
                                            } elseif ($fechaEntrada->isToday()) {
                                                $claseBoton = 'btn-success'; // Hoy (verde)
                                            } else {
                                                $claseBoton = 'btn-info'; // Futuro
                                            }
                                        @endphp

                                        {{-- Renderizar la celda con colspan --}}
                                        <td colspan="{{ $diasDiferencia }}" class="p-0 {{ $claseDiaHoy }}">
                                            <button type="button" class="w-100 rounded-0 btn {{ $claseBoton }}" data-bs-toggle="modal" data-bs-target="#modalReserva{{ $itemReserva->id }}">
                                                ({{ $itemReserva->fecha_entrada->format('d') }} - {{ $itemReserva->fecha_salida->format('d') }})
                                            </button>
                                        </td>

                                        {{-- Saltar los días que ya están cubiertos por el colspan --}}
                                        @php
                                            $day += $diasDiferencia - 1; // Avanzar el contador de días
                                            $found = true;
                                        @endphp
                                        @break  {{-- Salir del bucle de reservas si ya encontramos una para este día --}}
                                    @endif
                                @endforeach
                            
                                {{-- Si no se encontró ninguna reserva, agregar una celda vacía --}}
                                @if (!$found)
                                    <td data-apartamento="{{$apartamento->id}}" data-dia="{{$day}}" class="{{ $claseDiaHoy }}"></td>
                                @endif
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @foreach ($apartamentos as $apartamento)
            @foreach ($apartamento->reservas as $itemReserva)
              <!-- Modal -->
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
@endsection
