@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-colum mb-3">
        <h2 class="mb-0 me-3 encabezado_top">{{ __('Crear Cliente') }}</h2>
    </div>
    {{-- <a href="{{route('clientes.create')}}" class="btn bg-color-quinto">Crear cliente</a> --}}
    <hr>
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('clientes.store') }}" method="POST" class="row">
                @csrf  <!-- Token CSRF para proteger tu formulario -->

                <div class="col-md-6 col-12 mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control {{ $errors->has('nombre') ? 'is-invalid' : '' }}" id="nombre" name="nombre" value="{{ old('nombre') }}">
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="apellido1" class="form-label">Primer Apellido</label>
                    <input type="text" class="form-control {{ $errors->has('apellido1') ? 'is-invalid' : '' }}" id="apellido1" name="apellido1" value="{{ old('apellido1') }}">
                    @error('apellido1')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="apellido2" class="form-label">Segundo Apellido</label>
                    <input type="text" class="form-control {{ $errors->has('apellido2') ? 'is-invalid' : '' }}" id="apellido2" name="apellido2" value="{{ old('apellido2') }}">
                    @error('apellido2')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                    <input type="date" class="form-control {{ $errors->has('fecha_nacimiento') ? 'is-invalid' : '' }}" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}">
                    @error('fecha_nacimiento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="sexo_str" class="form-label">Sexo</label>
                    <input type="text" class="form-control {{ $errors->has('sexo') ? 'is-invalid' : '' }}" id="sexo_str" name="sexo" value="{{ old('sexo') }}">
                    @error('sexo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="telefono" class="form-label">Telefono</label>
                    <input type="text" class="form-control {{ $errors->has('telefono') ? 'is-invalid' : '' }}" id="telefono" name="telefono" value="{{ old('telefono') }}">
                    @error('telefono')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" id="email" name="email" value="{{ old('email') }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="idiomas" class="form-label">Idioma</label>
                    <!-- Input visible solo para lectura -->
                    <input type="text" id="idiomas_display" class="form-control" placeholder="Seleccione Nacionalidad..." readonly>
                    <!-- Input oculto que realmente se enviará -->
                    <input type="hidden" name="idiomas" id="idiomas" value="{{ old('idiomas') }}">
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="nacionalidad" class="form-label">Nacionalidad</label>
                    <select name="nacionalidad" id="nacionalidad" class="form-select {{ $errors->has('nacionalidad') ? 'is-invalid' : '' }}" aria-label="Pais">
                        <option value="" selected>Selecciona Nacionalidad</option>
                        @foreach ($paises as $pais)
                            <option value="{{ $pais }}" data-idioma="{{ $idiomaAPais[$pais] ?? 'No disponible' }}">
                                {{ $pais }}
                            </option>
                        @endforeach
                    </select>
                    @error('nacionalidad')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                    <select name="tipo_documento" id="tipo_documento" class="form-select {{ $errors->has('tipo_documento') ? 'is-invalid' : '' }}" aria-label="DNI o Pasaporte">
                        <option value="" selected>Selecciona el tipo de documento</option>
                        <option value="DNI">Dni</option>
                        <option value="Pasaporte">Pasaporte</option>
                    </select>
                    @error('tipo_documento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="num_identificacion" class="form-label">Numero de Identificacion</label>
                    <input type="text" class="form-control {{ $errors->has('num_identificacion') ? 'is-invalid' : '' }}" id="num_identificacion" name="num_identificacion" value="{{ old('num_identificacion') }}">
                    @error('num_identificacion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="fecha_expedicion_doc" class="form-label">Fecha de Expedicion</label>
                    <input max="{{ date('Y-m-d') }}" type="date" class="form-control {{ $errors->has('fecha_expedicion_doc') ? 'is-invalid' : '' }}" id="fecha_expedicion_doc" name="fecha_expedicion_doc" value="{{ old('fecha_expedicion_doc') }}">
                    @error('fecha_expedicion_doc')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-terminar w-100 fs-4 mt-4">Guardar</button>
            </form>

        </div>
    </div>
</div>
@endsection
@section('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectNacionalidad = document.getElementById('nacionalidad');
        const inputIdiomas = document.getElementById('idiomas');
        const inputIdiomasDisplay = document.getElementById('idiomas_display');

        selectNacionalidad.addEventListener('change', function() {
            // Obtener el idioma desde el atributo data del option seleccionado
            const idiomaSeleccionado = selectNacionalidad.options[selectNacionalidad.selectedIndex].dataset.idioma;
            // Establecer el idioma en el input correspondiente y el campo oculto
            inputIdiomas.value = idiomaSeleccionado;
            inputIdiomasDisplay.value = idiomaSeleccionado;
        });

        // Inicializa el campo de idiomas visible y oculto con el idioma del primer país seleccionado por defecto
        const idiomaInicial = selectNacionalidad.options[selectNacionalidad.selectedIndex].dataset.idioma;
        inputIdiomas.value = idiomaInicial;
        inputIdiomasDisplay.value = idiomaInicial;
    });
</script>



@endsection
