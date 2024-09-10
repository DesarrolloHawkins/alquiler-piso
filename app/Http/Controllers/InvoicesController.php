<?php

namespace App\Http\Controllers;

use App\Models\Invoices;
use App\Models\InvoicesReferenceAutoincrement;
use Carbon\Cli\Invoker;
use Illuminate\Http\Request;

class InvoicesController extends Controller
{
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
       $budgetCreationDate = $budget->creation_date ?? now();
       $datetimeBudgetCreationDate = new \DateTime($budgetCreationDate);

       // Formatear la fecha para obtener los componentes necesarios
       $year = $datetimeBudgetCreationDate->format('Y');
       $monthNum = $datetimeBudgetCreationDate->format('m');

       // Buscar la última referencia autoincremental para el año y mes actual
       $latestReference = InvoicesReferenceAutoincrement::where('year', $year)
                           ->where('month_num', $monthNum)
                           ->orderBy('reference_autoincrement', 'desc')
                           ->first();

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
}
