@extends('layouts.appAdmin')

@section('title', 'Mis Turnos')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt me-2"></i>Mis Turnos - {{ $fecha }}
                    </h3>
                    <div class="card-tools">
                        <form method="GET" class="d-inline">
                            <div class="input-group input-group-sm" style="width: 200px;">
                                <input type="date" class="form-control" name="fecha" value="{{ $fecha }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Estadísticas del día -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card bg-primary text-white text-center">
                                <div class="card-body py-2">
                                    <h5 class="mb-0">{{ $estadisticas['total_turnos'] }}</h5>
                                    <small>Total Turnos</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white text-center">
                                <div class="card-body py-2">
                                    <h5 class="mb-0">{{ $estadisticas['turnos_completados'] }}</h5>
                                    <small>Completados</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white text-center">
                                <div class="card-body py-2">
                                    <h5 class="mb-0">{{ $estadisticas['turnos_en_progreso'] }}</h5>
                                    <small>En Progreso</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white text-center">
                                <div class="card-body py-2">
                                    <h5 class="mb-0">{{ $estadisticas['total_tareas'] }}</h5>
                                    <small>Total Tareas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white text-center">
                                <div class="card-body py-2">
                                    <h5 class="mb-0">{{ $estadisticas['tareas_completadas'] }}</h5>
                                    <small>Tareas Completadas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white text-center">
                                <div class="card-body py-2">
                                    <h5 class="mb-0">{{ $estadisticas['tareas_pendientes'] }}</h5>
                                    <small>Pendientes</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($turnos->count() > 0)
                        <div class="row">
                            @foreach($turnos as $turno)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 {{ $turno->estado === 'completado' ? 'border-success' : ($turno->estado === 'en_progreso' ? 'border-warning' : 'border-primary') }}">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $turno->hora_inicio->format('H:i') }} - {{ $turno->hora_fin->format('H:i') }}
                                            </h6>
                                            <span class="badge badge-{{ $turno->estado === 'completado' ? 'success' : ($turno->estado === 'en_progreso' ? 'warning' : 'primary') }}">
                                                {{ ucfirst(str_replace('_', ' ', $turno->estado)) }}
                                            </span>
                                        </div>
                                        
                                        <div class="card-body">
                                            <!-- Progreso del turno -->
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <small>Progreso</small>
                                                    <small>{{ $turno->progreso }}%</small>
                                                </div>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar" role="progressbar" style="width: {{ $turno->progreso }}%"></div>
                                                </div>
                                            </div>

                                            <!-- Estadísticas del turno -->
                                            <div class="row text-center mb-3">
                                                <div class="col-4">
                                                    <small class="text-muted">Tareas</small>
                                                    <div class="fw-bold">{{ $turno->total_tareas }}</div>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted">Completadas</small>
                                                    <div class="fw-bold text-success">{{ $turno->tareas_completadas }}</div>
                                                </div>
                                                <div class="col-4">
                                                    <small class="text-muted">Pendientes</small>
                                                    <div class="fw-bold text-warning">{{ $turno->tareas_pendientes }}</div>
                                                </div>
                                            </div>

                                            <!-- Tiempo estimado -->
                                            <div class="mb-3">
                                                <small class="text-muted">Tiempo Estimado:</small>
                                                <div class="fw-bold">{{ $turno->tiempo_estimado_total_formateado }}</div>
                                            </div>

                                            <!-- Lista de tareas -->
                                            @if($turno->tareasAsignadas->count() > 0)
                                                <div class="mb-3">
                                                    <small class="text-muted">Tareas:</small>
                                                    <div class="mt-1">
                                                        @foreach($turno->tareasAsignadas->take(3) as $tarea)
                                                            <div class="d-flex align-items-center mb-1">
                                                                <i class="fas fa-{{ $tarea->estado === 'completada' ? 'check-circle text-success' : 'circle text-muted' }} me-2"></i>
                                                                <small class="flex-grow-1">{{ $tarea->tipoTarea->nombre }}</small>
                                                                <small class="text-muted">{{ $tarea->tiempo_estimado_formateado }}</small>
                                                            </div>
                                                        @endforeach
                                                        @if($turno->tareasAsignadas->count() > 3)
                                                            <small class="text-muted">... y {{ $turno->tareasAsignadas->count() - 3 }} más</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Observaciones -->
                                            @if($turno->observaciones)
                                                <div class="mb-3">
                                                    <small class="text-muted">Observaciones:</small>
                                                    <div class="small text-muted">{{ Str::limit($turno->observaciones, 100) }}</div>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="card-footer">
                                            <div class="d-flex justify-content-between">
                                                <a href="{{ route('limpiadora.turnos.show', $turno) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye me-1"></i>Ver Detalles
                                                </a>
                                                
                                                @if($turno->estado === 'programado')
                                                    <button class="btn btn-sm btn-success" onclick="iniciarTurno({{ $turno->id }})">
                                                        <i class="fas fa-play me-1"></i>Iniciar
                                                    </button>
                                                @elseif($turno->estado === 'en_progreso')
                                                    <button class="btn btn-sm btn-warning" onclick="finalizarTurno({{ $turno->id }})">
                                                        <i class="fas fa-stop me-1"></i>Finalizar
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No tienes turnos asignados para esta fecha</h5>
                            <p class="text-muted">Selecciona otra fecha para ver tus turnos</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para finalizar turno -->
<div class="modal fade" id="finalizarTurnoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Finalizar Turno</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="finalizarTurnoForm">
                <div class="modal-body">
                    <input type="hidden" id="turnoId" name="turno_id">
                    
                    <div class="mb-3">
                        <label for="horas_trabajadas" class="form-label">Horas Trabajadas</label>
                        <input type="number" class="form-control" id="horas_trabajadas" name="horas_trabajadas" 
                               step="0.5" min="0" max="24" placeholder="Ej: 7.5">
                        <div class="form-text">Deja vacío para calcular automáticamente</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3" 
                                  placeholder="Observaciones sobre el turno..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Finalizar Turno</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function iniciarTurno(turnoId) {
    if (confirm('¿Iniciar este turno?')) {
        $.ajax({
            url: '/limpiadora/turnos/' + turnoId + '/iniciar',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: response.message,
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            }
        });
    }
}

function finalizarTurno(turnoId) {
    $('#turnoId').val(turnoId);
    $('#finalizarTurnoModal').modal('show');
}

$('#finalizarTurnoForm').submit(function(e) {
    e.preventDefault();
    
    const turnoId = $('#turnoId').val();
    const formData = $(this).serialize();
    
    $.ajax({
        url: '/limpiadora/turnos/' + turnoId + '/finalizar',
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                $('#finalizarTurnoModal').modal('hide');
                Swal.fire({
                    title: '¡Éxito!',
                    text: response.message,
                    icon: 'success'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error de conexión', 'error');
        }
    });
});
</script>
@endpush

