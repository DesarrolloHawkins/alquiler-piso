<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings; // Añade esta interfaz

class InvoicesExport implements FromCollection, WithHeadings
{
    protected $invoices;

    public function __construct($invoices)
    {
        $this->invoices = $invoices;
    }

    /**
     * Retorna los datos a exportar.
     */
    public function collection()
    {
        return $this->invoices->map(function($invoice) {
            return [
                'reference' => $invoice->reference,
                'cliente' => optional($invoice->cliente)->nombre != null ? $invoice->cliente->nombre : $invoice->cliente->alias,
                'num_identificacion' => optional($invoice->cliente)->num_identificacion ?? 'Sin información',
                'concepto' => $invoice->concepto ?? 'Sin información',
                'fecha_entrada' => $invoice->reserva && $invoice->reserva->fecha_entrada 
                    ? Carbon::parse($invoice->reserva->fecha_entrada)->format('d/m/Y') 
                    : 'Sin información',

                'fecha_salida' => $invoice->reserva && $invoice->reserva->fecha_salida 
                    ? Carbon::parse($invoice->reserva->fecha_salida)->format('d/m/Y') 
                    : 'Sin información',

                'fecha' => $invoice->fecha 
                    ? Carbon::parse($invoice->fecha)->format('d/m/Y') 
                    : 'Sin información',
                'total' => $invoice->total,
                'estado' => optional($invoice->estado)->name ?? 'Sin información',
            ];
        });
    }

    /**
     * Retorna los encabezados de las columnas.
     */
    public function headings(): array
    {
        return [
            'Referencia',
            'Cliente',
            'Número de Identificación',
            'Concepto',
            'Fecha de Entrada',
            'Fecha de Salida',
            'Fecha de Factura',
            'Total',
            'Estado',
        ];
    }
}
