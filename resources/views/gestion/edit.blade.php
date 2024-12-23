@extends('layouts.appPersonal')

@section('title')
    {{ __('Realizando el Apartamento - ') . $apartamentoLimpieza->apartamento->nombre }}
@endsection

@section('bienvenido')
    <h5 class="navbar-brand mb-0 w-auto text-center text-white">Bienvenid@ {{ Auth::user()->name }}</h5>
@endsection

@section('content')
<div class="container" style="padding-right: 2.5rem !important; padding-left: 2.5rem !important;">
    <h2 class="mb-3">{{ __('Editar Limpieza del Apartamento') }}</h2>
    <hr class="mb-2">

    <form id="limpieza-form" action="{{ route('gestion.update', $apartamentoLimpieza->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        @if($checklists->isNotEmpty())
            @foreach($checklists as $checklist)
                <h4 class="bg-color-tercero text-white p-2 mb-3 fw-bold">
                    <i class="fa-solid fa-spray-can-sparkles fs-5 me-2 fw-regular"></i> {{ $checklist->nombre }}
                </h4>

                <!-- Mostrar los items del checklist -->
                @foreach($checklist->items as $item)
                    <div class="form-check form-switch mt-1">
                        <input data-id="{{ $apartamentoLimpieza->id }}" style="margin-right: 0 !important" class="form-check-input" type="checkbox" id="item_{{ $item->id }}" name="item_{{ $item->id }}" {{ old('item_' . $item->id, $item->pivot->status ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="item_{{ $item->id }}" style="padding-left: 0.5rem !important;">{{ $item->nombre }}</label>
                    </div>
                @endforeach

                <!-- Mostrar los requisitos de fotos para este checklist -->
                @if($checklist->photoRequirements->isNotEmpty())
                <br>
                    <h5 class="bg-danger text-white p-2 mb-3 fw-bold pulse-animation">
                        <i class="fa-solid fa-camera-retro me-2 fw-regular fs-5"></i> {{ __('Fotos Requeridas') }}
                    </h5>
                    @foreach($checklist->photoRequirements as $requirement)
                        <div class="mb-3">
                            <label class="fw-bold mb-2">{{ $requirement->nombre }} ({{ $requirement->cantidad }} fotos)</label>
                            <input type="file" name="photos[{{ $requirement->photo_categoria_id }}][]" multiple="multiple" class="form-control photo-upload" data-categoria-id="{{ $requirement->photo_categoria_id }}" data-requirement-id="{{ $requirement->id }}">
                            <div class="preview-container mt-2" id="preview-container-{{ $requirement->photo_categoria_id }}">
                                <!-- Mostrar las fotos ya subidas -->
                                @if(isset($uploadedPhotos[$requirement->photo_categoria_id]))
                                    @foreach($uploadedPhotos[$requirement->photo_categoria_id] as $photo)
                                        <img src="{{ asset($photo->url) }}" alt="{{ $photo->descripcion }}" class="img-thumbnail" style="max-width: 200px; margin: 5px;">
                                    @endforeach
                                @endif
                            </div> <!-- Contenedor para la vista previa -->
                            @if($requirement->descripcion)
                                <small class="form-text text-muted">{{ $requirement->descripcion }}</small>
                            @endif
                        </div>
                    @endforeach
                @endif
                <hr>
            @endforeach
        @else
            <p>{{ __('No hay checklists asociados a este edificio.') }}</p>
        @endif

        <button type="submit" class="btn btn-primary">{{ __('Guardar cambios') }}</button>
    </form>
</div>

<!-- CSS para la animación pulse -->
<style>
    @keyframes pulse {
        0% {
            box-shadow: 0 0 5px rgba(255, 0, 0, 0.4);
        }
        50% {
            box-shadow: 0 0 20px rgba(255, 0, 0, 0.7);
        }
        100% {
            box-shadow: 0 0 5px rgba(255, 0, 0, 0.4);
        }
    }

    .pulse-animation {
        animation: pulse 2s infinite;
    }

    .preview-container img {
        max-width: 200px;
        margin: 5px;
        border: 2px solid #ddd;
        border-radius: 5px;
    }
</style>
@endsection

@section('scripts')
<script>
    // Mostrar vista previa de imágenes seleccionadas
    document.querySelectorAll('.photo-upload').forEach(input => {
    input.addEventListener('change', function(event) {
        const files = event.target.files;
        const categoriaId = event.target.getAttribute('data-categoria-id');
        const requirementId = event.target.getAttribute('data-requirement-id');  // Agregar esta línea
        const previewContainer = document.getElementById('preview-container-' + categoriaId);

        previewContainer.innerHTML = '';  // Limpiar vista previa

        Array.from(files).forEach(file => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function(event) {
                const img = new Image();
                img.src = event.target.result;
                previewContainer.appendChild(img);

                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    const maxWidth = 900;
                    const scaleSize = maxWidth / img.width;
                    canvas.width = maxWidth;
                    canvas.height = img.height * scaleSize;
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

                    canvas.toBlob(function(blob) {
                        const formData = new FormData();
                        formData.append('photo', blob, file.name);
                        formData.append('photo_categoria_id', categoriaId);
                        formData.append('requirement_id', requirementId);  // Usar el requirementId obtenido
                        formData.append('_token', '{{ csrf_token() }}');

                        fetch("{{ route('photo.upload', $apartamentoLimpieza->id) }}", {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log('Foto subida exitosamente:', data);
                        })
                        .catch(error => {
                            console.error('Error al subir la foto:', error);
                        });
                    }, 'image/jpeg');
                };
            };
        });
    });
});

</script>
@endsection
