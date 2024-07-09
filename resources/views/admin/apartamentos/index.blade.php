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
    <h2 class="mb-3">{{ __('Nuestros Apartamentos') }}</h2>
    <a href="{{route('apartamentos.admin.create')}}" class="btn bg-color-quinto">Crear apartamento</a>
    <hr class="mb-5">
    <div class="row justify-content-center">

        <div class="col-md-12">
            @if (session('status'))
                 <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            <!-- Formulario de búsqueda -->
            <form action="{{ route('clientes.index') }}" method="GET" class="mb-4">
                <div class="input-group mb-5">
                    <input type="text" class="form-control" name="search" placeholder="Buscar apartamento" value="{{ request()->get('search') }}">
                    <button type="submit" class="btn bg-color-primero">Buscar</button>
                </div>
            </form>

            <table class="table table-striped table-hover">
                <thead>

                    <tr>
                        
                        <th scope="col">
                            <a href="{{ route('apartamentos.admin.index', ['sort' => 'id', 'order' => request('order', 'asc') == 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}"
                               class="{{ request('sort') == 'id' ? 'active-sort' : 'inactive-sort' }}">
                                ID
                                @if (request('sort') == 'id')
                                    <i class="fa {{ request('order', 'asc') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col">
                            <a href="{{ route('apartamentos.admin.index', ['sort' => 'edificio', 'order' => request('order', 'asc') == 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}"
                               class="{{ request('sort') == 'edificio' ? 'active-sort' : 'inactive-sort' }}">
                                Edificio
                                @if (request('sort') == 'edificio')
                                    <i class="fa {{ request('order', 'asc') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col">
                            <a href="{{ route('apartamentos.admin.index', ['sort' => 'nombre', 'order' => request('order', 'asc') == 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}"
                               class="{{ request('sort') == 'nombre' ? 'active-sort' : 'inactive-sort' }}">
                                Nombre
                                @if (request('sort') == 'nombre')
                                    <i class="fa {{ request('order', 'asc') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="inactive-sort">Id Booking</th>
                        <th scope="col" class="inactive-sort">Id Airbnb</th>
                        <th scope="col" class="inactive-sort">Id Web</th>
                        <th scope="col" class="inactive-sort" style="width: 200px;">Acción</th>
                    </tr>
                </thead>


                <tbody>
                    @foreach ($apartamentos as $apartamento)
                        <tr>
                            <th scope="row">{{$apartamento->id}}</th>
                            <th scope="row">@if($apartamento->edificio == 1) {{'Edificio Hawkins'}} @else {{'Edificio Costa'}} @endif</th>
                            <td>{{$apartamento->titulo}}</td>
                            <td>{{$apartamento->id_booking}}</td>
                            <td>{{$apartamento->nombre}}</td>
                            <td>{{$apartamento->id_web}}</td>
                            <td style="width:auto;">
                                <a href="{{route('apartamentos.admin.edit', $apartamento->id)}}" class="btn btn-secundario">Editar</a>
                                {{-- <form action="{{ route('clientes.destroy', $apartamento->id) }}" method="POST" style="display: inline;" class="delete-form">
                                    @csrf
                                    <button type="button" class="btn btn-danger delete-btn">Eliminar</button>
                                </form> --}}
                                {{-- <a href="{{route('clientes.destroy', $cliente->id)}}" class="btn btn-danger">Eliminar</a> --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <!-- Paginación links -->
            {{-- {!! $apartamentos->appends(['search' => request()->get('search')])->links('pagination::bootstrap-5') !!} --}}

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
</script>
@endsection

