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
    {{-- <a href="{{route('admin.bancos.create')}}" class="btn bg-color-quinto">Crear banco</a> --}}
    <hr class="mb-5">
    <div class="row justify-content-center">
      {{-- Filtros --}}

      <div class="col-md-12 mb-4">
        <h5>Filtros</h5>
        <form action="{{route('admin.jornada.index')}}" method="GET">
          <div class="row align-items-end">
            <div class="col-md-4">
              <label for="form-label">Buscar por dia</label>
              <input type="date" class="form-control" name="fecha_inicio">
            </div>
            <div class="col-md-4">
              <label for="form-label">Buscar por mes</label>
              <select name="mes" id="mes" class="form-select">
                <option value="{{null}}">-- Seleciones el Mes --</option>
                <option value="1">Enero</option>
                <option value="2">Febrero</option>
                <option value="3">Marzo</option>
                <option value="4">Abril</option>
                <option value="5">Mayo</option>
                <option value="6">Junio</option>
                <option value="7">Julio</option>
                <option value="8">Agosto</option>
                <option value="9">Septiembre</option>
                <option value="10">Octubre</option>
                <option value="11">Noviembre</option>
                <option value="12">Diciembre</option>
              </select>
            </div>
            <div class="col-md-4">
              <button class="btn btn-primary">Filtrar</button>
            </div>
          </div>
        </form>
      </div>
      <div class="col-md-12">
        @if (count($users) > 0)
            @foreach ($users as $user)
                <h3>{{$user->name}}</h3>
                <ul>
                  @isset($user->jornada)
                  @foreach ($user->jornada as  $itemJornada)
                    <li>
                      {{$itemJornada->hora_entrada}}
                    </li>
                  @endforeach
                  @endisset
                </ul>
                {{-- {{var_dump($user)}} --}}
            @endforeach
        @endif
      </div>
    </div>
</div>
@endsection

@include('sweetalert::alert')

@section('scripts')

@endsection

