@extends('layouts.appAdmin')

@section('content')
<!-- Fancybox CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0.27/dist/fancybox.min.css">

<!-- Fancybox JS -->
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0.27/dist/fancybox.umd.js"></script>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800">
            <i class="fas fa-eye text-primary me-2"></i>
            Detalles de la Reserva: <span class="text-primary">{{ $reserva->codigo_reserva }}</span>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('inicio') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('reservas.index') }}">Reservas</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detalles</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reservas.edit', $reserva->id) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>
            Editar
        </a>
        <a href="{{ route('reservas.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>
            Volver
        </a>
    </div>
</div>

<!-- Session Alerts -->
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Información Principal -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">
            <i class="fas fa-info-circle text-primary me-2"></i>
            Información de la Reserva
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-light rounded-3">
                    <div class="avatar-sm bg-info-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="fas fa-building text-info"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-semibold">Apartamento</h6>
                        <p class="mb-0 text-muted">{{ $reserva->apartamento->titulo }}</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-light rounded-3">
                    <div class="avatar-sm bg-secondary-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="fas fa-city text-secondary"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-semibold">Edificio</h6>
                        <p class="mb-0 text-muted">{{ $reserva->apartamento->edificioName->nombre }}</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-light rounded-3">
                    <div class="avatar-sm bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="fas fa-globe text-warning"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-semibold">Origen</h6>
                        <p class="mb-0 text-muted">{{ $reserva->origen }}</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-light rounded-3">
                    <div class="avatar-sm bg-success-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="fas fa-euro-sign text-success"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-semibold">Precio Total</h6>
                        <p class="mb-0 fw-bold fs-5 text-success">{{ number_format($reserva->precio, 2) }} €</p>
                        @if($reserva->neto)
                            <small class="text-muted">Neto: {{ number_format($reserva->neto, 2) }} €</small>
                        @endif
                    </div>
                </div>
            </div>
            
            @if($reserva->comision || $reserva->cargo_por_pago || $reserva->iva)
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-light rounded-3">
                    <div class="avatar-sm bg-info-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="fas fa-calculator text-info"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-semibold">Desglose Económico</h6>
                        <div class="small text-muted">
                            @if($reserva->comision)
                                <div>Comisión: {{ number_format($reserva->comision, 2) }} €</div>
                            @endif
                            @if($reserva->cargo_por_pago)
                                <div>Cargo por pago: {{ number_format($reserva->cargo_por_pago, 2) }} €</div>
                            @endif
                            @if($reserva->iva)
                                <div>IVA: {{ number_format($reserva->iva, 2) }} €</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-light rounded-3">
                    <div class="avatar-sm bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="fas fa-calendar-plus text-primary"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-semibold">Fecha de Entrada</h6>
                        <p class="mb-0 text-muted">{{ \Carbon\Carbon::parse($reserva->fecha_entrada)->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-light rounded-3">
                    <div class="avatar-sm bg-danger-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="fas fa-calendar-minus text-danger"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-semibold">Fecha de Salida</h6>
                        <p class="mb-0 text-muted">{{ \Carbon\Carbon::parse($reserva->fecha_salida)->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-light rounded-3">
                    <div class="avatar-sm bg-info-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="fas fa-id-card text-info"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-semibold">DNI Entregado</h6>
                        <p class="mb-0">
                            @if($reserva->dni_entregado == 1)
                                <span class="badge bg-success-subtle text-success">
                                    <i class="fas fa-check me-1"></i>Entregado
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">
                                    <i class="fas fa-times me-1"></i>No entregado
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-light rounded-3">
                    <div class="avatar-sm bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="fas fa-paper-plane text-warning"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-semibold">Enviado a Webpol</h6>
                        <p class="mb-0">
                            @if($reserva->enviado_webpol == 1)
                                <span class="badge bg-success-subtle text-success">
                                    <i class="fas fa-check me-1"></i>Enviado
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">
                                    <i class="fas fa-times me-1"></i>No enviado
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-light rounded-3">
                    <div class="avatar-sm bg-success-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="fas fa-user text-success"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-semibold">Cliente</h6>
                        <p class="mb-0 text-muted">
                            {{ $reserva->cliente->alias }}
                            <a href="{{ route('clientes.show', $reserva->cliente_id) }}" class="btn btn-outline-info btn-sm ms-2">
                                <i class="fas fa-eye"></i>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-light rounded-3">
                    <div class="avatar-sm bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="fas fa-users text-primary"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-semibold">Número de Adultos</h6>
                        <p class="mb-0 text-muted">{{ $reserva->numero_personas }}</p>
                    </div>
                </div>
            </div>
            
            @if($reserva->numero_ninos > 0)
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-light rounded-3">
                    <div class="avatar-sm bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="fas fa-child text-warning"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-semibold">Niños</h6>
                        <p class="mb-0 text-muted">
                            {{ $reserva->numero_ninos }} niño(s)
                            @if($reserva->edades_ninos && count($reserva->edades_ninos) > 0)
                                <br><small class="text-muted">
                                    Edades: {{ implode(', ', $reserva->edades_ninos) }} años
                                </small>
                            @endif
                            @if($reserva->notas_ninos)
                                <br><small class="text-muted">
                                    <i class="fas fa-sticky-note me-1"></i>{{ $reserva->notas_ninos }}
                                </small>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            @endif
            
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-light rounded-3">
                    <div class="avatar-sm bg-{{ $reserva->verificado ? 'success' : 'warning' }}-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="fas fa-{{ $reserva->verificado ? 'check-circle' : 'exclamation-triangle' }} text-{{ $reserva->verificado ? 'success' : 'warning' }}"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-semibold">Estado de Verificación</h6>
                        <p class="mb-0">
                            @if($reserva->verificado)
                                <span class="badge bg-success-subtle text-success">
                                    <i class="fas fa-check me-1"></i>Verificada
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Pendiente de verificación
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            
            @if($reserva->numero_personas_plataforma && $reserva->numero_personas_plataforma != $reserva->numero_personas)
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-light rounded-3">
                    <div class="avatar-sm bg-info-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="fas fa-users-cog text-info"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-semibold">Adultos en Plataforma</h6>
                        <p class="mb-0 text-muted">{{ $reserva->numero_personas_plataforma }}</p>
                    </div>
                </div>
            </div>
            @endif
            
            @if($reserva->fecha_limpieza)
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-light rounded-3">
                    <div class="avatar-sm bg-info-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="fas fa-broom text-info"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-semibold">Fecha de Limpieza</h6>
                        <p class="mb-0 text-muted">{{ \Carbon\Carbon::parse($reserva->fecha_limpieza)->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>
            @endif
            
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-light rounded-3">
                    <div class="avatar-sm bg-secondary-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="fas fa-link text-secondary"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-semibold">Enlace para DNI</h6>
                        <p class="mb-0">
                            <a href="http://crm.apartamentosalgeciras.com/dni-user/{{ $reserva->token }}" target="_blank" class="text-decoration-none">
                                <i class="fas fa-external-link-alt me-1"></i>Ver enlace
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="d-flex align-items-center p-3 bg-light rounded-3">
                    <div class="avatar-sm bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="fas fa-file-invoice text-warning"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-semibold">Facturación</h6>
                        <p class="mb-0">
                            @if (isset($factura))
                                <span class="badge bg-success-subtle text-success">
                                    <i class="fas fa-check me-1"></i>Facturada: {{ \Carbon\Carbon::parse($factura->fecha)->format('d/m/Y') }}
                                </span>
                            @else
                                <button id="facturar" class="btn btn-info btn-sm" data-reserva-id="{{ $reserva->id }}">
                                    <i class="fas fa-file-invoice me-1"></i>Facturar
                                </button>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        @if(count($huespedes) > 0)
        <div class="row mt-4">
            <div class="col-12">
                <h6 class="fw-semibold mb-3">
                    <i class="fas fa-users text-primary me-2"></i>
                    Huéspedes
                </h6>
                <div class="row g-2">
                    @foreach ($huespedes as $index => $huesped)
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-2 bg-light rounded-3">
                            <div class="avatar-sm bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                                <i class="fas fa-user text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 fw-semibold">Huésped {{ $index + 1 }}</h6>
                            </div>
                            <a href="{{ route('huespedes.show', $huesped->id) }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Mensajes Enviados -->
@if(count($mensajes) > 0)
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">
            <i class="fas fa-comments text-primary me-2"></i>
            Mensajes Enviados
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="border-0">
                            <i class="fas fa-calendar text-primary me-1"></i>
                            Fecha de Envío
                        </th>
                        <th scope="col" class="border-0">
                            <i class="fas fa-tag text-primary me-1"></i>
                            Categoría del Mensaje
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mensajes as $mensaje)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-info-subtle rounded-circle d-flex align-items-center justify-content-center me-2">
                                    <i class="fas fa-calendar text-info"></i>
                                </div>
                                <span class="fw-semibold">{{ \Carbon\Carbon::parse($mensaje->fecha_envio)->format('d/m/Y H:i') }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-success-subtle rounded-circle d-flex align-items-center justify-content-center me-2">
                                    <i class="fas fa-tag text-success"></i>
                                </div>
                                <span class="fw-semibold">{{ $mensaje->categoria->nombre }}</span>
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

<!-- Documentos de Identidad -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">
            <i class="fas fa-id-card text-primary me-2"></i>
            Documentos de Identidad
        </h5>
    </div>
    <div class="card-body">
        @if (count($photos) > 1)
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-center">
                        <h6 class="fw-semibold mb-3">
                            <i class="fas fa-id-card text-info me-2"></i>
                            DNI - Frente
                        </h6>
                        <a href="{{ asset($photos[0]->url) }}" data-fancybox="gallery" data-caption="DNI Frente">
                            <img src="{{ asset($photos[0]->url) }}" 
                                 alt="DNI Frente" 
                                 class="img-fluid rounded shadow-sm"
                                 style="object-fit: cover; object-position: center; max-height: 300px; width: 100%; cursor: pointer;">
                        </a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-center">
                        <h6 class="fw-semibold mb-3">
                            <i class="fas fa-id-card text-info me-2"></i>
                            DNI - Reverso
                        </h6>
                        <a href="{{ asset($photos[1]->url) }}" data-fancybox="gallery" data-caption="DNI Reverso">
                            <img src="{{ asset($photos[1]->url) }}" 
                                 alt="DNI Reverso" 
                                 class="img-fluid rounded shadow-sm"
                                 style="object-fit: cover; object-position: center; max-height: 300px; width: 100%; cursor: pointer;">
                        </a>
                    </div>
                </div>
            </div>
        @elseif (count($photos) == 1)
            <div class="text-center">
                <h6 class="fw-semibold mb-3">
                    <i class="fas fa-passport text-warning me-2"></i>
                    Pasaporte
                </h6>
                <a href="{{ asset($photos[0]->url) }}" data-fancybox="gallery" data-caption="Pasaporte">
                    <img src="{{ asset($photos[0]->url) }}" 
                         alt="Pasaporte" 
                         class="img-fluid rounded shadow-sm"
                         style="object-fit: cover; object-position: center; max-height: 300px; width: 100%; cursor: pointer;">
                </a>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-image fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No hay documentos subidos</h5>
                <p class="text-muted">No se han subido fotos de DNI o pasaporte para esta reserva.</p>
            </div>
        @endif
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        $('#facturar').on('click', function() {
            let reservaId = $(this).data('reserva-id'); // Obtener el ID de la reserva

            Swal.fire({
                title: '¿Facturar Reserva?',
                text: '¿Estás seguro de que deseas generar la factura para esta reserva?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0dcaf0',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-file-invoice me-2"></i>Sí, Facturar',
                cancelButtonText: '<i class="fas fa-times me-2"></i>Cancelar',
                customClass: {
                    confirmButton: 'btn btn-info',
                    cancelButton: 'btn btn-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loading
                    Swal.fire({
                        title: 'Procesando...',
                        text: 'Generando la factura, por favor espere.',
                        icon: 'info',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Enviar la solicitud POST usando Fetch
                    fetch(`{{ route('admin.facturas.facturar') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}' // Incluye el token CSRF
                        },
                        body: JSON.stringify({ reserva_id: reservaId }) // Enviar el ID de la reserva
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: '¡Factura Generada!',
                                text: 'La factura se ha generado correctamente.',
                                icon: 'success',
                                confirmButtonText: '<i class="fas fa-check me-2"></i>Continuar',
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                },
                                buttonsStyling: false
                            }).then(() => {
                                location.reload(); // Recargar la página para actualizar el estado
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message || 'Error al generar la factura.',
                                icon: 'error',
                                confirmButtonText: '<i class="fas fa-times me-2"></i>Entendido',
                                customClass: {
                                    confirmButton: 'btn btn-danger'
                                },
                                buttonsStyling: false
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error',
                            text: 'Hubo un error al procesar la solicitud.',
                            icon: 'error',
                            confirmButtonText: '<i class="fas fa-times me-2"></i>Entendido',
                            customClass: {
                                confirmButton: 'btn btn-danger'
                            },
                            buttonsStyling: false
                        });
                    });
                }
            });
        });
    });
</script>

@endsection
