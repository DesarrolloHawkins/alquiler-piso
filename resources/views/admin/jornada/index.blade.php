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
<div class="container-fluid">
    <h2 class="mb-3">{{ __('Jornada Laboral - Empleados') }}</h2>
    <hr class="mb-5">
    <div class="row justify-content-center">
      <div class="col-md-12 mb-4">
        <h5>Filtros</h5>
        <form action="{{route('admin.jornada.index')}}" method="GET">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label for="form-label">Buscar por día</label>
                    <input type="date" class="form-control" name="fecha_inicio" value="{{ $request->fecha_inicio }}">
                </div>
                <div class="col-md-4">
                    <label for="form-label">Buscar por mes</label>
                    <select name="mes" id="mes" class="form-select">
                        <option value="">-- Seleccione el Mes --</option>
                        @php
                            $meses = [
                                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                            ];
                        @endphp
                        @foreach ($meses as $numero => $nombre)
                            <option value="{{ $numero }}" {{ $request->mes == $numero ? 'selected' : '' }}>
                                {{ $nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary">Filtrar</button>
                </div>
            </div>
        </form>
      </div>      
      <div class="col-md-12">
        @if ($users->isNotEmpty())
            @foreach ($users as $user)
              <h3>Emplead@: {{$user->name}}</h3>
              @php $totalHorasMes = 0; @endphp

              <div class="accordion mb-4" id="accordionExample">
                @foreach ($user->jornada as $itemJornada)
                  @php
                    $entrada = \Carbon\Carbon::parse($itemJornada->hora_entrada);
                    $salida = \Carbon\Carbon::parse($itemJornada->hora_salida)->addHours(2);
                    $horasTrabajadas = $salida->diffInHours($entrada, true);
                    $totalHorasMes += $horasTrabajadas;
                  @endphp
                  <div class="accordion-item">
                    <h2 class="accordion-header">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#dia_{{$itemJornada->id}}" aria-expanded="false" aria-controls="collapseThree">
                        <p class="mb-1">
                          Dia: {{ $entrada->format('d/m/yy') }}
                          <br>
                          Hora de Entrada: {{ $entrada->addHours(2)->format('H:i:s') }} - 
                          Hora de Salida: {{ $salida->format('H:i:s') }}
                          <br>
                          <strong>Horas Trabajadas: {{ $horasTrabajadas }} hora(s)</strong>
                        </p>
                        </button>
                    </h2>
                    <div id="dia_{{$itemJornada->id}}" class="accordion-collapse collapse mb-4" data-bs-parent="#accordionExample">
                      <div class="accordion-body">
                        @if (count($itemJornada->limpiezas) > 0)
                          @foreach ( $itemJornada->limpiezas as $itemLimpieza )
                            <a class="d-block" href="{{route('apartamentoLimpieza.admin.show', $itemLimpieza->id)}}">Apartamento: {{$itemLimpieza->apartamento->titulo}}</a>
                          @endforeach
                        @endif
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
              <h4>Total de Horas Trabajadas en el Mes: {{ $totalHorasMes }} hora(s)</h4>
            @endforeach
        @else
            <p>No hay empleados para mostrar.</p>
        @endif
      </div>
    
    </div>
</div>
@endsection

@include('sweetalert::alert')

@section('scripts')

@endsection
