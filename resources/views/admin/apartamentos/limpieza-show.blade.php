@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <h2 class="mb-3">{{ __('Resumen de Limpieza del Apartamento') }}</h2>
    <hr class="mb-4">

    @foreach ($checklists as $checklist)
        @php
            $nombreHabitacion = strtolower(str_replace(' ', '_', $checklist->nombre));
            $nombreHabitacion = strtolower(str_replace('Ñ', 'ni', $nombreHabitacion));
            $excluirCamara = in_array(strtolower($checklist->nombre), ['perchero', 'amenities', 'ascensor', 'escalera', 'salÓn']);
        @endphp
        <div class="fila">
            <div class="header_sub mb-3">
                <div class="row bg-color-quinto m-1 text-white align-items-center">
                    <div class="col-8">
                        <h3 class="titulo mb-0">{{ strtoupper($checklist->nombre) }}</h3>
                    </div>
                </div>
            </
            <div class="content-check mx-2">
                @foreach ($checklist->items as $item)
                    <div class="form-check form-switch mt-2">
                        <input type="hidden" name="items[{{ $item->id }}]" value="0">
                        <input class="form-check-input" type="checkbox" disabled id="item_{{ $item->id }}" name="items[{{ $item->id }}]" value="1">
                        <label class="form-check-label" for="item_{{ $item->id }}">{{ $item->nombre }}</label>
                    </div>
                @endforeach
            </div>
            <hr>
        </div>
        @endforeach


    @if($apartamento->observacion)
        <div class="row mt-4">
            <div class="col-md-12">
                <h4 class="titulo mb-0">OBSERVACIONES</h4>
                <div class="form-check ps-0 mt-2">
                    <textarea class="form-control" cols="30" rows="5" readonly>{{ $apartamento->observacion }}</textarea>
                </div>
            </div>
        </div>
        <hr>
    @endif

    @if($fotos && $fotos->count())
        <div class="row mt-4">
            <div class="col-md-12">
                <h4 class="titulo mb-0">IMÁGENES</h4>
            </div>

            @foreach ($fotos as $foto)
                <div class="col-md-3 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-header text-center fw-bold">
                            {{ $foto->categoria->nombre ?? 'Sin categoría' }}
                        </div>
                        <img src="{{ asset($foto->url) }}" class="card-img-top" alt="foto">
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection


@include('sweetalert::alert')

@section('scripts')
<script>
    // document.addEventListener('DOMContentLoaded', function () {
    //     // Verificar si SweetAlert2 está definido
    //     if (typeof Swal === 'undefined') {
    //         console.error('SweetAlert2 is not loaded');
    //         return;
    //     }

    //     // Botones de eliminar
    //     const deleteButtons = document.querySelectorAll('.delete-btn');
    //     deleteButtons.forEach(button => {
    //         button.addEventListener('click', function (event) {
    //             event.preventDefault();
    //             const form = this.closest('form');
    //             Swal.fire({
    //                 title: '¿Estás seguro?',
    //                 text: "¡No podrás revertir esto!",
    //                 icon: 'warning',
    //                 showCancelButton: true,
    //                 confirmButtonColor: '#3085d6',
    //                 cancelButtonColor: '#d33',
    //                 confirmButtonText: 'Sí, eliminar!',
    //                 cancelButtonText: 'Cancelar'
    //             }).then((result) => {
    //                 if (result.isConfirmed) {
    //                     form.submit();
    //                 }
    //             });
    //         });
    //     });
    // });
</script>
@endsection

