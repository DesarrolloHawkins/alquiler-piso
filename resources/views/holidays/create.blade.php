@extends('layouts.appPersonal')

@section('title')
    {{ __('Mis Vacaciones - ') }}
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('assets/vendors/simple-datatables/style.css') }}">
@endsection

@section('content')
<div class="container" style="padding-right: 1.5rem !important; padding-left: 1.5rem !important;">
    <div class="row">
        <div class="col-sm-12 col-md-12 mb-3">
            <h3 class="text-center" style="width: 100%"><i class="fa-solid fa-umbrella-beach"></i> Mis Vacaciones</h3>
        </div>

        <div class="col-sm-12 col-md-12 mb-3">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end m-auto w-100 text-center">
                <ol class="breadcrumb m-auto justify-content-center">
                    {{-- <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li> --}}
                    <li class="breadcrumb-item"><a href="{{ route('holiday.index') }}" class="link-breadcrumb">Mis Vacaciones</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Petición de días</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="page-heading">
        <div class="row justify-content-center">
            @if ($userHolidaysQuantity != null)
                <div class="col-lg col-md-6 mt-4">
                    <div class="card">
                        <div class="card-body text-center">
                            @if ($userHolidaysQuantity->quantity)
                                <p class="fs-4">Tienes <span class="text-success"><strong>{{ $userHolidaysQuantity->quantity }}</strong></span> {{ Str::plural('día', $userHolidaysQuantity->quantity) }} de vacaciones</p>
                            @else
                                <p>No tienes días de vacaciones</p>
                            @endif

                            @if ($numberOfHolidayPetitions)
                                <p class="fs-4">Tienes <span class="text-warning"><strong>{{ $numberOfHolidayPetitions }}</strong></span> {{ Str::plural('petición', $numberOfHolidayPetitions) }} pendiente{{ $numberOfHolidayPetitions > 1 ? 's' : '' }}</p>
                            @else
                                <p class="fs-4">No tienes peticiones pendientes</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6 mt-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="mb-4"><strong>ESTADOS</strong></h5>
                            <p>
                                <i class="fa fa-square" style="color:#FFDD9E"></i> PENDIENTE
                                <i class="fa fa-square" style="margin-left:5%; color:#C3EBC4"></i> ACEPTADA
                                <i class="fa fa-square" style="margin-left:5%; color:#FBC4C4"></i> DENEGADA
                            </p>
                        </div>
                    </div>
                </div>
                <section class="section pt-4">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" action="{{ route('holiday.store') }}" enctype="multipart/form-data" data-callback="formCallback">
                                @csrf
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3 fs-4">
                                        <label for="from_date" class="form-label">Desde</label>
                                        <input type="date" name="from_date" class="form-control" id="from_date" />
                                    </div>
                                    <div class="col-md-3 fs-4">
                                        <label for="to_date" class="form-label">Hasta</label>
                                        <input type="date" name="to_date" class="form-control" id="to_date" />
                                    </div>
                                    <div class="col-md-3 fs-4">
                                        <label class="form-label">Medio día</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="half_day" name="half_day" value="1">
                                            <label class="form-check-label" for="half_day">Sí</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn bg-color-primero width-auto">Realizar Petición</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            @else
                <i class="fa-solid fa-circle-exclamation text-center mt-3 text-warning" style="font-size: 5rem"></i>
                <h4 class="text-center mt-3">No tienes días de vacaciones para solicitar</h4>
                <a class="btn bg-color-primero fs-4 mt-3 width-auto" href="{{route('holiday.index')}}"><i class="fa-solid fa-rotate-left"></i><span class="mx-3">VOVLER</span></a>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
{{-- @include('partials.toast') --}}
@endsection
