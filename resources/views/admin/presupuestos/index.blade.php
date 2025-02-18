@extends('layouts.appAdmin')

@section('content')
<div class="container">
    <h1>Presupuestos</h1>
    <a href="{{ route('presupuestos.create') }}" class="btn btn-primary mb-3">Crear Presupuesto</a>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Total</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($presupuestos as $presupuesto)
            <tr>
                <td>{{ $presupuesto->id }}</td>
                <td>{{ $presupuesto->cliente->nombre ?? 'Cliente no asignado' }}</td>
                <td>{{ $presupuesto->fecha }}</td>
                <td>{{ number_format($presupuesto->total, 2) }} €</td>
                <td>
                    <a href="{{ route('presupuestos.show', $presupuesto->id) }}" class="btn btn-info btn-sm">Ver</a>
                    <a href="{{ route('presupuestos.edit', $presupuesto->id) }}" class="btn btn-warning btn-sm">Editar</a>
                    <form action="{{ route('presupuestos.destroy', $presupuesto->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro?')">Eliminar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">No hay presupuestos registrados.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
