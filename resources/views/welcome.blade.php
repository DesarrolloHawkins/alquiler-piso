<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Hawkins Suite</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])

        @yield('scriptHead')
        <style>
            html, body {
                height: 100%;
            }
        </style>
    </head>
    <body class="bg-color-primero h-100">
        <div class="d-flex justify-content-center flex-column align-items-center h-100">
            <div class="container">
                <h2 class="text-center mb-5">Bienvenido a los apartamentos turisticos de Hawkins</h2>
                <img src="{{asset('logo_hawkins_white_center.png')}}" alt="" class="img-fluid d-block m-auto" style="max-width: 250px;">
                @if (Route::has('login'))
                    <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10 px-4 mt-5">
                        @auth
                            <a href="{{ url('/home') }}" class="">Home</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-light w-100">Log in</a>
                        {{--
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Register</a>
                            @endif
                        --}}
                        @endauth
                    </div>
                @endif
            </div>
        </div>
    </body>
</html>
