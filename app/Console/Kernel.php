<?php

namespace App\Console;

use App\Models\MensajeAuto;
use App\Models\Reserva;
use App\Services\ClienteService;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Kernel extends ConsoleKernel
{
    protected $clienteService;

    public function __construct(ClienteService $clienteService)
    {
        $this->clienteService = $clienteService;
    }
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        // Miramos si el cliente tiene la Nacionalidad e idioma
        $schedule->call(function () {
            // Obtener la fecha de hoy
            $hoy = Carbon::now();
            // Obtenemos la reservas que sean igual o superior a la fecha de entrada de hoy y no tengan el DNI Enrtegado.
            $reservasEntrada = Reserva::where('dni_entregado', null)
            ->where('estado_id', 1)
            ->where('fecha_entrada', '>=', $hoy->toDateString())
            ->get();

            foreach($reservasEntrada as $reserva){
                $resultado = $this->clienteService->getIdiomaClienteID($reserva->cliente_id);
            }
            Log::info("Tarea programada de Nacionalidad del cliente ejecutada con éxito.");
        })->everyMinute();

        // Miramos si el cliente tiene la Nacionalidad e idioma
        $schedule->call(function () {
            // Obtener la fecha de hoy
            $hoy = Carbon::now();
            // Obtener la fecha de dos días después
            $dosDiasDespues = Carbon::now()->addDays(2)->format('Y-m-d');

            // Modificar la consulta para obtener reservas desde hoy hasta dentro de dos días
            $reservasEntrada = Reserva::where('dni_entregado', null)
            ->where('estado_id', 1)
            ->where('cliente_id',133)
            ->get();
            // $reservasEntrada = Reserva::whereBetween('fecha_entrada', [date('Y-m-d'), $dosDiasDespues])
            // ->where('estado_id', 1)
            // ->get();

            // Validamos si hay reservas pendiente del DNI
            if(count($reservasEntrada) != 0){
                // Recorremos las reservas
                foreach($reservasEntrada as $reserva){

                    // Obtenemos el mensaje del DNI si existe
                    $mensajeDNI = MensajeAuto::where('reserva_id', $reserva->id)->where('categoria_id', 1)->first();
                    // Validamos si existe mensaje de DNI enviado
                    if ($mensajeDNI == null) {
                        $token = bin2hex(random_bytes(16)); // Genera un token de 32 caracteres
                        $reserva->token = $token;
                        $reserva->save();
                        $mensaje = 'Desde hawkins le solicitamos que rellenes sus datos para poder continuar con la reserva, entre en el siguiente enlace para completarla: https://crm.apartamentosalgeciras.com/dni-user/'.$token;
                        $phoneCliente =  $this->limpiarNumeroTelefono($reserva->cliente->telefono);
                        $enviarMensaje = $this->contestarWhatsapp($phoneCliente, $mensaje);
                        // return $enviarMensaje;

                        // Data para guardar Mensaje enviado
                        $dataMensaje = [
                            'reserva_id' => $reserva->id,
                            'cliente_id' => $reserva->cliente_id,
                            'categoria_id' => 1,
                            'fecha_envio' => Carbon::now()
                        ];

                        MensajeAuto::create($dataMensaje);                    
                        
                    }
                }
                
            }
            Log::info("Tarea programada de Nacionalidad del cliente ejecutada con éxito.");
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
    function limpiarNumeroTelefono($numero) {
        // Eliminar el signo más y cualquier espacio
        $numeroLimpio = preg_replace('/\+|\s+/', '', $numero);
    
        return $numeroLimpio;
    }
    public function contestarWhatsapp($phone, $texto){
        $token = env('TOKEN_WHATSAPP', 'valorPorDefecto');
        // return $texto;
        $mensajePersonalizado = '{
            "messaging_product": "whatsapp",
            "recipient_type": "individual",
            "to": "'.$phone.'",
            "type": "text", 
            "text": { 
                "body": "'.$texto.'"
            }
        }';
        // return $mensajePersonalizado;

        $urlMensajes = 'https://graph.facebook.com/v16.0/102360642838173/messages';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $urlMensajes,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $mensajePersonalizado,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ),
        
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        // $responseJson = json_decode($response);
        Storage::disk('local')->put('response0001.txt', json_encode($response) . json_encode($mensajePersonalizado) );
        return $response;

    }  

}
