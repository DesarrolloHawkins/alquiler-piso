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
        <div class="col-md-12">
            <form action="{{route('configuracion.update', $configuraciones[0]->id)}}" method="POST">
                @csrf
                <div class="row">
                    <label class="">Contraseña Booking</label>
                    <input class="form-input" name="password_booking" value="{{$configuraciones[0]->password_booking}}"/>
                </div>       
                <div class="row">
                    <label class="">Contraseña Airbnb</label>
                    <input class="form-input" name="password_airbnb" value="{{$configuraciones[0]->password_airbnb}}"/>
                </div>
                <button type="submit">Actualizar</button>    
            </form>

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

