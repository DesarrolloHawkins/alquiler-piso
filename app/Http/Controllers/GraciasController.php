<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GraciasController extends Controller
{
    //
    public function index(){
        return view('gracias');
    }
    public function contacto(){
        return view('contacto');
    }
}
