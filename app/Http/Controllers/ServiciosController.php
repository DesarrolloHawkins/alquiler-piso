<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use Illuminate\Http\Request;

class ServiciosController extends Controller
{
    /**
     * Mostrar página de servicios disponibles
     */
    public function index()
    {
        // Mostrar solo servicios con precio (extras comprables)
        $servicios = Servicio::activos()
            ->whereNotNull('precio')
            ->where('precio', '>', 0)
            ->ordenados()
            ->get();

        return view('public.servicios.index', compact('servicios'));
    }
}
