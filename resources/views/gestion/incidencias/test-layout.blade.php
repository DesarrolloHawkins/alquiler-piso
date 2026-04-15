@extends('layouts.appPersonal')

@section('title')
    Test Layout
@endsection

@section('bienvenido')
    <h5 class="navbar-brand mb-0 w-auto text-center text-white">Test Layout</h5>
@endsection

@section('content')
<div style="background: red; color: white; padding: 20px; margin: 20px;">
    <h1>TEST LAYOUT FUNCIONA</h1>
    <p>Si ves esto en rojo, el layout appPersonal funciona.</p>
    <p>Usuario: {{ Auth::user()->name }}</p>
</div>
@endsection
