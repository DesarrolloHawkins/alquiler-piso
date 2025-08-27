<?php

use App\Http\Controllers\CategoryEmailController;
use App\Http\Controllers\CuentasContableController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\GestionApartamentoController;
use App\Http\Controllers\MovimientosController;
use App\Http\Controllers\StatusMailController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\RatePlanController;
use App\Http\Controllers\RateUpdateController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\ARIController;
use App\Http\Controllers\AdminHolidaysController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\PresupuestoController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\MetalicoController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\WhatsappTemplateController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\TarifaController;
use App\Models\Cliente;
use App\Models\InvoicesStatus;
use App\Models\Reserva;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    if (Auth::check()) {
        // El usuario está autenticado, redirige a la ruta deseada.
        return redirect()->route('gestion.index');
    }
    return view('welcome');
})->name('inicio.welcome');
// routes/web.php
Route::get('/whatsapp/mensajes/{remitente}', [WhatsappController::class, 'mensajes']);

Route::get('/calendario/apartamento/{id}.ics', [CalendarioController::class, 'ics'])->name('calendario.ics');



Route::get('/regenerate-invoices', [App\Http\Controllers\InvoicesController::class, 'regenerateInvoicesForOctober']);
Route::get('/registrar-webhooks/{id}', [App\Http\Controllers\ApartamentosController::class, 'registrarWebhooks']);

Route::get('/request-data', function (Request $request) {
    return $request->all(); // Esto devolverá todos los datos de la solicitud
});
Route::get('paises', [App\Http\Controllers\HomeController::class, 'paises'])->name('paises');
Route::get('tipos', [App\Http\Controllers\HomeController::class, 'tipos'])->name('tipos');
Route::get('pruebas-dni', [App\Http\Controllers\HomeController::class, 'pruebas'])->name('pruebas');
Route::get('/get-reservas-json', [App\Http\Controllers\HomeController::class, 'getReservas'])->name('reservas.get.json');

Auth::routes();

Route::get('/test-chat-gpt', [App\Http\Controllers\TestController::class, 'chatGpt'])->name('test.chatGpt');

// Rutas de admin
Route::middleware(['auth', 'role:ADMIN'])->group(function () {

    Route::get('/admin', function () { return view('admin.dashboard');})->name('inicio');

    Route::resource('metalicos', MetalicoController::class);
    // Route::get('/metalico/create-gasto', [App\Http\Controllers\MetalicoController::class, 'createGasto'])->name('metalicos.createGasto');
    // Route::post('/metalico/store', [App\Http\Controllers\MetalicoController::class, 'storeGasto'])->name('metalicos.storeGasto');

    // Apartamentos
    Route::get('/apartamentos', [App\Http\Controllers\ApartamentosController::class, 'indexAdmin'])->name('apartamentos.admin.index');
    Route::get('/apartamentos/create', [App\Http\Controllers\ApartamentosController::class, 'createAdmin'])->name('apartamentos.admin.create');
    Route::get('/apartamentos/{id}/edit', [App\Http\Controllers\ApartamentosController::class, 'editAdmin'])->name('apartamentos.admin.edit');
    Route::post('/apartamentos/store', [App\Http\Controllers\ApartamentosController::class, 'storeAdmin'])->name('apartamentos.admin.store');
    Route::post('/apartamentos/{id}/update', [App\Http\Controllers\ApartamentosController::class, 'updateAdmin'])->name('apartamentos.admin.update');
    Route::post('/apartamentos/{id}/destroy', [App\Http\Controllers\ApartamentosController::class, 'destroy'])->name('apartamentos.admin.destroy');

    // Tarifas
    Route::resource('tarifas', TarifaController::class);
    Route::post('/tarifas/{tarifa}/toggle-status', [TarifaController::class, 'toggleStatus'])->name('tarifas.toggle-status');
    Route::post('/tarifas/{tarifa}/asignar-apartamento', [TarifaController::class, 'asignarApartamento'])->name('tarifas.asignar-apartamento');
    Route::post('/tarifas/{tarifa}/desasignar-apartamento', [TarifaController::class, 'desasignarApartamento'])->name('tarifas.desasignar-apartamento');

    Route::post('/upload-excel', [MovimientosController::class, 'uploadExcel'])->name('upload.excel');
    Route::post('/upload-csv-booking', [MovimientosController::class, 'uploadCSV'])->name('upload.csvBooking');
    Route::get('/upload-files', [MovimientosController::class, 'uploadFiles'])->name('admin.upload.files');
    Route::get('/upload-files-booking', [MovimientosController::class, 'uploadBooking'])->name('admin.uploadBooking.files');

    // Limpieza
    Route::get('aparatamento-limpieza/{id}/show', [\App\Http\Controllers\ApartamentoLimpiezaController::class, 'show'])->name('apartamentoLimpieza.admin.show');

    // Reservas
    // Route::get('/reservas', [App\Http\Controllers\ReservasController::class, 'index'])->name('reservas.index');

    // Clientes
    Route::get('/clientes', [App\Http\Controllers\ClientesController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/create', [App\Http\Controllers\ClientesController::class, 'create'])->name('clientes.create');
    Route::get('/clientes/{cliente}', [App\Http\Controllers\ClientesController::class, 'show'])->name('clientes.show');
    Route::post('/cliente/store', [App\Http\Controllers\ClientesController::class, 'store'])->name('clientes.store');
    Route::get('/cliente/edit/{id}', [App\Http\Controllers\ClientesController::class, 'edit'])->name('clientes.edit');
    Route::post('/cliente/update/{id}', [App\Http\Controllers\ClientesController::class, 'update'])->name('clientes.update');
    Route::post('/cliente/destroy/{id}', [App\Http\Controllers\ClientesController::class, 'destroy'])->name('clientes.destroy');

    // Reservas
    Route::get('/reservas', [App\Http\Controllers\ReservasController::class, 'index'])->name('reservas.index');
    Route::get('/reservas/{reserva}/show', [App\Http\Controllers\ReservasController::class, 'show'])->name('reservas.show');
    Route::get('/reservas/create', [App\Http\Controllers\ReservasController::class, 'create'])->name('reservas.create');
    Route::post('/reservas/store', [App\Http\Controllers\ReservasController::class, 'store'])->name('reservas.store');
    Route::post('/reservas/update/{id}', [App\Http\Controllers\ReservasController::class, 'update'])->name('reservas.update');
    Route::get('/get-reservas', [App\Http\Controllers\ReservasController::class, 'getReservas'])->name('reservas.get');
    Route::get('/get-room-types/{apartamento_id}', [App\Http\Controllers\ReservasController::class, 'getRoomTypes']);
    Route::get('reservas-calendar', [App\Http\Controllers\ReservasController::class, 'calendar'])->name('reservas.calendar');
    Route::put('/reservas/{id}', [App\Http\Controllers\ReservasController::class, 'updateReserva'])->name('reservas.updateReserva');
    Route::get('/reservas/{reserva}/edit', [App\Http\Controllers\ReservasController::class, 'edit'])->name('reservas.edit');
    Route::get('/reservas/{id}/destroy', [App\Http\Controllers\ReservasController::class, 'destroy'])->name('reservas.destroy');
    Route::post('/reservas/{id}/restore', [App\Http\Controllers\ReservasController::class, 'restore'])->name('reservas.restore');



    // Huespedes
    Route::get('/huespedes', [App\Http\Controllers\HuespedesController::class, 'index'])->name('huespedes.index');
    Route::get('/huesped/show/{id}', [App\Http\Controllers\HuespedesController::class, 'show'])->name('huespedes.show');

    // Configuraciones
    Route::get('/configuracion', [App\Http\Controllers\ConfiguracionesController::class, 'index'])->name('configuracion.index');
    Route::get('/configuracion/{id}/edit', [App\Http\Controllers\ConfiguracionesController::class, 'edit'])->name('configuracion.edit');
    Route::post('/configuracion/{id}/update', [App\Http\Controllers\ConfiguracionesController::class, 'update'])->name('configuracion.update');

    // Bancos
    Route::get('/bancos', [App\Http\Controllers\BancosController::class, 'index'])->name('admin.bancos.index');
    Route::get('/bancos-create', [App\Http\Controllers\BancosController::class, 'create'])->name('admin.bancos.create');
    Route::post('/bancos/store', [App\Http\Controllers\BancosController::class, 'store'])->name('admin.bancos.store');
    Route::get('/bancos/{banco}/edit', [App\Http\Controllers\BancosController::class, 'edit'])->name('admin.bancos.edit');
    Route::post('/bancos/{banco}/update', [App\Http\Controllers\BancosController::class, 'update'])->name('admin.bancos.update');
    Route::post('/bancos/{banco}/destroy', [App\Http\Controllers\BancosController::class, 'destroy'])->name('admin.bancos.destroy');

    // Edificios
    Route::get('/edificios', [App\Http\Controllers\EdificiosController::class, 'index'])->name('admin.edificios.index');
    Route::get('/edificio-create', [App\Http\Controllers\EdificiosController::class, 'create'])->name('admin.edificio.create');
    Route::post('/edificio/store', [App\Http\Controllers\EdificiosController::class, 'store'])->name('admin.edificio.store');
    Route::get('/edificio/{id}/edit', [App\Http\Controllers\EdificiosController::class, 'edit'])->name('admin.edificio.edit');
    Route::post('/edificio/{id}/update', [App\Http\Controllers\EdificiosController::class, 'update'])->name('admin.edificio.update');
    Route::post('/edificio/{id}/destroy', [App\Http\Controllers\EdificiosController::class, 'destroy'])->name('admin.edificio.destroy');

    // Categoria de Gastos
    Route::get('/categoria-gastos', [App\Http\Controllers\CategoriaGastosController::class, 'index'])->name('admin.categoriaGastos.index');
    Route::get('/categoria-gastos/create', [App\Http\Controllers\CategoriaGastosController::class, 'create'])->name('admin.categoriaGastos.create');
    Route::post('/categoria-gastos/store', [App\Http\Controllers\CategoriaGastosController::class, 'store'])->name('admin.categoriaGastos.store');
    Route::get('/categoria-gastos/{categoria}/edit', [App\Http\Controllers\CategoriaGastosController::class, 'edit'])->name('admin.categoriaGastos.edit');
    Route::post('/categoria-gastos/{categoria}/update', [App\Http\Controllers\CategoriaGastosController::class, 'update'])->name('admin.categoriaGastos.update');
    Route::post('/categoria-gastos/{categoria}/destroy', [App\Http\Controllers\CategoriaGastosController::class, 'destroy'])->name('admin.categoriaGastos.destroy');

    // Gastos
    Route::get('/gastos', [App\Http\Controllers\GastosController::class, 'index'])->name('admin.gastos.index');
    Route::get('/gastos/create', [App\Http\Controllers\GastosController::class, 'create'])->name('admin.gastos.create');
    Route::post('/gastos/store', [App\Http\Controllers\GastosController::class, 'store'])->name('admin.gastos.store');
    Route::get('/gastos/{categoria}/edit', [App\Http\Controllers\GastosController::class, 'edit'])->name('admin.gastos.edit');
    Route::post('/gastos/{categoria}/update', [App\Http\Controllers\GastosController::class, 'update'])->name('admin.gastos.update');
    Route::post('/gastos/{id}/destroy', [App\Http\Controllers\GastosController::class, 'destroy'])->name('admin.gastos.destroy');
    Route::get('/gastos/download/{id}', [App\Http\Controllers\GastosController::class, 'download'])->name('gastos.download');

    // Categoria de Ingresos
    Route::get('/categoria-ingresos', [App\Http\Controllers\CategoriaIngresosController::class, 'index'])->name('admin.categoriaIngresos.index');
    Route::get('/categoria-ingresos/create', [App\Http\Controllers\CategoriaIngresosController::class, 'create'])->name('admin.categoriaIngresos.create');
    Route::post('/categoria-ingresos/store', [App\Http\Controllers\CategoriaIngresosController::class, 'store'])->name('admin.categoriaIngresos.store');
    Route::get('/categoria-ingresos/{categoria}/edit', [App\Http\Controllers\CategoriaIngresosController::class, 'edit'])->name('admin.categoriaIngresos.edit');
    Route::post('/categoria-ingresos/{categoria}/update', [App\Http\Controllers\CategoriaIngresosController::class, 'update'])->name('admin.categoriaIngresos.update');
    Route::post('/categoria-ingresos/{categoria}/destroy', [App\Http\Controllers\CategoriaIngresosController::class, 'destroy'])->name('admin.categoriaIngresos.destroy');

    // Estados del Diario de Caja
    Route::get('/estados-diario', [App\Http\Controllers\EstadosDiarioController::class, 'index'])->name('admin.estadosDiario.index');
    Route::get('/estados-diario/create', [App\Http\Controllers\EstadosDiarioController::class, 'create'])->name('admin.estadosDiario.create');
    Route::post('/estados-diario/store', [App\Http\Controllers\EstadosDiarioController::class, 'store'])->name('admin.estadosDiario.store');
    Route::get('/estados-diario/{categoria}/edit', [App\Http\Controllers\EstadosDiarioController::class, 'edit'])->name('admin.estadosDiario.edit');
    Route::post('/estados-diario/{categoria}/update', [App\Http\Controllers\EstadosDiarioController::class, 'update'])->name('admin.estadosDiario.update');
    Route::post('/estados-diario/{categoria}/destroy', [App\Http\Controllers\EstadosDiarioController::class, 'destroy'])->name('admin.estadosDiario.destroy');

     // Ingresos
    Route::get('/ingresos', [App\Http\Controllers\IngresosController::class, 'index'])->name('admin.ingresos.index');
    Route::get('/ingresos/create', [App\Http\Controllers\IngresosController::class, 'create'])->name('admin.ingresos.create');
    Route::post('/ingresos/store', [App\Http\Controllers\IngresosController::class, 'store'])->name('admin.ingresos.store');
    Route::get('/ingresos/{categoria}/edit', [App\Http\Controllers\IngresosController::class, 'edit'])->name('admin.ingresos.edit');
    Route::post('/ingresos/{categoria}/update', [App\Http\Controllers\IngresosController::class, 'update'])->name('admin.ingresos.update');
    Route::post('/ingresos/{id}/destroy', [App\Http\Controllers\IngresosController::class, 'destroy'])->name('admin.ingresos.destroy');
    Route::get('/ingresos/download/{id}', [App\Http\Controllers\IngresosController::class, 'download'])->name('ingresos.download');

    // Diario de Caja
    Route::get('/diario-caja', [App\Http\Controllers\DiarioCajaController::class, 'index'])->name('admin.diarioCaja.index');
    Route::get('/diario-caja/ingreso', [App\Http\Controllers\DiarioCajaController::class, 'createIngreso'])->name('admin.diarioCaja.ingreso');
    Route::get('/diario-caja/gasto', [App\Http\Controllers\DiarioCajaController::class, 'createGasto'])->name('admin.diarioCaja.gasto');
    Route::post('/diario-caja/store', [App\Http\Controllers\DiarioCajaController::class, 'store'])->name('admin.diarioCaja.store');
    Route::post('/diario-caja/store/gasto', [App\Http\Controllers\DiarioCajaController::class, 'storeGasto'])->name('admin.diarioCaja.storeGasto');
    Route::get('/diario-caja/{id}/edit', [App\Http\Controllers\DiarioCajaController::class, 'edit'])->name('admin.diarioCaja.edit');
    Route::post('/diario-caja/{id}/update', [App\Http\Controllers\DiarioCajaController::class, 'update'])->name('admin.diarioCaja.update');
    Route::post('/diario-caja/{id}/destroy', [App\Http\Controllers\DiarioCajaController::class, 'destroy'])->name('admin.diarioCaja.destroy');
    Route::post('/diario-caja/{id}/destroy-linea', [App\Http\Controllers\DiarioCajaController::class, 'destroyDiarioCaja'])->name('admin.diarioCaja.destroyDiarioCaja');

    // Cuentas Contables
    Route::get('/cuentas-contables', [App\Http\Controllers\CuentasContableController::class, 'index'])->name('admin.cuentasContables.index');
    Route::get('/cuentas-contables/create', [App\Http\Controllers\CuentasContableController::class, 'create'])->name('admin.cuentasContables.create');
    Route::post('/cuentas-contables/store', [App\Http\Controllers\CuentasContableController::class, 'store'])->name('admin.cuentasContables.store');
    Route::get('/cuentas-contables/{id}/edit', [App\Http\Controllers\CuentasContableController::class, 'edit'])->name('admin.cuentasContables.edit');
    Route::post('/cuentas-contables/updated', [App\Http\Controllers\CuentasContableController::class, 'updated'])->name('admin.cuentasContables.updated');
    Route::delete('/cuentas-contables/destroy/{id}', [App\Http\Controllers\CuentasContableController::class, 'destroy'])->name('admin.cuentasContables.destroy');

    Route::get('/cuentas-contables/get-cuentas', [App\Http\Controllers\CuentasContableController::class, 'getCuentasByDataTables'])->name('admin.cuentasContables.getClients');

    // Sub-Cuentas Contables
    Route::get('/sub-cuentas-contables', [App\Http\Controllers\SubCuentasContableController::class, 'index'])->name('admin.subCuentasContables.index');
    Route::get('/sub-cuentas-contables/create', [App\Http\Controllers\SubCuentasContableController::class, 'create'])->name('admin.subCuentasContables.create');
    Route::post('/sub-cuentas-contables/store', [App\Http\Controllers\SubCuentasContableController::class, 'store'])->name('admin.subCuentasContables.store');
    Route::get('/sub-cuentas-contables/{id}/edit', [App\Http\Controllers\SubCuentasContableController::class, 'edit'])->name('admin.subCuentasContables.edit');
    Route::post('/sub-cuentas-contables/updated', [App\Http\Controllers\SubCuentasContableController::class, 'updated'])->name('admin.subCuentasContables.updated');
    Route::delete('/sub-cuentas-contables/destroy/{id}', [App\Http\Controllers\SubCuentasContableController::class, 'destroy'])->name('admin.subCuentasContables.destroy');

    // Sub-Cuentas Hijas Contables
    Route::get('/sub-cuentas-hijas-contables', [App\Http\Controllers\SubCuentasHijoController::class, 'index'])->name('admin.subCuentasHijaContables.index');
    Route::get('/sub-cuentas-hijas-contables/create', [App\Http\Controllers\SubCuentasHijoController::class, 'create'])->name('admin.subCuentasHijaContables.create');
    Route::post('/sub-cuentas-hijas-contables/store', [App\Http\Controllers\SubCuentasHijoController::class, 'store'])->name('admin.subCuentasHijaContables.store');
    Route::get('/sub-cuentas-hijas-contables/{id}/edit', [App\Http\Controllers\SubCuentasHijoController::class, 'edit'])->name('admin.subCuentasHijaContables.edit');
    Route::post('/sub-cuentas-hijas-contables/updated', [App\Http\Controllers\SubCuentasHijoController::class, 'updated'])->name('admin.subCuentasHijaContables.updated');
    Route::delete('/sub-cuentas-hijas-contables/destroy/{id}', [App\Http\Controllers\SubCuentasHijoController::class, 'destroy'])->name('admin.subCuentasHijaContables.destroy');

    // Grupos Contables
    Route::get('/grupo-contable', [App\Http\Controllers\GrupoContabilidadController::class, 'index'])->name('admin.grupoContabilidad.index');
    Route::get('/grupo-contable/create', [App\Http\Controllers\GrupoContabilidadController::class, 'create'])->name('admin.grupoContabilidad.create');
    Route::post('/grupo-contable/store', [App\Http\Controllers\GrupoContabilidadController::class, 'store'])->name('admin.grupoContabilidad.store');
    Route::get('/grupo-contable/{id}/edit', [App\Http\Controllers\GrupoContabilidadController::class, 'edit'])->name('admin.grupoContabilidad.edit');
    Route::post('/grupo-contable/updated', [App\Http\Controllers\GrupoContabilidadController::class, 'updated'])->name('admin.grupoContabilidad.updated');
    Route::delete('/grupo-contable/destroy/{id}', [App\Http\Controllers\GrupoContabilidadController::class, 'destroy'])->name('admin.grupoContabilidad.destroy');

    // Sub-Grupos Contables
    Route::get('/sub-grupo-contable', [App\Http\Controllers\SubGrupoContabilidadController::class, 'index'])->name('admin.subGrupoContabilidad.index');
    Route::get('/sub-grupo-contable/create', [App\Http\Controllers\SubGrupoContabilidadController::class, 'create'])->name('admin.subGrupoContabilidad.create');
    Route::post('/sub-grupo-contable/store', [App\Http\Controllers\SubGrupoContabilidadController::class, 'store'])->name('admin.subGrupoContabilidad.store');
    Route::get('/sub-grupo-contable/{id}/edit', [App\Http\Controllers\SubGrupoContabilidadController::class, 'edit'])->name('admin.subGrupoContabilidad.edit');
    Route::post('/sub-grupo-contable/updated', [App\Http\Controllers\SubGrupoContabilidadController::class, 'updated'])->name('admin.subGrupoContabilidad.updated');
    Route::delete('/sub-grupo-contable/destroy/{id}', [App\Http\Controllers\SubGrupoContabilidadController::class, 'destroy'])->name('admin.subGrupoContabilidad.destroy');

    // Ver usuario
    Route::get('/jornada', [App\Http\Controllers\JornadaController::class, 'index'])->name('admin.jornada.index');

    // Configuraciones
    Route::get('/configuracion', [App\Http\Controllers\ConfiguracionesController::class, 'index'])->name('configuracion.index');
    Route::get('/configuracion/{id}/edit', [App\Http\Controllers\ConfiguracionesController::class, 'edit'])->name('configuracion.edit');
    Route::post('/configuracion/{id}/update', [App\Http\Controllers\ConfiguracionesController::class, 'update'])->name('configuracion.update');
    Route::post('/configuracion/store-reparaciones', [App\Http\Controllers\ConfiguracionesController::class, 'storeReparaciones'])->name('configuracion.storeReparaciones');
    Route::post('/configuracion/update-reparaciones/{id}', [App\Http\Controllers\ConfiguracionesController::class, 'updateReparaciones'])->name('configuracion.updateReparaciones');
    Route::post('/configuracion/delete-reparaciones/{id}', [App\Http\Controllers\ConfiguracionesController::class, 'deleteReparaciones'])->name('configuracion.deleteReparaciones');
    Route::post('/configuracion/update-anio', [App\Http\Controllers\ConfiguracionesController::class, 'updateAnio'])->name('configuracion.updateAnio');
    Route::post('/configuracion/cierre-anio', [App\Http\Controllers\ConfiguracionesController::class, 'cierreAnio'])->name('configuracion.cierreAnio');
    Route::post('/configuracion/store-limpiadora', [App\Http\Controllers\ConfiguracionesController::class, 'storeLimpiadora'])->name('configuracion.storeLimpiadora');
    Route::post('/configuracion/update-limpiadora/{id}', [App\Http\Controllers\ConfiguracionesController::class, 'updateLimpiadora'])->name('configuracion.updateLimpiadora');
    Route::post('/configuracion/delete-limpiadora/{id}', [App\Http\Controllers\ConfiguracionesController::class, 'deleteLimpiadora'])->name('configuracion.deleteLimpiadora');
    Route::post('/configuracion/update-saldo', [App\Http\Controllers\ConfiguracionesController::class, 'saldoInicial'])->name('configuracion.saldoInicial');

    // Formas de Pago
    Route::post('/forma-pago/store', [App\Http\Controllers\FormasDePagoController::class, 'store'])->name('formaPago.store');
    Route::post('/forma-pago/update/{id}', [App\Http\Controllers\FormasDePagoController::class, 'update'])->name('formaPago.update');
    Route::post('/forma-pago/delete/{id}', [App\Http\Controllers\FormasDePagoController::class, 'delete'])->name('formaPago.delete');

    // Añadir apartamento para limpieza a fondo
    Route::get('/limpieza-apartamento', [App\Http\Controllers\GestionApartamentoController::class, 'limpiezaFondo'])->name('admin.limpiezaFondo.index');
    Route::get('/limpieza-apartamento/create', [App\Http\Controllers\GestionApartamentoController::class, 'limpiezaCreate'])->name('admin.limpiezaFondo.create');
    Route::post('/limpieza-apartamento', [App\Http\Controllers\GestionApartamentoController::class, 'limpiezaFondoStore'])->name('admin.limpiezaFondo.store');
    Route::get('/limpieza-apartamento/edit/{id}', [App\Http\Controllers\GestionApartamentoController::class, 'limpiezaFondoEdit'])->name('admin.limpiezaFondo.edit');
    Route::post('/limpieza-apartamento/update/{id}', [App\Http\Controllers\GestionApartamentoController::class, 'limpiezaFondoUpdate'])->name('admin.limpiezaFondo.update');
    Route::post('/limpieza-apartamento/destroy/{id}', [App\Http\Controllers\GestionApartamentoController::class, 'limpiezaFondoDestroy'])->name('admin.limpiezaFondo.destroy');


    Route::get('/plan-contable', [App\Http\Controllers\PlanContableController::class, 'index'])->name('admin.planContable.index');
    Route::get('/plan-contable/json', [App\Http\Controllers\PlanContableController::class, 'json']);

    Route::post('/actualizar-prompt', [App\Http\Controllers\ConfiguracionesController::class, 'actualizarPrompt'])->name('configuracion.actualizarPrompt');
    Route::post('/add-emails', [App\Http\Controllers\ConfiguracionesController::class, 'addEmailNotificaciones'])->name('configuracion.emails.add');
    Route::post('/delete-emails/{id}', [App\Http\Controllers\ConfiguracionesController::class, 'deleteEmailNotificaciones'])->name('configuracion.emails.delete');
    Route::post('/update-emails/{id}', [App\Http\Controllers\ConfiguracionesController::class, 'updateEmailNotificaciones'])->name('configuracion.emails.update');



    // Checklists - Limpieza
    Route::get('/checklists', [App\Http\Controllers\ChecklistController::class, 'index'])->name('admin.checklists.index');
    Route::get('/checklists-create', [App\Http\Controllers\ChecklistController::class, 'create'])->name('admin.checklists.create');
    Route::post('/checklists/store', [App\Http\Controllers\ChecklistController::class, 'store'])->name('admin.checklists.store');
    Route::get('/checklists/{id}/edit', [App\Http\Controllers\ChecklistController::class, 'edit_new'])->name('admin.checklists.edit');
    Route::post('/checklists/{id}/update', [App\Http\Controllers\ChecklistController::class, 'update'])->name('admin.checklists.update');
    Route::post('/checklists/{id}/destroy', [App\Http\Controllers\ChecklistController::class, 'destroy'])->name('admin.checklists.destroy');

    // Items_checklist - Limpieza
    Route::get('/items_checklist', [App\Http\Controllers\ItemChecklistController::class, 'index'])->name('admin.itemsChecklist.index');
    Route::get('/items_checklist-create', [App\Http\Controllers\ItemChecklistController::class, 'create'])->name('admin.itemsChecklist.create');
    Route::post('/items_checklist/store', [App\Http\Controllers\ItemChecklistController::class, 'store'])->name('admin.itemsChecklist.store');
    Route::get('/items_checklist/{id}/edit', [App\Http\Controllers\ItemChecklistController::class, 'edit'])->name('admin.itemsChecklist.edit');
    Route::post('/items_checklist/{id}/update', [App\Http\Controllers\ItemChecklistController::class, 'update'])->name('admin.itemsChecklist.update');
    Route::post('/items_checklist/{id}/destroy', [App\Http\Controllers\ItemChecklistController::class, 'destroy'])->name('admin.itemsChecklist.destroy');

    // Proveedores
    Route::get('/proveedores', [App\Http\Controllers\ProveedoresController::class, 'index'])->name('admin.proveedores.index');
    Route::get('/proveedores/create', [App\Http\Controllers\ProveedoresController::class, 'create'])->name('admin.proveedores.create');
    Route::post('/proveedores/store', [App\Http\Controllers\ProveedoresController::class, 'store'])->name('admin.proveedores.store');
    Route::get('/proveedores/{id}/edit', [App\Http\Controllers\ProveedoresController::class, 'edit'])->name('admin.proveedores.edit');
    Route::post('/proveedores/{id}/update', [App\Http\Controllers\ProveedoresController::class, 'update'])->name('admin.proveedores.update');
    Route::post('/proveedores/{id}/destroy', [App\Http\Controllers\ProveedoresController::class, 'destroy'])->name('admin.proveedores.destroy');

    // Tabla de Reservas
    Route::get('/tabla-reservas', [App\Http\Controllers\TablaReservasController::class, 'index'])->name('admin.tablaReservas.index');
    Route::get('/get-reservas', [App\Http\Controllers\ReservasController::class, 'getReservas'])->name('reservas.get');


    // Facturas
    Route::get('/facturas',[App\Http\Controllers\InvoicesController::class, 'index'])->name('admin.facturas.index');
    Route::get('/facturas/{id}/edit',[App\Http\Controllers\InvoicesController::class, 'edit'])->name('admin.facturas.edit');
    Route::put('/facturas/{id}',[App\Http\Controllers\InvoicesController::class, 'update'])->name('admin.facturas.update');
    Route::get('/facturas-excel',[App\Http\Controllers\InvoicesController::class, 'exportInvoices'])->name('admin.facturas.export');
    Route::get('/facturas-descargar/{id}',[App\Http\Controllers\InvoicesController::class, 'previewPDF'])->name('admin.facturas.previewPDF');
    Route::get('/invoice/pdf/{id}', [App\Http\Controllers\InvoicesController::class, 'generateInvoicePDF'])->name('admin.facturas.generatePdf');
    Route::post('/generar-factura',[App\Http\Controllers\InvoicesController::class, 'facturar'])->name('admin.facturas.facturar');
    Route::post('/facturas/update-fecha/{id}', [App\Http\Controllers\InvoicesController::class, 'updateFecha'])->name('admin.facturas.updateFecha');
    Route::get('/admin/facturas/download-zip', [App\Http\Controllers\InvoicesController::class, 'downloadInvoicesZip'])->name('admin.facturas.downloadZip');


    // Vacaciones
    Route::get('/holiday/index', [AdminHolidaysController::class, 'index'])->name('holiday.admin.index');
    Route::get('/holiday/admin-create', [AdminHolidaysController::class, 'create'])->name('holiday.admin.create');
    Route::get('/holiday/store', [AdminHolidaysController::class, 'store'])->name('holiday.admin.store');
    Route::get('/holiday/destroy', [AdminHolidaysController::class, 'destroy'])->name('holiday.admin.destroy');
    Route::get('/holidays/admin-edit/{id}', [AdminHolidaysController::class, 'edit'])->name('holiday.admin.edit');
    Route::post('/holidays/admin-update', [AdminHolidaysController::class, 'update'])->name('holiday.admin.update');
    Route::get('/holidays/petitions', [AdminHolidaysController::class, 'usersPetitions'])->name('holiday.admin.petitions');
    Route::get('/holidays/record', [AdminHolidaysController::class, 'addedRecord'])->name('holiday.admin.record');
    Route::get('/holidays/history', [AdminHolidaysController::class, 'allHistory'])->name('holiday.admin.history');
    Route::get('/holidays/managePetition/{id}', [AdminHolidaysController::class, 'managePetition'])->name('holiday.admin.managePetition');
    Route::post('/holidays/acceptHolidays', [AdminHolidaysController::class, 'acceptHolidays'])->name('holiday.admin.acceptHolidays');
    Route::post('/holidays/denyHolidays', [AdminHolidaysController::class, 'denyHolidays'])->name('holiday.admin.denyHolidays');
    Route::post('/holidays/getDate/{holidaysPetitions}', [AdminHolidaysController::class, 'getDate'])->name('holiday.admin.getDate');

    // Estadisticas
    Route::get('/estadisticas',[App\Http\Controllers\InvoicesController::class, 'index'])->name('admin.estadisticas.buscar');



    // Categoria de Emails
    Route::get('/category-email', [App\Http\Controllers\CategoryEmailController::class, 'index'])->name('admin.categoriaEmail.index');
    Route::get('/category-email/create', [App\Http\Controllers\CategoryEmailController::class, 'create'])->name('admin.categoriaEmail.create');
    Route::post('/category-email/store', [App\Http\Controllers\CategoryEmailController::class, 'store'])->name('admin.categoriaEmail.store');
    Route::get('/category-email/{id}/edit', [App\Http\Controllers\CategoryEmailController::class, 'edit'])->name('admin.categoriaEmail.edit');
    Route::post('/category-email/{id}/update', [App\Http\Controllers\CategoryEmailController::class, 'update'])->name('admin.categoriaEmail.update');
    Route::post('/category-email/{id}/destroy', [App\Http\Controllers\CategoryEmailController::class, 'destroy'])->name('admin.categoriaEmail.destroy');

    // Estados de Emails
    Route::get('/status-mail', [App\Http\Controllers\StatusMailController::class, 'index'])->name('admin.statusMail.index');
    Route::get('/status-mail/create', [App\Http\Controllers\StatusMailController::class, 'create'])->name('admin.statusMail.create');
    Route::post('/status-mail/store', [App\Http\Controllers\StatusMailController::class, 'store'])->name('admin.statusMail.store');
    Route::get('/status-mail/{id}/edit', [App\Http\Controllers\StatusMailController::class, 'edit'])->name('admin.statusMail.edit');
    Route::post('/status-mail/{id}/update', [App\Http\Controllers\StatusMailController::class, 'update'])->name('admin.statusMail.update');
    Route::post('/status-mail/{id}/destroy', [App\Http\Controllers\StatusMailController::class, 'destroy'])->name('admin.statusMail.destroy');
    //Route::resource('category_email', CategoryEmailController::class);

    // Usuarios - Empleados
    Route::get('/empleados', [App\Http\Controllers\UserController::class, 'index'])->name('admin.empleados.index');
    Route::get('/empleados/create', [App\Http\Controllers\UserController::class, 'create'])->name('admin.empleados.create');
    Route::post('/empleados/store', [App\Http\Controllers\UserController::class, 'store'])->name('admin.empleados.store');
    Route::get('/empleados/{id}/edit', [App\Http\Controllers\UserController::class, 'edit'])->name('admin.empleados.edit');
    Route::post('/empleados/{id}/update', [App\Http\Controllers\UserController::class, 'update'])->name('admin.empleados.update');
    Route::post('/empleados/{id}/destroy', [App\Http\Controllers\UserController::class, 'destroy'])->name('admin.empleados.destroy');

    // Emails
    Route::get('/emails', [EmailController::class, 'index'])->name('admin.emails.index');
    Route::get('/emails/{email}', [EmailController::class, 'show'])->name('admin.emails.show');

    Route::get('/emails-recive',[EmailController::class, 'email'])->name('admin.facturas.email');

    Route::prefix('presupuestos')->group(function () {
        Route::get('/', [PresupuestoController::class, 'index'])->name('presupuestos.index');
        Route::get('/create', [PresupuestoController::class, 'create'])->name('presupuestos.create');
        Route::post('/', [PresupuestoController::class, 'store'])->name('presupuestos.store');
        Route::get('/{id}', [PresupuestoController::class, 'show'])->name('presupuestos.show');
        Route::get('/{id}/edit', [PresupuestoController::class, 'edit'])->name('presupuestos.edit');
        Route::put('/{id}', [PresupuestoController::class, 'update'])->name('presupuestos.update');
        Route::delete('/{id}', [PresupuestoController::class, 'destroy'])->name('presupuestos.destroy');
    });
    Route::post('presupuestos/{presupuesto}/facturar', [PresupuestoController::class, 'facturar'])
    ->name('presupuestos.facturar');

    // admin.facturas.export
});

// Rutas de usuarios logueados
Route::middleware('auth')->group(function () {
    Route::get('/dashboard',[App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/pisos',[App\Http\Controllers\ApartamentosController::class, 'index'])->name('apartamentos.index');

    Route::get('/reservas-calendar', [App\Http\Controllers\ReservasController::class, 'calendar'])->name('reservas.calendar');

    Route::post('/fichajes/iniciar', [App\Http\Controllers\FichajeController::class, 'iniciarJornada'])->name('fichajes.iniciar');
    Route::post('/fichajes/pausa/iniciar', [App\Http\Controllers\FichajeController::class, 'iniciarPausa'])->name('fichajes.pausa.iniciar');
    Route::post('/fichajes/pausa/finalizar', [App\Http\Controllers\FichajeController::class, 'finalizarPausa'])->name('fichajes.pausa.finalizar');
    Route::post('/fichajes/finalizar', [App\Http\Controllers\FichajeController::class, 'finalizarJornada'])->name('fichajes.finalizar');

    //Holidays(Vacaciones users)
    Route::get('/holidays', [HolidayController::class, 'index'])->name('holiday.index');
    Route::get('/holidays/edit/{id}', [HolidayController::class, 'edit'])->name('holiday.edit');
    Route::post('/holidays/store', [HolidayController::class, 'store'])->name('holiday.store');
    Route::get('/holidays/create', [HolidayController::class, 'create'])->name('holiday.create');
    // Más rutas que solo deben ser accesibles
});



// Vistas
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/test', [App\Http\Controllers\HomeController::class, 'test'])->name('home');
Route::get('/email', [App\Http\Controllers\EstadoController::class, 'index'])->name('email.index');
Route::post('/comprobacion-server', [App\Http\Controllers\EstadoController::class, 'comprobacionServer'])->name('comprobacionServer');





// Añadir Reserva
Route::post('/agregar-reserva', [App\Http\Controllers\ReservasController::class, 'agregarReserva'])->name('reservas.agregarReserva');
Route::post('/reserva/agregar', [App\Http\Controllers\ReservasController::class, 'agregarReserva'])->name('reserva.agregar');

// Verificar Reserva de Booking
Route::get('/verificar-reserva/{reserva}', [App\Http\Controllers\ComprobarReserva::class, 'verificarReserva'])->name('reservas.verificarReserva');
Route::post('/enviar-dni/{id}', [App\Http\Controllers\ReservasController::class, 'enviarDni'])->name('reservas.enviarDni');
Route::post('/cancelar-booking/{reserva}', [App\Http\Controllers\ReservasController::class, 'cancelarBooking'])->name('cancelarBooking.index');
Route::post('/actualizar-booking/{reserva}', [App\Http\Controllers\ReservasController::class, 'actualizarBooking'])->name('actualizarBooking.index');
Route::post('/obtener-reserva', [App\Http\Controllers\ComprobarReserva::class, 'obtenerReserva'])->name('reservas.obtenerReserva');
Route::post('/obtener-codigos', [App\Http\Controllers\ComprobarReserva::class, 'obtenerCodigos'])->name('reservas.obtenerCodigos');
Route::post('/obtener-codigos-airbnb', [App\Http\Controllers\ComprobarReserva::class, 'obtenerCodigosAirBnb'])->name('reservas.obtenerCodigosAirBnb');

// Verificar Reserva de Airbnb
Route::get('/comprobar-reserva/{id}', [App\Http\Controllers\ComprobarReserva::class, 'index'])->name('comprobar.index');
Route::get('/comprobar-reserva-web/{id}', [App\Http\Controllers\ComprobarReserva::class, 'comprobarReservaWeb'])->name('comprobar.comprobarReservaWeb');
Route::post('/cancelar-airbnb/{reserva}', [App\Http\Controllers\ReservasController::class, 'cancelarAirBnb'])->name('cancelarAirBnb.index');
Route::post('/actualizar-airbnb/{reserva}', [App\Http\Controllers\ReservasController::class, 'actualizarAirbnb'])->name('actualizarAirbnb.index');


// Gestion del Apartamento
Route::get('/gestion', [App\Http\Controllers\GestionApartamentoController::class, 'index'])->name('gestion.index');
Route::get('/gestion-create/{id}', [App\Http\Controllers\GestionApartamentoController::class, 'create'])->name('gestion.create');
Route::post('/gestion-store', [App\Http\Controllers\GestionApartamentoController::class, 'store'])->name('gestion.store');
Route::get('/gestion-edit/{apartamentoLimpieza}', [App\Http\Controllers\GestionApartamentoController::class, 'edit'])->name('gestion.edit');
Route::post('/gestion-update/{apartamentoLimpieza}', [App\Http\Controllers\GestionApartamentoController::class, 'update'])->name('gestion.update');
Route::post('/gestion-finalizar/{apartamentoLimpieza}', [App\Http\Controllers\GestionApartamentoController::class, 'finalizar'])->name('gestion.finalizar');
Route::post('/gestion-store-column', [App\Http\Controllers\GestionApartamentoController::class, 'storeColumn'])->name('gestion.storeColumn');
Route::post('/gestion/{id}/upload-photo', [GestionApartamentoController::class, 'uploadPhoto'])->name('photo.upload');
Route::post('/gestion/update-checkbox/', [GestionApartamentoController::class, 'updateCheckbox'])->name('gestion.updateCheckbox');
Route::get('/gestion-create-fondo/{id}', [GestionApartamentoController::class, 'create_fondo'])->name('gestion.create_fondo');

// Fotos
Route::get('/fotos-dormitorio/{id}/{cat}', [App\Http\Controllers\PhotoController::class, 'index'])->name('fotos.dormitorio');
Route::post('/dormitorio-store/{id}/{cat}', [App\Http\Controllers\PhotoController::class, 'store'])->name('fotos.dormitorio-store');
Route::post('/actualizar-fotos-dormitorio/{id}/{cat}', [App\Http\Controllers\PhotoController::class, 'actualizar'])->name('actualizar.fotos.dormitorio');

// Route::post('/fotos-dormitorio-store/{id}', [App\Http\Controllers\PhotoController::class, 'dormitorioStore'])->name('fotos.dormitorioStore');
Route::get('/fotos-salon/{id}/{cat}', [App\Http\Controllers\PhotoController::class, 'index'])->name('fotos.salon');
Route::post('/fotos-salon-store/{id}/{cat}', [App\Http\Controllers\PhotoController::class, 'store'])->name('fotos.salon-store');
Route::post('/actualizar-fotos-salin/{id}/{cat}', [App\Http\Controllers\PhotoController::class, 'actualizar'])->name('actualizar.fotos.salon');


Route::get('/fotos-cocina/{id}/{cat}', [App\Http\Controllers\PhotoController::class, 'index'])->name('fotos.cocina');
Route::post('/fotos-cocina-store/{id}/{cat}', [App\Http\Controllers\PhotoController::class, 'store'])->name('fotos.cocina-store');
Route::post('/actualizar-fotos-cocina/{id}/{cat}', [App\Http\Controllers\PhotoController::class, 'actualizar'])->name('actualizar.fotos.cocina');

Route::get('/fotos-cocina-comun/{id}/{cat}', [App\Http\Controllers\PhotoController::class, 'index'])->name('fotos.cocina_comun');
Route::post('/fotos-cocina-comun-store/{id}/{cat}', [App\Http\Controllers\PhotoController::class, 'store'])->name('fotos.cocina_comun-store');
Route::post('/actualizar-fotos-cocina-comun/{id}/{cat}', [App\Http\Controllers\PhotoController::class, 'actualizar'])->name('actualizar.fotos.cocinaComun');

Route::get('/fotos-cajon_de_cama/{id}/{cat}', [App\Http\Controllers\PhotoController::class, 'index'])->name('fotos.cajon_de_cama');
Route::post('/fotos-cajon_de_cama/{id}/{cat}', [App\Http\Controllers\PhotoController::class, 'store'])->name('fotos.cajon_de_cama-store');
Route::post('/actualizar-fotos-cajon_de_cama/{id}/{cat}', [App\Http\Controllers\PhotoController::class, 'actualizar'])->name('actualizar.fotos.cajonDeCama');

Route::get('/fotos-banio/{id}/{cat}', [App\Http\Controllers\PhotoController::class, 'index'])->name('fotos.bano');
Route::post('/fotos-banio-store/{id}/{cat}', [App\Http\Controllers\PhotoController::class, 'store'])->name('fotos.bano-store');
Route::post('/actualizar-fotos-banio/{id}/{cat}', [App\Http\Controllers\PhotoController::class, 'actualizar'])->name('actualizar.fotos.bano');


// Obtener DNI
Route::get('/dni-user/{token}', [App\Http\Controllers\DNIController::class, 'index'])->name('dni.index');
Route::post('/dni/cambiar-idioma', [App\Http\Controllers\DNIController::class, 'cambiarIdioma'])->name('dni.cambiarIdioma');

Route::post('/guardar-numero-personas', [App\Http\Controllers\DNIController::class, 'storeNumeroPersonas'])->name('dni.storeNumeroPersonas');
Route::post('/dni-user/store', [App\Http\Controllers\DNIController::class, 'store'])->name('dni.store');
Route::get('/dni-user-subir/{id}', [App\Http\Controllers\DNIController::class, 'dniUpload'])->name('dni.dniUpload');
Route::get('/pasaporte-user-subir/{id}', [App\Http\Controllers\DNIController::class, 'pasaporteUpload'])->name('dni.dniUpload');
Route::get('/dni/{token}', [App\Http\Controllers\DNIController::class, 'dni'])->name('dni.dni');
Route::get('/pasaporte/{token}', [App\Http\Controllers\DNIController::class, 'pasaporte'])->name('dni.pasaporte');



// AI whatsapp
Route::get('/whatsapp', [App\Http\Controllers\WhatsappController::class, 'hookWhatsapp'])->name('whatsapp.hookWhatsapp');
Route::post('/whatsapp', [App\Http\Controllers\WhatsappController::class, 'processHookWhatsapp'])->name('whatsapp.processHookWhatsapp');
// Route::get('/chatgpt','SiteController@chatGptPruebas')->name('admin.estadisticas.hookWhatsapp');
// Route::get('/cron','SiteController@obtenerAudioMedia2')->name('admin.estadisticas.obtenerAudioMedia2');
Route::get('/chatgpt/{texto}', [App\Http\Controllers\WhatsappController::class, 'chatGptPruebas'])->name('whatsapp.chatGptPruebas');
Route::get('/cron', [App\Http\Controllers\WhatsappController::class, 'cron'])->name('whatsapp.cron');
Route::post('/whatsapp-envio', [App\Http\Controllers\WhatsappController::class, 'envioAutoVoz'])->name('whatsapp.envioAutoVoz');
//Route::post('/whatsapp-alerta', [App\Http\Controllers\WhatsappController::class, 'envioAlerta'])->name('whatsapp.envioAlerta');

// Rutas varias
Route::get('/gracias/{idioma}', [App\Http\Controllers\GraciasController::class, 'index'])->name('gracias.index');
Route::get('/contacto', [App\Http\Controllers\GraciasController::class, 'contacto'])->name('gracias.contacto');

Route::get('/mensajes-whatsapp', [App\Http\Controllers\WhatsappController::class, 'whatsapp'])->name('whatsapp.mensajes');
Route::post('/pass-booking', [App\Http\Controllers\ConfiguracionesController::class, 'passBooking'])->name('comprobacion.passBooking');
Route::post('/pass-airbnb', [App\Http\Controllers\ConfiguracionesController::class, 'passAirbnb'])->name('comprobacion.passAirbnb');

Route::post('/gastos-introducir', [App\Http\Controllers\GastosController::class, 'clasificarGastos'])->name('admin.gastos.clasificarGastos');
Route::post('/ingresos-introducir', [App\Http\Controllers\IngresosController::class, 'clasificarIngresos'])->name('admin.ingresos.clasificarIngresos');
Route::post('/get-data', [App\Http\Controllers\ReservasController::class, 'getData'])->name('admin.ingresos.getData');
Route::post('/change-state', [App\Http\Controllers\ReservasController::class, 'changeState'])->name('admin.ingresos.changeState');
Route::get('/facturar-reservas', [App\Http\Controllers\ReservasController::class, 'facturarReservas'])->name('admin.reservas.facturarReservas');
Route::post('/get-reserva-ia/{codigo}', [App\Http\Controllers\ReservasController::class, 'getReservaIA'])->name('admin.reservas.getReservaIA');

Route::get('/probar-ia', [App\Http\Controllers\ReservasController::class, 'probarIA'])->name('probarIA');
Route::get('/instrucciones', [App\Http\Controllers\ReservasController::class, 'mostrarInstrucciones'])->name('mostrarInstrucciones');
Route::post('/guardar-instrucciones', [App\Http\Controllers\ReservasController::class, 'guardarInstrucciones'])->name('guardarInstrucciones');
Route::post('/reservas-cobradas', [App\Http\Controllers\ReservasController::class, 'reservasCobradas'])->name('reservasCobradas');
Route::post('/obtener-reservas', [App\Http\Controllers\ReservasController::class, 'obtenerReservas'])->name('obtenerReservas');


// CHANNEX
Route::prefix('channex')->group(function () {
    Route::get('/full-sync', [App\Http\Controllers\ChannexWebController::class, 'fullSync'])->name('admin.channex.fullSync');
    Route::get('/rate-plans-list', [App\Http\Controllers\ChannexWebController::class, 'ratePlansList'])->name('admin.channex.ratePlansList');

});



// API
Route::post('/obtener-reservas-ia', [App\Http\Controllers\ReservasController::class, 'obtenerReservasIA'])->name('obtenerReservas');
Route::get('/obtener-apartamentos', [App\Http\Controllers\ReservasController::class, 'obtenerApartamentos'])->name('obtenerApartamentos');

Route::get('/chat/send-message', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.sendMessage');
Route::post('/avisar-tecnico', [App\Http\Controllers\ReservasController::class, 'avisarAveria'])->name('avisarAveria');
Route::post('/avisar-limpieza', [App\Http\Controllers\ReservasController::class, 'avisarLimpieza'])->name('avisarLimpieza');

Route::post('/channex/property', [App\Http\Controllers\ChannexWebController::class, 'createTestProperty'])->name('channex.createProperty');
Route::get('/channex/property', [App\Http\Controllers\ChannexWebController::class, 'index'])->name('channex.propiedad.index');
Route::get('/channex/property/create', [App\Http\Controllers\ChannexWebController::class, 'createProperty'])->name('channex.createPropiedad');
Route::post('/channex/property/store', [App\Http\Controllers\ChannexWebController::class, 'store'])->name('channex.storeProperty');
//Route::post('/channex/room-types/{propertyId}', [App\Http\Controllers\ChannexWebController::class, 'createRoomTypes'])->name('channex.createRoomTypes');
Route::post('/channex/rate-plans', [App\Http\Controllers\ChannexWebController::class, 'createRatePlans'])->name('channex.createRatePlans');
Route::post('/channex/distribution-channels/{propertyId}', [App\Http\Controllers\ChannexWebController::class, 'createDistributionChannels'])->name('channex.createDistributionChannels');
Route::post('/channex/bookings/{channelCode}/{propertyId}/{roomTypeId}', [App\Http\Controllers\ChannexWebController::class, 'createBooking'])->name('channex.createBooking');
Route::post('/channex/bookings/{bookingId}/confirm', [App\Http\Controllers\ChannexWebController::class, 'confirmBooking'])->name('channex.confirmBooking');
Route::post('/upload-photo', [App\Http\Controllers\PhotoController::class, 'upload'])->name('photo.upload');


Route::post('/webhook-handler', [App\Http\Controllers\WebhookController::class, 'handleWebhook']);


// Rate Plans
//Route::resource('/channex/rate-plans', RatePlanController::class);
Route::get('/channex/rate-plans', [App\Http\Controllers\RatePlanController::class, 'index'])->name('channex.ratePlans.index');
Route::get('/channex/rate-plans/create', [App\Http\Controllers\RatePlanController::class, 'create'])->name('channex.ratePlans.create');
Route::get('/channex/rate-plans/edit', [App\Http\Controllers\RatePlanController::class, 'edit'])->name('channex.ratePlans.edit');
Route::post('/channex/rate-plans/store', [App\Http\Controllers\RatePlanController::class, 'store'])->name('channex.ratePlans.store');
Route::post('/channex/rate-plans/destroy', [App\Http\Controllers\RatePlanController::class, 'destroy'])->name('channex.ratePlans.destroy');
Route::post('/channex/rate-plans/update', [App\Http\Controllers\RatePlanController::class, 'update'])->name('channex.ratePlans.update');

Route::resource('/channex/rate-updates', RateUpdateController::class)->only(['create', 'store']);

Route::get('/channex/room-types', [App\Http\Controllers\RoomTypeController::class, 'index'])->name('channex.roomTypes.index');
Route::post('/channex/room-types/store', [App\Http\Controllers\RoomTypeController::class, 'store'])->name('channex.roomTypes.store');
Route::get('/channex/room-types/create', [App\Http\Controllers\RoomTypeController::class, 'create'])->name('channex.roomTypes.create');
Route::get('/channex/room-types/edit', [App\Http\Controllers\RoomTypeController::class, 'edit'])->name('channex.roomTypes.edit');
Route::get('/channex/room-types/destroy', [App\Http\Controllers\RoomTypeController::class, 'destroy'])->name('channex.roomTypes.destroy');
//Route::resource('/channex/room-types', RoomTypeController::class);

Route::get('/channex/channel', [App\Http\Controllers\ChannelController::class, 'index'])->name('channex.channel.index');


Route::get('/channex/ari', [ARIController::class, 'index'])->name('ari.index');
Route::post('/channex/full-sync', [ARIController::class, 'fullSync'])->name('ari.fullSync');

Route::post('/channex/ari/update-rates', [ARIController::class, 'update'])->name('ari.updateRates');
Route::get('/channex/ari/room-types/{property_id}', [ARIController::class, 'getByProperty']);
Route::get('/channex/rate-plans/{propertyId}/{roomTypeId}', [ARIController::class, 'getRatePlans']);

// Rutas para obtener precios diarios
Route::post('/channex/ari/daily-prices', [ARIController::class, 'getDailyPrices'])->name('ari.dailyPrices');
Route::post('/channex/ari/all-daily-prices', [ARIController::class, 'getAllDailyPrices'])->name('ari.allDailyPrices');

// Webhooks
Route::post('/channex', [App\Http\Controllers\ChannexController::class, 'webhook'])->name('channex.webhook');
Route::post('/ari-changes', [App\Http\Controllers\ChannexController::class, 'ariChanges'])->name('channex.ariChanges');
Route::post('/booking-any', [App\Http\Controllers\ChannexController::class, 'bookingAny'])->name('channex.bookingAny');
Route::post('/new-booking', [App\Http\Controllers\ChannexController::class, 'newBooking'])->name('channex.newBooking');
Route::post('/modification-booking', [App\Http\Controllers\ChannexController::class, 'modificationBooking'])->name('channex.modificationBooking');
Route::post('/cancellation-booking', [App\Http\Controllers\ChannexController::class, 'cancellationBooking'])->name('channex.cancellationBooking');
Route::post('/channel-sync-error', [App\Http\Controllers\ChannexController::class, 'channelSyncError'])->name('channex.channelSyncError');
Route::post('/reservation-request', [App\Http\Controllers\ChannexController::class, 'reservationRequest'])->name('channex.reservationRequest');
Route::post('/booking-unamapped-room', [App\Http\Controllers\ChannexController::class, 'bookingUnamappedRoom'])->name('channex.webhook');
Route::post('/booking-unamapped-rate', [App\Http\Controllers\ChannexController::class, 'bookingUnamappedRate'])->name('channex.bookingUnamappedRate');
Route::post('/sync-warning', [App\Http\Controllers\ChannexController::class, 'syncWarning'])->name('channex.syncWarning');
Route::post('/new-message', [App\Http\Controllers\ChannexController::class, 'newMessage'])->name('channex.newMessage');
Route::post('/new-review', [App\Http\Controllers\ChannexController::class, 'newReview'])->name('channex.newReview');
Route::post('/alteration-request', [App\Http\Controllers\ChannexController::class, 'alterationRequest'])->name('channex.alterationRequest');
Route::post('/airbnb-inquiry', [App\Http\Controllers\ChannexController::class, 'airbnbInquiry'])->name('channex.airbnbInquiry');
Route::post('/disconnect-channel', [App\Http\Controllers\ChannexController::class, 'disconnectChannel'])->name('channex.disconnectChannel');
Route::post('/disconnect-listing', [App\Http\Controllers\ChannexController::class, 'disconnectListing'])->name('channex.disconnectListing');
Route::post('/rate-error', [App\Http\Controllers\ChannexController::class, 'rateError'])->name('channex.rateError');
Route::post('/accepted-reservation', [App\Http\Controllers\ChannexController::class, 'acceptedReservation'])->name('channex.acceptedReservation');
Route::post('/decline-reservation', [App\Http\Controllers\ChannexController::class, 'declineReservation'])->name('channex.declineReservation');


Route::get('/templates', [WhatsappTemplateController::class, 'index'])->name('templates.index');
Route::get('/templates/create', [WhatsappTemplateController::class, 'create'])->name('templates.create');
Route::post('/templates', [WhatsappTemplateController::class, 'store'])->name('templates.store');
Route::get('/templates/sync', [WhatsappTemplateController::class, 'sync'])->name('templates.sync');
Route::get('/templates/{template}/status', [WhatsappTemplateController::class, 'checkStatus'])->name('templates.checkStatus');
Route::get('/templates/{template}', [WhatsappTemplateController::class, 'show'])->name('templates.show');
Route::get('/templates/{template}/edit', [WhatsappTemplateController::class, 'edit'])->name('templates.edit');
Route::put('/templates/{template}', [WhatsappTemplateController::class, 'update'])->name('templates.update');

// Rutas para alertas
Route::middleware(['auth'])->group(function () {
    Route::get('/alerts/unread', [App\Http\Controllers\AlertController::class, 'getUnreadAlerts'])->name('alerts.unread');
    Route::post('/alerts/mark-read', [App\Http\Controllers\AlertController::class, 'markAsRead'])->name('alerts.mark-read');
    Route::post('/alerts/mark-all-read', [App\Http\Controllers\AlertController::class, 'markAllAsRead'])->name('alerts.mark-all-read');
    Route::delete('/alerts/{id}', [App\Http\Controllers\AlertController::class, 'destroy'])->name('alerts.destroy');
    
    // Rutas solo para administradores
    Route::middleware(['auth', 'role:ADMIN'])->group(function () {
        Route::post('/alerts/create', [App\Http\Controllers\AlertController::class, 'create'])->name('alerts.create');
    });
});

// Rutas de API para ARI
Route::middleware(['auth'])->group(function () {
    Route::get('/api/properties/{propertyId}/room-types', function($propertyId) {
        // Usar la misma ruta que usa el sistema ARI existente
        return redirect("/channex/ari/room-types/{$propertyId}");
    });
    
    Route::get('/api/properties/{propertyId}/room-types/{roomTypeId}/rate-plans', function($propertyId, $roomTypeId) {
        // Usar la misma ruta que usa el sistema ARI existente
        return redirect("/channex/rate-plans/{$propertyId}/{$roomTypeId}");
    });
});
