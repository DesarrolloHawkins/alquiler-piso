@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-row mb-3 justify-content-between">
        <h2 class="me-3 encabezado_top"><i class="fa-solid fa-arrow-left icon_encabezado"></i>{{ __('Editar Propiedad') }}</h2>
    </div>
    <div class="migas d-flex flex-row mb-3 justify-content-between">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a class="link-breadcrumb" href="#">Dashboard</a></li>
              <li class="breadcrumb-item"><a class="link-breadcrumb" href="#">Apartamentos</a></li>
              <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
        <button id="formGuardar" class="btn btn-guardar fs-5" type="submit"><i class="fa-regular fa-circle-check me-2"></i> Actualizar</button>
    </div>
    <form id="form" action="{{ route('apartamentos.admin.update', $apartamento->id) }}" method="POST" enctype="multipart/form-data" class="row">
        @csrf
        @method('PUT')

        <h4 class="mb-0"><strong>Información General</strong></h4>
        <hr class="my-4">

        {{-- Título --}}
        <div class="col-sm-12 col-md-6 mb-3">
            <label for="title" class="form-label">Título</label>
            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $apartamento->title) }}" placeholder="Título principal de la propiedad">
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Tipo de Propiedad --}}
        <div class="col-sm-12 col-md-3 mb-3">
            <label for="property_type" class="form-label">Tipo de Propiedad</label>
            <select class="form-select @error('property_type') is-invalid @enderror" id="property_type" name="property_type">
                <option value="apartment" {{ old('property_type', $apartamento->property_type) == 'apartment' ? 'selected' : '' }}>Apartamento</option>
                <option value="hotel" {{ old('property_type', $apartamento->property_type) == 'hotel' ? 'selected' : '' }}>Hotel</option>
                <option value="hostel" {{ old('property_type', $apartamento->property_type) == 'hostel' ? 'selected' : '' }}>Hostel</option>
                <option value="villa" {{ old('property_type', $apartamento->property_type) == 'villa' ? 'selected' : '' }}>Villa</option>
                <option value="guest_house" {{ old('property_type', $apartamento->property_type) == 'guest_house' ? 'selected' : '' }}>Casa de Huéspedes</option>
            </select>
            @error('property_type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Edificio -->
        <div class="col-sm-12 col-md-3 mb-3">
            <label for="edificio" class="form-label">Edificio</label>
            <select name="edificio_id" id="edificio_id" class="form-select @error('edificio_id') is-invalid @enderror">
                <option value="">Seleccione un Edificio</option>
                @if (count($edificios) > 0)
                    @foreach ($edificios as $edificio)
                        <option value="{{ $edificio->id }}" {{ old('edificio_id', $apartamento->edificio_id) == $edificio->id ? 'selected' : '' }}>
                            {{ $edificio->nombre }}
                        </option>
                    @endforeach
                @endif
            </select>
            @error('edificio_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Moneda --}}
        <div class="col-sm-12 col-md-4 mb-3">
            <label for="currency" class="form-label">Moneda</label>
            <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency">
                <option value="EUR" {{ old('currency', $apartamento->currency) == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                <option value="USD" {{ old('currency', $apartamento->currency) == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                <option value="GBP" {{ old('currency', $apartamento->currency) == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                <option value="JPY" {{ old('currency', $apartamento->currency) == 'JPY' ? 'selected' : '' }}>JPY - Japanese Yen</option>
                <option value="AUD" {{ old('currency', $apartamento->currency) == 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar</option>
            </select>
            @error('currency')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- País --}}
        <div class="col-sm-12 col-md-4 mb-3">
            <label for="country" class="form-label">País</label>
            <select class="form-select @error('country') is-invalid @enderror" id="country" name="country">
                <option value="ES" {{ old('country', $apartamento->country) == 'ES' ? 'selected' : '' }}>España</option>
                <option value="FR" {{ old('country', $apartamento->country) == 'FR' ? 'selected' : '' }}>Francia</option>
                <option value="IT" {{ old('country', $apartamento->country) == 'IT' ? 'selected' : '' }}>Italia</option>
                <option value="DE" {{ old('country', $apartamento->country) == 'DE' ? 'selected' : '' }}>Alemania</option>
                <option value="US" {{ old('country', $apartamento->country) == 'US' ? 'selected' : '' }}>Estados Unidos</option>
            </select>
            @error('country')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Estado/Provincia --}}
        <div class="col-sm-12 col-md-4 mb-3">
            <label for="state" class="form-label">Estado/Provincia</label>
            <select class="form-select @error('state') is-invalid @enderror" id="state" name="state">
                <option value="Cádiz" {{ old('state', $apartamento->state) == 'Cádiz' ? 'selected' : '' }}>Cádiz</option>
                <option value="Málaga" {{ old('state', $apartamento->state) == 'Málaga' ? 'selected' : '' }}>Málaga</option>
                <option value="Sevilla" {{ old('state', $apartamento->state) == 'Sevilla' ? 'selected' : '' }}>Sevilla</option>
                <option value="Granada" {{ old('state', $apartamento->state) == 'Granada' ? 'selected' : '' }}>Granada</option>
                <option value="Madrid" {{ old('state', $apartamento->state) == 'Madrid' ? 'selected' : '' }}>Madrid</option>
            </select>
            @error('state')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Zona Horaria -->
        <div class="col-sm-12 col-md-4 mb-3">
            <label for="timezone" class="form-label">Zona Horaria</label>
            <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone">
                <option value="Europe/Madrid" {{ old('state', $apartamento->timezone) == 'Europe/Madrid' ? 'selected' : '' }}>Europe/Madrid</option>
                <option value="Europe/Paris" {{ old('state', $apartamento->timezone) == 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris</option>
                <option value="Europe/Rome" {{ old('state', $apartamento->timezone) == 'Europe/Rome' ? 'selected' : '' }}>Europe/Rome</option>
                <option value="Europe/London" {{ old('state', $apartamento->timezone) == 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                <option value="America/New_York" {{ old('state', $apartamento->timezone) == 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
            </select>
            @error('timezone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Dirección --}}
        <div class="col-sm-12 col-md-4 mb-3">
            <label for="address" class="form-label">Dirección</label>
            <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address', $apartamento->address) }}" placeholder="Dirección de la propiedad">
            @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-sm-12 col-md-4 mb-3">
            <label for="city" class="form-label">Ciudad</label>
            <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', $apartamento->city) }}" placeholder="Ciudad de la propiedad">
            @error('city')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-sm-12 col-md-4 mb-3">
            <label for="zip_code" class="form-label">Código Postal</label>
            <input type="text" class="form-control @error('zip_code') is-invalid @enderror" id="zip_code" name="zip_code" value="{{ old('zip_code', $apartamento->zip_code) }}" placeholder="Codigo postal de la ciudad">
            @error('zip_code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Longitud --}}
        <div class="col-sm-12 col-md-4 mb-3">
            <label for="longitude" class="form-label">Longitud</label>
            <input type="text" class="form-control @error('longitude') is-invalid @enderror" id="longitude" name="longitude" value="{{ old('longitude', $apartamento->longitude) }}" readonly>
            @error('longitude')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Latitud --}}
        <div class="col-sm-12 col-md-4 mb-3">
            <label for="latitude" class="form-label">Latitud</label>
            <input type="text" class="form-control @error('latitude') is-invalid @enderror" id="latitude" name="latitude" value="{{ old('latitude', $apartamento->latitude) }}" readonly>
            @error('latitude')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

         <!-- Email -->
         <div class="col-sm-12 col-md-4 mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $apartamento->email) }}" placeholder="Dirección de correo electronico">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Teléfono -->
        <div class="col-sm-12 col-md-4 mb-3">
            <label for="phone" class="form-label">Teléfono</label>
            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $apartamento->phone) }}" placeholder="Numero de telefono">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Website -->
        <div class="col-sm-12 col-md-4 mb-3">
            <label for="website" class="form-label">Sitio Web</label>
            <input type="url" class="form-control @error('website') is-invalid @enderror" id="website" name="website" value="{{ old('website', $apartamento->website) }}" placeholder="Sitio web la propiedad">
            @error('website')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Otra información -->
        <div class="col-12">
            <div class="row">
                <!-- Descripción -->
                <div class="col-sm-12 col-md-6 mb-3">
                    <label for="description" class="form-label">Descripción</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" placeholder="Descripción de la propiedad">{{ old('description', $apartamento->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Información Importante -->
                <div class="col-sm-12 col-md-6 mb-3">
                    <label for="important_information" class="form-label">Información Importante</label>
                    <textarea class="form-control @error('important_information') is-invalid @enderror" id="important_information" name="important_information" placeholder="Información importante sobre la propiedad">{{ old('important_information', $apartamento->important_information) }}</textarea>
                    @error('important_information')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Información Importante -->
                <div class="col-sm-12 col-md-6 mb-3">
                    <label for="claves" class="form-label">Claves de Acceso</label>
                    <textarea class="form-control @error('claves') is-invalid @enderror" id="claves" name="claves" placeholder="Claves para acceder al apartamento">{{ old('claves', $apartamento->claves) }}</textarea>
                    @error('claves')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

        </div>

        {{-- Fotos --}}
        <div class="col-sm-12 col-md-12 mb-3">
            <div class="d-flex flex-row mb-3 justify-content-between">
                <h4 class="mb-0"><strong>Imágenes</strong></h4>
                <button type="button" class="btn btn-foto-apartamento fs-6" id="add-photo"><i class="fa-solid fa-circle-plus me-2"></i> Añadir Foto</button>
            </div>
            <hr class="my-4">
            <div id="photo-container">
                @foreach ($apartamento->photos as $index => $photo)
                    <div class="photo-group mb-3 row">
                        <div class="col-sm-12 col-md-3 mb-3 p-0">
                            <input type="file" class="form-control mb-2" name="photos[{{ $index }}][file]">
                        </div>
                        <div class="col-sm-12 col-md-3 mb-3 p-0">
                            <input type="number" class="form-control mb-2" name="photos[{{ $index }}][position]" value="{{ $photo->position }}">
                        </div>
                        <div class="col-sm-12 col-md-3 mb-3 p-0">
                            <input type="text" class="form-control mb-2" name="photos[{{ $index }}][author]" value="{{ $photo->author }}">
                        </div>
                        <div class="col-sm-12 col-md-3 mb-3 p-0">
                            <input type="text" class="form-control mb-2" name="photos[{{ $index }}][kind]" value="{{ $photo->kind }}">
                        </div>
                        <div class="col-sm-12 col-md-12 p-0">
                            <textarea class="form-control" name="photos[{{ $index }}][description]">{{ $photo->description }}</textarea>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- <button type="submit" class="btn btn-terminar mt-4">Actualizar</button> --}}
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const formGuardar = document.getElementById('formGuardar');
        const form = document.getElementById('form');

        formGuardar.addEventListener('click', function(e){
            e.preventDefault();
            form.submit();
        })
    })
</script>
@endsection
