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
    <h2 class="mb-3">{{ __('Configuracion') }}</h2>
    <hr class="mb-2">
    <ul class="nav nav-pills mb-3 mt-3" id="pills-tab" role="tablist">
        <li class="nav-item mx-2" role="presentation">
          <button class="nav-link active" id="pills-user-tab" data-bs-toggle="pill" data-bs-target="#pills-user" type="button" role="tab" aria-controls="pills-user" aria-selected="true">Credenciales Usuarios</button>
        </li>
        <li class="nav-item mx-2" role="presentation">
          <button class="nav-link" id="pills-contabilidad-tab" data-bs-toggle="pill" data-bs-target="#pills-contabilidad" type="button" role="tab" aria-controls="pills-contabilidad" aria-selected="false">Contabilidad y Gestion</button>
        </li>
        <li class="nav-item mx-2" role="presentation">
          <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Reparaciones</button>
        </li>
        <li class="nav-item mx-2" role="presentation">
          <button class="nav-link" id="pills-disabled-tab" data-bs-toggle="pill" data-bs-target="#pills-disabled" type="button" role="tab" aria-controls="pills-disabled" aria-selected="false">Otros</button>
        </li>
    </ul>
    <div class="tab-content bg-body-secondary p-4 bg-opacity-75 rounded-2" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-user" role="tabpanel" aria-labelledby="pills-user-tab" tabindex="0">
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
        <div class="tab-pane fade" id="pills-contabilidad" role="tabpanel" aria-labelledby="pills-contabilidad-tab" tabindex="0">
            <form action="{{route('configuracion.updateAnio')}}" method="POST">
                @csrf
                <div class="col-md-12">.

                    <h5 class="form-label">Saldo Inicial</h5>
                    {{-- {{$anio}} --}}
                    <input type="text" name="saldo_inicial" id="saldo_inicial" class="form-control w-auto"/>
                        
                </div>
                <button type="submit" class="btn btn-primary mt-3">Actualizar Saldo Inicial</button>    
            </form>
            <hr class="mb-4">
            <form action="{{route('configuracion.updateAnio')}}" method="POST">
                @csrf
                <div class="col-md-12">.

                    <h5 class="form-label">Año de Gestión</h5>
                    {{-- {{$anio}} --}}
                    <select name="anio" id="anio" class="form-select w-auto">
                        <option value="{{null}}">Seleciona año</option>
                        @foreach ($anios as $item)
                            <option @if($item == $anio) selected @endif value="{{$item}}">{{$item}}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Actualizar Año</button>    
            </form>
            <hr class="mt-4">
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
            <hr class="mt-4">
            <h5>Cierre del año contable</h5>
            <form action="{{route('configuracion.updateAnio')}}" method="POST">
                @csrf
                <div class="col-md-12">
                    <label class="form-label">Año de Gestión</label>
                    {{-- {{$anio}} --}}
                    <select name="anio" id="anio" class="form-select w-auto">
                        <option value="{{null}}">Seleciona año</option>
                        @foreach ($anios as $item)
                            <option @if($item == $anio) selected @endif value="{{$item}}">{{$item}}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Cierre del año</button>    
            </form>
        </div>
        <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab" tabindex="0">
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
        <div class="tab-pane fade" id="pills-disabled" role="tabpanel" aria-labelledby="pills-disabled-tab" tabindex="0">
            <form action="" method="POST">
                @csrf
                <label for="email_notificaciones">Emails para notificaciones</label>
                <input type="text" name="email_notificaciones" id="email_notificaciones" class="form-control">
            </form>
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

