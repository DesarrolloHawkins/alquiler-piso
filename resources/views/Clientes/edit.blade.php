@extends('layouts.appAdmin')

@php
    // Helper para formatear fechas de forma segura
    function formatDate($date, $format = 'Y-m-d') {
        if (!$date) return '';
        if (is_object($date)) {
            return $date->format($format);
        }
        try {
            return \Carbon\Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return '';
        }
    }
@endphp

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="h2 mb-1 text-dark fw-bold">
                        <i class="fas fa-user-edit me-2 text-primary"></i>
                        Editar Cliente: {{ $cliente->nombre }} {{ $cliente->apellido1 }}
                    </h1>
                    <p class="text-muted mb-0">Modifica la información del cliente seleccionado</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('clientes.show', $cliente->id) }}" class="btn btn-outline-info">
                        <i class="fas fa-eye me-2"></i>Ver Detalles
                    </a>
                    <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver al Listado
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas rápidas del cliente -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card shadow-sm border-0 bg-gradient-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-check fa-2x mb-2"></i>
                    <h4 class="mb-1">{{ $reservas->count() }}</h4>
                    <small>Total Reservas</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card shadow-sm border-0 bg-gradient-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-envelope fa-2x mb-2"></i>
                    <h4 class="mb-1">{{ $mensajes->count() }}</h4>
                    <small>Mensajes</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card shadow-sm border-0 bg-gradient-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-camera fa-2x mb-2"></i>
                    <h4 class="mb-1">{{ $photos->count() }}</h4>
                    <small>Fotos</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card shadow-sm border-0 bg-gradient-{{ $cliente->inactivo ? 'danger' : 'success' }} text-white">
                <div class="card-body text-center">
                    <i class="fas fa-{{ $cliente->inactivo ? 'times-circle' : 'check-circle' }} fa-2x mb-2"></i>
                    <h4 class="mb-1">{{ $cliente->inactivo ? 'Inactivo' : 'Activo' }}</h4>
                    <small>Estado</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <div class="row">
        <div class="col-12">
            <form action="{{ route('clientes.update', $cliente->id) }}" method="POST" id="clienteForm">
                @csrf
                @method('PUT')
                
                <!-- Información Personal -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-semibold text-dark">
                            <i class="fas fa-user me-2 text-primary"></i>
                            Información Personal
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="alias" class="form-label fw-semibold">
                                    Alias <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('alias') is-invalid @enderror" 
                                       id="alias" 
                                       name="alias" 
                                       value="{{ old('alias', $cliente->alias) }}"
                                       maxlength="255"
                                       placeholder="Apodo o nombre preferido"
                                       required>
                                @error('alias')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-tag me-1 text-info"></i>
                                    Nombre preferido o apodo del cliente
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="nombre" class="form-label fw-semibold">
                                    Nombre <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('nombre') is-invalid @enderror" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="{{ old('nombre', $cliente->nombre) }}"
                                       maxlength="255"
                                       placeholder="Nombre del cliente"
                                       required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="apellido1" class="form-label fw-semibold">
                                    Primer Apellido <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('apellido1') is-invalid @enderror" 
                                       id="apellido1" 
                                       name="apellido1" 
                                       value="{{ old('apellido1', $cliente->apellido1) }}"
                                       maxlength="255"
                                       placeholder="Primer apellido"
                                       required>
                                @error('apellido1')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="apellido2" class="form-label fw-semibold">Segundo Apellido</label>
                                <input type="text" 
                                       class="form-control @error('apellido2') is-invalid @enderror" 
                                       id="apellido2" 
                                       name="apellido2" 
                                       value="{{ old('apellido2', $cliente->apellido2) }}"
                                       maxlength="255"
                                       placeholder="Segundo apellido (opcional)">
                                @error('apellido2')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="fecha_nacimiento" class="form-label fw-semibold">
                                    Fecha de Nacimiento <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control @error('fecha_nacimiento') is-invalid @enderror" 
                                       id="fecha_nacimiento" 
                                       name="fecha_nacimiento" 
                                       value="{{ old('fecha_nacimiento', formatDate($cliente->fecha_nacimiento)) }}"
                                       required>
                                @error('fecha_nacimiento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="sexo" class="form-label fw-semibold">
                                    Sexo <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('sexo') is-invalid @enderror" 
                                        id="sexo" 
                                        name="sexo" 
                                        required>
                                    <option value="">Seleccionar sexo</option>
                                    <option value="Masculino" {{ old('sexo', $cliente->sexo) == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                    <option value="Femenino" {{ old('sexo', $cliente->sexo) == 'Femenino' ? 'selected' : '' }}>Femenino</option>
                                    <option value="No especificado" {{ old('sexo', $cliente->sexo) == 'No especificado' ? 'selected' : '' }}>No especificado</option>
                                </select>
                                @error('sexo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de Contacto -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-semibold text-dark">
                            <i class="fas fa-address-book me-2 text-primary"></i>
                            Información de Contacto
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $cliente->email) }}"
                                           maxlength="255"
                                           placeholder="correo@ejemplo.com"
                                           required>
                                </div>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1 text-info"></i>
                                    Email único para identificación
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="telefono" class="form-label fw-semibold">Teléfono</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-phone"></i>
                                    </span>
                                    <input type="tel" 
                                           class="form-control @error('telefono') is-invalid @enderror" 
                                           id="telefono" 
                                           name="telefono" 
                                           value="{{ old('telefono', $cliente->telefono) }}"
                                           maxlength="20"
                                           placeholder="+34 600 000 000">
                                </div>
                                @error('telefono')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de Documentación -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-semibold text-dark">
                            <i class="fas fa-id-card me-2 text-primary"></i>
                            Documentación
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="tipo_documento" class="form-label fw-semibold">
                                    Tipo de Documento <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('tipo_documento') is-invalid @enderror" 
                                        id="tipo_documento" 
                                        name="tipo_documento" 
                                        required>
                                    <option value="">Seleccionar tipo</option>
                                    <option value="DNI" {{ old('tipo_documento', $cliente->tipo_documento) == 'DNI' ? 'selected' : '' }}>DNI</option>
                                    <option value="Pasaporte" {{ old('tipo_documento', $cliente->tipo_documento) == 'Pasaporte' ? 'selected' : '' }}>Pasaporte</option>
                                </select>
                                @error('tipo_documento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="num_identificacion" class="form-label fw-semibold">
                                    Número de Identificación <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('num_identificacion') is-invalid @enderror" 
                                       id="num_identificacion" 
                                       name="num_identificacion" 
                                       value="{{ old('num_identificacion', $cliente->num_identificacion) }}"
                                       maxlength="255"
                                       placeholder="Número del documento"
                                       required>
                                @error('num_identificacion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="fecha_expedicion_doc" class="form-label fw-semibold">
                                    Fecha de Expedición <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control @error('fecha_expedicion_doc') is-invalid @enderror" 
                                       id="fecha_expedicion_doc" 
                                       name="fecha_expedicion_doc" 
                                       value="{{ old('fecha_expedicion_doc', formatDate($cliente->fecha_expedicion_doc)) }}"
                                       required>
                                @error('fecha_expedicion_doc')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de Nacionalidad e Idiomas -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-semibold text-dark">
                            <i class="fas fa-globe me-2 text-primary"></i>
                            Nacionalidad e Idiomas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nacionalidad" class="form-label fw-semibold">
                                    Nacionalidad <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('nacionalidad') is-invalid @enderror" 
                                        id="nacionalidad" 
                                        name="nacionalidad" 
                                        required>
                                    <option value="">Seleccionar nacionalidad</option>
                                    @foreach($paises as $pais)
                                        <option value="{{ $pais }}" {{ old('nacionalidad', $cliente->nacionalidad) == $pais ? 'selected' : '' }}>
                                            {{ $pais }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('nacionalidad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="idiomas" class="form-label fw-semibold">
                                    Idiomas <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('idiomas') is-invalid @enderror" 
                                       id="idiomas" 
                                       name="idiomas" 
                                       value="{{ old('idiomas', $cliente->idiomas) }}"
                                       maxlength="255"
                                       placeholder="Español, Inglés, Francés..."
                                       required>
                                @error('idiomas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-language me-1 text-info"></i>
                                    Idiomas que domina el cliente
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de Dirección -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-semibold text-dark">
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                            Información de Dirección
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="direccion" class="form-label fw-semibold">Dirección</label>
                                <input type="text" 
                                       class="form-control @error('direccion') is-invalid @enderror" 
                                       id="direccion" 
                                       name="direccion" 
                                       value="{{ old('direccion', $cliente->direccion) }}"
                                       maxlength="255"
                                       placeholder="Calle, número, piso...">
                                @error('direccion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label for="localidad" class="form-label fw-semibold">Localidad</label>
                                <input type="text" 
                                       class="form-control @error('localidad') is-invalid @enderror" 
                                       id="localidad" 
                                       name="localidad" 
                                       value="{{ old('localidad', $cliente->localidad) }}"
                                       maxlength="255"
                                       placeholder="Ciudad o pueblo">
                                @error('localidad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label for="codigo_postal" class="form-label fw-semibold">Código Postal</label>
                                <input type="text" 
                                       class="form-control @error('codigo_postal') is-invalid @enderror" 
                                       id="codigo_postal" 
                                       name="codigo_postal" 
                                       value="{{ old('codigo_postal', $cliente->codigo_postal) }}"
                                       maxlength="10"
                                       placeholder="28001">
                                @error('codigo_postal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label for="provincia" class="form-label fw-semibold">Provincia</label>
                                <input type="text" 
                                       class="form-control @error('provincia') is-invalid @enderror" 
                                       id="provincia" 
                                       name="provincia" 
                                       value="{{ old('provincia', $cliente->provincia) }}"
                                       maxlength="255"
                                       placeholder="Madrid, Barcelona...">
                                @error('provincia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="estado" class="form-label fw-semibold">Estado/Región</label>
                                <input type="text" 
                                       class="form-control @error('estado') is-invalid @enderror" 
                                       id="estado" 
                                       name="estado" 
                                       value="{{ old('estado', $cliente->estado) }}"
                                       maxlength="255"
                                       placeholder="Estado o región (opcional)">
                                @error('estado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg" id="btnSubmit">
                        <i class="fas fa-save me-2"></i>Actualizar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('clienteForm');
    const inputs = form.querySelectorAll('input, select, textarea');
    
    // Validación en tiempo real
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.hasAttribute('required') && !this.value.trim()) {
                this.classList.add('is-invalid');
            } else if (this.classList.contains('is-invalid')) {
                this.classList.remove('is-invalid');
            }
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                this.classList.remove('is-invalid');
            }
        });
    });

    // Envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validar campos requeridos
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            }
        });
        
        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Campos requeridos',
                text: 'Por favor, completa todos los campos obligatorios.',
                confirmButtonColor: '#d33'
            });
            return;
        }
        
        // Mostrar loading
        const btnSubmit = document.getElementById('btnSubmit');
        const originalText = btnSubmit.innerHTML;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Actualizando...';
        btnSubmit.disabled = true;
        
        // Enviar formulario
        form.submit();
    });

    // SweetAlert para mensajes de sesión
    @if(session('swal_success'))
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: '{{ session('swal_success') }}',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Aceptar'
        });
    @endif

    @if(session('swal_error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('swal_error') }}',
            confirmButtonColor: '#d33',
            confirmButtonText: 'Aceptar'
        });
    @endif

    // Mostrar errores de validación
    @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Errores de validación',
            text: 'Por favor, corrige los errores en el formulario.',
            confirmButtonColor: '#d33',
            confirmButtonText: 'Aceptar'
        });
    @endif
});
</script>

<style>
.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border-radius: 12px;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
    border-radius: 12px 12px 0 0 !important;
}

.form-control, .form-select {
    border-radius: 8px;
    border: 1px solid #e3e6f0;
    transition: all 0.2s ease-in-out;
    padding: 0.75rem 1rem;
}

.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.form-label {
    color: #495057;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    transition: all 0.2s ease-in-out;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.btn-lg {
    padding: 1rem 2rem;
    font-size: 1.1rem;
}

.input-group-text {
    background-color: #f8f9fa;
    border: 1px solid #e3e6f0;
    color: #6c757d;
}

.invalid-feedback {
    font-size: 0.875rem;
    color: #dc3545;
}

.text-danger {
    color: #dc3545 !important;
}

.bg-gradient-info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.bg-gradient-danger {
    background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
}

/* Animaciones */
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }
    
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .d-flex.justify-content-between .btn {
        width: 100%;
    }
}
</style>
@endsection
