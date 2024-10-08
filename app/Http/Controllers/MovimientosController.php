<?php

namespace App\Http\Controllers;

use App\Models\CategoriaGastos;
use App\Models\CategoriaIngresos;
use App\Models\DiarioCaja;
use Illuminate\Http\Request;
use App\Models\Ingresos;
use App\Models\Gastos;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MovimientosController extends Controller
{

    public function uploadFiles(){
        return view('admin.movimientos.upload');
    }
    public function uploadExcel(Request $request)
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
