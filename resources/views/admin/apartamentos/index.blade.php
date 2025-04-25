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
    .min-width-apto {
        min-width: 250px; /* Esto hace que no se aplaste */
    }
    .input-group .form-select,
    .input-group .form-control {
        margin-right: 10px; /* Espacio entre selects */
    }
</style>


<div class="container-fluid">
    <div class="d-flex flex-colum mb-3">
        <h2 class="mb-0 me-3 encabezado_top">{{ __('Nuestros Apartamentos') }}</h2>
        <a href="{{ route('apartamentos.admin.create') }}" class="btn bg-color-sexto text-uppercase">
            <i class="fa-solid fa-plus me-2"></i>
            Crear Apartamento
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
            <form action="{{ route('apartamentos.admin.index') }}" method="GET" class="mb-3" id="search_form">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <div>
                        <label class="form-label mb-0 me-2" for="apartamento_id">Apartamento</label>
                        <select class="form-select min-width-apto" name="apartamento_id" id="apartamento_id">
                            <option value="">Todos</option>
                            @foreach($apartamentoslist as $apartamento)
                                <option value="{{ $apartamento->id }}"
                                    {{ request()->get('apartamento_id') == $apartamento->id ? 'selected' : '' }}>
                                    {{ $apartamento->titulo }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label mb-0 me-2" for="edificio_id">Edificio</label>
                        <select name="edificio_id" id="edificio_id" class="form-select min-width-apto">
                            <option value="">Seleccione un edificio</option>
                            @foreach ($edificios as $edificio)
                                <option value="{{ $edificio->id }}" {{ request()->get('edificio_id') == $edificio->id ? 'selected' : '' }}>
                                    {{ $edificio->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="button" onclick="limpiar()" class="btn bg-color-segundo">Eliminar filtros</button>
                    <button type="submit" class="btn bg-color-primero">Buscar</button>
                </div>
            </form>



            <table class="table table-striped table-hover">
                <thead>
                    <tr class="bg-color-primero-table">
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
                            <a href="{{ route('apartamentos.admin.index', ['sort' => 'nombre', 'order' => request('order', 'asc') == 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}"
                               class="{{ request('sort') == 'nombre' ? 'active-sort' : 'inactive-sort' }}">
                                Nombre
                                @if (request('sort') == 'nombre')
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
                        <th scope="col" class="inactive-sort">ID Booking</th>
                        <th scope="col" class="inactive-sort">ID Airbnb</th>
                        <th scope="col" class="inactive-sort">ID Web</th>
                        <th scope="col" class="inactive-sort" style="width: 200px;">Acción</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($apartamentos as $apartamento)
                        <tr>
                            <td scope="row">{{ $apartamento->id }}</td>
                            <td>{{ $apartamento->titulo }}</td>
                            <td>{{ $apartamento->edificio_id != null ? $apartamento->edificioName->nombre : 'N/A' }}</td>
                            <td>{{ $apartamento->id_booking }}</td>
                            <td>{{ $apartamento->id_airbnb }}</td>
                            <td>{{ $apartamento->id_web }}</td>
                            <td style="width:30%;">
                                {{-- <a href="{{ route('apartamentos.admin.show', $apartamento->id) }}" class="btn bg-color-cuarto text-black">Ver</a> --}}
                                <a href="{{ route('apartamentos.admin.edit', $apartamento->id) }}" class="btn bg-color-quinto">Editar</a>
                                <form action="{{ route('apartamentos.admin.destroy', $apartamento->id) }}" method="POST" style="display: inline;" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger delete-btn">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <!-- Paginación links -->
            {!! $apartamentos->appends(['search' => request()->get('search')])->links('pagination::bootstrap-5') !!}
        </div>
    </div>
</div>
@endsection

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
        document.getElementById("apartamento_id").value = "";
        document.getElementById("edificio_id").value = "";
        document.getElementById("search_form").submit();
    }

</script>
@endsection
