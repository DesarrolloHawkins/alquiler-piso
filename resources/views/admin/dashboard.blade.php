@extends('layouts.appAdmin')

{{-- @section('bienvenido')
@section('tituloSeccion', 'Dashboard')
@endsection --}}

@section('content')
<style>
    .bg-primero {
        background: rgb(89,188,255);
        background: -moz-linear-gradient(90deg, rgba(89,188,255,1) 0%, rgba(144,223,254,1) 100%);
        background: -webkit-linear-gradient(90deg, rgba(89,188,255,1) 0%, rgba(144,223,254,1) 100%);
        background: linear-gradient(90deg, rgba(89,188,255,1) 0%, rgba(144,223,254,1) 100%);
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#59bcff",endColorstr="#90dffe",GradientType=1);
    }
</style>
<div class="container px-4" style="background-color: #f4f4f4;">
    <form action="{{route('dashboard.index')}}" class="row  align-items-end" method="GET">
        <div class="col-md-3">
            <label for="fecha_inicio">Fecha Inicio</label>
            <input type="text" id="fecha_inicio" name="fecha_inicio" class="form-control flatpickr"
                value="{{ request('fecha_inicio', '') }}" placeholder="Selecciona Fecha Inicio">
        </div>

        <div class="col-md-3">
            <label for="fecha_fin">Fecha Fin</label>
            <input type="text" id="fecha_fin" name="fecha_fin" class="form-control flatpickr"
                value="{{ request('fecha_fin', '') }}" placeholder="Selecciona Fecha Fin">
        </div>

        <div class="col-md-3">
            <button type="submit" class="btn bg-color-primero mt-4">Buscar</button>
        </div>
    </form>
    <div class="row" style="padding: 1rem;">
        <div class="col-12 mb-5">
            {{-- <form action="{{route('dashboard.index')}}" class="row  align-items-end" method="GET">
                <div class="col-md-6 col-sm-12">
                    <h1 class="mb-0">Dashboard</h1>
                </div>
                <div class="col-md-2">
                   <label for="mes">
                        Mes - {{$mesReturn}}
                   </label>
                   <select class="form-control" name="mes" id="mes" required>
                        <option value="">-- Selecciona Mes --</option>
                        <option value="01" @if(isset($mesReturn) && $mesReturn == '01') selected @endif>Enero</option>
                        <option value="02" @if(isset($mesReturn) && $mesReturn == '02') selected @endif>Febrero</option>
                        <option value="03" @if(isset($mesReturn) && $mesReturn == '03') selected @endif>Marzo</option>
                        <option value="04" @if(isset($mesReturn) && $mesReturn == '04') selected @endif>Abril</option>
                        <option value="05" @if(isset($mesReturn) && $mesReturn == '05') selected @endif>Mayo</option>
                        <option value="06" @if(isset($mesReturn) && $mesReturn == '06') selected @endif>Junio</option>
                        <option value="07" @if(isset($mesReturn) && $mesReturn == '07') selected @endif>Julio</option>
                        <option value="08" @if(isset($mesReturn) && $mesReturn == '08') selected @endif>Agosto</option>
                        <option value="09" @if(isset($mesReturn) && $mesReturn == '09') selected @endif>Septiembre</option>
                        <option value="10" @if(isset($mesReturn) && $mesReturn == '10') selected @endif>Octubre</option>
                        <option value="11" @if(isset($mesReturn) && $mesReturn == '11') selected @endif>Noviembre</option>
                        <option value="12" @if(isset($mesReturn) && $mesReturn == '12') selected @endif>Diciembre</option>
                    </select>
               </div>

               <div class="col-md-2">
                   <label for="anio">
                   Año
                   </label>
                   <select class="form-control" name="anio" id="anio" require>
                    <option @if(isset($anioReturn) && $anioReturn == $anioActual) selected @endif value="{{ $anioActual }}">{{ $anioActual }}</option>
                    <option @if(isset($anioReturn) && $anioReturn == $anioAnterior) selected @endif value="{{ $anioAnterior }}">{{ $anioAnterior }}</option>
                   </select>
               </div>

               <div class="col-md-2">
                    <button type="submit" class="btn bg-color-primero">Buscar</button>
               </div>
            </form> --}}
            <div class="row">
                <div class="col-xl-3 col-md-6 card rounded-2 me-2 mt-2">
                    <div class="row p-3">
                        <div class="col-7">
                            <h4 class="text-start mb-0 fs-5">Total de Reservas</h4>
                        </div>
                        <div class="col-5">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ $countReservas }}</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 card rounded-2 me-2 mt-2">
                    <div class="row p-3">
                        <div class="col-7">
                            <h4 class="text-start mb-0 fs-5">Ocupacción</h4>
                        </div>
                        <div class="col-5">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ $porcentajeOcupacion }} %</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 card rounded-2 me-2 mt-2">
                    <div class="row p-3">
                        <div class="col-5">
                            <h4 class="text-start mb-0 fs-5">Previsión</h4>
                        </div>
                        <div class="col-7">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($sumPrecio, 2) }} €</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 card rounded-2 me-2 mt-2">
                    <div class="row p-3">
                        <div class="col-5">
                            <h4 class="text-start mb-0 fs-5">Cobrado</h4>
                        </div>
                        <div class="col-7">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($ingresos, 2) }} €</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 card rounded-2 me-2 mt-2">
                    <div class="row p-3">
                        <div class="col-7">
                            <h4 class="text-start mb-0 fs-5">Ocupación Disponibles</h4>
                        </div>
                        <div class="col-5">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ $totalNochesPosibles }}</strong></h2>
                        </div>
                    </div>

                </div>
                <div class="col-xl-3 col-md-6 card rounded-2 me-2 mt-2">

                    <div class="row p-3">
                        <div class="col-8">
                            <h4 class="text-start mb-0 fs-5">Ocupación</h4>
                        </div>
                        <div class="col-4">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ $nochesOcupadas }}</strong></h2>
                        </div>
                    </div>

                </div>
                <div class="col-xl-3 col-md-6 card rounded-2 me-2 mt-2">

                    <div class="row p-3">
                        <div class="col-8">
                            <h4 class="text-start mb-0 fs-5">Ingresos</h4>
                        </div>
                        <div class="col-4">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($ingresos, 2) }} €</strong></h2>
                        </div>
                    </div>

                </div>
                <div class="col-xl-3 col-md-6 card rounded-2 me-2 mt-2">

                    <div class="row p-3">
                        <div class="col-8">
                            <h4 class="text-start mb-0 fs-5">Gastos</h4>
                        </div>
                        <div class="col-4">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($gastos, 2) }} €</strong></h2>
                        </div>
                    </div>

                </div>
                <div class="col-xl-3 col-md-6 card rounded-2 me-2 mt-2">

                    <div class="row p-3">
                        <div class="col-8">
                            <h4 class="text-start mb-0 fs-5">Beneficio</h4>
                        </div>
                        <div class="col-4">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($ingresos - $gastos, 2) }} €</strong></h2>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        {{-- <div class="col-md-3">
            <div class="row mx-1 bg-primero p-3 rounded-4">
                <div class="col-9">
                    <h4>{{$countReservas}}</h4>
                    <p>Reservas Año Actual</p>
                </div>
                <div class="col-3"></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="row mx-1 bg-success p-3 rounded-4">
                <div class="col-9">
                    <h4>{{$sumPrecio}} €</h4>
                    <p>Ingresos Año Actual</p>
                </div>
                <div class="col-3"></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="row mx-1 bg-warning p-3">
                <div class="col-9">
                    <h4>800</h4>
                    <p>New Booking</p>
                </div>
                <div class="col-3"></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="row mx-1 bg-danger p-3">
                <div class="col-9">
                    <h4>800</h4>
                    <p>New Booking</p>
                </div>
                <div class="col-3"></div>
            </div>
        </div>
    </div> --}}
    <div class="row justify-content-between">
        <div class="col-xl-12 col-md-12 card rounded-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="chartNacionalidad"></div>
                    </div>

                </div>
            </div>

        </div>
        <div class="col-xl-6 col-md-12 card rounded-4">
            <div class="card-body">
                <h2 class="text-center">Balance</h2>
                <div id="chart"></div>
            </div>
        </div>
        <div class="col-xl-6 col-md-12 card rounded-4">
            <h2 class="text-center">Distribución de Clientes por Rango de Edad</h2>
            <div id="chartEdad"></div>
        </div>
        <div class="col-md-6 card rounded-4">
            <h2 class="text-center">Distribución por Ocupantes</h2>
            <div id="chartOcupantes"></div>
        </div>
        <div class="col-md-6 card rounded-4">
            <h2 class="text-center">Distribución por Género</h2>
            <div id="chartSexo"></div>
        </div>
        <div class="col-md-6 card rounded-4">
            <h2 class="text-center">Distribución de Prescriptores</h2>
            <div id="chartPrescriptores"></div>
        </div>

        <div class="col-md-6 card rounded-4">
            <h2 class="text-center">Distribución de Reservas por Apartamento</h2>
            <div id="chartApartamentos"></div>
        </div>
        <div class="col-md-6 card rounded-4">
            <h2 class="text-center">Distribución de Gastos por Categoría</h2>
            <div id="chartGastos"></div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-4 mt-4">
                {{-- <form action="{{ route('dashboard.index') }}" method="GET">
                    <label for="mes">Selecciona un mes:</label>
                    <select name="mes" id="mes" class="form-control">
                        <option value="1" {{ (request('mes') == 1) ? 'selected' : '' }}>Enero</option>
                        <option value="2" {{ (request('mes') == 2) ? 'selected' : '' }}>Febrero</option>
                        <option value="3" {{ (request('mes') == 3) ? 'selected' : '' }}>Marzo</option>
                        <option value="4" {{ (request('mes') == 4) ? 'selected' : '' }}>Abril</option>
                        <option value="5" {{ (request('mes') == 5) ? 'selected' : '' }}>Mayo</option>
                        <option value="6" {{ (request('mes') == 6) ? 'selected' : '' }}>Junio</option>
                        <option value="7" {{ (request('mes') == 7) ? 'selected' : '' }}>Julio</option>
                        <option value="8" {{ (request('mes') == 8) ? 'selected' : '' }}>Agosto</option>
                        <option value="9" {{ (request('mes') == 9) ? 'selected' : '' }}>Septiembre</option>
                        <option value="10" {{ (request('mes') == 10) ? 'selected' : '' }}>Octubre</option>
                        <option value="11" {{ (request('mes') == 11) ? 'selected' : '' }}>Noviembre</option>
                        <option value="12" {{ (request('mes') == 12) ? 'selected' : '' }}>Diciembre</option>
                    </select>
                    <button type="submit" class="btn btn-primary mt-3 w-100">Consultar</button>
                </form> --}}

                <div class="container mt-4">
                    {{-- <h4 class="text-center">Reporte de Reservas para el Año {{ $anio }} @if($mes) y Mes <span class="text-capitalize">{{ $mesNombre }} </span>@endif</h4> --}}
                    <hr>

                    <p class="mb-0 text-center fs-5">Total de Reservas:</p>
                    <p class="fs-4 mb-0 text-center"><strong>{{ $countReservas }}</strong></p>
                    <p class="mb-0 text-center fs-5">Total de Ingresos:</p>
                    <p class="fs-4 mb-0 text-center"><strong>{{ number_format($sumPrecio, 2) }} €</strong></p>
                    <button class="btn btn-warning text-white w-100 mt-3" data-bs-toggle="modal" data-bs-target="#reservasModal">
                        Ver reservas
                    </button>
                </div>

            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="reservasModal" tabindex="-1" aria-labelledby="reservasModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-flex justify-content-between" id="reservasModalLabel"><span>Reservas</span> <span class="ms-5">Sumatorio: <strong>{{ number_format($sumPrecio, 2) }} €</strong> </span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Tabla para mostrar las reservas -->
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Código de Reserva</th>
                                <th>Origen</th>
                                <th>Fecha Entrada</th>
                                <th>Fecha Salida</th>
                                <th>Precio</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- @foreach ($reservas as $reserva)
                                <tr>
                                    <td>{{ $reserva->id }}</td>
                                    <td>{{ $reserva->codigo_reserva }}</td>
                                    <td>{{ $reserva->origen }}</td>
                                    <td>{{ $reserva->fecha_entrada }}</td>
                                    <td>{{ $reserva->fecha_salida }}</td>
                                    <td>{{ number_format($reserva->precio, 2) }} €</td>
                                </tr>
                            @endforeach --}}
                        </tbody>
                    </table>
                    <h6>Sumatorio: <strong>{{ number_format($sumPrecio, 2) }} €</strong> </h6>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #legendNacionalidad {
        font-size: 14px;
        line-height: 1.5;
    }

    #legendNacionalidad div {
        margin-bottom: 5px;
    }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- Incluir Flatpickr y la localización en español -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script><script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        flatpickr('.flatpickr', {
            dateFormat: "Y-m-d",
        });
    });
</script>
<script>
    var ingresos = @json($ingresos);
    var gastos = @json($gastos);

    var options = {
        series: [ingresos, gastos],
        chart: {
            width: 380,
            type: 'pie',
        },
        labels: ['Ingresos', 'Gastos'],
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    var chart = new ApexCharts(document.querySelector("#chart"), options);
    chart.render();
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Datos dinámicos desde el controlador
    var labels = @json($labels); // Nacionalidades
    var data = @json($data); // Porcentajes

    // Asegurarse de que los datos estén bien sincronizados
    console.log("Labels:", labels);
    console.log("Data:", data);

    // Función para generar colores aleatorios
    function generateRandomColors(count) {
        let colors = [];
        for (let i = 0; i < count; i++) {
            colors.push(`#${Math.floor(Math.random() * 16777215).toString(16)}`); // Generar color aleatorio
        }
        return colors;
    }

    // Generar colores en función del número de labels
    // var colors = generateRandomColors(labels.length);
    var colors = [];
    for (let index = 0; index < labels.length; index++) {
        colorVariable = getColorByIndex(index)
        colors.push(colorVariable)
    }

    function getColorByIndex(index, opacity = 1) {
        const r = (index * 137 + 83) % 256; // Números primos para rotación
        const g = (index * 197 + 67) % 256; // Números primos para rotación
        const b = (index * 229 + 47) % 256; // Números primos para rotación
        return rgba(r, g, b, opacity);
    }

    var options = {
        series: [{
            name: 'Porcentaje de Reservas',
            data: data // Datos dinámicos
        }],
        chart: {
            height: 400,
            type: 'bar'
        },
        colors: colors, // Aplicar colores generados dinámicamente
        plotOptions: {
            bar: {
                columnWidth: '70%', // Ajustar el ancho de las barras
                distributed: true // Colores únicos para cada barra
            }
        },
        dataLabels: {
            enabled: true, // Mostrar etiquetas
            formatter: function (val) {
                return val + "%"; // Mostrar como porcentaje
            },
            offsetY: -20, // Ajustar posición
            style: {
                fontSize: '10px', // Reducir tamaño del texto
                colors: ["#000"] // Color del texto
            }
        },
        xaxis: {
            categories: labels, // Nacionalidades dinámicas
            labels: {
                style: {
                    colors: colors, // Aplicar colores a las etiquetas
                    fontSize: '12px' // Ajustar tamaño del texto
                }
            },
            title: {
                text: 'Nacionalidades',
                style: {
                    fontSize: '14px',
                    fontWeight: 'bold'
                }
            }
        },
        yaxis: {
            labels: {
                formatter: function (val) {
                    return val + "%"; // Formato del eje Y
                }
            }
        },
        title: {
            text: 'Porcentaje de Reservas por Nacionalidad',
            align: 'center',
            style: {
                fontSize: '24px',
                fontWeight: 'bold',
                color: '#444',
                fontFamily: "Nunito"
            }
        }
    };

    var chart = new ApexCharts(document.querySelector("#chartNacionalidad"), options);
    chart.render();
});


</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var options = {
            series: @json($totalesEdades), // Porcentajes dinámicos
            chart: {
                type: 'donut',
                height: 350
            },
            labels: @json($rangoEdades), // Rangos dinámicos
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 300
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }],
            title: {
                text: 'Distribución por Rango de Edad',
                align: 'center'
            },
            legend: {
                position: 'right',
                labels: {
                    useSeriesColors: true
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val.toFixed(2) + '%';
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#chartEdad"), options);
        chart.render();
    });


    document.addEventListener('DOMContentLoaded', function () {
    var options = {
        series: @json($ocupantesData), // Porcentajes dinámicos
        chart: {
            height: 390,
            type: 'radialBar',
        },
        plotOptions: {
            radialBar: {
                offsetY: 0,
                startAngle: 0,
                endAngle: 270,
                hollow: {
                    size: '30%',
                },
                dataLabels: {
                    name: {
                        show: true,
                        fontSize: '16px',
                        color: '#000',
                        offsetY: -10
                    },
                    value: {
                        show: true,
                        fontSize: '14px',
                        color: '#333',
                        offsetY: 5
                    }
                }
            }
        },
        colors: ['#1ab7ea', '#0084ff', '#39539E', '#0077B5', '#F5A623', '#E74C3C'], // Colores personalizados
        labels: @json($ocupantesLabels), // Etiquetas dinámicas
        legend: {
            show: true,
            floating: true, // Leyenda flotante como en la captura
            fontSize: '16px',
            position: 'left',
            offsetX: 10,
            offsetY: 10,
            labels: {
                useSeriesColors: true // Colores que coincidan con el gráfico
            },
            markers: {
                size: 8 // Tamaño de los puntos en la leyenda
            },
            formatter: function(seriesName, opts) {
                return seriesName + ": " + opts.w.globals.series[opts.seriesIndex] + "%";
            }
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 300
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    var chart = new ApexCharts(document.querySelector("#chartOcupantes"), options);
    chart.render();
});
//
document.addEventListener('DOMContentLoaded', function () {
    var options = {
        series: @json($sexoData), // Porcentajes dinámicos
        chart: {
            type: 'donut',
            height: 350
        },
        labels: @json($sexoLabels), // Etiquetas dinámicas
        plotOptions: {
            pie: {
                startAngle: -90,
                endAngle: 90,
                offsetY: 10
            }
        },
        grid: {
            padding: {
                bottom: -100
            }
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 300
                },
                legend: {
                    position: 'bottom'
                }
            }
        }],
        legend: {
            show: true,
            position: 'right',
            labels: {
                useSeriesColors: true
            },
            markers: {
                size: 8
            },
            formatter: function(seriesName, opts) {
                return seriesName + ": " + opts.w.globals.series[opts.seriesIndex] + "%";
            }
        }
    };

    var chart = new ApexCharts(document.querySelector("#chartSexo"), options);
    chart.render();
});
// Prescriptores
document.addEventListener('DOMContentLoaded', function () {
    var options = {
        series: [{
            data: @json($prescriptoresData)
        }],
        chart: {
            type: 'bar',
            height: 350
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                horizontal: true
            }
        },
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: @json($prescriptoresLabels),
        }
    };

    var chart = new ApexCharts(document.querySelector("#chartPrescriptores"), options);
    chart.render();
});
// Reservas por Apartamento
document.addEventListener('DOMContentLoaded', function () {
    var options = {
        series: [{
            data: @json($apartamentosData)
        }],
        chart: {
            type: 'bar',
            height: 350
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                horizontal: true
            }
        },
        dataLabels: {
            enabled: true, // Habilitamos las etiquetas de datos
            formatter: function (val) {
                return val + '%'; // Mostramos el valor con el símbolo de porcentaje
            },
            style: {
                fontSize: '12px',
                colors: ['#304758']
            },
            offsetX: 10 // Ajustamos la posición horizontal para mayor claridad
        },
        xaxis: {
            categories: @json($apartamentosLabels),
            labels: {
                style: {
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            labels: {
                formatter: function (val) {
                    return val + '%'; // Opcional: Mostrar porcentaje en los ejes
                }
            }
        }
    };

    var chart = new ApexCharts(document.querySelector("#chartApartamentos"), options);
    chart.render();
});

document.addEventListener('DOMContentLoaded', function () {
    var options = {
        series: @json($categoriasData), // Porcentajes de cada categoría
        chart: {
            type: 'donut',
            height: 350
        },
        labels: @json($categoriasLabels), // Etiquetas de las categorías
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    var chart = new ApexCharts(document.querySelector("#chartGastos"), options);
    chart.render();
});


</script>
@endsection
