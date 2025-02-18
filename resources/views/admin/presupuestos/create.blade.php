@extends('layouts.appAdmin')

@section('content')
<div class="container">
    <h1>Crear Presupuesto</h1>
    @include('admin.presupuestos.form', [
        'action' => route('presupuestos.store'),
        'method' => null,
        'presupuesto' => null,
        'clientes' => $clientes
    ])
</div>
@endsection
