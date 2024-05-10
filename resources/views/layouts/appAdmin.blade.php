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
    @yield('scriptHead')

    <!-- Scripts -->
    <script>
        // Tiempo de sesión en milisegundos
        var sessionLifetime = {{ config('session.lifetime') * 60000 }};
    </script>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .font-titulo{
            font-family:Arial, Helvetica, sans-serif;
        }
        .nav-pills .nav-link{
            color: #3B3F64
        }
        .nav-pills .nav-link.active, .nav-pills .show > .nav-link{
            background-color: #0F1739;
            color: white;
            border-color: #0F1739;
        }
        .nav-pills .nav-link:hover{
            background-color: #3B3F64;
            color: white !important;
            border-color: #3B3F64;
        }
         /* Estilos para mantener fijo el sidebar y permitir el scroll en contenedor principal */
    .sidebar {
        //position: fixed; /* Hace el sidebar fijo */
        // width: 20%; /* Ajusta el ancho del sidebar */
        height: 100vh; /* Altura completa de la ventana */
        overflow-y: auto; /* Permite scroll solo si es necesario */
    }
    .contenedor-principal {
        //margin-left: 20%; /* Desplaza el contenedor principal para no solaparse con el sidebar */
        //width: 80%; /* Ajusta el ancho del contenedor principal */
        overflow-y: auto; /* Permite scroll en el contenedor principal */
        height: 100vh; /* Altura completa de la ventana */
    }

    </style>

</head>
<body>
    <div id="app">
        <div class="container-fluid h-100">
            <div class="row h-100">
                <div class="col-2 bg-light sidebar p-3" style="height: 100vh; overflow-y: auto;"> <!-- Sidebar -->
                    <div class="d-flex flex-column flex-shrink-0 text-white h-100" style="">
                        <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none w-100">
                          {{-- <svg class="bi me-2" width="40" height="32"><use xlink:href="#bootstrap"></use></svg> --}}
                          <img src="{{asset('logo_small_azul.png')}}" alt="" style="" class="img-fluid d-block m-auto mt-1">

                        </a>
                        <hr>
                        <ul class="nav nav-pills flex-column mb-auto">
                          <li class="nav-item">
                            <a href="{{route('dashboard.index')}}" class="nav-link fs-5 {{ request()->is('dashboard', 'dashboard/*') ? 'active' : '' }}" aria-current="page">
                                <i class="fa-solid fa-chart-area me-2"></i>
                               Dashboard
                            </a>
                          </li>
                          <li>
                            <a href="{{route('clientes.index')}}" class="nav-link fs-5 {{ request()->is('clientes', 'clientes/*') ? 'active' : '' }}" aria-current="page">
                                <i class="fa-solid fa-users me-2"></i>
                              Clientes
                            </a>
                          </li>
                          <li>
                            <a href="{{route('reservas.index')}}" class="nav-link fs-5 {{ request()->is('reservas', 'reservas/*') ? 'active' : '' }}" aria-current="page">
                                <i class="fa-solid fa-calendar-days me-2"></i>
                              Reservas
                            </a>
                          </li>
                          <li>
                            <a href="#" class="nav-link fs-5" aria-current="page">
                                <i class="fa-solid fa-id-card me-2"></i>
                              DNI
                            </a>
                          </li>
                          <li>
                            <a href="#" class="nav-link fs-5" aria-current="page">
                                <i class="fa-solid fa-user-astronaut me-2"></i>
                              Asistente
                            </a>
                          </li>
                          <li>
                            <a href="{{route('apartamentos.admin.index')}}" class="nav-link fs-5 {{ request()->is('apartamentos', 'apartamentos/*') ? 'active' : '' }}" aria-current="page">
                                <i class="fa-solid fa-file-lines me-2"></i>
                              Apartamentos
                            </a>
                          </li>
                          <li>
                            <a href="#" class="nav-link fs-5" aria-current="page">
                                <i class="fa-solid fa-file-lines me-2"></i>
                              Logs
                            </a>
                          </li>
                          <li>
                            <a href="#" class="nav-link fs-5" aria-current="page">
                                <i class="fa-solid fa-gear me-2"></i>
                              Configuracion
                            </a>
                          </li>
                        </ul>
                        <hr>
                        <div class="dropdown">
                            <a class="nav-link my-1 mx-3 text-black" href="{{ route('logout') }}"
                            onclick="event.preventDefault();
                                          document.getElementById('logout-form').submit();">
                             {{ __('Logout') }}
                         </a>

                         <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                             @csrf
                         </form>
                          {{-- <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://github.com/mdo.png" alt="" width="32" height="32" class="rounded-circle me-2">
                            <strong>mdo</strong>
                          </a> --}}
                          {{-- <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                            <li><a class="dropdown-item" href="#">New project...</a></li>
                            <li><a class="dropdown-item" href="#">Settings</a></li>
                            <li><a class="dropdown-item" href="#">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">Sign out</a></li>
                          </ul> --}}
                        </div>
                    </div>
                </div>
                <div class="col-10 p-4 contenedor-principal">
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
        </div>

        {{-- <footer class="nav-bar-mobile p-2" style="background-color: white">
            <div class="row px-3">
                <div class="col-3 ">
                   <a href="{{route('dashboard.index')}}" class="text-decoration-none text-center boton rounded d-block h-100 w-100">
                        <div class="icon fs-1 m-0 text-secondary" style="color: #b8c2d7 !important">
                            <i class="fa-solid fa-house "></i>
                        </div>

                   </a>
                </div>
                <div class="col-3">
                    <a href="#" class="text-decoration-none text-center boton rounded d-block h-100 w-100">
                         <div class="icon fs-1 m-0 ext-secondary" style="color: #b8c2d7 !important">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                         </div>

                    </a>
                </div>
                <div class="col-3">
                    <a href="#" class="text-decoration-none text-center boton rounded d-block h-100 w-100">
                         <div class="icon fs-1 m-0 ext-secondary" style="color: #b8c2d7 !important">
                            <i class="fa-solid fa-question"></i>
                         </div>

                    </a>
                </div>
                <div class="col-3">
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();" class="text-decoration-none text-center boton rounded d-block h-100 w-100">
                         <div class="icon fs-1 m-0 ext-secondary" style="color: #b8c2d7  !important">
                            <i class="fa-solid fa-right-from-bracket"></i>
                         </div>

                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>

            </div>
        </footer> --}}
    </div>
    {{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}

    {{-- Scripts --}}
    <script>
        var sessionLifetime = {{ config('session.lifetime') * 60000 }};
        var alertShown = false; // Flag para controlar si la alerta se ha mostrado

        function startSessionTimer() {
            window.sessionTimeout = setTimeout(function() {
                if (!alertShown) { // Verifica si la alerta no se ha mostrado aún
                    alertShown = true; // Marca que la alerta se va a mostrar
                    alert('Tu sesión ha expirado. Serás redirigido a la página de login.');
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
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    @include('sweetalert::alert')

    @yield('scripts')
</body>
</html>
