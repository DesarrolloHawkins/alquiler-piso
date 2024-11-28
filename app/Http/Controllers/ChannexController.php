<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChannexController extends Controller
{
    public function webhook(Request $request){
        Storage::disk('publico')->put('Channex-WebHook.txt', json_encode($request->all()));

        return response()->json('Enviado correctamente', 200);
    }
}
