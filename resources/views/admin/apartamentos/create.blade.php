@extends('layouts.appAdmin')

@section('title', 'Crear Apartamento')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="h2 mb-1 text-dark fw-bold">
                        <i class="fas fa-plus-circle me-2 text-success"></i>
                        Crear Nuevo Apartamento
                    </h1>
                    <p class="text-muted mb-0">Completa la información del apartamento para registrarlo en Channex</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('apartamentos.admin.index') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Volver
                    </a>
                    <button id="formGuardar" class="btn btn-success btn-lg">
                        <i class="fas fa-save me-2"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0 fw-semibold text-dark">
                <i class="fas fa-edit me-2 text-primary"></i>Información del Apartamento
            </h5>
        </div>
        <div class="card-body">
            <form id="form" action="{{ route('channex.storeProperty') }}" method="POST" enctype="multipart/form-data">
                @csrf

                @if ($errors->any())
                    <div class="alert alert-danger border-0">
                        <h6 class="alert-heading fw-semibold">
                            <i class="fas fa-exclamation-triangle me-2"></i>Errores de Validación
                        </h6>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Información General -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-primary mb-3 fw-semibold">
                            <i class="fas fa-info-circle me-2"></i>Información General
                        </h6>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="title" class="form-label fw-semibold">
                                <i class="fas fa-tag me-1 text-primary"></i>Título <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}"
                                   placeholder="Título principal de la propiedad"
                                   required>
                            @error('title')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="property_type" class="form-label fw-semibold">
                                <i class="fas fa-home me-1 text-primary"></i>Tipo de Propiedad <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('property_type') is-invalid @enderror" id="property_type" name="property_type" required>
                                <option value="">Selecciona el tipo de propiedad</option>
                                <option value="apartment" {{ old('property_type') == 'apartment' ? 'selected' : '' }}>Apartamento</option>
                                <option value="hotel" {{ old('property_type') == 'hotel' ? 'selected' : '' }}>Hotel</option>
                                <option value="hostel" {{ old('property_type') == 'hostel' ? 'selected' : '' }}>Hostel</option>
                                <option value="villa" {{ old('property_type') == 'villa' ? 'selected' : '' }}>Villa</option>
                                <option value="guest_house" {{ old('property_type') == 'guest_house' ? 'selected' : '' }}>Casa de Huéspedes</option>
                            </select>
                            @error('property_type')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="edificio_id" class="form-label fw-semibold">
                                <i class="fas fa-building me-1 text-primary"></i>Edificio <span class="text-danger">*</span>
                            </label>
                            <select name="edificio_id" id="edificio_id" class="form-select @error('edificio_id') is-invalid @enderror" required>
                                <option value="">Selecciona un edificio</option>
                                @if (count($edificios) > 0)
                                    @foreach ($edificios as $edificio)
                                        <option value="{{ $edificio->id }}" {{ old('edificio_id') == $edificio->id ? 'selected' : '' }}>
                                            {{ $edificio->nombre }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('edificio_id')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="nombre" class="form-label fw-semibold">
                                <i class="fas fa-home me-1 text-primary"></i>Nombre Interno
                            </label>
                            <input type="text" 
                                   class="form-control @error('nombre') is-invalid @enderror" 
                                   id="nombre" 
                                   name="nombre" 
                                   value="{{ old('nombre') }}"
                                   placeholder="Nombre interno del apartamento">
                            @error('nombre')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Ubicación -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-primary mb-3 fw-semibold">
                            <i class="fas fa-map-marker-alt me-2"></i>Ubicación
                        </h6>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="address" class="form-label fw-semibold">
                                <i class="fas fa-home me-1 text-primary"></i>Dirección <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('address') is-invalid @enderror" 
                                   id="address" 
                                   name="address" 
                                   value="{{ old('address') }}"
                                   placeholder="Dirección completa del apartamento"
                                   required>
                            @error('address')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="city" class="form-label fw-semibold">
                                <i class="fas fa-city me-1 text-primary"></i>Ciudad <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('city') is-invalid @enderror" 
                                   id="city" 
                                   name="city" 
                                   value="{{ old('city') }}"
                                   placeholder="Ciudad del apartamento"
                                   required>
                            @error('city')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="postal_code" class="form-label fw-semibold">
                                <i class="fas fa-mail-bulk me-1 text-primary"></i>Código Postal
                            </label>
                            <input type="text" 
                                   class="form-control @error('postal_code') is-invalid @enderror" 
                                   id="postal_code" 
                                   name="postal_code" 
                                   value="{{ old('postal_code') }}"
                                   placeholder="Código postal">
                            @error('postal_code')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="country" class="form-label fw-semibold">
                                <i class="fas fa-globe me-1 text-primary"></i>País <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('country') is-invalid @enderror" 
                                   id="country" 
                                   name="country" 
                                   value="{{ old('country', 'Spain') }}"
                                   placeholder="País del apartamento"
                                   required>
                            @error('country')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Detalles del Apartamento -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-primary mb-3 fw-semibold">
                            <i class="fas fa-bed me-2"></i>Detalles del Apartamento
                        </h6>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="bedrooms" class="form-label fw-semibold">
                                <i class="fas fa-bed me-1 text-primary"></i>Habitaciones <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   class="form-control @error('bedrooms') is-invalid @enderror" 
                                   id="bedrooms" 
                                   name="bedrooms" 
                                   value="{{ old('bedrooms') }}"
                                   placeholder="Número de habitaciones"
                                   min="1"
                                   required>
                            @error('bedrooms')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="bathrooms" class="form-label fw-semibold">
                                <i class="fas fa-bath me-1 text-primary"></i>Baños <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   class="form-control @error('bathrooms') is-invalid @enderror" 
                                   id="bathrooms" 
                                   name="bathrooms" 
                                   value="{{ old('bathrooms') }}"
                                   placeholder="Número de baños"
                                   min="1"
                                   step="0.5"
                                   required>
                            @error('bathrooms')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="max_guests" class="form-label fw-semibold">
                                <i class="fas fa-users me-1 text-primary"></i>Huéspedes Máximos <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   class="form-control @error('max_guests') is-invalid @enderror" 
                                   id="max_guests" 
                                   name="max_guests" 
                                   value="{{ old('max_guests') }}"
                                   placeholder="Número máximo de huéspedes"
                                   min="1"
                                   required>
                            @error('max_guests')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="size" class="form-label fw-semibold">
                                <i class="fas fa-ruler-combined me-1 text-primary"></i>Tamaño (m²)
                            </label>
                            <input type="number" 
                                   class="form-control @error('size') is-invalid @enderror" 
                                   id="size" 
                                   name="size" 
                                   value="{{ old('size') }}"
                                   placeholder="Tamaño en metros cuadrados"
                                   min="1"
                                   step="0.1">
                            @error('size')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-primary mb-3 fw-semibold">
                            <i class="fas fa-align-left me-2"></i>Descripción
                        </h6>
                    </div>
                    
                    <div class="col-12 mb-3">
                        <div class="form-group">
                            <label for="description" class="form-label fw-semibold">
                                <i class="fas fa-align-left me-1 text-primary"></i>Descripción <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4"
                                      placeholder="Descripción detallada del apartamento"
                                      required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- IDs Externos -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-primary mb-3 fw-semibold">
                            <i class="fas fa-link me-2"></i>IDs Externos
                        </h6>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label for="id_booking" class="form-label fw-semibold">
                                <i class="fas fa-key me-1 text-primary"></i>ID Booking
                            </label>
                            <input type="text" 
                                   class="form-control @error('id_booking') is-invalid @enderror" 
                                   id="id_booking" 
                                   name="id_booking" 
                                   value="{{ old('id_booking') }}"
                                   placeholder="ID de Booking.com">
                            @error('id_booking')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label for="id_airbnb" class="form-label fw-semibold">
                                <i class="fas fa-bed me-1 text-primary"></i>ID Airbnb
                            </label>
                            <input type="text" 
                                   class="form-control @error('id_airbnb') is-invalid @enderror" 
                                   id="id_airbnb" 
                                   name="id_airbnb" 
                                   value="{{ old('id_airbnb') }}"
                                   placeholder="ID de Airbnb">
                            @error('id_airbnb')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label for="id_web" class="form-label fw-semibold">
                                <i class="fas fa-globe me-1 text-primary"></i>ID Web
                            </label>
                            <input type="text" 
                                   class="form-control @error('id_web') is-invalid @enderror" 
                                   id="id_web" 
                                   name="id_web" 
                                   value="{{ old('id_web') }}"
                                   placeholder="ID de la web propia">
                            @error('id_web')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center pt-3">
                            <a href="{{ route('apartamentos.admin.index') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-2"></i>Guardar Apartamento
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
/* Formularios */
.form-group {
    margin-bottom: 1rem;
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    border-radius: 8px;
    border: 1px solid #e3e6f0;
    transition: all 0.2s ease-in-out;
}

.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* Botones */
.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease-in-out;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.125rem;
}

/* Cards */
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

/* Alertas */
.alert {
    border-radius: 8px;
}

/* Responsive */
@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .d-flex.justify-content-between .btn {
        width: 100%;
    }
    
    .col-md-6, .col-md-4 {
        margin-bottom: 1rem;
    }
}
</style>
@endsection

@section('scriptHead')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación del formulario
    const form = document.getElementById('form');
    const formGuardar = document.getElementById('formGuardar');
    
    formGuardar.addEventListener('click', function() {
        if (form.checkValidity()) {
            form.submit();
        } else {
            form.reportValidity();
        }
    });
    
    // Auto-completar país si está vacío
    const countryField = document.getElementById('country');
    if (!countryField.value) {
        countryField.value = 'Spain';
    }
});
</script>
@endsection
