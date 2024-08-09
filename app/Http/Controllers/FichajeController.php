<?php

namespace App\Http\Controllers;

use App\Models\Fichaje;
use App\Models\Pausa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FichajeController extends Controller
{
    public function iniciarJornada()
    {
        $fichaje = new Fichaje([
            'user_id' => Auth::id(),
            'hora_entrada' => now(),
        ]);
        $fichaje->save();
        return back()->with('status', 'Jornada iniciada')->with('refresh', true);
    }

    public function iniciarPausa()
    {
        $fichaje = Fichaje::where('user_id', Auth::id())->whereNull('hora_salida')->latest()->first();

        if ($fichaje) {
            $pausa = new Pausa([
                'fichaje_id' => $fichaje->id,
                'inicio_pausa' => now(),
            ]);
            $pausa->save();
            return back()->with('status', 'Pausa iniciada')->with('refresh', true);
        }

        return back()->with('error', 'No se encontró una jornada activa');
    }

    public function finalizarPausa()
    {
        $fichaje = Fichaje::where('user_id', Auth::id())->latest()->first();
        $pausa = $fichaje->pausas()->whereNull('fin_pausa')->first();
        $pausa->fin_pausa = now();
        $pausa->save();
        return back()->with('status', 'Pausa finalizada')->with('refresh', true);
    }

    public function finalizarJornada()
    {
        $fichaje = Fichaje::where('user_id', Auth::id())->latest()->first();
        $fichaje->hora_salida = now();
        $fichaje->save();
        return back()->with('status', 'Jornada finalizada');
    }

    public function showControlPanel()
    {
        $hoy = now()->toDateString();
        $fichajeHoy = Fichaje::where('user_id', Auth::id())
                            ->whereDate('hora_entrada', $hoy)
                            ->latest()
                            ->first();

        $pausaActiva = null;
        if ($fichajeHoy && !$fichajeHoy->hora_salida) {
            $pausaActiva = $fichajeHoy->pausas()->whereNull('fin_pausa')->latest()->first();
        }

        return view('fichajes', compact('fichajeHoy', 'pausaActiva'));
    }
}
