<?php

namespace App\Http\Controllers;

use App\Models\Huesped;
use Illuminate\Http\Request;

class HuespedesController extends Controller
{
    //
    public function index(){

    }

    public function show(string $id){
        $huesped = Huesped::find($id);
        return view('huespedes.show', compact('id','huesped'));
    }
}
