@extends('layouts.appAdmin')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<!-- Incluir el CSS de Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- Incluir Flatpickr y la localización en español -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

<div class="container-fluid">
    <div class="d-flex flex-colum mb-3">
        <h2 class="mb-0 me-3 encabezado_top">{{ __('Añadir Reserva') }}</h2>
    </div>
    {{-- <a href="{{route('clientes.create')}}" class="btn bg-color-quinto">Crear cliente</a> --}}
    <hr>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <form action="{{ route('reservas.store') }}" method="POST" class="row">
                @csrf
                <div class="col-md-6 col-12 mb-3">
                    <label for="cliente_id" class="form-label">Cliente ID</label>
                    <select class="form-control select2 {{ $errors->has('cliente_id') ? 'is-invalid' : '' }}" name="cliente_id" id="cliente_id">
                        <option value="">Seleccione un cliente</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->alias }} - {{$cliente->num_identificacion}}
                            </option>
                        @endforeach
                    </select>
                    @error('cliente_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>


                <div class="col-md-6 col-12 mb-3">
                    <label for="apartamento_id" class="form-label">Apartamento</label>
                    <select class="form-control {{ $errors->has('apartamento_id') ? 'is-invalid' : '' }}" name="apartamento_id" id="apartamento_id">
                        <option value="">Seleccione un apartamento</option>
                        @foreach($apartamentos as $apartamento)
                            <option value="{{ $apartamento->id }}" {{ old('apartamento_id') == $apartamento->id ? 'selected' : '' }}>{{ $apartamento->titulo }}</option>
                        @endforeach
                    </select>
                    @error('apartamento_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="origen" class="form-label">Origen</label>
                    <select class="form-control {{ $errors->has('origen') ? 'is-invalid' : '' }}" name="origen" id="origen">
                        <option value="">Seleccione el origen</option>
                        <option value="Airbnb" {{ old('origen') == 'Airbnb' ? 'selected' : '' }}>Airbnb</option>
                        <option value="Booking" {{ old('origen') == 'Booking' ? 'selected' : '' }}>Booking</option>
                        <option value="Web" {{ old('origen') == 'Web' ? 'selected' : '' }}>Web</option>
                    </select>
                    @error('origen')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 col-12 mb-3">
                    <label for="estado_id" class="form-label">Estado de la Reserva</label>
                    <select class="form-control {{ $errors->has('estado_id') ? 'is-invalid' : '' }}" name="estado_id" id="estado_id">
                        <option value="">Seleccione un apartamento</option>
                        @foreach($estados as $estado)
                            <option value="{{ $estado->id }}" {{ old('estado_id') == $estado->id ? 'selected' : '' }}>{{ $estado->nombre }}</option>
                        @endforeach
                    </select>
                    @error('estado_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="codigo_reserva" class="form-label">Código de Reserva</label>
                    <input type="text" class="form-control {{ $errors->has('codigo_reserva') ? 'is-invalid' : '' }}" name="codigo_reserva" value="{{ old('codigo_reserva') }}">
                    @error('codigo_reserva')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="fecha_entrada" class="form-label">Fecha de Entrada</label>
                    <input type="text" class="form-control {{ $errors->has('fecha_entrada') ? 'is-invalid' : '' }}" id="fecha_entrada" name="fecha_entrada" value="{{ old('fecha_entrada') }}" required>
                    @error('fecha_entrada')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="fecha_salida" class="form-label">Fecha de Salida</label>
                    <input type="text" class="form-control {{ $errors->has('fecha_salida') ? 'is-invalid' : '' }}" id="fecha_salida" name="fecha_salida" value="{{ old('fecha_salida') }}" required>
                    @error('fecha_salida')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="precio" class="form-label">Precio</label>
                    <input type="text" class="form-control {{ $errors->has('precio') ? 'is-invalid' : '' }}" name="precio" value="{{ old('precio') }}">
                    @error('precio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="verificado" class="form-label">Verificado</label>
                    <div class="form-check">
                        <input class="form-check-input {{ $errors->has('verificado') ? 'is-invalid' : '' }}" type="checkbox" name="verificado" id="verificado" value="1" {{ old('verificado') ? 'checked' : '' }}>
                        <label class="form-check-label" for="verificado">Sí</label>
                        @error('verificado')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="dni_entregado" class="form-label">DNI Entregado</label>
                    <div class="form-check">
                        <input class="form-check-input {{ $errors->has('dni_entregado') ? 'is-invalid' : '' }}" type="checkbox" name="dni_entregado" id="dni_entregado" value="1" {{ old('dni_entregado') ? 'checked' : '' }}>
                        <label class="form-check-label" for="dni_entregado">Sí</label>
                        @error('dni_entregado')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="enviado_webpol" class="form-label">Enviado Webpol</label>
                    <div class="form-check">
                        <input class="form-check-input {{ $errors->has('enviado_webpol') ? 'is-invalid' : '' }}" type="checkbox" name="enviado_webpol" id="enviado_webpol" value="1" {{ old('enviado_webpol') ? 'checked' : '' }}>
                        <label class="form-check-label" for="enviado_webpol">Sí</label>
                        @error('enviado_webpol')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-terminar w-100 fs-4 mt-4">Añadir</button>
            </form>

        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Inicializar Select2 para el campo cliente
        $('#cliente_id').select2({
            placeholder: "Seleccione un cliente",
            allowClear: true,
            width: '100%' // Asegura que el select ocupa el ancho completo del contenedor
        });
    });
</script>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar Flatpickr en los campos de fecha con localización en español
        flatpickr("#fecha_entrada", {
            dateFormat: "Y-m-d",
            locale: "es", // Configurar el idioma español usando "es"
            onChange: function(selectedDates, dateStr, instance) {
                document.getElementById('fecha_entrada').value = dateStr; // Actualizar el valor del input
            }
        });

        flatpickr("#fecha_salida", {
            dateFormat: "Y-m-d",
            locale: "es", // Configurar el idioma español usando "es"
            onChange: function(selectedDates, dateStr, instance) {
                document.getElementById('fecha_salida').value = dateStr; // Actualizar el valor del input
            }
        });
    });
    </script>
@endsection
