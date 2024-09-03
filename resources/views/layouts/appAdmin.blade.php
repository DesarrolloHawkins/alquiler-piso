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

    </head>
    <body>
        <div id="app">
            <div class="container-fluid h-100">
                <div class="row h-100">
                    <!-- Sidebar -->
                    <div class="col-auto bg-light sidebar p-3" 
                        style="height: 90vh; max-width: 300px; overflow: hidden; background-color: #0F1739 !important; margin: 20px 0 20px 20px; border-radius: 20px;">
                        <div class="d-flex flex-column flex-shrink-0 text-white h-100" style="">
                            <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none w-100">
                                <img src="{{asset('logo-hawkins-suite_white.png')}}" alt="" style="" class="img-fluid d-block m-auto mt-1">
                            </a>
                            <hr>
                            <ul class="nav nav-pills flex-row mb-auto" style="overflow-x: none; overflow:auto;">
                                {{-- Dashboard --}}
                                <li class="nav-item w-100">
                                <a href="{{route('dashboard.index')}}" class="nav-link fs-5 {{ request()->is('dashboard', 'dashboard/*') ? 'active' : '' }}" aria-current="page">
                                    <i class="fa-solid fa-chart-area me-2 fs-4" style=" width:25px"></i>
                                    Dashboard
                                </a>
                                </li>

                                {{-- Clientes --}}
                                <li class="nav-item w-100">
                                    <a href="{{route('clientes.index')}}" class="nav-link fs-5 {{ request()->is('clientes', 'clientes/*') ? 'active' : '' }}" aria-current="page">
                                        <i class="fa-solid fa-users me-2 fs-4" style=" width:25px"></i>
                                        Clientes
                                    </a>
                                </li>

                                {{-- Reservas --}}
                                <li class="nav-item w-100">
                                    <!-- Modificado para mantener abierto cuando esté dentro de las rutas relacionadas -->
                                    <button data-info="button" data-bs-target="#submenuReservas" href="#submenuReservas" class="nav-link fs-5 w-100 text-start {{ request()->is('reservas', 'reservas/*') ? 'active' : 'collapsed' }}" aria-expanded="{{ request()->is('reservas', 'reservas/*') ? 'true' : 'false' }}">
                                        <i class="fa-solid fa-table me-2 fs-4" style="width:25px"></i>
                                        Reservas
                                    </button>
                                    <!-- Submenú para Contabilidad, modificado para mostrar/ocultar basado en la ruta -->
                                    <ul class="collapse nav flex-column ms-1 fondo_dropdraw {{ request()->is('reservas*', 'reservas') ? 'show' : '' }}" id="submenuReservas">
                                        <li class="nav-item">
                                            <a href="{{ route('reservas.index') }}" class="nav-link fs-6 {{ request()->is('reservas', 'reservas/*') ? 'active' : '' }}">
                                                Ver Reservas
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.grupoContabilidad.index') }}" class="nav-link fs-6 {{ request()->is('grupo-contable', 'grupo-contable/*') ? 'active' : '' }}">
                                                Tabla de Reservas
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                {{-- Gestion --}}
                                <li class="nav-item w-100">
                                    <!-- Modificado para mantener abierto cuando esté dentro de las rutas relacionadas -->
                                    <button data-bs-target="#submenuGestion" href="#submenuGestion" data-info="button" class="nav-link fs-5 w-100 text-start {{ request()->is('jornada', 'jornada/*', 'edificios', 'edificios/*', 'gestion', 'gestion/*', 'limpieza-apartamento', 'limpieza-apartamento/*','checklists', 'checklists/*','items_checklist', 'items_checklist/*','apartamentos', 'apartamentos/*') ? 'active' : 'collapsed' }}" aria-expanded="{{ request()->is('jornada', 'jornada/*', 'edificios', 'edificios/*', 'gestion', 'gestion/*', 'limpieza-apartamento', 'limpieza-apartamento/*','checklists', 'checklists/*','items_checklist', 'items_checklist/*','apartamentos', 'apartamentos/*') ? 'true' : 'false' }}">
                                        <i class="fa-solid fa-list-check me-2 fs-4" style="width:25px"></i>
                                        Gestión
                                    </button>
                                    <!-- Submenú para Contabilidad, modificado para mostrar/ocultar basado en la ruta -->
                                    <ul class="collapse nav flex-column ms-1 fondo_dropdraw {{ request()->is('jornada', 'jornada/*', 'edificios', 'edificios/*', 'gestion', 'gestion/*', 'limpieza-apartamento', 'limpieza-apartamento/*','checklists', 'checklists/*','items_checklist', 'items_checklist/*','apartamentos', 'apartamentos/*') ? 'show' : '' }}" id="submenuGestion">

                                        <li class="nav-item w-100">
                                            <a href="{{route('apartamentos.admin.index')}}" class="nav-link fs-6 {{ request()->is('apartamentos', 'apartamentos/*') ? 'active' : '' }}">
                                                Apartamentos
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.jornada.index') }}" class="nav-link fs-6 {{ request()->is('jornada', 'jornada/*') ? 'active' : '' }}">
                                                Jornada
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{route('admin.edificios.index')}}" class="nav-link fs-6 {{ request()->is('edificios', 'edificios/*','edificio/*') ? 'active' : '' }}" >
                                                Edificios
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a target="blank" href="{{route('gestion.index')}}" class="nav-link fs-6 {{ request()->is('gestion', 'gestion/*') ? 'active' : '' }}">
                                                Limpieza
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{route('admin.limpiezaFondo.index')}}" class="nav-link fs-6 {{ request()->is('limpieza-apartamento', 'limpieza-apartamento/*') ? 'active' : '' }}">
                                                Limpieza Fondo
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{route('admin.checklists.index')}}" class="nav-link fs-6 {{ request()->is('checklists', 'checklists/*','items_checklist', 'items_checklist/*') ? 'active' : '' }}">
                                                Categorias de Limpieza
                                            </a>
                                        </li>
                                        
                                    </ul>
                                </li>

                                {{-- Asistente --}}
                                <li class="nav-item w-100">
                                    <a target="_blank" href="{{route('whatsapp.mensajes')}}" class="nav-link fs-5" aria-current="page">
                                        <i class="fa-solid fa-user-astronaut me-2 fs-4" style=" width:25px"></i>
                                        Conversaciones
                                    </a>
                                </li>

                                {{-- Tesoreria --}}
                                <li class="nav-item w-100">
                                    <!-- Modificado para mantener abierto cuando esté dentro de las rutas relacionadas -->
                                    <button data-bs-target="#submenuTesoreria" href="#submenuTesoreria" data-info="button" class="nav-link fs-5 w-100 text-start {{ request()->is('diario-caja', 'diario-caja/*', 'ingresos', 'ingresos/*', 'categoria-ingresos', 'categoria-ingresos/*', 'gastos', 'gastos/*','bancos', 'bancos/*','categoria-gastos', 'categoria-gastos/*') ? 'active' : 'collapsed' }}" aria-expanded="{{ request()->is('diario-caja', 'diario-caja/*', 'ingresos', 'ingresos/*', 'categoria-ingresos', 'categoria-ingresos/*', 'gastos', 'gastos/*','bancos', 'bancos/*','categoria-gastos', 'categoria-gastos/*') ? 'true' : 'false' }}">
                                        <i class="fa-solid fa-coins me-2 fs-4" style=" width:25px"></i>
                                        Tesoreria
                                    </button>
                                    <!-- Submenú para Contabilidad, modificado para mostrar/ocultar basado en la ruta -->
                                    <ul class="collapse nav flex-column ms-1 fondo_dropdraw {{ request()->is('diario-caja', 'diario-caja/*', 'ingresos', 'ingresos/*', 'categoria-ingresos', 'categoria-ingresos/*', 'gastos', 'gastos/*','bancos', 'bancos/*','categoria-gastos', 'categoria-gastos/*') ? 'show' : '' }}" id="submenuTesoreria">
                                        <li class="nav-item w-100">
                                            <a href="{{route('admin.diarioCaja.index')}}" class="nav-link fs-6 {{ request()->is('diario-caja', 'diario-caja/*') ? 'active' : '' }}">
                                                Diario de Caja
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.ingresos.index') }}" class="nav-link fs-6 {{ request()->is('ingresos', 'ingresos/*') ? 'active' : '' }}">
                                                Ver Ingresos
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.categoriaIngresos.index') }}" class="nav-link fs-6 {{ request()->is('categoria-ingresos', 'categoria-ingresos/*') ? 'active' : '' }}">
                                                Categoría de Ingresos
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.gastos.index') }}" class="nav-link fs-6 {{ request()->is('gastos', 'gastos/*') ? 'active' : '' }}">
                                                Ver Gastos
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.categoriaGastos.index') }}" class="nav-link fs-6 {{ request()->is('categoria-gastos', 'categoria-gastos/*') ? 'active' : '' }}">
                                                Categoría de Gastos
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{route('admin.bancos.index')}}" class="nav-link fs-6 {{ request()->is('bancos', 'bancos/*') ? 'active' : '' }}" >
                                            Bancos
                                            </a>
                                        </li>
                                        
                                    </ul>
                                </li>

                                {{-- Contabilidad --}}
                                <li class="nav-item w-100">
                                    <!-- Modificado para mantener abierto cuando esté dentro de las rutas relacionadas -->
                                    <button data-bs-target="#submenuContabilidad" href="#submenuContabilidad" data-info="button" class="nav-link fs-5 w-100 text-start {{ request()->is('cuentas-contables*', 'sub-cuentas-contables*', 'sub-cuentas-hijas-contables*', 'grupo-contable*', 'sub-grupo-contable*') ? '' : 'collapsed' }}" aria-expanded="{{ request()->is('cuentas-contables*', 'sub-cuentas-contables*', 'sub-cuentas-hijas-contables*', 'grupo-contable*', 'sub-grupo-contable*') ? 'true' : 'false' }}">
                                        <i class="fa-solid fa-calculator me-2 fs-4" style="width:25px"></i>
                                        Contabilidad
                                    </button>
                                    <!-- Submenú para Contabilidad, modificado para mostrar/ocultar basado en la ruta -->
                                    <ul class="fondo_dropdraw collapse nav flex-column ms-1 {{ request()->is('cuentas-contables*', 'plan-contable', 'sub-cuentas-contables*', 'sub-cuentas-hijas-contables*', 'grupo-contable*', 'sub-grupo-contable*') ? 'show' : '' }}" id="submenuContabilidad">
                                        <li class="nav-item">
                                            <a href="{{ route('admin.planContable.index') }}" class="nav-link fs-6 {{ request()->is('plan-contable', 'plan-contable/*') ? 'active' : '' }}">
                                                Plan General Contable
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.grupoContabilidad.index') }}" class="nav-link fs-6 {{ request()->is('grupo-contable', 'grupo-contable/*') ? 'active' : '' }}">
                                                Grupos Contables
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.subGrupoContabilidad.index') }}" class="nav-link fs-6 {{ request()->is('sub-grupo-contable', 'sub-grupo-contable/*') ? 'active' : '' }}">
                                                Sub-Grupos Contables
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.cuentasContables.index') }}" class="nav-link fs-6 {{ request()->is('cuentas-contables', 'cuentas-contables/*') ? 'active' : '' }}">
                                                Cuentas Contables
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.subCuentasContables.index') }}" class="nav-link fs-6 {{ request()->is('sub-cuentas-contables', 'sub-cuentas-contables/*') ? 'active' : '' }}">
                                                Sub-Cuentas Contables
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ route('admin.subCuentasHijaContables.index') }}" class="nav-link fs-6 {{ request()->is('sub-cuentas-hijas-contables', 'sub-cuentas-hijas-contables/*') ? 'active' : '' }}">
                                                Sub-Cuentas Hijas Contables
                                            </a>
                                        </li>
                                        
                                        
                                    </ul>
                                </li>
                                
                                {{-- Logs --}}
                                <li class="nav-item w-100">
                                    <a href="#" class="nav-link fs-5" aria-current="page">
                                        <i class="fa-solid fa-file-lines me-2 fs-4" style=" width:25px"></i>
                                    Logs
                                    </a>
                                </li>

                                {{-- Configuracion --}}
                                <li class="nav-item w-100">
                                    <a href="{{route('configuracion.index')}}"  class="nav-link fs-5" aria-current="page">
                                        <i class="fa-solid fa-gear me-2 fs-4" style=" width:25px"></i>
                                    Configuracion
                                    </a>
                                </li>
                            </ul>
                            <hr>
                            <div class="dropdown">
                                <a class="nav-link my-1 mx-3 text-black text-white" href="{{ route('logout') }}"
                                    onclick="
                                    event.preventDefault();
                                    document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </div>
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


        {{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}

        {{-- Scripts --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
        
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
            
            
            
        @include('sweetalert::alert')
        @yield('scripts')

    </body>
</html>
