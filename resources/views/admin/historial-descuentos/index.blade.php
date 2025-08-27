@extends('layouts.appAdmin')

@section('title', 'Historial de Descuentos')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-history me-2"></i>Historial de Descuentos
                </h1>
                <a href="{{ route('configuracion-descuentos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-cog me-2"></i>Configuración
                </a>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.historial-descuentos.index') }}" class="row g-3">
                        <div class="col-md-2">
                            <label for="fecha" class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" value="{{ request('fecha') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="">Todos</option>
                                <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="aplicado" {{ request('estado') == 'aplicado' ? 'selected' : '' }}>Aplicado</option>
                                <option value="error" {{ request('estado') == 'error' ? 'selected' : '' }}>Error</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="apartamento" class="form-label">Apartamento ID</label>
                            <input type="number" class="form-control" id="apartamento" name="apartamento" value="{{ request('apartamento') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary mt-4">
                                <i class="fas fa-search me-2"></i>Filtrar
                            </button>
                            <a href="{{ route('admin.historial-descuentos.index') }}" class="btn btn-outline-secondary mt-4">
                                <i class="fas fa-times me-2"></i>Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Registros</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $estadisticas['total'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-list fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Aplicados</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $estadisticas['aplicados'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pendientes</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $estadisticas['pendientes'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Ahorro Total</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($estadisticas['ahorro_total'], 2) }}€</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-euro-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Historial -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Registros de Historial</h6>
                </div>
                <div class="card-body">
                    @if($historial->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Apartamento</th>
                                        <th>Tarifa</th>
                                        <th>Descuento</th>
                                        <th>Precio Final</th>
                                        <th>Días</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Datos del Momento</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($historial as $registro)
                                        <tr>
                                            <td>{{ $registro->id }}</td>
                                            <td>
                                                <strong>{{ $registro->apartamento->nombre ?? 'N/A' }}</strong>
                                                @if($registro->apartamento)
                                                    <br><small class="text-muted">ID: {{ $registro->apartamento->id }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($registro->tarifa)
                                                    <strong>{{ $registro->tarifa->nombre }}</strong>
                                                    <br><small class="text-muted">{{ $registro->precio_original }}€</small>
                                                @else
                                                    <span class="text-muted">Sin tarifa</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $registro->porcentaje_descuento > 0 ? 'success' : 'warning' }}">
                                                    {{ $registro->porcentaje_formateado }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong>{{ $registro->precio_con_descuento }}€</strong>
                                                @if($registro->ahorro_total > 0)
                                                    <br><small class="text-success">Ahorro: {{ $registro->ahorro_total }}€</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $registro->dias_aplicados }}</span>
                                                <br><small class="text-muted">{{ $registro->rango_fechas }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $registro->estado == 'aplicado' ? 'success' : ($registro->estado == 'pendiente' ? 'warning' : 'danger') }}">
                                                    {{ $registro->estado_formateado }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>{{ $registro->fecha_aplicacion->format('d/m/Y') }}</small>
                                                <br><small class="text-muted">{{ $registro->fecha_aplicacion->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                @if($registro->datos_momento)
                                                    <button class="btn btn-sm btn-outline-info" onclick="verDatosMomento({{ $registro->id }})">
                                                        <i class="fas fa-eye me-1"></i>Ver Datos
                                                    </button>
                                                @else
                                                    <span class="text-muted">No disponible</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.historial-descuentos.show', $registro) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="d-flex justify-content-center">
                            {{ $historial->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                            <h5 class="text-gray-500">No se encontraron registros</h5>
                            <p class="text-gray-400">No hay historial de descuentos que coincida con los filtros aplicados.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Datos del Momento -->
<div class="modal fade" id="modalDatosMomento" tabindex="-1" aria-labelledby="modalDatosMomentoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDatosMomentoLabel">
                    <i class="fas fa-info-circle me-2"></i>Datos del Momento de Aplicación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalDatosMomentoBody">
                <!-- Los datos se cargarán aquí -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function verDatosMomento(historialId) {
    // Mostrar loading
    document.getElementById('modalDatosMomentoBody').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Cargando datos del momento...</p>
        </div>
    `;
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalDatosMomento'));
    modal.show();
    
    // Cargar datos
    fetch(`/admin/historial-descuentos/${historialId}/datos-momento`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                document.getElementById('modalDatosMomentoBody').innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>${data.error}
                    </div>
                `;
                return;
            }
            
            const datos = data.datos;
            const verificacion = data.verificacion;
            
            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary"><i class="fas fa-building me-2"></i>Información del Edificio</h6>
                        <ul class="list-unstyled">
                            <li><strong>Nombre:</strong> ${datos.edificio.nombre}</li>
                            <li><strong>Total Apartamentos:</strong> ${datos.edificio.total_apartamentos}</li>
                        </ul>
                        
                        <h6 class="text-primary mt-3"><i class="fas fa-cog me-2"></i>Configuración Aplicada</h6>
                        <ul class="list-unstyled">
                            <li><strong>Nombre:</strong> ${datos.configuracion.nombre}</li>
                            <li><strong>Descuento:</strong> ${datos.configuracion.porcentaje_descuento}%</li>
                            <li><strong>Incremento:</strong> ${datos.configuracion.porcentaje_incremento}%</li>
                        </ul>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-primary"><i class="fas fa-home me-2"></i>Información del Apartamento</h6>
                        <ul class="list-unstyled">
                            <li><strong>Nombre:</strong> ${datos.apartamento.nombre}</li>
                            <li><strong>ID Channex:</strong> ${datos.apartamento.id_channex || 'N/A'}</li>
                        </ul>
                        
                        <h6 class="text-primary mt-3"><i class="fas fa-calendar me-2"></i>Fechas</h6>
                        <ul class="list-unstyled">
                            <li><strong>Análisis:</strong> ${datos.fecha_analisis}</li>
                            <li><strong>Rango:</strong> ${datos.fecha_inicio} - ${datos.fecha_fin}</li>
                            <li><strong>Días Libres:</strong> ${datos.total_dias}</li>
                        </ul>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary"><i class="fas fa-chart-line me-2"></i>Datos de Ocupación</h6>
                        <ul class="list-unstyled">
                            <li><strong>Ocupación Actual:</strong> <span class="badge bg-${datos.ocupacion_actual < 50 ? 'success' : datos.ocupacion_actual > 80 ? 'danger' : 'warning'}">${datos.ocupacion_actual}%</span></li>
                            <li><strong>Límite Aplicado:</strong> ${datos.ocupacion_limite}%</li>
                            <li><strong>Acción:</strong> <span class="badge bg-${datos.accion === 'descuento' ? 'success' : 'warning'}">${datos.accion.toUpperCase()}</span></li>
                            <li><strong>Porcentaje:</strong> ${datos.porcentaje}%</li>
                        </ul>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-primary"><i class="fas fa-euro-sign me-2"></i>Información Financiera</h6>
                        <ul class="list-unstyled">
                            <li><strong>Precio Original:</strong> ${datos.precio_original}€</li>
                            <li><strong>Precio con Ajuste:</strong> ${datos.precio_con_ajuste}€</li>
                            <li><strong>Ahorro por Día:</strong> ${datos.ahorro_por_dia}€</li>
                        </ul>
                    </div>
                </div>
                
                <hr>
                
                <div class="alert alert-${verificacion.cumplidos ? 'success' : 'warning'}">
                    <h6 class="alert-heading">
                        <i class="fas fa-${verificacion.cumplidos ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                        Verificación de Requisitos
                    </h6>
                    <p class="mb-0"><strong>Estado:</strong> ${verificacion.cumplidos ? 'REQUISITOS CUMPLIDOS' : 'REQUISITOS NO CUMPLIDOS'}</p>
                    <p class="mb-0"><strong>Razón:</strong> ${verificacion.razon}</p>
                </div>
            `;
            
            document.getElementById('modalDatosMomentoBody').innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('modalDatosMomentoBody').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>Error al cargar los datos del momento.
                </div>
            `;
        });
}
</script>
@endpush
