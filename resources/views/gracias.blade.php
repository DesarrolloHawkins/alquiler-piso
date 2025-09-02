<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Hawkins Suite - {{ $textos['title'] }}</title>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        /* Fondo PWA + tipografía */
        body{
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display:flex; align-items:flex-start; justify-content:center;
            padding: 24px;
        }

        /* Card glass */
        .form-card{
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            transition: all .3s ease;
            width: 100%;
            max-width: 420px;
            overflow: hidden;
        }
        .form-card:hover{ transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,0,0,0.15); }

        .form-header{
            background: linear-gradient(135deg, rgba(255,255,255,.35) 0%, rgba(255,255,255,.2) 100%);
            color: #ffffff;
            padding: 18px 22px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,.35);
        }
        .form-header h2{ margin:0; font-weight:700; font-size:1.05rem; color:#fff; }

        .content{ padding: 18px; text-align: center; }
        .logo img{ max-width: 240px; width: 100%; }
        .lead{ color:#4a5568; font-size:.98rem; margin:6px 0; }

        .btn-modern{
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none; color: #fff;
            border-radius: 14px;
            padding: 14px 24px;
            font-weight: 700; font-size: 1rem;
            width: 100%;
            transition: all .25s ease;
        }
        .btn-modern:hover{ transform: translateY(-2px); box-shadow: 0 10px 20px rgba(102,126,234,.30); color:#fff; }

        a.link{ color:#5a67d8; font-weight:600; text-decoration:none; }
        a.link:hover{ text-decoration:underline; }

        .pill{
            display:flex; align-items:center; justify-content:center;
            background: rgba(255,255,255,.85);
            border:1px solid rgba(0,0,0,.06);
            color:#4a5568;
            padding:12px; border-radius:14px; font-weight:600;
        }
    </style>
</head>
<body>

    <div class="form-card">
        <div class="form-header">
            <h2>{{ $textos['subtitle'] }}</h2>
        </div>

        <div class="content">
            <div class="logo mb-3">
                <img src="https://apartamentosalgeciras.com/wp-content/uploads/2022/09/Logo-Hawkins-Suites.svg"
                     class="img-fluid" alt="Hawkins Suites" />
            </div>

            <p class="lead">{{ $textos['tenemos'] }}</p>

            <p class="lead" style="font-size:.94rem;">
                {{ $textos['info'] }}
                <a class="link" href="{{ route('gracias.contacto') }}">{{ $textos['contacto'] }}</a>
                {{ $textos['telefono'] }}
                <a class="link" href="tel:+34605379329">+34 605 37 93 29</a>
                {{ $textos['horario'] }}
            </p>

            <div class="pill my-2" style="width:100%;">
                {{ $textos['horaario2'] }}
            </div>

            <button onclick="openWhatsapp()" class="btn-modern my-2">
                {{ $textos['ir'] }}
            </button>

            <p class="lead" style="font-size:.9rem; color:#6b7280;">{{ $textos['ia'] }}</p>
        </div>
    </div>

    <script>
        function openWhatsapp(){
            window.open('https://wa.me/34605379329', '_blank');
        }
    </script>
</body>
</html>
