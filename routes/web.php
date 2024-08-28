<?php

use App\Http\Controllers\CuentasContableController;
use Illuminate\Support\Facades\Route;

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

Auth::routes();

// Rutas de admin
Route::middleware(['auth', 'role:ADMIN'])->group(function () {
    Route::get('/admin', function () { return view('admin.dashboard');})->name('inicio');

    // Apartamentos
    Route::get('/apartamentos', [App\Http\Controllers\ApartamentosController::class, 'indexAdmin'])->name('apartamentos.admin.index');
    Route::get('/apartamentos/create', [App\Http\Controllers\ApartamentosController::class, 'createAdmin'])->name('apartamentos.admin.create');
    Route::get('/apartamentos/{id}/edit', [App\Http\Controllers\ApartamentosController::class, 'editAdmin'])->name('apartamentos.admin.edit');
    Route::post('/apartamentos/store', [App\Http\Controllers\ApartamentosController::class, 'storeAdmin'])->name('apartamentos.admin.store');
    Route::post('/apartamentos/{id}/update', [App\Http\Controllers\ApartamentosController::class, 'updateAdmin'])->name('apartamentos.admin.update');

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
    Route::get('/get-reservas', [App\Http\Controllers\ReservasController::class, 'getReservas'])->name('reservas.get');

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
    Route::post('/gastos/{categoria}/destroy', [App\Http\Controllers\GastosController::class, 'destroy'])->name('admin.gastos.destroy');
    Route::get('/gastos/download/{id}', [App\Http\Controllers\GastosController::class, 'download'])->name('gastos.download');
   
    // Categoria de Ingresos
    Route::get('/categoria-ingresos', [App\Http\Controllers\CategoriaIngresosController::class, 'index'])->name('admin.categoriaIngresos.index');
    Route::get('/categoria-ingresos/create', [App\Http\Controllers\CategoriaGastosController::class, 'create'])->name('admin.categoriaIngresos.create');
    Route::post('/categoria-ingresos/store', [App\Http\Controllers\CategoriaGastosController::class, 'store'])->name('admin.categoriaIngresos.store');
    Route::get('/categoria-ingresos/{categoria}/edit', [App\Http\Controllers\CategoriaGastosController::class, 'edit'])->name('admin.categoriaIngresos.edit');
    Route::post('/categoria-ingresos/{categoria}/update', [App\Http\Controllers\CategoriaGastosController::class, 'update'])->name('admin.categoriaIngresos.update');
    Route::post('/categoria-ingresos/{categoria}/destroy', [App\Http\Controllers\CategoriaGastosController::class, 'destroy'])->name('admin.categoriaIngresos.destroy');
    
     // Ingresos
    Route::get('/ingresos', [App\Http\Controllers\IngresosController::class, 'index'])->name('admin.ingresos.index');
    Route::get('/ingresos/create', [App\Http\Controllers\IngresosController::class, 'create'])->name('admin.ingresos.create');
    Route::post('/ingresos/store', [App\Http\Controllers\IngresosController::class, 'store'])->name('admin.ingresos.store');
    Route::get('/ingresos/{categoria}/edit', [App\Http\Controllers\IngresosController::class, 'edit'])->name('admin.ingresos.edit');
    Route::post('/ingresos/{categoria}/update', [App\Http\Controllers\IngresosController::class, 'update'])->name('admin.ingresos.update');
    Route::post('/ingresos/{categoria}/destroy', [App\Http\Controllers\IngresosController::class, 'destroy'])->name('admin.ingresos.destroy');
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

    Route::post('/actualizar-prompt', [App\Http\Controllers\ConfiguracionesController::class, 'actualizarPrompt'])->name('configuracion.actualizarPrompt');
    Route::post('/add-emails', [App\Http\Controllers\ConfiguracionesController::class, 'addEmailNotificaciones'])->name('configuracion.emails.add');
    Route::post('/delete-emails/{id}', [App\Http\Controllers\ConfiguracionesController::class, 'deleteEmailNotificaciones'])->name('configuracion.emails.delete');
    Route::post('/update-emails/{id}', [App\Http\Controllers\ConfiguracionesController::class, 'updateEmailNotificaciones'])->name('configuracion.emails.update');

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
    

    // Más rutas que solo deben ser accesibles
});



// Vistas
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/test', [App\Http\Controllers\HomeController::class, 'test'])->name('home');
Route::get('/email', [App\Http\Controllers\EstadoController::class, 'index'])->name('email.index');
Route::post('/comprobacion-server', [App\Http\Controllers\EstadoController::class, 'comprobacionServer'])->name('comprobacionServer');





// Añadir Reserva
Route::post('/agregar-reserva', [App\Http\Controllers\ReservasController::class, 'agregarReserva'])->name('reservas.agregarReserva');

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

// Fotos
Route::get('/fotos-dormitorio/{id}', [App\Http\Controllers\PhotoController::class, 'indexDormitorio'])->name('fotos.dormitorio');
Route::post('/actualizar-fotos-dormitorio/{id}', [App\Http\Controllers\PhotoController::class, 'actualizarDormitorio'])->name('actualizar.fotos.dormitorio');
// Route::post('/fotos-dormitorio-store/{id}', [App\Http\Controllers\PhotoController::class, 'dormitorioStore'])->name('fotos.dormitorioStore');
Route::get('/fotos-salon/{id}', [App\Http\Controllers\PhotoController::class, 'indexSalon'])->name('fotos.salon');
Route::post('/fotos-salon-store/{id}', [App\Http\Controllers\PhotoController::class, 'salonStore'])->name('fotos.salonStore');
Route::post('/actualizar-fotos-salin/{id}', [App\Http\Controllers\PhotoController::class, 'actualizarSalon'])->name('actualizar.fotos.salon');


Route::get('/fotos-cocina/{id}', [App\Http\Controllers\PhotoController::class, 'indexCocina'])->name('fotos.cocina');
Route::post('/fotos-cocina-store/{id}', [App\Http\Controllers\PhotoController::class, 'cocinaStore'])->name('fotos.cocinaStore');
Route::post('/actualizar-fotos-cocina/{id}', [App\Http\Controllers\PhotoController::class, 'actualizarCocina'])->name('actualizar.fotos.cocina');


Route::get('/fotos-banio/{id}', [App\Http\Controllers\PhotoController::class, 'indexBanio'])->name('fotos.banio');
Route::post('/fotos-banio-store/{id}', [App\Http\Controllers\PhotoController::class, 'banioStore'])->name('fotos.banioStore');
Route::post('/actualizar-fotos-banio/{id}', [App\Http\Controllers\PhotoController::class, 'actualizarBanio'])->name('actualizar.fotos.banio');

Route::post('/upload-dormitorio/{id}', [App\Http\Controllers\PhotoController::class, 'dormitorioStore'])->name('fotos.dormitorioStore');

// Obtener DNI
Route::get('/dni-user/{token}', [App\Http\Controllers\DNIController::class, 'index'])->name('dni.index');

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
