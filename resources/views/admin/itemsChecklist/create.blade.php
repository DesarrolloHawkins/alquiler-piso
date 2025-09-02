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
                        Nuevo Item
                    </h1>
                    <p class="text-muted mb-0">Crea un nuevo item para el checklist</p>
                </div>
                <a href="{{ route('admin.itemsChecklist.index', ['id' => $checklist->id]) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6">
            <!-- Información del checklist -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="avatar-lg bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="fas fa-clipboard-check fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-semibold text-dark">{{ $checklist->nombre }}</h5>
                            <p class="text-muted mb-0">
                                <i class="fas fa-building me-1"></i>
                                {{ $checklist->edificio->nombre }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario principal -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-semibold text-dark">
                        <i class="fas fa-check-square me-2 text-primary"></i>
                        Información del Item
                    </h5>
                </div>
                
                <div class="card-body p-4">
                    <form action="{{ route('admin.itemsChecklist.store') }}" method="POST" id="item-form">
                        @csrf
                        <input type="hidden" name="checklistId" value="{{ $checklist->id }}">
                        
                        <!-- Nombre del item -->
                        <div class="mb-4">
                            <label for="nombre" class="form-label fw-semibold text-dark">
                                <i class="fas fa-signature me-2 text-primary"></i>
                                Nombre del Item
                            </label>
                            <input type="text" 
                                   class="form-control @error('nombre') is-invalid @enderror" 
                                   id="nombre" 
                                   name="nombre" 
                                   placeholder="Ej: Verificar limpieza del baño"
                                   value="{{ old('nombre') }}"
                                   required>
                            <div class="invalid-feedback" id="nombre-error">
                                @error('nombre') {{ $message }} @enderror
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1 text-muted"></i>
                                Nombre descriptivo del item a verificar
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-4">
                            <label for="descripcion" class="form-label fw-semibold text-dark">
                                <i class="fas fa-align-left me-2 text-primary"></i>
                                Descripción
                            </label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" 
                                      name="descripcion" 
                                      rows="3" 
                                      placeholder="Descripción opcional del item...">{{ old('descripcion') }}</textarea>
                            <div class="invalid-feedback" id="descripcion-error">
                                @error('descripcion') {{ $message }} @enderror
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1 text-muted"></i>
                                Descripción opcional para explicar qué verificar
                            </div>
                        </div>

                        <!-- Tipo de item -->
                        <div class="mb-4">
                            <label for="tipo" class="form-label fw-semibold text-dark">
                                <i class="fas fa-tags me-2 text-primary"></i>
                                Tipo de Item
                            </label>
                            <select name="tipo" 
                                    id="tipo" 
                                    class="form-select @error('tipo') is-invalid @enderror">
                                <option value="simple" {{ old('tipo') == 'simple' ? 'selected' : '' }}>Simple (Checkbox)</option>
                                <option value="multiple" {{ old('tipo') == 'multiple' ? 'selected' : '' }}>Múltiple (Selección)</option>
                                <option value="texto" {{ old('tipo') == 'texto' ? 'selected' : '' }}>Texto (Comentario)</option>
                                <option value="foto" {{ old('tipo') == 'foto' ? 'selected' : '' }}>Foto (Evidencia)</option>
                            </select>
                            <div class="invalid-feedback" id="tipo-error">
                                @error('tipo') {{ $message }} @enderror
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1 text-muted"></i>
                                Tipo de respuesta que requiere este item
                            </div>
                        </div>

                        <!-- Orden -->
                        <div class="mb-4">
                            <label for="orden" class="form-label fw-semibold text-dark">
                                <i class="fas fa-sort-numeric-up me-2 text-primary"></i>
                                Orden de Aparición
                            </label>
                            <input type="number" 
                                   class="form-control @error('orden') is-invalid @enderror" 
                                   id="orden" 
                                   name="orden" 
                                   placeholder="1"
                                   value="{{ old('orden', 1) }}"
                                   min="1">
                            <div class="invalid-feedback" id="orden-error">
                                @error('orden') {{ $message }} @enderror
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1 text-muted"></i>
                                Orden en que aparecerá este item en el checklist
                            </div>
                        </div>

                        <!-- Estado obligatorio -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="obligatorio" 
                                       name="obligatorio" 
                                       value="1" 
                                       {{ old('obligatorio') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-dark" for="obligatorio">
                                    <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                                    Item obligatorio
                                </label>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1 text-muted"></i>
                                Los items obligatorios deben completarse antes de finalizar el checklist
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="d-flex gap-3 pt-3">
                            <button type="submit" class="btn btn-primary btn-lg px-4" id="submit-btn">
                                <i class="fas fa-save me-2"></i>
                                Crear Item
                            </button>
                            <a href="{{ route('admin.itemsChecklist.index', ['id' => $checklist->id]) }}" class="btn btn-outline-secondary btn-lg px-4">
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
                        Consejos para crear items
                    </h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle me-2 text-success"></i>
                            Usa nombres claros y específicos
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle me-2 text-success"></i>
                            Selecciona el tipo apropiado según la respuesta esperada
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle me-2 text-success"></i>
                            Marca como obligatorio solo los items críticos
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle me-2 text-success"></i>
                            Organiza el orden lógicamente para facilitar la verificación
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
    const form = document.getElementById('item-form');
    const nombreInput = document.getElementById('nombre');
    const descripcionInput = document.getElementById('descripcion');
    const tipoSelect = document.getElementById('tipo');
    const ordenInput = document.getElementById('orden');
    const submitBtn = document.getElementById('submit-btn');

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

    // Validar descripción
    descripcionInput.addEventListener('input', function() {
        validateField(this, 'descripcion-error', function(value) {
            return value.length === 0 || value.length <= 1000;
        });
    });

    // Validar orden
    ordenInput.addEventListener('input', function() {
        validateField(this, 'orden-error', function(value) {
            const num = parseInt(value);
            return !isNaN(num) && num >= 1;
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
        
        // Validar descripción
        if (!validateField(descripcionInput, 'descripcion-error', function(value) {
            return value.length === 0 || value.length <= 1000;
        })) {
            isValid = false;
        }
        
        // Validar orden
        if (!validateField(ordenInput, 'orden-error', function(value) {
            const num = parseInt(value);
            return !isNaN(num) && num >= 1;
        })) {
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Formulario incompleto',
                text: 'Por favor, completa todos los campos obligatorios correctamente',
                confirmButtonColor: '#dc3545'
            });
            return false;
        }
        
        // Mostrar confirmación
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creando...';
        
        Swal.fire({
            title: 'Creando item...',
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

