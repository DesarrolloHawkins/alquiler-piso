@extends('layouts.appPersonal')

@section('title')
    Mis Incidencias
@endsection

@section('bienvenido')
    <h5 class="navbar-brand mb-0 w-auto text-center text-white">Bienvenid@ {{Auth::user()->name}}</h5>
@endsection

@section('content')
<h1>DEBUG</h1>
@endsection
