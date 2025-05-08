@extends('layouts.appAdmin')

@section('content')
<style>
    .inactive-sort {
        color: #ffffff;
        text-decoration: none;
    }
    .active-sort {
        color: #ffa3fa;
        font-weight: bold;
        text-decoration: none;
    }
</style>
<div class="container-fluid">
    <div class="d-flex flex-colum mb-3">
        <h2 class="mb-0 me-3 encabezado_top">{{ __('Plantillas de WhatsApp') }}</h2>
        <a href="{{ route('templates.create') }}" class="btn bg-color-sexto text-uppercase me-2">
            <i class="fa-solid fa-plus me-2"></i>
            Crear plantilla
        </a>
        <a href="{{ route('templates.sync') }}" class="btn bg-color-tercero text-uppercase">
            <i class="fa-solid fa-arrows-rotate me-2"></i>
            Sincronizar plantillas
        </a>
    </div>

    <hr class="mb-3">

    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @elseif ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <table class="table table-striped table-hover">
                <thead>
                    <tr class="bg-color-primero-table">
                        <th scope="col">Nombre</th>
                        <th scope="col">Idioma</th>
                        <th scope="col">Categoría</th>
                        <th scope="col">Estado</th>
                        <th scope="col" style="width: 240px;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $template)
                        <tr>
                            <td>{{ $template->name }}</td>
                            <td>{{ $template->language }}</td>
                            <td>{{ $template->category }}</td>
                            <td>{{ $template->status }}</td>
                            <td>
                                <a href="{{ route('templates.show', $template) }}" class="btn bg-color-cuarto text-black">Ver</a>
                                <a href="{{ route('templates.edit', $template) }}" class="btn bg-color-quinto">Editar</a>
                                <a href="{{ route('templates.checkStatus', $template) }}" class="btn btn-secondary">Actualizar estado</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Si usas paginación en templates: --}}
            {{-- {!! $templates->links('pagination::bootstrap-5') !!} --}}
        </div>
    </div>
</div>
@endsection
