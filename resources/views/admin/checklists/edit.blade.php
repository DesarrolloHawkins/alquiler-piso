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
    <h2 class="mb-3">{{ __('Nuestros Bancos') }}</h2>
    {{-- <a href="{{route('apartamentos.admin.create')}}" class="btn bg-color-quinto">Crear banco</a> --}}
    <hr class="mb-5">
    <div class="row justify-content-center">
      <div class="col-md-12">
        @if (session('status'))
              <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif
        <!-- Formulario de búsqueda -->
        <form action="{{ route('admin.bancos.update', $checklist->id) }}" method="POST" class="mb-4">
          @csrf
            <div class="form-grup mb-2">
              <label for="form-label">Edificio</label>
              <select name="edificio_id" id="edificio_id" class="form-select">
                  @if (count($edificios) > 0 )
                      @foreach ($edificios as $edificio)
                          <option @if($checklist->edificio_id == $edificio->id) selected @endif value="{{$edificio->id}}">{{$edificio->nombre}}</option>
                      @endforeach
                  @endif
              </select>
            </div>
            <div class="form-grup mb-5">
              <label for="form-label">Nombre de la Categoria</label>
              <input type="text" class="form-control" name="nombre" value="{{$checklist->nombre}}">
            </div>
              <button type="submit" class="btn bg-color-primero">Actualizar Categoria</button>
        </form>

        </div>
    </div>
</div>
@endsection

@include('sweetalert::alert')

@section('scripts')

@endsection

