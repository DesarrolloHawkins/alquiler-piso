@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Nuestros Clientes') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form action="" method="POST">
                        <div class="mb-3">
                          <label for="nombre" class="form-label">Nombre</label>
                          <input type="text" class="form-control" id="nombre">
                        </div>
                        <div class="mb-3">
                          <label for="apellido1" class="form-label">Primer Apellido</label>
                          <input type="text" class="form-control" id="apellido1">
                        </div>

                        <div class="mb-3">
                            <label for="apellido2" class="form-label">Segundo Apellido</label>
                            <input type="text" class="form-control" id="apellido2">
                        </div>
                        <div class="mb-3">
                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" id="fecha_nacimiento">
                        </div>
                        <div class="mb-3">
                            <label for="sexo_str" class="form-label">Sexo</label>
                            <input type="text" class="form-control" id="sexo_str">
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Telefono</label>
                            <input type="text" class="form-control" id="telefono">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email">
                        </div>
                        <div class="mb-3">
                            <label for="idiomas" class="form-label">Idioma</label>
                            <input type="text" class="form-control" id="idiomas">
                        </div>
                        <div class="mb-3">
                            <label for="nacionalidad" class="form-label">Nacionalidad</label>
                            <input type="text" class="form-control" id="nacionalidad">
                        </div>
                        <div class="mb-3">
                            <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                            <input type="text" class="form-control" id="tipo_documento">
                        </div>
                        <div class="mb-3">
                            <label for="num_identificacion" class="form-label">Numero de Identificacion</label>
                            <input type="text" class="form-control" id="num_identificacion">
                        </div>
                        <div class="mb-3">
                            <label for="fecha_expedicion_doc" class="form-label">Fecha de Expedicion</label>
                            <input type="date" class="form-control" id="fecha_expedicion_doc">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Guardar</button>
                      </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
