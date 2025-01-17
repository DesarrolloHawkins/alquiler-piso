@extends('layouts.appAdmin')

@section('content')
<style>
  .drop-zone {
      max-width: 100%;
      padding: 50px;
      border: 2px dashed #cccccc;
      border-radius: 10px;
      text-align: center;
      color: #cccccc;
      font-family: Arial, sans-serif;
      cursor: pointer;
  }
  .drop-zone.dragover {
      border-color: #6666ff;
      color: #6666ff;
  }
</style>
<div class="container-fluid">
    <div class="d-flex flex-colum mb-3">
        <h2 class="mb-0 me-3 encabezado_top">{{ __('Subir Archivo de Banco') }}</h2>
    </div>
    <hr class="mb-5">
    <div class="row justify-content-center">
      <div class="drop-zone" id="drop-zone">
        Arrastra y suelta tu archivo aquí, o haz clic para seleccionarlo.
      </div>

      <!-- Input oculto para soportar clic en la zona de drop -->
      <input type="file" name="file" id="fileInput" accept=".xlsx" style="display:none;"

    </div>
</div>
@endsection

{{-- @include('sweetalert::alert') --}}

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  const dropZone = document.getElementById('drop-zone');
  const fileInput = document.getElementById('fileInput');

  // Abrir el input de archivos al hacer clic en la zona de drop
  dropZone.addEventListener('click', () => fileInput.click());

  // Añadir eventos de drag-and-drop
  dropZone.addEventListener('dragover', (e) => {
      e.preventDefault();
      dropZone.classList.add('dragover');
  });

  dropZone.addEventListener('dragleave', () => {
      dropZone.classList.remove('dragover');
  });

  dropZone.addEventListener('drop', (e) => {
      e.preventDefault();
      dropZone.classList.remove('dragover');

      if (e.dataTransfer.files.length) {
          fileInput.files = e.dataTransfer.files;
          uploadFile(fileInput.files[0]);  // Subir el archivo arrastrado
      }
  });

  // Si selecciona archivo con el input
  fileInput.addEventListener('change', () => {
      if (fileInput.files.length) {
          uploadFile(fileInput.files[0]);
      }
  });

  // Función para subir el archivo con fetch
  function uploadFile(file) {
      let formData = new FormData();
      formData.append('file', file);

      fetch("{{ route('upload.excel') }}", {
          method: 'POST',
          body: formData,
          headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
      })
      .then(response => response.json())
      .then(data => {
        console.log(data)
          if (data.message) {
              // Mostrar SweetAlert si la respuesta es exitosa
              Swal.fire({
                  title: 'Éxito!',
                  text: data.message,
                  icon: 'success',
                  confirmButtonText: 'Aceptar'
              });
          }
      })
      .catch(error => {
         console.log(error)

          // Mostrar un error si la subida falla
          Swal.fire({
              title: 'Error!',
              text: 'Ocurrió un error al procesar el archivo.',
              icon: 'error',
              confirmButtonText: 'Aceptar'
          });
      });
  }
</script>

@endsection

