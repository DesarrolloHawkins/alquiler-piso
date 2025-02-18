@extends('layouts.appAdmin')

@section('titulo', 'Vacaciones')

@section('css')
    <link rel="stylesheet" href="assets/vendors/simple-datatables/style.css">
@endsection

@section('content')
    <style>#calendar {
        width: 100%;
        min-height: 600px;
        margin: 0 auto;
    }
    </style>
    <div class="page-heading" style="box-shadow: none !important" >

        {{-- Titulos --}}
        <div class="page-title">
            <div class="row justify-content-center">
                <div class="col-sm-12 col-md-12">
                    <h3 class="fs-3 text-center"><i class="fa-solid fa-umbrella-beach me-3"></i>Vacaciones</h3>
                    <p class="text-subtitle text-muted text-center">Listado de vacaciones</p>
                </div>
                <div class="col-sm-12 col-md-12">
                    <nav aria-label="breadcrumb" class="breadcrumb-header text-center">
                        <ol class="breadcrumb mb-0" style="width: 100%; justify-content: center;">
                            <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Gestión de vacaciones</li>
                        </ol>
                    </nav>

                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg col-md-6 mt-4">
                <div class="card2">
                    <div class="card-body ">
                        <div class="col-12" style="text-align: center">
                            <p for="">&nbsp;</p>
                            @if( $numberOfholidaysPetitions == 1)
                                <p for="pendant">Tienes <span style="color:red"><strong>{{ $numberOfholidaysPetitions }}</strong></span> petición pendiente de gestión</p>
                            @else
                                <p for="pendant">Tienes <span style="color:red"><strong>{{ $numberOfholidaysPetitions }}</strong></span> peticiones pendientes de gestión</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg col-md-6 mt-4">
                <div class="card2">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12" style="text-align: center">
                                <p for="status"><strong>ESTADOS</strong></p>
                                <p for="pendant">
                                    <i class="fa fa-square" aria-hidden="true" style="color:#FFDD9E"></i>&nbsp;&nbsp;PENDIENTE
                                    <i class="fa fa-square" aria-hidden="true" style="margin-left:5%;color:#C3EBC4"></i>&nbsp;&nbsp;ACEPTADA
                                    <i class="fa fa-square" aria-hidden="true" style="margin-left:5%;color:#FBC4C4"></i>&nbsp;&nbsp;DENEGADA
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
{{--
        @php
            use Jenssegers\Agent\Agent;

            $agent = new Agent();
        @endphp
        @if ($agent->isMobile())
            <div>
                <span>Es movil</span>
            </div>
            @livewire('holidays-table')

        @else

            @livewire('holidays-table')
        @endif --}}

        <div class="card mt-4 col-12" >
            <div class="card-body">
                <div id="calendar" class="p-4" style="margin-top: 0.75rem; margin-bottom: 0.75rem; overflow-y: auto; border-color:black; border-width: thin; border-radius: 20px;" >
                    <!-- Aquí se renderizarán las tareas según la vista seleccionada -->
                </div>
            </div>
        </div>
        <div class="modal fade" id="holidayModal" tabindex="-1" aria-labelledby="holidayModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="holidayModalLabel">Gestión de Vacaciones</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Usuario:</strong> <span id="holidayUser"></span></p>
                        <p><strong>Fecha de inicio:</strong> <span id="holidayStart"></span></p>
                        <p><strong>Fecha de fin:</strong> <span id="holidayEnd"></span></p>
                        <p><strong>Fecha de solicitud:</strong> <span id="holidayCreate"></span></p>
                        <input type="hidden" id="holidayId">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" onclick="acceptHoliday()">Aceptar</button>
                        <button type="button" class="btn btn-danger" onclick="denyHoliday()">Rechazar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('partials.toast')
    <script src="{{asset('assets/vendors/choices.js/choices.min.js')}}"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales-all.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
                initializeCalendar();
        });

        function getParameterByName(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }
        const eventDate = getParameterByName('fecha');

        function initializeCalendar() {
            var calendarEl = document.getElementById('calendar');
            if (!calendarEl) return; // Asegúrate de que el elemento existe

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialDate: eventDate || new Date(), // Usa la fecha del evento o la fecha actual
                initialView: 'dayGridMonth',
                locale: 'es',
                navLinks: true,
                contentHeight: 600,
                nowIndicator: true,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                events: @json($holydayEvents),
                eventClick: function(info) {
                    $('#holidayStart').text(info.event.start.toLocaleDateString());
                    $('#holidayEnd').text(info.event.extendedProps.endTrue ? new Date(info.event.extendedProps.endTrue).toLocaleDateString() : 'N/A');
                    $('#holidayUser').text(info.event.title ?? 'Usuario sin nombre');
                    $('#holidayCreate').text(info.event.extendedProps.created_at || 'N/A');
                    $('#holidayId').val(info.event.id);
                    $('#holidayModal').modal('show');
                }

            });
            calendar.render();
        }

        function acceptHoliday() {
            var holidayId = $('#holidayId').val();

            $.ajax({
                url: '/holidays/acceptHolidays',
                type: 'POST',
                data: {
                    id: holidayId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#holidayModal').modal('hide');
                    showToast(response.status === 'success' ? 'success' : 'error', response.mensaje || 'Error al aceptar la petición');

                },
                error: function(xhr) {
                    showToast('error', 'Ocurrió un error al aceptar la petición. Por favor, inténtalo de nuevo.');
                    console.error(xhr.responseText);
                }
            });
        }

        function denyHoliday() {
            var holidayId = $('#holidayId').val();

            $.ajax({
                url: '/holidays/denyHolidays',
                type: 'POST',
                data: {
                    id: holidayId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#holidayModal').modal('hide');
                    showToast(response.status === 'success' ? 'success' : 'error', response.mensaje || 'Error al denegar la petición');
                },
                error: function(xhr) {
                    showToast('error', 'Ocurrió un error al denegar la petición. Por favor, inténtalo de nuevo.');
                    console.error(xhr.responseText);
                }
            });
        }

        function showToast(icon, title) {
            Swal.fire({
                icon: icon,
                title: title,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                },
                didClose: () => {
                    if (icon === 'success') {
                        location.reload(); // Recarga solo si es éxito
                    }
                }
            });
        }
    </script>
@endsection
