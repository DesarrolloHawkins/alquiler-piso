<?php

namespace App\Http\Controllers;

use App\Models\CategoriaGastos;
use App\Models\CategoriaIngresos;
use App\Models\DiarioCaja;
use Illuminate\Http\Request;
use App\Models\Ingresos;
use App\Models\Gastos;
use App\Models\Reserva;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class MovimientosController extends Controller
{

    public function uploadFiles(){
        return view('admin.movimientos.upload');
    }
    public function uploadBooking(){
        return view('admin.movimientos.uploadBooking');
    }

    public function uploadExcel(Request $request){

        // Validación del archivo Excel
        $request->validate([
            'file' => 'required|mimes:xlsx'
        ]);
        // Devolver la respuesta decodificada
        return response()->json([
            'status' => 'success',
            'data' => $request->all()
        ]);


        // Cargar el archivo Excel del request
        $file = $request->file('file');
        $filePath = $file->getRealPath();
        $fileContent = file_get_contents($filePath);
        $fileBase64 = base64_encode($fileContent);


        $prompt = '
        "Necesito procesar un archivo de Excel con movimientos bancarios y devolver la información en un formato JSON. El archivo contiene las columnas Fecha Operación, Fecha Valor, Concepto e Importe. A continuación, los pasos que el programa debe seguir:
        Cargar el archivo Excel desde el usuario.
        Identificar las columnas mencionadas, incluso si los encabezados no son exactamente iguales (debería ser capaz de adaptarse a ligeros cambios de texto).
        Convertir las fechas a un formato estándar ISO 8601 (YYYY-MM-DD).
        Convertir el importe a formato numérico, respetando el uso de decimales y separadores europeos (puntos o comas).
        Determinar si cada transacción es un ingreso o un gasto según el valor del importe (positivo para ingreso, negativo para gasto).
        Generar un archivo JSON que contenga un arreglo de objetos con los siguientes campos para cada movimiento:
        fecha_operacion (formato ISO 8601)
        fecha_valor (formato ISO 8601, si está disponible)
        concepto (descripción textual del movimiento)
        importe (número decimal)
        tipo (valor Ingreso o Gasto basado en el importe).
        Debe asegurar que:

        Los caracteres especiales (acentos, ñ, etc.) sean correctamente representados en el archivo JSON.
        La salida sea un archivo JSON válido con codificación UTF-8.
        ';

        // Configuración de la solicitud a OpenAI
        $token = env('TOKEN_OPENAI', 'valorPorDefecto');
        $url = 'https://api.openai.com/v1/chat/completions';
        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ];

        // Construir el contenido del mensaje
        $data = [
            "model" => "gpt-4",
            "messages" => [
                [
                    "role" => "user",
                    "content" => $prompt
                ],
                [
                    "role" => "user",
                    "content" => "Aquí tienes el archivo Excel en base64 para procesar.",
                    "name" => "file",
                    "content" => $fileBase64
                ]
            ]
        ];

        // Inicializar cURL y configurar las opciones
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // Ejecutar la solicitud y obtener la respuesta
        $response = curl_exec($curl);
        curl_close($curl);
        dd($response);
        // Guardar la respuesta en un archivo
        Storage::disk('public')->put('RespuestaMovimientos.json', $response);

        // Decodificar la respuesta JSON
        $response_data = json_decode($response, true);

        // Manejo de errores
        if ($response === false || !$response_data) {
            return response()->json(['status' => 'error', 'message' => 'Error al realizar la solicitud']);
        }

        // Devolver la respuesta decodificada
        return response()->json([
            'status' => 'success',
            'data' => $response_data
        ]);
    }

    public function uploadExcel2(Request $request)
    {
        // Validación del archivo
        $request->validate([
            'file' => 'required|mimes:xlsx'
        ]);

        // Leer el archivo Excel
        $file = $request->file('file');
        $data = Excel::toArray([], $file);

        // Procesar el primer sheet del archivo Excel
        $rows = $data[0];

        // Filtrar y ordenar las filas por fecha contable
        $filteredRows = array_filter($rows, function ($row, $index) {
            return $index >= 5 && isset($row[0]) && !empty($row[0]) && strtolower($row[0]) !== 'fecha contable' && is_numeric($row[0]);
        }, ARRAY_FILTER_USE_BOTH);

        usort($filteredRows, function ($a, $b) {
            return (float)$a[0] <=> (float)$b[0];
        });

        // Procesar cada fila
        foreach ($filteredRows as $row) {
            try {
                // Convertir el número de fecha de Excel en una fecha válida de Carbon
                $fecha_contable = Carbon::createFromFormat('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[0])->format('Y-m-d'));
            } catch (\Exception $e) {
                // Si no se puede convertir a fecha, saltar esta fila
                continue;
            }

            $descripcion = $row[5];
            $debe = $row[7];  // Gastos
            $haber = $row[8]; // Ingresos
            $saldo = $row[10]; // Ingresos

            // Generar un hash único basado en la combinación de fecha, descripción, debe y haber
            $hash = md5($fecha_contable->format('Y-m-d') . $descripcion . (float)$debe . (float)$haber . (float)$saldo);

            // Verificar si ya existe un registro con este hash
            $existingHash = DB::table('hash_movimientos')
                ->where('hash', $hash)
                ->first();

            if ($existingHash) {
                continue; // Si ya existe el hash, saltar esta fila para evitar duplicados
            }

            // Obtener una categoría por defecto (ajustar según tu lógica)
            $categoria_ingreso = CategoriaIngresos::first();
            $categoria_gasto = CategoriaGastos::first();

            // Si es un ingreso (HABER)
            if (!empty($haber) && $haber > 0) {
                // Verificar si el ingreso ya existe en la tabla de ingresos
                $existingIngreso = Ingresos::where('date', $fecha_contable)
                    ->where('title', $descripcion)
                    ->where('quantity', $haber)
                    ->first();

                if (!$existingIngreso) {
                    // Crear el ingreso
                    $ingreso = Ingresos::create([
                        'categoria_id' => $categoria_ingreso->id ?? 1,
                        'bank_id' => 1,      // Ajusta según tu lógica
                        'title' => $descripcion,
                        'quantity' => $haber,
                        'date' => $fecha_contable,
                        'estado_id' => 1
                    ]);

                    // Reflejar el ingreso en el Diario de Caja
                    DiarioCaja::create([
                        'asiento_contable' => $this->generarAsientoContable(),
                        'cuenta_id' => 1, // Aquí seleccionas la cuenta contable adecuada
                        'ingreso_id' => $ingreso->id,
                        'date' => $fecha_contable,
                        'concepto' => $descripcion,
                        'haber' => $haber,
                        'tipo' => 'ingreso',
                        'estado_id' => 1
                    ]);
                }
            }

            // Si es un gasto (DEBE)
            if (!empty($debe) && $debe != 0) {
                // Verificar si el gasto ya existe en la tabla de gastos
                $existingGasto = Gastos::where('date', $fecha_contable)
                    ->where('title', $descripcion)
                    ->where('quantity', $debe)
                    ->first();

                if (!$existingGasto) {
                    // Crear el gasto
                    $gasto = Gastos::create([
                        'categoria_id' => $categoria_gasto->id ?? 1,
                        'bank_id' => 1,
                        'title' => $descripcion,
                        'quantity' => $debe,
                        'date' => $fecha_contable,
                        'estado_id' => 1
                    ]);

                    // Reflejar el gasto en el Diario de Caja
                    DiarioCaja::create([
                        'asiento_contable' => $this->generarAsientoContable(),
                        'cuenta_id' => 1, // Aquí seleccionas la cuenta contable adecuada
                        'gasto_id' => $gasto->id,
                        'date' => $fecha_contable,
                        'concepto' => $descripcion,
                        'debe' => $debe,
                        'tipo' => 'gasto',
                        'estado_id' => 1
                    ]);
                }
            }

            // Guardar el hash en la tabla de hash_movimientos para evitar duplicados futuros
            DB::table('hash_movimientos')->insert([
                'hash' => $hash,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return response()->json(['message' => 'Archivo procesado correctamente.']);
    }


    public function uploadCSV(Request $request)
    {
        // Validar que el archivo es un CSV
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt',
        ]);

        // Abrir el archivo CSV
        $file = fopen($request->file('csv_file'), 'r');
        fgetcsv($file); // Saltar la primera fila (encabezados)

        $totalNeto = 0;

        while (($data = fgetcsv($file, 1000, ',')) !== FALSE) {
            // Datos del CSV
            $numeroReferencia = $data[1];
            $comision = $data[9];
            $cargoPorPago = $data[10];
            $iva = $data[11];
            $neto = $data[12];
            $estadoPago = $data[8];
            //dd($numeroReferencia);
            // Solo procesar si el estado de pago es 'by_booking' (indica que se pagó a través de la plataforma)
            $reserva = Reserva::where('codigo_reserva', $numeroReferencia)->first();
            //dd($reserva);
            if ($reserva) {
                // Cambiar el estado de la reserva a 'pagada'
                $reserva->estado_id = 6;

                // Actualizar los valores de la reserva
                $reserva->comision = $comision;
                $reserva->cargo_por_pago = $cargoPorPago;
                $reserva->iva = $iva;
                $reserva->neto = $neto;
                $reserva->save();

                // Sumar el neto al total del neto
                $totalNeto += $neto;
            }
        }

        fclose($file);

        // Obtener el último ingreso de categoría 'booking'
        $ultimoIngresoBooking = Ingresos::where('categoria_id', 2)->where('quantity', $totalNeto)->orderBy('created_at', 'desc')->first();

        if ($ultimoIngresoBooking) {
            // $diferencia = $totalNeto - $ultimoIngresoBooking->neto;
            // Comparar la diferencia
        }

        return redirect()->back()->with('status', 'Archivo procesado exitosamente.');
    }


    private function generarAsientoContable()
    {
        // Generar un número de asiento contable único para cada registro
        $asiento = DiarioCaja::orderBy('id', 'desc')->first();
        $anio = Carbon::now()->format('Y');
        $numeroAsiento;

        if ($asiento != null) {
            $asientoTemporal = explode("/", $asiento->asiento_contable);
            $numeroAsientos = $asientoTemporal[0] + 1;
            $numeroConCeros = str_pad($numeroAsientos, 4, "0", STR_PAD_LEFT);
            $numeroAsiento =  $numeroConCeros. '/' . $anio;
        } else {
            $numeroAsiento = '0001' . '/' . $anio;
        }

        return $numeroAsiento;
    }



}
