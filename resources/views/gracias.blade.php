<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Hawkins Suite - {{ $textos['title'] }}</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        /* === Fondo PWA + tipografía === */
        body{
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display:flex; align-items:center; justify-content:center;
            padding: 24px;
        }

        /* === Card “glassmorphism” === */
        .form-card{
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            transition: all .3s ease;
            width: 100%;
            max-width: 680px;
            overflow: hidden;
        }
        .form-card:hover{
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .form-header{
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 22px;
            text-align: center;
        }
        .form-header h1{
            margin: 0; font-weight: 700; font-size: 1.4rem;
        }

        .content{
            padding: 24px;
            text-align: center;
        }

        .logo img{
            max-width: 300px;
            width: 100%;
        }

        .lead{
            color: #4a5568;
            font-size: 1.05rem;
            margin: 6px 0;
        }

        /* Botón moderno */
        .btn-modern{
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none; color: #fff;
            border-radius: 14px;
            padding: 14px 24px;
            font-weight: 700; font-size: 1rem;
            width: 100%;
            max-width: 320px;
            transition: all .25s ease;
        }
        .btn-modern:hover{ transform: translateY(-2px); box-shadow: 0 10px 20px rgba(102,126,234,.30); color:#fff; }
        .btn-modern:active{ transform: translateY(0); }

        /* Enlaces */
        a.link{ color:#5a67d8; font-weight:600; text-decoration:none; }
        a.link:hover{ text-decoration:underline; }

        /* Selector de idioma flotante (igual que en la otra vista) */
        .lang-switch{
            position: fixed; top: 10px; right: 10px; z-index: 11000;
            background: rgba(255,255,255,.95);
            backdrop-filter: blur(8px);
            border-radius: 999px;
            padding: 6px 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,.15);
            border: 1px solid rgba(255,255,255,.6);
            display: flex; align-items: center; gap: 6px;
        }
        .lang-switch .flag{ font-size: 18px; line-height:1; }
        .lang-switch select{
            border: none; background: transparent; outline: none;
            font-weight: 600; padding: 6px 4px; border-radius: 999px;
            appearance: none; -webkit-appearance: none; -moz-appearance: none;
            max-width: 160px;
        }
        @media (max-width: 480px){
            .lang-switch select{ max-width: 120px; font-size: .95rem; }
        }

        /* Layout helpers */
        .stack{ display:flex; flex-direction:column; gap: 10px; align-items:center; }
        .stack-lg{ gap: 16px; }
        .muted{ color:#6b7280; }

        /* Info capsules */
        .pill{
            display:inline-flex; align-items:center; gap:8px;
            background: #f7fafc; border: 1px solid #edf2f7;
            color:#4a5568; padding: 8px 12px; border-radius: 999px;
            font-weight: 600; font-size: .95rem;
        }
    </style>
</head>
<body>

    {{-- Selector global de idioma (mismos idiomas que el otro) --}}
    <div class="lang-switch">
        <span class="flag" id="flagIcon">
            @php $loc = session('locale','es'); @endphp
            @switch($loc)
                @case('en') 🇺🇸 @break
                @case('fr') 🇫🇷 @break
                @case('de') 🇩🇪 @break
                @case('it') 🇮🇹 @break
                @case('pt') 🇵🇹 @break
                @default 🇪🇸
            @endswitch
        </span>
        <select id="globalIdioma" aria-label="Language selector" onchange="cambiarIdioma(this.value)">
            <option value="es" {{ $loc=='es'?'selected':'' }}>Español</option>
            <option value="en" {{ $loc=='en'?'selected':'' }}>English</option>
            <option value="fr" {{ $loc=='fr'?'selected':'' }}>Français</option>
            <option value="de" {{ $loc=='de'?'selected':'' }}>Deutsch</option>
            <option value="it" {{ $loc=='it'?'selected':'' }}>Italiano</option>
            <option value="pt" {{ $loc=='pt'?'selected':'' }}>Português</option>
        </select>
    </div>

    <div class="form-card">
        <div class="form-header">
            <h1>{{ $textos['subtitle'] }}</h1>
        </div>

        <div class="content">
            <div class="stack">
                <div class="logo">
                    <img src="https://apartamentosalgeciras.com/wp-content/uploads/2022/09/Logo-Hawkins-Suites.svg"
                         class="img-fluid" alt="Hawkins Suites" />
                </div>

                <p class="lead">{{ $textos['tenemos'] }}</p>

                <div class="stack-lg" style="width:100%; max-width:520px;">
                    <p class="muted">
                        {{ $textos['info'] }}
                        <a class="link" href="{{ route('gracias.contacto') }}">{{ $textos['contacto'] }}</a>
                        {{ $textos['telefono'] }}
                        <a class="link" href="tel:+34605379329">+34 605 37 93 29</a>
                        {{ $textos['horario'] }}
                    </p>

                    <div class="pill">
                        <i class="fa-regular fa-clock"></i>
                        <span>{{ $textos['horaario2'] }}</span>
                    </div>

                    <button onclick="openWhatsapp()" class="btn-modern">
                        {{ $textos['ir'] }}
                    </button>

                    <p class="muted">{{ $textos['ia'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Icons (si usas Font Awesome vía Vite ya no hace falta este CDN) --}}
    <script src="https://kit.fontawesome.com/a2e0e6ad5c.js" crossorigin="anonymous"></script>

    <script>
        function openWhatsapp(){
            window.open('https://wa.me/34605379329', '_blank');
        }

        // Cambio de idioma – igual que en la otra pantalla
        async function cambiarIdioma(idioma){
            if(!idioma) return;

            // feedback rápido en el pill
            const flagMap = { es:'🇪🇸', en:'🇺🇸', fr:'🇫🇷', de:'🇩🇪', it:'🇮🇹', pt:'🇵🇹' };
            const flag = flagMap[idioma] || '🌐';
            const flagEl = document.getElementById('flagIcon');
            if(flagEl) flagEl.textContent = flag;

            try{
                const res = await fetch('{{ route("dni.cambiarIdioma") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        idioma,
                        // si tienes token de reserva en esta vista, añádelo:
                        token: '{{ $reserva->token ?? '' }}'
                    })
                });
                const data = await res.json();
                if(data?.success){
                    // si mandas redirect desde el backend: data.redirect
                    window.location.href = data.redirect || window.location.href;
                }else{
                    // fallback visual mínimo
                    alert('Error al cambiar el idioma');
                }
            }catch(e){
                console.error(e);
                alert('Error al cambiar el idioma');
            }
        }
    </script>
</body>
</html>
