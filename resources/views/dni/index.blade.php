@extends('layouts.appUser')

@section('bienvenido')
    <h5 class="navbar-brand mb-0 w-auto text-center">Rellene el formulario para confirmar su reserva</h5>
@endsection

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 text-center">
            <img src="https://apartamentosalgeciras.com/wp-content/uploads/2022/09/Logo-Hawkins-Suites.svg" alt="" class="img-fluid mb-3 w-50 m-auto">
        </div>
        <div class="col-sm-12">
            
            <div class="card">
                <div class="card-header bg-color-primero">
                    Rellene los datos con su DNI o Pasaporte
                </div>
                <div class="card-body">
                    <form action="{{route('dni.store')}}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{$id}}">
                        <div class="mb-3">
                            <input class="form-control" type="text" placeholder="Nombre" aria-label="Nombre" name="nombre" id="nombre" value="{{ old('nombre')}}">
                            @error('nombre')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <input class="form-control" type="text" placeholder="Primer Apellido" aria-label="Primer Apellido" name="apellido1" id="apellido1" value="{{ old('apellido1')}}">
                            @error('apellido1')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <input class="form-control" type="text" placeholder="Segundo Apellido" aria-label="Segundo Apellido" name="apellido2" id="apellido2" value="{{ old('apellido2')}}">
                            @error('apellido2')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <select name="nacionalidad" id="nacionalidad" class="form-control js-example-basic-single" placeholder="DNI o Pasaporte" aria-label="DNI o Pasaporte" >
                                <option value="{{null}}">Seleccione Pais</option>
                                @foreach ($paises as $pais)
                                <option value="{{$pais}}"  {{ (old('nacionalidad') == $pais ? 'selected' : '') }}>{{$pais}}</option>
                                @endforeach
                            </select>
                            @error('nacionalidad')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <select name="tipo_documento" id="tipo_documento" class="form-control" placeholder="DNI o Pasaporte" aria-label="DNI o Pasaporte">
                                <option value="{{null}}">DNI o Pasaporte</option>
                                <option value="0"  {{ (old('tipo_documento') == '0' ? 'selected' : '') }}>DNI</option>
                                <option value="1" {{ (old('tipo_documento') == '1' ? 'selected' : '') }}>Pasaporte</option>
                            </select>
                            @error('tipo_documento')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <input class="form-control" type="text" placeholder="Numero Identificación" aria-label="Numero Identificación" name="num_identificacion" id="num_identificacion" value="{{ old('num_identificacion')}}">
                            @error('num_identificacion')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="fecha_expedicion_doc">Fecha de Expedición</label>
                            <input class="form-control" type="date" placeholder="Fecha de Expedición" aria-label="Fecha de Expedición" name="fecha_expedicion_doc" id="fecha_expedicion_doc" value="{{ old('fecha_expedicion_doc')}}">
                            @error('fecha_expedicion_doc')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                            <input class="form-control" type="date" placeholder="Fecha de Nacimiento" aria-label="Fecha de Nacimiento" name="fecha_nacimiento" id="fecha_nacimiento" value="{{ old('fecha_nacimiento')}}">
                            @error('fecha_nacimiento')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <select name="sexo" id="sexo" class="form-control" placeholder="Sexo" aria-label="Sexo">
                                <option value="{{null}}">Seleccione Sexo</option>
                                <option value="Masculino" {{ (old('tipo_documento') == 'Masculino' ? 'selected' : '') }}>Masculino</option>
                                <option value="Femenino" {{ (old('tipo_documento') == 'Femenino' ? 'selected' : '') }}>Femenino</option>
                                <option value="Binario" {{ (old('tipo_documento') == 'Binario' ? 'selected' : '') }}>Binario</option>
                            </select>
                            @error('sexo')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <input class="form-control" type="text" placeholder="Correo Electronico" aria-label="Correo Electronico" name="email" id="email" value="{{ old('email')}}">
                            @error('email')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                           <button class="btn btn-terminar w-100">Enviar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .file-input {
      display: none;
    }
</style>
@endsection
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // In your Javascript (external .js resource or <script> tag)
    $(document).ready(function() {
        $('.js-example-basic-single').select2();
    });
</script>
@endsection