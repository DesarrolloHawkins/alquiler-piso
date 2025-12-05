<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Detección de Documentos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f0f0f0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .camera-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: 20px auto;
            border: 2px solid #ddd;
            border-radius: 10px;
            overflow: hidden;
        }
        #camera {
            width: 100%;
            height: auto;
            display: block;
        }
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
        }
        .guide-frame {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            height: 60%;
            border: 3px solid #00ff00;
            border-radius: 10px;
        }
        .corner {
            position: absolute;
            width: 20px;
            height: 20px;
            border: 3px solid #00ff00;
        }
        .corner.top-left {
            top: -3px;
            left: -3px;
            border-right: none;
            border-bottom: none;
        }
        .corner.top-right {
            top: -3px;
            right: -3px;
            border-left: none;
            border-bottom: none;
        }
        .corner.bottom-left {
            bottom: -3px;
            left: -3px;
            border-right: none;
            border-top: none;
        }
        .corner.bottom-right {
            bottom: -3px;
            right: -3px;
            border-left: none;
            border-top: none;
        }
        .controls {
            text-align: center;
            margin: 20px 0;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 5px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #0056b3;
        }
        button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .status {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            background: #f8f9fa;
            border-left: 4px solid #007bff;
        }
        .error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        .result {
            margin: 20px 0;
            padding: 15px;
            border-radius: 5px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .result h3 {
            margin-top: 0;
            color: #495057;
        }
        .result pre {
            background: #e9ecef;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
        }
        .detection-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 10px 0;
        }
        .detection-item {
            padding: 10px;
            background: #e9ecef;
            border-radius: 5px;
            text-align: center;
        }
        .detection-item strong {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Test Detección de Documentos</h1>
        
        <div class="status" id="status">
            Iniciando sistema de detección...
        </div>
        
        <div class="camera-container">
            <video id="camera" autoplay muted playsinline></video>
            <div class="overlay">
                <div class="guide-frame">
                    <div class="corner top-left"></div>
                    <div class="corner top-right"></div>
                    <div class="corner bottom-left"></div>
                    <div class="corner bottom-right"></div>
                </div>
            </div>
        </div>
        
        <div class="controls">
            <button id="startBtn" onclick="startCamera()">Iniciar Cámara</button>
            <button id="captureBtn" onclick="captureAndDetect()" disabled>Capturar y Detectar</button>
            <button id="stopBtn" onclick="stopCamera()" disabled>Detener Cámara</button>
            <button id="checkTesseractBtn" onclick="checkTesseract()">Verificar Tesseract</button>
        </div>
        
        <div class="result" id="result" style="display: none;">
            <h3>Resultado de la Detección:</h3>
            <div class="detection-info" id="detectionInfo"></div>
            <pre id="resultText"></pre>
        </div>
    </div>

    <script>
        let stream = null;
        let canvas = null;
        let ctx = null;
        
        function updateStatus(message, type = 'info') {
            const status = document.getElementById('status');
            status.textContent = message;
            status.className = `status ${type}`;
        }
        
        function updateButtons(start, capture, stop) {
            document.getElementById('startBtn').disabled = !start;
            document.getElementById('captureBtn').disabled = !capture;
            document.getElementById('stopBtn').disabled = !stop;
        }
        
        async function startCamera() {
            try {
                updateStatus('Solicitando acceso a la cámara...');
                
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    throw new Error('getUserMedia no soportado en este navegador');
                }
                
                const constraints = {
                    video: {
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        facingMode: 'environment'
                    }
                };
                
                stream = await navigator.mediaDevices.getUserMedia(constraints);
                const camera = document.getElementById('camera');
                camera.srcObject = stream;
                
                canvas = document.createElement('canvas');
                ctx = canvas.getContext('2d');
                
                updateStatus('✅ Cámara iniciada correctamente', 'success');
                updateButtons(false, true, true);
                
            } catch (error) {
                console.error('Error iniciando cámara:', error);
                updateStatus(`❌ Error: ${error.message}`, 'error');
                updateButtons(true, false, false);
            }
        }
        
        async function captureAndDetect() {
            try {
                const camera = document.getElementById('camera');
                
                if (!camera || !camera.videoWidth || !camera.videoHeight) {
                    throw new Error('Cámara no está lista');
                }
                
                canvas.width = camera.videoWidth;
                canvas.height = camera.videoHeight;
                ctx.drawImage(camera, 0, 0, canvas.width, canvas.height);
                
                const imageData = canvas.toDataURL('image/jpeg', 0.8);
                
                updateStatus('Procesando imagen con OCR...');
                
                // Obtener token CSRF de forma segura
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    throw new Error('Token CSRF no encontrado');
                }
                
                const response = await fetch('/document-detection/process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                    },
                    body: JSON.stringify({
                        image: imageData,
                        side: 'front'
                    })
                });
                
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    displayDetectionResult(result.detection);
                    updateStatus('✅ Detección completada', 'success');
                } else {
                    updateStatus(`❌ Error: ${result.message}`, 'error');
                }
                
            } catch (error) {
                console.error('Error capturando imagen:', error);
                updateStatus(`❌ Error: ${error.message}`, 'error');
            }
        }
        
        function displayDetectionResult(detection) {
            const resultDiv = document.getElementById('result');
            const detectionInfo = document.getElementById('detectionInfo');
            const resultText = document.getElementById('resultText');
            
            // Mostrar información de detección
            detectionInfo.innerHTML = `
                <div class="detection-item">
                    <strong>Tipo Detectado</strong>
                    ${detection.type.toUpperCase()}
                </div>
                <div class="detection-item">
                    <strong>Confianza</strong>
                    ${detection.confidence.toFixed(1)}%
                </div>
                <div class="detection-item">
                    <strong>Puntuación</strong>
                    ${detection.score || 0}
                </div>
                <div class="detection-item">
                    <strong>Estado</strong>
                    ${detection.confidence > 70 ? '✅ ALTO' : detection.confidence > 40 ? '⚠️ MEDIO' : '❌ BAJO'}
                </div>
            `;
            
            resultText.textContent = JSON.stringify(detection, null, 2);
            resultDiv.style.display = 'block';
        }
        
        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            
            const camera = document.getElementById('camera');
            camera.srcObject = null;
            
            updateStatus('Cámara detenida');
            updateButtons(true, false, false);
        }
        
        async function checkTesseract() {
            try {
                updateStatus('Verificando instalación de Tesseract...');
                
                const response = await fetch('/document-detection/check-tesseract');
                const result = await response.json();
                
                if (result.success && result.installed) {
                    updateStatus('✅ Tesseract OCR está instalado', 'success');
                } else {
                    updateStatus('❌ Tesseract OCR no está instalado', 'error');
                }
                
            } catch (error) {
                updateStatus(`❌ Error verificando Tesseract: ${error.message}`, 'error');
            }
        }
        
        // Inicializar al cargar la página
        window.addEventListener('load', () => {
            updateStatus('Sistema listo. Haz clic en "Iniciar Cámara" para comenzar.');
            updateButtons(true, false, false);
        });
    </script>
</body>
</html>
