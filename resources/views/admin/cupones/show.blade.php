@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1 text-dark fw-bold">
                <i class="fas fa-ticket-alt me-2 text-primary"></i>
                Detalles del Cupón
            </h1>
            <p class="text-muted mb-0">Información completa del cupón de descuento</p>
        </div>
        <div>
            <a href="{{ route('admin.cupones.edit', $cupone->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>
                Editar
            </a>
            <a href="{{ route('admin.cupones.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Volver
            </a>
        </div>
    </div>

    <hr class="mb-4">

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-info-circle me-2"></i>
                        Información del Cupón
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Código:</strong>
                            <p class="text-primary fs-5 fw-bold">{{ $cupone->codigo }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Nombre:</strong>
                            <p>{{ $cupone->nombre }}</p>
                        </div>
                    </div>

                    @if($cupone->descripcion)
                    <div class="mb-3">
                        <strong>Descripción:</strong>
                        <p>{{ $cupone->descripcion }}</p>
                    </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Tipo:</strong>
                            <p>
                                @if($cupone->tipo === 'porcentaje')
                                    <span class="badge bg-info">Porcentaje</span>
                                @else
                                    <span class="badge bg-success">Fijo</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4">
                            <strong>Valor:</strong>
                            <p>
                                @if($cupone->tipo === 'porcentaje')
                                    {{ number_format($cupone->valor, 0) }}%
                                @else
                                    {{ number_format($cupone->valor, 2, ',', '.') }}€
                                @endif
                            </p>
                        </div>
                        @if($cupone->descuento_maximo)
                        <div class="col-md-4">
                            <strong>Descuento Máximo:</strong>
                            <p>{{ number_format($cupone->descuento_maximo, 2, ',', '.') }}€</p>
                        </div>
                        @endif
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Vigencia:</strong>
                            <p>
                                Del {{ $cupone->fecha_inicio->format('d/m/Y') }}<br>
                                al {{ $cupone->fecha_fin->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <strong>Estado:</strong>
                            <p>
                                @if($cupone->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Usos Actuales:</strong>
                            <p>{{ $cupone->usos_actuales }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Usos Máximos:</strong>
                            <p>{{ $cupone->usos_maximos ?? 'Ilimitado' }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Usos por Cliente:</strong>
                            <p>{{ $cupone->usos_por_cliente }}</p>
                        </div>
                    </div>

                    @if($cupone->importe_minimo)
                    <div class="mb-3">
                        <strong>Importe Mínimo:</strong>
                        <p>{{ number_format($cupone->importe_minimo, 2, ',', '.') }}€</p>
                    </div>
                    @endif

                    @if($cupone->apartamentos_ids && count($cupone->apartamentos_ids) > 0)
                    <div class="mb-3">
                        <strong>Apartamentos Específicos:</strong>
                        <ul>
                            @foreach($cupone->apartamentos_ids as $aptId)
                                @php
                                    $apt = \App\Models\Apartamento::find($aptId);
                                @endphp
                                @if($apt)
                                    <li>{{ $apt->titulo }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                    @else
                    <div class="mb-3">
                        <strong>Apartamentos:</strong>
                        <p class="text-muted">Aplica a todos los apartamentos</p>
                    </div>
                    @endif
                </div>
            </div>

            @if($cupone->pagos->count() > 0)
            <div class="card shadow-sm border-0">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-history me-2"></i>
                        Historial de Usos ({{ $cupone->pagos->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Reserva</th>
                                    <th>Cliente</th>
                                    <th>Descuento</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cupone->pagos->sortByDesc('created_at') as $pago)
                                <tr>
                                    <td>{{ $pago->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($pago->reserva)
                                            <a href="{{ route('admin.reservas.show', $pago->reserva->id) }}">
                                                {{ $pago->reserva->codigo_reserva }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        @if($pago->cliente)
                                            {{ $pago->cliente->nombre ?? $pago->cliente->alias }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="text-success">
                                            -{{ number_format($pago->descuento_aplicado ?? 0, 2, ',', '.') }}€
                                        </strong>
                                    </td>
                                    <td>
                                        @if($pago->estado === 'completado')
                                            <span class="badge bg-success">Completado</span>
                                        @elseif($pago->estado === 'pendiente')
                                            <span class="badge bg-warning">Pendiente</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $pago->estado }}</span>
                                        @endif
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
    </div>
</div>
@endsection

