@extends('layouts.appAdmin')

@section('title', 'Mensajes Personalizados')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 48px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 48px;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px;
        right: 10px;
    }
    .select2-container .select2-search--inline .select2-search__field {
        margin-top: 8px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-paper-plane me-2"></i>
                        Enviar Mensaje Personalizado
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.mensajes-personalizados.enviar') }}" method="POST" id="formMensaje">
                        @csrf

                        <!-- Canal de envío -->
                        <div class="mb-4">
                            <label for="canal" class="form-label fw-bold">
                                <i class="fas fa-broadcast-tower me-2"></i>
                                Canal de Envío
                            </label>
                            <select name="canal" id="canal" class="form-select form-select-lg" required>
                                <option value="">Seleccione un canal</option>
                                <option value="whatsapp" {{ old('canal') == 'whatsapp' ? 'selected' : '' }}>
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </option>
                                <option value="channex" {{ old('canal') == 'channex' ? 'selected' : '' }}>
                                    <i class="fas fa-comments"></i> Channex Chat
                                </option>
                            </select>
                            <small class="form-text text-muted">
                                Seleccione el canal por el cual desea enviar el mensaje
                            </small>
                        </div>

                        <!-- Tipo de destinatario -->
                        <div class="mb-4">
                            <label for="tipo_destinatario" class="form-label fw-bold">
                                <i class="fas fa-user-tag me-2"></i>
                                Tipo de Destinatario
                            </label>
                            <select name="tipo_destinatario" id="tipo_destinatario" class="form-select form-select-lg" required>
                                <option value="">Seleccione el tipo</option>
                                <option value="cliente" {{ old('tipo_destinatario') == 'cliente' ? 'selected' : '' }}>
                                    Cliente (por nombre)
                                </option>
                                <option value="reserva" {{ old('tipo_destinatario') == 'reserva' ? 'selected' : '' }}>
                                    Reserva (por código de reserva)
                                </option>
                                <option value="telefono_directo" {{ old('tipo_destinatario') == 'telefono_directo' ? 'selected' : '' }}>
                                    Teléfono Directo
                                </option>
                                <option value="booking_id" {{ old('tipo_destinatario') == 'booking_id' ? 'selected' : '' }}>
                                    Booking ID de Channex
                                </option>
                            </select>
                            <small class="form-text text-muted">
                                Seleccione cómo identificar al destinatario
                            </small>
                        </div>

                        <!-- Selector de Cliente -->
                        <div class="mb-4" id="selector_cliente" style="display: none;">
                            <label for="cliente_id" class="form-label fw-bold">
                                <i class="fas fa-user me-2"></i>
                                Cliente
                            </label>
                            <select name="cliente_id" id="cliente_id" class="form-select select2-cliente">
                                <option value="">Seleccione un cliente</option>
                                @foreach($clientes as $cliente)
                                    @php
                                        // Construir nombre completo
                                        $nombreCompleto = trim(($cliente->nombre ?? '') . ' ' . ($cliente->apellido1 ?? '') . ' ' . ($cliente->apellido2 ?? ''));
                                        $nombreCompleto = $nombreCompleto ?: ($cliente->alias ?? 'Sin nombre');
                                        
                                        // Construir información adicional
                                        $infoAdicional = [];
                                        if ($cliente->email) {
                                            $infoAdicional[] = $cliente->email;
                                        }
                                        if ($cliente->telefono) {
                                            $infoAdicional[] = 'Tel: ' . $cliente->telefono;
                                        }
                                        if ($cliente->telefono_movil) {
                                            $infoAdicional[] = 'Móvil: ' . $cliente->telefono_movil;
                                        }
                                        if ($cliente->num_identificacion) {
                                            $infoAdicional[] = 'ID: ' . $cliente->num_identificacion;
                                        }
                                        if ($cliente->alias && $nombreCompleto !== $cliente->alias) {
                                            $infoAdicional[] = 'Alias: ' . $cliente->alias;
                                        }
                                        
                                        $textoCompleto = $nombreCompleto;
                                        if (!empty($infoAdicional)) {
                                            $textoCompleto .= ' | ' . implode(' | ', $infoAdicional);
                                        }
                                    @endphp
                                    <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }} data-email="{{ $cliente->email ?? '' }}" data-telefono="{{ $cliente->telefono ?? '' }}">
                                        {{ $textoCompleto }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                Escriba para buscar por nombre, email o teléfono
                            </small>
                        </div>

                        <!-- Selector de Reserva -->
                        <div class="mb-4" id="selector_reserva" style="display: none;">
                            <label for="reserva_id" class="form-label fw-bold">
                                <i class="fas fa-calendar-check me-2"></i>
                                Reserva
                            </label>
                            <select name="reserva_id" id="reserva_id" class="form-select select2-reserva">
                                <option value="">Seleccione una reserva</option>
                                @foreach($reservas as $reserva)
                                    @php
                                        // Nombre del cliente
                                        $nombreCliente = trim(($reserva->cliente->nombre ?? '') . ' ' . ($reserva->cliente->apellido1 ?? '') . ' ' . ($reserva->cliente->apellido2 ?? ''));
                                        $nombreCliente = $nombreCliente ?: ($reserva->cliente->alias ?? 'Sin nombre');
                                        
                                        // Información de la reserva
                                        $infoReserva = [];
                                        $infoReserva[] = 'Código: ' . ($reserva->codigo_reserva ?? 'N/A');
                                        $infoReserva[] = 'Cliente: ' . $nombreCliente;
                                        if ($reserva->apartamento) {
                                            $infoReserva[] = 'Apto: ' . $reserva->apartamento->nombre;
                                        }
                                        if ($reserva->fecha_entrada) {
                                            $infoReserva[] = 'Entrada: ' . $reserva->fecha_entrada->format('d/m/Y');
                                        }
                                        if ($reserva->fecha_salida) {
                                            $infoReserva[] = 'Salida: ' . $reserva->fecha_salida->format('d/m/Y');
                                        }
                                        if ($reserva->cliente && $reserva->cliente->email) {
                                            $infoReserva[] = 'Email: ' . $reserva->cliente->email;
                                        }
                                        if ($reserva->cliente && $reserva->cliente->telefono) {
                                            $infoReserva[] = 'Tel: ' . $reserva->cliente->telefono;
                                        }
                                        
                                        $textoCompleto = implode(' | ', $infoReserva);
                                    @endphp
                                    <option value="{{ $reserva->id }}" {{ old('reserva_id') == $reserva->id ? 'selected' : '' }}>
                                        {{ $textoCompleto }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                Escriba para buscar por código de reserva, cliente o apartamento
                            </small>
                        </div>

                        <!-- Teléfono Directo -->
                        <div class="mb-4" id="selector_telefono" style="display: none;">
                            <label for="nombre_cliente_telefono" class="form-label fw-bold">
                                <i class="fas fa-user me-2"></i>
                                Nombre del Cliente
                            </label>
                            <input type="text" 
                                   name="nombre_cliente_telefono" 
                                   id="nombre_cliente_telefono" 
                                   class="form-control form-control-lg" 
                                   placeholder="Ej: Juan Pérez"
                                   value="{{ old('nombre_cliente_telefono') }}">
                            <small class="form-text text-muted">
                                Nombre del destinatario (se usará como variable 1 en el template de WhatsApp)
                            </small>
                            
                            <label for="telefono" class="form-label fw-bold mt-3">
                                <i class="fas fa-phone me-2"></i>
                                Número de Teléfono
                            </label>
                            <input type="text" 
                                   name="telefono" 
                                   id="telefono" 
                                   class="form-control form-control-lg" 
                                   placeholder="Ej: +34612345678"
                                   value="{{ old('telefono') }}">
                            <small class="form-text text-muted">
                                Incluya el código de país (ej: +34 para España)
                            </small>
                        </div>

                        <!-- Booking ID -->
                        <div class="mb-4" id="selector_booking_id" style="display: none;">
                            <label for="booking_id" class="form-label fw-bold">
                                <i class="fas fa-hashtag me-2"></i>
                                Booking ID de Channex
                            </label>
                            <input type="text" 
                                   name="booking_id" 
                                   id="booking_id" 
                                   class="form-control form-control-lg" 
                                   placeholder="Ej: 12345678"
                                   value="{{ old('booking_id') }}">
                            <small class="form-text text-muted">
                                ID de la reserva en Channex
                            </small>
                        </div>

                        <!-- Editor de Mensaje -->
                        <div class="mb-4">
                            <label for="mensaje" class="form-label fw-bold">
                                <i class="fas fa-comment-dots me-2"></i>
                                Mensaje Personalizado
                            </label>
                            <textarea name="mensaje" 
                                      id="mensaje" 
                                      class="form-control" 
                                      rows="10" 
                                      required
                                      placeholder="Escriba su mensaje personalizado aquí... Este mensaje se enviará como variable 2 en el template de WhatsApp.">{{ old('mensaje') }}</textarea>
                            <div class="alert alert-info mt-2" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Información:</strong> 
                                <ul class="mb-0 mt-2">
                                    <li>El mensaje se enviará usando el template <strong>informacion_al_cliente</strong> de WhatsApp</li>
                                    <li><strong>Variable 1:</strong> Nombre del cliente (se obtiene automáticamente)</li>
                                    <li><strong>Variable 2:</strong> Su mensaje personalizado (lo que escriba arriba)</li>
                                    <li>El idioma se detecta automáticamente según el idioma del cliente</li>
                                    <li>Idiomas disponibles: Español, Inglés, Francés, Alemán, Italiano, Árabe, Portugués</li>
                                </ul>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <strong>Caracteres:</strong> <span id="contador_caracteres">0</span> / 5000
                                </small>
                            </div>
                        </div>

                        <!-- Vista Previa -->
                        <div class="mb-4">
                            <button type="button" class="btn btn-outline-secondary" id="btnVistaPrevia">
                                <i class="fas fa-eye me-2"></i>
                                Vista Previa
                            </button>
                            <div id="vista_previa" class="mt-3 p-3 bg-light border rounded" style="display: none;">
                                <h6 class="fw-bold mb-2">Vista Previa del Mensaje:</h6>
                                <div id="vista_previa_contenido" class="text-muted">
                                    <!-- Se mostrará aquí la vista previa -->
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>
                                Enviar Mensaje
                            </button>
                            <button type="reset" class="btn btn-secondary btn-lg">
                                <i class="fas fa-redo me-2"></i>
                                Limpiar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tipoDestinatario = document.getElementById('tipo_destinatario');
        const selectorCliente = document.getElementById('selector_cliente');
        const selectorReserva = document.getElementById('selector_reserva');
        const selectorTelefono = document.getElementById('selector_telefono');
        const selectorBookingId = document.getElementById('selector_booking_id');
        const mensaje = document.getElementById('mensaje');
        const contadorCaracteres = document.getElementById('contador_caracteres');
        const btnVistaPrevia = document.getElementById('btnVistaPrevia');
        const vistaPrevia = document.getElementById('vista_previa');
        const vistaPreviaContenido = document.getElementById('vista_previa_contenido');

        // Inicializar Select2 para cliente y reserva
        function inicializarSelect2() {
            // Inicializar Select2 para cliente
            if ($('#cliente_id').length && !$('#cliente_id').hasClass('select2-hidden-accessible')) {
                $('#cliente_id').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Seleccione un cliente',
                    allowClear: true,
                    width: '100%',
                    language: {
                        noResults: function() {
                            return "No se encontraron resultados";
                        },
                        searching: function() {
                            return "Buscando...";
                        }
                    }
                });
            }

            // Inicializar Select2 para reserva
            if ($('#reserva_id').length && !$('#reserva_id').hasClass('select2-hidden-accessible')) {
                $('#reserva_id').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Seleccione una reserva',
                    allowClear: true,
                    width: '100%',
                    language: {
                        noResults: function() {
                            return "No se encontraron resultados";
                        },
                        searching: function() {
                            return "Buscando...";
                        }
                    }
                });
            }
        }

        // Inicializar Select2 al cargar la página
        inicializarSelect2();

        // Mostrar/ocultar selectores según el tipo de destinatario
        tipoDestinatario.addEventListener('change', function() {
            // Ocultar todos los selectores
            selectorCliente.style.display = 'none';
            selectorReserva.style.display = 'none';
            selectorTelefono.style.display = 'none';
            selectorBookingId.style.display = 'none';

            // Mostrar el selector correspondiente
            switch(this.value) {
                case 'cliente':
                    selectorCliente.style.display = 'block';
                    document.getElementById('cliente_id').required = true;
                    document.getElementById('reserva_id').required = false;
                    document.getElementById('telefono').required = false;
                    document.getElementById('booking_id').required = false;
                    // Inicializar Select2 para cliente si no está inicializado
                    setTimeout(function() {
                        if (!$('#cliente_id').hasClass('select2-hidden-accessible')) {
                            $('#cliente_id').select2({
                                theme: 'bootstrap-5',
                                placeholder: 'Seleccione un cliente',
                                allowClear: true,
                                width: '100%',
                                language: {
                                    noResults: function() {
                                        return "No se encontraron resultados";
                                    },
                                    searching: function() {
                                        return "Buscando...";
                                    }
                                }
                            });
                        }
                    }, 100);
                    break;
                case 'reserva':
                    selectorReserva.style.display = 'block';
                    document.getElementById('cliente_id').required = false;
                    document.getElementById('reserva_id').required = true;
                    document.getElementById('telefono').required = false;
                    document.getElementById('booking_id').required = false;
                    // Inicializar Select2 para reserva si no está inicializado
                    setTimeout(function() {
                        if (!$('#reserva_id').hasClass('select2-hidden-accessible')) {
                            $('#reserva_id').select2({
                                theme: 'bootstrap-5',
                                placeholder: 'Seleccione una reserva',
                                allowClear: true,
                                width: '100%',
                                language: {
                                    noResults: function() {
                                        return "No se encontraron resultados";
                                    },
                                    searching: function() {
                                        return "Buscando...";
                                    }
                                }
                            });
                        }
                    }, 100);
                    break;
                case 'telefono_directo':
                    selectorTelefono.style.display = 'block';
                    document.getElementById('cliente_id').required = false;
                    document.getElementById('reserva_id').required = false;
                    document.getElementById('nombre_cliente_telefono').required = true;
                    document.getElementById('telefono').required = true;
                    document.getElementById('booking_id').required = false;
                    break;
                case 'booking_id':
                    selectorBookingId.style.display = 'block';
                    document.getElementById('cliente_id').required = false;
                    document.getElementById('reserva_id').required = false;
                    document.getElementById('telefono').required = false;
                    document.getElementById('booking_id').required = true;
                    break;
            }
        });

        // Contador de caracteres
        mensaje.addEventListener('input', function() {
            const longitud = this.value.length;
            contadorCaracteres.textContent = longitud;
            
            if (longitud > 5000) {
                contadorCaracteres.classList.add('text-danger');
            } else {
                contadorCaracteres.classList.remove('text-danger');
            }
        });

        // Inicializar contador
        contadorCaracteres.textContent = mensaje.value.length;

        // Vista previa
        btnVistaPrevia.addEventListener('click', function() {
            const texto = mensaje.value;
            if (!texto.trim()) {
                alert('Escriba un mensaje para ver la vista previa');
                return;
            }

            // Mostrar cómo se verá el mensaje con el template
            let vistaPreviaTexto = 'Template: informacion_al_cliente\n\n';
            vistaPreviaTexto += 'Variable 1 (Nombre del cliente): Juan Pérez\n';
            vistaPreviaTexto += 'Variable 2 (Su mensaje):\n';
            vistaPreviaTexto += texto;

            vistaPreviaContenido.innerHTML = '<pre style="white-space: pre-wrap; font-family: inherit;">' + vistaPreviaTexto + '</pre>';
            vistaPrevia.style.display = vistaPrevia.style.display === 'none' ? 'block' : 'none';
        });

        // Inicializar selectores al cargar la página
        if (tipoDestinatario.value) {
            tipoDestinatario.dispatchEvent(new Event('change'));
        } else {
            // Si no hay valor seleccionado, inicializar Select2 de todos modos
            inicializarSelect2();
        }
    });
</script>
@endpush
@endsection

