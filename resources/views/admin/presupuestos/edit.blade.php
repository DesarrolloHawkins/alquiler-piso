@extends('layouts.appAdmin')

@section('content')
<div class="container">
    <h1>Editar Presupuesto</h1>

    <form action="{{ route('presupuestos.update', $presupuesto->id) }}" method="POST" id="formPresupuesto">
        @csrf
        @method('PUT')

        <!-- Paso 1: Cliente y fecha -->
        <div id="step1" class="step active">
            <h4>Paso 1: Cliente y Fecha</h4>

            <div class="mb-3">
                <label for="cliente_id" class="form-label">Cliente</label>
                <select name="cliente_id" id="cliente_id" class="form-select">
                    <option value="">Seleccionar cliente</option>
                    @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}" @selected($cliente->id == old('cliente_id', $presupuesto->cliente_id))>
                        {{ $cliente->nombre }} - {{ $cliente->email }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha del Presupuesto</label>
                <input type="date" name="fecha" id="fecha" class="form-control"
                    value="{{ old('fecha', \Carbon\Carbon::parse($presupuesto->fecha)->toDateString()) }}">
            </div>

            <button type="button" class="btn btn-primary next-step">Siguiente</button>
        </div>

        <!-- Paso 2: Conceptos -->
        <div id="step2" class="step">
            <h4>Paso 2: Conceptos</h4>

            <table class="table" id="conceptosTable">
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>€/día</th>
                        <th>Días</th>
                        <th>Total</th>
                        <th>Eliminar</th>
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
                        <td><button type="button" class="btn btn-danger btn-sm removeConcepto">X</button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <button type="button" id="addConcepto" class="btn btn-secondary btn-sm">+ Añadir Concepto</button>

            <div class="mt-3">
                <button type="button" class="btn btn-secondary prev-step">Atrás</button>
                <button type="button" class="btn btn-primary next-step">Siguiente</button>
            </div>
        </div>

        <!-- Paso 3: Revisión -->
        <div id="step3" class="step">
            <h4>Paso 3: Revisión Final</h4>
            <p id="clienteSeleccionado"></p>
            <div id="conceptosResumen"></div>
            <p id="resumenTotal" class="fw-bold fs-5"></p>

            <div class="mt-3">
                <button type="button" class="btn btn-secondary prev-step">Atrás</button>
                <button type="submit" class="btn btn-success">Guardar Cambios</button>
            </div>
        </div>
    </form>
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
