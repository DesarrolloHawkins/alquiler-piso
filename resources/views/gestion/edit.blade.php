@extends('layouts.appPersonal')

@section('title')
    {{ __('Realizando el Apartamento - ') . $apartamentoLimpieza->apartamento->nombre}}
@endsection

@section('bienvenido')
    <h5 class="navbar-brand mb-0 w-auto text-center text-white">Bienvenid@ {{Auth::user()->name}}</h5>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-color-segundo">
                    <i class="fa-solid fa-spray-can-sparkles"></i>
                    <span class="ms-2 text-uppercase fw-bold">{{ __('Apartamento - ') .  $apartamentoLimpieza->apartamento->nombre}}</span>
                </div>
                <div class="card-body">
                    <form action="{{ route('gestion.update', $apartamentoLimpieza) }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $apartamentoLimpieza->id }}">

                        @foreach ($checklists as $checklist)
                        @php
                        // Normaliza el nombre para usar como identificador
                        $nombreHabitacion = strtolower(str_replace(' ', '_', $checklist->nombre));
                        // Quitar tildes manualmente
                        $nombreHabitacion = strtr($nombreHabitacion, [
                            'á' => 'a', 'é' => 'e', 'í' => 'i',
                            'ó' => 'o', 'ú' => 'u',
                            'Á' => 'a', 'É' => 'e', 'Í' => 'i',
                            'Ó' => 'o', 'Ú' => 'u',
                            'ñ' => 'n', 'Ñ' => 'n',
                        ]);

                        // Lista de nombres que deben excluir cámara (sin acentos y en minúscula)
                        $excluirCamara = in_array($nombreHabitacion, ['canape', 'armario', 'perchero', 'amenities', 'ascensor', 'escalera']);
                    @endphp


                            <div class="fila">
                                <div class="header_sub mb-3">
                                    <div class="row bg-color-quinto m-1 text-white align-items-center">
                                        <div class="col-8">
                                            <h3 class="titulo mb-0">{{ strtoupper($checklist->nombre) }}</h3>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-check form-switch mt-2 mb-2 d-flex w-100 justify-content-evenly">
                                                @php
                                                $isChecklistChecked = isset($checklistsExistentes[$checklist->id]) && $checklistsExistentes[$checklist->id] == 1;
                                                @endphp
                                                <input
                                                {{ isset($checklistsExistentes[$checklist->id]) && $checklistsExistentes[$checklist->id] == 1 ? 'checked' : '' }}
                                                class="form-check-input checklist-toggle"
                                                value="1"
                                                name="checklist[{{ $checklist->id }}]"
                                                type="checkbox"
                                                data-habitacion="{{ $checklist->id }}"
                                                >
                                            <label class="form-check-label"></label>
                                                @if (!$excluirCamara)
                                                @php
                                                    $fotoRuta = route('fotos.' . $nombreHabitacion, [
                                                        'id' => $apartamentoLimpieza->id,
                                                        'cat' => $checklist->id,
                                                    ]);
                                                @endphp
                                                        <a id="camara{{ $checklist->id }}" href="{{ $fotoRuta }}" class="btn btn-foto" style="display: none">
                                                        <i class="fa-solid fa-camera"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="content-check mx-2">
                                    @foreach ($checklist->items as $item)
                                        <div class="form-check form-switch mt-2">
                                            <input type="hidden" name="items[{{ $item->id }}]" value="0">
                                            @php
                                                $isChecked = isset($itemsExistentes[$item->id]) && $itemsExistentes[$item->id] == 1;
                                            @endphp
                                            <input class="form-check-input" type="checkbox"
                                                id="item_{{ $item->id }}"
                                                name="items[{{ $item->id }}]"
                                                value="1"
                                                {{ $isChecked ? 'checked' : '' }}>

                                            <label class="form-check-label" for="item_{{ $item->id }}">{{ $item->nombre }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <hr>
                            </div>
                        @endforeach

                        <div class="fila">
                            <div class="content-check mx-2">
                                <textarea name="observacion" id="observacion" cols="30" rows="6" placeholder="Escriba alguna observacion..." style="width: 100%">{{ $apartamentoLimpieza->observacion }}</textarea>
                            </div>
                        </div>
                        <div class="fila mt-2">
                            <button type="submit" class="btn btn-guardar w-100 text-uppercase fw-bold">Guardar</button>
                        </div>
                    </form>

                    <form id="formFinalizar" action="{{ route('gestion.finalizar', $apartamentoLimpieza->id) }}" method="POST">
                        @csrf
                    </form>
                    <button type="button" class="btn btn-terminar w-100 mt-2 text-uppercase fw-bold" onclick="enviarFormulario('finalizar')">Terminar</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function enviarFormulario() {
        document.getElementById('formFinalizar').submit();
    }

    $(document).ready(function () {
    console.log('Limpieza de Apartamento by Hawkins.')

    $('.checklist-toggle').each(function () {
        const habitacion = $(this).data('habitacion');
        const selectorCamara = '#camara' + habitacion;

        if ($(this).is(':checked')) {
            $(selectorCamara).show();
        }
    });

    $('.checklist-toggle').on('change', function () {
        const habitacion = $(this).data('habitacion');
        const selectorCamara = '#camara' + habitacion;

        if ($(this).is(':checked')) {
            $(selectorCamara).show();
        } else {
            $(selectorCamara).hide();
        }
    });
});

</script>
@endsection
