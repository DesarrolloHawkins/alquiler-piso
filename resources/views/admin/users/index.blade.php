@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-colum mb-3">
        <h2 class="mb-0 me-3 encabezado_top">{{ __('Empleados') }}</h2>
        <a href="{{ route('admin.empleados.create') }}" class="btn bg-color-sexto text-uppercase">
            <i class="fa-solid fa-plus me-2"></i> Crear Usuario
        </a>
    </div>
    <hr>
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Filtros de búsqueda -->
            <h6 class="text-uppercase"><i class="fa-solid fa-filter me-1"></i> Filtros</h6>
            <form action="{{ route('admin.empleados.index') }}" method="GET" class="mb-3" id="search_form">
                <div class="row">
                    <!-- Campo de búsqueda por nombre o email -->
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="search" name="search" placeholder="Buscar por nombre o email" value="{{ request()->get('search') }}">
                    </div>
                    <!-- Filtro por rol -->
                    <div class="col-md-3">
                        <select name="role" id="role" class="form-select">
                            <option value="">Seleccionar Rol</option>
                            <option value="ADMIN" {{ request()->get('role') == 'ADMIN' ? 'selected' : '' }}>ADMIN</option>
                            <option value="USER" {{ request()->get('role') == 'USER' ? 'selected' : '' }}>USER</option>
                            <option value="LIMPIEZA" {{ request()->get('role') == 'LIMPIEZA' ? 'selected' : '' }}>LIMPIEZA</option>
                            <option value="MANTENIMIENTO" {{ request()->get('role') == 'MANTENIMIENTO' ? 'selected' : '' }}>MANTENIMIENTO</option>
                        </select>
                    </div>
                    <!-- Filtro por estado activo/inactivo -->
                    <div class="col-md-3">
                        <select name="active" id="active" class="form-select">
                            <option value="">Estado</option>
                            <option value="1" {{ request()->get('active') == '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ request()->get('active') == '0' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn bg-color-primero w-100">Filtrar</button>
                    </div>
                </div>
            </form>

            <!-- Tabla de usuarios -->
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th scope="col">Nombre</th>
                        <th scope="col">Email</th>
                        <th scope="col">Rol</th>
                        <th scope="col">Activo</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role }}</td>
                            <td>
                              @if($user->inactive) 
                                  <span class="badge bg-danger">No</span>
                              @else 
                                  <span class="badge bg-success">Sí</span>
                              @endif
                          </td>
                            <td>
                                <a href="{{ route('admin.empleados.edit', $user->id) }}" class="btn btn-warning">Editar</a>
                                <form action="{{ route('admin.empleados.destroy', $user->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Paginación -->
            {!! $users->appends(request()->except('page'))->links('pagination::bootstrap-5') !!}
        </div>
    </div>
</div>
@endsection
