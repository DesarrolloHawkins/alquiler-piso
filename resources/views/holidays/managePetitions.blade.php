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
                        <p class="text-subtitle text-muted text-center">Gestión de petición</p>
                    </div>
                </div>
                <div class="col-sm-12 col-md-12">
                    <nav aria-label="breadcrumb" class="breadcrumb-header text-center">
                        <ol class="breadcrumb mb-0" style="width: 100%; justify-content: center;">
                            <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{route('holiday.admin.petitions')}}">Gestión de vacaciones</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Petición</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section pt-4">
            <form action="{{route('holiday.admin.acceptHolidays', $holidayPetition->id)}}" method="POST" class="form-primary">
                @csrf
            <div class="row">
                <div class="col-9">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    @if($holidayPetition)
                                        <div class="col-12 col-sm-12 col-md-6 col-lg-12 col-xl-12 form-group">
                                            <label>Fecha de vacaciones pedidas:</label>
                                            <p><strong>{{ Carbon\Carbon::parse($holidayPetition->from)->format('d/m/Y') . ' - ' . Carbon\Carbon::parse($holidayPetition->to)->format('d/m/Y')}}</strong> </p>
                                        </div>
                                        <div class="col-12 col-sm-12 col-md-6 col-lg-12 col-xl-12 form-group">
                                            <label>Fecha de creación la petición:</label>
                                            <p><strong>{{ Carbon\Carbon::parse($holidayPetition->created_at)->format('d/m/Y H:i:s') }}</strong></p>
                                        </div>
                                        <div class="col-12 col-sm-12 col-md-6 col-lg-12 col-xl-12 form-group">
                                            @if($holidayPetition->half_day)
                                                <label>Días en total:</label>
                                                <p><strong>Medio día</strong></p>
                                            @elseif($holidayPetition->total_days == 1)
                                                <label>Días en total:</label>
                                                <p><strong>1 día</strong></p>
                                            @else
                                                <label>Días en total:&nbsp;&nbsp;</label>
                                                <p>{{ $holidayPetition->total_days .' '}}días</strong></p>
                                            @endif
                                        </div>
                                        <input type="hidden" name="id" value="{{$holidayPetition->id}}" />
                                        <input type="hidden" name="admin_user_id" value="{{$holidayPetition->admin_user_id}}" />
                                        <input type="hidden" name="holidays_status_id" value="{{$holidayPetition->holidays_status_id}}" />
                                        <input type="hidden" name="from" value="{{$holidayPetition->from}}" />
                                        <input type="hidden" name="to" value="{{$holidayPetition->to}}" />
                                        <input type="hidden" name="total_days" value="{{$holidayPetition->total_days}}" />
                                        <input type="hidden" name="half_day" value="{{$holidayPetition->half_day}}" />
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="card-body p-3">
                            <div class="card-title">
                                Acciones
                                <hr>
                            </div>
                            <div class="card-body">
                                @if($holidayPetition->holidays_status_id != 1)
                                    <button class="btn btn-success btn-block">
                                        Aceptar
                                    </button>
                                @endif
                                @if($holidayPetition->holidays_status_id != 2)
                                    <button type="button" id="denyHolidays" data-id="{{$holidayPetition->id}}" class="btn btn-outline-danger btn-block">
                                        Denegar
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            <form>
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

