@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">.
    <div class="d-flex flex-colum mb-3">
        <h2 class="mb-0 me-3 encabezado_top">{{ __('Editar Cliente: ') }}{{$cliente->nombre == null ? $cliente->alias: $cliente->nombre}}</h2>
        {{-- <a href="{{route('clientes.create')}}" class="btn bg-color-sexto text-uppercase">
            <i class="fa-solid fa-plus me-2"></i>
            Crear cliente
        </a> --}}

    </div>
    {{-- <h2 class="mb-3">{{ __('Editar Cliente') }}</h2> --}}
    {{-- <a href="{{route('clientes.create')}}" class="btn bg-color-quinto">Crear cliente</a> --}}
    <hr>
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('clientes.update', $cliente->id) }}" method="POST" class="row">
                @csrf
                <div class="col-md-6 col-12 mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre" value="{{ old('nombre', $cliente->nombre) }}">
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="apellido1" class="form-label">Primer Apellido</label>
                    <input type="text" class="form-control @error('apellido1') is-invalid @enderror" id="apellido1" name="apellido1" value="{{ old('apellido1', $cliente->apellido1) }}">
                    @error('apellido1')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="apellido2" class="form-label">Segundo Apellido (Opcional)</label>
                    <input type="text" class="form-control @error('apellido2') is-invalid @enderror" id="apellido2" name="apellido2" value="{{ old('apellido2', $cliente->apellido2) }}">
                    @error('apellido2')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                    <input type="date" class="form-control @error('fecha_nacimiento') is-invalid @enderror" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $cliente->fecha_nacimiento) }}">
                    @error('fecha_nacimiento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="sexo" class="form-label">Sexo</label>
                    <input type="text" class="form-control @error('sexo') is-invalid @enderror" id="sexo" name="sexo" value="{{ old('sexo', $cliente->sexo) }}">
                    @error('sexo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="telefono" class="form-label">Telefono</label>
                    <input type="text" class="form-control @error('telefono') is-invalid @enderror" id="telefono" name="telefono" value="{{ old('telefono', $cliente->telefono) }}">
                    @error('telefono')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $cliente->email) }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="idiomas_display" class="form-label">Idioma</label>
                    <input type="text" id="idiomas_display" class="form-control" readonly value="{{ $idiomaAPais[$cliente->nacionalidad] ?? 'No disponible' }}">
                    <input type="hidden" name="idiomas" id="idiomas" value="{{ $cliente->idiomas }}">
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="nacionalidad" class="form-label">Nacionalidad</label>
                    <select name="nacionalidad" id="nacionalidad" class="form-select @error('nacionalidad') is-invalid @enderror" aria-label="Nacionalidad">
                        <option value="" disabled>Selecciona Nacionalidad</option>
                        @foreach ($paises as $pais)
                            <option value="{{ $pais }}" {{ $cliente->nacionalidad == $pais ? 'selected' : '' }} data-idioma="{{ $idiomaAPais[$pais] ?? 'No disponible' }}">
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
                    <select name="tipo_documento" id="tipo_documento" class="form-select @error('tipo_documento') is-invalid @enderror" aria-label="Tipo de Documento">
                        <option value="DNI" {{ $cliente->tipo_documento == 'DNI' ? 'selected' : '' }}>DNI</option>
                        <option value="Pasaporte" {{ $cliente->tipo_documento == 'Pasaporte' ? 'selected' : '' }}>Pasaporte</option>
                    </select>
                    @error('tipo_documento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="num_identificacion" class="form-label">Número de Identificación</label>
                    <input type="text" class="form-control @error('num_identificacion') is-invalid @enderror" id="num_identificacion" name="num_identificacion" value="{{ old('num_identificacion', $cliente->num_identificacion) }}">
                    @error('num_identificacion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="fecha_expedicion_doc" class="form-label">Fecha de Expedición</label>
                    <input max="{{ date('Y-m-d') }}" type="date" class="form-control @error('fecha_expedicion_doc') is-invalid @enderror" id="fecha_expedicion_doc" name="fecha_expedicion_doc" value="{{ old('fecha_expedicion_doc', $cliente->fecha_expedicion_doc) }}">
                    @error('fecha_expedicion_doc')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <h3>Domicilio de Facturación</h3>

                <div class="col-md-6 col-12 mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" name="direccion" class="form-control"  value="{{ $cliente->direccion }}">
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <label for="localidad" class="form-label">Ciudad</label>
                    <input type="text" name="localidad" class="form-control"  value="{{ $cliente->localidad }}">
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <label for="codigo_postal" class="form-label">Codigo Postal</label>
                    <input type="text" name="codigo_postal" class="form-control"  value="{{ $cliente->codigo_postal }}">
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <label for="provincia" class="form-label">Provincia</label>
                    <input type="text" name="provincia" class="form-control"  value="{{ $cliente->provincia }}">
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <label for="estado" class="form-label">Pais</label>
                    <input type="text" name="estado" class="form-control"  value="{{ $cliente->estado }}">
                </div>

                <button type="submit" class="btn btn-terminar w-100 mt-4">Actualizar</button>
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
            const idiomaSeleccionado = selectNacionalidad.options[selectNacionalidad.selectedIndex].dataset.idioma;
            inputIdiomas.value = idiomaSeleccionado;
            inputIdiomasDisplay.value = idiomaSeleccionado;
        });

        // Inicializar con el valor actual al cargar
        const idiomaInicial = selectNacionalidad.options[selectNacionalidad.selectedIndex].dataset.idioma;
        inputIdiomas.value = idiomaInicial;
        inputIdiomasDisplay.value = idiomaInicial;
        console.log(inputIdiomas.value);
    });
</script>
@endsection
