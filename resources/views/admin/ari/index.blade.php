@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Actualizar Tarifas, Disponibilidad y Restricciones</h2>
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <form action="{{ route('ari.updateRates') }}" method="POST">
        @csrf
        <table class="table table-bordered" id="updatesTable">
            <thead>
                <tr>
                    <th>Propiedad</th>
                    <th>Tipo de Habitación</th>
                    <th>Rate Plan</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Tipo de Actualización</th>
                    <th>Valor</th>
                    <th>Restricciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select name="updates[0][property_id]" class="form-select property-select" required>
                            <option value="" disabled selected>Seleccione una propiedad</option>
                            @foreach ($properties as $property)
                                <option value="{{ $property->id_channex }}">{{ $property->nombre }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select name="updates[0][room_type_id]" class="form-select room-type-select" required>
                            <option value="" disabled selected>Seleccione un tipo de habitación</option>
                        </select>
                    </td>
                    <td>
                        <select name="updates[0][rate_plan_id]" class="form-select rate-plan-select" required>
                            <option value="" disabled selected>Seleccione un Rate Plan</option>
                        </select>
                    </td>
                    <td><input type="date" name="updates[0][date_from]" class="form-control" required></td>
                    <td><input type="date" name="updates[0][date_to]" class="form-control"></td>
                    <td>
                        <select name="updates[0][update_type]" class="form-select update-type-select">
                            <option value="{{null}}">Rate</option>
                            <option value="availability">Availability</option>
                            <option value="min_stay">Min Stay</option>
                            <option value="stop_sell">Stop Sell</option>
                            <option value="restrictions">Restrictions</option>
                        </select>
                    </td>
                    <td><input type="text" name="updates[0][value]" class="form-control"></td>
                    <td>
                        <input type="checkbox" name="updates[0][closed_to_arrival]"> Closed to Arrival
                        <input type="checkbox" name="updates[0][closed_to_departure]"> Closed to Departure
                        <input type="number" name="updates[0][min_stay]" class="form-control" placeholder="Min Stay">
                        <input type="number" name="updates[0][max_stay]" class="form-control" placeholder="Max Stay">
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-row">Eliminar</button>
                    </td>
                </tr>
            </tbody>
        </table>
        <button type="button" id="addRow" class="btn btn-secondary">Añadir Fila</button>
        <button type="submit" class="btn btn-primary mt-4 d-block w-100">Enviar</button>
    </form>
</div>

<script>
    let rowIndex = 1;

    document.getElementById('addRow').addEventListener('click', () => {
        const table = document.getElementById('updatesTable').getElementsByTagName('tbody')[0];
        const newRow = table.rows[0].cloneNode(true);

        Array.from(newRow.querySelectorAll('input, select')).forEach((input) => {
            const name = input.getAttribute('name');
            input.setAttribute('name', name.replace(/\d+/, rowIndex));
            input.value = '';

            if (input.classList.contains('room-type-select')) {
                input.innerHTML = '<option value="" disabled selected>Seleccione un tipo de habitación</option>';
            }
        });

        table.appendChild(newRow);
        rowIndex++;
    });

    document.getElementById('updatesTable').addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('tr').remove();
        }
    });

    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('property-select')) {
            const propertyId = e.target.value;
            const roomTypeSelect = e.target.closest('tr').querySelector('.room-type-select');

            fetch(`/channex/ari/room-types/${propertyId}`)
                .then((response) => response.json())
                .then((data) => {
                    roomTypeSelect.innerHTML = '<option value="" disabled selected>Seleccione un tipo de habitación</option>';
                    data.forEach((roomType) => {
                        roomTypeSelect.innerHTML += `<option value="${roomType.id_channex}">${roomType.title}</option>`;
                    });
                })
                .catch((error) => {
                    console.error('Error al cargar tipos de habitación:', error);
                    roomTypeSelect.innerHTML = '<option value="" disabled>Error al cargar</option>';
                });
        }
    });
</script>

<script>
   document.addEventListener('change', (e) => {
    if (e.target.classList.contains('room-type-select')) {
        const roomTypeId = e.target.value;
        const propertyId = e.target.closest('tr').querySelector('.property-select').value;
        const ratePlanSelect = e.target.closest('tr').querySelector('.rate-plan-select');

        if (roomTypeId && propertyId) {
            // Llamada al backend para obtener los Rate Plans desde la base de datos
            fetch(`/channex/rate-plans/${propertyId}/${roomTypeId}`)
                .then((response) => response.json())
                .then((data) => {
                    ratePlanSelect.innerHTML = '<option value="" disabled selected>Seleccione un Rate Plan</option>';
                    data.forEach((ratePlan) => {
                        ratePlanSelect.innerHTML += `<option value="${ratePlan.id_channex}">${ratePlan.title}</option>`;
                    });
                })
                .catch((error) => {
                    console.error('Error al cargar Rate Plans:', error);
                    ratePlanSelect.innerHTML = '<option value="" disabled>Error al cargar</option>';
                });
        }
    }
});

</script>


@endsection
