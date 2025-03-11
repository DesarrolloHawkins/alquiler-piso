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
<div class="container-fluid">
    <h1 class="mb-4">DASHBOARD</h1>
    <form action="{{route('dashboard.index')}}" class="row align-items-end" method="GET">
        <h4>Filtrado por fechas</h4>
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

        <div class="col-md-3 col-sm-12 mt-4">
            <button type="submit" class="btn bg-color-primero w-sm-100 w-md-auto text-uppercase">Buscar</button>
        </div>
        <div class="col-md-3">
            <button id="verReservasBtn" class="btn bg-color-segundo w-sm-100 w-md-auto text-uppercase">Ver Reservas</button>
        </div>
    </form>
    <br>
    <div class="row" style="padding: 1rem;">
        <div class="col-12 mb-5">
            <div class="row justify-content-between align-items-stretch">
                <h3 class="text-center mt-5">Información de Gestión</h3>
                <hr>
                <div class="col-xl-4 col-md-6">
                    <div class="row p-3 card m-3 flex-row">
                        <div class="col-8">
                            <h4 class="text-start
                            mb-0 fs-5">Apartamentos Libres Hoy</h4>
                        </div>
                        <div class="col-4">
                            <h2 class="text-end mb-0 fs-4"><strong>{{$apartamentosLibresHoy->count()}}</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="row p-3 card m-3 flex-row">
                        <div class="col-8">
                            <h4 class="text-start mb-0 fs-5">Total de Reservas</h4>
                        </div>
                        <div class="col-4">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ $countReservas }}</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="row p-3 card m-3 flex-row">
                        <div class="col-7">
                            <h4 class="text-start mb-0 fs-5">Ocupacción %</h4>
                        </div>
                        <div class="col-5">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ $porcentajeOcupacion }} %</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="row p-3 card m-3 flex-row">
                        <div class="col-8">
                            <h4 class="text-start mb-0 fs-5">Ocupación</h4>
                        </div>
                        <div class="col-4">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ $nochesOcupadas }}</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="row p-3 card m-3 flex-row">
                        <div class="col-7">
                            <h4 class="text-start mb-0 fs-5">Ocupación Disponibles</h4>
                        </div>
                        <div class="col-5">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ $totalNochesPosibles }}</strong></h2>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <div class="row justify-content-between align-items-stretch">
                <h3 class="text-center mt-4">Información de Economica</h3>
                <hr>
                <div class="col-xl-4 col-md-6">
                    <div class="row p-3 card m-3 flex-row">
                        <div class="col-5">
                            <h4 class="text-start mb-0 fs-5">Facturación</h4>
                        </div>
                        <div class="col-7">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($sumPrecio, 2) }} €</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="row p-3 card m-3 flex-row">
                        <div class="col-5">
                            <h4 class="text-start mb-0 fs-5">Cobrado</h4>
                        </div>
                        <div class="col-7">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($ingresos, 2) }} €</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="row p-3 card m-3 flex-row">
                        <div class="col-6">
                            <h4 class="text-start mb-0 fs-5">Cash Flow</h4>
                        </div>
                        <div class="col-6">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($ingresos - $gastos, 2) }} €</strong></h2>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="row p-3 card m-3 flex-row">
                        <div class="col-6">
                            <h4 class="text-start mb-0 fs-5">Ingresos</h4>
                        </div>
                        <div class="col-6">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($ingresos, 2) }} €</strong></h2>
                        </div>
                    </div>

                </div>
                <div class="col-xl-4 col-md-6">
                    <div class="row p-3 card m-3 flex-row">
                        <div class="col-6">
                            <h4 class="text-start mb-0 fs-5">Gastos</h4>
                        </div>
                        <div class="col-6">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($gastos, 2) }} €</strong></h2>
                        </div>
                    </div>

                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="row p-3 card m-3 flex-row">
                        <div class="col-6">
                            <h4 class="text-start mb-0 fs-5">Beneficio</h4>
                        </div>
                        <div class="col-6">
                            <h2 class="text-end mb-0 fs-4"><strong>{{ number_format($sumPrecio - $gastos, 2) }} €</strong></h2>
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
    <h3 class="text-center mt-4">Estadisticas</h3>
    <hr>
    <div class="row justify-content-between align-items-stretch ">
        <div class="col-xl-12 col-md-12 rounded-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="chartNacionalidad"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-md-12 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Balance</h2>
                    <div id="chart"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-md-12 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Distribución por Género</h2>
                    <div id="chartSexo"></div>
                </div>

            </div>
        </div>
        <div class="col-md-6 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Distribución por Ocupantes</h2>
                    <div id="chartOcupantes"></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Distribución de Clientes por Rango de Edad</h2>
                    <div id="chartEdad"></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Distribución de Prescriptores</h2>
                    <div id="chartPrescriptores"></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Distribución de Reservas por Apartamento</h2>
                    <div id="chartApartamentos"></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 rounded-4 mt-3">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="text-center">Distribución de Gastos por Categoría</h2>
                    <div id="chartGastos"></div>
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
    document.getElementById("verReservasBtn").addEventListener("click", function () {
        // Obtener las fechas de entrada y salida desde los inputs
        let fechaEntrada = document.getElementById("fecha_inicio").value;
        let fechaSalida = document.getElementById("fecha_fin").value;

        // Si no hay fechas, dejar los parámetros vacíos
        let url = "/reservas?order_by=fecha_entrada&direction=asc&perPage=&search=";

        if (fechaEntrada) {
            url += `&fecha_entrada=${fechaEntrada}`;
        }
        if (fechaSalida) {
            url += `&fecha_salida=${fechaSalida}`;
        }

        // Abrir en una nueva pestaña
        window.open(url, "_blank");
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
        return `rgba(${r}, ${g}, ${b}, ${opacity})`; // Corrige la sintaxis
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
