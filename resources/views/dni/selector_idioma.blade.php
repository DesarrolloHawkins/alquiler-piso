<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Idioma - Language Selection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .language-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .language-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .language-option {
            cursor: pointer;
            padding: 20px;
            border-radius: 15px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            background: white;
            margin-bottom: 15px;
        }
        
        .language-option:hover {
            border-color: #667eea;
            background: #f8f9ff;
            transform: scale(1.02);
        }
        
        .language-option.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .flag-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .language-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .language-native {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .btn-continue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-continue:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-continue:disabled {
            opacity: 0.6;
            transform: none;
        }
        
        .welcome-text {
            color: white;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .welcome-text h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .welcome-text p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .loading {
            display: none;
        }
        
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-lg-8 col-md-10 col-sm-12">
                <!-- Texto de bienvenida -->
                <div class="welcome-text">
                    <h1>🌍 Bienvenido / Welcome / Bienvenue / Willkommen / Benvenuto / Bem-vindo</h1>
                    <p>Por favor, selecciona tu idioma preferido para continuar</p>
                    <p>Please select your preferred language to continue</p>
                </div>
                
                <!-- Tarjeta de selección de idioma -->
                <div class="language-card p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="language-option" data-lang="es">
                                <div class="text-center">
                                    <div class="flag-icon">🇪🇸</div>
                                    <div class="language-name">Español</div>
                                    <div class="language-native">Spanish</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="language-option" data-lang="en">
                                <div class="text-center">
                                    <div class="flag-icon">🇺🇸</div>
                                    <div class="language-name">English</div>
                                    <div class="language-native">Inglés</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="language-option" data-lang="fr">
                                <div class="text-center">
                                    <div class="flag-icon">🇫🇷</div>
                                    <div class="language-name">Français</div>
                                    <div class="language-native">French</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="language-option" data-lang="de">
                                <div class="text-center">
                                    <div class="flag-icon">🇩🇪</div>
                                    <div class="language-name">Deutsch</div>
                                    <div class="language-native">German</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="language-option" data-lang="it">
                                <div class="text-center">
                                    <div class="flag-icon">🇮🇹</div>
                                    <div class="language-name">Italiano</div>
                                    <div class="language-native">Italian</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="language-option" data-lang="pt">
                                <div class="text-center">
                                    <div class="flag-icon">🇵🇹</div>
                                    <div class="language-name">Português</div>
                                    <div class="language-native">Portuguese</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botón continuar -->
                    <div class="text-center mt-4">
                        <button id="btnContinuar" class="btn btn-continue text-white" disabled>
                            <span class="loading">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            </span>
                            <span class="btn-text">Continuar / Continue / Continuer / Fortfahren / Continua / Continuar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let selectedLanguage = null;
        
        // Seleccionar idioma
        $('.language-option').click(function() {
            $('.language-option').removeClass('selected');
            $(this).addClass('selected');
            selectedLanguage = $(this).data('lang');
            $('#btnContinuar').prop('disabled', false);
        });
        
        // Continuar con el idioma seleccionado
        $('#btnContinuar').click(function() {
            if (!selectedLanguage) return;
            
            // Mostrar loading
            $('.loading').show();
            $('.btn-text').hide();
            $(this).prop('disabled', true);
            
            // Hacer petición AJAX para establecer el idioma
            $.ajax({
                url: '{{ route("dni.cambiarIdioma") }}',
                type: 'POST',
                data: {
                    idioma: selectedLanguage,
                    token: '{{ $token }}',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        // Redirigir al formulario de DNI
                        window.location.href = response.redirect;
                    } else {
                        alert('Error al establecer el idioma: ' + response.message);
                        // Restaurar botón
                        $('.loading').hide();
                        $('.btn-text').show();
                        $('#btnContinuar').prop('disabled', false);
                    }
                },
                error: function() {
                    alert('Error al establecer el idioma');
                    // Restaurar botón
                    $('.loading').hide();
                    $('.btn-text').show();
                    $('#btnContinuar').prop('disabled', false);
                }
            });
        });
        
        // Efecto hover en las opciones de idioma
        $('.language-option').hover(
            function() {
                if (!$(this).hasClass('selected')) {
                    $(this).css('transform', 'scale(1.02)');
                }
            },
            function() {
                if (!$(this).hasClass('selected')) {
                    $(this).css('transform', 'scale(1)');
                }
            }
        );
    </script>
</body>
</html>
