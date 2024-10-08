@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <h2 class="mb-3">{{ __('Editando Estado: ' . $estado->nombre) }}</h2>
    <hr class="mb-5">

    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form action="{{ route('admin.estadosDiario.update', $estado->id) }}" method="POST" class="mb-4">
        @csrf
        <div class="form-group mb-5">
            <label for="nombre" class="form-label">Nombre del Estado</label>
            <input type="text" class="form-control" name="nombre" value="{{ $estado->nombre }}" required>
        </div>
        <button type="submit" class="btn bg-color-primero">Actualizar Estado</button>
    </form>
</div>
@endsection
