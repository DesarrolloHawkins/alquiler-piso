<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/obtener-reservas-hoy', [App\Http\Controllers\Api\ApiController::class, 'obtenerReservasHoy'])->name('obtenerReservasHoy');
Route::get('/obtener-apartamentos', [App\Http\Controllers\Api\ApiController::class, 'obtenerApartamentos'])->name('obtenerApartamentos');
Route::get('/obtener-apartamentos-disponibles', [App\Http\Controllers\Api\ApiController::class, 'obtenerApartamentosDisponibles'])->name('obtenerApartamentosDisponibles');
Route::post('/averias-tecnico', [App\Http\Controllers\Api\ApiController::class, 'averiasTecnico'])->name('averiasTecnico');
