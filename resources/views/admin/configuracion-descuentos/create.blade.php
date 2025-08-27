@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus mr-2"></i>
                        Nueva Configuración de Descuento
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('configuracion-descuentos.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Volver
                        </a>
                    </div>
                </div>
                <form action="{{ route('configuracion-descuentos.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombre">Nombre de la Configuración *</label>
                                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                           id="nombre" name="nombre" value="{{ old('nombre') }}" 
                                           placeholder="Ej: Descuento Temporada Baja" required>
                                    @error('nombre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="porcentaje_descuento">Porcentaje de Descuento *</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('porcentaje_descuento') is-invalid @enderror" 
                                               id="porcentaje_descuento" name="porcentaje_descuento" 
                                               value="{{ old('porcentaje_descuento', 20) }}" 
                                               min="0" max="100" step="0.01" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    @error('porcentaje_descuento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        El descuento se aplicará sobre el precio base de la tarifa.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" name="descripcion" rows="3" 
                                      placeholder="Describe las condiciones y criterios para aplicar este descuento...">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="activo" name="activo" 
                                       {{ old('activo', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="activo">
                                    Configuración Activa
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Solo las configuraciones activas se utilizarán para aplicar descuentos automáticos.
                            </small>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-cogs mr-2"></i>
                                    Condiciones Avanzadas
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="dia_semana">Día de la Semana</label>
                                            <select class="form-control" id="dia_semana" name="condiciones[dia_semana]">
                                                <option value="monday" {{ old('condiciones.dia_semana', 'friday') == 'monday' ? 'selected' : '' }}>Lunes</option>
                                                <option value="tuesday" {{ old('condiciones.dia_semana', 'friday') == 'tuesday' ? 'selected' : '' }}>Martes</option>
                                                <option value="wednesday" {{ old('condiciones.dia_semana', 'friday') == 'wednesday' ? 'selected' : '' }}>Miércoles</option>
                                                <option value="thursday" {{ old('condiciones.dia_semana', 'friday') == 'thursday' ? 'selected' : '' }}>Jueves</option>
                                                <option value="friday" {{ old('condiciones.dia_semana', 'friday') == 'friday' ? 'selected' : '' }}>Viernes</option>
                                                <option value="saturday" {{ old('condiciones.dia_semana', 'friday') == 'saturday' ? 'selected' : '' }}>Sábado</option>
                                                <option value="sunday" {{ old('condiciones.dia_semana', 'friday') == 'sunday' ? 'selected' : '' }}>Domingo</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="temporada">Temporada</label>
                                            <select class="form-control" id="temporada" name="condiciones[temporada]">
                                                <option value="baja" {{ old('condiciones.temporada', 'baja') == 'baja' ? 'selected' : '' }}>Temporada Baja</option>
                                                <option value="alta" {{ old('condiciones.temporada', 'baja') == 'alta' ? 'selected' : '' }}>Temporada Alta</option>
                                                <option value="media" {{ old('condiciones.temporada', 'baja') == 'media' ? 'selected' : '' }}>Temporada Media</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="dias_minimos_libres">Días Mínimos Libres</label>
                                            <input type="number" class="form-control" id="dias_minimos_libres" 
                                                   name="condiciones[dias_minimos_libres]" 
                                                   value="{{ old('condiciones.dias_minimos_libres', 1) }}" 
                                                   min="1" max="7">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('configuracion-descuentos.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times mr-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>
                                Guardar Configuración
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Preview del descuento
    $('#porcentaje_descuento').on('input', function() {
        const porcentaje = parseFloat($(this).val()) || 0;
        const precioEjemplo = 100;
        const precioConDescuento = precioEjemplo * (1 - porcentaje / 100);
        
        // Actualizar ejemplo en tiempo real
        $('.form-text').html(`
            El descuento se aplicará sobre el precio base de la tarifa.<br>
            <strong>Ejemplo:</strong> Precio de 100€ con ${porcentaje}% de descuento = ${precioConDescuento.toFixed(2)}€
        `);
    });

    // Trigger inicial
    $('#porcentaje_descuento').trigger('input');
});
</script>
@endpush
