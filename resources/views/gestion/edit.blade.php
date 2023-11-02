@extends('layouts.appPersonal')

@section('title')
{{ __('Realizando el Apartamento - ') . $apartamentoLimpieza->apartamento->nombre}}
@endsection

@section('bienvenido')
    <h5 class="navbar-brand mb-0 w-auto text-center text-white">Bienvenid@ {{Auth::user()->name}}</h5>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-color-segundo">
                    <i class="fa-solid fa-spray-can-sparkles"></i>
                    <span class="ms-2 text-uppercase fw-bold">{{ __('Apartamento - ') .  $apartamentoLimpieza->apartamento->nombre}}</span>
                </div>
                <div class="card-body">
                    <form action="{{route('gestion.update',$apartamentoLimpieza )}}" method="POST">
                        @csrf
                        <input data-id="{{$apartamentoLimpieza->id}}" type="hidden" name="id" value="{{$apartamentoLimpieza->id}}">
                        <div class="fila">
                            <div class="header_sub mb-3">
                                <div class="row bg-color-quinto m-1 text-white align-items-center">
                                    <div class="col-7">
                                        <h3 class="titulo mb-0">DORMITORIO</h3>
                                    </div>
                                    <div class="col-5">
                                        <div class="form-check form-switch mt-2 mb-2 d-flex w-100 justify-content-evenly">
                                            <input data-id="{{$apartamentoLimpieza->id}}" {{ old('dormitorio', $apartamentoLimpieza->dormitorio) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="dormitorio" name="dormitorio">
                                            <label class="form-check-label" for="dormitorio"></label>
                                            <a @if( $apartamentoLimpieza->dormitorio === 1) style="display:block" @else style="display:none" @endif id="camaraDormitorio" href="{{route('fotos.dormitorio', $apartamentoLimpieza->id)}}" class="btn btn-foto"><i class="fa-solid fa-camera"></i></a>                                                
                                        </div>  
                                </div>
                            </div>
                            
                            <div class="content-check mx-2">
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('dormitorio_sabanas', $apartamentoLimpieza->dormitorio_sabanas) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="dormitorio_sabanas" name="dormitorio_sabanas">
                                    <label class="form-check-label" for="dormitorio_sabanas">Sabanas</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('dormitorio_cojines', $apartamentoLimpieza->dormitorio_cojines) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="dormitorio_cojines" name="dormitorio_cojines">
                                    <label class="form-check-label" for="dormitorio_cojines">Cojines (4 uds)</label>
                                </div>
            
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('dormitorio_edredon', $apartamentoLimpieza->dormitorio_edredon) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="dormitorio_edredon" name="dormitorio_edredon">
                                    <label class="form-check-label" for="dormitorio_edredon">Edredrón</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('dormitorio_funda_edredon', $apartamentoLimpieza->dormitorio_funda_edredon) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="dormitorio_funda_edredon" name="dormitorio_funda_edredon">
                                    <label class="form-check-label" for="dormitorio_funda_edredon">Funda de edredrón</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('dormitorio_canape', $apartamentoLimpieza->dormitorio_canape) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="dormitorio_canape" name="dormitorio_canape">
                                    <label class="form-check-label" for="dormitorio_canape">Canapé</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('dormitorio_manta_cubrepies', $apartamentoLimpieza->dormitorio_manta_cubrepies) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="dormitorio_manta_cubrepies" name="dormitorio_manta_cubrepies">
                                    <label class="form-check-label" for="dormitorio_manta_cubrepies">Manta gris</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('dormitorio_papel_plancha', $apartamentoLimpieza->dormitorio_papel_plancha) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="dormitorio_papel_plancha" name="dormitorio_papel_plancha">
                                    <label class="form-check-label" for="dormitorio_papel_plancha">Papel plancha</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('dormitorio_toallas_rulo', $apartamentoLimpieza->dormitorio_toallas_rulo) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="dormitorio_toallas_rulo" name="dormitorio_toallas_rulo">
                                    <label class="form-check-label" for="dormitorio_toallas_rulo">Toallas Rulo (2 uds.)</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('dormitorio_revision_pelos', $apartamentoLimpieza->dormitorio_revision_pelos) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="dormitorio_revision_pelos" name="dormitorio_revision_pelos">
                                    <label class="form-check-label" for="dormitorio_revision_pelos">Revisión Pelos</label>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="fila">
                            <div class="header_sub mb-3">
                                <div class="row bg-color-quinto m-1 text-white align-items-center">
                                    <div class="col-8">
                                        <h3 class="titulo mb-0">ARMARIO</h3>
                                    </div>
                                    <div class="col-4">
                                        <div class="form-check form-switch mt-2 mb-2 d-flex w-100 justify-content-evenly">
                                            <input data-id="{{$apartamentoLimpieza->id}}" {{ old('armario', $apartamentoLimpieza->armario) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="armario" name="armario">
                                            <label class="form-check-label" for="armario"></label>
                                            @if ($apartamentoLimpieza->armario)
                                                <a id="camaraArmario" @if ($apartamentoLimpieza->armario === 1 ) style="display:block" @else style="display:none" @endif href="{{route('fotos.dormitorio', $apartamentoLimpieza->id)}}" class="btn btn-foto"><i class="fa-solid fa-camera"></i></a>                                                
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="content-check mx-2">
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('armario_perchas', $apartamentoLimpieza->armario_perchas) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="armario_perchas" name="armario_perchas">
                                    <label class="form-check-label" for="armario_perchas">Perchas(5 uds.)</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('armario_almohada_repuesto_sofa', $apartamentoLimpieza->armario_almohada_repuesto_sofa) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="armario_almohada_repuesto_sofa" name="armario_almohada_repuesto_sofa">
                                    <label class="form-check-label" for="armario_almohada_repuesto_sofa">Almohada de repuesto sofá</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('armario_edredon_repuesto_sofa', $apartamentoLimpieza->armario_edredon_repuesto_sofa) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="armario_edredon_repuesto_sofa" name="armario_edredon_repuesto_sofa">
                                    <label class="form-check-label" for="armario_edredon_repuesto_sofa">Edredón de respuesto sofá</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('armario_funda_repuesto_edredon', $apartamentoLimpieza->armario_funda_repuesto_edredon) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="armario_funda_repuesto_edredon" name="armario_funda_repuesto_edredon">
                                    <label class="form-check-label" for="armario_funda_repuesto_edredon">Funda de repuesto edred.</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('armario_sabanas_repuesto', $apartamentoLimpieza->armario_sabanas_repuesto) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="armario_sabanas_repuesto" name="armario_sabanas_repuesto">
                                    <label class="form-check-label" for="armario_sabanas_repuesto">Sábanas de repuesto</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('armario_plancha', $apartamentoLimpieza->armario_plancha) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="armario_plancha" name="armario_plancha">
                                    <label class="form-check-label" for="armario_plancha">Plancha</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('armario_tabla_plancha', $apartamentoLimpieza->armario_tabla_plancha) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="armario_tabla_plancha" name="armario_tabla_plancha">
                                    <label class="form-check-label" for="armario_tabla_plancha">Ambientador</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('armario_toalla', $apartamentoLimpieza->armario_toalla) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="armario_toalla" name="armario_toalla">
                                    <label class="form-check-label" for="armario_toalla">Toallas</label>
                                </div>                               

                            </div>
                        </div>
                        <hr>
                        <div class="fila">
                            <div class="header_sub mb-3">
                                <div class="row bg-color-quinto m-1 text-white align-items-center">
                                    <div class="col-8">
                                        <h3 class="titulo mb-0">CANAPE</h3>
                                    </div>
                                    <div class="col-4">
                                        <div class="form-check form-switch mt-2 mb-2 d-flex w-100 justify-content-evenly">
                                            <input data-id="{{$apartamentoLimpieza->id}}" {{ old('canape', $apartamentoLimpieza->canape) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="canape" name="canape">
                                            <label class="form-check-label" for="canape"></label>
                                            @if ($apartamentoLimpieza->canape)
                                                <a href="{{route('fotos.dormitorio', $apartamentoLimpieza->id)}}" class="btn btn-foto"><i class="fa-solid fa-camera"></i></a>                                                
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="content-check mx-2">
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('canape_almohada', $apartamentoLimpieza->canape_almohada) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="canape_almohada" name="canape_almohada">
                                    <label class="form-check-label" for="canape_almohada">Almohada</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('canape_gel', $apartamentoLimpieza->canape_gel) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="canape_gel" name="canape_gel">
                                    <label class="form-check-label" for="canape_gel">Gel</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('canape_sabanas', $apartamentoLimpieza->canape_sabanas) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="canape_sabanas" name="canape_sabanas">
                                    <label class="form-check-label" for="canape_sabanas">Sabanas</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('canape_toallas', $apartamentoLimpieza->canape_toallas) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="canape_toallas" name="canape_toallas">
                                    <label class="form-check-label" for="canape_toallas">2 Toallas</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('canape_papel_wc', $apartamentoLimpieza->canape_papel_wc) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="canape_papel_wc" name="canape_papel_wc">
                                    <label class="form-check-label" for="canape_papel_wc">Papel WC</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('canape_estropajo', $apartamentoLimpieza->canape_estropajo) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="canape_estropajo" name="canape_estropajo">
                                    <label class="form-check-label" for="canape_estropajo">Estropajo</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('canape_bayeta', $apartamentoLimpieza->canape_bayeta) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="canape_bayeta" name="canape_bayeta">
                                    <label class="form-check-label" for="canape_bayeta">Bayeta</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('canape_antihumedad', $apartamentoLimpieza->canape_antihumedad) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="canape_antihumedad" name="canape_antihumedad">
                                    <label class="form-check-label" for="canape_antihumedad">Antihumedad</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('canape_ambientador', $apartamentoLimpieza->canape_ambientador) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="canape_ambientador" name="canape_ambientador">
                                    <label class="form-check-label" for="canape_ambientador">Ambientador</label>
                                </div>                                

                            </div>
                        </div>
                        <hr>
                        <div class="fila">
                            <div class="header_sub mb-3">
                                <div class="row bg-color-quinto m-1 text-white align-items-center">
                                    <div class="col-8">
                                        <h3 class="titulo mb-0">SALÓN</h3>
                                    </div>
                                    <div class="col-4">
                                        <div class="form-check form-switch mt-2 mb-2 d-flex w-100 justify-content-evenly">
                                            <input data-id="{{$apartamentoLimpieza->id}}" {{ old('salon', $apartamentoLimpieza->salon) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="salon" name="salon">
                                            <label class="form-check-label" for="salon"></label>
                                            <a @if($apartamentoLimpieza->salon === 1) style="display:block" @else style="display:none" @endif id="camaraSalon" href="{{route('fotos.salon', $apartamentoLimpieza->id)}}" class="btn btn-foto"><i class="fa-solid fa-camera"></i></a>                                                
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="content-check mx-2">
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('salon_cojines', $apartamentoLimpieza->salon_cojines) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="salon_cojines" name="salon_cojines">
                                    <label class="form-check-label" for="salon_cojines">Cojines (2 uds)</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('salon_sofa_cama', $apartamentoLimpieza->salon_sofa_cama) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="salon_sofa_cama" name="salon_sofa_cama">
                                    <label class="form-check-label" for="salon_sofa_cama">Sofá cama daño o sucio</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('salon_planta_cesta', $apartamentoLimpieza->salon_planta_cesta) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="salon_planta_cesta" name="salon_planta_cesta">
                                    <label class="form-check-label" for="salon_planta_cesta">Planta con cesta</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('salon_mandos', $apartamentoLimpieza->salon_mandos) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="salon_mandos" name="salon_mandos">
                                    <label class="form-check-label" for="salon_mandos">Mando (TV y Aire Acondicionado)</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('salon_tv', $apartamentoLimpieza->salon_tv) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="salon_tv" name="salon_tv">
                                    <label class="form-check-label" for="salon_tv">Probar TV (Encender y apagar)</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('salon_cortinas', $apartamentoLimpieza->salon_cortinas) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="salon_cortinas" name="salon_cortinas">
                                    <label class="form-check-label" for="salon_cortinas">Cortinas (Limpias y bien engachadas)</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('salon_sillas', $apartamentoLimpieza->salon_sillas) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="salon_sillas" name="salon_sillas">
                                    <label class="form-check-label" for="salon_sillas">Sillas y mesa</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('salon_salvamanteles', $apartamentoLimpieza->salon_salvamanteles) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="salon_salvamanteles" name="salon_salvamanteles">
                                    <label class="form-check-label" for="salon_salvamanteles">Salvamanteles (2 uds)</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('salon_estanteria', $apartamentoLimpieza->salon_estanteria) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="salon_estanteria" name="salon_estanteria">
                                    <label class="form-check-label" for="salon_estanteria">Estantería</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('salon_decoracion', $apartamentoLimpieza->salon_decoracion) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="salon_decoracion" name="salon_decoracion">
                                    <label class="form-check-label" for="salon_decoracion">Decoración</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('salon_ambientador', $apartamentoLimpieza->salon_ambientador) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="salon_ambientador" name="salon_ambientador">
                                    <label class="form-check-label" for="salon_ambientador">Ambientador</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('salon_libros_juego', $apartamentoLimpieza->salon_libros_juego) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="salon_libros_juego" name="salon_libros_juego">
                                    <label class="form-check-label" for="salon_libros_juego">Libros y juegos</label>
                                </div>                                

                            </div>
                        </div>
                        <hr>
                        <div class="fila">
                            <div class="header_sub mb-3">
                                <div class="row bg-color-quinto m-1 text-white align-items-center">
                                    <div class="col-8">
                                        <h3 class="titulo mb-0">COCINA</h3>
                                    </div>
                                    <div class="col-4">
                                        <div class="form-check form-switch mt-2 mb-2 d-flex w-100 justify-content-evenly">
                                            <input data-id="{{$apartamentoLimpieza->id}}" {{ old('cocina', $apartamentoLimpieza->cocina) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="cocina" name="cocina">
                                            <label class="form-check-label" for="cocina"></label>
                                            {{$apartamentoLimpieza->cocina_photo}}
                                            <a @if( $apartamentoLimpieza->cocina === 1) style="display:block" @else style="display:none" @endif id="camaraCocina" href="{{route('fotos.cocina', $apartamentoLimpieza->id)}}" class="btn btn-foto"><i class="fa-solid fa-camera"></i></a>                                                
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="content-check mx-2">
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('cocina_vitroceramica', $apartamentoLimpieza->cocina_vitroceramica) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="cocina_vitroceramica" name="cocina_vitroceramica">
                                    <label class="form-check-label" for="cocina_vitroceramica">Vitrocerámica</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('cocina_vajilla', $apartamentoLimpieza->cocina_vajilla) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="cocina_vajilla" name="cocina_vajilla">
                                    <label class="form-check-label" for="cocina_vajilla">Vajilla</label>
                                </div>
            
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('cocina_vasos', $apartamentoLimpieza->cocina_vasos) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="cocina_vasos" name="cocina_vasos">
                                    <label class="form-check-label" for="cocina_vasos">Vasos (4 uds)</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('cocina_tazas', $apartamentoLimpieza->cocina_tazas) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="cocina_tazas" name="cocina_tazas">
                                    <label class="form-check-label" for="cocina_tazas">Tazas (4 uds)</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('cocina_tapadera', $apartamentoLimpieza->cocina_tapadera) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="cocina_tapadera" name="cocina_tapadera">
                                    <label class="form-check-label" for="cocina_tapadera">Tapadera (2 uds)</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('cocina_sartenes', $apartamentoLimpieza->cocina_sartenes) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="cocina_sartenes" name="cocina_sartenes">
                                    <label class="form-check-label" for="cocina_sartenes">Sartenes (2 uds)</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('cocina_paño_cocina', $apartamentoLimpieza->cocina_paño_cocina) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="cocina_paño_cocina" name="cocina_paño_cocina">
                                    <label class="form-check-label" for="cocina_paño_cocina">Paño cocina (1 ud)</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('cocina_cuberteria', $apartamentoLimpieza->cocina_cuberteria) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="cocina_cuberteria" name="cocina_cuberteria">
                                    <label class="form-check-label" for="cocina_cuberteria">Cubertería (4 uds)</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('cocina_cuchillo', $apartamentoLimpieza->cocina_cuchillo) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="cocina_cuchillo" name="cocina_cuchillo">
                                    <label class="form-check-label" for="cocina_cuchillo">Cuchillo (1 ud)</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('cocina_ollas', $apartamentoLimpieza->cocina_ollas) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="cocina_ollas" name="cocina_ollas">
                                    <label class="form-check-label" for="cocina_ollas">Ollas</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('cocina_papel_cocina', $apartamentoLimpieza->cocina_papel_cocina) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="cocina_papel_cocina" name="cocina_papel_cocina">
                                    <label class="form-check-label" for="cocina_papel_cocina">Papel de cocina</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('cocina_tapadera_micro', $apartamentoLimpieza->cocina_tapadera_micro) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="cocina_tapadera_micro" name="cocina_tapadera_micro">
                                    <label class="form-check-label" for="cocina_tapadera_micro">Tapadera Micro</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('cocina_estropajo', $apartamentoLimpieza->cocina_estropajo) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="cocina_estropajo" name="cocina_estropajo">
                                    <label class="form-check-label" for="cocina_estropajo">Estropajo/Bayeta</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('cocina_mistol', $apartamentoLimpieza->cocina_mistol) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="cocina_mistol" name="cocina_mistol">
                                    <label class="form-check-label" for="cocina_mistol">Mistol</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('cocina_tostadora', $apartamentoLimpieza->cocina_tostadora) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="cocina_tostadora" name="cocina_tostadora">
                                    <label class="form-check-label" for="cocina_tostadora">Tostadora</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('cocina_bolsa_basura', $apartamentoLimpieza->cocina_bolsa_basura) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="cocina_bolsa_basura" name="cocina_bolsa_basura">
                                    <label class="form-check-label" for="cocina_bolsa_basura">Bolsa de basura</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('cocina_tabla_cortar', $apartamentoLimpieza->cocina_tabla_cortar) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="cocina_tabla_cortar" name="cocina_tabla_cortar">
                                    <label class="form-check-label" for="cocina_tabla_cortar">Tabla de cortar</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('cocina_escurreplatos', $apartamentoLimpieza->cocina_escurreplatos) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="cocina_escurreplatos" name="cocina_escurreplatos">
                                    <label class="form-check-label" for="cocina_escurreplatos">Escurreplatos</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('cocina_bol_escurridor', $apartamentoLimpieza->cocina_bol_escurridor) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="cocina_bol_escurridor" name="cocina_bol_escurridor">
                                    <label class="form-check-label" for="cocina_bol_escurridor">Bol y Escurridorr</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('cocina_utensilios_cocina', $apartamentoLimpieza->cocina_utensilios_cocina) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="cocina_utensilios_cocina" name="cocina_utensilios_cocina">
                                    <label class="form-check-label" for="cocina_utensilios_cocina">Utensilios(Pinza, cucharón y espumadera)</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" {{ old('cocina_dolcegusto', $apartamentoLimpieza->cocina_dolcegusto) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="cocina_dolcegusto" name="cocina_dolcegusto">
                                    <label class="form-check-label" for="cocina_dolcegusto">Dolcegusto (3 Capsulas)</label>
                                </div>

                            </div>
                        </div>
                        <hr>
                        <div class="fila">
                            <div class="header_sub mb-3">
                                <div class="row bg-color-quinto m-1 text-white align-items-center">
                                    <div class="col-8">
                                        <h3 class="titulo mb-0">BAÑO</h3>
                                    </div>
                                    <div class="col-4">
                                        <div class="form-check form-switch mt-2 mb-2 d-flex w-100 justify-content-evenly">
                                            <input data-id="{{$apartamentoLimpieza->id}}" {{ old('bano', $apartamentoLimpieza->bano) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="bano" name="bano">
                                            <label class="form-check-label" for="bano"></label>
                                            <a @if($apartamentoLimpieza->bano === 1) style="display:block" @else style="display:none" @endif href="{{route('fotos.banio', $apartamentoLimpieza->id)}}" class="btn btn-foto" id="camaraBano"><i class="fa-solid fa-camera"></i></a>                                                
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="content-check mx-2">
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" class="form-check-input" type="checkbox" id="bano_toallas_aseos" name="bano_toallas_aseos" {{ old('bano_toallas_aseos', $apartamentoLimpieza->bano_toallas_aseos) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="bano_toallas_aseos">Toallas de Baño (2 uds)</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" class="form-check-input" type="checkbox" id="bano_toallas_mano" name="bano_toallas_mano" {{ old('bano_toallas_mano', $apartamentoLimpieza->bano_toallas_mano) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="bano_toallas_mano">Toallas mano (1 ud)</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" class="form-check-input" type="checkbox" id="bano_alfombra" name="bano_alfombra" {{ old('bano_alfombra', $apartamentoLimpieza->bano_alfombra) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="bano_alfombra">Alfombra (1 ud)</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" class="form-check-input" type="checkbox" id="bano_secador" name="bano_secador" {{ old('bano_secador', $apartamentoLimpieza->bano_secador) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="bano_secador">Secador</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" class="form-check-input" type="checkbox" id="bano_papel" name="bano_papel" {{ old('bano_papel', $apartamentoLimpieza->bano_papel) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="bano_papel">Papel</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" class="form-check-input" type="checkbox" id="bano_rellenar_gel" name="bano_rellenar_gel" {{ old('bano_rellenar_gel', $apartamentoLimpieza->bano_rellenar_gel) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="bano_rellenar_gel">Gel</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" class="form-check-input" type="checkbox" id="bano_espejo" name="bano_espejo" {{ old('bano_espejo', $apartamentoLimpieza->bano_espejo) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="bano_espejo">Espejo</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" class="form-check-input" type="checkbox" id="bano_ganchos" name="bano_ganchos" {{ old('bano_ganchos', $apartamentoLimpieza->bano_ganchos) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="bano_ganchos">Ganchos puerta (2 uds)</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" class="form-check-input" type="checkbox" id="bano_muebles" name="bano_muebles" {{ old('bano_muebles', $apartamentoLimpieza->bano_muebles) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="bano_muebles">Revisar mueble</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" class="form-check-input" type="checkbox" id="bano_desague" name="bano_desague" {{ old('bano_desague', $apartamentoLimpieza->bano_desague) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="bano_desague">Revisar desagüe (pelos)</label>
                                </div>                                

                            </div>
                        </div>
                        <hr>
                        <div class="fila">
                            <div class="header_sub mb-3">
                                <div class="row bg-color-quinto m-1 text-white align-items-center">
                                    <div class="col-8">
                                        <h3 class="titulo mb-0">AMENITIES</h3>
                                    </div>
                                    <div class="col-4">
                                        <div class="form-check form-switch mt-2 mb-2 d-flex w-100 justify-content-evenly">
                                            <input data-id="{{$apartamentoLimpieza->id}}" {{ old('amenities', $apartamentoLimpieza->amenities) ? 'checked' : '' }} class="form-check-input" type="checkbox" id="amenities" name="amenities">
                                            <label class="form-check-label" for="amenities"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="content-check mx-2">
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" class="form-check-input" type="checkbox" id="amenities_nota_agradecimiento" name="amenities_nota_agradecimiento" {{ old('amenities_nota_agradecimiento', $apartamentoLimpieza->amenities_nota_agradecimiento) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="amenities_nota_agradecimiento">Nota de agradecimiento</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" class="form-check-input" type="checkbox" id="amenities_magdalenas" name="amenities_magdalenas" {{ old('amenities_magdalenas', $apartamentoLimpieza->amenities_magdalenas) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="amenities_magdalenas">3 magdalenas</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input data-id="{{$apartamentoLimpieza->id}}" class="form-check-input" type="checkbox" id="amenities_caramelos" name="amenities_caramelos" {{ old('amenities_caramelos', $apartamentoLimpieza->amenities_caramelos) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="amenities_caramelos">Caramelos</label>
                                </div>                                
                            </div>
                        </div>
                        <hr>
                        <div class="fila">                         
                            <div class="content-check mx-2">
                                <textarea name="observacion" id="observacion" cols="30" rows="6" placeholder="Escriba alguna observacion..." style="width: 100%">{{$apartamentoLimpieza->observacion}}</textarea>    
                            </div>
                        </div>
                        <div class="fila mt-2">
                            <button type="submit" class="btn btn-guardar w-100 text-uppercase fw-bold">Guardar</button>
                        </div>
                    </form>

                    <form id="formFinalizar" action="{{route('gestion.finalizar',$apartamentoLimpieza->id)}}" method="POST">
                        @csrf
                    </form>
                    <button type="button" class="btn btn-terminar w-100 mt-2 text-uppercase fw-bold" onclick="enviarFormulario('finalizar')">Terminar</button>
                </div>
            </div>     
                
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function enviarFormulario(formulario) {
        document.getElementById('formFinalizar').submit();
    }
    console.log('Limpieza de Apartamento by Hawkins.')

    $(document).ready(function(){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).on('change', 'input:checkbox', function() {
            
            console.log('click')
            if ($(this).is(':checked')) {
                console.log('El checkbox está marcado');
            } else {
                console.log('El checkbox no está marcado');
            }
            var inputId = $(this).attr('data-id');
            var name = $(this).attr('name');
            var isChecked = $(this).is(':checked');
            // Preparar los datos para enviar
            var data = {
                id: inputId,
                name: name,
                checked: isChecked
            };
            // Realizar la petición AJAX
            $.ajax({
                url: '{{route("gestion.storeColumn")}}', // Reemplaza esto con la URL correcta
                type: 'POST',
                data: data,
                success: function(response) {
                    console.log('Peticion realizada con exito', response);
                },
                error: function(error) {
                    console.error('Ha ocurrido un error', error);
                }
            });
        });
    });
    $(document).ready(function(){
        console.log($('input[name="armario"]'))

        $('input[name="dormitorio"]').on('change', function(){
            var photo = '{{$apartamentoLimpieza->dormitorio_photo}}'
            if ($(this).is(':checked')) {
                console.log('El checkbox está marcado');
                $('#camaraDormitorio').css('display', 'block')
            } else {
                console.log('El checkbox no está marcado');
                $('#camaraDormitorio').css('display', 'none')

            }
        })
        $('input[name="armario"]').on('change', function(){
            if ($(this).is(':checked')) {
                console.log('El checkbox está marcado');
                $('#camaraArmario').css('display', 'block')
            } else {
                console.log('El checkbox no está marcado');
                $('#camaraArmario').css('display', 'none')

            }
        })
        $('input[name="bano"]').on('change', function(){
            if ($(this).is(':checked')) {
                console.log('El checkbox está marcado');
                $('#camaraBano').css('display', 'block')
            } else {
                console.log('El checkbox no está marcado');
                $('#camaraBano').css('display', 'none')

            }
        })

        $('input[name="salon"]').on('change', function(){
            if ($(this).is(':checked')) {
                console.log('El checkbox está marcado');
                $('#camaraSalon').css('display', 'block')
            } else {
                console.log('El checkbox no está marcado');
                $('#camaraSalon').css('display', 'none')

            }
        })
        $('input[name="cocina"]').on('change', function(){
            if ($(this).is(':checked')) {
                console.log('El checkbox está marcado');
                $('#camaraCocina').css('display', 'block')
            } else {
                console.log('El checkbox no está marcado');
                $('#camaraCocina').css('display', 'none')
            }
        })
        
    })
</script>
@endsection
