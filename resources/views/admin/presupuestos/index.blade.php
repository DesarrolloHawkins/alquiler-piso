@extends('layouts.appAdmin')

@section('content')
<div class="container">
    <h1>Presupuestos</h1>
    <a href="{{ route('presupuestos.create') }}" class="btn btn-primary mb-3">Crear Presupuesto</a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th><th>Cliente</th><th>Fecha</th><th>Total</th><th>Estado</th><th>Acciones</th>
            </tr>
        </thead>
        <tbody>
           @forelse($presupuestos as $p)
           <tr>
               <td>{{ $p->id }}</td>
               <td>{{ $p->cliente->nombre ?? '—' }}</td>
               <td>{{ $p->fecha }}</td>
               <td>{{ number_format($p->total,2) }} €</td>
               <td>
                   <span class="badge
                     {{ $p->estado=='facturado' ? 'bg-success' : 'bg-secondary' }}">
                     {{ ucfirst($p->estado) }}
                   </span>
               </td>
               <td>
                   <a href="{{ route('presupuestos.show',$p->id) }}"
                      class="btn btn-info btn-sm">Ver</a>

                   @if($p->estado !== 'facturado')
                       <a href="{{ route('presupuestos.edit',$p->id) }}"
                          class="btn btn-warning btn-sm">Editar</a>

                       <form action="{{ route('presupuestos.destroy',$p->id) }}"
                             method="POST" style="display:inline">
                           @csrf @method('DELETE')
                           <button onclick="return confirm('¿Está seguro?')"
                                   class="btn btn-danger btn-sm">Eliminar</button>
                       </form>

                       <form action="{{ route('presupuestos.facturar',$p->id) }}"
                             method="POST" style="display:inline">
                           @csrf
                           <button class="btn btn-success btn-sm"
                                   onclick="return confirm('Facturar presupuesto?')">
                              Facturar
                           </button>
                       </form>
                   @endif
               </td>
           </tr>
           @empty
           <tr>
             <td colspan="6" class="text-center">No hay presupuestos.</td>
           </tr>
           @endforelse
        </tbody>
    </table>
</div>
@endsection
