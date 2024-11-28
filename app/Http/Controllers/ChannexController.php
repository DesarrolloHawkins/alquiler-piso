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

    public function ariChanges(Request $request){
        $fecha = Carbon::now()->format('Y-m-d_H-i-s'); // Puedes cambiar el formato según lo que necesites

        Storage::disk('publico')->put("ari-changes_{$fecha}.txt", json_encode($request->all()));

        return response()->json('Enviado correctamente', 200);
    }

    public function bookingAny(Request $request){
        $fecha = Carbon::now()->format('Y-m-d_H-i-s'); // Puedes cambiar el formato según lo que necesites

        Storage::disk('publico')->put("booking-any_{$fecha}.txt", json_encode($request->all()));

        return response()->json('Enviado correctamente', 200);
    }

    public function newBooking(Request $request){
        $fecha = Carbon::now()->format('Y-m-d_H-i-s'); // Puedes cambiar el formato según lo que necesites

        Storage::disk('publico')->put("new-booking_{$fecha}.txt", json_encode($request->all()));

        return response()->json('Enviado correctamente', 200);
    }

    public function modificationBooking(Request $request){
        $fecha = Carbon::now()->format('Y-m-d_H-i-s'); // Puedes cambiar el formato según lo que necesites

        Storage::disk('publico')->put("modification-booking_{$fecha}.txt", json_encode($request->all()));

        return response()->json('Enviado correctamente', 200);
    }

    public function cancellationBooking(Request $request){
        $fecha = Carbon::now()->format('Y-m-d_H-i-s'); // Puedes cambiar el formato según lo que necesites

        Storage::disk('publico')->put("cancellation-booking_{$fecha}.txt", json_encode($request->all()));

        return response()->json('Enviado correctamente', 200);
    }

    public function channelSyncError(Request $request){
        $fecha = Carbon::now()->format('Y-m-d_H-i-s'); // Puedes cambiar el formato según lo que necesites

        Storage::disk('publico')->put("channel-sync-error_{$fecha}.txt", json_encode($request->all()));

        return response()->json('Enviado correctamente', 200);
    }

    public function reservationRequest(Request $request){
        $fecha = Carbon::now()->format('Y-m-d_H-i-s'); // Puedes cambiar el formato según lo que necesites

        Storage::disk('publico')->put("reservation-request_{$fecha}.txt", json_encode($request->all()));

        return response()->json('Enviado correctamente', 200);
    }

    public function bookingUnamappedRoom(Request $request){
        $fecha = Carbon::now()->format('Y-m-d_H-i-s'); // Puedes cambiar el formato según lo que necesites

        Storage::disk('publico')->put("booking-unamapped-room_{$fecha}.txt", json_encode($request->all()));

        return response()->json('Enviado correctamente', 200);
    }

    public function bookingUnamappedRate(Request $request){
        $fecha = Carbon::now()->format('Y-m-d_H-i-s'); // Puedes cambiar el formato según lo que necesites

        Storage::disk('publico')->put("booking-unamapped-rate_{$fecha}.txt", json_encode($request->all()));

        return response()->json('Enviado correctamente', 200);
    }

    public function syncWarning(Request $request){
        $fecha = Carbon::now()->format('Y-m-d_H-i-s'); // Puedes cambiar el formato según lo que necesites

        Storage::disk('publico')->put("sync-warning_{$fecha}.txt", json_encode($request->all()));

        return response()->json('Enviado correctamente', 200);
    }

    public function newMessage(Request $request){
        $fecha = Carbon::now()->format('Y-m-d_H-i-s'); // Puedes cambiar el formato según lo que necesites

        Storage::disk('publico')->put("new-message_{$fecha}.txt", json_encode($request->all()));

        return response()->json('Enviado correctamente', 200);
    }

    public function newReview(Request $request){
        $fecha = Carbon::now()->format('Y-m-d_H-i-s'); // Puedes cambiar el formato según lo que necesites

        Storage::disk('publico')->put("new-review_{$fecha}.txt", json_encode($request->all()));

        return response()->json('Enviado correctamente', 200);
    }

    public function alterationRequest(Request $request){
        $fecha = Carbon::now()->format('Y-m-d_H-i-s'); // Puedes cambiar el formato según lo que necesites

        Storage::disk('publico')->put("alteration-request_{$fecha}.txt", json_encode($request->all()));

        return response()->json('Enviado correctamente', 200);
    }

    public function airbnbInquiry(Request $request){
        $fecha = Carbon::now()->format('Y-m-d_H-i-s'); // Puedes cambiar el formato según lo que necesites

        Storage::disk('publico')->put("airbnb-inquiry_{$fecha}.txt", json_encode($request->all()));

        return response()->json('Enviado correctamente', 200);
    }

    public function disconnectChannel(Request $request){
        $fecha = Carbon::now()->format('Y-m-d_H-i-s'); // Puedes cambiar el formato según lo que necesites

        Storage::disk('publico')->put("disconnect-channel_{$fecha}.txt", json_encode($request->all()));

        return response()->json('Enviado correctamente', 200);
    }

    public function disconnectListing(Request $request){
        $fecha = Carbon::now()->format('Y-m-d_H-i-s'); // Puedes cambiar el formato según lo que necesites

        Storage::disk('publico')->put("disconnect-listing_{$fecha}.txt", json_encode($request->all()));

        return response()->json('Enviado correctamente', 200);
    }

    public function rateError(Request $request){
        $fecha = Carbon::now()->format('Y-m-d_H-i-s'); // Puedes cambiar el formato según lo que necesites

        Storage::disk('publico')->put("rate-error_{$fecha}.txt", json_encode($request->all()));

        return response()->json('Enviado correctamente', 200);
    }

    public function acceptedReservation(Request $request){
        $fecha = Carbon::now()->format('Y-m-d_H-i-s'); // Puedes cambiar el formato según lo que necesites

        Storage::disk('publico')->put("accepted-reservation{$fecha}.txt", json_encode($request->all()));

        return response()->json('Enviado correctamente', 200);
    }

    public function declineReservation(Request $request){
        $fecha = Carbon::now()->format('Y-m-d_H-i-s'); // Puedes cambiar el formato según lo que necesites

        Storage::disk('publico')->put("decline-reservation_{$fecha}.txt", json_encode($request->all()));

        return response()->json('Enviado correctamente', 200);
    }
}
