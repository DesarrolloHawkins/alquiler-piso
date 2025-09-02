<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Incidencias</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f0f0f0;
        }
        .test-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .test-box {
            margin: 20px 0;
            padding: 20px;
            border-radius: 10px;
            color: white;
            text-align: center;
        }
        .test-red { background: #dc3545; }
        .test-blue { background: #007bff; }
        .test-green { background: #28a745; }
        .test-yellow { background: #ffc107; color: #212529; }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🧪 TEST COMPLETAMENTE INDEPENDIENTE</h1>
        
        <div class="test-box test-red">
            <h2>🚨 CAJA ROJA - DEBERÍA VERSE</h2>
            <p>Si ves esto, el problema NO está en la vista</p>
        </div>
        
        <div class="test-box test-blue">
            <h2>📋 DATOS DE INCIDENCIAS</h2>
            <p>Total: {{ $incidencias->total() ?? 'ERROR' }}</p>
            <p>Count: {{ $incidencias->count() ?? 'ERROR' }}</p>
            <p>Usuario: {{ Auth::user()->name ?? 'NO AUTENTICADO' }}</p>
        </div>
        
        <div class="test-box test-green">
            <h2>✅ LISTA DE INCIDENCIAS</h2>
            @if($incidencias->count() > 0)
                <ul style="text-align: left; display: inline-block;">
                    @foreach($incidencias as $incidencia)
                        <li>
                            <strong>#{{ $incidencia->id }}</strong> - 
                            {{ $incidencia->titulo }} 
                            ({{ $incidencia->estado }})
                        </li>
                    @endforeach
                </ul>
            @else
                <p>No hay incidencias</p>
            @endif
        </div>
        
        <div class="test-box test-yellow">
            <h2>🔗 ENLACES</h2>
            <a href="{{ route('gestion.incidencias.create') }}" 
               style="background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 10px; display: inline-block; margin: 10px;">
                <i class="fa-solid fa-plus"></i> Nueva Incidencia
            </a>
            <br>
            <a href="{{ route('gestion.index') }}" 
               style="background: #6c757d; color: white; padding: 15px 30px; text-decoration: none; border-radius: 10px; display: inline-block; margin: 10px;">
                Volver a Gestión
            </a>
        </div>
        
        <div style="text-align: center; margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
            <h3>📊 INFORMACIÓN DE DEBUG</h3>
            <p><strong>Ruta actual:</strong> {{ request()->url() }}</p>
            <p><strong>Método:</strong> {{ request()->method() }}</p>
            <p><strong>Usuario ID:</strong> {{ Auth::id() ?? 'NO' }}</p>
            <p><strong>Timestamp:</strong> {{ now() }}</p>
        </div>
    </div>
</body>
</html>
