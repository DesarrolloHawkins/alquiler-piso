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
    .custom-tooltip {
        --bs-tooltip-bg: var(--bd-violet-bg);
        --bs-tooltip-color: var(--bs-white);
    }
</style>
<!-- Incluir jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Incluir Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<div class="container-fluid">
    <h2 class="mb-3">Diario de Caja</h2>
    {{-- <button type="button" class="btn bg-color-quinto" data-toggle="modal" data-target="#modalDiarioCaja">
        Añadir al diario de caja
    </button>
    
    <!-- Modal -->
    <div class="modal fade" id="modalDiarioCaja" tabindex="-1" role="dialog" aria-labelledby="modalDiarioCajaLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDiarioCajaLabel">Añadir al Diario de Caja</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <a href="{{ route('admin.diarioCaja.ingreso') }}" class="btn btn-primary">Añadir Ingreso</a>
                    <a href="{{ route('admin.diarioCaja.gasto') }}" class="btn btn-secondary">Añadir Gasto</a>
                </div>
            </div>
        </div>
    </div> --}}

    <hr class="mb-5">

    <!-- Filtros -->
    <form action="{{ route('admin.diarioCaja.index') }}" method="GET" class="mb-4">
        <div class="row align-items-start">
            <div class="col-md-2">
                <label for="start_date" class="form-label">Fecha Inicio</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-2">
                <label for="end_date" class="form-label">Fecha Fin</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-2">
                <label for="estado_id" class="form-label">Estado</label>
                <select name="estado_id" class="form-control">
                    <option value="">Todos los Estados</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado->id }}" {{ request('estado_id') == $estado->id ? 'selected' : '' }}>{{ $estado->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="cuenta_id" class="form-label">Cuenta</label>
                <select name="cuenta_id" class="form-control">
                    <option value="">Todas las Cuentas</option>
                    @foreach ($cuentas as $cuenta)
                        <option value="{{ $cuenta->id }}" {{ request('cuenta_id') == $cuenta->id ? 'selected' : '' }}>{{ $cuenta->numero .' - '. $cuenta->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="concepto" class="form-label">Concepto</label>
                <input type="text" name="concepto" class="form-control" value="{{ request('concepto') }}" placeholder="Buscar concepto...">
            </div>
            <div class="col-md-2 align-items-end d-flex justify-content-center">
                <button type="submit" class="btn btn-primary mt-4">Filtrar</button>
            </div>
        </div>
    </form>

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="jumbotron">       
                <div class="table-responsive">                   
                    <table id="cuentas" class="table table-striped table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Asiento</th>
                                {{-- <th>Estado</th> --}}
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
                            <tr>
                                <td colspan="7"></td>
                                <td><strong>Saldo Inicial:</strong></td>
                                <td>{{ number_format($saldoInicial, 2) }} €</td>
                                <td></td>
                            </tr>
                            
                            @if (count($response) > 0)
                                @foreach ($response as $linea)
                                <tr>
                                    <td>{{ $linea->asiento_contable }}</td>
                                    {{-- <td>@if ($linea->estado == null)
                                        <span class="badge bg-danger">No encontrado</span>
                                        @else
                                            {{ $linea->estado->nombre }}
                                        @endif
                                    </td> --}}
                                    <td></td>
                                    <td>{{ $linea->date }}</td>
                                    <td>{{ $linea->concepto }}</td>
                                    <td>{{ $linea->forma_pago }}</td>
                                    <td>{{ number_format($linea->debe, 2) }} €</td>
                                    <td>{{ number_format($linea->haber, 2) }} €</td>
                                    <td>{{ number_format($linea->saldo, 2) }} €</td>
                                    <td>
                                        @if ($linea->tipo == 'ingreso')
                                            <a href="{{ route('admin.ingresos.edit', $linea->ingreso_id) }}" class="btn btn-warning">Editar</a>
                                        @elseif ($linea->tipo == 'gasto')
                                            <a href="{{ route('admin.gastos.edit', $linea->gasto_id) }}" class="btn btn-warning">Editar</a>
                                        @endif
                                        <form action="{{ route('admin.diarioCaja.destroyDiarioCaja', $linea->id) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger delete-btn">Eliminar</button>
                                        </form>                                   
                                    </td>                                    
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                        
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
      // Verificar si SweetAlert2 está definido
      if (typeof Swal === 'undefined') {
          console.error('SweetAlert2 is not loaded');
          return;
      }
      const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
      const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

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
