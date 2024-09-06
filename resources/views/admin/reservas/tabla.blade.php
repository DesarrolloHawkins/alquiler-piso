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
</style>
<style>
  body { font-family: Arial, sans-serif; }
  table { width: 100%; border-collapse: collapse; }
  th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
  th { background-color: #f2f2f2; }
  .header { background-color: #4CAF50; color: white; padding: 10px; }
</style>
<div class="container-fluid">
    <div class="d-flex flex-colum mb-3">
        <h2 class="mb-0 me-3 encabezado_top">{{ __('Tabla de Reservas') }}</h2>
    </div>
    <hr class="mb-5">
    <div class="row justify-content-center">
      <div class="header">
        <a href="{{ route('admin.tablaReservas.index', ['date' => \Carbon\Carbon::createFromFormat('Y-m', $date)->subMonth()->format('Y-m')]) }}" class="nav-link">Mes Anterior</a>
        <h1>Calendario de Reservas para {{ $monthName }}</h1>
        <a href="{{ route('admin.tablaReservas.index', ['date' => \Carbon\Carbon::createFromFormat('Y-m', $date)->addMonth()->format('Y-m')]) }}" class="nav-link">Mes Siguiente</a>
      </div>
      @if ($apartamentos)
        <table class="table table-bordered">
          <thead>
              <tr>
                  <th>Apartamentos</th>
                  @for ($day = 1; $day <= $daysInMonth; $day++)
                      <th>{{ $day }}</th>
                  @endfor
              </tr>
          </thead>
          <tbody>
              @foreach ($apartamentos as $apartamento)
                  <tr>
                      <td>{{ $apartamento->titulo }}</td>
                      
                      @for ($day = 1; $day <= $daysInMonth; $day++)
                          @php
                              $found = false;
                          @endphp
                          
                          {{-- Buscar si hay una reserva que coincida con este día --}}
                          @foreach ($apartamento->reservas as $itemReserva)
                              @if ($itemReserva->fecha_entrada->day == $day)
                                  @php
                                      // Comparar la fecha con la fecha de hoy
                                      $fechaHoy = \Carbon\Carbon::now();
                                      $claseBoton = '';

                                      if ($itemReserva->fecha_entrada->isPast()) {
                                          $claseBoton = 'btn-warning'; // Pasado
                                      } elseif ($itemReserva->fecha_entrada->isToday()) {
                                          $claseBoton = 'btn-success'; // Hoy
                                      } else {
                                          $claseBoton = 'btn-info'; // Futuro
                                      }
                                  @endphp
                                  <td class="p-0">
                                      <button type="button" class="rounded-0 btn {{ $claseBoton }}" data-bs-toggle="modal" data-bs-target="#modalReserva{{ $itemReserva->id }}">
                                          ({{ $itemReserva->fecha_entrada->format('d/m') }})
                                      </button>
                                  </td>
                                  @php
                                      $found = true;
                                  @endphp
                                  @break  {{-- Salir del bucle de reservas si ya encontramos una para este día --}}
                              @endif
                          @endforeach
      
                          {{-- Si no se encontró ninguna reserva, agregar una celda vacía --}}
                          @if (!$found)
                              <td></td>
                          @endif
                      @endfor
                  </tr>
              @endforeach
          </tbody>
        </table>
      
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
