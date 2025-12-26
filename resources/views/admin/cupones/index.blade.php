@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1 text-dark fw-bold">
                <i class="fas fa-ticket-alt me-2 text-primary"></i>
                Cupones de Descuento
            </h1>
            <p class="text-muted mb-0">Gestiona los cupones de descuento para reservas</p>
        </div>
        <a href="{{ route('admin.cupones.create') }}" class="btn btn-primary btn-lg">
            <i class="fas fa-plus me-2"></i>
            Nuevo Cupón
        </a>
    </div>

    <hr class="mb-4">

    @if(session('swal_success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('swal_success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($cupones->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No hay cupones configurados. <a href="{{ route('admin.cupones.create') }}">Crea el primero</a>
        </div>
    @else
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Descuento</th>
                                <th>Vigencia</th>
                                <th>Usos</th>
                                <th>Estado</th>
                                <th style="width: 150px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cupones as $cupon)
                                <tr>
                                    <td>
                                        <strong class="text-primary">{{ $cupon->codigo }}</strong>
                                    </td>
                                    <td>{{ $cupon->nombre }}</td>
                                    <td>
                                        @if($cupon->tipo === 'porcentaje')
                                            <span class="badge bg-info">Porcentaje</span>
                                        @else
                                            <span class="badge bg-success">Fijo</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($cupon->tipo === 'porcentaje')
                                            {{ number_format($cupon->valor, 0) }}%
                                            @if($cupon->descuento_maximo)
                                                <br><small class="text-muted">Máx: {{ number_format($cupon->descuento_maximo, 2, ',', '.') }}€</small>
                                            @endif
                                        @else
                                            {{ number_format($cupon->valor, 2, ',', '.') }}€
                                        @endif
                                    </td>
                                    <td>
                                        <small>
                                            {{ $cupon->fecha_inicio->format('d/m/Y') }}<br>
                                            <strong>al</strong><br>
                                            {{ $cupon->fecha_fin->format('d/m/Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($cupon->usos_maximos)
                                            {{ $cupon->usos_actuales }} / {{ $cupon->usos_maximos }}
                                        @else
                                            {{ $cupon->usos_actuales }} / ∞
                                        @endif
                                    </td>
                                    <td>
                                        @if($cupon->activo)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-secondary">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.cupones.show', $cupon->id) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.cupones.edit', $cupon->id) }}" 
                                               class="btn btn-sm btn-warning" 
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.cupones.toggle-active', $cupon->id) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('¿{{ $cupon->activo ? 'Desactivar' : 'Activar' }} este cupón?');">
                                                @csrf
                                                @method('POST')
                                                <button type="submit" 
                                                        class="btn btn-sm {{ $cupon->activo ? 'btn-secondary' : 'btn-success' }}" 
                                                        title="{{ $cupon->activo ? 'Desactivar' : 'Activar' }}">
                                                    <i class="fas fa-{{ $cupon->activo ? 'toggle-on' : 'toggle-off' }}"></i>
                                                </button>
                                            </form>
                                            @if($cupon->pagos()->count() === 0)
                                                <form action="{{ route('admin.cupones.destroy', $cupon->id) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('¿Estás seguro de eliminar este cupón?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

