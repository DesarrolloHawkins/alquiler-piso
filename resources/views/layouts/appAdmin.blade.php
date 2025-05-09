<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
        <link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
        <link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
        <link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
        <link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
        <link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
        <link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
        <link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
        <link rel="icon" type="image/png" sizes="192x192"  href="/android-icon-192x192.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="manifest" href="/manifest.json">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
        <meta name="theme-color" content="#ffffff">
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - @yield('title')</title>

        <!-- Fonts -->
        <link rel="dns-prefetch" href="//fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

        @yield('scriptHead')

        <!-- Scripts -->
        <script>
            // Tiempo de sesión en milisegundos
            var sessionLifetime = {{ config('session.lifetime') * 60000 }};
        </script>
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                @if(session('swal_success'))
                    Swal.fire({
                        title: "¡Éxito!",
                        text: "{{ session('swal_success') }}",
                        icon: "success",
                        confirmButtonText: "Aceptar"
                    });
                @endif

                @if(session('swal_error'))
                    Swal.fire({
                        title: "Error",
                        text: "{{ session('swal_error') }}",
                        icon: "error",
                        confirmButtonText: "Aceptar"
                    });
                @endif
            });
        </script>
        <style>
            /* Aquí va el estilo que ya tienes... */
            /* total width */
            body::-webkit-scrollbar {
                background-color: #fff;
                width: 12px;
            }

            /* background of the scrollbar except button or resizer */
            body::-webkit-scrollbar-track {
                background-color: #fff;
            }

            /* scrollbar itself */
            body::-webkit-scrollbar-thumb {
                background-color: #babac0;
                border-radius: 16px;
                border: 3px solid #fff;
            }

            /* set button(top and bottom of the scrollbar) */
            body::-webkit-scrollbar-button {
                display:none;
            }

            /* ...fin de los estilos scroll */
        </style>

    </head>
    <body>
        <div id="app">
            <div class="container-fluid">
                <div class="row">
                    <!-- Sidebar -->
                    <nav id="mainNavbar" class="navbar navbar-expand-lg navbar-dark bg-color-primero px-4">
                        <a class="navbar-brand" href="/">
                            <img src="{{ asset('logo-hawkins-suite_white.png') }}" alt="Logo" height="40">
                        </a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                            <span class="navbar-toggler-icon"></span>
                        </button>

                        <div class="collapse navbar-collapse" id="mainNavbar">
                            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                                <li class="nav-item"><a class="nav-link {{ request()->is('dashboard*') ? 'active' : '' }}" href="{{ route('dashboard.index') }}">Dashboard</a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->is('clientes*') ? 'active' : '' }}" href="{{ route('clientes.index') }}">Clientes</a></li>

                                <!-- Channex -->
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('channex*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                                        Channex
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('channex.ratePlans.index') }}">Rate Plans</a></li>
                                        <li><a class="dropdown-item" href="{{ route('channex.roomTypes.index') }}">Room Types</a></li>
                                        <li><a class="dropdown-item" href="{{ route('channex.propiedad.index') }}">Property</a></li>
                                        <li><a class="dropdown-item" href="{{ route('ari.index') }}">Update Rate & Availability</a></li>
                                        <li><a class="dropdown-item" href="{{ route('channex.channel.index') }}">Channel</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="document.getElementById('fullSyncBtn')?.click()">Full Sync</a></li>
                                    </ul>
                                </li>

                                <!-- Reservas -->
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('reservas*') || request()->is('tabla-reservas*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                                        Reservas
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('reservas.index') }}">Ver Reservas</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.tablaReservas.index') }}">Tabla de Reservas</a></li>
                                    </ul>
                                </li>

                                <!-- Gestión -->
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('gestion*') || request()->is('jornada*') || request()->is('apartamentos*') || request()->is('checklists*') || request()->is('holidays*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                                        Gestión
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('apartamentos.admin.index') }}">Apartamentos</a></li>
                                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.empleados.index') }}">Empleados</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.jornada.index') }}">Jornada</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.edificios.index') }}">Edificios</a></li>
                                        <li><a class="dropdown-item" href="{{ route('gestion.index') }}" target="_blank">Limpieza</a></li>
                                        <li><a class="dropdown-item" href="{{ route('holiday.admin.index') }}" target="_blank">Vacaciones</a></li>
                                        <li><a class="dropdown-item" href="{{ route('holiday.admin.petitions') }}" target="_blank">Gestion Vacaciones</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.limpiezaFondo.index') }}">Limpieza Fondo</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.checklists.index') }}">Categorias de Limpieza</a></li>
                                    </ul>
                                </li>

                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('diario-caja*', 'ingresos*', 'gastos*', 'facturas*', 'bancos*', 'upload-files*', 'presupuestos*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                                        Tesorería
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('admin.diarioCaja.index') }}">Diario de Caja</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.estadosDiario.index') }}">Estados del Diario</a></li>
                                        <li><a class="dropdown-item" href="{{ route('presupuestos.index') }}">Presupuestos</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.facturas.index') }}">Facturas</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.ingresos.index') }}">Ingresos</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.categoriaIngresos.index') }}">Categoría de Ingresos</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.gastos.index') }}">Gastos</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.categoriaGastos.index') }}">Categoría de Gastos</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.bancos.index') }}">Bancos</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.upload.files') }}">Subida Ficheros</a></li>
                                        <li><a class="dropdown-item" href="{{ route('metalicos.index') }}">Metálicos</a></li>
                                    </ul>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('plan-contable*', 'cuentas-contables*', 'sub-cuentas-contables*', 'sub-cuentas-hijas-contables*', 'grupo-contable*', 'sub-grupo-contable*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                                        Contabilidad
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('admin.planContable.index') }}">Plan General Contable</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.grupoContabilidad.index') }}">Grupos Contables</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.subGrupoContabilidad.index') }}">Sub-Grupos Contables</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.cuentasContables.index') }}">Cuentas Contables</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.subCuentasContables.index') }}">Sub-Cuentas Contables</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.subCuentasHijaContables.index') }}">Sub-Cuentas Hijas</a></li>
                                    </ul>
                                </li>

                                <!-- Emails -->
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('emails*') || request()->is('status-mail*') || request()->is('category-email*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                                        Emails
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('admin.statusMail.index') }}">Status de Emails</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.categoriaEmail.index') }}">Categorías de Emails</a></li>
                                    </ul>
                                </li>

                                <!-- Emails -->
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->is('whatsapp*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                                        Plataforma de Mensajes
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('templates.index') }}">Plantilla de Mesanjes</a></li>
                                        <li><a target="_blank" class="dropdown-item" href="{{ route('whatsapp.mensajes') }}">Conversaciones</a></li>
                                    </ul>
                                </li>

                                <!-- Otros -->
                                {{-- <li class="nav-item"><a class="nav-link" href="#">Logs</a></li> --}}
                                <li class="nav-item"><a class="nav-link" href="{{ route('configuracion.index') }}">Configuración</a></li>
                            </ul>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-flex">
                                @csrf
                                <button class="btn btn-outline-light" type="submit">Salir</button>
                            </form>
                        </div>
                    </nav>

                    {{-- Fondo de Sidebar Active --}}
                    <div id="fondoActiveSidebar" class="fondo-active-sidebar hidden">

                    </div>
                    <!-- Botón de toggle para dispositivos pequeños -->
                    <div class="d-md-none p-3" style="position: absolute;z-index: 850;right: 0;bottom: 0;text-align: right;">
                        <button class="btn bg-color-tercero" id="toggleSidebar" style="border-radius: 50%;height: 75px;width: 75px;">
                            <i class="fa-solid fa-bars" style="font-size: 28px;"></i>
                        </button>
                    </div>
                    {{-- Content --}}
                    <div class="col p-4 contenedor-principal">
                        <div class="nav-top">
                            <h3 class="fw-bold font-titulo">
                                @yield('tituloSeccion')
                            </h3>
                        </div>
                        <main class="contendor">
                            @yield('content')
                        </main>
                    </div>
                </div>
            </div> <!-- container-fluid -->
        </div> <!-- END APP -->

        <style>


            /* Sidebar animado */
           /* Sidebar por defecto (visible en pantallas grandes) */
            #sidebar {
                transform: translateX(0); /* Visible por defecto */
                position: relative;
                z-index: 800;
                transition: transform 0.3s ease-in-out;
            }

            /* Sidebar visible */
            #sidebar.active {
                transform: translateX(0);
            }

            /* Fondo de overlay */
            .fondo-active-sidebar {
                background-color: rgba(0, 0, 0, 0.7); /* Fondo oscuro con opacidad */
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 700;
                display: none; /* Oculto por defecto */
                opacity: 0; /* Transparente */
                transition: opacity 0.3s ease-in-out; /* Transición de opacidad */
            }

            /* Fondo visible */
            .fondo-active-sidebar.show {
                display: block; /* Mostrar el fondo */
                opacity: 1; /* Totalmente visible */
            }


            /* Ocultar sidebar en dispositivos pequeños */
            @media (max-width: 768px) {
                #sidebar {
                    transform: translateX(-115%); /* Escondido por defecto */
                    position: absolute;
                    z-index: 800;
                }
                .contenedor-principal {
                    margin-top: 0
                }
                .btn {
                    width: 100%;
                }
            }

            /* Mostrar el sidebar cuando está activo */
            /* #sidebar.active {
                transform: translateX(0);
            } */

        </style>
        {{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}

        {{-- Scripts --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <!-- DataTables CSS y JS -->
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const fullSyncBtn = document.getElementById("fullSyncBtn");
                if (fullSyncBtn) {
                    fullSyncBtn.addEventListener("click", function () {
                        Swal.fire({
                            title: "¿Estás seguro?",
                            text: "Esto sincronizará todas las disponibilidades con Channex.",
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonText: "Sí, sincronizar",
                            cancelButtonText: "Cancelar"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fetch("{{ route('ari.fullSync') }}", {
                                    method: "POST",
                                    headers: {
                                        "X-CSRF-TOKEN": document.querySelector('meta[name=\"csrf-token\"]').getAttribute("content"),
                                        "Content-Type": "application/json"
                                    },
                                    body: JSON.stringify({})
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire("¡Éxito!", data.message, "success");
                                    } else {
                                        Swal.fire("Error", data.message, "error");
                                    }
                                })
                                .catch(error => {
                                    Swal.fire("Error", "Ocurrió un error inesperado.", "error");
                                });
                            }
                        });
                    });
                }

            });
        </script>

        {{-- Script cerrar sesion con alerta --}}
        <script>
            var sessionLifetime = {{ config('session.lifetime') * 60000 }};
            var alertShown = false; // Flag para controlar si la alerta se ha mostrado

            function startSessionTimer() {
                window.sessionTimeout = setTimeout(function() {
                    if (!alertShown) { // Verifica si la alerta no se ha mostrado aún
                        alertShown = true; // Marca que la alerta se va a mostrar
                        // alert('Tu sesión ha expirado. Serás redirigido a la página de login.');
                        window.location.href = '/login'; // Redirecciona después de que el usuario acepte la alerta
                    }
                }, sessionLifetime);
            }

            function resetSessionTimer() {
                clearTimeout(window.sessionTimeout);
                alertShown = false; // Restablece la alerta al reiniciar el temporizador
                startSessionTimer();
            }

            // Inicia el temporizador de sesión
            startSessionTimer();

            // Reinicia el temporizador con cualquier interacción del usuario
            document.addEventListener('mousemove', resetSessionTimer);
            document.addEventListener('keypress', resetSessionTimer);
            document.addEventListener('click', resetSessionTimer);


            document.addEventListener('DOMContentLoaded', function () {
                const toggleSidebarButton = document.getElementById('toggleSidebar');
                const sidebar = document.getElementById('sidebar');
                const fondo = document.getElementById('fondoActiveSidebar');

                // Mostrar/Ocultar Sidebar y Fondo
                toggleSidebarButton.addEventListener('click', function () {
                    sidebar.classList.toggle('active'); // Alternar clase para mostrar/ocultar el sidebar
                    fondo.classList.toggle('show'); // Alternar clase para fade del fondo
                });

                // Cerrar sidebar al hacer clic en el fondo
                fondo.addEventListener('click', function () {
                    sidebar.classList.remove('active');
                    fondo.classList.remove('show');
                });
            });


        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const collapseButtons = document.querySelectorAll('[data-info="button"]');

                collapseButtons.forEach(button => {
                    button.addEventListener('click', function(e) {
                        // e.preventDefault()
                        const targetId = this.getAttribute('data-bs-target');
                        const target = document.querySelector(targetId);
                        console.log(target.classList)

                        if (!target) {
                            console.error(`No se encontró el submenú con el ID: ${targetId}`);
                            return;
                        }
                        console.log(target.classList)

                        // Verificar si el submenú actual está colapsado o expandido
                        const isExpanded = target.classList.contains('show');
                        console.log(isExpanded)

                        // Cerrar todos los submenús
                        collapseButtons.forEach(btn => {
                            const btnTargetId = btn.getAttribute('data-bs-target');
                            const btnTarget = document.querySelector(btnTargetId);

                            if (btnTarget && btnTarget !== target) {
                                btnTarget.classList.remove('show');
                                btn.setAttribute('aria-expanded', 'false');
                            }
                        });

                        // Alternar el submenú actual
                        if (isExpanded) {
                            target.classList.remove('show');
                            this.setAttribute('aria-expanded', 'false');
                        } else {
                            target.classList.add('show');
                            this.setAttribute('aria-expanded', 'true');
                        }
                    });
                });
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const navbar = document.getElementById('mainNavbar');
                let lastScrollTop = 0;

                window.addEventListener('scroll', function () {
                    const scrollTop = window.scrollY || document.documentElement.scrollTop;

                    if (scrollTop > 10 && !navbar.classList.contains('fixed-top')) {
                        navbar.classList.add('fixed-top', 'shadow-sm');
                        // navbar.classList.remove('bg-color-primero');
                        // navbar.classList.add('bg-dark');
                    } else if (scrollTop <= 10 && navbar.classList.contains('fixed-top')) {
                        navbar.classList.remove('fixed-top', 'shadow-sm');
                        // navbar.classList.remove('bg-dark');
                        // navbar.classList.add('bg-color-primero');
                    }

                    lastScrollTop = scrollTop;
                });
            });
        </script>


        @yield('scripts')
        @include('sweetalert::alert')

    </body>
</html>
