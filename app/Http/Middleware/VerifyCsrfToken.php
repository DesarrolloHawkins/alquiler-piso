<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/agregar-reserva',
        '/comprobar-reserva/*',
		'/verificar-reserva/*',
        '/actualizar-booking/*',
        '/actualizar-airbnb/*',
        '/whatsapp',
        '/comprobacion-server',
        '/pass-booking',
        '/pass-airbnb',
        '/obtener-reserva',
        '/obtener-codigos',
        '/obtener-codigos-airbnb',
        '/cancelar-booking/*',
        '/cancelar-booking',
        'gastos-introducir',
        'ingresos-introducir',
        '/whatsapp-envio'
    ];
}
