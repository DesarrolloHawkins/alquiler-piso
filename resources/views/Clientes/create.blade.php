@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="h2 mb-1 text-dark fw-bold">
                        <i class="fas fa-user-plus me-2 text-primary"></i>
                        Crear Nuevo Cliente
                    </h1>
                    <p class="text-muted mb-0">Registra un nuevo cliente en el sistema</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver al Listado
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <div class="row">
        <div class="col-12">
            <form action="{{ route('clientes.store') }}" method="POST" id="clienteForm">
                @csrf
                
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
                                <label for="nombre" class="form-label fw-semibold">
                                    Nombre <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('nombre') is-invalid @enderror" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="{{ old('nombre') }}"
                                       maxlength="255"
                                       placeholder="Nombre del cliente"
                                       required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-lightbulb me-1 text-info"></i>
                                    Nombre completo del cliente
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="apellido1" class="form-label fw-semibold">
                                    Primer Apellido <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('apellido1') is-invalid @enderror" 
                                       id="apellido1" 
                                       name="apellido1" 
                                       value="{{ old('apellido1') }}"
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
                                       value="{{ old('apellido2') }}"
                                       maxlength="255"
                                       placeholder="Segundo apellido (opcional)">
                                @error('apellido2')
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
                                    <option value="Masculino" {{ old('sexo') == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                    <option value="Femenino" {{ old('sexo') == 'Femenino' ? 'selected' : '' }}>Femenino</option>
                                    <option value="No especificado" {{ old('sexo') == 'No especificado' ? 'selected' : '' }}>No especificado</option>
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
                                           value="{{ old('email') }}"
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
                                <label for="telefono" class="form-label fw-semibold">
                                    Teléfono <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-phone"></i>
                                    </span>
                                    <input type="tel" 
                                           class="form-control @error('telefono') is-invalid @enderror" 
                                           id="telefono" 
                                           name="telefono" 
                                           value="{{ old('telefono') }}"
                                           maxlength="20"
                                           placeholder="+34 600 000 000"
                                           required>
                                </div>
                                @error('telefono')
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
                                        <option value="{{ $pais }}" {{ old('nacionalidad') == $pais ? 'selected' : '' }}>
                                            {{ $pais }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('nacionalidad')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="idiomas" class="form-label fw-semibold">Idiomas</label>
                                <input type="text" 
                                       class="form-control @error('idiomas') is-invalid @enderror" 
                                       id="idiomas" 
                                       name="idiomas" 
                                       value="{{ old('idiomas') }}"
                                       maxlength="255"
                                       placeholder="Español, Inglés, Francés...">
                                @error('idiomas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-language me-1 text-info"></i>
                                    Idiomas que domina el cliente (opcional)
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
                                       value="{{ old('direccion') }}"
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
                                       value="{{ old('localidad') }}"
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
                                       value="{{ old('codigo_postal') }}"
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
                                       value="{{ old('provincia') }}"
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
                                       value="{{ old('estado') }}"
                                       maxlength="255"
                                       placeholder="Estado o región (opcional)">
                                @error('estado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información Adicional -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-semibold text-dark">
                            <i class="fas fa-plus-circle me-2 text-primary"></i>
                            Información Adicional
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="tipo_documento" class="form-label fw-semibold">Tipo de Documento</label>
                                <select class="form-select @error('tipo_documento') is-invalid @enderror" 
                                        id="tipo_documento" 
                                        name="tipo_documento">
                                    <option value="">Seleccionar tipo</option>
                                    <option value="DNI" {{ old('tipo_documento') == 'DNI' ? 'selected' : '' }}>DNI</option>
                                    <option value="Pasaporte" {{ old('tipo_documento') == 'Pasaporte' ? 'selected' : '' }}>Pasaporte</option>
                                    <option value="NIE" {{ old('tipo_documento') == 'NIE' ? 'selected' : '' }}>NIE</option>
                                    <option value="Otro" {{ old('tipo_documento') == 'Otro' ? 'selected' : '' }}>Otro</option>
                                </select>
                                @error('tipo_documento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-id-card me-1 text-info"></i>
                                    Tipo de documento de identidad (opcional)
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="alias" class="form-label fw-semibold">Alias</label>
                                <input type="text" 
                                       class="form-control @error('alias') is-invalid @enderror" 
                                       id="alias" 
                                       name="alias" 
                                       value="{{ old('alias') }}"
                                       maxlength="255"
                                       placeholder="Apodo o nombre preferido">
                                @error('alias')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="fas fa-tag me-1 text-info"></i>
                                    Nombre preferido o apodo del cliente
                                </div>
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
                        <i class="fas fa-save me-2"></i>Crear Cliente
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
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creando...';
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
