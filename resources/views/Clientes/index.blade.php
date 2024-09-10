@extends('layouts.appAdmin')

@section('content')
<style>
    .inactive-sort {
        color: #ffffff;
        text-decoration: none;
    }
    .active-sort {
        color: #ffa3fa;
        font-weight: bold;
        text-decoration: none;
    }
</style>
<div class="container-fluid">
    <div class="d-flex flex-colum mb-3">
        <h2 class="mb-0 me-3 encabezado_top">{{ __('Nuestros Clientes') }}</h2>
        <a href="{{route('clientes.create')}}" class="btn bg-color-sexto text-uppercase">
            <i class="fa-solid fa-plus me-2"></i>
            Crear cliente
        </a>

    </div>
    <hr class="mb-3">
    <div class="row justify-content-center">

        <div class="col-md-12">
            @if (session('status'))
                 <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            <h6 class="text-uppercase"><i class="fa-solid fa-filter me-1"></i> Filtros</h6>
            <!-- Formulario de búsqueda -->
            <form action="{{ route('clientes.index') }}" method="GET" class="mb-3" id="search_form">
                <div class="input-group">
                    <input type="text" class="form-control" id="search" name="search" placeholder="Buscar cliente" value="{{ request()->get('search') }}">
                    <button type="button" onclick="limpiar()" class="btn bg-color-segundo">Eliminar filtros</button>
                    <button type="submit" class="btn bg-color-primero">Buscar</button>
                </div>
            </form>

            <table class="table table-striped table-hover">
                <thead >
                    <tr class="bg-color-primero-table">
                        {{-- <th scope="col">
                            <a href="{{ route('clientes.index', ['sort' => 'id', 'order' => request('order', 'asc') == 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}"
                               class="{{ request('sort') == 'id' ? 'active-sort' : 'inactive-sort' }}">
                                ID
                                @if (request('sort') == 'id')
                                    <i class="fa {{ request('order', 'asc') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                @endif
                            </a>
                        </th> --}}
                        <th scope="col">
                            <a href="{{ route('clientes.index', ['sort' => 'nombre', 'order' => request('order', 'asc') == 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}"
                               class="{{ request('sort') == 'nombre' ? 'active-sort' : 'inactive-sort' }}">
                                Nombre
                                @if (request('sort') == 'nombre')
                                    <i class="fa {{ request('order', 'asc') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="inactive-sort">Apellidos</th>
                        <th scope="col">
                            <a href="{{ route('clientes.index', ['sort' => 'idioma', 'order' => request('order', 'asc') == 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}"
                               class="{{ request('sort') == 'idioma' ? 'active-sort' : 'inactive-sort' }}">
                                Idioma
                                @if (request('sort') == 'idioma')
                                    <i class="fa {{ request('order', 'asc') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                @endif
                            </a>
                        </th>
                        {{-- <th scope="col" class="inactive-sort">DNI</th> --}}
                        <th scope="col" class="inactive-sort" style="width: 200px;">Acción</th>
                    </tr>
                </thead>


                <tbody>
                    @foreach ($clientes as $cliente)
                        <tr>
                            {{-- <th scope="row">{{$cliente->id}}</th> --}}
                            <td scope="row" style="width: 40%">{{$cliente->alias != null ? $cliente->alias : $cliente->nombre}}</td>
                            <td style="width: 20%">{{$cliente->apellido1}} {{$cliente->apellido2}}</td>
                            <td style="width: 10%">{{$cliente->idioma}}</td>
                            {{-- <td>{{$cliente->num_identificacion}}</td> --}}
                            <td style="width:30%;">
                                <a href="{{route('clientes.show', $cliente->id)}}" class="btn bg-color-cuarto text-black">Ver</a>
                                <a href="{{route('clientes.edit', $cliente->id)}}" class="btn bg-color-quinto">Editar</a>
                                <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" style="display: inline;" class="delete-form">
                                    @csrf
                                    <button type="button" class="btn btn-danger delete-btn">Eliminar</button>
                                </form>
                                {{-- <a href="{{route('clientes.destroy', $cliente->id)}}" class="btn btn-danger">Eliminar</a> --}}
                            </td>
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

{{-- @include('sweetalert::alert') --}}

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Verificar si SweetAlert2 está definido
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 is not loaded');
            return;
        }

        // Botones de eliminar
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                const form = this.closest('form');
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
                        form.submit();
                    }
                });
            });
        });
        
    });
    function limpiar() {
            document.getElementById("search").value = "";
            const form = document.getElementById("search_form");
            form.submit();

        }
</script>
@endsection

