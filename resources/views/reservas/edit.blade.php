@extends('layouts.appAdmin')

@section('content')
<!-- Fancybox CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0.27/dist/fancybox.min.css">

<!-- Fancybox JS -->
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0.27/dist/fancybox.umd.js"></script>

<div class="container-fluid">
    <div class="d-flex flex-colum mb-3">
        <h2 class="mb-0 me-3 encabezado_top">{{ __('Información de la Reserva: ') }}<span class="text-primary align-baseline">{{$reserva->codigo_reserva}}</span></h2>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            {{-- Formulario de Edición --}}
            <div class="card">
                <div class="card-body">
                    <h4><i class="fa-regular fa-edit"></i> Editar Información de la Reserva</h4>
                    <hr>
                    <form action="{{ route('reservas.updateReserva', $reserva->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="apartamento" class="form-label">Apartamento</label>
                            <input type="text" class="form-control" id="apartamento" name="apartamento" value="{{ $reserva->apartamento->titulo }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="origen" class="form-label">Origen</label>
                            <input type="text" class="form-control" id="origen" name="origen" value="{{ $reserva->origen }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="fecha_entrada" class="form-label">Fecha Entrada</label>
                            <input type="date" class="form-control" id="fecha_entrada" name="fecha_entrada" value="{{ $reserva->fecha_entrada }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="fecha_salida" class="form-label">Fecha Salida</label>
                            <input type="date" class="form-control" id="fecha_salida" name="fecha_salida" value="{{ $reserva->fecha_salida }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="precio" class="form-label">Precio</label>
                            <input type="number" step="0.01" class="form-control" id="precio" name="precio" value="{{ $reserva->precio }}" required>
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Actualizar Reserva</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Rest of the content... --}}

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        $('#facturar').on('click', function() {
            let reservaId = $(this).data('reserva-id'); // Obtener el ID de la reserva

            // Confirmación opcional
            if (!confirm('¿Estás seguro de que deseas facturar esta reserva?')) {
                return;
            }

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
                    alert('Factura generada correctamente.');
                    location.reload(); // Recargar la página para actualizar el estado
                } else {
                    alert(data.message || 'Error al generar la factura.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Hubo un error al procesar la solicitud.');
            });
        });
    });
</script>

@endsection
