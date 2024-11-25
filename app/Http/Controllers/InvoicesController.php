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
use Maatwebsite\Excel\Facades\Excel;
use Webklex\IMAP\Facades\Client;

class InvoicesController extends Controller
{



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
