@extends('layouts.appAdmin')

@section('content')
<div class="container">
    <h1>Editar Presupuesto</h1>
    @include('admin.presupuestos.form', [
        'action' => route('presupuestos.update', $presupuesto->id),
        'method' => 'PUT',
        'presupuesto' => $presupuesto,
        'clientes' => $clientes
    ])
</div>
@endsection
