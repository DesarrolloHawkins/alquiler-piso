@extends('layouts.appAdmin')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<div class="container-fluid">
    <h2 class="mb-3">{{ __('Nueva Reserva') }}</h2>
    <hr class="mb-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <form action="{{ route('reservas.store') }}" method="POST">
                @csrf
                <div class="form-group mb-3">
                  <label for="cliente_id">Cliente ID</label>
                  <select class="form-control select2" name="cliente_id" id="cliente_id" required>
                      <option value="">Seleccione un cliente</option>
                      @foreach($clientes as $cliente)
                          <option value="{{ $cliente->id }}">{{ $cliente->alias }}</option>
                      @endforeach
                  </select>
                </div>
              
                <div class="form-group mb-3">
                  <label for="apartamento_id">Apartamento</label>
                  <select class="form-control" name="apartamento_id" id="apartamento_id" required>
                      <option value="">Seleccione un apartamento</option>
                      @foreach($apartamentos as $apartamento)
                          <option value="{{ $apartamento->id }}">{{ $apartamento->titulo }}</option>
                      @endforeach
                  </select>
              </div>
              
                {{-- <div class="form-group">
                    <label for="estado_id">Estado ID</label>
                    <input type="number" class="form-control" name="estado_id" required>
                </div> --}}
                <div class="form-group mb-3">
                  <label for="origen">Origen</label>
                  <select class="form-control" name="origen" id="origen">
                      <option value="">Seleccione el origen</option>
                      <option value="Airbnb">Airbnb</option>
                      <option value="Booking">Booking</option>
                      <option value="Web">Web</option>
                  </select>
                </div>
                <div class="form-group mb-3">
                  <label for="codigo_reserva">Código de Reserva</label>
                  <input type="text" class="form-control" name="codigo_reserva">
                </div>
                <div class="form-group mb-3">
                    <label for="fecha_entrada">Fecha de Entrada</label>
                    <input type="date" class="form-control" name="fecha_entrada" required>
                </div>
                <div class="form-group mb-3">
                    <label for="fecha_salida">Fecha de Salida</label>
                    <input type="date" class="form-control" name="fecha_salida" required>
                </div>
                <div class="form-group mb-3">
                    <label for="precio">Precio</label>
                    <input type="text" class="form-control" name="precio">
                </div>
                <div class="form-group mb-3">
                  <label for="verificado">Verificado</label>
                  <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="verificado" id="verificado" value="1" {{ old('verificado') ? 'checked' : '' }}>
                      <label class="form-check-label" for="verificado">
                          Sí
                      </label>
                  </div>
              </div>
              
              <div class="form-group mb-3">
                  <label for="dni_entregado">DNI Entregado</label>
                  <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="dni_entregado" id="dni_entregado" value="1" {{ old('dni_entregado') ? 'checked' : '' }}>
                      <label class="form-check-label" for="dni_entregado">
                          Sí
                      </label>
                  </div>
              </div>
              
              <div class="form-group mb-3">
                  <label for="enviado_webpol">Enviado Webpol</label>
                  <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="enviado_webpol" id="enviado_webpol" value="1" {{ old('enviado_webpol') ? 'checked' : '' }}>
                      <label class="form-check-label" for="enviado_webpol">
                          Sí
                      </label>
                  </div>
              </div>
              
                
                {{-- <div class="form-group mb-3">
                    <label for="fecha_limpieza">Fecha de Limpieza</label>
                    <input type="date" class="form-control" name="fecha_limpieza">
                </div> --}}
                <button type="submit" class="btn btn-primary">Crear Reserva</button>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Selecciona un cliente",
            allowClear: true
        });
    });
</script>
@endsection
