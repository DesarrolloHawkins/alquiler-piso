<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Hawkins Suite - Gracias por reservar con nosotros</title>
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
                <h2>Gracias por reservar un apartamento en nuestras estancias.</h2>
            </div>
            <div class="col-12 mt-4">
                <p>Ya tenemos los datos necesario para poder acceder a nuestras instalaciones, el mismo dia de la fecha de entrada recibira las indicaciones para acceder al apartamento.</p>
            </div>
            <div class="col-12 mt-4">
                <p>Para cualquier informacion o reclamacion puede realizarla atraves de nuestro formulario de <a href="{{route('gracias.contacto')}}">contacto</a> o en el telefono: <a href="tel:+34652544141">+34 652 54 41 41</a> en horario de 09:00 a 14:00 horas.</p>
                <p>Para cualquier horario a traves de whatsapp:
                <button class="btn btn-primary d-block w-100 my-3">Ir al Whatsapp</button>
                sera atendido por nuestra Inteligencia Artificial.</p>
            </div>
        </div>
    </div>

</body>
</html>