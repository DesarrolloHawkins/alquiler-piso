@extends('layouts.appAdmin')

@section('titulo', 'Mis Vacaciones')

@section('css')
    <link rel="stylesheet" href="assets/vendors/simple-datatables/style.css">
@endsection

@section('content')

    <div class="page-heading" style="box-shadow: none !important" >
        {{-- Titulos --}}
        <div class="page-title">
            <div class="row justify-content-center">
                <div class="col-sm-12 col-md-12">
                    <div class="col-auto">
                        <h3 class="fs-3 text-center"><i class="fa-solid fa-umbrella-beach me-3"></i>Vacaciones</h3>
                        <p class="text-subtitle text-muted text-center">Vacaciones disponibles </p>
                    </div>
                </div>
                <div class="col-sm-12 col-md-12">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            {{-- <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li> --}}
                            <li class="breadcrumb-item active" aria-current="page">Vacaciones</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section pt-4">
            <div class="card">
                <div class="card-body">
                    <div>
                        <form method="GET" action="{{ route('holiday.admin.index') }}" class="mb-4">
                            <div class="row">
                                <!-- Selector de número de elementos por página -->
                                <div class="col-md-3 col-sm-12">
                                    <label for="perPage">Nº</label>
                                    <select name="perPage" id="perPage" class="form-select" onchange="this.form.submit()">
                                        <option value="10" {{ request('perPage', 10) == 10 ? 'selected' : '' }}>10</option>
                                        <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                                        <option value="all" {{ request('perPage') == 'all' ? 'selected' : '' }}>Todo</option>
                                    </select>
                                </div>

                                <!-- Campo de búsqueda -->
                                <div class="col-md-9 col-sm-12">
                                    <label for="buscar">Buscar</label>
                                    <input type="text" name="buscar" id="buscar" class="form-control"
                                           value="{{ request('buscar') }}" placeholder="Escriba el nombre del usuario...">
                                </div>
                            </div>
                        </form>


                        @if ($holidays->count())
                            <div class="table-responsive">
                                    <table class="table table-hover">
                                    <thead class="header-table">
                                        <tr>
                                            <th class="px-3" style="font-size:0.75rem">USUARIO</th>
                                            <th class="" style="font-size:0.75rem">DIAS DISPONIBLES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($holidays as $holiday)
                                            <tr >
                                                <td>{{$holiday->adminUser ? optional($holiday->adminUser)->name.' '.optional($holiday->adminUser)->surname : 'Usuario Borrado'}}</td>
                                                <td>{{ number_format($holiday->quantity, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @if($perPage !== 'all')
                                    {{ $holidays->links() }}
                                @endif
                            </div>
                        @else
                            <div class="text-center py-4">
                                <h3 class="text-center fs-3">No se encontraron registros de <strong>Vacaciones</strong></h3>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    @include('partials.toast')
<script>

    $('#denyHolidays').on('click', function(e){
        e.preventDefault();
        let id = $(this).data('id'); // Usa $(this) para obtener el atributo data-id
        botonAceptar(id);
    })

    function botonAceptar(id){
        // Salta la alerta para confirmar la eliminacion
        Swal.fire({
            title: "¿Va a rechazar ésta petición de vacaciones.?",
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: "Rechazar petición",
            cancelButtonText: "Cancelar",
            // denyButtonText: `No Borrar`
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                // Llamamos a la funcion para borrar el usuario
                $.when( denyHolidays(id) ).then(function( data, textStatus, jqXHR ) {
                    console.log(data)
                    if (!data.status) {
                        // Si recibimos algun error
                        Toast.fire({
                            icon: "error",
                            title: data.mensaje
                        })
                    } else {
                        // Todo a ido bien
                        Toast.fire({
                            icon: "success",
                            title: data.mensaje
                        })
                        .then(() => {
                            window.location.href = "{{ route('holiday.admin.petitions') }}";
                        })
                    }
                });
            }
        });
    }

    function denyHolidays(id) {
        // Ruta de la peticion
        const url = '{{route("holiday.admin.denyHolidays")}}';
        // Peticion
        return $.ajax({
            type: "POST",
            url: url,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            data: {
                'id': id,
            },
            dataType: "json"
        });
    }
</script>

@endsection

