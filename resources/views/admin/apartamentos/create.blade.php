@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-row mb-3 justify-content-between">
        <h2 class="me-3 encabezado_top"><i class="fa-solid fa-arrow-left icon_encabezado"></i>{{ __('Crear Propiedad') }}</h2>
    </div>
    <div class="migas d-flex flex-row mb-3 justify-content-between">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a class="link-breadcrumb" href="#">Dashboard</a></li>
              <li class="breadcrumb-item"><a class="link-breadcrumb" href="#">Apartamentos</a></li>
              <li class="breadcrumb-item active" aria-current="page">Crear</li>
            </ol>
        </nav>
        <button id="formGuardar" class="btn btn-guardar fs-5" type="submit"><i class="fa-regular fa-circle-check me-2"></i> Guardar</button>
    </div>
    <form id="form" action="{{ route('channex.storeProperty') }}" method="POST" enctype="multipart/form-data" class="row">
        {{-- @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif --}}

        @csrf

        <h4 class="mb-0"><strong>Informacion General</strong></h4>
        <hr class="my-4">

        {{-- Titulo --}}
        <div class="col-sm-12 col-md-6 mb-3">
            <label for="title" class="form-label">Título</label>
            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" placeholder="Titulo principal de la propiedad">
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Tipo de Propiedad -->
        <div class="col-sm-12 col-md-6 mb-3">
            <label for="property_type" class="form-label">Tipo de Propiedad</label>
            <select class="form-select @error('property_type') is-invalid @enderror" id="property_type" name="property_type">
                <option value="apartment" selected>Apartamento</option>
                <option value="hotel">Hotel</option>
                <option value="hostel">Hostel</option>
                <option value="villa">Villa</option>
                <option value="guest_house">Casa de Huéspedes</option>
            </select>
            @error('property_type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Moneda -->
        <div class="col-sm-12 col-md-4 mb-3">
            <label for="currency" class="form-label">Moneda</label>
            <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency">
                <option value="EUR" selected>EUR - Euro</option>
                <option value="USD">USD - US Dollar</option>
                <option value="GBP">GBP - British Pound</option>
                <option value="JPY">JPY - Japanese Yen</option>
                <option value="AUD">AUD - Australian Dollar</option>
            </select>
            @error('currency')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- País -->
        <div class="col-sm-12 col-md-4 mb-3">
            <label for="country" class="form-label">País</label>
            <select class="form-select @error('country') is-invalid @enderror" id="country" name="country">
                <option value="ES" selected>España</option>
                <option value="FR">Francia</option>
                <option value="IT">Italia</option>
                <option value="DE">Alemania</option>
                <option value="US">Estados Unidos</option>
            </select>
            @error('country')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Estado/Provincia -->
        <div class="col-sm-12 col-md-4 mb-3">
            <label for="state" class="form-label">Estado/Provincia</label>
            <select class="form-select @error('state') is-invalid @enderror" id="state" name="state">
                <option value="Cádiz" selected>Cádiz</option>
                <option value="Málaga">Málaga</option>
                <option value="Sevilla">Sevilla</option>
                <option value="Granada">Granada</option>
                <option value="Madrid">Madrid</option>
            </select>
            @error('state')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Zona Horaria -->
        <div class="col-sm-12 col-md-4 mb-3">
            <label for="timezone" class="form-label">Zona Horaria</label>
            <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone">
                <option value="Europe/Madrid" selected>Europe/Madrid</option>
                <option value="Europe/Paris">Europe/Paris</option>
                <option value="Europe/Rome">Europe/Rome</option>
                <option value="Europe/London">Europe/London</option>
                <option value="America/New_York">America/New_York</option>
            </select>
            @error('timezone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Dirección, Ciudad, Código Postal, Longitud y Latitud -->
        <div class="col-sm-12 col-md-4 mb-3">
            <label for="address" class="form-label">Dirección</label>
            <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address') }}" placeholder="Dirección de la propiedad">
            @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-sm-12 col-md-4 mb-3">
            <label for="city" class="form-label">Ciudad</label>
            <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city') }}" placeholder="Ciudad de la propiedad">
            @error('city')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-sm-12 col-md-4 mb-3">
            <label for="zip_code" class="form-label">Código Postal</label>
            <input type="text" class="form-control @error('zip_code') is-invalid @enderror" id="zip_code" name="zip_code" value="{{ old('zip_code') }}" placeholder="Codigo postal de la ciudad">
            @error('zip_code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-sm-12 col-md-4 mb-3">
            <label for="longitude" class="form-label">Longitud</label>
            <input type="text" class="form-control @error('longitude') is-invalid @enderror" id="longitude" name="longitude" value="{{ old('longitude') }}" readonly>
            @error('longitude')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-sm-12 col-md-4 mb-3">
            <label for="latitude" class="form-label">Latitud</label>
            <input type="text" class="form-control @error('latitude') is-invalid @enderror" id="latitude" name="latitude" value="{{ old('latitude') }}" readonly>
            @error('latitude')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Email -->
        <div class="col-sm-12 col-md-4 mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Dirección de correo electronico">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Teléfono -->
        <div class="col-sm-12 col-md-4 mb-3">
            <label for="phone" class="form-label">Teléfono</label>
            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Numero de telefono">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Website -->
        <div class="col-sm-12 col-md-4 mb-3">
            <label for="website" class="form-label">Sitio Web</label>
            <input type="url" class="form-control @error('website') is-invalid @enderror" id="website" name="website" value="{{ old('website') }}" placeholder="Sitio web la propiedad">
            @error('website')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Edificio -->
        <div class="col-sm-12 col-md-4 mb-3">
            <label for="edificio" class="form-label">Edificio</label>
            <select name="edificio_id" id="edificio_id" class="form-select @error('edificio_id') is-invalid @enderror">
                <option value="{{null}}">Seleccione un Edificio</option>
                @if (count($edificios) > 0)
                    @foreach ($edificios as $edificio)
                        <option value="{{$edificio->id}}">{{$edificio->nombre}}</option>
                    @endforeach
                @endif
            </select>
            @error('edificio_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12">
            <div class="row">
                <!-- Descripción -->
                <div class="col-sm-12 col-md-6 mb-3">
                    <label for="description" class="form-label">Descripción</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" placeholder="Descripción de la propiedad">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Información Importante -->
                <div class="col-sm-12 col-md-6 mb-3">
                    <label for="important_information" class="form-label">Información Importante</label>
                    <textarea class="form-control @error('important_information') is-invalid @enderror" id="important_information" name="important_information" placeholder="Información importante sobre la propiedad">{{ old('important_information') }}</textarea>
                    @error('important_information')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

        </div>

         <!-- Photos -->
         <div class="col-sm-12 col-md-12 mb-3 mt-5">
            <div class="d-flex flex-row mb-3 justify-content-between">
                <h4 class="mb-0"><strong>Imagenes</strong></h4>
                <button type="button" class="btn btn-foto-apartamento fs-6" id="add-photo"><i class="fa-solid fa-circle-plus me-2"></i> Añadir Foto</button>
            </div>
            <hr class="my-4">
            <div id="photo-container">
                <div class="photo-group mb-3 row">
                    <div class="col-sm-12 col-md-3 mb-3 p-0">
                        <input type="file" class="form-control mb-2 @error('photos.0.file') is-invalid @enderror" name="photos[0][file]" accept="image/*">
                    </div>
                    <div class="col-sm-12 col-md-3 mb-3 p-0">
                        <input type="number" class="form-control mb-2" name="photos[0][position]" placeholder="Posición - Orden" value="">
                    </div>
                    <div class="col-sm-12 col-md-3 mb-3 p-0">
                        <input type="text" class="form-control mb-2" name="photos[0][author]" placeholder="Autor">
                    </div>
                    <div class="col-sm-12 col-md-3 mb-3 p-0">
                        <input type="text" class="form-control mb-2" name="photos[0][kind]" placeholder="Tipo (e.g., photo)">
                    </div>
                    <div class="col-sm-12 col-md-12 p-0">
                        <textarea class="form-control" name="photos[0][description]" placeholder="Descripción de la fotografia"></textarea>
                    </div>
                </div>
            </div>
            @error('photos.*.file')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>


        <div id="map" style="height: 400px;" class="mb-3"></div>

        {{-- <button type="submit" class="btn btn-terminar w-100 fs-4 mt-4">Guardar</button> --}}
    </form>

</div>
<style>
    .fondo_gris {
        background-color: rgb(238, 238, 238);
        padding: 30px;
        border-radius: 10px;
    }
</style>

<!-- Google Maps Script -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCjf5b7p8WO6CTTNVnfnv3Xrjz10u-Y74g"></script>
<script>
    let map;
    let marker;
    const geocoder = new google.maps.Geocoder();

    function initializeMap() {
        const defaultLocation = { lat: 40.416775, lng: -3.703790 }; // Madrid como ubicación por defecto
        map = new google.maps.Map(document.getElementById("map"), {
            center: defaultLocation,
            zoom: 6,
        });

        marker = new google.maps.Marker({
            map: map,
            draggable: false,
        });
    }

    async function searchCoordinates() {
        const address = document.getElementById('address').value;
        const city = document.getElementById('city').value;
        const zipCode = document.getElementById('zip_code').value;

        if (address && city && zipCode) {
            const fullAddress = `${address}, ${city}, ${zipCode}`;
            geocoder.geocode({ address: fullAddress }, function (results, status) {
                if (status === "OK") {
                    const location = results[0].geometry.location;

                    document.getElementById('latitude').value = location.lat();
                    document.getElementById('longitude').value = location.lng();

                    map.setCenter(location);
                    map.setZoom(15);

                    marker.setPosition(location);
                } else {
                    alert("No se pudo encontrar la dirección: " + status);
                }
            });
        } else {
            alert('Por favor, complete Dirección, Ciudad y Código Postal antes de buscar.');
        }
    }

    document.getElementById('address').addEventListener('change', searchCoordinates);
    document.getElementById('city').addEventListener('change', searchCoordinates);
    document.getElementById('zip_code').addEventListener('change', searchCoordinates);

    window.onload = initializeMap;
</script>

<script>
    let photoIndex = 1;

    document.getElementById('add-photo').addEventListener('click', function () {
        const container = document.getElementById('photo-container');
        const group = document.createElement('div');
        group.classList.add('photo-group', 'mb-3', 'row', 'mt-5');
        group.innerHTML = `
            <div class="col-sm-12 col-md-3 mb-3 p-0">
                <input type="file" class="form-control mb-2" name="photos[${photoIndex}][file]" accept="image/*">
            </div>
            <div class="col-sm-12 col-md-3 mb-3 p-0">
                <input type="text" class="form-control mb-2" name="photos[${photoIndex}][position]" placeholder="Posición - Orden" value="">
            </div>
            <div class="col-sm-12 col-md-3 mb-3 p-0">
                <input type="text" class="form-control mb-2" name="photos[${photoIndex}][author]" placeholder="Autor">
            </div>
            <div class="col-sm-12 col-md-3 mb-3 p-0">
                <input type="text" class="form-control mb-2" name="photos[${photoIndex}][kind]" placeholder="Tipo (e.g., photo)">
            </div>
            <div class="col-sm-12 col-md-12 mb-3 p-0">
                <textarea class="form-control" name="photos[${photoIndex}][description]" placeholder="Descripción"></textarea>
            </div>
        `;
        container.appendChild(group);
        photoIndex++;
    });
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
