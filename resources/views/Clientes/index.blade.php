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

                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Apellidos</th>
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
                                    <td>{{$cliente->tipo_documento_str}}</td>
                                    <td><a href="" class="btn btn-primary">Editar</a></td>
                                </tr>                 
                            @endforeach
                        </tbody>
                    </table>
                    <!-- Paginación links -->
                    {{ $clientes->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
