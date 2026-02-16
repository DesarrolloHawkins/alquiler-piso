@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="h3 mb-1 text-gray-800">
            <i class="fas fa-plus-circle text-primary me-2"></i>
            Crear Nuevo Cupón
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('inicio') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.cupones.index') }}">Cupones</a></li>
                <li class="breadcrumb-item active">Crear</li>
            </ol>
        </nav>
    </div>

    <form action="{{ route('admin.cupones.store') }}" method="POST">
        @csrf
        @include('admin.cupones._form')
    </form>
</div>
@endsection
