<?php

use App\Http\Controllers\RatePlanController;
use App\Http\Controllers\WebhookController;
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
Route::post('/equipo-limpieza', [App\Http\Controllers\Api\ApiController::class, 'equipoLimpieza'])->name('equipoLimpieza');
Route::post('/apartamentos-disponibles', [App\Http\Controllers\Api\ApiController::class, 'equipoLimpieza'])->name('equipoLimpieza');

Route::get('/room-types/{propertyId}', [RatePlanController::class, 'getRoomTypes']);

// Webhooks
Route::post('/channex', [App\Http\Controllers\ChannexController::class, 'webhook'])->name('channex.webhook');
Route::post('/ari-changes', [App\Http\Controllers\ChannexController::class, 'ariChanges'])->name('channex.ariChanges');
Route::post('/booking-any', [App\Http\Controllers\ChannexController::class, 'bookingAny'])->name('channex.bookingAny');
Route::post('/new-booking', [App\Http\Controllers\ChannexController::class, 'newBooking'])->name('channex.newBooking');
Route::post('/modification-booking', [App\Http\Controllers\ChannexController::class, 'modificationBooking'])->name('channex.modificationBooking');
Route::post('/cancellation-booking', [App\Http\Controllers\ChannexController::class, 'cancellationBooking'])->name('channex.cancellationBooking');
Route::post('/channel-sync-error', [App\Http\Controllers\ChannexController::class, 'channelSyncError'])->name('channex.channelSyncError');
Route::post('/reservation-request', [App\Http\Controllers\ChannexController::class, 'reservationRequest'])->name('channex.reservationRequest');
Route::post('/booking-unamapped-room', [App\Http\Controllers\ChannexController::class, 'bookingUnamappedRoom'])->name('channex.webhook');
Route::post('/booking-unamapped-rate', [App\Http\Controllers\ChannexController::class, 'bookingUnamappedRate'])->name('channex.bookingUnamappedRate');
Route::post('/sync-warning', [App\Http\Controllers\ChannexController::class, 'syncWarning'])->name('channex.syncWarning');
Route::post('/new-message', [App\Http\Controllers\ChannexController::class, 'newMessage'])->name('channex.newMessage');
Route::post('/new-review', [App\Http\Controllers\ChannexController::class, 'newReview'])->name('channex.newReview');
Route::post('/alteration-request', [App\Http\Controllers\ChannexController::class, 'alterationRequest'])->name('channex.alterationRequest');
Route::post('/airbnb-inquiry', [App\Http\Controllers\ChannexController::class, 'airbnbInquiry'])->name('channex.airbnbInquiry');
Route::post('/disconnect-channel', [App\Http\Controllers\ChannexController::class, 'disconnectChannel'])->name('channex.disconnectChannel');
Route::post('/disconnect-listing', [App\Http\Controllers\ChannexController::class, 'disconnectListing'])->name('channex.disconnectListing');
Route::post('/rate-error', [App\Http\Controllers\ChannexController::class, 'rateError'])->name('channex.rateError');
Route::post('/accepted-reservation', [App\Http\Controllers\ChannexController::class, 'acceptedReservation'])->name('channex.acceptedReservation');
Route::post('/decline-reservation', [App\Http\Controllers\ChannexController::class, 'declineReservation'])->name('channex.declineReservation');


// WEBHOOOKS POR APARTAMENTOS
Route::prefix('/webhooks')->group(function () {
    Route::post('{id}/ari-changes', [App\Http\Controllers\WebhookController::class, 'ariChanges'])->name('webhook.channex.ariChanges');
    Route::post('{id}/booking-any', [App\Http\Controllers\WebhookController::class, 'bookingAny'])->name('webhook.channex.bookingAny');
    Route::post('{id}/booking-unmapped-room', [App\Http\Controllers\WebhookController::class, 'bookingUnmappedRoom'])->name('webhook.channex.bookingAny');
    Route::post('{id}/booking-unmapped-rate', [App\Http\Controllers\WebhookController::class, 'bookingUnmappedRate'])->name('webhook.channex.bookingAny');
    Route::post('{id}/message', [App\Http\Controllers\WebhookController::class, 'message'])->name('webhook.channex.bookingAny');
    Route::post('{id}/review', [App\Http\Controllers\WebhookController::class, 'review'])->name('webhook.channex.bookingAny');
    Route::post('{id}/alteration_request', [App\Http\Controllers\WebhookController::class, 'alterationRequest'])->name('webhook.channex.bookingAny');
    Route::post('{id}/reservation-request', [App\Http\Controllers\WebhookController::class, 'reservationRequest'])->name('webhook.channex.bookingAny');
    Route::post('{id}/sync-error', [App\Http\Controllers\WebhookController::class, 'syncError'])->name('webhook.channex.syncWarning');
});

Route::post('/fotos-cocina-store/{id}/{cat}', [App\Http\Controllers\PhotoController::class, 'store'])->name('fotos.cocina-store');
