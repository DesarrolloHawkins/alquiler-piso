@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Nuestros Clientes') }}</div>

                <div class="card-body">
                    <h2>Bienvenido {{Auth::user()->name}}</h2>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
