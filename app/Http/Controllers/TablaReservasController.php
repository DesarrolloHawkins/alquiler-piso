<?php

namespace App\Http\Controllers;

use App\Models\Apartamento;
use Illuminate\Http\Request;

class TablaReservasController extends Controller
{
    public function index(){
        $apartamentos = Apartamento::all();
        return view('admin.reservas.tabla', compact('apartamentos'));
    }
}
