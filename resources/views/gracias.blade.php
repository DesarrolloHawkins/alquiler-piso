<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Hawkins Suite - {{$textos['title']}}</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-white">
    <div class="container text-center">
        <div class="row align-items-center justify-content-center">
            <div class="col-12 mt-5">
                <div class="logo">
                    <img src="https://apartamentosalgeciras.com/wp-content/uploads/2022/09/Logo-Hawkins-Suites.svg" alt="" class="img-fluid mb-3 w-75 m-auto">
                </div>
            </div>
            <div class="col-12 mt-3">
                <h2>{{$textos['subtitle']}}</h2>
            </div>
            <div class="col-12 mt-4">
                <p>{{$textos['tenemos']}}</p>
            </div>
            <div class="col-12 mt-4">
                <p>{{$textos['info']}} <a href="{{route('gracias.contacto')}}">{{$textos['contacto']}}</a> {{$textos['telefono']}} <a href="tel:+34605379329">+34 605 37 93 29</a> {{$textos['horario']}}</p>
                <p>{{$textos['horaario2']}}
                    <button onclick="window.open("https://wa.me/34605379329", "_blank")" class="btn btn-primary d-block w-100 my-3">{{$textos['ir']}}</button>
                    {{$textos['ia']}}</p>
            </div>
        </div>
    </div>

</body>
</html>