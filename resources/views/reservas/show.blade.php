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
                {{-- Titulo --}}
                {{-- <div class="card-header">
                    <h4 class="mb-0"><i class="fa-solid fa-info"></i> {{ __('Información de la Reserva :') }} </h4>
                </div>      --}}
                <table class="table">
                    <tbody>
                        <tr>
                            <th style="width: 140px" scope="row">Apartamento</th>
                            <td>
                                {{$reserva->apartamento->titulo}}
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Edificio</th>
                            <td>
                                {{$reserva->apartamento->edificioName->nombre}}
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Origen</th>
                            <td>{{$reserva->origen}}</td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Fecha Entrada</th>
                            <td>{{$reserva->fecha_entrada}}</td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Fecha Salida</th>
                            <td>{{$reserva->fecha_salida}}</td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Precio</th>
                            <td>{{$reserva->precio}}</td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Dni Entregado</th>
                            <td>
                                @if($reserva->dni_entregado == 1) <span class="badge text-bg-success">Entregado</span> @else <span class="badge text-bg-danger">No entregado</span>@endif
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Cliente</th>
                            <td>{{$reserva->cliente->alias}} <a href="{{route('clientes.show', $reserva->cliente_id)}}" class="btn bg-color-quinto ms-3"><i class="fa-regular fa-eye"></i> </a></td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Huespeds</th>
                            {{-- {{dd($huespedes)}} --}}
                            @foreach ($huespedes as $index => $huesped)
                                <td>Huesped {{$index+1}} <a href="{{route('huespedes.show', $huesped->id)}}" class="btn bg-color-quinto ms-3"><i class="fa-regular fa-eye"></i> </a></td>
                            @endforeach
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Fecha Limpieza</th>
                            <td>{{$reserva->fecha_limpieza}}</td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Numero de Adultos</th>
                            <td>{{$reserva->numero_personas}}</td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Enviado a Webpol</th>
                            <td>
                                @if($reserva->enviado_webpol == 1)
                                    <span class="badge text-bg-success">Entregado</span>
                                @else
                                    <span class="badge text-bg-danger">No entregado</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Enlace para DNI</th>
                            <td><a href="http://crm.apartamentosalgeciras.com/dni-user/{{$reserva->token}}">http://crm.apartamentosalgeciras.com/dni-user/{{$reserva->token}}</a></td>
                        </tr>
                        <tr>
                            <th style="width: 140px" scope="row">Facturado</th>
                            <td>
                                @if (isset($factura))
                                    <span>Reserva Facturada: {{$factura->fecha}}</span>
                                @else
                                    <button id="facturar" class="btn btn-info text-white" data-reserva-id="{{ $reserva->id }}">Facturar</button>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="card mb-4">
                    <div class="card-body">
                        <h4><i class="fa-regular fa-comment"></i> Mensajes enviado</h4>
                        <hr>
                        <table class="table">
                            <thead>
                                <tr>
                                <th scope="col">Fecha de Envio</th>
                                <th scope="col">Categoria del Mensaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (count($mensajes) > 0)
                                    @foreach ($mensajes as $mensaje)
                                    <tr>
                                        <th scope="row">{{$mensaje->fecha_envio}}</th>
                                        <td>{{$mensaje->categoria->nombre}}</td>
                                        </tr>
                                    @endforeach
                                @endif


                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        @if (count($photos) > 1)
                            <h4><i class="fa-regular fa-address-card"></i> DNI</h4>
                            <hr>
                            <div class="row">
                                <div class="col-6">
                                    <a href="{{ asset($photos[0]->url) }}" data-fancybox="gallery" data-caption="DNI Frente">
                                        <img src="{{ asset($photos[0]->url) }}" alt="DNI Frente" style="object-fit: cover; object-position: center; max-height: 200px; width: 100%;">
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="{{ asset($photos[1]->url) }}" data-fancybox="gallery" data-caption="DNI Reverso">
                                        <img src="{{ asset($photos[1]->url) }}" alt="DNI Reverso" style="object-fit: cover; object-position: center; max-height: 200px; width: 100%;">
                                    </a>
                                </div>
                            </div>
                        @elseif (count($photos) == 1)
                            <h4><i class="fa-regular fa-address-card"></i> Pasaporte</h4>
                            <hr>
                            <a href="{{ asset($photos[0]->url) }}" data-fancybox="gallery" data-caption="Pasaporte">
                                <img src="{{ asset($photos[0]->url) }}" alt="Pasaporte" style="object-fit: cover; object-position: center; max-height: 200px; width: 100%;">
                            </a>
                        @else
                            <h4><i class="fa-regular fa-address-card"></i> DNI o Pasaporte</h4>
                            <hr>
                            <p>No se subieron ninguna fotos.</p>
                        @endif
                    </div>
                </div>
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
