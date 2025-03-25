@extends('layouts.appAdmin')

@section('content')
@php
    use Carbon\Carbon;

    // Rango del mes según tu variable $date ("YYYY-MM")
    $startOfMonth = Carbon::createFromFormat('Y-m', $date)->startOfMonth();
    $endOfMonth   = $startOfMonth->copy()->endOfMonth();
    $daysInMonth  = $startOfMonth->daysInMonth;

    // Diseño
    $dayWidth     = 80;     // px por día
    $sidebarWidth = 200;    // px para la columna de apartamentos
@endphp

<style>
/* Prevenir scroll vertical en padres, usar el del navegador */
.container, .container-fluid, .content, .card-body {
  overflow-y: visible !important;
  height: auto !important;
}

/* .gantt-container:
   solo scroll horizontal, sin altura fija */
.gantt-container {
  width: 100%;
  border: 1px solid #ccc;
  background: #fff;
  overflow-x: auto;
  overflow-y: visible;
  box-sizing: border-box;
  position: relative;
  margin-bottom: 1rem;
}

/* Cabecera días (sticky) */
.gantt-header {
  position: sticky;
  top: 0;
  left: 0;
  z-index: 10;
  width: {{ $sidebarWidth + $daysInMonth * $dayWidth }}px;
  background: #fbfbfb;
  border-bottom: 2px solid #ccc;
  display: flex;       /* para poner la spacer y los días en una misma línea */
  align-items: center;
  box-sizing: border-box;
  padding: 8px 0;      /* algo de espacio vertical */
}

/* Espacio para alinear con la columna de aptos */
.header-sidebar-spacer {
  width: {{ $sidebarWidth }}px;
  border-right: 1px solid #ddd;
  box-sizing: border-box;
}

/* Día en la cabecera */
.gantt-header-day {
  width: {{ $dayWidth }}px;
  border-right: 1px solid #ddd;
  flex-shrink: 0;
  text-align: center;
  font-weight: bold;
  box-sizing: border-box;
  padding: 0;
    font-size: 12px;
}

/* FILA de apartamento:
   usamos flex en una sola línea: [label][days] */
.gantt-row {
  width: {{ $sidebarWidth + $daysInMonth * $dayWidth }}px;
  border-bottom: 1px solid #ddd;
  box-sizing: border-box;
  position: relative;
  display: flex;           /* un contenedor flex */
  flex-direction: row;     /* horizontal */
  align-items: stretch;    /* ambos subcontenedores misma altura de fila */
  /* margin: 0; min-height: algo si deseas */
}

/* Columna del apartamento:
   ancho fijo, sticky */
.gantt-row-label {
  position: sticky;
  left: 0;
  z-index: 2;
  width: {{ $sidebarWidth }}px;
  background: #fafafa;
  border-right: 1px solid #ddd;
  padding: 8px;
  box-sizing: border-box;
  white-space: nowrap;
  font-weight: bold;
  display: flex;
  align-items: center;
  z-index: 5000;
  font-size: 11px;
}

/* Contenedor de días:
   Ocupa el resto, con ancho = daysInMonth * dayWidth.
   No salte a la siguiente línea, pues es un flex item. */
.gantt-row-days {
  position: relative;
  box-sizing: border-box;
  width: {{ $daysInMonth * $dayWidth }}px;
  /* sin "margin-left" ahora, pues lo ubicamos con flex */
}

/* Línea vertical (1 por día), cubre toda la altura de la fila */
.gantt-day-line {
  position: absolute;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #d4d4d4;
  z-index: 0;
}

/* Cada reserva (badge) */
.reserva-badge {
  position: absolute;
  height: 20px;
  background-color: #57628e;
  color: #fff;
  border-radius: 15px;
  font-size: 12px;
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;

  /* Centrado vertical en la .gantt-row-days.
     Por ejemplo, top: 8px para no pisar la línea de arriba.
     O top: 50% => transform => se centrará.
     Depende de la altura total que adoptes.
     Usa margin si prefieres */
  top: 50%;
  transform: translateY(-50%);

  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 5;
  padding: 0 10px;
}
</style>

<div class="container-fluid">
  <!-- Botonera mes -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('admin.tablaReservas.index',
        $startOfMonth->copy()->subMonth()->format('Y-m')) }}" class="btn btn-primary">
      Mes Anterior
    </a>
    <h3 class="text-uppercase">{{ $startOfMonth->translatedFormat('F') }} {{ $startOfMonth->year }}</h3>
    <a href="{{ route('admin.tablaReservas.index',
        $startOfMonth->copy()->addMonth()->format('Y-m')) }}" class="btn btn-primary">
      Mes Siguiente
    </a>
  </div>

  <div class="gantt-container">
    <!-- Cabecera -->
    <div class="gantt-header">
      <div class="header-sidebar-spacer"></div>
      @for($d=1; $d<=$daysInMonth; $d++)
        <div class="gantt-header-day">
          Día {{ $d }}
        </div>
      @endfor
    </div>

    @php
    // Lista de apartamentos a excluir
    $apartamentosExcluir = ['16', '17','18','19','20','23','22']; // O usa los IDs
    @endphp
    {{-- {{dd($apartamentos[1])}} --}}
    <!-- Filas de apartamentos (flex) -->
    @foreach($apartamentos as $apt)


        @php
            // Comprobar si el apartamento está en la lista de exclusión
            if (in_array($apt->id, $apartamentosExcluir)) {
                    continue; // Excluir apartamento
            }
            $colors = [
                '1' => '#FF5733',  // Rojo
                '2' => '#33FF57',  // Verde
                '3' => '#3357FF',  // Azul
                '4' => '#FF33A1',  // Rosa
                '5' => '#FFD700',  // Amarillo
                '6' => '#7B68EE',  // Azul morado
                '7' => '#FF6347',  // Tomate
                '8' => '#00FA9A',  // Verde mar
                '9' => '#8A2BE2',  // Azul violeta
                '10' => '#FF1493', // Rosa profundo
                '11' => '#00BFFF', // Azul profundo
                '12' => '#32CD32', // Verde lima
                '13' => '#FF8C00', // Naranja oscuro
                '14' => '#D2691E', // Chocolate
                '15' => '#A52A2A', // Marrón
                '21' => '#5F9EA0', // Azul pálido
            ];

            $color = $colors[$apt->id] ?? '#';  // Color por defecto si no se encuentra
        @endphp

      <div class="gantt-row">
        <!-- Etiqueta apto (sticky) -->
        <div class="gantt-row-label" id="apt{{ $apt->id }}">
          {{ $apt->titulo }}
        </div>

        <!-- Días -->
        <div class="gantt-row-days">
          <!-- Líneas verticales -->
          @for($d=1; $d<=$daysInMonth; $d++)
            @php
              $leftPos = ($d-1) * $dayWidth;
            @endphp
            <div class="gantt-day-line" style="left: {{ $leftPos }}px;"></div>
          @endfor

          <!-- Reservas -->
          @foreach($apt->reservas as $reserva)
            @php
              // Compara las fechas completas (no solo el día)
              $startFecha = $reserva->fecha_entrada;
              $endFecha   = $reserva->fecha_salida;

              // Asegúrate de que las fechas están dentro del rango
              if ($startFecha->lt($startOfMonth) && $endFecha->lt($startOfMonth)) {
                  continue; // Si la reserva empieza antes de este mes y termina antes, la excluimos
              }

              if ($startFecha->gt($endOfMonth) && $endFecha->gt($endOfMonth)) {
                  continue; // Si la reserva empieza después de este mes y termina después, la excluimos
              }

              // Ajusta las fechas si es necesario
              $startFecha = max($startFecha, $startOfMonth); // Si la entrada es antes del inicio del mes, ajusta al inicio
              $endFecha = min($endFecha, $endOfMonth); // Si la salida es después del final del mes, ajusta al final

              $startDay = $startFecha->day;
              $endDay   = $endFecha->day;

              $pxPerHour = $dayWidth / 24.0;
              $startOffsetH = (($startDay - 1) * 24) + 12;
              $endOffsetH = (($endDay - 1) * 24) + 12;

              $durationH = $endOffsetH - $startOffsetH;
              if ($durationH < 0) continue;

              $leftPx = $startOffsetH * $pxPerHour;
              $widthPx = $durationH * $pxPerHour;

              // gap de 8px total
              $gap = 8;
              $adjLeft = $leftPx + $gap / 2;
              $adjWidth = max(0, $widthPx - $gap);

              // flecha
              $arrow = '';
              if ($endFecha->gt($endOfMonth)) {
                  $arrow = ' →';
              }
              $badgeText = ($reserva->cliente->nombre ?? $reserva->cliente->alias) . $arrow;
            @endphp

            <div class="reserva-badge"
                 style="left:{{ $adjLeft }}px; width:{{ $adjWidth }}px;background-color: {{ $color }}"
                 title="Reserva #{{ $reserva->id }}"
            >
              <span id="badgeText">
                {{ $badgeText }}
              </span>
            </div>
          @endforeach
        </div><!-- .gantt-row-days -->
      </div><!-- .gantt-row -->
    @endforeach
  </div><!-- .gantt-container -->
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
      var badgeText = document.querySelectorAll('#badgeText');
      console.log(badgeText);
      for (var i = 0; i < badgeText.length; i++) {
        if (badgeText[i].innerText.length > 6) {
          badgeText[i].innerText = badgeText[i].innerText.substring(0, 6) + '...';
        }
      }
    });
</script>

@endsection
