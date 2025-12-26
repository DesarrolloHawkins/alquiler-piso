@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1 text-dark fw-bold">
                <i class="fas fa-plus me-2 text-primary"></i>
                Nuevo Cupón de Descuento
            </h1>
            <p class="text-muted mb-0">Crea un nuevo cupón de descuento para aplicar en reservas</p>
        </div>
        <a href="{{ route('admin.cupones.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Volver
        </a>
    </div>

    <hr class="mb-4">

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-exclamation-triangle me-2"></i>Errores de validación:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form action="{{ route('admin.cupones.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="codigo" class="form-label fw-semibold">
                                    <i class="fas fa-barcode me-1 text-primary"></i>
                                    Código del Cupón <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('codigo') is-invalid @enderror" 
                                       id="codigo" 
                                       name="codigo" 
                                       value="{{ old('codigo') }}"
                                       placeholder="Ej: VERANO2025, DESCUENTO10"
                                       style="text-transform: uppercase;"
                                       required>
                                <small class="form-text text-muted">
                                    Código único que el cliente introducirá (se convertirá a mayúsculas).
                                </small>
                                @error('codigo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="nombre" class="form-label fw-semibold">
                                    <i class="fas fa-tag me-1 text-primary"></i>
                                    Nombre <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('nombre') is-invalid @enderror" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="{{ old('nombre') }}"
                                       placeholder="Ej: Descuento Verano 2025"
                                       required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="descripcion" class="form-label fw-semibold">
                                <i class="fas fa-align-left me-1 text-primary"></i>
                                Descripción
                            </label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" 
                                      name="descripcion" 
                                      rows="3"
                                      placeholder="Descripción opcional del cupón">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="tipo" class="form-label fw-semibold">
                                    <i class="fas fa-percent me-1 text-primary"></i>
                                    Tipo de Descuento <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('tipo') is-invalid @enderror" 
                                        id="tipo" 
                                        name="tipo" 
                                        required
                                        onchange="toggleDescuentoMaximo()">
                                    <option value="porcentaje" {{ old('tipo') === 'porcentaje' ? 'selected' : '' }}>Porcentaje (%)</option>
                                    <option value="fijo" {{ old('tipo') === 'fijo' ? 'selected' : '' }}>Cantidad Fija (€)</option>
                                </select>
                                @error('tipo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="valor" class="form-label fw-semibold">
                                    <i class="fas fa-euro-sign me-1 text-primary"></i>
                                    Valor <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('valor') is-invalid @enderror" 
                                       id="valor" 
                                       name="valor" 
                                       value="{{ old('valor') }}"
                                       step="0.01"
                                       min="0"
                                       placeholder="10 o 25.50"
                                       required>
                                <small class="form-text text-muted" id="valorHelp">
                                    Si es porcentaje: 10 = 10%. Si es fijo: 25.50 = 25.50€.
                                </small>
                                @error('valor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4" id="descuentoMaximoContainer" style="display: none;">
                                <label for="descuento_maximo" class="form-label fw-semibold">
                                    <i class="fas fa-euro-sign me-1 text-primary"></i>
                                    Descuento Máximo (€)
                                </label>
                                <input type="number" 
                                       class="form-control @error('descuento_maximo') is-invalid @enderror" 
                                       id="descuento_maximo" 
                                       name="descuento_maximo" 
                                       value="{{ old('descuento_maximo') }}"
                                       step="0.01"
                                       min="0"
                                       placeholder="50.00">
                                <small class="form-text text-muted">
                                    Límite máximo del descuento si es porcentaje (ej: máximo 50€ aunque sea 20%).
                                </small>
                                @error('descuento_maximo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="importe_minimo" class="form-label fw-semibold">
                                    <i class="fas fa-euro-sign me-1 text-primary"></i>
                                    Importe Mínimo (€)
                                </label>
                                <input type="number" 
                                       class="form-control @error('importe_minimo') is-invalid @enderror" 
                                       id="importe_minimo" 
                                       name="importe_minimo" 
                                       value="{{ old('importe_minimo') }}"
                                       step="0.01"
                                       min="0"
                                       placeholder="100.00">
                                <small class="form-text text-muted">
                                    Importe mínimo de reserva para poder usar este cupón (opcional).
                                </small>
                                @error('importe_minimo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="fecha_inicio" class="form-label fw-semibold">
                                    <i class="fas fa-calendar-alt me-1 text-primary"></i>
                                    Fecha de Inicio <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control @error('fecha_inicio') is-invalid @enderror" 
                                       id="fecha_inicio" 
                                       name="fecha_inicio" 
                                       value="{{ old('fecha_inicio') }}"
                                       required>
                                @error('fecha_inicio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="fecha_fin" class="form-label fw-semibold">
                                    <i class="fas fa-calendar-alt me-1 text-primary"></i>
                                    Fecha de Fin <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control @error('fecha_fin') is-invalid @enderror" 
                                       id="fecha_fin" 
                                       name="fecha_fin" 
                                       value="{{ old('fecha_fin') }}"
                                       required>
                                @error('fecha_fin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <label for="usos_maximos" class="form-label fw-semibold">
                                    <i class="fas fa-users me-1 text-primary"></i>
                                    Usos Máximos
                                </label>
                                <input type="number" 
                                       class="form-control @error('usos_maximos') is-invalid @enderror" 
                                       id="usos_maximos" 
                                       name="usos_maximos" 
                                       value="{{ old('usos_maximos') }}"
                                       min="1"
                                       placeholder="Dejar vacío = ilimitado">
                                <small class="form-text text-muted">
                                    Número máximo de veces que se puede usar este cupón (dejar vacío = ilimitado).
                                </small>
                                @error('usos_maximos')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-4">
                                <label for="usos_por_cliente" class="form-label fw-semibold">
                                    <i class="fas fa-user me-1 text-primary"></i>
                                    Usos por Cliente <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('usos_por_cliente') is-invalid @enderror" 
                                       id="usos_por_cliente" 
                                       name="usos_por_cliente" 
                                       value="{{ old('usos_por_cliente', 1) }}"
                                       min="1"
                                       required>
                                <small class="form-text text-muted">
                                    Cuántas veces puede usar el mismo cliente este cupón.
                                </small>
                                @error('usos_por_cliente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-toggle-on me-1 text-primary"></i>
                                    Estado
                                </label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="activo" 
                                           name="activo" 
                                           {{ old('activo', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="activo">
                                        Cupón activo
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    Solo los cupones activos se pueden usar.
                                </small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="apartamentos_ids" class="form-label fw-semibold">
                                <i class="fas fa-building me-1 text-primary"></i>
                                Apartamentos Específicos
                            </label>
                            <select class="form-select @error('apartamentos_ids') is-invalid @enderror" 
                                    id="apartamentos_ids" 
                                    name="apartamentos_ids[]" 
                                    multiple
                                    size="5">
                                @foreach($apartamentos as $apt)
                                    <option value="{{ $apt->id }}" 
                                            {{ in_array($apt->id, old('apartamentos_ids', [])) ? 'selected' : '' }}>
                                        {{ $apt->titulo }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                Selecciona apartamentos específicos donde aplica este cupón. Si no seleccionas ninguno, aplica a todos.
                            </small>
                            @error('apartamentos_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.cupones.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Guardar Cupón
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        Información
                    </h5>
                    <hr>
                    <p class="card-text">
                        <strong>Tipos de descuento:</strong>
                    </p>
                    <ul class="small text-muted">
                        <li><strong>Porcentaje:</strong> Ej: 10% de descuento</li>
                        <li><strong>Fijo:</strong> Ej: 25€ de descuento</li>
                    </ul>
                    <hr>
                    <p class="card-text">
                        <strong>Ejemplos de códigos:</strong>
                    </p>
                    <ul class="small text-muted">
                        <li>VERANO2025</li>
                        <li>DESCUENTO10</li>
                        <li>BIENVENIDA20</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleDescuentoMaximo() {
        const tipo = document.getElementById('tipo').value;
        const container = document.getElementById('descuentoMaximoContainer');
        const valorHelp = document.getElementById('valorHelp');
        
        if (tipo === 'porcentaje') {
            container.style.display = 'block';
            valorHelp.textContent = 'Si es porcentaje: 10 = 10%. Si es fijo: 25.50 = 25.50€.';
        } else {
            container.style.display = 'none';
            document.getElementById('descuento_maximo').value = '';
            valorHelp.textContent = 'Cantidad fija en euros: 25.50 = 25.50€ de descuento.';
        }
    }

    // Convertir código a mayúsculas
    document.getElementById('codigo')?.addEventListener('input', function(e) {
        this.value = this.value.toUpperCase();
    });

    // Inicializar al cargar
    document.addEventListener('DOMContentLoaded', function() {
        toggleDescuentoMaximo();
    });
</script>
@endpush
@endsection

