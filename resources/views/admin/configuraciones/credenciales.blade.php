@extends('admin.configuraciones.layout')

@section('config-title', 'Credenciales')
@section('config-subtitle', 'Gestiona las credenciales de acceso a plataformas externas (Booking, Airbnb)')

@section('config-content')
<div class="config-card">
    <div class="config-card-header">
        <h5>
            <i class="fas fa-key"></i>
            Credenciales de Usuario - Booking y Airbnb
        </h5>
    </div>
    <div class="config-card-body">
        <form action="{{ route('configuracion.credenciales.update', $configuraciones[0]->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label">
                        <i class="fas fa-user"></i>
                        Usuario Booking
                    </label>
                    <input class="form-control" name="user_booking" value="{{$configuraciones[0]->user_booking}}" placeholder="Usuario de Booking.com"/>
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">
                        <i class="fas fa-lock"></i>
                        Contraseña Booking
                    </label>
                    <input type="password" class="form-control" name="password_booking" value="{{$configuraciones[0]->password_booking}}" placeholder="Contraseña de Booking.com"/>
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">
                        <i class="fas fa-user"></i>
                        Usuario Airbnb
                    </label>
                    <input class="form-control" name="user_airbnb" value="{{$configuraciones[0]->user_airbnb}}" placeholder="Usuario de Airbnb"/>
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">
                        <i class="fas fa-lock"></i>
                        Contraseña Airbnb
                    </label>
                    <input type="password" class="form-control" name="password_airbnb" value="{{$configuraciones[0]->password_airbnb}}" placeholder="Contraseña de Airbnb"/>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Actualizar Credenciales
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

