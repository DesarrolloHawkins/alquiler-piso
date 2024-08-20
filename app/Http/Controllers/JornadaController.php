<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JornadaController extends Controller
{
    public function index() {
        return view('admin.jornada.index');
    }
}
