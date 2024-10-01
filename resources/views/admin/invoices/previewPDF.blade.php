<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $data['title'] }}</title>
    <style type="text/css">
        @page {
            margin-top: 120px;
            margin-bottom: 130px;
        }

        body {
            font-family: Verdana, Arial, sans-serif;
            padding-top: 20px;
        }

        table {
            font-size: x-small;
        }

        .invoice h3 {
            margin-left: 15px;
        }

        .table th, .table td {
            border-bottom: 1px solid #dddddd;
            padding: 8px;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .total-amount {
            font-size: large;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <div class="information" style="margin-top: -130px">
            <table width="100%">
                <tr>
                    <td align="left" style="width: 40%;padding-left: 20px;vertical-align: bottom;">
                        <h1 style="font-weight: normal;"><strong>FACTURA</strong></h1>
                    </td>
                    <td align="right" style="width: 50%;padding-right: 45px;">
                        <h1>Hawkins Real State</h1>
                        {{-- <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS1JSTbvPQy4RdU-Av5a1Rv6JdYIZZrRrhbCA&s" alt="Logo" width="200" class="logo"/> --}}
                    </td>
                </tr>
            </table>
        </div>

        <div class="information">
            <table width="100%">
                <tr>
                    <td align="left" style="width: 40%;padding-left:20px;">
                        <p><strong>Ref.:</strong> {{ $invoice->reference }}</p>
                        <p><strong>Fecha de Factura:</strong> {{ \Carbon\Carbon::parse($invoice->fecha)->format('d/m/Y') }}</p>
                        <p><strong>Concepto:</strong> {{ $invoice->concepto }}</p>
                        <p><strong>Observaciones:</strong> {{ $invoice->description }}</p>
                    </td>
                    <td align="right" style="width: 50%;padding-right: 20px;">
                        <h3>{{ $invoice->cliente->nombre == null ? $invoice->cliente->alias : $invoice->cliente->nombre .' '. $invoice->cliente->apellido1 }}</h3>
                        {{-- <p>{{ $invoice->cliente_direccion }}</p> --}}
                        {{-- <p>{{ $invoice->cliente_ciudad }} - {{ $invoice->cliente_cp }} ({{ $invoice->cliente_provincia }})</p> --}}
                        <p>
                            @if ($invoice->cliente->tipo_documento == 1)
                                <strong>DNI:</strong> {{ $invoice->cliente->num_identificacion }}
                            @else
                                <strong>PASAPORTE:</strong> {{ $invoice->cliente->num_identificacion }}
                            @endif
                        </p>
                        <p>
                            <strong>TELEFONO:</strong> {{ $invoice->cliente->telefono }}
                        </p>
                        <p>
                            <strong>EMAIL:</strong> {{ $invoice->cliente->email }}
                        </p>

                        {{-- <h4>Forma de pago: {{ $invoice->forma_pago }}</h4> --}}
                    </td>
                </tr>
            </table>
        </div>
        <br/>
    </header>

    <main style="margin-top: 50px">
        <div class="invoice" style="padding-left:0px;">
            <table class="table fixed" width="100%">
                <thead>
                    <tr>
                        <th style="width: 50%;">Descripción</th>
                        <th style="width: 15%; text-align: right;">F. Entrada.</th>
                        <th style="width: 15%; text-align: right;">F. Salida.</th>
                        <th style="width: 10%; text-align: right;">Uds.</th>
                        <th style="width: 15%; text-align: right;">Precio/Uds.</th>
                        <th style="width: 8%; text-align: right;">Dcto.</th>
                        <th style="width: 15%; text-align: right;">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!is_null($conceptos) && is_array(json_decode($conceptos)) || is_object(json_decode($conceptos)))
                        @foreach(json_decode($conceptos) as $concept)
                        {{-- {{dd($concept)}} --}}
                        <tr>
                            <td><strong>{{ $concept->edificio->nombre .': '.$concept->apartamento->titulo }}</strong></td>
                            <td style="text-align: right;">{{ $concept->fecha_entrada }}</td>
                            <td style="text-align: right;">{{ $concept->fecha_salida }}</td>
                            <td style="text-align: right;">1</td>
                            <td style="text-align: right;">{{ number_format($invoice->base - $invoice->iva , 2) }} &euro;</td>
                            <td style="text-align: right;">{{ $invoice->discount }}%</td>
                            <td style="text-align: right;">{{ number_format($invoice->base - $invoice->iva , 2) }} &euro;</td>
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
            <table id="summary" width="100%" style="margin-top: 70px;">
                <tr>
                    <th style="text-align:center">Bruto</th>
                    <th style="text-align:center">Dto.</th>
                    <th style="text-align:center">Base</th>
                    <th style="text-align:center">IVA {{ $invoice->iva_percentage }}%</th>
                    <th style="text-align:right">TOTAL</th>
                </tr>
                <tr>
                    <td style="text-align:center">{{ number_format($invoice->base - $invoice->iva , 2) }} &euro;</td>
                    <td style="text-align:center">{{ number_format($invoice->descuento, 2) }} &euro;</td>
                    <td style="text-align:center">{{ number_format($invoice->base - $invoice->iva , 2) }} &euro;</td>
                    <td style="text-align:center">21%</td>
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
                    @if(\Carbon\Carbon::parse($invoice->created_at) >= \Carbon\Carbon::parse("2021/02/01"))
                        HAWKINS REAL STATE - B72284631 - C/General Primo de Rivera s/N - CP 11201 Algeciras (Cádiz)
                    @else
                        IPOINT COMUNICACION MASIVA SL - CIF: B72139868 - Urb. Parque del Oeste nº5 11205 Algeciras (Cádiz)
                    @endif
                </td>
            </tr>
            <tr>
                <td align="left" style="width: 50%;">
                    @if(\Carbon\Carbon::parse($invoice->created_at) >= \Carbon\Carbon::parse("2021/02/01"))
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
