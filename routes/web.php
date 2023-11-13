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
    if (Auth::check()) {
        // El usuario está autenticado, redirige a la ruta deseada.
        return redirect()->route('gestion.index');
    }
    return view('welcome');
});

Auth::routes();

// Vistas
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/clientes', [App\Http\Controllers\ClientesController::class, 'index'])->name('clientes.index');
Route::get('/clientes/create', [App\Http\Controllers\ClientesController::class, 'create'])->name('clientes.create');
Route::get('/reservas', [App\Http\Controllers\ReservasController::class, 'index'])->name('reservas.index');
Route::get('/get-reservas', [App\Http\Controllers\ReservasController::class, 'getReservas'])->name('reservas.get');

// Añadir Reserva
Route::post('/agregar-reserva', [App\Http\Controllers\ReservasController::class, 'agregarReserva'])->name('reservas.agregarReserva');

// Verificar Reserva de Booking
Route::post('/verificar-reserva/{reserva}', [App\Http\Controllers\ComprobarReserva::class, 'verificarReserva'])->name('reservas.verificarReserva');
Route::post('/enviar-dni/{id}', [App\Http\Controllers\ReservasController::class, 'enviarDni'])->name('reservas.enviarDni');
Route::post('/cancelar-booking/{reserva}', [App\Http\Controllers\ReservasController::class, 'cancelarBooking'])->name('cancelarBooking.index');
Route::post('/actualizar-booking/{reserva}', [App\Http\Controllers\ReservasController::class, 'actualizarBooking'])->name('actualizarBooking.index');

// Verificar Reserva de Airbnb
Route::get('/comprobar-reserva/{id}', [App\Http\Controllers\ComprobarReserva::class, 'index'])->name('comprobar.index');
Route::get('/comprobar-reserva-web/{id}', [App\Http\Controllers\ComprobarReserva::class, 'comprobarReservaWeb'])->name('comprobar.comprobarReservaWeb');
Route::post('/cancelar-airbnb/{reserva}', [App\Http\Controllers\ReservasController::class, 'cancelarAirBnb'])->name('cancelarAirBnb.index');
Route::post('/actualizar-airbnb/{reserva}', [App\Http\Controllers\ReservasController::class, 'actualizarAirbnb'])->name('actualizarAirbnb.index');


// Gestion del Apartamento
Route::get('/gestion', [App\Http\Controllers\GestionApartamentoController::class, 'index'])->name('gestion.index');
Route::get('/gestion-create/{id}', [App\Http\Controllers\GestionApartamentoController::class, 'create'])->name('gestion.create');
Route::post('/gestion-store', [App\Http\Controllers\GestionApartamentoController::class, 'store'])->name('gestion.store');
Route::get('/gestion-edit/{apartamentoLimpieza}', [App\Http\Controllers\GestionApartamentoController::class, 'edit'])->name('gestion.edit');
Route::post('/gestion-update/{apartamentoLimpieza}', [App\Http\Controllers\GestionApartamentoController::class, 'update'])->name('gestion.update');
Route::post('/gestion-finalizar/{apartamentoLimpieza}', [App\Http\Controllers\GestionApartamentoController::class, 'finalizar'])->name('gestion.finalizar');
Route::post('/gestion-store-column', [App\Http\Controllers\GestionApartamentoController::class, 'storeColumn'])->name('gestion.storeColumn');

// Fotos
Route::get('/fotos-dormitorio/{id}', [App\Http\Controllers\PhotoController::class, 'indexDormitorio'])->name('fotos.dormitorio');
Route::post('/fotos-dormitorio-store/{id}', [App\Http\Controllers\PhotoController::class, 'dormitorioStore'])->name('fotos.dormitorioStore');
Route::get('/fotos-salon/{id}', [App\Http\Controllers\PhotoController::class, 'indexSalon'])->name('fotos.salon');
Route::post('/fotos-salon-store/{id}', [App\Http\Controllers\PhotoController::class, 'salonStore'])->name('fotos.salonStore');
Route::get('/fotos-cocina/{id}', [App\Http\Controllers\PhotoController::class, 'indexCocina'])->name('fotos.cocina');
Route::post('/fotos-cocina-store/{id}', [App\Http\Controllers\PhotoController::class, 'cocinaStore'])->name('fotos.cocinaStore');
Route::get('/fotos-banio/{id}', [App\Http\Controllers\PhotoController::class, 'indexBanio'])->name('fotos.banio');
Route::post('/fotos-banio-store/{id}', [App\Http\Controllers\PhotoController::class, 'banioStore'])->name('fotos.banioStore');

// Obtener DNI
Route::get('/dni-user/{id}', [App\Http\Controllers\DNIController::class, 'index'])->name('dni.index');
Route::post('/dni-user/store', [App\Http\Controllers\DNIController::class, 'store'])->name('dni.store');
Route::get('/dni-user-subir/{id}', [App\Http\Controllers\DNIController::class, 'dniUpload'])->name('dni.dniUpload');
Route::get('/pasaporte-user-subir/{id}', [App\Http\Controllers\DNIController::class, 'pasaporteUpload'])->name('dni.dniUpload');
Route::get('/dni/{id}', [App\Http\Controllers\DNIController::class, 'dni'])->name('dni.dni');
Route::get('/pasaporte/{id}', [App\Http\Controllers\DNIController::class, 'pasaporte'])->name('dni.pasaporte');


Route::get('/gracias', [App\Http\Controllers\GraciasController::class, 'index'])->name('gracias.index');
