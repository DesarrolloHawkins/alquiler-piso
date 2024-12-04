@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column mb-3">
        <h2 class="mb-0 me-3 encabezado_top">{{ __('Crear Propiedad') }}</h2>
    </div>
    <hr>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <form action="{{ route('channex.storeProperty') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="title" class="form-label">Título</label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}">
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Teléfono</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Dirección</label>
                    <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address') }}">
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="city" class="form-label">Ciudad</label>
                    <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city') }}">
                    @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="zip_code" class="form-label">Código Postal</label>
                    <input type="text" class="form-control @error('zip_code') is-invalid @enderror" id="zip_code" name="zip_code" value="{{ old('zip_code') }}">
                    @error('zip_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="longitude" class="form-label">Longitud</label>
                    <input type="text" class="form-control @error('longitude') is-invalid @enderror" id="longitude" name="longitude" value="{{ old('longitude') }}" readonly>
                    @error('longitude')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="latitude" class="form-label">Latitud</label>
                    <input type="text" class="form-control @error('latitude') is-invalid @enderror" id="latitude" name="latitude" value="{{ old('latitude') }}" readonly>
                    @error('latitude')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div id="map" style="height: 400px;" class="mb-3"></div>

                <button type="submit" class="btn btn-terminar w-100 fs-4 mt-4">Guardar</button>
            </form>
        </div>
    </div>
</div>

<!-- Leaflet.js -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
<script>
    const map = L.map('map').setView([51.505, -0.09], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    }).addTo(map);

    let marker;

    async function searchCoordinates() {
        const address = document.getElementById('address').value;
        const city = document.getElementById('city').value;
        const zipCode = document.getElementById('zip_code').value;

        if (address && city && zipCode) {
            const query = `${address}, ${city}, ${zipCode}`;
            const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${query}`);
            const data = await response.json();

            if (data.length > 0) {
                const lat = data[0].lat;
                const lon = data[0].lon;

                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lon;

                if (marker) {
                    map.removeLayer(marker);
                }

                marker = L.marker([lat, lon]).addTo(map);
                map.setView([lat, lon], 15);
            } else {
                alert('No se pudo encontrar la ubicación exacta con los datos proporcionados.');
            }
        } else {
            alert('Por favor, complete Dirección, Ciudad y Código Postal antes de buscar.');
        }
    }

    const fields = ['address', 'city', 'zip_code'];
    fields.forEach(field => {
        document.getElementById(field).addEventListener('change', searchCoordinates);
    });
</script>
@endsection
