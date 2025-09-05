@extends('layouts.appAdmin')

@section('title', 'Editar Turno')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit me-2"></i>Editar Turno
                    </h3>
                </div>
                
                <form action="{{ route('gestion.turnos.update', $turno) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Empleada</label>
                                    <input type="text" class="form-control" value="{{ $turno->user->name }}" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Fecha</label>
                                    <input type="text" class="form-control" value="{{ $turno->fecha->format('d/m/Y') }}" readonly>
                                </div>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label for="hora_inicio" class="form-label">Hora Inicio <span class="text-danger">*</span></label>
                                            <input type="time" class="form-control @error('hora_inicio') is-invalid @enderror" 
                                                   id="hora_inicio" name="hora_inicio" value="{{ old('hora_inicio', $turno->hora_inicio->format('H:i')) }}" required>
                                            @error('hora_inicio')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label for="hora_fin" class="form-label">Hora Fin <span class="text-danger">*</span></label>
                                            <input type="time" class="form-control @error('hora_fin') is-invalid @enderror" 
                                                   id="hora_fin" name="hora_fin" value="{{ old('hora_fin', $turno->hora_fin->format('H:i')) }}" required>
                                            @error('hora_fin')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                                    <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                                        <option value="programado" {{ old('estado', $turno->estado) == 'programado' ? 'selected' : '' }}>Programado</option>
                                        <option value="en_progreso" {{ old('estado', $turno->estado) == 'en_progreso' ? 'selected' : '' }}>En Progreso</option>
                                        <option value="completado" {{ old('estado', $turno->estado) == 'completado' ? 'selected' : '' }}>Completado</option>
                                        <option value="ausente" {{ old('estado', $turno->estado) == 'ausente' ? 'selected' : '' }}>Ausente</option>
                                    </select>
                                    @error('estado')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="observaciones" class="form-label">Observaciones</label>
                                    <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                              id="observaciones" name="observaciones" rows="3">{{ old('observaciones', $turno->observaciones) }}</textarea>
                                    @error('observaciones')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h6>Estadísticas del Turno</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="card bg-primary text-white text-center mb-2">
                                            <div class="card-body py-2">
                                                <h6 class="mb-0">{{ $turno->total_tareas }}</h6>
                                                <small>Total Tareas</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card bg-success text-white text-center mb-2">
                                            <div class="card-body py-2">
                                                <h6 class="mb-0">{{ $turno->tareas_completadas }}</h6>
                                                <small>Completadas</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="card bg-warning text-white text-center mb-2">
                                            <div class="card-body py-2">
                                                <h6 class="mb-0">{{ $turno->tareas_pendientes }}</h6>
                                                <small>Pendientes</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card bg-info text-white text-center mb-2">
                                            <div class="card-body py-2">
                                                <h6 class="mb-0">{{ $turno->tiempo_estimado_total_formateado }}</h6>
                                                <small>Tiempo Est.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <h6>Progreso</h6>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $turno->progreso }}%">
                                            {{ $turno->progreso }}%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('gestion.turnos.show', $turno) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>Actualizar Turno
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
