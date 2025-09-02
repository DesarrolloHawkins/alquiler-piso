<!DOCTYPE html>
<html>
<head>
    <title>Test Simple</title>
    <style>
        body { 
            background: red; 
            color: white; 
            padding: 50px; 
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <h1>TEST VISTA SIMPLE</h1>
    <p>Si ves esto en rojo, la vista funciona.</p>
    <p>Usuario: {{ Auth::user()->name }}</p>
    <p>Incidencias: {{ $incidencias->count() }}</p>
</body>
</html>
