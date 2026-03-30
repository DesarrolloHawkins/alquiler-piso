<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckinLinkController extends Controller
{
    /**
     * Genera un enlace firmado con los datos de la reserva para enviar al huésped.
     */
    public function generarLink(Request $request, $reservaId)
    {
        $reserva = Reserva::with(['cliente', 'apartamento'])->find($reservaId);

        if (!$reserva) {
            return response()->json(['error' => 'Reserva no encontrada'], 404);
        }

        $payload = [
            'reserva_id' => $reserva->id,
            'nombre'     => $reserva->cliente->nombre ?? '',
            'apellido'   => $reserva->cliente->apellido1 ?? '',
            'email'      => $reserva->cliente->email ?? '',
            'telefono'   => $reserva->cliente->telefono_movil ?? $reserva->cliente->telefono ?? '',
            'checkin'    => $reserva->fecha_entrada,
            'checkout'   => $reserva->fecha_salida,
            'apartamento' => $reserva->apartamento->nombre ?? '',
            'exp'        => now()->addDays(7)->timestamp,
        ];

        $encoded   = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', $encoded, config('app.key'));
        $token     = $encoded . '.' . $signature;

        $reserva->update(['token' => $token]);

        return response()->json([
            'url'   => config('services.checkin.url') . '/checkin?token=' . urlencode($token),
            'token' => $token,
        ]);
    }

    /**
     * Recibe los datos completos del huésped desde la app externa de registro.
     */
    public function recibirDatos(Request $request)
    {
        try {
            $request->validate([
                'token'  => 'required|string',
                'guests' => 'required|array',
            ]);

            $token = $request->input('token');
            $guests = $request->input('guests');

            $parts = explode('.', $token, 2);
            if (count($parts) !== 2) {
                return response()->json(['error' => 'Token inválido'], 401);
            }

            [$encoded, $signature] = $parts;

            $expectedSignature = hash_hmac('sha256', $encoded, config('app.key'));
            if (!hash_equals($expectedSignature, $signature)) {
                return response()->json(['error' => 'Firma inválida'], 401);
            }

            $payload = json_decode(base64_decode($encoded), true);

            if (!$payload || !isset($payload['exp']) || $payload['exp'] < now()->timestamp) {
                return response()->json(['error' => 'Token expirado'], 401);
            }

            $reserva = Reserva::find($payload['reserva_id']);

            if (!$reserva || $reserva->token !== $token) {
                return response()->json(['error' => 'Token no coincide con la reserva'], 401);
            }

            // Los campos vienen con los nombres del app de registro de visitantes
            // y se mapean a los nombres del modelo Cliente del CRM
            $guest = $guests[0];

            $map = [
                // campo_registro_visitantes => campo_cliente_crm
                'first_name'       => 'nombre',
                'last_name'        => 'apellido1',
                'email'            => 'email',
                'phone'            => 'telefono_movil',
                'document_number'  => 'num_identificacion',
                'document_type'    => 'tipo_documento_str',
                'birth_date'       => 'fecha_nacimiento',
                'gender'           => 'sexo_str',
                'nationality'      => 'nacionalidadStr',
                'address'          => 'direccion',
                'postal_code'      => 'codigo_postal',
                'city'             => 'localidad',
            ];

            $clienteData = [];
            foreach ($map as $guestField => $clienteField) {
                if (!empty($guest[$guestField])) {
                    $clienteData[$clienteField] = $guest[$guestField];
                }
            }

            if (!empty($clienteData) && $reserva->cliente) {
                $reserva->cliente->update($clienteData);
            }

            $reserva->update([
                'dni_entregado' => 1,
                'verificado'    => 1,
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('CheckinLinkController@recibirDatos error: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
}
