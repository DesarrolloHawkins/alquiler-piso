@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="h2 mb-1 text-dark fw-bold">
                        <i class="fas fa-plus-circle me-2 text-primary"></i>
                        Nuevo Edificio
                    </h1>
                    <p class="text-muted mb-0">Crea un nuevo edificio para la plataforma</p>
                </div>
                <a href="{{ route('admin.edificios.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6">
            <!-- Formulario principal -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-semibold text-dark">
                        <i class="fas fa-building me-2 text-primary"></i>
                        Información del Edificio
                    </h5>
                </div>
                
                <div class="card-body p-4">
                    <form action="{{ route('admin.edificio.store') }}" method="POST" id="edificio-form">
                        @csrf
                        
                        <!-- Nombre del edificio -->
                        <div class="mb-4">
                            <label for="nombre" class="form-label fw-semibold text-dark">
                                <i class="fas fa-signature me-2 text-primary"></i>
                                Nombre del Edificio
                            </label>
                            <input type="text" 
                                   class="form-control @error('nombre') is-invalid @enderror" 
                                   id="nombre" 
                                   name="nombre" 
                                   placeholder="Ej: Residencial Los Pinos"
                                   value="{{ old('nombre') }}"
                                   required>
                            <div class="invalid-feedback" id="nombre-error">
                                @error('nombre') {{ $message }} @enderror
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1 text-muted"></i>
                                Nombre descriptivo y fácil de recordar
                            </div>
                        </div>

                        <!-- Clave de acceso -->
                        <div class="mb-4">
                            <label for="clave" class="form-label fw-semibold text-dark">
                                <i class="fas fa-key me-2 text-primary"></i>
                                Clave de Acceso
                            </label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control @error('clave') is-invalid @enderror" 
                                       id="clave" 
                                       name="clave" 
                                       placeholder="Ej: EDIF001"
                                       value="{{ old('clave') }}"
                                       required>
                                <button type="button" 
                                        class="btn btn-outline-secondary" 
                                        id="generate-key"
                                        title="Generar clave automática">
                                    <i class="fas fa-magic"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback" id="clave-error">
                                @error('clave') {{ $message }} @enderror
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1 text-muted"></i>
                                Clave única para identificar el edificio en el sistema
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="d-flex gap-3 pt-3">
                            <button type="submit" class="btn btn-primary btn-lg px-4" id="submit-btn">
                                <i class="fas fa-save me-2"></i>
                                Crear Edificio
                            </button>
                            <a href="{{ route('admin.edificios.index') }}" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="fas fa-times me-2"></i>
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body p-4">
                    <h6 class="fw-semibold text-dark mb-3">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>
                        Consejos para crear edificios
                    </h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle me-2 text-success"></i>
                            Usa nombres descriptivos y fáciles de recordar
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle me-2 text-success"></i>
                            Las claves deben ser únicas en todo el sistema
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle me-2 text-success"></i>
                            Puedes editar esta información más tarde
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('sweetalert::alert')

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('edificio-form');
    const nombreInput = document.getElementById('nombre');
    const claveInput = document.getElementById('clave');
    const submitBtn = document.getElementById('submit-btn');
    const generateKeyBtn = document.getElementById('generate-key');

    // Generar clave automática
    generateKeyBtn.addEventListener('click', function() {
        const nombre = nombreInput.value.trim();
        if (nombre) {
            const clave = 'EDIF' + Math.random().toString(36).substr(2, 6).toUpperCase();
            claveInput.value = clave;
            claveInput.classList.remove('is-invalid');
            document.getElementById('clave-error').textContent = '';
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Campo requerido',
                text: 'Primero debes ingresar el nombre del edificio',
                confirmButtonColor: '#6c757d'
            });
        }
    });

    // Validación en tiempo real
    function validateField(input, errorId, validationFn) {
        const value = input.value.trim();
        const errorElement = document.getElementById(errorId);
        
        if (validationFn(value)) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            errorElement.textContent = '';
            return true;
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            return false;
        }
    }

    // Validar nombre
    nombreInput.addEventListener('input', function() {
        validateField(this, 'nombre-error', function(value) {
            return value.length >= 3 && value.length <= 255;
        });
    });

    // Validar clave
    claveInput.addEventListener('input', function() {
        validateField(this, 'clave-error', function(value) {
            return value.length >= 3 && value.length <= 255;
        });
    });

    // Validación del formulario
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validar nombre
        if (!validateField(nombreInput, 'nombre-error', function(value) {
            return value.length >= 3 && value.length <= 255;
        })) {
            isValid = false;
        }
        
        // Validar clave
        if (!validateField(claveInput, 'clave-error', function(value) {
            return value.length >= 3 && value.length <= 255;
        })) {
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Formulario incompleto',
                text: 'Por favor, completa todos los campos correctamente',
                confirmButtonColor: '#dc3545'
            });
            return false;
        }
        
        // Mostrar confirmación
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creando...';
        
        Swal.fire({
            title: 'Creando edificio...',
            text: 'Por favor espera mientras se procesa la información',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    });

    // Mostrar mensajes de SweetAlert
    @if(session('swal_success'))
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: '{{ session('swal_success') }}',
            timer: 3000,
            timerProgressBar: true,
            toast: true,
            position: 'top-end',
            showConfirmButton: false
        });
    @endif

    @if(session('swal_error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('swal_error') }}',
            timer: 5000,
            timerProgressBar: true,
            toast: true,
            position: 'top-end',
            showConfirmButton: false
        });
    @endif

    // Mostrar errores de validación del servidor
    @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Errores de validación',
            html: `@foreach($errors->all() as $error)<div class="text-start">• {{ $error }}</div>@endforeach`,
            confirmButtonColor: '#dc3545'
        });
    @endif
});
</script>
@endsection

