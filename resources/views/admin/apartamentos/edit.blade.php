@extends('layouts.appAdmin')

@section('title', 'Editar Apartamento')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="h2 mb-1 text-dark fw-bold">
                        <i class="fas fa-edit me-2 text-warning"></i>
                        Editar Apartamento
                    </h1>
                    <p class="text-muted mb-0">Modifica la información del apartamento y sincroniza los cambios con Channex</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('apartamentos.admin.index') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Volver
                    </a>
                    <button id="formGuardar" class="btn btn-warning btn-lg">
                        <i class="fas fa-save me-2"></i>Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Formulario Principal -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-semibold text-dark">
                        <i class="fas fa-edit me-2 text-primary"></i>Formulario de Edición
                    </h5>
                </div>
                <div class="card-body">
                    <form id="form" action="{{ route('apartamentos.admin.update', $apartamento->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

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
                                           value="{{ old('title', $apartamento->titulo) }}"
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
                                        <option value="apartment" {{ old('property_type', $apartamento->property_type) == 'apartment' ? 'selected' : '' }}>Apartamento</option>
                                        <option value="hotel" {{ old('property_type', $apartamento->property_type) == 'hotel' ? 'selected' : '' }}>Hotel</option>
                                        <option value="hostel" {{ old('property_type', $apartamento->property_type) == 'hostel' ? 'selected' : '' }}>Hostel</option>
                                        <option value="villa" {{ old('property_type', $apartamento->property_type) == 'villa' ? 'selected' : '' }}>Villa</option>
                                        <option value="guest_house" {{ old('property_type', $apartamento->property_type) == 'guest_house' ? 'selected' : '' }}>Casa de Huéspedes</option>
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
                                                <option value="{{ $edificio->id }}" {{ old('edificio_id', $apartamento->edificio_id) == $edificio->id ? 'selected' : '' }}>
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
                                           value="{{ old('nombre', $apartamento->nombre) }}"
                                           placeholder="Nombre interno del apartamento">
                                    @error('nombre')
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="claves" class="form-label fw-semibold">
                                        <i class="fas fa-key me-1 text-primary"></i>Claves <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('claves') is-invalid @enderror" 
                                           id="claves" 
                                           name="claves" 
                                           value="{{ old('claves', $apartamento->claves) }}"
                                           placeholder="Claves del apartamento"
                                           required>
                                    @error('claves')
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
                                           value="{{ old('address', $apartamento->address) }}"
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
                                           value="{{ old('city', $apartamento->city) }}"
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
                                    <label for="zip_code" class="form-label fw-semibold">
                                        <i class="fas fa-mail-bulk me-1 text-primary"></i>Código Postal
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('zip_code') is-invalid @enderror" 
                                           id="zip_code" 
                                           name="zip_code" 
                                           value="{{ old('zip_code', $apartamento->zip_code) }}"
                                           placeholder="Código postal">
                                    @error('zip_code')
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
                                           value="{{ old('country', $apartamento->country ?? 'Spain') }}"
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
                                        <i class="fas fa-bed me-1 text-primary"></i>Habitaciones
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('bedrooms') is-invalid @enderror" 
                                           id="bedrooms" 
                                           name="bedrooms" 
                                           value="{{ old('bedrooms', $apartamento->bedrooms) }}"
                                           placeholder="Número de habitaciones"
                                           min="1">
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
                                        <i class="fas fa-bath me-1 text-primary"></i>Baños
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('bathrooms') is-invalid @enderror" 
                                           id="bathrooms" 
                                           name="bathrooms" 
                                           value="{{ old('bathrooms', $apartamento->bathrooms) }}"
                                           placeholder="Número de baños"
                                           min="1"
                                           step="0.5">
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
                                        <i class="fas fa-users me-1 text-primary"></i>Huéspedes Máximos
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('max_guests') is-invalid @enderror" 
                                           id="max_guests" 
                                           name="max_guests" 
                                           value="{{ old('max_guests', $apartamento->max_guests) }}"
                                           placeholder="Número máximo de huéspedes"
                                           min="1">
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
                                           value="{{ old('size', $apartamento->size) }}"
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
                                        <i class="fas fa-align-left me-1 text-primary"></i>Descripción
                                    </label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="4"
                                              placeholder="Descripción detallada del apartamento">{{ old('description', $apartamento->description) }}</textarea>
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
                                           value="{{ old('id_booking', $apartamento->id_booking) }}"
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
                                           value="{{ old('id_airbnb', $apartamento->id_airbnb) }}"
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
                                           value="{{ old('id_web', $apartamento->id_web) }}"
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
                                    <button type="submit" class="btn btn-warning btn-lg">
                                        <i class="fas fa-save me-2"></i>Actualizar Apartamento
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4">
            <!-- Información Actual -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-semibold text-dark">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Información Actual
                    </h6>
                </div>
                <div class="card-body">
                    <div class="info-group mb-3">
                        <label class="form-label fw-semibold text-muted">ID</label>
                        <div class="info-value">
                            <span class="badge bg-secondary fs-6">#{{ $apartamento->id }}</span>
                        </div>
                    </div>
                    
                    <div class="info-group mb-3">
                        <label class="form-label fw-semibold text-muted">Título</label>
                        <div class="info-value">
                            {{ $apartamento->titulo ?? 'No especificado' }}
                        </div>
                    </div>
                    
                    <div class="info-group mb-3">
                        <label class="form-label fw-semibold text-muted">Tipo</label>
                        <div class="info-value">
                            <span class="badge bg-primary">{{ $apartamento->property_type ?? 'No especificado' }}</span>
                        </div>
                    </div>
                    
                    <div class="info-group mb-3">
                        <label class="form-label fw-semibold text-muted">Edificio</label>
                        <div class="info-value">
                            {{ $apartamento->edificio->nombre ?? 'No asignado' }}
                        </div>
                    </div>
                    
                    <div class="info-group mb-3">
                        <label class="form-label fw-semibold text-muted">Creado</label>
                        <div class="info-value">
                            <i class="fas fa-calendar me-2 text-muted"></i>
                            {{ $apartamento->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    
                    <div class="info-group">
                        <label class="form-label fw-semibold text-muted">Última Actualización</label>
                        <div class="info-value">
                            <i class="fas fa-clock me-2 text-muted"></i>
                            {{ $apartamento->updated_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado de Sincronización -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-semibold text-dark">
                        <i class="fas fa-sync me-2 text-primary"></i>Estado de Sincronización
                    </h6>
                </div>
                <div class="card-body">
                    <div class="info-group mb-3">
                        <label class="form-label fw-semibold text-muted">Channex ID</label>
                        <div class="info-value">
                            @if($apartamento->id_channex)
                                <span class="badge bg-success fs-6">{{ $apartamento->id_channex }}</span>
                                <small class="text-muted d-block mt-1">Sincronizado</small>
                            @else
                                <span class="badge bg-warning fs-6">No sincronizado</span>
                            @endif
                        </div>
                    </div>
                    
                    @if($apartamento->id_channex)
                        <div class="alert alert-info border-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>Los cambios se sincronizarán automáticamente con Channex</small>
                        </div>
                    @else
                        <div class="alert alert-warning border-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <small>Este apartamento no está sincronizado con Channex</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-semibold text-dark">
                        <i class="fas fa-tools me-2 text-primary"></i>Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('apartamentos.admin.show', $apartamento->id) }}" class="btn btn-outline-primary">
                            <i class="fas fa-eye me-2"></i>Ver Detalles
                        </a>
                        
                        <a href="{{ route('apartamentos.admin.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-2"></i>Lista de Apartamentos
                        </a>
                        
                        @if($apartamento->id_channex)
                            <button class="btn btn-outline-info" onclick="registrarWebhooks({{ $apartamento->id }})">
                                <i class="fas fa-sync me-2"></i>Registrar Webhooks
                            </button>
                        @endif
                    </div>
                </div>
            </div>
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

/* Estilos para grupos de información */
.info-group {
    margin-bottom: 1rem;
}

.info-group label {
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.info-value {
    font-size: 1rem;
    color: #495057;
}

/* Badges */
.badge {
    font-size: 0.75em;
    font-weight: 500;
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
    
    .col-lg-8, .col-lg-4 {
        margin-bottom: 1rem;
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

// Función para registrar webhooks
function registrarWebhooks(apartamentoId) {
    Swal.fire({
        title: 'Registrando Webhooks',
        text: 'Por favor espera mientras se registran los webhooks...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(`/apartamentos/admin/${apartamentoId}/webhooks`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        let successCount = 0;
        let errorCount = 0;
        
        data.forEach(item => {
            if (item.status === 'success') successCount++;
            else errorCount++;
        });

        Swal.fire({
            title: 'Webhooks Registrados',
            html: `
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-check-circle text-success fa-2x"></i>
                    </div>
                    <p><strong>${successCount}</strong> webhooks registrados exitosamente</p>
                    ${errorCount > 0 ? `<p class="text-warning"><strong>${errorCount}</strong> webhooks con errores</p>` : ''}
                </div>
            `,
            icon: 'success',
            confirmButtonText: 'Aceptar'
        });
    })
    .catch(error => {
        Swal.fire({
            title: 'Error',
            text: 'Error al registrar los webhooks: ' + error.message,
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
    });
}
</script>
@endsection
