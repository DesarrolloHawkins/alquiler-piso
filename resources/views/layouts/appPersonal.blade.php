<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-info-subtle shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                @yield('bienvenido')

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

        <main class="py-4">
            @yield('content')
        </main>
        <footer class="nav-bar-mobile bg-dark-subtle p-2">
            <div class="row px-3">
                <div class="col-3 ">
                   <a href="#" class="text-decoration-none text-center boton rounded bg-body-tertiary d-block h-100 w-100">
                        <div class="icon fs-1 m-0 text-info">
                            <i class="fa-solid fa-house "></i>
                        </div>
                        <div class="texto fs-6 p-0 text-muted">
                            Inicio
                        </div>
                   </a>
                </div>
                <div class="col-3">
                    <a href="#" class="text-decoration-none text-center boton rounded bg-body-tertiary d-block h-100 w-100">
                         <div class="icon fs-1 m-0 text-success">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                         </div>
                         <div class="texto fs-6 p-0 text-muted">
                            Historial
                         </div>
                    </a>
                </div>
                <div class="col-3">
                    <a href="#" class="text-decoration-none text-center boton rounded bg-body-tertiary d-block h-100 w-100">
                         <div class="icon fs-1 m-0 text-warning">
                            <i class="fa-solid fa-question"></i>
                         </div>
                         <div class="texto fs-6 p-0 text-muted">
                            Faq
                         </div>
                    </a>
                </div>
                <div class="col-3">
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();" class="text-decoration-none text-center boton rounded bg-body-tertiary d-block h-100 w-100">
                         <div class="icon fs-1 m-0 text-danger">
                            <i class="fa-solid fa-right-from-bracket"></i>
                         </div>
                         <div class="texto fs-6 p-0 text-muted">
                            Salir
                         </div>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>

            </div>
        </footer>
    </div>
</body>
</html>
