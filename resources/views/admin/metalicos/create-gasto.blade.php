@extends('layouts.appAdmin')
@section('content')
<div class="container">
    <h1>Nuevo Gasto Metálico</h1>
    <form action="{{ route('metalicos.storeGasto') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="titulo" class="form-label">Título</label>
            <input type="text" name="titulo" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="importe" class="form-label">Importe</label>
            <input type="number" step="0.01" name="importe" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="reserva_id" class="form-label">Reserva ID</label>
            <input type="number" name="reserva_id" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="fecha_ingreso" class="form-label">Fecha de Ingreso</label>
            <input type="date" name="fecha_ingreso" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Guardar</button>
    </form>
</div>
@endsection
