@extends('layouts.appAdmin')

@section('content')
<style>
    .inactive-sort {
        color: #0F1739;
        text-decoration: none;
    }
    .active-sort {
        color: #757191;
    }
</style>
<div class="container-fluid">
    
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mb-3">{{ __('Configuracion') }}</h2>
            <hr class="mb-2">
            <form action="{{route('configuracion.update', $configuraciones[0]->id)}}" method="POST">
                @csrf
                <div class="px-2">
                    <label class="form-label">User Booking</label>
                    <input class="form-control w-100" name="user_booking" value="{{$configuraciones[0]->user_booking}}"/>
                </div>
                <div class="mt-3 mb-3 px-2">
                    <label class="form-label">Contraseña Booking</label>
                    <input class="form-control w-100" name="password_booking" value="{{$configuraciones[0]->password_booking}}"/>
                </div>
                <div class="mt-3 mb-3 px-2">
                    <label class="form-label">User Airbnb</label>
                    <input class="form-control w-100" name="user_airbnb" value="{{$configuraciones[0]->user_airbnb}}"/>
                </div>
                <div class="mt-3 mb-3 px-2">
                    <label class="form-label">Contraseña Airbnb</label>
                    <input class="form-control w-100" name="password_airbnb" value="{{$configuraciones[0]->password_airbnb}}"/>
                </div>
                <button type="submit" class="btn btn-primary">Actualizar</button>    
            </form>

        </div>
        <div class="col-md-6">
            <h2 class="mb-3">{{ __('Reparaciones') }}</h2>
            <hr class="mb-2">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <form action="{{route('configuracion.updateReparaciones')}}" method="POST">
                        @csrf
                        <div class="">
                            <label class="form-label">Nombre de la Persona</label>
                            <input class="form-control w-50" name="nombre" value="@isset($reparaciones[0]->nombre){{$reparaciones[0]->nombre}}@endisset"/>
                        </div>
                        <div class="mt-3 mb-3">
                            <label class="form-label">Telefono de la Persona</label>
                            <input class="form-control w-50" name="telefono" value="@isset($reparaciones[0]->telefono){{$reparaciones[0]->telefono}}@endisset"/>
                        </div>
                        <button type="submit" class="btn btn-primary">Actualizar</button>    
                    </form>

                </div>
            </div>
        </div>
    </div>
    
    <div class="row justify-content-start">
        <div class="col-md-6">
            <h2 class="mb-3 mt-5">{{ __('Año de Gestión') }}</h2>
            <hr class="mb-2">
            <form action="{{route('configuracion.updateAnio')}}" method="POST">
                @csrf
                <div class="col-md-12">
                    <label class="form-label">Año de Gestión</label>
                    {{-- {{$anio}} --}}
                    <select name="anio" id="anio" class="form-select">
                        <option value="{{null}}">Seleciona año</option>
                        @foreach ($anios as $item)
                            <option @if($item == $anio) selected @endif value="{{$item}}">{{$item}}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Actualizar Año</button>    
            </form>
        </div>
        <div class="col-md-6">
            <h2 class="mb-3 mt-5">{{ __('Formas de Pago') }}</h2>
            <hr class="mb-2">
            @if (count($formasPago) > 0 )
                <button class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#createForma">Crear Metodo</button>

                <ul class="list-group">
                    @foreach ( $formasPago as $forma )
                        <li class="list-group-item d-flex flex-row justify-content-between ">
                            <div class="w-100">
                                <input id="input_formas" data-id="{{$forma->id}}" type="text" value="{{$forma->nombre}}" class="form-control w-100">
                            </div>
                            <div class="w-auto ms-3">
                                <button id="delete_btn" data-id="{{$forma->id}}" class="btn btn-danger">Eliminar</button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <h5>No hay metodos de Pagos introducidos</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createForma">Crear Metodo</button>
                <!-- Modal -->
                
            @endif
            <div class="modal fade" id="createForma" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Crear Forma de Pago</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{route('formaPago.store')}}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <label class="form-label" for="nombre">Nombre de la Forma de Pago</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Enviar</button>
                        </div>
                    </form>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('sweetalert::alert')

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Verificar si SweetAlert2 está definido
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 is not loaded');
            return;
        }

        $('#input_formas').each(function(){
            $(this).on('change', function(){
                var nuevoValor = this.value;
                var id = $(this).attr('data-id'); // Corregido: ahora correctamente obtiene el atributo data-id
                var baseUrl = "{{ route('formaPago.update', ['id' => ':id']) }}"; // Genera una URL base con un placeholder
                var url = baseUrl.replace(':id', id); // Reemplaza el placeholder por el id real

                var formData = new FormData();
                
                formData.append('_token', '{{ csrf_token() }}'); // Añade el token CSRF aquí
                formData.append('nombre', nuevoValor); // Añade el token CSRF aquí

                $.ajax({
                    url: url, // Reemplaza con la URL de tu servidor
                    type: 'POST',
                    data: formData,
                    processData: false,  // Evita que jQuery procese los datos
                    contentType: false,  // Evita que jQuery establezca el tipo de contenido
                    success: function(data) {

                        const Toast = Swal.mixin({
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.onmouseenter = Swal.stopTimer;
                                toast.onmouseleave = Swal.resumeTimer;
                            },
                            didDestroy: () => {
                                // Aquí puedes colocar la acción que desees realizar después de que el toast desaparezca
                                location.reload();
                            }
                        });
                        Toast.fire({
                            icon: "success",
                            title: 'Forma de Pago actualizada correctamente'
                        });
                        // console.log('Archivo enviado con éxito:', data);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al enviar el archivo:', error);
                    }
                });
            })
        })
        
        $('#delete_btn').each(function(){
            $(this).on('click', function(){
                var id = $(this).attr('data-id'); // Corregido: ahora correctamente obtiene el atributo data-id
                var baseUrl = "{{ route('formaPago.delete', ['id' => ':id']) }}"; // Genera una URL base con un placeholder
                var url = baseUrl.replace(':id', id); // Reemplaza el placeholder por el id real
                $.ajax({
                    url: url, // Reemplaza con la URL de tu servidor
                    type: 'POST',
                    data: formData,
                    processData: false,  // Evita que jQuery procese los datos
                    contentType: false,  // Evita que jQuery establezca el tipo de contenido
                    success: function(data) {

                        const Toast = Swal.mixin({
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.onmouseenter = Swal.stopTimer;
                                toast.onmouseleave = Swal.resumeTimer;
                            },
                            didDestroy: () => {
                                // Aquí puedes colocar la acción que desees realizar después de que el toast desaparezca
                                location.reload();
                            }
                        });
                        Toast.fire({
                            icon: "success",
                            title: 'Forma de Pago eliminada correctamente'
                        });
                        // console.log('Archivo enviado con éxito:', data);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al enviar el archivo:', error);
                    }
                });
            })
        })
    });
</script>
@endsection

