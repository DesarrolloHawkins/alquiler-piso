@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <h2 class="mb-3">{{ __('Nuestros Clientes') }}</h2>
    <hr>
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
                 <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            <!-- Formulario de búsqueda -->
            <form action="{{ route('clientes.index') }}" method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Buscar cliente" value="{{ request()->get('search') }}">
                    <button type="submit" class="btn bg-color-primero">Buscar</button>
                </div>
            </form>

            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Apellidos</th>
                        <th scope="col">Idioma</th>
                        <th scope="col">DNI</th>
                        <th scope="col">Accion</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($clientes as $cliente)
                        <tr>
                            <th scope="row">{{$cliente->id}}</th>
                            <td>{{$cliente->alias}}</td>
                            <td>{{$cliente->apellido1}} {{$cliente->apellido2}}</td>
                            <td>{{$cliente->idiomas}}</td>
                            <td>{{$cliente->tipo_documento_str}}</td>
                            <td><a href="" class="btn btn-primary">Editar</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <!-- Paginación links -->
            {!! $clientes->appends(['search' => request()->get('search')])->links('pagination::bootstrap-5') !!}

        </div>
    </div>
</div>
@endsection
