<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ChannexController extends Controller
{
    public function webhook(Request $request){
        $fecha = Carbon::now()->format('Y-m-d_H-i-s'); // Puedes cambiar el formato según lo que necesites

        Storage::disk('publico')->put("Channex-WebHook_{$fecha}.txt", json_encode($request->all()));

        return response()->json('Enviado correctamente', 200);
    }
}
