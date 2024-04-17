<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index() {
        $reservasPendientes = Reserva::apartamentosPendiente();

        return Auth::user()->redirectToDashboard( $reservasPendientes );
    }
}
