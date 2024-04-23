<?php

namespace App\Http\Controllers;

use App\Models\Huesped;
use App\Models\Photo;
use Illuminate\Http\Request;

class HuespedesController extends Controller
{
    //
    public function index(){

    }

    public function show(string $id){
        $huesped = Huesped::find($id);
        $photos = Photo::where('huesped_id', $id)->get();
        return view('huespedes.show', compact('id','huesped','photos'));
    }
}
