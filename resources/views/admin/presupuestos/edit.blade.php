@extends('layouts.appAdmin')

@section('title', 'Editar Presupuesto')

@section('content')
<div class="container-fluid">
    <!-- Header de la Página -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit me-2 text-warning"></i>
                Editar Presupuesto
            </h1>
            <p class="text-muted mb-0">Modifica la información del presupuesto existente</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('presupuestos.index') }}">Presupuestos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar Presupuesto</li>
            </ol>
        </nav>
    </div>

    <!-- Formulario de Edición -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0 fw-semibold text-dark">
                <i class="fas fa-edit me-2 text-primary"></i>
                Edición de Presupuesto
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('presupuestos.update', $presupuesto->id) }}" method="POST" id="formPresupuesto">
                @csrf
                @method('PUT')

                <!-- Paso 1: Cliente y fecha -->
                <div id="step1" class="step active">
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="fw-semibold text-primary mb-3">
                                <i class="fas fa-user me-2"></i>Paso 1: Cliente y Fecha
                            </h4>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="cliente_id" class="form-label fw-semibold">
                                <i class="fas fa-user me-2 text-success"></i>Cliente
                            </label>
                            <select name="cliente_id" id="cliente_id" class="form-select form-select-lg">
                                <option value="">Seleccionar cliente</option>
                                @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" @selected($cliente->id == old('cliente_id', $presupuesto->cliente_id))>
                                    {{ $cliente->nombre }} - {{ $cliente->email }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="fecha" class="form-label fw-semibold">
                                <i class="fas fa-calendar me-2 text-info"></i>Fecha del Presupuesto
                            </label>
                            <input type="date" name="fecha" id="fecha" class="form-control form-control-lg"
                                value="{{ old('fecha', \Carbon\Carbon::parse($presupuesto->fecha)->toDateString()) }}">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-primary btn-lg next-step">
                            <i class="fas fa-arrow-right me-2"></i>Siguiente
                        </button>
                    </div>
                </div>

                <!-- Paso 2: Conceptos -->
                <div id="step2" class="step">
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="fw-semibold text-primary mb-3">
                                <i class="fas fa-list me-2"></i>Paso 2: Conceptos del Presupuesto
                            </h4>
                        </div>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-hover" id="conceptosTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="fw-semibold text-dark">
                                        <i class="fas fa-tag me-2 text-success"></i>Descripción
                                    </th>
                                    <th class="fw-semibold text-dark">
                                        <i class="fas fa-calendar-plus me-2 text-info"></i>Entrada
                                    </th>
                                    <th class="fw-semibold text-dark">
                                        <i class="fas fa-calendar-minus me-2 text-warning"></i>Salida
                                    </th>
                                    <th class="fw-semibold text-dark">
                                        <i class="fas fa-euro-sign me-2 text-primary"></i>€/día
                                    </th>
                                    <th class="fw-semibold text-dark">
                                        <i class="fas fa-clock me-2 text-secondary"></i>Días
                                    </th>
                                    <th class="fw-semibold text-dark">
                                        <i class="fas fa-calculator me-2 text-success"></i>Total
                                    </th>
                                    <th class="fw-semibold text-dark text-center">
                                        <i class="fas fa-trash me-2 text-danger"></i>Eliminar
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($presupuesto->conceptos as $index => $concepto)
                                <tr>
                                    <td><input type="text" name="conceptos[{{ $index }}][descripcion]" class="form-control" value="{{ old("conceptos.$index.descripcion", $concepto->concepto) }}"></td>
                                    <td><input type="date" name="conceptos[{{ $index }}][fecha_entrada]" class="form-control fecha-entrada" value="{{ old("conceptos.$index.fecha_entrada") }}"></td>
                                    <td><input type="date" name="conceptos[{{ $index }}][fecha_salida]" class="form-control fecha-salida" value="{{ old("conceptos.$index.fecha_salida") }}"></td>
                                    <td><input type="number" name="conceptos[{{ $index }}][precio_por_dia]" class="form-control precio-por-dia" step="0.01" value="{{ old("conceptos.$index.precio_por_dia") }}"></td>
                                    <td><input type="number" name="conceptos[{{ $index }}][dias_totales]" class="form-control dias-totales" readonly></td>
                                    <td><input type="number" name="conceptos[{{ $index }}][precio_total]" class="form-control precio-total" step="0.01" readonly></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm removeConcepto">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" id="addConcepto" class="btn btn-secondary btn-lg">
                            <i class="fas fa-plus me-2"></i>Añadir Concepto
                        </button>
                        <div class="d-flex gap-3">
                            <button type="button" class="btn btn-outline-secondary btn-lg prev-step">
                                <i class="fas fa-arrow-left me-2"></i>Atrás
                            </button>
                            <button type="button" class="btn btn-primary btn-lg next-step">
                                <i class="fas fa-arrow-right me-2"></i>Siguiente
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Paso 3: Revisión -->
                <div id="step3" class="step">
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="fw-semibold text-primary mb-3">
                                <i class="fas fa-check-circle me-2"></i>Paso 3: Revisión Final
                            </h4>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-12">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="fw-semibold text-success mb-3">
                                        <i class="fas fa-user me-2"></i>Información del Cliente
                                    </h6>
                                    <p id="clienteSeleccionado" class="mb-0"></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="fw-semibold text-info mb-3">
                                        <i class="fas fa-list me-2"></i>Resumen de Conceptos
                                    </h6>
                                    <div id="conceptosResumen"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card bg-primary-subtle border-0">
                                <div class="card-body text-center">
                                    <p id="resumenTotal" class="fw-bold fs-3 text-primary mb-0"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="button" class="btn btn-outline-secondary btn-lg prev-step">
                            <i class="fas fa-arrow-left me-2"></i>Atrás
                        </button>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-save me-2"></i>Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const steps = document.querySelectorAll('.step');
        let currentStep = 0;

        function showStep(index) {
            steps.forEach((step, i) => step.classList.toggle('active', i === index));
        }

        showStep(currentStep);

        document.querySelectorAll('.next-step').forEach(btn => btn.addEventListener('click', () => {
            currentStep++;
            showStep(currentStep);
        }));

        document.querySelectorAll('.prev-step').forEach(btn => btn.addEventListener('click', () => {
            currentStep--;
            showStep(currentStep);
        }));

        function bindConceptoListeners(row) {
            const entrada = row.querySelector('.fecha-entrada');
            const salida = row.querySelector('.fecha-salida');
            const precio = row.querySelector('.precio-por-dia');

            [entrada, salida, precio].forEach(input => {
                input.addEventListener('change', () => {
                    const e = new Date(entrada.value);
                    const s = new Date(salida.value);
                    const d = (s - e) / (1000 * 60 * 60 * 24) + 1;
                    const dias = d > 0 ? d : 0;
                    const p = parseFloat(precio.value) || 0;

                    row.querySelector('.dias-totales').value = dias;
                    row.querySelector('.precio-total').value = (dias * p).toFixed(2);

                    actualizarTotal();
                });
            });
        }

        function actualizarTotal() {
            let total = 0;
            document.querySelectorAll('.precio-total').forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            document.getElementById('resumenTotal').textContent = `Total: ${total.toFixed(2)} €`;
        }

        document.querySelectorAll('#conceptosTable tbody tr').forEach(bindConceptoListeners);

        document.getElementById('addConcepto').addEventListener('click', function () {
            const tbody = document.querySelector('#conceptosTable tbody');
            const index = tbody.children.length;
            const tr = document.createElement('tr');

            tr.innerHTML = `
                <td><input type="text" name="conceptos[${index}][descripcion]" class="form-control"></td>
                <td><input type="date" name="conceptos[${index}][fecha_entrada]" class="form-control fecha-entrada"></td>
                <td><input type="date" name="conceptos[${index}][fecha_salida]" class="form-control fecha-salida"></td>
                <td><input type="number" name="conceptos[${index}][precio_por_dia]" class="form-control precio-por-dia" step="0.01"></td>
                <td><input type="number" name="conceptos[${index}][dias_totales]" class="form-control dias-totales" readonly></td>
                <td><input type="number" name="conceptos[${index}][precio_total]" class="form-control precio-total" step="0.01" readonly></td>
                <td><button type="button" class="btn btn-danger btn-sm removeConcepto">X</button></td>
            `;

            tbody.appendChild(tr);
            bindConceptoListeners(tr);

            tr.querySelector('.removeConcepto').addEventListener('click', function () {
                tr.remove();
                actualizarTotal();
            });
        });

        document.querySelectorAll('.removeConcepto').forEach(btn => {
            btn.addEventListener('click', function () {
                this.closest('tr').remove();
                actualizarTotal();
            });
        });

        // Paso 3: resumen
        document.querySelectorAll('.next-step').forEach((btn, i, all) => {
            if (i === all.length - 1) {
                btn.addEventListener('click', () => {
                    const cliente = document.querySelector('#cliente_id option:checked').textContent;
                    document.getElementById('clienteSeleccionado').textContent = `Cliente: ${cliente}`;

                    const resumen = [];
                    document.querySelectorAll('#conceptosTable tbody tr').forEach(tr => {
                        const d = tr.querySelector('[name*="[descripcion]"]').value;
                        const e = tr.querySelector('[name*="[fecha_entrada]"]').value;
                        const s = tr.querySelector('[name*="[fecha_salida]"]').value;
                        const dias = tr.querySelector('.dias-totales').value;
                        const total = tr.querySelector('.precio-total').value;
                        resumen.push({ d, e, s, dias, total });
                    });

                    let html = '<table class="table"><thead><tr><th>Descripción</th><th>Entrada</th><th>Salida</th><th>Días</th><th>Total</th></tr></thead><tbody>';
                    resumen.forEach(c => {
                        html += `<tr>
                            <td>${c.d}</td><td>${c.e}</td><td>${c.s}</td><td>${c.dias}</td><td>${c.total} €</td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                    document.getElementById('conceptosResumen').innerHTML = html;

                    actualizarTotal();
                });
            }
        });
    });
</script>

<style>
    .step { display: none; }
    .step.active { display: block; }
</style>
@endsection
