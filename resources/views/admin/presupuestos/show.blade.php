@extends('layouts.appAdmin')

@section('content')
<div class="container">
    <h1>Presupuesto #{{ $presupuesto->id }}</h1>
    <p><strong>Cliente:</strong> {{ $presupuesto->cliente->nombre ?? 'Sin cliente asignado' }}</p>
    <p><strong>Fecha:</strong> {{ $presupuesto->fecha }}</p>
    <p><strong>Total:</strong> {{ number_format($presupuesto->total, 2) }} €</p>

    <h4>Conceptos</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Concepto</th>
                <th>Fecha Entrada</th>
                <th>Fecha Salida</th>
                <th>Días Totales</th>
                <th>Precio por Día</th>
                <th>Precio Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($presupuesto->conceptos as $concepto)
            <tr>
                <td>{{ $concepto->concepto }}</td>
                <td>{{ $concepto->fecha_entrada }}</td>
                <td>{{ $concepto->fecha_salida }}</td>
                <td>{{ $concepto->dias_totales }}</td>
                <td>{{ number_format($concepto->precio_por_dia, 2) }} €</td>
                <td>{{ number_format($concepto->precio_total, 2) }} €</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection
