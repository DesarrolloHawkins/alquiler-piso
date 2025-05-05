@extends('layouts.appPersonal')

@section('title')
    {{ __('Subidas de fotos de la categoría ') . $checklist->nombre }}
@endsection

@section('volver')
    <button class="back" type="button" onclick="history.back()"><i class="fa-solid fa-angle-left"></i></button>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('gestion.edit', $id) }}" method="GET" enctype="multipart/form-data" id="uploadForm">
        @csrf
        @foreach ($categorias as $categoria)
            <div class="files mt-4 card p-2">
                <h3 class="text-center text-uppercase fw-bold">{{ $categoria->nombre }}</h3>
                <input type="file"
                    accept="image/*"
                    class="file-input"
                    capture="camera"
                    name="image_{{ $categoria->id }}"
                    id="image_{{ $categoria->id }}"
                    style="display: none;">
                <button type="button"
                        class="btn btn-secundario fs-5"
                        onclick="document.getElementById('image_{{ $categoria->id }}').click()">
                    <i class="fa-solid fa-camera me-2"></i> CÁMARA
                </button>
                <img id="preview_{{ $categoria->id }}"
                    style="max-width: 100%; margin-top: 10px;"
                    src="{{ isset($imagenes[$categoria->id]) ? asset($imagenes[$categoria->id]->photo_url) : '' }}">
            </div>
        @endforeach

        <button id="btn_continuar" class="btn btn-terminar mt-3 w-100 text-uppercase fs-4" type="submit">Continuar</button>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    @foreach ($categorias as $categoria)
        const input{{ $categoria->id }} = document.getElementById('image_{{ $categoria->id }}');
        input{{ $categoria->id }}.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('preview_{{ $categoria->id }}').src = e.target.result;
            };
            reader.readAsDataURL(file);

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('image', file);
            formData.append('item_id', '{{ $categoria->id }}');
            formData.append('checklist_id', '{{ $cat }}');
            console.log(formData);
            // Usar route con placeholders y reemplazarlos
            const baseRoute = "{{ route('fotos.' . strtolower(strtr($checklist->nombre, [
                ' ' => '_', 'á' => 'a', 'é' => 'e', 'í' => 'i',
                'ó' => 'o', 'ú' => 'u', 'Á' => 'a', 'É' => 'e',
                'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'ñ' => 'n',
                'Ñ' => 'n'
            ])).'-store', ['id' => '__ID__', 'cat' => '__CAT__']) }}";

            const uploadUrl = baseRoute.replace('__ID__', '{{ $id }}').replace('__CAT__', '{{ $cat }}') + '';

            fetch(uploadUrl, {
                method: 'POST',
                body: formData,
            })
            .then(async response => {
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.includes("application/json")) {
                    const data = await response.json();
                    if (data.url) {
                        document.getElementById('preview_{{ $categoria->id }}').src = data.url;
                    }
                } else {
                    const text = await response.text();
                    console.error('❌ Respuesta no JSON:', text);
                }
            })
            .catch(error => console.error('Error al subir imagen:', error));
        });
    @endforeach
});
</script>
@endsection
