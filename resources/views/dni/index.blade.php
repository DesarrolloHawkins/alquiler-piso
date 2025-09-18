@extends('layouts.appUser')

@section('bienvenido')
    <h5 class="navbar-brand mb-0 w-auto text-center">Rellene el formulario para confirmar su reserva</h5>
@endsection

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    body{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif}
    .form-card{background:rgba(255,255,255,.95);border-radius:20px;box-shadow:0 15px 35px rgba(0,0,0,.1);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.2);transition:.3s}
    .form-card:hover{transform:translateY(-5px);box-shadow:0 20px 40px rgba(0,0,0,.15)}
    .form-header{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;border-radius:20px 20px 0 0;padding:20px;text-align:center}
    .form-header h3{margin:0;font-weight:600;font-size:1.5rem}
    .form-floating{position:relative;margin-bottom:1.2rem}
    .form-control,.form-select{background:#fff;border:2px solid #e9ecef;border-radius:15px;padding:15px;font-size:1rem;transition:.3s;height:auto}
    .form-control:focus,.form-select:focus{border-color:#667eea;box-shadow:0 0 0 .2rem rgba(102,126,234,.25);outline:none;transform:translateY(-2px)}
    .form-floating>label{position:absolute;top:15px;left:15px;color:#6c757d;transition:.3s;background:#fff;padding:0 5px;font-size:.9rem;height:fit-content!important}
    .form-control:focus+label,.form-control:not(:placeholder-shown)+label,.form-select:focus+label,.form-select:not([value=""])+label{top:-10px;left:10px;font-size:.8rem;color:#667eea;font-weight:600}
    .btn-modern{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border:none;border-radius:25px;padding:15px 40px;font-weight:600;font-size:1.1rem;transition:.3s;color:#fff;width:100%;max-width:300px}
    .btn-modern:hover{transform:translateY(-2px);box-shadow:0 10px 20px rgba(102,126,234,.3);color:#fff}
    .btn-modern:disabled{opacity:.6;transform:none}
    .btn-secondary-modern{background:#6c757d;border:none;border-radius:15px;padding:10px 20px;font-weight:600;transition:.3s;color:#fff}
    .btn-secondary-modern:hover{background:#5a6268;transform:translateY(-2px);color:#fff}
    .alert-modern{background:rgba(255,255,255,.9);border-radius:10px;padding:15px;margin:20px 0;color:#495057;text-align:center;border:none;font-weight:500;box-shadow:0 2px 10px rgba(0,0,0,.1)}
    .alert-modern i{margin-right:8px;color:#667eea}
    .progress-bar-container{background:rgba(255,255,255,.2);border-radius:10px;height:8px;margin:20px 0;overflow:hidden}
    .progress-bar{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);height:100%;border-radius:10px;transition:width .5s}
    .step-indicator{display:flex;justify-content:center;margin:20px 0}
    .step{width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,.3);color:#fff;display:flex;align-items:center;justify-content:center;margin:0 10px;font-weight:600;transition:.3s}
    .step.active{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);transform:scale(1.1)}
    .step.completed{background:#28a745}
    .spinner-border-sm{width:1rem;height:1rem}
    .file-input{display:none}
    .select2-container .select2-selection--single{height:55px}
    .select2-container--default .select2-selection--single .select2-selection__rendered{line-height:55px}
    .select2-container--default .select2-search--dropdown .select2-search__field{height:37px}
    .transition-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);z-index:9999;display:none;justify-content:center;align-items:center}
    .transition-overlay::after{content:'';width:50px;height:50px;border:3px solid rgba(255,255,255,.3);border-top:3px solid #fff;border-radius:50%;animation:spin 1s linear infinite}
    @keyframes spin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}
    .error-notification{position:fixed;top:20px;right:20px;background:#dc3545;color:#fff;padding:15px 20px;border-radius:10px;box-shadow:0 5px 15px rgba(220,53,69,.3);z-index:10000;display:none;max-width:400px}
    .error-notification i{margin-right:10px;color:#ffc107}
    .error-notification .close-notification{background:none;border:none;color:#fff;font-size:18px;margin-left:15px;cursor:pointer;opacity:.7;transition:.2s}
    .error-notification .close-notification:hover{opacity:1}

    /* Selector de idioma global (flotante) */
    .lang-switch{
        position:fixed; top:10px; right:10px; z-index:11000;
        background:rgba(255,255,255,.95); backdrop-filter:blur(8px);
        border-radius:999px; padding:6px 10px; box-shadow:0 8px 20px rgba(0,0,0,.15);
        border:1px solid rgba(255,255,255,.6);
        display:flex; align-items:center; gap:6px;
    }
    .lang-switch .flag{font-size:18px; line-height:1}
    .lang-switch select{
        border:none; background:transparent; outline:none;
        font-weight:600; padding:6px 4px; border-radius:999px;
        appearance:none; -webkit-appearance:none; -moz-appearance:none;
        max-width:160px;
    }
    @media (max-width:480px){ .lang-switch select{max-width:120px; font-size:.95rem} }
</style>

{{-- Selector de idioma global --}}
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

<div class="container">
    {{-- BLOQUE 1: Idioma + nº personas --}}
    @if ($reserva->numero_personas == 0 || $reserva->numero_personas == null)
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-lg-8 col-md-10 col-sm-12">
            <div class="text-center mb-4">
                <img src="https://apartamentosalgeciras.com/wp-content/uploads/2022/09/Logo-Hawkins-Suites.svg" alt="Hawkins Suites" class="img-fluid mb-3" style="max-width:300px;">
            </div>

            @if(!isset($cliente) || !($cliente->idioma_establecido ?? false))
            <div class="form-card" id="cardIdioma">
                <div class="form-header">
                    <h3><i class="fa-solid fa-language me-2"></i>Selecciona tu idioma</h3>
                </div>
                <div class="p-4">
                    <div class="mb-4">
                        <select id="idioma" class="form-select" onchange="cambiarIdioma(this.value)">
                            <option value="">-- Selecciona tu idioma --</option>
                            <option value="es" {{ session('locale','es')=='es'?'selected':'' }}>🇪🇸 Español</option>
                            <option value="en" {{ session('locale')=='en'?'selected':'' }}>🇺🇸 English</option>
                            <option value="fr" {{ session('locale')=='fr'?'selected':'' }}>🇫🇷 Français</option>
                            <option value="de" {{ session('locale')=='de'?'selected':'' }}>🇩🇪 Deutsch</option>
                            <option value="it" {{ session('locale')=='it'?'selected':'' }}>🇮🇹 Italiano</option>
                            <option value="pt" {{ session('locale')=='pt'?'selected':'' }}>🇵🇹 Português</option>
                        </select>
                    </div>
                    <div class="alert-modern">
                        <i class="fa-solid fa-info-circle"></i>
                        <span id="textoIdioma">Selecciona tu idioma preferido para continuar con el proceso de registro.</span>
                    </div>
                </div>
            </div>
            @endif

            <div class="form-card mt-4" id="cardNumeroPersonas" style="{{ isset($cliente) && ($cliente->idioma_establecido ?? false) ? '' : 'display:none;' }}">
                <div class="form-header">
                    <h3><i class="fa-solid fa-users me-2"></i>Número de personas</h3>
                </div>
                <div class="p-4">
                    <div class="alert-modern">
                        <i class="fa-solid fa-info-circle"></i>
                        <span id="tituloNumeroPersonas">Para poder continuar debes decirnos el número de adultos (mayores de 18 años) que van a ocupar la reserva.</span>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold" id="labelNumeroAdultos">Número de Adultos:</label>
                        </div>
                        <div class="col-6">
                            <input type="number" id="numero" value="1" min="1" step="1" class="form-control">
                            <input type="hidden" name="idReserva" id="idReserva" value="{{ $id }}">
                        </div>
                        <div class="col-3">
                            <button type="button" id="sumar" class="btn btn-secondary-modern w-100">+</button>
                        </div>
                        <div class="col-3">
                            <button type="button" id="restar" class="btn btn-secondary-modern w-100">-</button>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <button type="button" id="btnEnviarNumero" class="btn btn-modern">
                            <span class="loading d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            </span>
                            <span class="btn-text">Continuar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- BLOQUE 2: Formularios por persona --}}
    @if ($reserva->numero_personas != 0 || $reserva->numero_personas != null)
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12">
            <div class="text-center mb-4">
                <img src="https://apartamentosalgeciras.com/wp-content/uploads/2022/09/Logo-Hawkins-Suites.svg" alt="Hawkins Suites" class="img-fluid mb-3" style="max-width:300px;">
            </div>

            <div class="progress-bar-container">
                <div class="progress-bar" id="progressBar" style="width:0%"></div>
            </div>

            <div class="step-indicator">
                @for ($step = 1; $step <= $reserva->numero_personas; $step++)
                    <div class="step" id="step{{ $step }}">{{ $step }}</div>
                @endfor
            </div>

            <div class="form-card">
                <div class="form-header">
                    <h3><i class="fa-solid fa-id-card me-2"></i>{{ $textos['Inicio'] ?? 'Datos de los huéspedes' }}</h3>
                </div>

                @if (session('alerta'))
                    <div class="alert alert-warning m-4">{{ session('alerta') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger m-4">
                        <h5><i class="fa-solid fa-exclamation-triangle me-2"></i>Errores de validación:</h5>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="p-4">
                    @php $nacionalidadComun = $data[0]->nacionalidad ?? null; @endphp

                    <div id="formularios">
                        <form action="{{ route('dni.store') }}" method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" value="{{ $id }}">

                            @for ($i = 0; $i < $reserva->numero_personas; $i++)
                                <div class="person-form" id="personForm{{ $i }}" style="{{ $i>0 ? 'display:none;' : '' }}">
                                    <div class="text-center mb-4">
                                        <h4 class="text-dark">
                                            @if ($i == 0)
                                                <i class="fa-solid fa-user me-2"></i>{{ $textos['Huesped.Principal'] ?? 'Huésped Principal' }}
                                            @else
                                                <i class="fa-solid fa-user-plus me-2"></i>{{ ($textos['Acompañante'] ?? 'Acompañante') }} {{ $i }}
                                            @endif
                                        </h4>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input name="nombre_{{ $i }}" type="text" class="form-control" id="nombre_{{ $i }}" placeholder="{{ $textos['Nombre'] ?? 'Nombre' }}" value="{{ $data[$i]->nombre ?? '' }}" required>
                                                <label for="nombre_{{ $i }}">{{ $textos['Nombre'] ?? 'Nombre' }}</label>
                                                <div class="invalid-feedback">{{ $textos['nombre_obli'] ?? 'El nombre es obligatorio.' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input name="apellido1_{{ $i }}" type="text" class="form-control" id="apellido1_{{ $i }}" placeholder="{{ $textos['Primer.Apellido'] ?? 'Primer Apellido' }}" value="{{ $data[$i]->primer_apellido ?? ($data[$i]->apellido1 ?? '') }}" required>
                                                <label for="apellido1_{{ $i }}">{{ $textos['Primer.Apellido'] ?? 'Primer Apellido' }}</label>
                                                <div class="invalid-feedback">{{ $textos['apellido_obli'] ?? 'El primer apellido es obligatorio.' }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input name="apellido2_{{ $i }}" type="text" class="form-control" id="apellido2_{{ $i }}" placeholder="{{ $textos['Segundo.Apellido'] ?? 'Segundo Apellido' }}" value="{{ $data[$i]->segundo_apellido ?? ($data[$i]->apellido2 ?? '') }}">
                                                <label for="apellido2_{{ $i }}">{{ $textos['Segundo.Apellido'] ?? 'Segundo Apellido' }}</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input name="fecha_nacimiento_{{ $i }}" type="date" class="form-control" id="fecha_nacimiento_{{ $i }}" placeholder="{{ $textos['Fecha.Nacimiento'] ?? 'Fecha de Nacimiento' }}" value="{{ $data[$i]->fecha_nacimiento ?? '' }}" required>
                                                <label for="fecha_nacimiento_{{ $i }}">{{ $textos['Fecha.Nacimiento'] ?? 'Fecha de Nacimiento' }}</label>
                                                <div class="invalid-feedback">{{ $textos['fecha_naci_obli'] ?? 'La fecha de nacimiento es obligatoria.' }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <select name="nacionalidad_{{ $i }}" id="nacionalidad_{{ $i }}" class="form-select js-example-basic-single{{ $i }} nacionalidad" placeholder="{{ $textos['Pais'] ?? 'País' }}">
                                                    @foreach ($paises as $pais)
                                                        <option value="{{ $pais }}"
                                                            {{
                                                                (old('nacionalidad_'.$i) == $pais) ||
                                                                (empty(old('nacionalidad_'.$i)) && $data[$i]->nacionalidad ?? '' == $pais) ||
                                                                (empty(old('nacionalidad_'.$i)) && empty($data[$i]->nacionalidad ?? '') && $pais == 'España')
                                                                ? 'selected' : ''
                                                            }}>
                                                            {{ $pais }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <label for="nacionalidad_{{ $i }}">{{ $textos['Pais'] ?? 'País' }}</label>
                                                <div class="invalid-feedback">{{ $textos['pais_obli'] ?? 'El país es obligatorio.' }}</div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <select
                                                    name="tipo_documento_{{ $i }}"
                                                    id="tipo_documento_{{ $i }}"
                                                    class="form-select tipo-documento"
                                                    data-index="{{ $i }}"
                                                    placeholder="{{ $textos['Tipo.Documento'] ?? 'Tipo de Documento' }}"
                                                    required>
                                                    <option value="">{{ $textos['Tipo.Documento'] ?? 'Tipo de Documento' }}</option>
                                                    <option value="D" {{ 
                                                        (old('tipo_documento_'.$i)=='D' ? 'selected':'') || 
                                                        (empty(old('tipo_documento_'.$i)) && ($data[$i]->tipo_documento ?? '') == 'D' ? 'selected' : '') 
                                                    }}>{{ $textos['Dni'] ?? 'DNI' }}</option>
                                                    <option value="P" {{ 
                                                        (old('tipo_documento_'.$i)=='P' ? 'selected':'') || 
                                                        (empty(old('tipo_documento_'.$i)) && ($data[$i]->tipo_documento ?? '') == 'P' ? 'selected' : '') 
                                                    }}>{{ $textos['Pasaporte'] ?? 'Pasaporte' }}</option>
                                                    {{-- Otras letras si las necesitas:
                                                    <option value="C">Permiso Conducir</option>
                                                    <option value="X">Residencia UE</option>
                                                    <option value="N">NIE/TIE</option>
                                                    <option value="I">ID extranjera</option> --}}
                                                </select>
                                                <label for="tipo_documento_{{ $i }}">{{ $textos['Tipo.Documento'] ?? 'Tipo de Documento' }}</label>
                                                <div class="invalid-feedback">{{ $textos['tipo_obli'] ?? 'El tipo de documento es obligatorio.' }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input name="num_identificacion_{{ $i }}" type="text" class="form-control" id="num_identificacion_{{ $i }}" placeholder="{{ $textos['Numero.Identificacion'] ?? 'Número de Identificación' }}" value="{{ $data[$i]->num_identificacion ?? ($data[$i]->numero_identificacion ?? '') }}" required>
                                                <label for="num_identificacion_{{ $i }}">{{ $textos['Numero.Identificacion'] ?? 'Número de Identificación' }}</label>
                                                <div class="invalid-feedback">{{ $textos['numero_obli'] ?? 'El número es obligatorio.' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input
                                                    name="fecha_expedicion_doc_{{ $i }}"
                                                    type="date"
                                                    class="form-control"
                                                    id="fecha_expedicion_doc_{{ $i }}"
                                                    placeholder="{{ $textos['Fecha.Expedicion'] ?? 'Fecha de Expedición' }}"
                                                    value="{{ $data[$i]->fecha_expedicion ?? ($data[$i]->fecha_expedicion_doc ?? '') }}"
                                                    required>
                                                <label for="fecha_expedicion_doc_{{ $i }}">{{ $textos['Fecha.Expedicion'] ?? 'Fecha de Expedición' }}</label>
                                                <div class="invalid-feedback">{{ $textos['fecha_obli'] ?? 'La fecha es obligatoria.' }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <select name="sexo_{{ $i }}" id="sexo_{{ $i }}" class="form-select" placeholder="{{ $textos['Sexo'] ?? 'Sexo' }}" required>
                                                    <option value="">{{ $textos['Sexo'] ?? 'Sexo' }}</option>
                                                    <option value="Masculino" {{ 
                                                        (old('sexo_'.$i)=='Masculino' ? 'selected':'') || 
                                                        (empty(old('sexo_'.$i)) && ($data[$i]->sexo ?? '') == 'Masculino' ? 'selected' : '') 
                                                    }}>{{ $textos['Masculino'] ?? 'Masculino' }}</option>
                                                    <option value="Femenino" {{ 
                                                        (old('sexo_'.$i)=='Femenino' ? 'selected':'') || 
                                                        (empty(old('sexo_'.$i)) && ($data[$i]->sexo ?? '') == 'Femenino' ? 'selected' : '') 
                                                    }}>{{ $textos['Femenino'] ?? 'Femenino' }}</option>
                                                </select>
                                                <label for="sexo_{{ $i }}">{{ $textos['Sexo'] ?? 'Sexo' }}</label>
                                                <div class="invalid-feedback">{{ $textos['sexo_obli'] ?? 'El sexo es obligatorio.' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input name="email_{{ $i }}" type="email" class="form-control" id="email_{{ $i }}" placeholder="{{ $textos['Correo.Electronico'] ?? 'Correo Electrónico' }}" value="{{ $data[$i]->email ?? '' }}" required>
                                                <label for="email_{{ $i }}">{{ $textos['Correo.Electronico'] ?? 'Correo Electrónico' }}</label>
                                                <div class="invalid-feedback">{{ $textos['email_obli'] ?? 'El email es obligatorio.' }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- === UPLOADS (coinciden con el controlador) === --}}
                                    <div class="row mt-2">
                                        {{-- DNI (frontal/trasera) --}}
                                        <div class="col-12" id="dniUploaed_{{ $i }}" style="display:none;">
                                            <h5 class="mt-2">{{ $textos['Imagen.Frontal'] ?? 'Imagen frontal (DNI)' }}</h5>
                                            <div class="files mt-2">
                                                <input type="file" accept="image/*" capture="environment"
                                                       class="file-input doc-uploader"
                                                       name="fontal_{{ $i }}" id="fontal_{{ $i }}"
                                                       data-index="{{ $i }}" data-target="frontal">
                                                <button type="button" class="btn btn-secondary-modern w-100"
                                                        onclick="document.getElementById('fontal_{{ $i }}').click()">
                                                    <i class="fa-solid fa-camera me-2"></i>{{ $textos['Frontal'] ?? 'Subir frontal' }}
                                                </button>
                                                <img id="image-preview_frontal_{{ $i }}" style="max-width:100%;margin-top:10px;display:none;"/>
                                                <div id="status_frontal_{{ $i }}" class="mt-2 text-muted small"></div>
                                            </div>

                                            <h5 class="mt-3">{{ $textos['Imagen.Trasera'] ?? 'Imagen trasera (DNI)' }}</h5>
                                            <div class="files mt-2">
                                                <input type="file" accept="image/*" capture="environment"
                                                       class="file-input doc-uploader"
                                                       name="trasera_{{ $i }}" id="trasera_{{ $i }}"
                                                       data-index="{{ $i }}" data-target="trasera">
                                                <button type="button" class="btn btn-secondary-modern w-100"
                                                        onclick="document.getElementById('trasera_{{ $i }}').click()">
                                                    <i class="fa-solid fa-camera me-2"></i>{{ $textos['Trasera'] ?? 'Subir trasera' }}
                                                </button>
                                                <img id="image-preview_trasera_{{ $i }}" style="max-width:100%;margin-top:10px;display:none;"/>
                                                <div id="status_trasera_{{ $i }}" class="mt-2 text-muted small"></div>
                                            </div>
                                        </div>

                                        {{-- PASAPORTE --}}
                                        <div class="col-12" id="pasaporteUpload_{{ $i }}" style="display:none;">
                                            <h5 class="mt-2">{{ $textos['Imagen.Pasaporte'] ?? 'Imagen pasaporte' }}</h5>
                                            <div class="files mt-2">
                                                <input type="file" accept="image/*" capture="environment"
                                                       class="file-input doc-uploader"
                                                       name="pasaporte_{{ $i }}" id="pasaporte_{{ $i }}"
                                                       data-index="{{ $i }}" data-target="pasaporte">
                                                <button type="button" class="btn btn-secondary-modern w-100"
                                                        onclick="document.getElementById('pasaporte_{{ $i }}').click()">
                                                    <i class="fa-solid fa-camera me-2"></i>{{ $textos['Frontal'] ?? 'Subir pasaporte' }}
                                                </button>
                                                <img id="image-preview_pasaporte_{{ $i }}" style="max-width:65%;margin-top:10px;display:none;"/>
                                                <div id="status_pasaporte_{{ $i }}" class="mt-2 text-muted small"></div>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- === /UPLOADS === --}}

                                    <div class="text-center mt-4">
                                        @if($i>0)
                                            <button type="button" class="btn btn-secondary-modern me-3" onclick="previousPerson({{ $i }})">
                                                <i class="fa-solid fa-arrow-left me-2"></i>Anterior
                                            </button>
                                        @endif
                                        @if($i < $reserva->numero_personas - 1)
                                            <button type="button" class="btn btn-modern" onclick="nextPerson({{ $i }})">
                                                Siguiente<i class="fa-solid fa-arrow-right ms-2"></i>
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-modern" id="btnSubmitForm">
                                                <span class="spinner-border spinner-border-sm me-2 d-none" id="spinnerSubmit" role="status" aria-hidden="true"></span>
                                                <span class="btn-text">{{ $textos['Enviar'] ?? 'Enviar' }}</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endfor
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // --- Utilidad: notificaciones de error
    function showError(message){
        const n = $(`
            <div class="error-notification">
                <i class="fas fa-exclamation-triangle"></i>
                <span>${message}</span>
                <button class="close-notification">&times;</button>
            </div>
        `);
        $('body').append(n);
        n.fadeIn(200);
        setTimeout(()=>n.fadeOut(300,()=>n.remove()),5000);
        n.find('.close-notification').on('click',()=>n.fadeOut(200,()=>n.remove()));
    }

    // --- Cambio de idioma (usado por #idioma y #globalIdioma)
    function cambiarIdioma(idioma){
        if(!idioma) return;
        const flag = {es:'🇪🇸',en:'🇺🇸',fr:'🇫🇷',de:'🇩🇪',it:'🇮🇹',pt:'🇵🇹'}[idioma] || '🌐';
        $('#flagIcon').text(flag);

        const overlay = $('<div class="transition-overlay"></div>').hide().appendTo('body').fadeIn(150);

        $.ajax({
            url: '{{ route("dni.cambiarIdioma") }}',
            type: 'POST',
            data: { idioma, token: '{{ $reserva->token ?? "" }}', _token: '{{ csrf_token() }}' },
            success: function(r){
                if(r && r.success){ setTimeout(()=> window.location.href = r.redirect || window.location.href, 250); }
                else{ overlay.fadeOut(150,()=>overlay.remove()); showError('Error al cambiar el idioma'); }
            },
            error: function(){ overlay.fadeOut(150,()=>overlay.remove()); showError('Error al cambiar el idioma'); }
        });
    }

    // --- Nº de personas (+/- y envío)
    $(document).on('click','#sumar',function(e){ e.preventDefault(); const $i=$('#numero'); let v=parseInt($i.val(),10)||1; $i.val(v+1); });
    $(document).on('click','#restar',function(e){ e.preventDefault(); const $i=$('#numero'); let v=parseInt($i.val(),10)||1; $i.val(Math.max(1,v-1)); });

    $(document).on('click','#btnEnviarNumero',function(){
        const numero = parseInt($('#numero').val(),10) || 1;
        const idReserva = $('#idReserva').val();
        const $btn = $(this);
        $btn.find('.loading').removeClass('d-none'); $btn.prop('disabled', true);

        $.ajax({
            url: '{{ route("dni.storeNumeroPersonas") }}',
            method: 'POST',
            data: { numero, idReserva, _token: '{{ csrf_token() }}' },
            success: function(r){ if(r && r.success){ window.location.reload(); } else{ $btn.find('.loading').addClass('d-none'); $btn.prop('disabled', false); showError('No se pudo actualizar el número de personas'); } },
            error: function(){ $btn.find('.loading').addClass('d-none'); $btn.prop('disabled', false); showError('Error al actualizar el número de personas'); }
        });
    });

    // --- Wizard personas
    let currentPerson = 0;
    const totalPersons = {{ (int)($reserva->numero_personas ?? 0) }};

    function updateProgress(){
        if(!totalPersons) return;
        const pct = ((currentPerson+1)/totalPersons)*100;
        $('#progressBar').css('width', pct+'%');
        for(let i=0;i<totalPersons;i++){
            const $s = $('#step'+(i+1));
            if(i<currentPerson) $s.removeClass('active').addClass('completed');
            else if(i===currentPerson) $s.removeClass('completed').addClass('active');
            else $s.removeClass('active completed');
        }
    }
    function nextPerson(i){
        if(validatePersonForm(i)){
            $('#personForm'+i).hide(); $('#personForm'+(i+1)).show();
            currentPerson=i+1; updateProgress();
        }
    }
    function previousPerson(i){
        $('#personForm'+i).hide(); $('#personForm'+(i-1)).show();
        currentPerson=i-1; updateProgress();
    }
    window.nextPerson = nextPerson;
    window.previousPerson = previousPerson;

    function validatePersonForm(i){
        const req = ['nombre_','apellido1_','fecha_nacimiento_','nacionalidad_','tipo_documento_','num_identificacion_','fecha_expedicion_doc_','sexo_','email_'].map(p=>p+i);
        let ok = true;
        req.forEach(id=>{
            const el = document.getElementById(id);
            if(el && !String(el.value||'').trim()){ el.classList.add('is-invalid'); ok=false; }
            else if(el){ el.classList.remove('is-invalid'); el.classList.add('is-valid'); }
        });
        
        // Los archivos NO son obligatorios, solo validamos campos de texto
        console.log(`Validando campos de texto para persona ${i}`);
        
        if(!ok) showError('Por favor, completa todos los campos obligatorios');
        return ok;
    }

    // --- Uploads y previsualización (coincidiendo con el controlador)
    function toggleUploads(index){
        const tipo = $('#tipo_documento_'+index).val();
        const $dni  = $('#dniUploaed_'+index);
        const $pass = $('#pasaporteUpload_'+index);

        console.log(`toggleUploads para persona ${index}, tipo:`, tipo);

        if (tipo === 'P') { // Pasaporte
            console.log(`Configurando pasaporte para persona ${index}`);
            $dni.hide();  $pass.show();
            $('#fontal_'+index).prop('required', false);
            $('#trasera_'+index).prop('required', false);
            $('#pasaporte_'+index).prop('required', false); // NO obligatorio
            $('#required_frontal_'+index).hide();
            $('#required_trasera_'+index).hide();
            $('#required_pasaporte_'+index).hide(); // Sin asterisco
        } else if (tipo) { // Cualquier otro (D,C,X,N,I...) => DNI
            console.log(`Configurando DNI para persona ${index}`);
            $dni.show();  $pass.hide();
            $('#fontal_'+index).prop('required', false); // NO obligatorio
            $('#trasera_'+index).prop('required', false); // NO obligatorio
            $('#pasaporte_'+index).prop('required', false);
            $('#required_frontal_'+index).hide(); // Sin asterisco
            $('#required_trasera_'+index).hide(); // Sin asterisco
            $('#required_pasaporte_'+index).hide();
        } else {
            console.log(`Sin tipo de documento para persona ${index}, ocultando uploads`);
            $dni.hide();  $pass.hide();
            $('#fontal_'+index).prop('required', false);
            $('#trasera_'+index).prop('required', false);
            $('#pasaporte_'+index).prop('required', false);
            $('#required_frontal_'+index).hide();
            $('#required_trasera_'+index).hide();
            $('#required_pasaporte_'+index).hide();
        }
    }
    $(document).on('change','.tipo-documento',function(){ toggleUploads($(this).data('index')); });

    function previewDoc(fileInput, index, target){
        const file = fileInput.files && fileInput.files[0];
        if(!file) return;
        
        // Validar tamaño del archivo (5MB máximo)
        const maxSize = 5 * 1024 * 1024; // 5MB en bytes
        if (file.size > maxSize) {
            showError(`El archivo ${file.name} es demasiado grande. El tamaño máximo permitido es 5MB.`);
            fileInput.value = ''; // Limpiar el input
            return;
        }
        
        // Validar tipo de archivo
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            showError(`El archivo ${file.name} no es un formato válido. Solo se permiten archivos JPEG, JPG o PNG.`);
            fileInput.value = ''; // Limpiar el input
            return;
        }
        
        console.log(`Archivo ${target} para persona ${index}:`, {
            name: file.name,
            size: file.size,
            type: file.type,
            sizeMB: (file.size / (1024 * 1024)).toFixed(2) + 'MB'
        });
        
        // Actualizar estado del archivo
        const statusId = `status_${target}_${index}`;
        const statusEl = document.getElementById(statusId);
        if (statusEl) {
            statusEl.innerHTML = `<i class="fa-solid fa-check-circle text-success"></i> Archivo seleccionado: ${file.name} (${(file.size / (1024 * 1024)).toFixed(2)}MB)`;
        }
        
        const reader = new FileReader();
        reader.onload = function(e){
            const id = {
                frontal:   'image-preview_frontal_'+index,
                trasera:   'image-preview_trasera_'+index,
                pasaporte: 'image-preview_pasaporte_'+index
            }[target];
            const img = document.getElementById(id);
            if(img){ img.src = e.target.result; img.style.display = 'block'; }
        };
        reader.readAsDataURL(file);
    }
    $(document).on('change','input.doc-uploader',function(){
        previewDoc(this, $(this).data('index'), $(this).data('target'));
    });

    // --- Init
    $(function(){
        // Mostrar card nº personas cuando ya hay idioma
        @if(isset($cliente) && ($cliente->idioma_establecido ?? false))
            $('#cardNumeroPersonas').show(); $('#cardIdioma').hide();
        @else
            if($('#idioma').val()){ $('#cardNumeroPersonas').show(); $('#cardIdioma').hide(); }
        @endif

        // Init Select2 (retry si aún no cargó)
        function initSelect2(){
            if(typeof $.fn.select2 === 'undefined'){ setTimeout(initSelect2,100); return; }
            for(let i=0;i<totalPersons;i++){
                try{ $('.js-example-basic-single'+i).select2({width:'100%'}); }catch(e){}
            }
            
            // Después de inicializar Select2, configurar uploads
            setTimeout(() => {
                console.log('Inicializando uploads después de Select2');
                for(let i=0;i<totalPersons;i++){ 
                    console.log(`Inicializando uploads para persona ${i}`);
                    toggleUploads(i); 
                }
            }, 100);
        }
        initSelect2();

        @for ($j = 0; $j < ($reserva->numero_personas ?? 0); $j++)
            @if(isset($data[$j]))
                @if(isset($data[$j]->frontal) && isset($data[$j]->frontal->url))
                    (function(){ const img=document.getElementById('image-preview_frontal_{{ $j }}'); if(img){ img.src='/{{ $data[$j]->frontal->url }}'; img.style.display='block'; }})();
                @endif
                @if(isset($data[$j]->trasera) && isset($data[$j]->trasera->url))
                    (function(){ const img=document.getElementById('image-preview_trasera_{{ $j }}'); if(img){ img.src='/{{ $data[$j]->trasera->url }}'; img.style.display='block'; }})();
                @endif
                @if(isset($data[$j]->pasaporte) && isset($data[$j]->pasaporte->url))
                    (function(){ const img=document.getElementById('image-preview_pasaporte_{{ $j }}'); if(img){ img.src='/{{ $data[$j]->pasaporte->url }}'; img.style.display='block'; }})();
                @endif
            @endif
        @endfor

        updateProgress();

        // Validación Bootstrap
        $('.needs-validation').on('submit', function (e) {
            console.log('=== INICIO VALIDACIÓN FORMULARIO ===');
            console.log('Formulario enviándose...');
            console.log('Form validity:', this.checkValidity());
            
            // Verificar archivos
            const formData = new FormData(this);
            console.log('FormData entries:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}:`, value);
            }
            
            // Verificar archivos específicos
            const fileInputs = this.querySelectorAll('input[type="file"]');
            console.log('File inputs encontrados:', fileInputs.length);
            fileInputs.forEach((input, index) => {
                console.log(`File input ${index}:`, {
                    name: input.name,
                    files: input.files.length,
                    required: input.required,
                    value: input.value
                });
                if (input.files.length > 0) {
                    console.log(`Archivo ${index}:`, {
                        name: input.files[0].name,
                        size: input.files[0].size,
                        type: input.files[0].type
                    });
                }
            });
            
            // Validar cada persona
            let allValid = true;
            for(let i = 0; i < totalPersons; i++) {
                if (!validatePersonForm(i)) {
                    allValid = false;
                }
            }
            
            if(!this.checkValidity() || !allValid){ 
                console.log('Formulario no válido, previniendo envío');
                e.preventDefault(); 
                e.stopPropagation(); 
            } else {
                console.log('Formulario válido, enviando...');
            }
            $(this).addClass('was-validated');
            $('#spinnerSubmit').removeClass('d-none');
        });
    });
</script>
@endsection
