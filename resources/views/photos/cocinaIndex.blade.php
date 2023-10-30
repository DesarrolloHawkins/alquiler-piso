@extends('layouts.appPersonal')
@section('volver')
    <button class="back" type="button" onclick="history.back()"><i class="fa-solid fa-angle-left"></i></button>
@endsection

@section('title')
{{ __('Subidas de fotos del dormitorio')}}
@endsection

{{-- @section('bienvenido')
    <h5 class="navbar-brand mb-0 w-auto text-center text-white">Bienvenid@ {{Auth::user()->name}}</h5>
@endsection --}}

@section('content')
<style>
    .file-input {
      display: none;
    }
  </style>
<div class="container-fluid">
    <form action="{{ route('fotos.cocinaStore', $id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="filesc card p-2">
            <h3 class="text-center text-uppercase fw-bold">Cocina General</h3>
            <input type="file" accept="image/*" class="file-input"  capture="camera" name="image_general" id="image_general" onchange="previewImage(event)">
            <button type="button" class="btn btn-secundario fs-5" onclick="document.getElementById('image_general').click()"><i class="fa-solid fa-camera me-2"></i> CÁMARA</button>
            <img id="image-preview" style="max-width: 100%; max-height: auto; margin-top: 10px;"/>
        </div>
        <div class="files mt-4 card p-2">
            <h3 class="text-center text-uppercase fw-bold">Cocina Nevera</h3>
            <input type="file" accept="image/*" class="file-input" capture="camera" name="image_nevera" id="image_nevera" onchange="previewImage2(event)">
            <button type="button" class="btn btn-secundario fs-5" onclick="document.getElementById('image_nevera').click()"><i class="fa-solid fa-camera me-2"></i> CÁMARA</button>
            <img id="image-preview2" style="max-width: 100%; max-height: auto; margin-top: 10px;"/>
        </div>
        <div class="files mt-4 card p-2">
            <h3 class="text-center text-uppercase fw-bold">Cocina Microondas</h3>
            <input type="file" accept="image/*" class="file-input" capture="camera" name="image_microondas" id="image_microondas" onchange="previewImage3(event)">
            <button type="button" class="btn btn-secundario fs-5" onclick="document.getElementById('image_microondas').click()"><i class="fa-solid fa-camera me-2"></i> CÁMARA</button>
            <img id="image-preview3" style="max-width: 100%; max-height: auto; margin-top: 10px;"/>
        </div>
        <div class="files mt-4 card p-2">
            <h3 class="text-center text-uppercase fw-bold">Cocina Bajos</h3>
            <input type="file" accept="image/*" class="file-input" capture="camera" name="image_bajos" id="image_bajos" onchange="previewImage4(event)">
            <button type="button" class="btn btn-secundario fs-5" onclick="document.getElementById('image_bajos').click()"><i class="fa-solid fa-camera me-2"></i> CÁMARA</button>
            <img id="image-preview4" style="max-width: 100%; max-height: auto; margin-top: 10px;"/>
        </div>
        
        <button class="btn btn-terminar mt-3 w-100 text-uppercase fs-4" type="submit">Subir Imagenes</button>
    </form>
</div>
@endsection

@section('scripts')
<script>
    console.log('Limpieza de Apartamento by Hawkins.')

    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('image-preview');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
    // Si ya existe una URL de imagen, mostrar la vista previa al cargar la página
    window.onload = function() {
        var imageUrl = "{{ $imageUrl }}";
        if (imageUrl) {
            var output = document.getElementById('image-preview');
            output.src = imageUrl;
            output.style.display = 'block';
        }
        var imageUrl = "{{ $imageUrlNevera }}";
        if (imageUrl) {
            var output = document.getElementById('image-preview2');
            output.src = imageUrl;
            output.style.display = 'block';
        }
        var imageUrl = "{{ $imageUrlMicroondas }}";
        if (imageUrl) {
            var output = document.getElementById('image-preview3');
            output.src = imageUrl;
            output.style.display = 'block';
        }
        var imageUrl = "{{ $imageUrlBajos }}";
        if (imageUrl) {
            var output = document.getElementById('image-preview4');
            output.src = imageUrl;
            output.style.display = 'block';
        }
    };

    function previewImage2(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('image-preview2');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    function previewImage3(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('image-preview3');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
    function previewImage4(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('image-preview4');
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
@endsection




