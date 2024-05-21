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
                                <option value="Masculino" {{ (old('sexo') == 'Masculino' ? 'selected' : '') }}>Masculino</option>
                                <option value="Femenino" {{ (old('sexo') == 'Femenino' ? 'selected' : '') }}>Femenino</option>
                                <!-- <option value="Binario" {{ (old('tipo_documento') == 'Binario' ? 'selected' : '') }}>Binario</option> -->
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
                        <div class="row align-items-center">
                            <div class="col-12" > Numero de Adultos:</div>
                            <div class="col-6">
                                <input type="number" id="numero" value="1" min="1" step="1" class="form-control w-100">
                                <input type="hidden" name="idReserva" id="idReserva" value="{{$id}}">
                            </div>
                            <div class="col-3">
                                <button id="sumar" class="w-100 btn btn-secondary">+</button>
                            </div>
                            <div class="col-3">
                                <button id="restar" class="w-100 btn btn-secondary">-</button>
                            </div>

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
                        {{$textos['Inicio']}}
                    </div>
                    <div class="card-body">
                        @if (session('alerta'))
                            <div class="alert alert-warning">
                                {{ session('alerta') }}
                            </div>
                        @endif
                        @php
                            $nacionalidadComun = $data[0]->nacionalidad;
                        @endphp
                        <div id="formularios">
                            <form action="{{route('dni.store')}}" method="POST" class="row g-3 needs-validation" novalidate enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="id" value="{{$id}}">
                                @for ($i = 0; $i < $reserva->numero_personas; $i++)
                                    <div class="card-body">
                                        @if ($i == 0)
                                            <h3 class="fw-bold bg-color-quinto titulo-dni p-3 text-center">{{$textos['Huesped.Principal']}}</h3>
                                        @else
                                            <h3 class="fw-bold bg-color-quinto titulo-dni p-3 text-center">{{$textos['Acompañante']}} {{$i}}</h3>
                                        @endif
                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <input 
                                                name="nombre_{{$i}}" 
                                                type="text" 
                                                class="form-control" 
                                                id="nombre_{{$i}}" 
                                                placeholder="{{$textos['Nombre']}}" 
                                                value="{{ $i == 0 || isset($data[$i]) ? $data[$i]->nombre : '' }}" 
                                                required>
                                                <label for="nombre_{{$i}}">{{$textos['Nombre']}}</label>
                                                <div class="valid-feedback">
                                                    {{$textos['Correcto']}}
                                                </div>
                                                <div class="invalid-feedback">
                                                    {{$textos['nombre_obli']}}
                                                </div>
                                                @error('nombre_{{$i}}')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div> 
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <input 
                                                name="apellido1_{{$i}}" 
                                                type="text" 
                                                class="form-control" 
                                                id="apellido1_{{$i}}"  
                                                value="{{ $i != 0 && isset($data[$i]) ? $data[$i]->primer_apellido : (isset($data[$i]->apellido1) ? $data[$i]->apellido1 : '') }}" 
                                                placeholder="{{$textos['Primer.Apellido']}}" required>
                                                <label for="apellido1_{{$i}}">{{$textos['Primer.Apellido']}}</label>
                                                <div class="valid-feedback">
                                                    {{$textos['Correcto']}}
                                                </div>
                                                <div class="invalid-feedback">
                                                    {{$textos['apellido_obli']}}
                                                </div>
                                                @error('apellido1_{{$i}}')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div> 
                                        </div>
                                        
                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <input 
                                                name="apellido2_{{$i}}" 
                                                type="text" 
                                                class="form-control" 
                                                id="apellido2_{{$i}}"
                                                value="{{ $i != 0 && isset($data[$i]) ? $data[$i]->segundo_apellido : (isset($data[$i]->apellido2) ? $data[$i]->apellido2 : '') }}"                                                 
                                                placeholder="{{$textos['Segundo.Apellido']}}">
                                                <label for="apellido2_{{$i}}">{{$textos['Segundo.Apellido']}}</label>
                                                <div class="valid-feedback">
                                                    {{$textos['Correcto']}}
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
                                                <input 
                                                name="fecha_nacimiento_{{$i}}" 
                                                type="date" 
                                                class="form-control" 
                                                id="fecha_nacimiento_{{$i}}" 
                                                value="{{ isset($data[$i]) ? $data[$i]->fecha_nacimiento : '' }}"
                                                placeholder="{{$textos['Fecha.Nacimiento']}}" 
                                                aria-label="Fecha de Nacimiento" 
                                                required>
                                                <label for="fecha_nacimiento_{{$i}}">{{$textos['Fecha.Nacimiento']}}</label>
                                                <div class="valid-feedback">
                                                    {{$textos['Correcto']}}
                                                </div>
                                                <div class="invalid-feedback">
                                                    {{$textos['fecha_naci_obli']}}
                                                </div>
                                                @error('fecha_nacimiento_{{$i}}')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div> 
                                        </div>

                                        <div class="col-12">
                                            <div class="form-floating mb-3 ">
                                                <select 
                                                name="nacionalidad_{{$i}}" 
                                                id="nacionalidad_{{$i}}" 
                                                class="form-select js-example-basic-single{{$i}} nacionalidad" 
                                                aria-label="Pais" 
                                                placeholder="{{$textos['Pais']}}">
                                                    @foreach ($paises as $pais)
                                                        <option value="{{$pais}}"
                                                            {{
                                                                (isset($nacionalidadComun) && $nacionalidadComun == $pais) ||
                                                                (old('nacionalidad_'.$i) == $pais) ||
                                                                (empty(old('nacionalidad_'.$i)) && !isset($nacionalidadComun) && $pais == 'España')
                                                                ? 'selected' : ''
                                                            }}
                                                        >
                                                            {{$pais}}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <label for="nacionalidad_{{$i}}">{{$textos['Pais']}}</label>

                                                <div class="valid-feedback">
                                                    {{$textos['Correcto']}}
                                                </div>
                                                <div class="invalid-feedback">
                                                    {{$textos['pais_obli']}}
                                                </div>
                                                @error('nacionalidad_{{$i}}')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div> 
                                        </div>

                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <select data-info="{{$i}}" name="tipo_documento_{{$i}}" id="tipo_documento_{{$i}}" class="form-select tiposDocumentos" aria-label="DNI o Pasaporte" placeholder="{{$textos['Tipo.Documento']}}">
                                                    <option value="{{null}}" selected>Seleccion el tipo</option>
                                                    <option 
                                                    value="1"
                                                    {{ (isset($data[$i]) && $data[$i]->tipo_documento == '1') || old('tipo_documento_'.$i) == '1' ? 'selected' : '' }}
                                                     >{{$textos['Dni']}}</option>
                                                    <option 
                                                    value="2"
                                                    {{ (isset($data[$i]) && $data[$i]->tipo_documento == '2') || old('tipo_documento_'.$i) == '2' ? 'selected' : '' }}
                                                     >{{$textos['Pasaporte']}}</option>
                                                </select>
                                                <label for="tipo_documento_{{$i}}">{{$textos['Tipo.Documento']}}</label>

                                                <div class="valid-feedback">
                                                    {{$textos['Correcto']}}
                                                </div>
                                                <div class="invalid-feedback">
                                                    {{$textos['tipo_obli']}}
                                                </div>
                                                @error('tipo_documento_{{$i}}')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div> 
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <input 
                                                name="num_identificacion_{{$i}}" 
                                                type="text" 
                                                class="form-control" 
                                                id="num_identificacion_{{$i}}"
                                                value="{{ isset($data[$i]) ? ($i == 0 ? $data[$i]->num_identificacion : $data[$i]->numero_identificacion) : '' }}"
                                                placeholder="{{$textos['Numero.Identificacion']}}" 
                                                aria-label="Numero Identificación" 
                                                required>
                                                <label for="num_identificacion_{{$i}}">{{$textos['Numero.Identificacion']}}</label>
                                                <div class="valid-feedback">
                                                    {{$textos['Correcto']}}
                                                </div>
                                                <div class="invalid-feedback">
                                                    {{$textos['numero_obli']}}
                                                </div>
                                                @error('num_identificacion_{{$i}}')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div> 
                                        </div>

                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <input 
                                                name="fecha_expedicion_doc_{{$i}}" 
                                                type="date" 
                                                class="form-control" 
                                                id="fecha_expedicion_doc_{{$i}}" 
                                                value="{{ $i != 0 && isset($data[$i]) ? $data[$i]->fecha_expedicion : (isset($data[$i]->fecha_expedicion_doc) ? $data[$i]->fecha_expedicion_doc : '') }}"
                                                placeholder="{{$textos['Fecha.Expedicion']}}" 
                                                aria-label="Fecha de Expedición" 
                                                required>
                                                <label for="fecha_expedicion_doc_{{$i}}">{{$textos['Fecha.Expedicion']}}</label>
                                                <div class="valid-feedback">
                                                    {{$textos['Correcto']}}
                                                </div>
                                                <div class="invalid-feedback">
                                                    {{$textos['fecha_obli']}}
                                                </div>
                                                @error('fecha_expedicion_doc_{{$i}}')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div> 
                                        </div>

                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <select name="sexo_{{$i}}" id="sexo_{{$i}}" class="form-select" aria-label="Sexo" placeholder="{{$textos['Sexo']}}" required>
                                                    <option 
                                                    value="Masculino" 
                                                    {{ (isset($data[$i]) && $data[$i]->sexo == 'Masculino') || old('sexo_'.$i) == 'Masculino' ? 'selected' : '' }}
                                                    >{{$textos['Masculino']}}
                                                    </option>
                                                    <option 
                                                    value="Femenino" 
                                                    {{ (isset($data[$i]) && $data[$i]->sexo == 'Femenino') || old('sexo_'.$i) == 'Femenino' ? 'selected' : '' }}
                                                    >
                                                    {{$textos['Femenino']}}
                                                    </option>
                                                </select>
                                                <label for="sexo_{{$i}}">{{$textos['Sexo']}}</label>

                                                <div class="valid-feedback">
                                                    {{$textos['Correcto']}}
                                                </div>
                                                <div class="invalid-feedback">
                                                    {{$textos['sexo_obli']}}
                                                    
                                                </div>
                                                @error('sexo_{{$i}}')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div> 
                                        </div>

                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <input 
                                                name="email_{{$i}}" 
                                                type="text" 
                                                class="form-control" 
                                                id="email_{{$i}}"
                                                value="{{ isset($data[$i]) ? $data[$i]->email : '' }}"
                                                placeholder="{{$textos['Correo.Electronico']}}" 
                                                aria-label="Correo Electronico" 
                                                required>
                                                <label for="email_{{$i}}">{{$textos['Correo.Electronico']}}</label>
                                                <div class="valid-feedback">
                                                    {{$textos['Correcto']}}
                                                </div>
                                                <div class="invalid-feedback">
                                                    {{$textos['email_obli']}}
                                                </div>
                                                @error('email_{{$i}}')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div> 
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <div id="dniUploaed_{{$i}}" style="display: none">
                                                    <h4>{{$textos['Imagen.Frontal']}}</h4>
                                                    <div class="files mt-3">
                                                        <input type="file" accept="image/*" class="file-input" capture="camera" name="fontal_{{$i}}" id="fontal_{{$i}}" onchange="previewImage({{$i}},event)">
                                                        <button type="button" class="btn btn-secundario fs-5 w-100" onclick="document.getElementById('fontal_{{$i}}').click()"><i class="fa-solid fa-camera me-2"></i> {{$textos['Frontal']}}</button>
                                                        <img data-info="{{$i}}" id="image-preview_frontal_{{$i}}" style="max-width: 100%; max-height: auto; margin-top: 10px;"/>
                                                        <div class="valid-feedback">
                                                            {{$textos['Correcto']}}
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            {{$textos['dni_front_obli']}}
                                                        </div>
                                                        @error('fontal_{{$i}}')
                                                            <div class="alert alert-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <h4>{{$textos['Imagen.Trasera']}}</h4>

                                                    <div class="files mt-3">
                                                        <input type="file" accept="image/*" class="file-input" capture="camera" name="trasera_{{$i}}" id="trasera_{{$i}}" onchange="previewImage2({{$i}},event)">
                                                        <button type="button" class="btn btn-secundario fs-5 w-100" onclick="document.getElementById('trasera_{{$i}}').click()"><i class="fa-solid fa-camera me-2"></i> {{$textos['Trasera']}}</button>
                                                        <img data-info="{{$i}}" id="image-preview_trasera_{{$i}}" style="max-width: 100%; max-height: auto; margin-top: 10px;"/>
                                                        <div class="valid-feedback">
                                                            {{$textos['Correcto']}}
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            {{$textos['dni_front_obli']}}
                                                        </div>
                                                        @error('trasera_{{$i}}')
                                                            <div class="alert alert-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div id="pasaporteUpload_{{$i}}" style="display: none">
                                                    <h4>{{$textos['Imagen.Pasaporte']}}</h4>
                                                    <div class="files mt-3">
                                                        <input type="file" accept="image/*" class="file-input" capture="camera" name="pasaporte_{{$i}}" id="frontal_{{$i}}" onchange="previewImage3({{$i}},event)">
                                                        <button type="button" class="btn btn-secundario fs-5 w-100" onclick="document.getElementById('frontal_{{$i}}').click()"><i class="fa-solid fa-camera me-2"></i> {{$textos['Frontal']}}</button>
                                                        <img data-info="{{$i}}" id="image-preview_pasaporte_{{$i}}" style="max-width: 65%; max-height: auto; margin-top: 10px;"/>
                                                        <div class="valid-feedback">
                                                            {{$textos['Correcto']}}
                                                        </div>
                                                        <div class="invalid-feedback">
                                                            {{$textos['pasaporte_obli']}}
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
                                    <button class="btn btn-terminar w-100">{{$textos['Enviar']}}</button>
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
    $(document).ready(function() {
        var cantidadPersonas = @json($reserva->numero_personas);
        for (let i = 0; i < cantidadPersonas; i++) {
            $('.js-example-basic-single'+i).select2();
        }

        $('#sumar').on('click', function(){
            let valor = parseInt($('#numero').val(), 10); // Convierte el valor a un número entero
            valor += 1;
            if (valor == 0) {
                valor = 1;
            }
            $('#numero').val(valor);
            console.log($('#numero').val());
        });
        $('#restar').on('click', function(){
            let valor = parseInt($('#numero').val(), 10); // Convierte el valor a un número entero
            valor -= 1;
            if (valor == 0) {
                valor = 1;
            }
            $('#numero').val(valor);
            console.log($('#numero').val());
        });

        $('#enviar').click(function() {
            var cantidad = $('#numero').val();
            var id = $('#idReserva').val();
            console.log(id);
            $.ajax({
                url: '/guardar-numero-personas',
                method: 'POST',
                data: {
                    cantidad: cantidad,
                    id: id,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    window.location.reload();
                }
            });
        });

        var tipoDocumento = document.querySelectorAll('.tiposDocumentos');
        tipoDocumento.forEach(function(tipo){
            tipo.addEventListener('change', function(e) {
                var valor = this.value;
                var info = this.getAttribute('data-info');
                if (valor === '1') {
                    document.getElementById('dniUploaed_'+info).style.display = 'block';
                    document.getElementById('fontal_'+info).required = true;
                    document.getElementById('trasera_'+info).required = true;
                    document.getElementById('pasaporteUpload_'+info).style.display = 'none';
                    document.getElementById('frontal_'+info).required = false;
                } else if (valor === '2') {
                    document.getElementById('dniUploaed_'+info).style.display = 'none';
                    document.getElementById('pasaporteUpload_'+info).style.display = 'block';
                    document.getElementById('fontal_'+info).required = false;
                    document.getElementById('trasera_'+info).required = false;
                    document.getElementById('frontal_'+info).required = true;
                }
            });
        });
    });

    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();

    function previewImage(info, event) {
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

    window.onload = function() {
        var reserva = @json($reserva);
        var data = @json($data);

        for (let i = 0; i < data.length; i++) {
            if (data[i].tipo_documento == 1) {
                var divPhotos = document.getElementById('dniUploaed_' + i);
                divPhotos.style.display = 'block';

                if (data[i].frontal && data[i].frontal.url) {
                    var output = document.getElementById('image-preview_frontal_' + i);
                    output.src = '/' + data[i].frontal.url;
                    output.style.display = 'block';
                }

                if (data[i].trasera && data[i].trasera.url) {
                    var output = document.getElementById('image-preview_trasera_' + i);
                    output.src = '/' + data[i].trasera.url;
                    output.style.display = 'block';
                }
            } else {
                var divPhotos = document.getElementById('pasaporteUpload_' + i);
                divPhotos.style.display = 'block';

                if (data[i].pasaporte && data[i].pasaporte.url) {
                    var output = document.getElementById('image-preview_pasaporte_' + i);
                    output.src = '/' + data[i].pasaporte.url;
                    output.style.display = 'block';
                }
            }
        }
    };

    $(document).ready(function() {
        $('.nacionalidad').select2(); // Asegura que Select2 esté inicializado

        function handleNationalityChange() {
            var selectedValue = $('.nacionalidad').val();
            var normalizedValue = (selectedValue === "España") ? selectedValue.toUpperCase() : normalizeText(selectedValue);

            var opciones = @json($optionesTipo);
            var paisesDni = @json($paisesDni);
            var countryInfo = paisesDni[normalizedValue];

            if (countryInfo) {
                let indices;
                let nuevasOpciones = [];

                if (normalizedValue === "ESPAÑA") {
                    indices = [0, 4, 5];
                } else if (countryInfo.isEuropean) {
                    indices = [0, 1, 2, 3];
                } else {
                    indices = [0, 1, 2];
                }

                indices.forEach(i => {
                    nuevasOpciones.push(opciones[i]);
                });

                $('.tiposDocumentos').empty().each(function() {
                    var select = $(this);
                    nuevasOpciones.forEach(opcion => {
                        select.append($('<option></option>').val(opcion.codigo).text(opcion.descripcion));
                    });
                });
            } else {
                console.log("No se encontró información para:", normalizedValue);
            }
        }

        $('.nacionalidad').on('change', handleNationalityChange);

        handleNationalityChange();

        function normalizeText(input) {
            return input
                .normalize("NFD")
                .replace(/(?<!n[\u0300-\u036f])[\u0300-\u036f]/gi, "")
                .toUpperCase();
        }
    });
</script>
@endsection
