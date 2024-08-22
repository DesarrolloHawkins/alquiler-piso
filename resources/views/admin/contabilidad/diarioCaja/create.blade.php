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
    <h2 class="mb-3">Agregar Ingreso</h2>
    {{-- route('admin.diarioCaja.create')route('admin.diarioCaja.create') --}}
    <hr>
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif 
            <div class="col">
                <form method="POST" action="{{ route('admin.diarioCaja.store') }}" class="row" enctype="multipart/form-data" data-callback="formCallback">
                        {{ csrf_field() }}       
                        <div class="col-lg col-md-12">
                            {{-- Asiento --}}
                            <div class="col-12 form-group mb-3">
                                <label for="asientoContable">Asiento Contable</label>
                                <input type="text" class="form-control" id="asientoContable" name="asientCcontable" value="{{$numeroAsiento}}" disabled >
                                <input type="hidden" name="asiento_contable" value="{{$numeroAsiento}}" >
                            </div>

                            {{-- Cuenta Contable --}}
                            <div class="col-12 form-group mb-3">
                                <label for="cuenta_id">Cuenta Contable</label>
                                <div class="input-group">
                                    <select name="cuenta_id" id="cuenta_id" class="selectpicker form-control" data-show-subtext="true" data-live-search="true">
                                        <option disabled value="">-- Seleccione Cuenta --</option>
                                        @foreach($response as $grupos)
                                            @foreach($grupos as $itemGroup)
                                            <option disabled value="">- {{$itemGroup['grupo']->numero .'. '. $itemGroup['grupo']->nombre}} -</option>
                                                @foreach($itemGroup['subGrupo'] as $subGrupo)
                                                    <option disabled value="">-- {{ $subGrupo['item']->numero .'. '. $subGrupo['item']->nombre}}  --</option>
                                                    @foreach($subGrupo['cuentas'] as $cuentas)
                                                        <option value="{{$cuentas['item']->numero}}">--- {{ $cuentas['item']->numero .'. '. $cuentas['item']->nombre}} ---</option>
                                                        @if(count($cuentas['subCuentas']) > 0)
                                                            @foreach($cuentas['subCuentas'] as $subCuentas)
                                                                <option value="{{$subCuentas['item']->numero}}">---- {{ $subCuentas['item']->numero .'. '. $subCuentas['item']->nombre}} ----</option>
                                                                @if(count($subCuentas['subCuentasHija']) > 0)
                                                                    @foreach($subCuentas['subCuentasHija'] as $subCuentasHijas)
                                                                        <option value="{{$subCuentasHijas->numero}}">---- {{ $subCuentasHijas->numero .'. '. $subCuentasHijas->nombre}} ----</option>
                                                                    @endforeach
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    @endforeach
                                                @endforeach
                                            @endforeach  
                                        @endforeach

                                    
                                    </select>
                                </div>
                            </div>

                            {{-- Ingreso --}}
                            <div class="col-12 form-group mb-3">
                                <label for="invoice_id">Ingreso</label>
                                <div class="input-group">
                                    <select name="ingreso_id" id="ingreso_id" class="selectpicker form-control" data-show-subtext="true" data-live-search="true">
                                        <option value="">-- Seleccione Ingreo --</option>
                                        @if($ingresos)
                                            @foreach($ingresos as $grupo)
                                                <option value="{{$grupo->id}}">{{$grupo->title}}</option>
                                            @endforeach
                                        @endif
                                    
                                    </select>
                                </div>
                            </div>

                            {{-- Fecha --}}
                            <div class="col-12 form-group mb-3">
                                <label for="date">Fecha</label>
                                <input type="date" class="form-control" id="date" name="date" >
                            </div>

                            {{-- Concepto --}}
                            <div class="col-12 form-group mb-3">
                                <label for="concepto">Concepto</label>
                                <input type="text" class="form-control" id="concepto" name="concepto" >
                            </div>

                            {{-- Debe --}}
                            {{-- <div class="col-12 form-group mb-3">
                                <label for="debe">Debe</label>
                                <input type="number" class="form-control" id="debe" name="debe" step="any" >
                            </div> --}}

                            {{-- Haber --}}
                            <div class="col-12 form-group mb-3">
                                <label for="haber">Importe</label>
                                <input type="number" class="form-control" id="haber" name="haber" step="any" >
                            </div>  
                             
                            <button type="submit" class="btn btn-primary w-100" >
                                Guardar
                            </button>     
                        </div>
                </form>
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
