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
    <h2 class="mb-3">{{ __('Nuestros Gastos') }}</h2>
    <a href="{{route('admin.gastos.create')}}" class="btn bg-color-quinto">Crear gasto</a>
    <hr class="mb-5">
    <div class="row justify-content-center">

        <div class="col-md-12">
            @if (session('status'))
                 <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            <div class="row align-items-start">
                <div class="col-md-2">
                    <div class="mb-3">
                        <form action="{{ route('admin.gastos.index') }}" method="GET">
                            <div class="form-group">
                                 <!-- Otros parámetros como campos ocultos -->
                                <input type="hidden" name="order_by" value="{{ request()->get('order_by') }}">
                                <input type="hidden" name="direction" value="{{ request()->get('direction') }}">
                                <input type="hidden" name="search" value="{{ request()->get('search') }}">
                                <label for="perPage" class="form-label">Registros por página:</label>
                                <select name="perPage" id="perPage" class="form-control" onchange="this.form.submit()">
                                    {{-- <option value="5" {{ request()->get('perPage') == 5 ? 'selected' : '' }}>5</option> --}}
                                    <option value="10" {{ request()->get('perPage') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="20" {{ request()->get('perPage') == 20 ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ request()->get('perPage') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request()->get('perPage') == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-10">
                    <div class="mb-3">
                        <form action="{{ route('admin.gastos.index') }}" method="GET" class="mb-4">
                            <input type="hidden" name="order_by" value="{{ request()->get('order_by', 'fecha_entrada') }}">
                            <input type="hidden" name="direction" value="{{ request()->get('direction', 'asc') }}">
                            <input type="hidden" name="perPage" value="{{ request()->get('perPage') }}">
                            <div class="input-group mb-5 justify-content-around">
                                <div class="col-md-3 px-3">
                                    <label for="search" class="form-label">Busqueda</label>
                                    <input type="text" class="form-control" name="search" placeholder="Buscar..." value="{{ request()->get('search') }}">
                                </div>   
                                <div class="col-md-2 px-3">
                                    <label for="estado_id" class="form-label">Estados</label>
                                    <select class="form-control" name="estado_id">
                                        <option value="">Todos los estados</option>
                                        @foreach ($estados as $estado)
                                            <option value="{{ $estado->id }}" {{ request('estado_id') == $estado->id ? 'selected' : '' }}>{{ $estado->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 px-3">
                                    <label for="category" class="form-label">Categoría</label>
                                    <select class="form-control" name="category">
                                        <option value="">Todas las categorías</option>
                                        @foreach ($categorias as $categoria)
                                            <option value="{{ $categoria->id }}" {{ request('category') == $categoria->id ? 'selected' : '' }}>{{ $categoria->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                                             
                                <div class="col-md-2 px-3">
                                    <label for="search" class="form-label">Mes</label>
                                    <select class="form-control" name="month">
                                        <option value="">Todos los meses</option>
                                        @php
                                        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                                        @endphp
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}" @if (request('month') == $i) selected @endif>{{ $meses[$i - 1] }}</option>
                                        @endfor
                                    </select>
                                </div>                                  
                                <div class="col-md-2 align-items-end d-flex justify-content-center">
                                    <button type="submit" class="btn btn-terminar">Buscar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th scope="col">
                            <a href="{{ route('admin.gastos.index', ['sort' => 'id', 'order' => request('order', 'asc') == 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}"
                               class="{{ request('sort') == 'id' ? 'active-sort' : 'inactive-sort' }}">
                                ID
                                @if (request('sort') == 'id')
                                    <i class="fa {{ request('order', 'asc') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col">
                            <a href="{{ route('admin.gastos.index', ['sort' => 'estado_id', 'order' => request('order', 'asc') == 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}"
                               class="{{ request('sort') == 'estado_id' ? 'active-sort' : 'inactive-sort' }}">
                                Estado
                                @if (request('sort') == 'estado_id')
                                    <i class="fa {{ request('order', 'asc') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col">
                            <a href="{{ route('admin.gastos.index', ['sort' => 'categoria_id', 'order' => request('order', 'asc') == 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}"
                               class="{{ request('sort') == 'categoria_id' ? 'active-sort' : 'inactive-sort' }}">
                                Categoria
                                @if (request('sort') == 'categoria_id')
                                    <i class="fa {{ request('order', 'asc') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col">
                            <a href="{{ route('admin.gastos.index', ['sort' => 'nombre', 'order' => request('order', 'asc') == 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}"
                               class="{{ request('sort') == 'nombre' ? 'active-sort' : 'inactive-sort' }}">
                                Nombre
                                @if (request('sort') == 'nombre')
                                    <i class="fa {{ request('order', 'asc') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                @endif
                            </a>
                        </th>
                        
                        <th scope="col">
                            <a href="{{ route('admin.gastos.index', ['sort' => 'date', 'order' => request('order', 'asc') == 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}"
                               class="{{ request('sort') == 'date' ? 'active-sort' : 'inactive-sort' }}">
                                Fecha
                                @if (request('sort') == 'date')
                                    <i class="fa {{ request('order', 'asc') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col">
                            <a href="{{ route('admin.gastos.index', ['sort' => 'quantity', 'order' => request('order', 'asc') == 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}"
                               class="{{ request('sort') == 'quantity' ? 'active-sort' : 'inactive-sort' }}">
                                Importe
                                @if (request('sort') == 'quantity')
                                    <i class="fa {{ request('order', 'asc') == 'asc' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="inactive-sort" style="width: 200px;">Acción</th>
                    </tr>
                </thead>


                <tbody>
                    @foreach ($gastos as $gasto)
                        <tr>
                            <th scope="row">{{$gasto->id}}</th>
                            <td>
                                @if ($gasto->estado_id)
                                    @if ($gasto->estado_id == 1)
                                        <span class="badge bg-warning text-dark fs-6">{{$gasto->estado->nombre}}</span>
                                    @elseif ($gasto->estado_id == 2)
                                        <span class="badge bg-primary fs-6">{{$gasto->estado->nombre}}</span>
                                    @elseif ($gasto->estado_id == 3)
                                        <span class="badge bg-success fs-6">{{$gasto->estado->nombre}}</span>
                                    @else
                                        <span class="badge bg-danger fs-6">{{$gasto->estado->nombre}}</span>
                                    @endif
                                    
                                @else
                                    <strong>{{'Sin Estado'}}</strong>
                                @endif
                            </td>
                            <td>
                                @if ($gasto->categoria_id != null)
                                    {{$gasto->categoria->nombre}}
                                @else
                                    {{'Sin Categoria'}}
                                @endif
                            </td>
                            <td>{{$gasto->title}}</td>
                            <td>{{ !empty($gasto->date) ? \Carbon\Carbon::parse($gasto->date)->format('d-m-Y') : 'Sin fecha establecida' }}</td>
                            <td>{{$gasto->quantity}} €</td>
                            <td style="width:auto;">
                                <a href="{{route('admin.gastos.edit', $gasto->id)}}" class="btn btn-secundario">Editar</a>
                                <form action="{{ route('admin.gastos.destroy', $gasto->id) }}" method="POST" style="display: inline;" class="delete-form">
                                    @csrf
                                    <button type="button" class="btn btn-danger delete-btn">Eliminar</button>
                                </form>
                                {{-- <a href="{{route('clientes.destroy', $cliente->id)}}" class="btn btn-danger">Eliminar</a> --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fs-5">
                        <th colspan="4" class="text-end">TOTAL: </th> <!-- Ajusta el índice de colspan según el número de columnas antes de 'quantity' -->
                        <th>{{ $totalQuantity }} €</th>
                        <th colspan="2"></th> <!-- Ajusta este valor según el número de columnas después de 'quantity' -->
                    </tr>
                </tfoot>
            </table>
            <!-- Paginación links -->
            {{-- {!! $apartamentos->appends(['search' => request()->get('search')])->links('pagination::bootstrap-5') !!} --}}
            {{ $gastos->appends(request()->except('page'))->links() }}

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

