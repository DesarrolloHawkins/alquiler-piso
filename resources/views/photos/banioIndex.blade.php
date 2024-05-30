@extends('layouts.appPersonal')
@section('volver')
    <button class="back" type="button" onclick="history.back()"><i class="fa-solid fa-angle-left"></i></button>
@endsection

@section('title')
{{ __('Subidas de fotos del baño')}}
@endsection

@section('content')
<style>
    .file-input {
      display: none;
    }
</style>
<div class="container-fluid">
    <form action="{{ route('fotos.banioStore', $id) }}" method="POST" enctype="multipart/form-data" id="uploadForm">
        @csrf
        <div class="filesc card p-2">
            <h3 class="text-center text-uppercase fw-bold">Baños General</h3>
            <input type="file" accept="image/*" class="file-input"  capture="camera" name="image_general" id="image_general" onchange="resizeImage(event, 'image-preview', 'image_general_resized')">
            <button type="button" class="btn btn-secundario fs-5" onclick="document.getElementById('image_general').click()"><i class="fa-solid fa-camera me-2"></i> CÁMARA</button>
            <img id="image-preview" style="max-width: 100%; max-height: auto; margin-top: 10px;"/>
            <input type="hidden" name="image_general_resized" id="image_general_resized">
        </div>
        <div class="files mt-4 card p-2">
            <h3 class="text-center text-uppercase fw-bold">Baños Inodoro</h3>
            <input type="file" accept="image/*" class="file-input" capture="camera" name="image_inodoro" id="image_inodoro" onchange="resizeImage(event, 'image-preview2', 'image_inodoro_resized')">
            <button type="button" class="btn btn-secundario fs-5" onclick="document.getElementById('image_inodoro').click()"><i class="fa-solid fa-camera me-2"></i> CÁMARA</button>
            <img id="image-preview2" style="max-width: 100%; max-height: auto; margin-top: 10px;"/>
            <input type="hidden" name="image_inodoro_resized" id="image_inodoro_resized">
        </div>
        <div class="files mt-4 card p-2">
            <h3 class="text-center text-uppercase fw-bold">Baños Desagüe Ducha</h3>
            <input type="file" accept="image/*" class="file-input" capture="camera" name="image_desague" id="image_desague" onchange="resizeImage(event, 'image-preview3', 'image_desague_resized')">
            <button type="button" class="btn btn-secundario fs-5" onclick="document.getElementById('image_desague').click()"><i class="fa-solid fa-camera me-2"></i> CÁMARA</button>
            <img id="image-preview3" style="max-width: 100%; max-height: auto; margin-top: 10px;"/>
            <input type="hidden" name="image_desague_resized" id="image_desague_resized">
        </div>
        
        <button class="btn btn-terminar mt-3 w-100 text-uppercase fs-4" type="submit">Subir Imagenes</button>
    </form>
</div>
@endsection

@section('scripts')
<script>
    console.log('Limpieza de Apartamento by Hawkins.');

    function resizeImage(event, previewElementId, hiddenInputId) {
        var file = event.target.files[0];
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = new Image();
            img.onload = function() {
                var canvas = document.createElement('canvas');
                var ctx = canvas.getContext('2d');
                var maxWidth = 800; // Max width for the image
                var maxHeight = 800; // Max height for the image
                var width = img.width;
                var height = img.height;

                if (width > height) {
                    if (width > maxWidth) {
                        height *= maxWidth / width;
                        width = maxWidth;
                    }
                } else {
                    if (height > maxHeight) {
                        width *= maxHeight / height;
                        height = maxHeight;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                ctx.drawImage(img, 0, 0, width, height);

                // Show the resized image in the preview element
                var dataurl = canvas.toDataURL('image/jpeg');
                document.getElementById(previewElementId).src = dataurl;

                // Set the resized image data in the hidden input
                document.getElementById(hiddenInputId).value = dataurl;
            }
            img.src = e.target.result;
        }
        reader.readAsDataURL(file);
    }

    window.onload = function() {
        var imageUrl = "{{ $imageUrl }}";
        if (imageUrl) {
            var output = document.getElementById('image-preview');
            output.src = imageUrl;
            output.style.display = 'block';
        }
        var imageUrlInodoro = "{{ $imageUrlInodoro }}";
        if (imageUrlInodoro) {
            var output2 = document.getElementById('image-preview2');
            output2.src = imageUrlInodoro;
            output2.style.display = 'block';
        }
        var imageUrlDesague = "{{ $imageUrlDesague }}";
        if (imageUrlDesague) {
            var output3 = document.getElementById('image-preview3');
            output3.src = imageUrlDesague;
            output3.style.display = 'block';
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
   
</script>
@endsection
