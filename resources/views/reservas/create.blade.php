@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <h2 class="mb-3">{{ __('Nueva Reserva') }}</h2>
    <hr class="mb-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <form action="{{ route('reservas.store') }}" method="POST">
                @csrf
                <div class="form-group">
                  <label for="cliente_id">Cliente ID</label>
                  <select class="form-control select2" name="cliente_id" id="cliente_id" required>
                      <option value="">Seleccione un cliente</option>
                      @foreach($clientes as $cliente)
                          <option value="{{ $cliente->id }}">{{ $cliente->nombre }} ({{ $cliente->email }})</option>
                      @endforeach
                  </select>
                </div>
              
                <div class="form-group">
                    <label for="apartamento_id">Apartamento ID</label>
                    <input type="number" class="form-control" name="apartamento_id" required>
                </div>
                <div class="form-group">
                    <label for="estado_id">Estado ID</label>
                    <input type="number" class="form-control" name="estado_id" required>
                </div>
                <div class="form-group">
                    <label for="origen">Origen</label>
                    <input type="text" class="form-control" name="origen">
                </div>
                <div class="form-group">
                    <label for="fecha_entrada">Fecha de Entrada</label>
                    <input type="date" class="form-control" name="fecha_entrada" required>
                </div>
                <div class="form-group">
                    <label for="fecha_salida">Fecha de Salida</label>
                    <input type="date" class="form-control" name="fecha_salida" required>
                </div>
                <div class="form-group">
                    <label for="precio">Precio</label>
                    <input type="text" class="form-control" name="precio">
                </div>
                <div class="form-group">
                    <label for="verificado">Verificado</label>
                    <input type="text" class="form-control" name="verificado">
                </div>
                <div class="form-group">
                    <label for="dni_entregado">DNI Entregado</label>
                    <input type="text" class="form-control" name="dni_entregado">
                </div>
                <div class="form-group">
                    <label for="enviado_webpol">Enviado Webpol</label>
                    <input type="text" class="form-control" name="enviado_webpol">
                </div>
                <div class="form-group">
                    <label for="codigo_reserva">Código de Reserva</label>
                    <input type="text" class="form-control" name="codigo_reserva">
                </div>
                <div class="form-group">
                    <label for="fecha_limpieza">Fecha de Limpieza</label>
                    <input type="date" class="form-control" name="fecha_limpieza">
                </div>
                <button type="submit" class="btn btn-primary">Crear Reserva</button>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Selecciona un cliente",
            allowClear: true
        });
    });
</script>
@endsection
