@extends('layouts.appAdmin')

@section('content')
<style>
    .inactive-sort {
        color: #0F1739;
        text-decoration: none;
    }
    .active-sort {
        color: #757191;
    }
</style>
<div class="container-fluid">
    <h2 class="mb-3">{{ __('Nuestro Apartamento Limpiado') }}</h2>
    <hr class="mb-5">

    <!-- Sección DORMITORIO -->
    <div class="row">
        <div class="col-md-12">
            <h4 class="titulo mb-0">DORMITORIO</h4>
        </div>
        <!-- Todos los controles de DORMITORIO -->
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="dormitorio_sabanas" name="dormitorio_sabanas" {{ old('dormitorio_sabanas', $apartamento->dormitorio_sabanas) ? 'checked' : '' }}>
                <label class="form-check-label" for="dormitorio_sabanas">Sabanas</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="dormitorio_cojines" name="dormitorio_cojines" {{ old('dormitorio_cojines', $apartamento->dormitorio_cojines) ? 'checked' : '' }}>
                <label class="form-check-label" for="dormitorio_cojines">Cojines (4 uds)</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="dormitorio_edredon" name="dormitorio_edredon" {{ old('dormitorio_edredon', $apartamento->dormitorio_edredon) ? 'checked' : '' }}>
                <label class="form-check-label" for="dormitorio_edredon">Edredón</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="dormitorio_funda_edredon" name="dormitorio_funda_edredon" {{ old('dormitorio_funda_edredon', $apartamento->dormitorio_funda_edredon) ? 'checked' : '' }}>
                <label class="form-check-label" for="dormitorio_funda_edredon">Funda de edredón</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="dormitorio_canape" name="dormitorio_canape" {{ old('dormitorio_canape', $apartamento->dormitorio_canape) ? 'checked' : '' }}>
                <label class="form-check-label" for="dormitorio_canape">Canapé</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="dormitorio_manta_cubrepies" name="dormitorio_manta_cubrepies" {{ old('dormitorio_manta_cubrepies', $apartamento->dormitorio_manta_cubrepies) ? 'checked' : '' }}>
                <label class="form-check-label" for="dormitorio_manta_cubrepies">Manta gris</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="dormitorio_papel_plancha" name="dormitorio_papel_plancha" {{ old('dormitorio_papel_plancha', $apartamento->dormitorio_papel_plancha) ? 'checked' : '' }}>
                <label class="form-check-label" for="dormitorio_papel_plancha">Papel plancha</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="dormitorio_toallas_rulo" name="dormitorio_toallas_rulo" {{ old('dormitorio_toallas_rulo', $apartamento->dormitorio_toallas_rulo) ? 'checked' : '' }}>
                <label class="form-check-label" for="dormitorio_toallas_rulo">Toallas Rulo (2 uds.)</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="dormitorio_revision_pelos" name="dormitorio_revision_pelos" {{ old('dormitorio_revision_pelos', $apartamento->dormitorio_revision_pelos) ? 'checked' : '' }}>
                <label class="form-check-label" for="dormitorio_revision_pelos">Revisión Pelos</label>
            </div>
        </div>
    </div>
    <hr>
    <!-- Sección ARMARIO -->
    <div class="row mt-4">
        <div class="col-md-12">
            <h4 class="titulo mb-0">ARMARIO</h4>
        </div>
        <!-- Todos los controles de ARMARIO -->
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="armario_perchas" name="armario_perchas" {{ old('armario_perchas', $apartamento->armario_perchas) ? 'checked' : '' }}>
                <label class="form-check-label" for="armario_perchas">Perchas (5 uds.)</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="armario_almohada_repuesto_sofa" name="armario_almohada_repuesto_sofa" {{ old('armario_almohada_repuesto_sofa', $apartamento->armario_almohada_repuesto_sofa) ? 'checked' : '' }}>
                <label class="form-check-label" for="armario_almohada_repuesto_sofa">Almohada de repuesto sofá</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="armario_edredon_repuesto_sofa" name="armario_edredon_repuesto_sofa" {{ old('armario_edredon_repuesto_sofa', $apartamento->armario_edredon_repuesto_sofa) ? 'checked' : '' }}>
                <label class="form-check-label" for="armario_edredon_repuesto_sofa">Edredón de repuesto sofá</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="armario_funda_repuesto_edredon" name="armario_funda_repuesto_edredon" {{ old('armario_funda_repuesto_edredon', $apartamento->armario_funda_repuesto_edredon) ? 'checked' : '' }}>
                <label class="form-check-label" for="armario_funda_repuesto_edredon">Funda de repuesto edredón</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="armario_sabanas_repuesto" name="armario_sabanas_repuesto" {{ old('armario_sabanas_repuesto', $apartamento->armario_sabanas_repuesto) ? 'checked' : '' }}>
                <label class="form-check-label" for="armario_sabanas_repuesto">Sábanas de repuesto</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="armario_plancha" name="armario_plancha" {{ old('armario_plancha', $apartamento->armario_plancha) ? 'checked' : '' }}>
                <label class="form-check-label" for="armario_plancha">Plancha</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="armario_tabla_plancha" name="armario_tabla_plancha" {{ old('armario_tabla_plancha', $apartamento->armario_tabla_plancha) ? 'checked' : '' }}>
                <label class="form-check-label" for="armario_tabla_plancha">Tabla de planchar</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="armario_toalla" name="armario_toalla" {{ old('armario_toalla', $apartamento->armario_toalla) ? 'checked' : '' }}>
                <label class="form-check-label" for="armario_toalla">Toallas</label>
            </div>
        </div>
    </div>
    <hr>
    
    <!-- Sección CANAPE -->
    <div class="row mt-4">
        <div class="col-md-12">
            <h4 class="titulo mb-0">CANAPE</h4>
        </div>
        <!-- Todos los controles de CANAPE -->
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="canape_almohada" name="canape_almohada" {{ old('canape_almohada', $apartamento->canape_almohada) ? 'checked' : '' }}>
                <label class="form-check-label" for="canape_almohada">Almohada</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="canape_gel" name="canape_gel" {{ old('canape_gel', $apartamento->canape_gel) ? 'checked' : '' }}>
                <label class="form-check-label" for="canape_gel">Gel</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="canape_sabanas" name="canape_sabanas" {{ old('canape_sabanas', $apartamento->canape_sabanas) ? 'checked' : '' }}>
                <label class="form-check-label" for="canape_sabanas">Sabanas</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="canape_toallas" name="canape_toallas" {{ old('canape_toallas', $apartamento->canape_toallas) ? 'checked' : '' }}>
                <label class="form-check-label" for="canape_toallas">2 Toallas</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="canape_papel_wc" name="canape_papel_wc" {{ old('canape_papel_wc', $apartamento->canape_papel_wc) ? 'checked' : '' }}>
                <label class="form-check-label" for="canape_papel_wc">Papel WC</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="canape_estropajo" name="canape_estropajo" {{ old('canape_estropajo', $apartamento->canape_estropajo) ? 'checked' : '' }}>
                <label class="form-check-label" for="canape_estropajo">Estropajo</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="canape_bayeta" name="canape_bayeta" {{ old('canape_bayeta', $apartamento->canape_bayeta) ? 'checked' : '' }}>
                <label class="form-check-label" for="canape_bayeta">Bayeta</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="canape_antihumedad" name="canape_antihumedad" {{ old('canape_antihumedad', $apartamento->canape_antihumedad) ? 'checked' : '' }}>
                <label class="form-check-label" for="canape_antihumedad">Antihumedad</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="canape_ambientador" name="canape_ambientador" {{ old('canape_ambientador', $apartamento->canape_ambientador) ? 'checked' : '' }}>
                <label class="form-check-label" for="canape_ambientador">Ambientador</label>
            </div>
        </div>
    </div>
    <hr>

    <!-- Sección SALÓN -->
    <div class="row mt-4">
        <div class="col-md-12">
            <h4 class="titulo mb-0">SALÓN</h4>
        </div>
        <!-- Todos los controles de SALÓN -->
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="salon_cojines" name="salon_cojines" {{ old('salon_cojines', $apartamento->salon_cojines) ? 'checked' : '' }}>
                <label class="form-check-label" for="salon_cojines">Cojines (2 uds)</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="salon_sofa_cama" name="salon_sofa_cama" {{ old('salon_sofa_cama', $apartamento->salon_sofa_cama) ? 'checked' : '' }}>
                <label class="form-check-label" for="salon_sofa_cama">Sofá cama daño o sucio</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="salon_planta_cesta" name="salon_planta_cesta" {{ old('salon_planta_cesta', $apartamento->salon_planta_cesta) ? 'checked' : '' }}>
                <label class="form-check-label" for="salon_planta_cesta">Planta con cesta</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="salon_mandos" name="salon_mandos" {{ old('salon_mandos', $apartamento->salon_mandos) ? 'checked' : '' }}>
                <label class="form-check-label" for="salon_mandos">Mando (TV y Aire Acondicionado)</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="salon_tv" name="salon_tv" {{ old('salon_tv', $apartamento->salon_tv) ? 'checked' : '' }}>
                <label class="form-check-label" for="salon_tv">Probar TV (Encender y apagar)</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="salon_cortinas" name="salon_cortinas" {{ old('salon_cortinas', $apartamento->salon_cortinas) ? 'checked' : '' }}>
                <label class="form-check-label" for="salon_cortinas">Cortinas (Limpias y bien engachadas)</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="salon_sillas" name="salon_sillas" {{ old('salon_sillas', $apartamento->salon_sillas) ? 'checked' : '' }}>
                <label class="form-check-label" for="salon_sillas">Sillas y mesa</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="salon_salvamanteles" name="salon_salvamanteles" {{ old('salon_salvamanteles', $apartamento->salon_salvamanteles) ? 'checked' : '' }}>
                <label class="form-check-label" for="salon_salvamanteles">Salvamanteles (2 uds)</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="salon_estanteria" name="salon_estanteria" {{ old('salon_estanteria', $apartamento->salon_estanteria) ? 'checked' : '' }}>
                <label class="form-check-label" for="salon_estanteria">Estantería</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="salon_decoracion" name="salon_decoracion" {{ old('salon_decoracion', $apartamento->salon_decoracion) ? 'checked' : '' }}>
                <label class="form-check-label" for="salon_decoracion">Decoración</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="salon_ambientador" name="salon_ambientador" {{ old('salon_ambientador', $apartamento->salon_ambientador) ? 'checked' : '' }}>
                <label class="form-check-label" for="salon_ambientador">Ambientador</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="salon_libros_juego" name="salon_libros_juego" {{ old('salon_libros_juego', $apartamento->salon_libros_juego) ? 'checked' : '' }}>
                <label class="form-check-label" for="salon_libros_juego">Libros y juegos</label>
            </div>
        </div>
    </div>
    <hr>

    <!-- Sección COCINA -->
    <div class="row mt-4">
        <div class="col-md-12">
            <h4 class="titulo mb-0">COCINA</h4>
        </div>
        <!-- Todos los controles de COCINA -->
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="cocina_vitroceramica" name="cocina_vitroceramica" {{ old('cocina_vitroceramica', $apartamento->cocina_vitroceramica) ? 'checked' : '' }}>
                <label class="form-check-label" for="cocina_vitroceramica">Vitrocerámica</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="cocina_vajilla" name="cocina_vajilla" {{ old('cocina_vajilla', $apartamento->cocina_vajilla) ? 'checked' : '' }}>
                <label class="form-check-label" for="cocina_vajilla">Vajilla</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="cocina_vasos" name="cocina_vasos" {{ old('cocina_vasos', $apartamento->cocina_vasos) ? 'checked' : '' }}>
                <label class="form-check-label" for="cocina_vasos">Vasos (4 uds)</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="cocina_tazas" name="cocina_tazas" {{ old('cocina_tazas', $apartamento->cocina_tazas) ? 'checked' : '' }}>
                <label class="form-check-label" for="cocina_tazas">Tazas (4 uds)</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="cocina_tapadera" name="cocina_tapadera" {{ old('cocina_tapadera', $apartamento->cocina_tapadera) ? 'checked' : '' }}>
                <label class="form-check-label" for="cocina_tapadera">Tapaderas (2 uds)</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="cocina_sartenes" name="cocina_sartenes" {{ old('cocina_sartenes', $apartamento->cocina_sartenes) ? 'checked' : '' }}>
                <label class="form-check-label" for="cocina_sartenes">Sartenes (2 uds)</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="cocina_paño_cocina" name="cocina_paño_cocina" {{ old('cocina_paño_cocina', $apartamento->cocina_paño_cocina) ? 'checked' : '' }}>
                <label class="form-check-label" for="cocina_paño_cocina">Paño cocina (1 ud)</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="cocina_cuberteria" name="cocina_cuberteria" {{ old('cocina_cuberteria', $apartamento->cocina_cuberteria) ? 'checked' : '' }}>
                <label class="form-check-label" for="cocina_cuberteria">Cubertería (4 uds)</label>
            </div>
        </div>
        <!-- Más controles para la cocina -->
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="cocina_cuchillo" name="cocina_cuchillo" {{ old('cocina_cuchillo', $apartamento->cocina_cuchillo) ? 'checked' : '' }}>
                <label class="form-check-label" for="cocina_cuchillo">Cuchillo (1 ud)</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="cocina_ollas" name="cocina_ollas" {{ old('cocina_ollas', $apartamento->cocina_ollas) ? 'checked' : '' }}>
                <label class="form-check-label" for="cocina_ollas">Ollas</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="cocina_papel_cocina" name="cocina_papel_cocina" {{ old('cocina_papel_cocina', $apartamento->cocina_papel_cocina) ? 'checked' : '' }}>
                <label class="form-check-label" for="cocina_papel_cocina">Papel de cocina</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="cocina_tapadera_micro" name="cocina_tapadera_micro" {{ old('cocina_tapadera_micro', $apartamento->cocina_tapadera_micro) ? 'checked' : '' }}>
                <label class="form-check-label" for="cocina_tapadera_micro">Tapadera Micro</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="cocina_estropajo" name="cocina_estropajo" {{ old('cocina_estropajo', $apartamento->cocina_estropajo) ? 'checked' : '' }}>
                <label class="form-check-label" for="cocina_estropajo">Estropajo/Bayeta</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="cocina_mistol" name="cocina_mistol" {{ old('cocina_mistol', $apartamento->cocina_mistol) ? 'checked' : '' }}>
                <label class="form-check-label" for="cocina_mistol">Mistol</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="cocina_tostadora" name="cocina_tostadora" {{ old('cocina_tostadora', $apartamento->cocina_tostadora) ? 'checked' : '' }}>
                <label class="form-check-label" for="cocina_tostadora">Tostadora</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="cocina_bolsa_basura" name="cocina_bolsa_basura" {{ old('cocina_bolsa_basura', $apartamento->cocina_bolsa_basura) ? 'checked' : '' }}>
                <label class="form-check-label" for="cocina_bolsa_basura">Bolsa de basura</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="cocina_tabla_cortar" name="cocina_tabla_cortar" {{ old('cocina_tabla_cortar', $apartamento->cocina_tabla_cortar) ? 'checked' : '' }}>
                <label class="form-check-label" for="cocina_tabla_cortar">Tabla de cortar</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="cocina_escurreplatos" name="cocina_escurreplatos" {{ old('cocina_escurreplatos', $apartamento->cocina_escurreplatos) ? 'checked' : '' }}>
                <label class="form-check-label" for="cocina_escurreplatos">Escurreplatos</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="cocina_bol_escurridor" name="cocina_bol_escurridor" {{ old('cocina_bol_escurridor', $apartamento->cocina_bol_escurridor) ? 'checked' : '' }}>
                <label class="form-check-label" for="cocina_bol_escurridor">Bol y Escurridor</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="cocina_utensilios_cocina" name="cocina_utensilios_cocina" {{ old('cocina_utensilios_cocina', $apartamento->cocina_utensilios_cocina) ? 'checked' : '' }}>
                <label class="form-check-label" for="cocina_utensilios_cocina">Utensilios (Pinza, cucharón y espumadera)</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="cocina_dolcegusto" name="cocina_dolcegusto" {{ old('cocina_dolcegusto', $apartamento->cocina_dolcegusto) ? 'checked' : '' }}>
                <label class="form-check-label" for="cocina_dolcegusto">Dolcegusto (3 cápsulas)</label>
            </div>
        </div>
    </div>

    <!-- Sección BAÑO -->
    <div class="row mt-4">
        <div class="col-md-12">
            <h4 class="titulo mb-0">BAÑO</h4>
        </div>
        <!-- Todos los controles de BAÑO -->
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="bano_toallas_aseos" name="bano_toallas_aseos" {{ old('bano_toallas_aseos', $apartamento->bano_toallas_aseos) ? 'checked' : '' }}>
                <label class="form-check-label" for="bano_toallas_aseos">Toallas de Baño (2 uds)</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="bano_toallas_mano" name="bano_toallas_mano" {{ old('bano_toallas_mano', $apartamento->bano_toallas_mano) ? 'checked' : '' }}>
                <label class="form-check-label" for="bano_toallas_mano">Toallas de mano (1 ud)</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="bano_alfombra" name="bano_alfombra" {{ old('bano_alfombra', $apartamento->bano_alfombra) ? 'checked' : '' }}>
                <label class="form-check-label" for="bano_alfombra">Alfombra (1 ud)</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="bano_secador" name="bano_secador" {{ old('bano_secador', $apartamento->bano_secador) ? 'checked' : '' }}>
                <label class="form-check-label" for="bano_secador">Secador</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="bano_papel" name="bano_papel" {{ old('bano_papel', $apartamento->bano_papel) ? 'checked' : '' }}>
                <label class="form-check-label" for="bano_papel">Papel</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="bano_rellenar_gel" name="bano_rellenar_gel" {{ old('bano_rellenar_gel', $apartamento->bano_rellenar_gel) ? 'checked' : '' }}>
                <label class="form-check-label" for="bano_rellenar_gel">Gel</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="bano_espejo" name="bano_espejo" {{ old('bano_espejo', $apartamento->bano_espejo) ? 'checked' : '' }}>
                <label class="form-check-label" for="bano_espejo">Espejo</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="bano_ganchos" name="bano_ganchos" {{ old('bano_ganchos', $apartamento->bano_ganchos) ? 'checked' : '' }}>
                <label class="form-check-label" for="bano_ganchos">Ganchos puerta (2 uds)</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="bano_muebles" name="bano_muebles" {{ old('bano_muebles', $apartamento->bano_muebles) ? 'checked' : '' }}>
                <label class="form-check-label" for="bano_muebles">Revisar muebles</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="bano_desague" name="bano_desague" {{ old('bano_desague', $apartamento->bano_desague) ? 'checked' : '' }}>
                <label class="form-check-label" for="bano_desague">Revisar desagüe (pelos)</label>
            </div>
        </div>
    </div>
    <hr>

    <!-- Sección AMENITIES -->
    <div class="row mt-4">
        <div class="col-md-12">
            <h4 class="titulo mb-0">AMENITIES</h4>
        </div>
        <!-- Todos los controles de AMENITIES -->
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="amenities_nota_agradecimiento" name="amenities_nota_agradecimiento" {{ old('amenities_nota_agradecimiento', $apartamento->amenities_nota_agradecimiento) ? 'checked' : '' }}>
                <label class="form-check-label" for="amenities_nota_agradecimiento">Nota de agradecimiento</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="amenities_magdalenas" name="amenities_magdalenas" {{ old('amenities_magdalenas', $apartamento->amenities_magdalenas) ? 'checked' : '' }}>
                <label class="form-check-label" for="amenities_magdalenas">3 magdalenas</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check form-switch mt-2">
                <input data-id="{{$apartamento->id}}" class="form-check-input" type="checkbox" id="amenities_caramelos" name="amenities_caramelos" {{ old('amenities_caramelos', $apartamento->amenities_caramelos) ? 'checked' : '' }}>
                <label class="form-check-label" for="amenities_caramelos">Caramelos</label>
            </div>
        </div>
    </div>
    <hr>

    <!-- Sección OBSERVACION -->
    <div class="row mt-4">
        <div class="col-md-12">
            <h4 class="titulo mb-0">OBSERVACION</h4>
        </div>
        <!-- Todos los controles de AMENITIES -->
        <div class="col-md-12">
            <div class="form-check ps-0 mt-2">
                <textarea name="observacion" id="observacion" cols="30" rows="6" placeholder="Escriba alguna observacion..." style="width: 100%">{{$apartamento->observacion}}</textarea>    
            </div>
        </div>
    </div>

    <!-- Sección IMAGENES -->
    <div class="row mt-4">
        <div class="col-md-12">
            <h4 class="titulo mb-0">Imagenes</h4>
        </div>
        <!-- Todos los controles de AMENITIES -->
        <div class="col-md-3">
            @if ($fotos != null)
                @foreach ($fotos as $foto)
                    <img src="/{{$foto->url}}" alt="{{$foto->categoria->nombre}}" class="img-fluid" />
                @endforeach
            @endif
        </div>
    </div>
    

</div>
@endsection

@include('sweetalert::alert')

@section('scripts')
<script>
    // document.addEventListener('DOMContentLoaded', function () {
    //     // Verificar si SweetAlert2 está definido
    //     if (typeof Swal === 'undefined') {
    //         console.error('SweetAlert2 is not loaded');
    //         return;
    //     }

    //     // Botones de eliminar
    //     const deleteButtons = document.querySelectorAll('.delete-btn');
    //     deleteButtons.forEach(button => {
    //         button.addEventListener('click', function (event) {
    //             event.preventDefault();
    //             const form = this.closest('form');
    //             Swal.fire({
    //                 title: '¿Estás seguro?',
    //                 text: "¡No podrás revertir esto!",
    //                 icon: 'warning',
    //                 showCancelButton: true,
    //                 confirmButtonColor: '#3085d6',
    //                 cancelButtonColor: '#d33',
    //                 confirmButtonText: 'Sí, eliminar!',
    //                 cancelButtonText: 'Cancelar'
    //             }).then((result) => {
    //                 if (result.isConfirmed) {
    //                     form.submit();
    //                 }
    //             });
    //         });
    //     });
    // });
</script>
@endsection

