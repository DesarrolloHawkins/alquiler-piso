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
          <button class="nav-link" id="pills-user-tab" data-bs-toggle="pill" data-bs-target="#pills-user" type="button" role="tab" aria-controls="pills-user" aria-selected="true">Credenciales Usuarios</button>
        </li>
        <li class="nav-item mx-2" role="presentation">
          <button class="nav-link" id="pills-contabilidad-tab" data-bs-toggle="pill" data-bs-target="#pills-contabilidad" type="button" role="tab" aria-controls="pills-contabilidad" aria-selected="false">Contabilidad y Gestion</button>
        </li>
        <li class="nav-item mx-2" role="presentation">
          <button class="nav-link active" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Reparaciones</button>
        </li>
        <li class="nav-item mx-2" role="presentation">
          <button class="nav-link" id="pills-disabled-tab" data-bs-toggle="pill" data-bs-target="#pills-disabled" type="button" role="tab" aria-controls="pills-disabled" aria-selected="false">Otros</button>
        </li>
        <li class="nav-item mx-2" role="presentation">
          <button class="nav-link" id="pills-prompt-tab" data-bs-toggle="pill" data-bs-target="#pills-prompt" type="button" role="tab" aria-controls="pills-prompt" aria-selected="false">Prompt Asistente</button>
        </li>
    </ul>
    <div class="tab-content bg-body-secondary p-4 bg-opacity-75 rounded-2" id="pills-tabContent">
        <div class="tab-pane fade" id="pills-user" role="tabpanel" aria-labelledby="pills-user-tab" tabindex="0">
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
        <div class="tab-pane fade show active" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab" tabindex="0">
            <form action="{{route('configuracion.updateReparaciones')}}" method="POST">
                @csrf
                <h4>Reparaciones (Tecnicos)</h4>
                @if (count($reparaciones) > 0)
                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addTecnico">Añadir tecnico</button>
                    @foreach ($reparaciones as $reparacion)
                        <div class="row align-items-end px-2 py-4 bg-light">
                            <div class="col-lg-3 col-sm-12">
                                <label class="form-label">Nombre de la Persona</label>
                                <input class="form-control w-100" name="nombre" value="@isset($reparacion->nombre){{$reparacion->nombre}}@endisset"/>
                            </div>
                            <div class="col-lg-3 col-sm-12">
                                <label class="form-label">Teléfono de la Persona</label>
                                <input class="form-control w-100" name="telefono" value="@isset($reparacion->telefono){{$reparacion->telefono}}@endisset"/>
                            </div>
                            <div class="col-lg-2 col-sm-12">
                                <label class="form-label">Hora Inicio</label>
                                <select class="form-control w-100" name="hora_inicio">
                                    @for ($hour = 0; $hour < 24; $hour++)
                                        @php
                                            $hora1 = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
                                            $hora2 = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':30';
                                        @endphp
                                        <option value="{{ $hora1 }}" @isset($reparacion->hora_inicio) @if($reparacion->hora_inicio == $hora1) selected @endif @endisset>{{ $hora1 }}</option>
                                        <option value="{{ $hora2 }}" @isset($reparacion->hora_inicio) @if($reparacion->hora_inicio == $hora2) selected @endif @endisset>{{ $hora2 }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-lg-2 col-sm-12">
                                <label class="form-label">Hora Fin</label>
                                <select class="form-control w-100" name="hora_fin">
                                    @for ($hour = 0; $hour < 24; $hour++)
                                        @php
                                            $hora1 = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
                                            $hora2 = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':30';
                                        @endphp
                                        <option value="{{ $hora1 }}" @isset($reparacion->hora_fin) @if($reparacion->hora_fin == $hora1) selected @endif @endisset>{{ $hora1 }}</option>
                                        <option value="{{ $hora2 }}" @isset($reparacion->hora_fin) @if($reparacion->hora_fin == $hora2) selected @endif @endisset>{{ $hora2 }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-lg-2 col-sm-12 mt-sm-3">
                                <button type="submit" class="btn btn-danger w-100">Eliminar</button>    
                            </div>
                        </div>                    
                    @endforeach
                    
                @else
                    <h6>No se añadieron tecnicos</h6>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTecnico">Añadir tecnico</button>
                @endif

            </form>
            <div class="modal fade" id="addTecnico" tabindex="-1" aria-labelledby="addTecnicoLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addTecnicoLabel">Añadir Persona para Notificaciones</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="{{route('configuracion.emails.add')}}">
                                @csrf
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                                <div class="mb-3">
                                    <label for="telefono" class="form-label">Telefono</label>
                                    <input type="text" class="form-control" id="telefono" name="telefono" placeholder="34600600600">
                                </div>
                                <div class="mb-3">
                                    <label for="emailAddress" class="form-label">Dirección de Email</label>
                                    <input type="email" class="form-control" id="emailAddress" name="email" required>
                                </div>
                                <button id="addEmailForm" type="button" class="btn btn-primary">Añadir</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade " id="pills-disabled" role="tabpanel" aria-labelledby="pills-disabled-tab" tabindex="0">
            <h4>Notificaciones</h4>
            @if (count($emailsNotificaciones) > 0)
                <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addEmailModal">Añadir persona</button>
                <ul style="margin-left: 0; padding-left: 0">
                    @foreach ($emailsNotificaciones as $person)                    
                        <li class="row align-items-end mb-3">
                            <div class="col-md-3 col-sm-12">
                                <label class="form-label" for="nombre">Nombre</label>
                                <input data-id="{{$person->id}}" id="input_persona" class="form-control" name="nombre" type="text" value="{{$person->nombre}}">
                            </div>
                            <div class="col-md-4 col-sm-12">
                                <label class="form-label" for="email">Email</label>
                                <input data-id="{{$person->id}}" id="input_persona" class="form-control" name="email" type="text" value="{{$person->email}}">
                            </div>
                            <div class="col-md-3 col-sm-12">
                                <label class="form-label" for="phone">Telefono</label>
                                <input data-id="{{$person->id}}" id="input_persona" class="form-control" name="telefono" type="text" value="{{$person->telefono}}">
                            </div>
                            <div class="col-md-2 col-sm-12">
                                <button id="deletePerson" data-id="{{$person->id}}" class="btn btn-danger">Eliminar</button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <h6>No se añadieron personas para recibir las notificaciones</h6>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmailModal">Añadir persona</button>
            @endif
            <!-- Modal -->
            <div class="modal fade" id="addEmailModal" tabindex="-1" aria-labelledby="addEmailModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addEmailModalLabel">Añadir Persona para Notificaciones</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="{{route('configuracion.emails.add')}}">
                                @csrf
                                <div class="mb-3">
                                    <label for="emailAddress" class="form-label">Dirección de Email</label>
                                    <input type="email" class="form-control" id="emailAddress" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                                <div class="mb-3">
                                    <label for="telefono" class="form-label">Telefono</label>
                                    <input type="text" class="form-control" id="telefono" name="telefono" placeholder="34600600600">
                                </div>
                                <button id="addEmailForm" type="button" class="btn btn-primary">Añadir</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="pills-prompt" role="tabpanel" aria-labelledby="pills-prompt-tab" tabindex="0">
            <form action="{{route('configuracion.actualizarPrompt')}}" method="POST">
                @csrf
                <label class="form-label" for="prompt">Prompt - Asistente de la Inteligencia Artificial</label>
              
                <textarea rows="25" type="text" name="prompt" id="prompt" class="form-control">@if (count($prompt) > 0) {{ $prompt[0]->prompt }} @else {{ '' }} @endif</textarea>
                <button class="btn btn-guardar mt-3 text-uppercase">Actualizar</button>
            </form>
        </div>
    </div>
</div>
@endsection

{{-- @include('sweetalert::alert') --}}

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            console.log('Apartamentos Hawkins')
            // Verificar si SweetAlert2 está definido
            if (typeof Swal === 'undefined') {
                console.error('SweetAlert2 is not loaded');
                return;
            }

            const inputsFormasPago = document.querySelectorAll('#input_formas')
            inputsFormasPago.forEach(function(nodo){
                $(nodo).on('change', function(){
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
            
            const deleteFormasPago = document.querySelectorAll('#delete_btn')

            deleteFormasPago.forEach(function(nodo){
                $(nodo).on('click', function(){
                    var id = $(this).attr('data-id'); // Corregido: ahora correctamente obtiene el atributo data-id
                    var baseUrl = "{{ route('formaPago.delete', ['id' => ':id']) }}"; // Genera una URL base con un placeholder
                    var url = baseUrl.replace(':id', id); // Reemplaza el placeholder por el id real

                    var formData = new FormData();
                    
                    formData.append('_token', '{{ csrf_token() }}');


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

            $('#addEmailForm').on('click', function(e) {
                e.preventDefault(); // Evita el envío tradicional del formulario
                console.log('prueba')
                var formData = {
                    email: $('#emailAddress').val(),
                    nombre: $('#nombre').val(),
                    telefono: $('#telefono').val(),
                    _token: '{{ csrf_token() }}' // Token CSRF para seguridad de Laravel
                };

                $.ajax({
                    url: '{{ route("configuracion.emails.add") }}', // Cambia esto por la ruta adecuada
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        console.log(response)
                        //$('#addEmailModal').modal('hide'); // Cierra el modal
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
                                window.location.href = response.redirect_url;
                            }
                        });
                        Toast.fire({
                            icon: "success",
                            title: 'La persona para notificación se añadio correctamente'
                        });

                    },
                    error: function(xhr, status, error) {
                        console.error('Error al añadir persona:', error);
                        Swal.fire({
                            title: 'Error',
                            text: 'No se pudo añadir la persona.',
                            icon: 'error',
                            confirmButtonText: 'Cerrar'
                        });
                    }
                });
            });

            const botonesDeleteUser = document.querySelectorAll('#deletePerson')
            botonesDeleteUser.forEach(function(nodo){
                $(nodo).on('click', function(){
                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: "¡No podrás revertir esto!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, eliminar!',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var id = $(this).attr('data-id'); // Corregido: ahora correctamente obtiene el atributo data-id
                            var baseUrl = "{{ route('configuracion.emails.delete', ['id' => ':id']) }}"; // Genera una URL base con un placeholder
                            var url = baseUrl.replace(':id', id); // Reemplaza el placeholder por el id real

                            var formData = new FormData();
                    
                            formData.append('_token', '{{ csrf_token() }}');

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
                                        title: 'Persona de contacto eliminada correctamente'
                                    });
                                    // console.log('Archivo enviado con éxito:', data);
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error al enviar el archivo:', error);
                                }
                            });                    }
                    });
                    
                })
            })

            const inputsPersonasEmails = document.querySelectorAll('#input_persona')
            // console.log(inputsPersonasEmails)
            inputsPersonasEmails.forEach(function(nodo){
                // console.log($(nodo))
                $(nodo).on('change', function(){
                    var nuevoValor = this.value;
                    var id = $(this).attr('data-id');
                    var propiedad = $(this).attr('name');

                    var baseUrl = "{{ route('configuracion.emails.update', ['id' => ':id']) }}"; // Genera una URL base con un placeholder
                    var url = baseUrl.replace(':id', id); // Reemplaza el placeholder por el id real

                    var formData = new FormData();
                    
                    formData.append('_token', '{{ csrf_token() }}'); // Añade el token CSRF aquí
                    formData.append(propiedad, nuevoValor); // Añade el token CSRF aquí

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
                                title: 'Persona de contacto actualizada correctamente'
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

