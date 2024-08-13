<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request) {
        $anio = Carbon::now()->format();
        $reservas = Reserva::

        return view('admin.dashboard');
       // return Auth::user()->redirectToDashboard();
    }
}
