@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <h2 class="mb-3">{{ __('Editar Apartamento') }}</h2>
    {{-- <a href="{{route('clientes.create')}}" class="btn bg-color-quinto">Crear cliente</a> --}}
    <hr>
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('apartamentos.admin.update', $apartamento->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="edificio" class="form-label">Edificio</label>
                    <select name="edificio" id="edificio" class="form-control @error('edificio') is-invalid @enderror">
                        <option value="{{null}}">Seleccione un Edificio</option>
                        <option value="{{1}}">Edificio Hawkins</option>
                        <option value="{{2}}">Edificio Costa</option>
                    </select>
                    
                    @error('edificio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="titulo" class="form-label">Nombre</label>
                    <input type="text" class="form-control @error('titulo') is-invalid @enderror" id="titulo" name="titulo" value="{{ old('titulo', $apartamento->titulo) }}">
                    @error('titulo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="nombre" class="form-label">Id Airbnb</label>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre', $apartamento->nombre) }}">
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="id_booking" class="form-label">Id Booking</label>
                    <input type="text" class="form-control @error('id_booking') is-invalid @enderror" id="id_booking" name="id_booking" value="{{ old('id_booking', $apartamento->id_booking) }}">
                    @error('id_booking')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="id_web" class="form-label">Id Web</label>
                    <input type="text" class="form-control @error('id_web') is-invalid @enderror" id="id_web" name="id_web" value="{{ old('id_web', $apartamento->id_web) }}">
                    @error('id_web')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="claves" class="form-label">Clave de Acceso</label>
                    <input type="text" class="form-control @error('claves') is-invalid @enderror" id="claves" name="claves" value="{{ old('claves', $apartamento->claves) }}">
                    @error('claves')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-terminar mt-4">Actualizar</button>
            </form>

        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
</script>
@endsection
