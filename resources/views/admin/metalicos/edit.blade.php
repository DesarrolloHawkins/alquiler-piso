@extends('layouts.appAdmin')

@section('content')
<div class="container">
    <h1>Editar Metálico</h1>
    <form action="{{ route('metalicos.update', $metalico) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="titulo" class="form-label">Título</label>
            <input type="text" name="titulo" class="form-control" value="{{ old('titulo', $metalico->titulo) }}" required>
        </div>

        <div class="mb-3">
            <label for="importe" class="form-label">Importe</label>
            <input type="number" step="0.01" name="importe" class="form-control" value="{{ old('importe', $metalico->importe) }}" required>
        </div>

        <div class="mb-3">
            <label for="tipo" class="form-label">Tipo</label>
            <select name="tipo" class="form-control" required>
                <option value="ingreso" {{ old('tipo', $metalico->tipo) == 'ingreso' ? 'selected' : '' }}>Ingreso</option>
                <option value="gasto" {{ old('tipo', $metalico->tipo) == 'gasto' ? 'selected' : '' }}>Gasto</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="fecha_ingreso" class="form-label">Fecha de Ingreso</label>
            <input type="date" name="fecha_ingreso" class="form-control" value="{{ old('fecha_ingreso', $metalico->fecha_ingreso) }}" required>
        </div>

        <div class="mb-3">
            <label for="observaciones" class="form-label">Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="3">{{ old('observaciones', $metalico->observaciones) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('metalicos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
