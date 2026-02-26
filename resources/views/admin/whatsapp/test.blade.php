@extends('layouts.appAdmin')

@section('title', 'Test de Templates WhatsApp')

@section('tituloSeccion', 'Test de Templates WhatsApp')

@section('content')
<style>
    .test-container {
        padding: 24px;
        background: #F2F2F7;
        min-height: calc(100vh - 80px);
    }

    .test-card {
        background: #FFFFFF;
        border-radius: 16px;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
        border: none;
        margin-bottom: 24px;
    }

    .test-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid #E5E5EA;
        background: linear-gradient(135deg, #F2F2F7 0%, #E5E5EA 100%);
        border-radius: 16px 16px 0 0;
    }

    .test-card-header h5 {
        font-size: 18px;
        font-weight: 600;
        color: #1D1D1F;
        margin: 0;
    }

    .test-card-body {
        padding: 24px;
    }

    .form-label {
        font-weight: 600;
        color: #1D1D1F;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #E5E5EA;
        padding: 10px 12px;
        transition: all 0.2s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #007AFF;
        box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
    }

    .btn-primary {
        background: #007AFF;
        border: none;
        border-radius: 8px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .btn-primary:hover {
        background: #0051D5;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 122, 255, 0.3);
    }

    .parameter-input {
        margin-bottom: 12px;
    }

    .alert {
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 16px;
    }
</style>

<div class="test-container">
    <div class="test-card">
        <div class="test-card-header">
            <h5>Enviar Template de Prueba</h5>
        </div>
        <div class="test-card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('admin.whatsapp.test.send') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="template_name" class="form-label">Template</label>
                    <select name="template_name" id="template_name" class="form-select" required>
                        <option value="">Selecciona un template</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->name }}" data-components="{{ json_encode($template->components ?? []) }}">
                                {{ $template->name }} 
                                @if($template->status)
                                    <span class="badge bg-{{ $template->status === 'APPROVED' ? 'success' : 'warning' }}">
                                        {{ $template->status }}
                                    </span>
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Selecciona el template que deseas probar</small>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Número de Teléfono</label>
                    <input type="text" 
                           name="phone" 
                           id="phone" 
                           class="form-control" 
                           placeholder="34612345678" 
                           required
                           pattern="[0-9+\s\-()]+">
                    <small class="form-text text-muted">Formato: 34612345678 (con código de país, sin espacios ni guiones)</small>
                </div>

                <div id="parameters-container">
                    <label class="form-label">Parámetros del Template</label>
                    <small class="form-text text-muted d-block mb-3">Los parámetros se agregarán automáticamente según el template seleccionado</small>
                    <div id="parameters-list"></div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Enviar Test
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(session('debug_data'))
        <div class="test-card">
            <div class="test-card-header">
                <h5>
                    <i class="fas fa-bug"></i> Información de Debug
                    <button class="btn btn-sm btn-outline-secondary float-end" onclick="toggleDebug()">
                        <i class="fas fa-chevron-down" id="debug-toggle-icon"></i>
                    </button>
                </h5>
            </div>
            <div class="test-card-body" id="debug-section" style="display: none;">
                @php
                    $debug = session('debug_data');
                @endphp

                <!-- Resumen -->
                <div class="mb-4">
                    <h6 class="text-primary"><i class="fas fa-info-circle"></i> Resumen</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th width="200">Timestamp</th>
                                <td>{{ $debug['timestamp'] ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Template</th>
                                <td>
                                    {{ $debug['template_name'] ?? 'N/A' }}
                                    @if(isset($debug['template_status']))
                                        <span class="badge bg-{{ $debug['template_status'] === 'APPROVED' ? 'success' : 'warning' }}">
                                            {{ $debug['template_status'] }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Teléfono Original</th>
                                <td>{{ $debug['phone_original'] ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Teléfono Normalizado</th>
                                <td>{{ $debug['phone_normalized'] ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Status Code HTTP</th>
                                <td>
                                    <span class="badge bg-{{ ($debug['status_code'] ?? 0) >= 200 && ($debug['status_code'] ?? 0) < 300 ? 'success' : 'danger' }}">
                                        {{ $debug['status_code'] ?? 'N/A' }}
                                    </span>
                                </td>
                            </tr>
                            @if(isset($debug['message_id']))
                            <tr>
                                <th>Message ID</th>
                                <td>
                                    <code>{{ $debug['message_id'] }}</code>
                                    <button class="btn btn-sm btn-outline-primary ms-2" onclick="refreshMessageStatus('{{ $debug['message_id'] }}')">
                                        <i class="fas fa-sync-alt"></i> Actualizar Estado
                                    </button>
                                </td>
                            </tr>
                            @endif
                            @if(isset($debug['success']))
                            <tr>
                                <th>Resultado</th>
                                <td>
                                    <span class="badge bg-success">Éxito</span>
                                </td>
                            </tr>
                            @endif
                            @if(isset($debug['error']))
                            <tr>
                                <th>Error</th>
                                <td>
                                    <span class="badge bg-danger">Error {{ $debug['error']['code'] ?? 'N/A' }}</span>
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- Errores -->
                @if(isset($debug['error']))
                <div class="mb-4">
                    <h6 class="text-danger"><i class="fas fa-exclamation-triangle"></i> Detalles del Error</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th width="200">Código</th>
                                <td><code>{{ $debug['error']['code'] ?? 'N/A' }}</code></td>
                            </tr>
                            <tr>
                                <th>Tipo</th>
                                <td>{{ $debug['error']['type'] ?? 'N/A' }}</td>
                            </tr>
                            @if(isset($debug['error']['subcode']))
                            <tr>
                                <th>Subcódigo</th>
                                <td><code>{{ $debug['error']['subcode'] }}</code></td>
                            </tr>
                            @endif
                            <tr>
                                <th>Mensaje</th>
                                <td>{{ $debug['error']['message'] ?? 'N/A' }}</td>
                            </tr>
                            @if(isset($debug['error']['fbtrace_id']))
                            <tr>
                                <th>FB Trace ID</th>
                                <td><code>{{ $debug['error']['fbtrace_id'] }}</code></td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
                @endif

                <!-- Estado del Mensaje en BD -->
                @if(isset($debug['mensaje_db']))
                <div class="mb-4">
                    <h6 class="text-info"><i class="fas fa-database"></i> Estado en Base de Datos</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th width="200">ID Mensaje</th>
                                <td>{{ $debug['mensaje_db']['id'] ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Estado Actual</th>
                                <td>
                                    @php
                                        $estado = $debug['mensaje_db']['estado_actual'] ?? 'unknown';
                                        $badgeClass = $estado === 'delivered' ? 'success' : 
                                                     ($estado === 'failed' ? 'danger' : 
                                                     ($estado === 'read' ? 'info' : 'warning'));
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }}">
                                        {{ $estado }}
                                    </span>
                                    @if($estado === 'failed')
                                        <span class="text-danger ms-2"><i class="fas fa-exclamation-triangle"></i> El mensaje falló</span>
                                    @endif
                                </td>
                            </tr>
                            @if(isset($debug['mensaje_db']['conversacion_id']))
                            <tr>
                                <th>Conversación ID</th>
                                <td><code>{{ $debug['mensaje_db']['conversacion_id'] }}</code></td>
                            </tr>
                            @endif
                            @if(isset($debug['mensaje_db']['categoria_precio']))
                            <tr>
                                <th>Categoría Precio</th>
                                <td>{{ $debug['mensaje_db']['categoria_precio'] }}</td>
                            </tr>
                            @endif
                            @if(isset($debug['mensaje_db']['billable']))
                            <tr>
                                <th>Facturable</th>
                                <td>{{ $debug['mensaje_db']['billable'] ? 'Sí' : 'No' }}</td>
                            </tr>
                            @endif
                            @if(isset($debug['mensaje_db']['fecha_mensaje']))
                            <tr>
                                <th>Fecha Mensaje</th>
                                <td>{{ $debug['mensaje_db']['fecha_mensaje'] }}</td>
                            </tr>
                            @endif
                            @if(!empty($debug['mensaje_db']['estados_historial']))
                            <tr>
                                <th>Historial de Estados</th>
                                <td>
                                    <ul class="mb-0">
                                        @foreach($debug['mensaje_db']['estados_historial'] as $estado)
                                        <li>
                                            @php
                                                $badgeClass = $estado['estado'] === 'delivered' ? 'success' : 
                                                             ($estado['estado'] === 'failed' ? 'danger' : 
                                                             ($estado['estado'] === 'read' ? 'info' : 'secondary'));
                                            @endphp
                                            <span class="badge bg-{{ $badgeClass }}">{{ $estado['estado'] }}</span>
                                            - {{ $estado['fecha'] ?? 'N/A' }}
                                        </li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                            @endif
                            @if(!empty($debug['mensaje_db']['errores_detalle']))
                            <tr>
                                <th>Errores Detallados</th>
                                <td>
                                    @foreach($debug['mensaje_db']['errores_detalle'] as $error)
                                    <div class="alert alert-danger mb-2">
                                        <strong><i class="fas fa-exclamation-circle"></i> Error {{ $error['codigo'] ?? 'N/A' }}</strong>
                                        @if(isset($error['tipo']))
                                            <span class="badge bg-secondary ms-2">{{ $error['tipo'] }}</span>
                                        @endif
                                        @if(isset($error['subcodigo']))
                                            <span class="badge bg-warning ms-2">Subcódigo: {{ $error['subcodigo'] }}</span>
                                        @endif
                                        <br>
                                        <strong>Título:</strong> {{ $error['titulo'] ?? 'N/A' }}<br>
                                        <strong>Mensaje:</strong> {{ $error['mensaje'] ?? 'N/A' }}
                                        @if(isset($error['raw']))
                                        <details class="mt-2">
                                            <summary class="text-muted" style="cursor: pointer;">Ver detalles técnicos</summary>
                                            <pre class="bg-light p-2 rounded mt-2" style="font-size: 11px;"><code>{{ json_encode($error['raw'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                        </details>
                                        @endif
                                    </div>
                                    @endforeach
                                </td>
                            </tr>
                            @elseif(!empty($debug['mensaje_db']['errores']))
                            <tr>
                                <th>Errores (Raw)</th>
                                <td>
                                    <pre class="bg-light p-2 rounded"><code>{{ json_encode($debug['mensaje_db']['errores'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
                @endif

                <!-- Payload Enviado -->
                @if(isset($debug['payload']))
                <div class="mb-4">
                    <h6 class="text-primary"><i class="fas fa-paper-plane"></i> Payload Enviado</h6>
                    <pre class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;"><code>{{ json_encode($debug['payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                </div>
                @endif

                <!-- Respuesta de la API -->
                @if(isset($debug['response_json']))
                <div class="mb-4">
                    <h6 class="text-success"><i class="fas fa-reply"></i> Respuesta de WhatsApp API</h6>
                    <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><code>{{ json_encode($debug['response_json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                </div>
                @endif

                <!-- Response Body Raw -->
                @if(isset($debug['response_body']))
                <div class="mb-4">
                    <h6 class="text-secondary"><i class="fas fa-file-code"></i> Response Body (Raw)</h6>
                    <pre class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;"><code>{{ $debug['response_body'] }}</code></pre>
                </div>
                @endif

                <!-- URL -->
                @if(isset($debug['url']))
                <div class="mb-4">
                    <h6 class="text-info"><i class="fas fa-link"></i> URL de la API</h6>
                    <code>{{ $debug['url'] }}</code>
                </div>
                @endif

                <!-- Excepciones -->
                @if(isset($debug['exception']))
                <div class="mb-4">
                    <h6 class="text-danger"><i class="fas fa-exclamation-circle"></i> Excepción</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th width="200">Mensaje</th>
                                <td>{{ $debug['error_message'] ?? 'N/A' }}</td>
                            </tr>
                            @if(isset($debug['error_trace']))
                            <tr>
                                <th>Stack Trace</th>
                                <td>
                                    <pre class="bg-light p-2 rounded" style="max-height: 200px; overflow-y: auto; font-size: 11px;"><code>{{ $debug['error_trace'] }}</code></pre>
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
                @endif

                <!-- Error de BD -->
                @if(isset($debug['db_error']))
                <div class="mb-4">
                    <h6 class="text-warning"><i class="fas fa-database"></i> Error al Guardar en BD</h6>
                    <div class="alert alert-warning">
                        {{ $debug['db_error'] }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    @endif

    <div class="test-card">
        <div class="test-card-header">
            <h5>Información</h5>
        </div>
        <div class="test-card-body">
            <p><strong>Nota:</strong> Este test enviará un mensaje real de WhatsApp al número especificado usando el template seleccionado.</p>
            <p>Los parámetros del template se pueden dejar vacíos si el template no los requiere.</p>
            <p>Si el envío falla, se creará una alerta en el panel de administrador.</p>
            <p><strong>Debug:</strong> Después de cada envío, se mostrará una sección con toda la información técnica (payload, respuesta, errores, etc.)</p>
        </div>
    </div>
</div>

<script>
function toggleDebug() {
    const debugSection = document.getElementById('debug-section');
    const toggleIcon = document.getElementById('debug-toggle-icon');
    
    if (debugSection.style.display === 'none') {
        debugSection.style.display = 'block';
        toggleIcon.classList.remove('fa-chevron-down');
        toggleIcon.classList.add('fa-chevron-up');
    } else {
        debugSection.style.display = 'none';
        toggleIcon.classList.remove('fa-chevron-up');
        toggleIcon.classList.add('fa-chevron-down');
    }
}

// Auto-expandir debug si hay errores
document.addEventListener('DOMContentLoaded', function() {
    @php
        $hasError = session('debug_data') && (isset(session('debug_data')['error']) || isset(session('debug_data')['exception']));
    @endphp
    @if($hasError)
        toggleDebug();
    @endif
});

function refreshMessageStatus(messageId) {
    const btn = event.target.closest('button');
    const icon = btn.querySelector('i');
    
    // Mostrar loading
    icon.classList.add('fa-spin');
    btn.disabled = true;
    
    fetch(`/admin/whatsapp/test/message/${messageId}/status`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recargar la página para mostrar toda la información actualizada
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al consultar el estado del mensaje');
        })
        .finally(() => {
            icon.classList.remove('fa-spin');
            btn.disabled = false;
        });
}

document.addEventListener('DOMContentLoaded', function() {
    const templateSelect = document.getElementById('template_name');
    const parametersList = document.getElementById('parameters-list');

    templateSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const components = JSON.parse(selectedOption.getAttribute('data-components') || '[]');
        
        parametersList.innerHTML = '';

        // Buscar componentes de tipo body (puede ser 'body', 'BODY', etc.)
        const bodyComponent = components.find(c => {
            const type = (c.type || '').toLowerCase();
            return type === 'body';
        });
        
        if (bodyComponent) {
            let paramCount = 0;
            
            // Método 1: Contar variables en el texto del body ({{1}}, {{2}}, etc.)
            const bodyText = bodyComponent.text || bodyComponent.body || bodyComponent.body_text || '';
            if (bodyText) {
                const matches = bodyText.match(/\{\{(\d+)\}\}/g);
                if (matches) {
                    const numbers = matches.map(m => parseInt(m.replace(/\{\{|\}\}/g, '')));
                    paramCount = numbers.length > 0 ? Math.max(...numbers) : 0;
                }
            }
            
            // Método 2: Si tiene example, contar los parámetros del ejemplo
            if (paramCount === 0 && bodyComponent.example) {
                const exampleBody = bodyComponent.example.body_text || bodyComponent.example.body || [];
                if (Array.isArray(exampleBody) && exampleBody.length > 0) {
                    const firstExample = exampleBody[0];
                    if (Array.isArray(firstExample)) {
                        paramCount = firstExample.length;
                    }
                }
            }
            
            // Método 3: Si tiene parámetros definidos directamente
            if (paramCount === 0 && bodyComponent.parameters && Array.isArray(bodyComponent.parameters)) {
                paramCount = bodyComponent.parameters.length;
            }
            
            if (paramCount > 0) {
                parametersList.innerHTML = `<p class="text-info mb-3"><i class="fas fa-info-circle"></i> Este template requiere <strong>${paramCount}</strong> parámetros</p>`;
                
                for (let i = 0; i < paramCount; i++) {
                    const div = document.createElement('div');
                    div.className = 'parameter-input';
                    div.innerHTML = `
                        <label for="param_${i}" class="form-label">Parámetro ${i + 1} <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="parameters[]" 
                               id="param_${i}" 
                               class="form-control" 
                               placeholder="Valor para el parámetro ${i + 1}"
                               required>
                    `;
                    parametersList.appendChild(div);
                }
            } else {
                parametersList.innerHTML = '<p class="text-muted"><i class="fas fa-check-circle"></i> Este template no requiere parámetros en el body</p>';
            }
        } else {
            parametersList.innerHTML = '<p class="text-warning"><i class="fas fa-exclamation-triangle"></i> No se pudo detectar la estructura del template. Intenta sincronizar los templates desde el panel de administración.</p>';
        }
    });
});
</script>

@endsection
