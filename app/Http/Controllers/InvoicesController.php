<?php

namespace App\Http\Controllers;

use App\Exports\InvoicesExport;
use App\Models\Email;
use App\Models\Invoices;
use App\Models\InvoicesReferenceAutoincrement;
use App\Models\Reserva;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Cli\Invoker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Webklex\IMAP\Facades\Client;
use ZipArchive;

class InvoicesController extends Controller
{

    public function regenerateInvoicesForOctober()
    {
        $anio = 2024; // Cambia al año correspondiente
        $mes = 10; // Octubre

        // Filtrar todas las reservas del mes de octubre
        $reservasOctubre = Reserva::whereYear('fecha_entrada', $anio)
            ->whereMonth('fecha_entrada', $mes)
            ->whereNotIn('estado_id', [4]) // Filtrar estado_id diferente de 4
            ->get();

        // Eliminar las facturas existentes del mes de octubre
        $facturasOctubre = Invoices::whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->get();

        foreach ($facturasOctubre as $factura) {
            $factura->delete();
        }

        // Eliminar referencias autoincrementales del mes de octubre
        InvoicesReferenceAutoincrement::where('year', $anio)
            ->where('month_num', $mes)
            ->delete();

        // Verifica si todas las facturas y referencias fueron eliminadas
        if (
            Invoices::whereYear('fecha', $anio)->whereMonth('fecha', $mes)->exists() ||
            InvoicesReferenceAutoincrement::where('year', $anio)->where('month_num', $mes)->exists()
        ) {
            Log::error("Error al eliminar facturas o referencias del mes de $anio/$mes.");
            return response()->json(['error' => "No se pudieron eliminar todas las facturas o referencias del mes de $anio/$mes."]);
        }

        // Crear nuevas facturas para las reservas de octubre
        foreach ($reservasOctubre as $reserva) {
            $data = [
                'budget_id' => null,
                'cliente_id' => $reserva->cliente_id,
                'reserva_id' => $reserva->id,
                'invoice_status_id' => 1,
                'concepto' => 'Estancia en apartamento: ' . $reserva->apartamento->titulo,
                'description' => '',
                'fecha' => $reserva->fecha_entrada, // Fecha de entrada en la reserva
                'fecha_cobro' => null,
                'base' => $reserva->precio,
                'iva' => $reserva->precio * 0.10,
                'descuento' => null,
                'total' => $reserva->precio,
                'created_at' => $reserva->fecha_entrada,
                'updated_at' => $reserva->fecha_entrada,
            ];

            // Crear la factura
            $crearFactura = Invoices::create($data);

            // Generar referencia específica y actualizar la factura
            $referencia = $this->generateSpecificBudgetReference($crearFactura, $anio, $mes);
            $crearFactura->reference = $referencia['reference'];
            $crearFactura->reference_autoincrement_id = $referencia['id'];
            $crearFactura->invoice_status_id = 3;
            $crearFactura->save();

            // Actualizar el estado de la reserva
            $reserva->estado_id = 5;
            $reserva->save();
        }

        // Log para indicar que la tarea se completó
        Log::info("Facturas del mes de octubre de $anio regeneradas correctamente.");

        return response()->json(['message' => "Facturas del mes de octubre de $anio regeneradas correctamente."]);
    }


    /**
     * Generar referencias presupuestarias específicas para un año y mes
     */
    protected function generateSpecificBudgetReference(Invoices $invoices, $anio, $mes)
    {
        do {
            // Buscar la última referencia autoincremental para el año y mes proporcionados
            $latestReference = InvoicesReferenceAutoincrement::where('year', $anio)
                ->where('month_num', $mes)
                ->orderBy('id', 'desc')
                ->first();

            // Si no existe, empezamos desde 1, de lo contrario, incrementamos
            $newReferenceAutoincrement = $latestReference ? $latestReference->reference_autoincrement + 1 : 1;

            // Formatear el número autoincremental a 6 dígitos
            $formattedAutoIncrement = str_pad($newReferenceAutoincrement, 6, '0', STR_PAD_LEFT);

            // Crear la referencia
            $reference = $anio . '/' . $mes . '/' . $formattedAutoIncrement;

            // Verificar si ya existe en la tabla de facturas
            $exists = Invoices::where('reference', $reference)->exists();

            if (!$exists) {
                break;
            }

            // Si existe, incrementar el autoincremento manualmente para evitar colisiones
            $newReferenceAutoincrement++;
        } while (true);

        // Guardar o actualizar la referencia autoincremental
        $referenceToSave = new InvoicesReferenceAutoincrement([
            'reference_autoincrement' => $newReferenceAutoincrement,
            'year' => $anio,
            'month_num' => $mes,
        ]);
        $referenceToSave->save();

        // Devolver el resultado
        return [
            'id' => $referenceToSave->id,
            'reference' => $reference,
            'reference_autoincrement' => $newReferenceAutoincrement,
        ];
    }





    public function index(Request $request)
    {
        $orderBy = $request->get('order_by', 'fecha');
        $direction = $request->get('direction', 'asc');
        $perPage = $request->get('perPage', 10);
        $searchTerm = $request->get('search', '');
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');

        // Query inicial para facturas con su cliente y reserva asociados
        $query = Invoices::with(['cliente', 'reserva']); // Asegúrate de incluir las relaciones

        // Filtro de búsqueda por cliente, concepto, total, etc.
        if (!empty($searchTerm)) {
            $query->where(function($subQuery) use ($searchTerm) {
                $subQuery->whereHas('cliente', function($q) use ($searchTerm) {
                    $q->where('alias', 'LIKE', '%' . $searchTerm . '%');
                })
                ->orWhere('reference', 'LIKE', '%' . $searchTerm . '%')
                ->orWhere('concepto', 'LIKE', '%' . $searchTerm . '%')
                ->orWhere('total', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // Filtro por rango de fechas
        if (!empty($fechaInicio) || !empty($fechaFin)) {
            if (!empty($fechaInicio) && !empty($fechaFin)) {
                $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
            } elseif (!empty($fechaInicio)) {
                $query->where('fecha', '>=', $fechaInicio);
            } elseif (!empty($fechaFin)) {
                $query->where('fecha', '<=', $fechaFin);
            }
        }

        // Filtro por estado de factura (si es necesario)
        if ($request->has('estado')) {
            $query->where('invoice_status_id', $request->get('estado'));
        }

        // Aplicar orden por columna y dirección
        $facturas = $query->orderBy($orderBy, $direction)
                    ->paginate($perPage)
                    ->appends([
                        'order_by' => $orderBy,
                        'direction' => $direction,
                        'search' => $searchTerm,
                        'perPage' => $perPage,
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin,
                    ]);

        $sumatorio = $facturas->sum('total');

        return view('admin.invoices.index', compact('facturas', 'sumatorio'));
    }

    public function downloadInvoicesZip(Request $request)
{
    $fechaInicio = $request->get('fecha_inicio');
    $fechaFin = $request->get('fecha_fin');

    // Validar que las fechas estén presentes
    if (!$fechaInicio || !$fechaFin) {
        return redirect()->back()->with('error', 'Debes seleccionar un rango de fechas.');
    }

    // Obtener las facturas en el rango de fechas
    $facturas = Invoices::whereBetween('fecha', [$fechaInicio, $fechaFin])->get();

    if ($facturas->isEmpty()) {
        return redirect()->back()->with('error', 'No se encontraron facturas en el rango de fechas seleccionado.');
    }

    // Crear un archivo ZIP temporal
    $zipFileName = 'facturas_' . $fechaInicio . '_to_' . $fechaFin . '.zip';
    $zipFilePath = storage_path('app/' . $zipFileName);
    $zip = new ZipArchive;

    if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        foreach ($facturas as $invoice) {
            // Obtener conceptos relacionados con la reserva
            $conceptos = Reserva::where('id', $invoice->reserva_id)->get();
            foreach ($conceptos as $concepto) {
                $apartamento = $concepto->apartamento;
                $edificio = $concepto->apartamento->edificioName;
                $concepto['apartamento'] = $apartamento;
                $concepto['edificio'] = $edificio;
            }

            // Preparar datos para la vista del PDF
            $data = [
                'title' => 'Factura ' . $invoice->reference,
                'invoice' => $invoice,
            ];
            $invoice['conceptos'] = $conceptos;

            // Generar el PDF
            $pdf = PDF::loadView('admin.invoices.previewPDF', compact('data', 'invoice', 'conceptos'));

            // Generar el nombre del archivo PDF
            $fileName = 'factura_' . preg_replace('/[^A-Za-z0-9_\-]/', '', $invoice->reference) . '.pdf';

            // Añadir el PDF al ZIP
            $zip->addFromString($fileName, $pdf->output());
        }

        $zip->close();
    } else {
        return redirect()->back()->with('error', 'No se pudo crear el archivo ZIP.');
    }

    // Descargar el archivo ZIP
    return response()->download($zipFilePath)->deleteFileAfterSend(true);
}




    public function previewPDF($id){
        // Buscar la factura por su ID
        $invoice = Invoices::findOrFail($id);

        // Datos adicionales para la vista
        $data = [
            'title' => 'Factura ' . $invoice->reference,
        ];
        // Sanear el nombre del archivo para evitar caracteres inválidos
        $safeFileName = preg_replace('/[\/\\\\]/', '-', $invoice->reference);
        // Generar el PDF utilizando la vista 'facturas.pdf'
        $pdf = Pdf::loadView('admin.invoices.previewPDF', compact('invoice', 'data'));

        // Descargar o visualizar el PDF
        return $pdf->stream('factura_' . $safeFileName . '.pdf'); // Para visualizar en el navegador
        // return $pdf->download('factura_' . $invoice->reference . '.pdf'); // Para forzar la descarga


    }

    public function generateInvoicePDF($invoiceId)
    {
        // Obtener la factura desde la base de datos
        $invoice = Invoices::findOrFail($invoiceId);

        // Aquí puedes definir más datos o preparaciones si lo necesitas
        $data = [
            'title' => 'Factura ' . $invoice->reference,
            'invoice' => $invoice,
        ];
        $conceptos = Reserva::where('id',$invoice->reserva_id)->get();
        foreach($conceptos as $concepto){
            $apartamento = $concepto->apartamento;
            $edificio = $concepto->apartamento->edificioName;
            $concepto['apartamento'] = $apartamento;
            $concepto['edificio'] = $edificio;
        }
        $invoice['conceptos'] = $conceptos;
        // dd($conceptos);
        // Sanitizar el nombre del archivo para eliminar caracteres no válidos
        $fileName = 'factura_' . preg_replace('/[^A-Za-z0-9_\-]/', '', $invoice->reference) . '.pdf';

        // Renderizar la vista y pasarle los datos
        $pdf = PDF::loadView('admin.invoices.previewPDF', compact( 'data', 'invoice', 'conceptos'));

        // Configurar el tamaño de la página y las márgenes si es necesario
        $pdf->setPaper('A4', 'portrait');

        // Descargar el PDF o verlo en el navegador
        return $pdf->download($fileName);
    }


    public function create(Request $request){
        $data = [
            'budget_id' => null,
            'cliente_id' => $request->cliente_id,
            'reserva_id' => $request->reserva_id,
            'reserva_id' => $request->reserva_id,
            'reserva_id' => $request->reserva_id,
            'invoice_status_id ' => 1,
            'concepto' => $request->concepto,
            'description' => $request->descripcion,
            'fecha' => $request->fecha,
            'fecha_cobro' => null,
            'base' => $request->precio,
            'iva' => $request->precio * 0.10,
            'descuento' => isset($request->descuento) ? $request->descuento : null,
            'total' => $request->precio,
        ];
        $crear = Invoices::create($data);
        $referencia = $this->generateBudgetReference($crear);
        $crear->reference = $referencia['reference'];
        $crear->reference_autoincrement_id = $referencia['id'];
        $crear->budget_status_id = 3;
        $crear->save();

    }

    public function generateReferenceTemp($reference){

        // Extrae los dos dígitos del final de la cadena usando expresiones regulares
        preg_match('/temp_(\d{2})/', $reference, $matches);
       // Incrementa el número primero
       if(count($matches) >= 1){
           $incrementedNumber = intval($matches[1]) + 1;
           // Asegura que el número tenga dos dígitos
           $formattedNumber = str_pad($incrementedNumber, 2, '0', STR_PAD_LEFT);
           // Concatena con la cadena "temp_"
           return "temp_" . $formattedNumber;
       }
   }
   private function generateReferenceDelete($reference){
        // Extrae los dos dígitos del final de la cadena usando expresiones regulares
        preg_match('/delete_(\d{2})/', $reference, $matches);
       // Incrementa el número primero
       if(count($matches) >= 1){
           $incrementedNumber = intval($matches[1]) + 1;
           // Asegura que el número tenga dos dígitos
           $formattedNumber = str_pad($incrementedNumber, 2, '0', STR_PAD_LEFT);
           // Concatena con la cadena "temp_"
           return "delete_" . $formattedNumber;
       }
   }


    public function generateBudgetReference(Invoices $invoices) {

        // Obtener la fecha actual del presupuesto
        $budgetCreationDate = $invoices->created_at ?? now();
        $datetimeBudgetCreationDate = new \DateTime($budgetCreationDate);

        // Formatear la fecha para obtener los componentes necesarios
        $year = $datetimeBudgetCreationDate->format('Y');
        $monthNum = $datetimeBudgetCreationDate->format('m');

        //dd($year, $monthNum, $budgetCreationDate, $datetimeBudgetCreationDate);
        // Buscar la última referencia autoincremental para el año y mes actual
        $latestReference = InvoicesReferenceAutoincrement::where('year', $year)
                            ->where('month_num', $monthNum)
                            ->orderBy('id', 'desc')
                            ->first();
         //dd($latestReference->reference_autoincrement);
        // Si no existe, empezamos desde 1, de lo contrario, incrementamos
        $newReferenceAutoincrement = $latestReference ? $latestReference->reference_autoincrement + 1 : 1;

        // Formatear el número autoincremental a 6 dígitos
        $formattedAutoIncrement = str_pad($newReferenceAutoincrement, 6, '0', STR_PAD_LEFT);

        // Crear la referencia
        $reference = $year . '/' . $monthNum . '/' . $formattedAutoIncrement;

        // Guardar o actualizar la referencia autoincremental en BudgetReferenceAutoincrement
        $referenceToSave = new InvoicesReferenceAutoincrement([
            'reference_autoincrement' => $newReferenceAutoincrement,
            'year' => $year,
            'month_num' => $monthNum,
            // Otros campos pueden ser asignados si son necesarios
        ]);
        $referenceToSave->save();

        // Devolver el resultado
        return [
            'id' => $referenceToSave->id,
            'reference' => $reference,
            'reference_autoincrement' => $newReferenceAutoincrement,
            'budget_reference_autoincrements' => [
                'year' => $year,
                'month_num' => $monthNum,
                // Añade aquí más si es necesario
            ],
        ];
   }

   public function updateFecha(Request $request, $id)
    {
        $factura = Invoices::find($id);

        if (!$factura) {
            return response()->json(['success' => false, 'message' => 'Factura no encontrada.'], 404);
        }

        $request->validate([
            'fecha' => 'required|date',
        ]);

        $factura->fecha = $request->input('fecha');
        $factura->save();

        return response()->json(['success' => true, 'message' => 'Fecha actualizada correctamente.']);
    }


   public function exportInvoices(Request $request)
   {
       $orderBy = $request->get('order_by', 'fecha');
       $direction = $request->get('direction', 'asc');
       $searchTerm = $request->get('search', '');
       $fechaInicio = $request->get('fecha_inicio');
       $fechaFin = $request->get('fecha_fin');

       // Query inicial para facturas con cliente, reserva y estado asociados
       $query = Invoices::with(['cliente', 'reserva']);

       // Filtro de búsqueda por cliente, referencia, concepto, o total
       if (!empty($searchTerm)) {
           $query->where(function($subQuery) use ($searchTerm) {
               $subQuery->whereHas('cliente', function($q) use ($searchTerm) {
                   $q->where('alias', 'LIKE', '%' . $searchTerm . '%');
               })
               ->orWhere('reference', 'LIKE', '%' . $searchTerm . '%')
               ->orWhere('concepto', 'LIKE', '%' . $searchTerm . '%')
               ->orWhere('total', 'LIKE', '%' . $searchTerm . '%');
           });
       }

       // Filtro por rango de fechas
       if (!empty($fechaInicio) || !empty($fechaFin)) {
           if (!empty($fechaInicio) && !empty($fechaFin)) {
               $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
           } elseif (!empty($fechaInicio)) {
               $query->where('fecha', '>=', $fechaInicio);
           } elseif (!empty($fechaFin)) {
               $query->where('fecha', '<=', $fechaFin);
           }
       }

    //    // Filtro por estado de factura
    //    if ($request->has('estado')) {
    //        $query->where('invoice_status_id', $request->get('estado'));
    //    }

       // Aplicar el orden
       $query->orderBy($orderBy, $direction);

       // Obtener los resultados filtrados
       $invoices = $query->get();

       // Exportar el Excel con los datos filtrados
       return Excel::download(new InvoicesExport($invoices), 'invoices.xlsx');
   }

   public function facturar(Request $request)
   {
       $idReserva = $request->input('reserva_id');
       $reserva = Reserva::find($idReserva);

       if (!$reserva) {
           return response()->json(['success' => false, 'message' => 'Reserva no encontrada.'], 404);
       }

       $invoice = Invoices::where('reserva_id', $idReserva)->first();

       if ($invoice == null) {
           $data = [
               'budget_id' => null,
               'cliente_id' => $reserva->cliente_id,
               'reserva_id' => $reserva->id,
               'invoice_status_id' => 1,
               'concepto' => 'Estancia en apartamento: '. $reserva->apartamento->titulo,
               'description' => '',
               'fecha' => $reserva->fecha_salida,
               'fecha_cobro' => null,
               'base' => $reserva->precio,
               'iva' => $reserva->precio * 0.10,
               'descuento' => null,
               'total' => $reserva->precio,
               'created_at' => $reserva->fecha_salida,
               'updated_at' => $reserva->fecha_salida,
           ];

           $crearFactura = Invoices::create($data);

           $referencia = $this->generateBudgetReference($crearFactura);
           $crearFactura->reference = $referencia['reference'];
           $crearFactura->reference_autoincrement_id = $referencia['id'];
           $crearFactura->invoice_status_id = 3;
           $crearFactura->save();

           $reserva->estado_id = 5;
           $reserva->save();

           return response()->json(['success' => true, 'message' => 'Factura generada correctamente.']);
       } else {
           return response()->json(['success' => false, 'message' => 'La factura ya estaba generada.']);
       }
   }


}
