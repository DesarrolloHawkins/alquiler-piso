<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title')</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Scripts -->

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-color-primero shadow-sm"
             style="
                    height: 52px;
                    border-radius: 0 0 45px 45px;
                    box-shadow: 1px 1px 1px black;
        ">
            <div class="container">
                @yield('volver')
                {{-- <a class="navbar-brand text-white text-center" href="{{ url('/') }}">
                    {{ config('app.name', 'Hawkins Suite') }}
                <img src="{{asset('logo-hawkins-suite_white.png')}}" alt="" class="img-fluid m-auto" style="width: 60%">

                </a> --}}
                <div class="pt-1 w-100 text-light">
                    @yield('bienvenido')
                    <h5 class="navbar-brand mb-0 text-center text-light w-100">Bienvenid@ <span class="text-uppercase">{{Auth::user()->name}}</span></h5>
                </div>

                {{-- <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button> --}}

                <div class="collapse navbar-collapse" id="navbarSupportedContent">

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown bg-light mt-2">
                                {{-- <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a> --}}

                                <a class="nav-link my-1 mx-3" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                                {{-- <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                </div> --}}
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4 contendor">
            @yield('content')
        </main>
        <footer class="nav-bar-mobile p-2" style="background-color: white">
            <div class="row px-3">
                <div class="col-3 ">
                   <a href="{{route('dashboard.index')}}" class="text-decoration-none text-center boton rounded d-block h-100 w-100">
                        <div class="icon fs-1 m-0 text-secondary" style="color: #b8c2d7 !important">
                            <i class="fa-solid fa-house "></i>
                        </div>
                        {{-- <div class="texto fs-6 p-0 text-muted">
                            Inicio
                        </div> --}}
                   </a>
                </div>
                <div class="col-3">
                    <a href="#" class="text-decoration-none text-center boton rounded d-block h-100 w-100">
                         <div class="icon fs-1 m-0 ext-secondary" style="color: #b8c2d7 !important">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                         </div>
                         {{-- <div class="texto fs-6 p-0 text-muted">
                            Historial
                         </div> --}}
                    </a>
                </div>
                <div class="col-3">
                    <a href="#" class="text-decoration-none text-center boton rounded d-block h-100 w-100">
                         <div class="icon fs-1 m-0 ext-secondary" style="color: #b8c2d7 !important">
                            <i class="fa-solid fa-question"></i>
                         </div>
                         {{-- <div class="texto fs-6 p-0 text-muted">
                            Faq
                         </div> --}}
                    </a>
                </div>
                <div class="col-3">
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();" class="text-decoration-none text-center boton rounded d-block h-100 w-100">
                         <div class="icon fs-1 m-0 ext-secondary" style="color:#b8c2d7 !important">
                            <i class="fa-solid fa-right-from-bracket"></i>
                         </div>
                         {{-- <div class="texto fs-6 p-0 text-muted">
                            Salir
                         </div> --}}
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>

            </div>
        </footer>
    </div>
    {{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}

    {{-- Scripts --}}

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    @include('sweetalert::alert')

    @yield('scripts')
</body>
</html>
