<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $data['title'] }}</title>
    <style type="text/css">
        @page {
            margin: 0px;
        }
        @page { 
            margin-top: 120px;
            margin-bottom: 130px;
        }

        body {
            margin: 0px;
            padding-top: 20px;
        }

        * {
            font-family: Verdana, Arial, sans-serif;
        }

        table {
            font-size: x-small;
        }

        tfoot tr td {
            font-weight: bold;
            font-size: x-small;
        }

        .invoice table {
            margin: 15px;
        }

        .invoice h3 {
            margin-left: 15px;
        }

        .information {
            color: black;
        }

        .information .logo {
            margin: 5px;
        }

        .information table {
            padding-bottom: 0px;
        }

        .projectConceptRow {
            border-collapse: collapse;
        }

        table.fixed {
            table-layout: fixed;
            width: 100%;
        }

        #summary th, #summary td {
            text-align: left;
            padding: 8px;
        }

        #summary th {
            background-color: black;
            color: white;
        }

        div.breakNow { 
            page-break-inside: avoid; 
            page-break-after: always;
        }

        .table th, .table td {
            border-bottom: 1px solid #dddddd;
            padding: 8px;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .table td {
            vertical-align: top;
        }

        .total-amount {
            font-size: large;
            font-weight: bold;
        }
    </style>
</head>
<body style="padding-right:40px">
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("Arial", "bold");
            $pdf->page_text(510, 753, "Página {PAGE_NUM}/{PAGE_COUNT}", $font, 9, array(0, 0, 0));
        }
    </script>

    <header>
        <div class="information" style="margin-top: -130px">
            <table width="100%">
                <tr>
                    <td align="left" style="width: 40%;padding-left: 20px;vertical-align: bottom;">
                        <h1 style="font-weight: normal;">FACTURA</h1>
                    </td>
                    <td align="right" style="width: 50%;padding-right: 45px;">
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS1JSTbvPQy4RdU-Av5a1Rv6JdYIZZrRrhbCA&s" alt="Logo" width="200" class="logo"/>
                    </td>
                </tr>
            </table>
        </div>

        <div class="information">
            <table width="100%">
                <tr>
                    <td align="left" style="width: 40%;padding-left:20px;">
                        <p><strong>Ref.:</strong> {{ $invoice->reference }}</p>
                        <p><strong>Fecha de Creación:</strong> {{ Carbon\Carbon::parse($invoice->created_at)->format('d/m/Y') }}</p>
                        <p><strong>Concepto:</strong> {{ $invoice->concept }}</p>
                        <p><strong>Observaciones:</strong> {{ $invoice->observations }}</p>
                    </td>
                    <td align="right" style="width: 50%;padding-right: 20px;">
                        <h3>{{ $invoice->cliente_nombre }}</h3> <!-- Directo desde la tabla -->
                        <p>{{ $invoice->cliente_direccion }}</p> <!-- Directo desde la tabla -->
                        <p>{{ $invoice->cliente_ciudad }} - {{ $invoice->cliente_cp }} ({{ $invoice->cliente_provincia }})</p> <!-- Directo desde la tabla -->
                        <p><strong>NIF:</strong> {{ $invoice->cliente_nif }}</p> <!-- Directo desde la tabla -->

                        <h4>Forma de pago: {{ $invoice->forma_pago }}</h4> <!-- Directo desde la tabla -->
                    </td>
                </tr>
            </table>
        </div>
        <br/>
    </header>

    <main style="margin-top: -10px">
        <div class="invoice" style="padding-left:16px;">
            <table class="table fixed" width="100%">
                <thead>
                    <tr>
                        <th style="width: 50%;">Descripción</th>
                        <th style="width: 10%; text-align: right;">Uds.</th>
                        <th style="width: 15%; text-align: right;">Precio/Uds.</th>
                        <th style="width: 10%; text-align: right;">Dcto.</th>
                        <th style="width: 15%; text-align: right;">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!is_null($invoice->conceptos) && is_array(json_decode($invoice->conceptos)) || is_object(json_decode($invoice->conceptos)))
                        @foreach(json_decode($invoice->conceptos) as $concept)
                        <tr>
                            <td>
                                <strong>{{ $concept->title }}</strong><br>
                                <span style="padding-left: 10px;">{{ $concept->description }}</span>
                            </td>
                            <td style="text-align: right;">{{ $concept->units }}</td>
                            <td style="text-align: right;">{{ $concept->price_unit }} &euro;</td>
                            <td style="text-align: right;">{{ $concept->discount }}%</td>
                            <td style="text-align: right;">{{ $concept->total }} &euro;</td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" style="text-align: center;">No hay conceptos disponibles para esta factura.</td>
                        </tr>
                    @endif
                </tbody>
                
            </table>
        </div>

        <div class="information">
            <table id="summary" width="100%" style="margin-top: 20px;">
                <tr>
                    <th style="text-align:center">Bruto</th>
                    <th style="text-align:center">Dto.</th>
                    <th style="text-align:center">Base</th>
                    <th style="text-align:center">IVA {{ $invoice->iva_percentage }}%</th>
                    <th style="text-align:right">TOTAL</th>
                </tr>
                <tr>
                    <td style="text-align:center">{{ number_format($invoice->gross, 2) }} &euro;</td>
                    <td style="text-align:center">{{ number_format($invoice->discount, 2) }} &euro;</td>
                    <td style="text-align:center">{{ number_format($invoice->base, 2) }} &euro;</td>
                    <td style="text-align:center">{{ number_format($invoice->iva, 2) }} &euro;</td>
                    <td style="text-align:right" class="total-amount">{{ number_format($invoice->total, 2) }} &euro;</td>
                </tr>
            </table>
        </div>
    </main>

    <footer class="information" style="position: fixed; bottom: -160px; padding-left: 30px; padding-right: 30px; height: 140px;">
        <hr style="border-style: inset; border-width: 0.5px; color: black;">
        <table width="100%" style="margin-bottom: 5px;">
            <tr>
                <td align="left" style="width: 50%;">
                    @if(Carbon\Carbon::parse($invoice->created_at) >= Carbon\Carbon::parse("2021/02/01"))
                        THWORK 3000 SL - B72284631 - C/General Primo de Rivera s/N - CP 11201 Algeciras (Cádiz)
                    @else
                        IPOINT COMUNICACION MASIVA SL - CIF: B72139868 - Urb. Parque del Oeste nº5 11205 Algeciras (Cádiz)
                    @endif
                </td>
            </tr>
            <tr>
                <td align="left" style="width: 50%;">
                    @if(Carbon\Carbon::parse($invoice->created_at) >= Carbon\Carbon::parse("2021/02/01"))
                        BANKINTER: ES84 0128 0733 2001 0007 1396
                    @else
                        Santander: ES81 0049 1672 4225 1004 9483
                    @endif
                </td>
            </tr>
        </table>
    </footer>
</body>
</html>
