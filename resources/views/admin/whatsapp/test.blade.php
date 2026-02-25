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

    <div class="test-card">
        <div class="test-card-header">
            <h5>Información</h5>
        </div>
        <div class="test-card-body">
            <p><strong>Nota:</strong> Este test enviará un mensaje real de WhatsApp al número especificado usando el template seleccionado.</p>
            <p>Los parámetros del template se pueden dejar vacíos si el template no los requiere.</p>
            <p>Si el envío falla, se creará una alerta en el panel de administrador.</p>
        </div>
    </div>
</div>

<script>
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
