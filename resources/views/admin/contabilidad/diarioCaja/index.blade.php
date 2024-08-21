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
    <h2 class="mb-3">Diario de Caja</h2>
    {{-- route('admin.diarioCaja.create')route('admin.diarioCaja.create') --}}
    <a href="{{ route('admin.subCuentasHijaContables.create') }}" class="btn bg-color-quinto">Añadir al diario de caja</a>
    <hr class="mb-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
          <div class="jumbotron">
            {{-- <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.dashboard') }}">Panel de usuario</a>
                </li>
                <li class="breadcrumb-item active">Todos los clientes</li>
            </ol> --}}          
            <div class="row">
                <div class="col">
                    <div class="col-md-6">
                    </div>
                    <div class="table-responsive">                   
                      <table id="cuentas" class="data-table table table-hover table-striped table-bordered " style="width:100%">
                          <thead>
                              <tr>
                                  <th>Asiento</th>
                                  {{-- <th>Nº Factura</th> --}}
                                  <th>Cuenta</th>
                                  <th>Fecha</th>
                                  <th>Concepto</th>
                                  <th>Forma de Pago</th>
                                  <th>Debe</th>
                                  <th>Haber</th>
                                  <th>Saldo</th>
                                  <th>Editar</th>
                                  </tr>
                          </thead>
                          <tbody>
                            @foreach ($response as $linea)
                              <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                              </tr>
                            @endforeach
                          </tbody>
                      </table>
                    </div>
                </div>
            </div>
          </div>
        </div>
    </div>
</div>

@include('sweetalert::alert')

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
      // Verificar si SweetAlert2 está definido
      if (typeof Swal === 'undefined') {
          console.error('SweetAlert2 is not loaded');
          return;
      }

      // Botones de eliminar
      const deleteButtons = document.querySelectorAll('.delete-btn');
      deleteButtons.forEach(button => {
          button.addEventListener('click', function (event) {
              event.preventDefault();
              const form = this.closest('form');
              Swal.fire({
                  title: '¿Estás seguro?',
                  text: "¡No podrás revertir esto!",
                  icon: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'Sí, eliminar!',
                  cancelButtonText: 'Cancelar'
              }).then((result) => {
                  if (result.isConfirmed) {
                      form.submit();
                  }
              });
          });
      });
  });
</script>
@endsection
@endsection
