@extends('layouts.appUser')

@section('bienvenido')
    <h5 class="navbar-brand mb-0 w-auto text-center">Rellene el formulario para confirmar su reserva</h5>
@endsection

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<div class="container-fluid">
    <div class="row" style="display: none">
        <div class="col-sm-12 text-center">
            <img src="https://apartamentosalgeciras.com/wp-content/uploads/2022/09/Logo-Hawkins-Suites.svg" alt="" class="img-fluid mb-3 w-50 m-auto">
        </div>
        <div class="col-sm-12">
            
            <div class="card">
                <div class="card-header bg-color-primero">
                    Rellene los datos con su DNI o Pasaporte
                </div>
                <div class="card-body">
                    <form action="{{route('dni.store')}}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{$id}}">
                        <div class="mb-3">
                            <input class="form-control" type="text" placeholder="Nombre" aria-label="Nombre" name="nombre" id="nombre" value="{{ old('nombre')}}">
                            @error('nombre')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <input class="form-control" type="text" placeholder="Primer Apellido" aria-label="Primer Apellido" name="apellido1" id="apellido1" value="{{ old('apellido1')}}">
                            @error('apellido1')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <input class="form-control" type="text" placeholder="Segundo Apellido" aria-label="Segundo Apellido" name="apellido2" id="apellido2" value="{{ old('apellido2')}}">
                            @error('apellido2')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <select name="nacionalidad" id="nacionalidad" class="form-control js-example-basic-single" placeholder="DNI o Pasaporte" aria-label="DNI o Pasaporte" >
                                <option value="{{null}}">Seleccione Pais</option>
                                @foreach ($paises as $pais)
                                <option value="{{$pais}}"  {{ (old('nacionalidad') == $pais ? 'selected' : '') }}>{{$pais}}</option>
                                @endforeach
                            </select>
                            @error('nacionalidad')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <select name="tipo_documento" id="tipo_documento" class="form-control" placeholder="DNI o Pasaporte" aria-label="DNI o Pasaporte">
                                <option value="{{null}}">DNI o Pasaporte</option>
                                <option value="0"  {{ (old('tipo_documento') == '0' ? 'selected' : '') }}>DNI</option>
                                <option value="1" {{ (old('tipo_documento') == '1' ? 'selected' : '') }}>Pasaporte</option>
                            </select>
                            @error('tipo_documento')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <input class="form-control" type="text" placeholder="Numero Identificación" aria-label="Numero Identificación" name="num_identificacion" id="num_identificacion" value="{{ old('num_identificacion')}}">
                            @error('num_identificacion')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="fecha_expedicion_doc">Fecha de Expedición</label>
                            <input class="form-control" type="date" placeholder="Fecha de Expedición" aria-label="Fecha de Expedición" name="fecha_expedicion_doc" id="fecha_expedicion_doc" value="{{ old('fecha_expedicion_doc')}}">
                            @error('fecha_expedicion_doc')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                            <input class="form-control" type="date" placeholder="Fecha de Nacimiento" aria-label="Fecha de Nacimiento" name="fecha_nacimiento" id="fecha_nacimiento" value="{{ old('fecha_nacimiento')}}">
                            @error('fecha_nacimiento')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <select name="sexo" id="sexo" class="form-control" placeholder="Sexo" aria-label="Sexo">
                                <option value="{{null}}">Seleccione Sexo</option>
                                <option value="Masculino" {{ (old('tipo_documento') == 'Masculino' ? 'selected' : '') }}>Masculino</option>
                                <option value="Femenino" {{ (old('tipo_documento') == 'Femenino' ? 'selected' : '') }}>Femenino</option>
                                <option value="Binario" {{ (old('tipo_documento') == 'Binario' ? 'selected' : '') }}>Binario</option>
                            </select>
                            @error('sexo')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <input class="form-control" type="text" placeholder="Correo Electronico" aria-label="Correo Electronico" name="email" id="email" value="{{ old('email')}}">
                            @error('email')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                           <button class="btn btn-terminar w-100">Enviar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @if ($reserva->numero_personas == 0 || $reserva->numero_personas == null)
        <div class="row">
            <div class="col-sm-12 text-center">
                <img src="https://apartamentosalgeciras.com/wp-content/uploads/2022/09/Logo-Hawkins-Suites.svg" alt="" class="img-fluid mb-3 w-50 m-auto">
            </div>
            <div class="col-sm-12">
                
                <div class="card">
                    <div class="card-header bg-color-primero">
                        Para poder continuar debes decirnos el numero de adultos (mayores de 18 años), que van ocupar la reserva.
                    </div>
                    <div class="card-body">
                        {{-- <form class="row g-3 needs-validation" novalidate>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="nombre" placeholder="Escriba su nombre..." required>
                                    <label for="nombre">Nombre</label>
                                    <div class="valid-feedback">
                                        Correcto!
                                    </div>
                                    <div class="invalid-feedback">
                                        Por favor el nombre es obligatorio.
                                    </div>
                                </div>
                            
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com" required>
                                    <label for="floatingInput">Email address</label>
                                </div>
                                <div class="valid-feedback">
                                    Looks good!
                                </div>
                                <div class="invalid-feedback">
                                    Please choose a username.
                                </div>
                            </div>
                            <div class="col-md-4">
                            <label for="validationCustomUsername" class="form-label">Username</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text" id="inputGroupPrepend">@</span>
                                <input type="text" class="form-control" id="validationCustomUsername" aria-describedby="inputGroupPrepend" required>
                                <div class="invalid-feedback">
                                Please choose a username.
                                </div>
                            </div>
                            </div>
                            <div class="col-md-6">
                            <label for="validationCustom03" class="form-label">City</label>
                            <input type="text" class="form-control" id="validationCustom03" required>
                            <div class="invalid-feedback">
                                Please provide a valid city.
                            </div>
                            </div>
                            <div class="col-md-3">
                            <label for="validationCustom04" class="form-label">State</label>
                            <select class="form-select" id="validationCustom04" required>
                                <option selected disabled value="">Choose...</option>
                                <option>...</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select a valid state.
                            </div>
                            </div>
                            <div class="col-md-3">
                            <label for="validationCustom05" class="form-label">Zip</label>
                            <input type="text" class="form-control" id="validationCustom05" required>
                            <div class="invalid-feedback">
                                Please provide a valid zip.
                            </div>
                            </div>
                            <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="invalidCheck" required>
                                <label class="form-check-label" for="invalidCheck">
                                Agree to terms and conditions
                                </label>
                                <div class="invalid-feedback">
                                You must agree before submitting.
                                </div>
                            </div>
                            </div>
                            <div class="col-12">
                            <button class="btn btn-primary" type="submit">Submit form</button>
                            </div>
                        </form> --}}
                        <div class="row align-items-center">
                            <div class="col-3"> Numero de Adultos:</div>
                            <div class="col-4">
                                <input type="number" id="numero" value="1" min="1" step="1" class="form-control w-100">
                                <input type="hidden" name="idReserva" id="idReserva" value="{{$id}}">
                            </div>
                            <div class="col-2">
                                <button id="sumar" class="w-100 btn btn-info">+</button>
                            </div>
                            <div class="col-2">
                                <button id="restar" class="w-100 btn btn-info">-</button>
                            </div>
                            <div class="col-1"></div>

                        </div>
                        <button id="enviar" class="btn btn-primary w-100 mt-3">Enviar</button>
                    </div>
                </div>
            </div>
        </div> 
    @endif
    @if ($reserva->numero_personas != 0 || $reserva->numero_personas != null)
        <div class="row">
            <div class="col-sm-12 text-center">
                <img src="https://apartamentosalgeciras.com/wp-content/uploads/2022/09/Logo-Hawkins-Suites.svg" alt="" class="img-fluid mb-3 w-50 m-auto">
            </div>
            <div class="col-sm-12">
                
                <div class="card">
                    <div class="card-header bg-color-primero">
                        Debes rellenar los datos para verificar el numero de personas que ya añadiste.
                    </div>
                    <div class="card-body">
                        <div id="formularios">
                            <form action="{{route('dni.store')}}" method="POST" class="row g-3 needs-validation" novalidate>
                                @csrf
                                <input type="hidden" name="id" value="{{$id}}">
                                @for ($i = 0; $i < $reserva->numero_personas; $i++)
                                    <div class="card-body">
                                        @if ($i == 0)
                                            <h3>Huesped Principal</h3>
                                        @else
                                            <h3>Acompañante {{$i}}</h3>
                                        @endif
                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <input name="nombre_{{$i}}" type="text" class="form-control" id="nombre_{{$i}}" placeholder="Escriba su nombre" required>
                                                <label for="nombre_{{$i}}">Nombre</label>
                                                <div class="valid-feedback">
                                                    Correcto!
                                                </div>
                                                <div class="invalid-feedback">
                                                    El nombre es obligatorio.
                                                </div>
                                                @error('nombre_{{$i}}')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div> 
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <input name="apellido1_{{$i}}" type="text" class="form-control" id="apellido1_{{$i}}" placeholder="Escriba su primer apellido" required>
                                                <label for="apellido1_{{$i}}">Primer Apellido</label>
                                                <div class="valid-feedback">
                                                    Correcto!
                                                </div>
                                                <div class="invalid-feedback">
                                                    El primer apellido es obligatorio.
                                                </div>
                                                @error('apellido1_{{$i}}')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div> 
                                        </div>
                                        
                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <input name="apellido2_{{$i}}" type="text" class="form-control" id="apellido2_{{$i}}" placeholder="Escriba su segundo apellido">
                                                <label for="apellido2_{{$i}}">Segundo Apellido</label>
                                                <div class="valid-feedback">
                                                    Correcto!
                                                </div>
                                                <div class="invalid-feedback">
                                                    El primer apellido es obligatorio.
                                                </div>
                                                @error('apellido2_{{$i}}')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div> 
                                        </div>

                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <input name="fecha_nacimiento_{{$i}}" type="date" class="form-control" id="fecha_nacimiento_{{$i}}" placeholder="Fecha de Nacimiento" aria-label="Fecha de Nacimiento" required>
                                                <label for="fecha_nacimiento_{{$i}}">Fecha de Nacimiento</label>
                                                <div class="valid-feedback">
                                                    Correcto!
                                                </div>
                                                <div class="invalid-feedback">
                                                    La fecha de nacimiento es obligatoria.
                                                </div>
                                                @error('fecha_nacimiento_{{$i}}')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div> 
                                        </div>

                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <select name="nacionalidad_{{$i}}" id="nacionalidad_{{$i}}" class="form-select js-example-basic-single{{$i}}" aria-label="Pais" placeholder="Pais">
                                                    @foreach ($paises as $pais)
                                                    <option value="{{$pais}}"  {{ (old('nacionalidad_'.$i) == $pais ? 'selected' : '') }}>{{$pais}}</option>
                                                    @endforeach
                                                </select>
                                                <label for="nacionalidad_{{$i}}">Seleccione Pais</label>

                                                <div class="valid-feedback">
                                                    Correcto!
                                                </div>
                                                <div class="invalid-feedback">
                                                    El pais obligatorio.
                                                </div>
                                                @error('nacionalidad_{{$i}}')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div> 
                                        </div>

                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <select data-info="{{$i}}" name="tipo_documento_{{$i}}" id="tipo_documento_{{$i}}" class="form-select tiposDocumentos" aria-label="DNI o Pasaporte" placeholder="DNI o Pasaporte">
                                                    <option value="{{null}}">Seleccion el tipo</option>
                                                    <option value="0"  {{ (old('tipo_documento') == '0' ? 'selected' : '') }}>DNI</option>
                                                    <option value="1" {{ (old('tipo_documento') == '1' ? 'selected' : '') }}>Pasaporte</option>
                                                </select>
                                                <label for="tipo_documento_{{$i}}">Seleccione tipo de documento</label>

                                                <div class="valid-feedback">
                                                    Correcto!
                                                </div>
                                                <div class="invalid-feedback">
                                                    El primer tipo de documento es obligatorio.
                                                </div>
                                                @error('tipo_documento_{{$i}}')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div> 
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <input name="num_identificacion_{{$i}}" type="text" class="form-control" id="num_identificacion_{{$i}}" placeholder="Numero Identificación" aria-label="Numero Identificación" required>
                                                <label for="num_identificacion_{{$i}}">Numero de Identificación</label>
                                                <div class="valid-feedback">
                                                    Correcto!
                                                </div>
                                                <div class="invalid-feedback">
                                                    El numero de identificación es obligatorio.
                                                </div>
                                                @error('num_identificacion_{{$i}}')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div> 
                                        </div>

                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <input name="fecha_expedicion_doc_{{$i}}" type="date" class="form-control" id="fecha_expedicion_doc_{{$i}}" placeholder="Fecha de Expedición" aria-label="Fecha de Expedición" required>
                                                <label for="fecha_expedicion_doc_{{$i}}">Fecha de Expedición</label>
                                                <div class="valid-feedback">
                                                    Correcto!
                                                </div>
                                                <div class="invalid-feedback">
                                                    La fecha de expedición es obligatoria.
                                                </div>
                                                @error('fecha_expedicion_doc_{{$i}}')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div> 
                                        </div>

                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <select name="sexo_{{$i}}" id="sexo_{{$i}}" class="form-select" aria-label="Sexo" placeholder="Sexo" required>
                                                    <option value="Masculino" {{ (old('sexo_'.$i) == 'Masculino' ? 'selected' : '') }}>Masculino</option>
                                                    <option value="Femenino" {{ (old('sexo_'.$i) == 'Femenino' ? 'selected' : '') }}>Femenino</option>
                                                    <option value="Binario" {{ (old('sexo_'.$i) == 'Binario' ? 'selected' : '') }}>No Binario</option>
                                                </select>
                                                <label for="sexo_{{$i}}">Seleccione Sexo</label>

                                                <div class="valid-feedback">
                                                    Correcto!
                                                </div>
                                                <div class="invalid-feedback">
                                                    El sexo es obligatorio.
                                                </div>
                                                @error('sexo_{{$i}}')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div> 
                                        </div>

                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <input name="email_{{$i}}" type="text" class="form-control" id="email_{{$i}}" placeholder="Correo Electronico" aria-label="Correo Electronico" required>
                                                <label for="email_{{$i}}">Correo Electronico</label>
                                                <div class="valid-feedback">
                                                    Correcto!
                                                </div>
                                                <div class="invalid-feedback">
                                                    El correo electronico es obligatorio.
                                                </div>
                                                @error('email_{{$i}}')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div> 
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <div id="dniUploaed_{{$i}}" style="display: none">
                                                    <h4>Imagen frontal del DNI</h4>
                                                    <div class="files mt-3">
                                                        <input type="file" accept="image/*" class="file-input" capture="camera" name="fontal_{{$i}}" id="fontal_{{$i}}" onchange="previewImage({{$i}},event)" required>
                                                        <button type="button" class="btn btn-secundario fs-5 w-100" onclick="document.getElementById('fontal_{{$i}}').click()"><i class="fa-solid fa-camera me-2"></i> FRONTAL</button>
                                                        <img data-info="{{$i}}" id="image-preview_frontal_{{$i}}" style="max-width: 100%; max-height: auto; margin-top: 10px;"/>
                                                        <div class="valid-feedback">
                                                            Correcto!
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            El correo electronico es obligatorio.
                                                        </div>
                                                        @error('fontal_{{$i}}')
                                                            <div class="alert alert-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <h4>Imagen trasera del DNI</h4>

                                                    <div class="files mt-3">
                                                        <input type="file" accept="image/*" class="file-input" capture="camera" name="trasera_{{$i}}" id="trasera_{{$i}}" onchange="previewImage2({{$i}},event)" required>
                                                        <button type="button" class="btn btn-secundario fs-5 w-100" onclick="document.getElementById('trasera_{{$i}}').click()"><i class="fa-solid fa-camera me-2"></i> TRASERA</button>
                                                        <img data-info="{{$i}}" id="image-preview_trasera_{{$i}}" style="max-width: 100%; max-height: auto; margin-top: 10px;"/>
                                                        <div class="valid-feedback">
                                                            Correcto!
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            El correo electronico es obligatorio.
                                                        </div>
                                                        @error('trasera_{{$i}}')
                                                            <div class="alert alert-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div id="pasaporteUpload_{{$i}}" style="display: none">
                                                    <h4>Imagen de la hoja de informacion del Pasaporte</h4>
                                                    <div class="files mt-3">
                                                        <input type="file" accept="image/*" class="file-input" capture="camera" name="fontal_{{$i}}" id="frontal_{{$i}}" onchange="previewImage3({{$i}},event)" required>
                                                        <button type="button" class="btn btn-secundario fs-5 w-100" onclick="document.getElementById('frontal_{{$i}}').click()"><i class="fa-solid fa-camera me-2"></i> FRONTAL</button>
                                                        <img data-info="{{$i}}" id="image-preview_pasaporte_{{$i}}" style="max-width: 65%; max-height: auto; margin-top: 10px;"/>
                                                        <div class="valid-feedback">
                                                            Correcto!
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            El correo electronico es obligatorio.
                                                        </div>
                                                        @error('fontal_{{$i}}')
                                                            <div class="alert alert-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div> 
                                        </div>
                                    </div>
                                @endfor  
                                <div class="mb-3">
                                    <button class="btn btn-terminar w-100">Enviar</button>
                                </div>  
                            </form>    
                        </div>    
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
<style>
    .file-input {
      display: none;
    }
    .select2-container .select2-selection--single {
        height: 55px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 71px;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        height: 37px;
    }
</style>
@endsection
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // In your Javascript (external .js resource or <script> tag)
    $(document).ready(function() {
        var cantidadPersonas = @json($reserva->numero_personas);
        for (let i = 0; i < cantidadPersonas; i++) {
            $('.js-example-basic-single'+i).select2();
        }

        $('#sumar').on('click', function(){
            let valor = parseInt($('#numero').val(), 10); // Convierte el valor a un número entero
            valor += 1; 
            $('#numero').val(valor);
            console.log($('#numero').val())
        })
        $('#restar').on('click', function(){
            let valor = parseInt($('#numero').val(), 10); // Convierte el valor a un número entero
            valor -= 1; 
            $('#numero').val(valor);
            console.log($('#numero').val())
        })

        $('#enviar').click(function() {
            var cantidad = $('#numero').val();
            var id = $('#idReserva').val();
            console.log(id)
            $.ajax({
                url: '/guardar-numero-personas',
                method: 'POST',
                data: {
                    cantidad: cantidad,
                    id: id,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // $('#formularios').html('');
                    // for (var i = 0; i < cantidad; i++) {
                    //     $('#formularios').append(`<form><input type="text" name="campo${i}"></form>`);
                    // }
                    window.location.reload();
                }
            });
        });
        var tipoDocumento = document.querySelectorAll('.tiposDocumentos')
        console.log(tipoDocumento)
        tipoDocumento.forEach( function(tipo){
            tipo.addEventListener('change', function() {
                var valor = this.value;
                var info = this.getAttribute('data-info')
                console.log(valor)
                console.log(info)
                if (valor === '0') {
                    // dniUploaed - pasaporteUpload
                    document.getElementById('dniUploaed_'+info).style.display = 'block';
                    document.getElementById('pasaporteUpload_'+info).style.display = 'none';

                } else if (valor === '1') {
                    document.getElementById('dniUploaed_'+info).style.display = 'none';
                    document.getElementById('pasaporteUpload_'+info).style.display = 'block';

                } else {

                }
            });
        })
    });
    // Example starter JavaScript for disabling form submissions if there are invalid fields
    (function () {
    'use strict'

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.querySelectorAll('.needs-validation')

    // Loop over them and prevent submission
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
            }

            form.classList.add('was-validated')
        }, false)
        })
    })()

    function previewImage(info, event) {
        console.log(info)
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('image-preview_frontal_'+info);
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
    

    function previewImage2(info, event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('image-preview_trasera_'+info);
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
    function previewImage3(info, event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('image-preview_pasaporte_'+info);
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
    // Si ya existe una URL de imagen, mostrar la vista previa al cargar la página
    window.onload = function() {
        
        var imageUrl = false;

        if (imageUrl) {
            var output = document.getElementById('image-preview');
            output.src = imageUrl;
            output.style.display = 'block';
        }

        var imageUrl2 = false;

        if (imageUrl2) {
            var output = document.getElementById('image-preview2');
            output.src = imageUrl2;
            output.style.display = 'block';
        }
        var imageUrl3 = false;
        
        if (imageUrl3) {
            var output = document.getElementById('image-preview3');
            output.src = imageUrl3;
            output.style.display = 'block';
        }
    };

</script>
@endsection