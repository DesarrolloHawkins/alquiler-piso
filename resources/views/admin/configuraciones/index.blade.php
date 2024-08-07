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
                <div class="">
                    <label class="form-label">User Booking</label>
                    <input class="form-control w-50" name="user_booking" value="{{$configuraciones[0]->user_booking}}"/>
                </div>
                <div class="mt-3 mb-3">
                    <label class="form-label">Contraseña Booking</label>
                    <input class="form-control w-50" name="password_booking" value="{{$configuraciones[0]->password_booking}}"/>
                </div>
                <div class="mt-3 mb-3">
                    <label class="form-label">User Airbnb</label>
                    <input class="form-control w-50" name="user_airbnb" value="{{$configuraciones[0]->user_airbnb}}"/>
                </div>
                <div class="mt-3 mb-3">
                    <label class="form-label">Contraseña Airbnb</label>
                    <input class="form-control w-50" name="password_airbnb" value="{{$configuraciones[0]->password_airbnb}}"/>
                </div>
                <button type="submit" class="btn btn-primary">Actualizar</button>    
            </form>

        </div>
    </div>
    <h2 class="mb-3 mt-5">{{ __('Reparaciones') }}</h2>
    <hr class="mb-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <form action="{{route('configuracion.updateReparaciones')}}" method="POST">
                @csrf
                <div class="">
                    <label class="form-label">Nombre de la Persona</label>
                    <input class="form-control w-50" name="nombre" value="@isset($reparaciones[0]->nombre){{$reparaciones[0]->nombre}}@endisset"/>
                </div>
                <div class="mt-3 mb-3">
                    <label class="form-label">Telefono de la Persona</label>
                    <input class="form-control w-50" name="telefono" value="@isset($reparaciones[0]->telefono){{$reparaciones[0]->telefono}}@endisset"/>
                </div>
                <button type="submit" class="btn btn-primary">Actualizar</button>    
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

