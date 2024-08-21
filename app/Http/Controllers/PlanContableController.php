<?php

// App\Http\Controllers\PlanContableController.php
namespace App\Http\Controllers;

use App\Models\GrupoContable;
use Illuminate\Http\Request;

class PlanContableController extends Controller
{
    public function index()
    {
        $grupos = GrupoContable::with('subGrupos.cuentas.subCuentas.cuentasHijas')->orderBy('numero', 'asc')->get();
        return view('admin.contabilidad.planContable.index', compact('grupos'));
    }
}