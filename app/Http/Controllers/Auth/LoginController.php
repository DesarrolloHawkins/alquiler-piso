<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    // ...

    /**
     * Where to redirect users after login, based on their role.
     *
     * @return string
     */
    protected function redirectTo()
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->role === 'ADMIN') {
                return '/dashboard';  // Ruta de redirección para administradores.
            } elseif ($user->role === 'USER ') {
                return '/dashboard';  // Ruta de redirección para usuarios estándar.
            }
        }

        return RouteServiceProvider::HOME; // O puedes poner una ruta por defecto aquí.
    }

    // ...
}
