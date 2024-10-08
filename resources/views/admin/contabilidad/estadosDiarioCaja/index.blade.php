@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <h2 class="mb-3">{{ __('Estados del Diario') }}</h2>
    <a href="{{ route('admin.estadosDiario.create') }}" class="btn bg-color-quinto">Crear Estado</a>
    <hr class="mb-5">

    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <!-- Formulario de búsqueda -->
    <form action="{{ route('admin.estadosDiario.index') }}" method="GET" class="mb-4">
        <div class="input-group mb-5">
            <input type="text" class="form-control" name="search" placeholder="Buscar estado" value="{{ request()->get('search') }}">
            <button type="submit" class="btn bg-color-primero">Buscar</button>
        </div>
    </form>

    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th style="width: 200px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($estados as $estado)
                <tr>
                    <th>{{ $estado->id }}</th>
                    <td>{{ $estado->nombre }}</td>
                    <td>
                        <a href="{{ route('admin.estadosDiario.edit', $estado->id) }}" class="btn btn-secundario">Editar</a>
                        <form action="{{ route('admin.estadosDiario.destroy', $estado->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-danger delete-btn">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Paginación -->
    {{ $estados->appends(request()->input())->links() }}
</div>
@endsection

@include('sweetalert::alert')

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
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
