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
    <h2 class="mb-3">{{ __('Crear comprobacion') }}</h2>
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
            <form action="{{ route('admin.itemsChecklist.store') }}" method="POST" class="mb-4">
              @csrf
              <input type="hidden" name="checklistId" value="{{$checklist->id}}">
                <div class="form-grup mb-5">
                  <label for="form-label">Nombre de la Comprobacion</label>
                    <input type="text" class="form-control" name="nombre" placeholder="Nombre banco">
                </div>
                  <button type="submit" class="btn bg-color-primero">Crear Item</button>
              </form>

        </div>
    </div>
</div>
@endsection

@include('sweetalert::alert')

@section('scripts')

@endsection

