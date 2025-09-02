@extends('layouts.appAdmin')

@section('title', 'Detalles del Apartamento')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="h2 mb-1 text-dark fw-bold">
                        <i class="fas fa-building me-2 text-primary"></i>
                        {{ $apartamento->titulo ?? $apartamento->nombre }}
                    </h1>
                    <p class="text-muted mb-0">Información detallada y estadísticas del apartamento</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('apartamentos.admin.index') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Volver
                    </a>
                    <a href="{{ route('apartamentos.admin.edit', $apartamento->id) }}" class="btn btn-warning btn-lg">
                        <i class="fas fa-edit me-2"></i>Editar
                    </a>
                    @if($apartamento->id_channex)
                        <button class="btn btn-info btn-lg" onclick="registrarWebhooks({{ $apartamento->id }})">
                            <i class="fas fa-sync me-2"></i>Registrar Webhooks
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Columna principal -->
        <div class="col-lg-8">
            <!-- Información General -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-semibold text-dark">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Información General
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="form-label fw-semibold text-muted">Título</label>
                                <div class="info-value">
                                    {{ $apartamento->titulo ?? 'No especificado' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="form-label fw-semibold text-muted">Tipo de Propiedad</label>
                                <div class="info-value">
                                    <span class="badge bg-primary">{{ $apartamento->property_type ?? 'No especificado' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="form-label fw-semibold text-muted">Edificio</label>
                                <div class="info-value">
                                    {{ $apartamento->edificioRel ? $apartamento->edificioRel->nombre : 'No asignado' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="form-label fw-semibold text-muted">Claves de Acceso</label>
                                <div class="info-value">
                                    {{ $apartamento->claves ?? 'No especificadas' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ubicación -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-semibold text-dark">
                        <i class="fas fa-map-marker-alt me-2 text-primary"></i>Ubicación
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="form-label fw-semibold text-muted">Dirección</label>
                                <div class="info-value">
                                    {{ $apartamento->address ?? 'No especificada' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="form-label fw-semibold text-muted">Ciudad</label>
                                <div class="info-value">
                                    {{ $apartamento->city ?? 'No especificada' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="form-label fw-semibold text-muted">Código Postal</label>
                                <div class="info-value">
                                    {{ $apartamento->postal_code ?? 'No especificado' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="form-label fw-semibold text-muted">País</label>
                                <div class="info-value">
                                    {{ $apartamento->country ?? 'No especificado' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Características del Apartamento -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-semibold text-dark">
                        <i class="fas fa-bed me-2 text-primary"></i>Características
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="info-group text-center">
                                <div class="feature-icon mb-2">
                                    <i class="fas fa-bed fa-2x text-primary"></i>
                                </div>
                                <label class="form-label fw-semibold text-muted">Habitaciones</label>
                                <div class="info-value">
                                    <span class="badge bg-primary fs-5">{{ $apartamento->bedrooms ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group text-center">
                                <div class="feature-icon mb-2">
                                    <i class="fas fa-bath fa-2x text-info"></i>
                                </div>
                                <label class="form-label fw-semibold text-muted">Baños</label>
                                <div class="info-value">
                                    <span class="badge bg-info fs-5">{{ $apartamento->bathrooms ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group text-center">
                                <div class="feature-icon mb-2">
                                    <i class="fas fa-users fa-2x text-success"></i>
                                </div>
                                <label class="form-label fw-semibold text-muted">Huéspedes</label>
                                <div class="info-value">
                                    <span class="badge bg-success fs-5">{{ $apartamento->max_guests ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group text-center">
                                <div class="feature-icon mb-2">
                                    <i class="fas fa-ruler-combined fa-2x text-warning"></i>
                                </div>
                                <label class="form-label fw-semibold text-muted">Tamaño</label>
                                <div class="info-value">
                                    <span class="badge bg-warning fs-5">{{ $apartamento->size ?? 'N/A' }} m²</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Descripción -->
            @if($apartamento->description)
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-semibold text-dark">
                            <i class="fas fa-align-left me-2 text-primary"></i>Descripción
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="info-group">
                            <div class="info-value">
                                {{ $apartamento->description }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- IDs Externos -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-semibold text-dark">
                        <i class="fas fa-link me-2 text-primary"></i>IDs Externos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="info-group">
                                <label class="form-label fw-semibold text-muted">ID Booking</label>
                                <div class="info-value">
                                    @if($apartamento->id_booking)
                                        <code class="bg-light px-2 py-1 rounded">{{ $apartamento->id_booking }}</code>
                                    @else
                                        <span class="text-muted">No especificado</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group">
                                <label class="form-label fw-semibold text-muted">ID Airbnb</label>
                                <div class="info-value">
                                    @if($apartamento->id_airbnb)
                                        <code class="bg-light px-2 py-1 rounded">{{ $apartamento->id_airbnb }}</code>
                                    @else
                                        <span class="text-muted">No especificado</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-group">
                                <label class="form-label fw-semibold text-muted">ID Web</label>
                                <div class="info-value">
                                    @if($apartamento->id_web)
                                        <code class="bg-light px-2 py-1 rounded">{{ $apartamento->id_web }}</code>
                                    @else
                                        <span class="text-muted">No especificado</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado de Sincronización -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-semibold text-dark">
                        <i class="fas fa-sync me-2 text-primary"></i>Estado de Sincronización
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-group">
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
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <label class="form-label fw-semibold text-muted">Última Sincronización</label>
                                <div class="info-value">
                                    @if($apartamento->updated_at)
                                        <i class="fas fa-clock me-2 text-muted"></i>
                                        {{ $apartamento->updated_at->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-muted">Nunca</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4">
            <!-- Información del Apartamento -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-semibold text-dark">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Información del Apartamento
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
                        <label class="form-label fw-semibold text-muted">Nombre Interno</label>
                        <div class="info-value">
                            {{ $apartamento->nombre ?? 'No especificado' }}
                        </div>
                    </div>
                    
                    <div class="info-group mb-3">
                        <label class="form-label fw-semibold text-muted">Creado</label>
                        <div class="info-value">
                            <i class="fas fa-calendar me-2 text-muted"></i>
                            {{ $apartamento->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    
                    <div class="info-group mb-3">
                        <label class="form-label fw-semibold text-muted">Última Actualización</label>
                        <div class="info-value">
                            <i class="fas fa-clock me-2 text-muted"></i>
                            {{ $apartamento->updated_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-semibold text-dark">
                        <i class="fas fa-tools me-2 text-primary"></i>Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('apartamentos.admin.edit', $apartamento->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Editar Apartamento
                        </a>
                        
                        @if($apartamento->id_channex)
                            <button class="btn btn-info" onclick="registrarWebhooks({{ $apartamento->id }})">
                                <i class="fas fa-sync me-2"></i>Registrar Webhooks
                            </button>
                        @endif
                        
                        <a href="{{ route('apartamentos.admin.estadisticas', $apartamento->id) }}" class="btn btn-outline-info">
                            <i class="fas fa-chart-bar me-2"></i>Ver Estadísticas
                        </a>
                    </div>
                </div>
            </div>

            <!-- Información del Edificio -->
            @if($apartamento->edificioRel)
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 fw-semibold text-dark">
                            <i class="fas fa-building me-2 text-primary"></i>Información del Edificio
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="info-group mb-3">
                            <label class="form-label fw-semibold text-muted">Nombre</label>
                            <div class="info-value">
                                <span class="badge bg-primary">{{ $apartamento->edificioRel->nombre ?? 'Sin nombre' }}</span>
                            </div>
                        </div>
                        
                        @if($apartamento->edificioRel->direccion)
                            <div class="info-group mb-3">
                                <label class="form-label fw-semibold text-muted">Dirección</label>
                                <div class="info-value">
                                    {{ $apartamento->edificioRel->direccion }}
                                </div>
                            </div>
                        @endif
                        
                        @if($apartamento->edificioRel->ciudad)
                            <div class="info-group">
                                <label class="form-label fw-semibold text-muted">Ciudad</label>
                                <div class="info-value">
                                    {{ $apartamento->edificioRel->ciudad }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 fw-semibold text-dark">
                            <i class="fas fa-building me-2 text-primary"></i>Información del Edificio
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="info-group">
                            <div class="info-value">
                                <span class="text-muted">No hay edificio asignado a este apartamento</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
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

/* Iconos de características */
.feature-icon {
    opacity: 0.8;
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

/* Badges */
.badge {
    font-size: 0.75em;
    font-weight: 500;
}

/* Código */
code {
    font-size: 0.875rem;
    color: #495057;
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
    
    .feature-icon {
        font-size: 1.5rem !important;
    }
}
</style>
@endsection

@section('scriptHead')
<script>
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
