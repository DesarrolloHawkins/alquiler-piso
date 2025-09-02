@extends('layouts.appAdmin')

@section('title', 'Crear Zona Común')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-plus"></i> Crear Nueva Zona Común
                </h1>
                <a href="{{ route('admin.zonas-comunes.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>

            <!-- Formulario -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-edit"></i> Información de la Zona Común
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.zonas-comunes.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <!-- Nombre -->
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">
                                    <i class="fas fa-tag"></i> Nombre <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('nombre') is-invalid @enderror" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="{{ old('nombre') }}" 
                                       placeholder="Ej: Recepción, Piscina, Gimnasio..."
                                       required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tipo -->
                            <div class="col-md-6 mb-3">
                                <label for="tipo" class="form-label">
                                    <i class="fas fa-cog"></i> Tipo <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('tipo') is-invalid @enderror" 
                                        id="tipo" 
                                        name="tipo" 
                                        required>
                                    <option value="">Seleccionar tipo...</option>
                                    <option value="zona_comun" {{ old('tipo') == 'zona_comun' ? 'selected' : '' }}>
                                        Zona Común
                                    </option>
                                    <option value="area_servicio" {{ old('tipo') == 'area_servicio' ? 'selected' : '' }}>
                                        Área de Servicio
                                    </option>
                                    <option value="recepcion" {{ old('tipo') == 'recepcion' ? 'selected' : '' }}>
                                        Recepción
                                    </option>
                                    <option value="piscina" {{ old('tipo') == 'piscina' ? 'selected' : '' }}>
                                        Piscina
                                    </option>
                                    <option value="gimnasio" {{ old('tipo') == 'gimnasio' ? 'selected' : '' }}>
                                        Gimnasio
                                    </option>
                                    <option value="terraza" {{ old('tipo') == 'terraza' ? 'selected' : '' }}>
                                        Terraza
                                    </option>
                                </select>
                                @error('tipo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Ubicación -->
                            <div class="col-md-6 mb-3">
                                <label for="ubicacion" class="form-label">
                                    <i class="fas fa-map-marker-alt"></i> Ubicación
                                </label>
                                <input type="text" 
                                       class="form-control @error('ubicacion') is-invalid @enderror" 
                                       id="ubicacion" 
                                       name="ubicacion" 
                                       value="{{ old('ubicacion') }}" 
                                       placeholder="Ej: Planta baja, Primer piso...">
                                @error('ubicacion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Orden -->
                            <div class="col-md-6 mb-3">
                                <label for="orden" class="form-label">
                                    <i class="fas fa-sort-numeric-up"></i> Orden
                                </label>
                                <input type="number" 
                                       class="form-control @error('orden') is-invalid @enderror" 
                                       id="orden" 
                                       name="orden" 
                                       value="{{ old('orden', 0) }}" 
                                       min="0"
                                       placeholder="0">
                                @error('orden')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Número para ordenar las zonas comunes (0 = sin orden específico)
                                </small>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">
                                <i class="fas fa-align-left"></i> Descripción
                            </label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" 
                                      name="descripcion" 
                                      rows="4" 
                                      placeholder="Descripción detallada de la zona común...">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.zonas-comunes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Crear Zona Común
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px 10px 0 0 !important;
    border: none;
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn {
    border-radius: 8px;
    font-weight: 500;
    padding: 0.5rem 1.5rem;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
}

.btn-secondary {
    background: #6c757d;
    border: none;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-resize textarea
    const textarea = document.getElementById('descripcion');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }

    // Form validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre').value.trim();
            const tipo = document.getElementById('tipo').value;

            if (!nombre) {
                e.preventDefault();
                alert('Por favor, ingresa el nombre de la zona común.');
                document.getElementById('nombre').focus();
                return false;
            }

            if (!tipo) {
                e.preventDefault();
                alert('Por favor, selecciona el tipo de zona común.');
                document.getElementById('tipo').focus();
                return false;
            }
        });
    }
});
</script>
@endsection
