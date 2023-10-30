<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// Vistas
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/clientes', [App\Http\Controllers\ClientesController::class, 'index'])->name('clientes.index');
Route::get('/clientes/create', [App\Http\Controllers\ClientesController::class, 'create'])->name('clientes.create');
Route::get('/reservas', [App\Http\Controllers\ReservasController::class, 'index'])->name('reservas.index');

// Añadir Reserva
Route::post('/agregar-reserva', [App\Http\Controllers\ReservasController::class, 'agregarReserva'])->name('reservas.agregarReserva');

// Verificar Reserva de Booking
Route::post('/verificar-reserva', [App\Http\Controllers\ComprobarReserva::class, 'verificarReserva'])->name('reservas.verificarReserva');
Route::post('/enviar-dni/{id}', [App\Http\Controllers\ReservasController::class, 'enviarDni'])->name('reservas.enviarDni');

// Verificar Reserva de Airbnb
Route::get('/comprobar-reserva/{id}', [App\Http\Controllers\ComprobarReserva::class, 'index'])->name('comprobar.index');
Route::get('/comprobar-reserva-web/{id}', [App\Http\Controllers\ComprobarReserva::class, 'comprobarReservaWeb'])->name('comprobar.comprobarReservaWeb');
Route::post('/cancelar-airbnb/{reserva}', [App\Http\Controllers\ReservasController::class, 'cancelarAirBnb'])->name('cancelarAirBnb.index');

// Gestion del Apartamento
Route::get('/gestion', [App\Http\Controllers\GestionApartamentoController::class, 'index'])->name('gestion.index');
Route::get('/gestion-create/{id}', [App\Http\Controllers\GestionApartamentoController::class, 'create'])->name('gestion.create');
Route::post('/gestion-store', [App\Http\Controllers\GestionApartamentoController::class, 'store'])->name('gestion.store');
Route::get('/gestion-edit/{apartamentoLimpieza}', [App\Http\Controllers\GestionApartamentoController::class, 'edit'])->name('gestion.edit');
Route::post('/gestion-update/{apartamentoLimpieza}', [App\Http\Controllers\GestionApartamentoController::class, 'update'])->name('gestion.update');
Route::post('/gestion-finalizar/{apartamentoLimpieza}', [App\Http\Controllers\GestionApartamentoController::class, 'finalizar'])->name('gestion.finalizar');

// Fotos
Route::get('/fotos-dormitorio/{id}', [App\Http\Controllers\PhotoController::class, 'indexDormitorio'])->name('fotos.dormitorio');
Route::post('/fotos-dormitorio-store/{id}', [App\Http\Controllers\PhotoController::class, 'dormitorioStore'])->name('fotos.dormitorioStore');
Route::get('/fotos-salon/{id}', [App\Http\Controllers\PhotoController::class, 'indexSalon'])->name('fotos.salon');
Route::post('/fotos-salon-store/{id}', [App\Http\Controllers\PhotoController::class, 'salonStore'])->name('fotos.salonStore');
Route::get('/fotos-cocina/{id}', [App\Http\Controllers\PhotoController::class, 'indexCocina'])->name('fotos.cocina');
Route::post('/fotos-cocina-store/{id}', [App\Http\Controllers\PhotoController::class, 'cocinaStore'])->name('fotos.cocinaStore');
Route::get('/fotos-banio/{id}', [App\Http\Controllers\PhotoController::class, 'indexBanio'])->name('fotos.banio');
Route::post('/fotos-banio-store/{id}', [App\Http\Controllers\PhotoController::class, 'banioStore'])->name('fotos.banioStore');
