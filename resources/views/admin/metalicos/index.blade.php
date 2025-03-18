@extends('layouts.appAdmin')

@section('content')
<div class="container">
    <h1>Lista de Metálicos</h1>
    <a href="{{ route('metalicos.create') }}" class="btn btn-primary">Nuevo Metálico</a>

    @if(session('success'))
        <div class="alert alert-success mt-2">
            {{ session('success') }}
        </div>
    @endif

    <table class="table mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Importe</th>
                <th>Tipo</th>
                <th>Fecha Ingreso</th>
                <th>Saldo Acumulado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5"></td>
                <td><strong>Saldo Inicial:</strong> {{ number_format($saldoInicial, 2) }} €</td>
                <td></td>
            </tr>

            @foreach($response as $metalico)
            <tr>
                <td>{{ $metalico->id }}</td>
                <td>{{ $metalico->titulo }}</td>
                <td>{{ number_format($metalico->importe, 2) }} €</td>
                <td>
                    @if($metalico->tipo === 'ingreso')
                        <span class="badge bg-success">Ingreso</span>
                    @else
                        <span class="badge bg-danger">Gasto</span>
                    @endif
                </td>
                <td>{{ $metalico->fecha_ingreso }}</td>
                <td>{{ number_format($metalico->saldo, 2) }} €</td>
                <td>
                    <a href="{{ route('metalicos.show', $metalico) }}" class="btn btn-info btn-sm">Ver</a>
                    <a href="{{ route('metalicos.edit', $metalico) }}" class="btn btn-warning btn-sm">Editar</a>
                    <form action="{{ route('metalicos.destroy', $metalico) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar?')">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
