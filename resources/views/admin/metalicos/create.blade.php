@extends('layouts.appAdmin')

@section('content')
<div class="container">
    <h1>Nuevo Metálico</h1>
    <form action="{{ route('metalicos.store') }}" method="POST">
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
            <label for="tipo" class="form-label">Tipo</label>
            <select name="tipo" class="form-control" required>
                <option value="ingreso">Ingreso</option>
                <option value="gasto">Gasto</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="fecha_ingreso" class="form-label">Fecha de Ingreso</label>
            <input type="date" name="fecha_ingreso" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="observaciones" class="form-label">Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="3" placeholder="Notas opcionales"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Guardar</button>
    </form>
</div>
@endsection
