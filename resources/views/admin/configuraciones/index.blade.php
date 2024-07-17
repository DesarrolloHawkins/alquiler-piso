@extends('layouts.appAdmin')

@section('content')
<style>
    .inactive-sort {
        color: #0F1739;
        text-decoration: none;
    }
    .active-sort {
        color: #757191;
    }
</style>
<div class="container-fluid">
    <h2 class="mb-3">{{ __('Configuracion') }}</h2>
    <hr class="mb-5">
    <div class="row justify-content-center">
        {{dd($configuraciones[0])}}
        <div class="col-md-12">
            <div class="row">
                <div class="col-4">Contraseña Booking</div>
                <div class="col-8">{{$configuraciones[0]->password_booking}}</div>
            </div>       
            <div class="row">
                <div class="col-4">Contraseña Airbnb</div>
                <div class="col-8">{{$configuraciones[0]->password_airbnb}}</div>
            </div>       

        </div>
    </div>
</div>
@endsection

@include('sweetalert::alert')

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Verificar si SweetAlert2 está definido
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 is not loaded');
            return;
        }
    });
</script>
@endsection

