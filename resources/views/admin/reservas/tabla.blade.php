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
  /* .nav-link { color: white; text-decoration: none; padding: 5px 10px; background-color: #4CAF50; border-radius: 5px; } */
</style>
<div class="container-fluid">
    <div class="d-flex flex-colum mb-3">
        <h2 class="mb-0 me-3 encabezado_top">{{ __('Tabla de Reservas') }}</h2>
    </div>
    <hr class="mb-5">
    <div class="row justify-content-center">
      <div class="header">
        <a href="{{ route('admin.tablaReservas.index', ['date' => \Carbon\Carbon::createFromFormat('Y-m', $date)->subMonth()->format('Y-m')]) }}" class="nav-link">Mes Anterior</a>
        <h1>Calendario de Reservas para {{ $monthName  }}</h1>
        <a href="{{ route('admin.tablaReservas.index', ['date' => \Carbon\Carbon::createFromFormat('Y-m', $date)->addMonth()->format('Y-m')]) }}" class="nav-link">Mes Siguiente</a>
    </div>
      @if ($apartamentos)
        {{-- <div id="calendar"></div> --}}
        <table>
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
                                  <td class="bg-warning">
                                      {{-- {{ $itemReserva->cli<table>
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
                            <td class="bg-warning">
                                {{-- {{ $itemReserva->cliente->nombre }}  --}}
                                ({{ $itemReserva->fecha_entrada->format('d/m') }})
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
ente->nombre }}  --}}
                                      ({{ $itemReserva->fecha_entrada->format('d/m') }})
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
      
      @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
  window.apartamentos = @json($apartamentos);
</script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.5/fullcalendar.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar-resource-timeline/6.1.5/resourceTimeline.min.css" />
  {{-- @vite(['resources/js/calendar.js']) --}}
  <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Verificar si SweetAlert2 está definido
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 is not loaded');
            return;
        }
    });
  </script>
@endsection
