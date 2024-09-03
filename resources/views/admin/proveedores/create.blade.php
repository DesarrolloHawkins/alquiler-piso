
@extends('layouts.appAdmin')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-colum mb-3">
        <h2 class="mb-0 me-3 encabezado_top">{{ __('Crear Proveedor') }}</h2>
    </div>
    <hr>
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('admin.proveedores.store') }}" method="POST" class="row">
                @csrf  <!-- Token CSRF para proteger tu formulario -->

                <div class="col-md-6 col-12 mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control {{ $errors->has('nombre') ? 'is-invalid' : '' }}" id="nombre" name="nombre" value="{{ old('nombre') }}">
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="dni" class="form-label">DNI</label>
                    <input type="text" class="form-control {{ $errors->has('dni') ? 'is-invalid' : '' }}" id="dni" name="dni" value="{{ old('dni') }}">
                    @error('dni')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="cif" class="form-label">CIF</label>
                    <input type="text" class="form-control {{ $errors->has('cif') ? 'is-invalid' : '' }}" id="cif" name="cif" value="{{ old('cif') }}">
                    @error('cif')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" id="email" name="email" value="{{ old('email') }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="pais" class="form-label">País</label>
                    <select id="pais" name="pais" class="form-control {{ $errors->has('pais') ? 'is-invalid' : '' }}">
                        <option value="">Selecciona un país</option>
                        <!-- Aquí se cargarán los países dinámicamente -->
                    </select>
                    @error('pais')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="provincia" class="form-label">Provincia</label>
                    <select id="provincia" name="provincia" class="form-control {{ $errors->has('provincia') ? 'is-invalid' : '' }}">
                        <option value="">Selecciona una provincia</option>
                        <!-- Aquí se cargarán las provincias dinámicamente -->
                    </select>
                    @error('provincia')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="ciudad" class="form-label">Ciudad</label>
                    <select id="ciudad" name="ciudad" class="form-control {{ $errors->has('ciudad') ? 'is-invalid' : '' }}">
                        <option value="">Selecciona una ciudad</option>
                        <!-- Aquí se cargarán las ciudades dinámicamente -->
                    </select>
                    @error('ciudad')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" class="form-control {{ $errors->has('direccion') ? 'is-invalid' : '' }}" id="direccion" name="direccion" value="{{ old('direccion') }}">
                    @error('direccion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="zipcode" class="form-label">Código Postal</label>
                    <input type="text" class="form-control {{ $errors->has('zipcode') ? 'is-invalid' : '' }}" id="zipcode" name="zipcode" value="{{ old('zipcode') }}">
                    @error('zipcode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="work_activity" class="form-label">Actividad Laboral</label>
                    <input type="text" class="form-control {{ $errors->has('work_activity') ? 'is-invalid' : '' }}" id="work_activity" name="work_activity" value="{{ old('work_activity') }}">
                    @error('work_activity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="fax" class="form-label">Fax</label>
                    <input type="text" class="form-control {{ $errors->has('fax') ? 'is-invalid' : '' }}" id="fax" name="fax" value="{{ old('fax') }}">
                    @error('fax')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="phone" class="form-label">Teléfono</label>
                    <input type="text" class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}" id="phone" name="phone" value="{{ old('phone') }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="web" class="form-label">Página Web</label>
                    <input type="text" class="form-control {{ $errors->has('web') ? 'is-invalid' : '' }}" id="web" name="web" value="{{ old('web') }}">
                    @error('web')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="facebook" class="form-label">Facebook</label>
                    <input type="text" class="form-control {{ $errors->has('facebook') ? 'is-invalid' : '' }}" id="facebook" name="facebook" value="{{ old('facebook') }}">
                    @error('facebook')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="twitter" class="form-label">Twitter</label>
                    <input type="text" class="form-control {{ $errors->has('twitter') ? 'is-invalid' : '' }}" id="twitter" name="twitter" value="{{ old('twitter') }}">
                    @error('twitter')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="linkedin" class="form-label">LinkedIn</label>
                    <input type="text" class="form-control {{ $errors->has('linkedin') ? 'is-invalid' : '' }}" id="linkedin" name="linkedin" value="{{ old('linkedin') }}">
                    @error('linkedin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="instagram" class="form-label">Instagram</label>
                    <input type="text" class="form-control {{ $errors->has('instagram') ? 'is-invalid' : '' }}" id="instagram" name="instagram" value="{{ old('instagram') }}">
                    @error('instagram')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="pinterest" class="form-label">Pinterest</label>
                    <input type="text" class="form-control {{ $errors->has('pinterest') ? 'is-invalid' : '' }}" id="pinterest" name="pinterest" value="{{ old('pinterest') }}">
                    @error('pinterest')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 mb-3">
                    <label for="note" class="form-label">Nota</label>
                    <textarea class="form-control {{ $errors->has('note') ? 'is-invalid' : '' }}" id="note" name="note">{{ old('note') }}</textarea>
                    @error('note')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-terminar w-100 fs-4 mt-4">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    //f70b99b7f3msh675b920689d2542p1c18a1jsn4c4c3387811a
    
    document.addEventListener('DOMContentLoaded', function () {
        let token;
        var req = axios.get('https://www.universal-tutorial.com/api/getaccesstoken', {
            headers: {
                "Accept": "application/json",
                "api-token": "okiCmY0EwJykpQWDAtYRNqPGLfHp6zH6KHnWQbqNC-i315D9WZuwBLdrRAkCxYHq3nA",
                "user-email": "p.ragel@hawkins.es"
            }
        }). then(resp => {
            token = resp.data.auth_token;
            console.log("Bearer " + token)
            // Cargar países al cargar la página usando REST Countries API
            axios.get('https://www.universal-tutorial.com/api/countries/',{
                headers: {
                        "Authorization": "Bearer " + token,
                        "Accept": "application/json"
                }
            })
            .then(response => {
                console.log(response)
                const paises = response.data;
                paises.forEach(pais => {
                    const option = document.createElement('option');
                    option.value = pais.country_name;  // Usamos el nombre común del país
                    option.textContent = pais.country_name; // Mostrar el nombre en español si está disponible
                    paisSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error al cargar los países:', error));
        })
        // var req = unirest("GET", "https://www.universal-tutorial.com/api/getaccesstoken");
        const paisSelect = document.getElementById('pais');
        const regionSelect = document.getElementById('provincia');
        const ciudadSelect = document.getElementById('ciudad');

        

        // Cargar regiones/provincias cuando se selecciona un país
        paisSelect.addEventListener('change', function () {
            const pais = paisSelect.value;
            if (pais) {
                axios.get('https://www.universal-tutorial.com/api/states/' +  pais, {
                    headers: {
                            "Authorization": "Bearer " + token,
                            "Accept": "application/json"
                    }
                })
                    .then(response => {
                        console.log(response)
                        const regiones = response.data;
                        regionSelect.innerHTML = '<option value="">Selecciona una región/provincia</option>';
                        ciudadSelect.innerHTML = '<option value="">Selecciona una ciudad</option>';
                        regiones.forEach(region => {
                            const option = document.createElement('option');
                            option.value = region.state_name;
                            option.textContent = region.state_name;
                            regionSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error al cargar las regiones/provincias:', error));
            } else {
                regionSelect.innerHTML = '<option value="">Selecciona una región/provincia</option>';
                ciudadSelect.innerHTML = '<option value="">Selecciona una ciudad</option>';
            }
        });

        // Cargar ciudades cuando se selecciona una región/provincia
        regionSelect.addEventListener('change', function () {
            const provincia = regionSelect.value;
            const pais = paisSelect.value;

            if (provincia) {
                axios.get('https://www.universal-tutorial.com/api/cities/' +  provincia, {
                    headers: {
                            "Authorization": "Bearer " + token,
                            "Accept": "application/json"
                    }
                })
                    .then(response => {
                        const ciudades = response.data;
                        console.log(response)

                        ciudadSelect.innerHTML = '<option value="">Selecciona una ciudad</option>';
                        if (ciudades.length > 0) {
                            ciudades.forEach(ciudad => {
                                const option = document.createElement('option');
                                option.value = ciudad.city_name;
                                option.textContent = ciudad.city_name;
                                ciudadSelect.appendChild(option);
                            });
                        } else {
                            ciudadSelect.innerHTML = '<option value="">No hay ciudades disponibles para esta provincia</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Error al cargar las ciudades:', error);
                        ciudadSelect.innerHTML = '<option value="">No se pudieron cargar las ciudades</option>';
                    });
            } else {
                ciudadSelect.innerHTML = '<option value="">Selecciona una ciudad</option>';
            }
        });
    });

</script>
@endsection
