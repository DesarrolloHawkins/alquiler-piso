<?php $lineCounterForBreak = 0;?>
<!doctype html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>{{ $data['title'] }}</title>

        <style type="text/css">
            @page {
                margin: 0px;
            }
            @page { margin-top: 120px;
                margin-bottom:130px}

            body {
                margin: 0px;
                padding-top:20px;
            }
            * {
                font-family: Verdana, Arial, sans-serif;
            }
            a {
                color: #fff;
                text-decoration: none;
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
                /*background-color: #60A7A6;*/
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
                table-layout:fixed;
            }
            /*table.fixed td { overflow: hidden; }*/
            #summary th, #summary td {
                text-align: left;
                padding: 8px;
            }

            #summary th {
                background-color: black;
                color: white;
            }
            div.breakNow { page-break-inside:avoid; page-break-after:always; }

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
          <div class="information" style="margin-top:-130px">
            <table width="100%">
                 <tr>
                    <td align="left" style="width: 40%;padding-left:20px;vertical-align:bottom;">
                        <h1 style="font-weight: normal;vertical-align:bottom"><cite>FACTURA</cite></h1>
                    </td>
                    <td align="right" style="width: 50%;padding-left:20px;padding-right:45px">
                         <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS1JSTbvPQy4RdU-Av5a1Rv6JdYIZZrRrhbCA&s" alt="Logo" width="200" class="logo"/>

                    </td>
                </tr>
            </table>
            </div>
            <div class="information" style="">
                <table width="100%">
                    <tr>
                        <td align="left" style="width: 40%;padding-left:20px;vertical-align:top;">
                            <p style="font-size:12px">Ref.:<span style="padding-left:72px;font-weight: bold;">{{ $invoice->reference }}</span></p>
                            <p style="font-size:12px">Versión: <span style="padding-left:46px;"></span></p>
                            <p style="font-size:12px">Fecha de Creación: <span style="padding-left:17px;">{{ Carbon\Carbon::parse($invoice->created_at)->format('d/m/Y') }}</span></p>
                            <p style="font-size:12px">Campaña: <span style="padding-left:17px;">{{ $invoice->project->name }}</span></p>
                            <p style="font-size:12px">Concepto: <span style="padding-left:17px;">{{ $invoice->concept }}</span></p>
                            <p style="font-size:12px">Observaciones: <span style="padding-left:17px;">{{ $invoice->observations }}</span></p>
                        </td>
                        <td align="right" style="width: 50%;padding-right:20px;">
                                    <h3>{{ $invoice->client->name }}</h3>
                                    <span style="text-align: left;font-size:12px">{{ $invoice->client->address }}</span>
                                    <br>
                                    <span style="text-align: left;font-size:12px">{{ $invoice->client->city }}</span>
                                    <br>
                                    <span style="text-align: left;font-size:12px">{{ $invoice->client->zipcode}}&nbsp;&nbsp;&nbsp;&nbsp;{{ $invoice->client->province }}</span>
                                    <br>
                                    <span style="text-align: left;font-size:12px">NIF&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $invoice->client->cif }}</span>
                            @if ($invoice->payment_method_id == 9)
                                <h4>Forma de pago: Transeferencia</h4>
                            @else
                                <h4>Forma de pago: {{ $invoice->paymentMethod->name}}</h4>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            <br/>
        </header>
        <main style="margin-top: -10px">
            <div style="display: block; breakNow">
                <div class="invoice" style="padding-left:16px; breakNow">
                    <table id="conceptTable" class="fixed" width="100%">
                        <tr>
                            <th  style="width:60%;text-align:left;">Conceptos</th>
                            <th  style="width:10%;text-align:right">Uds.</th>
                            <th  style="width:15%;text-align:right">Precio/Uds.</th>
                            <th  style="width:15%;text-align:right">Subtotal.</th>
                            <th  style="width:10%;text-align:right">Dcto.</th>
                            <th  style="width:15%;text-align:right">TOTAL</th>
                        </tr>
                        </thead>
                        <tbody>

                            @foreach($invoiceConceptsFormated as $concept)
                                <tr style="padding-right:20px;">


                                    <td style="width:100px; page-break-after: avoid">
                                        <span style="font-weight: bold">{{ $concept['title'] }}</span>

                                        <br>
                                        <span >
                                            @foreach($concept['description'] as $conceptDescriptionRow)
                                                <span style="padding-left:10px;">{{ $conceptDescriptionRow }}</span>
                                            @endforeach
                                        </span>
                                    </td>
                                    <td style="text-align:right;vertical-align: top;">{{ $concept['units'] }}</td>
                                    <td style="text-align:right;vertical-align: top;">{{ $concept['price_unit'] }} &nbsp;€</td>
                                    <td style="text-align:right;vertical-align: top;">{{ $concept['subtotal'] }} &nbsp;€</td>
                                    <td style="text-align:right;vertical-align: top;">{{ $concept['discount'] }}</td>
                                    <td style="text-align:right;vertical-align: top;">{{ $concept['total'] }} &nbsp;€</td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
            <div style="page-break-after: auto">
                <div class="information">
                    <div style="page-break-after: avoid; height: 140px;padding-left:35px">
                            <br><br><br><br>
                        <table id="summary" style="border-collapse: collapse;width: 100%;">
                            <tr>
                                <th style="text-align:center">Bruto</th>
                                <th style="text-align:center">Dto.</th>
                                <th style="text-align:center">Base</th>
                                <th style="text-align:center">IVA {{ $invoice->iva_percentage }}%</th>
                                <th style="text-align:right">TOTAL</th>
                            </tr>
                            <tr>
                                <td style="text-align:center">{{ $invoice->gross }}&nbsp;€</td>
                                <td style="text-align:center">{{ $invoice->discount }}&nbsp;€</td>
                                <td style="text-align:center">{{ $invoice->base }}&nbsp;€</td>
                                <td style="text-align:center">{{ $invoice->iva }}&nbsp;€</td>
                                <td style="text-align:right">{{ $invoice->total }}&nbsp;€</td>
                            </tr>
                            <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td> </tr>
                        </table>
                    </div>
                </div>
            </div>
        </main>

        <footer class="information pagenum" style="position: fixed; bottom:-160px;padding-left:30px;padding-right:30px;height: 140px;" >
            <hr style="margin-bottom: 0.1em;border-style:inset;border-width: 0.5px;color:black">
            <table width="100%;margin-bottom:5px">
                <tr>
                    <td align="left" style="width: 50%;">
                        @if( Carbon\Carbon::parse($invoice->created_at) >= Carbon\Carbon::parse("2021/02/01"))
                            THWORK 3000 SL B72284631 C/General Primo de Rivera s/N  CP 11201 Algeciras (Cádiz)
                        @else
                            IPOINT COMUNICACION MASIVA SL.CIF: B 72139868 - Urb. Parque del Oeste nº5 11205 Algeciras (Cádiz)
                        @endif
                    </td>
                </tr>
                <tr>
                    <td align="left" style="width: 50%;">
                        @if( Carbon\Carbon::parse($invoice->created_at) >= Carbon\Carbon::parse("2021/02/01"))
                            BANKINTER: ES84 0128 0733 2001 0007 1396
                        @else
                            Santander: ES81 0049 1672 4225 10049483
                        @endif
                        </td>
                    </tr>
            </table>
        </footer>
    </body>
</html>
