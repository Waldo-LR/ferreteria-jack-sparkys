<?php

use App\Http\Controllers\AlquilerController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CobranzaController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

// Autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/quick-login/{role}', [AuthController::class, 'quickLogin'])->name('quick.login');
Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');

// Panel y Módulos Web del Sistema
Route::get('/', [DashboardController::class, 'index'])->name('home');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Módulo POS & Ventas
Route::get('/ventas/pos', [VentaController::class, 'pos'])->name('ventas.pos');
Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
Route::post('/ventas/store', [VentaController::class, 'store'])->name('ventas.store');
Route::get('/ventas/imprimir/{id}', [VentaController::class, 'imprimir'])->name('ventas.imprimir');
Route::get('/ventas/{id}/pdf', [VentaController::class, 'descargarPdf'])->name('ventas.pdf');
Route::post('/ventas/anular/{id}', [VentaController::class, 'anular'])->name('ventas.anular');
Route::post('/ventas/alertar-almacen', [VentaController::class, 'alertarAlmacen'])->name('ventas.alertarAlmacen');
Route::post('/ventas/notificar-cliente', [VentaController::class, 'notificarCliente'])->name('ventas.notificarCliente');

// Módulo Alquileres de Equipos
Route::middleware(['auth', 'check.rol:admin,vendedor,cobrador'])->group(function (): void {
    Route::redirect('/alquileres', '/alquileres/historial')->name('alquileres.home');
    Route::get('/alquileres/registrar', [AlquilerController::class, 'create'])->name('alquileres.create');
    Route::get('/alquileres/historial', [AlquilerController::class, 'index'])->name('alquileres.index');
    Route::post('/alquileres/store', [AlquilerController::class, 'store'])->name('alquileres.store');
    Route::post('/alquileres/{alquiler}/devolver', [AlquilerController::class, 'devolver'])->name('alquileres.devolver');

    // Módulo Cobranzas y Moras
    Route::get('/cobranzas', [CobranzaController::class, 'index'])->name('cobranzas.index');
    Route::post('/cobranzas/pagar/{cobranza}', [CobranzaController::class, 'pagar'])->name('cobranzas.pagar');
    Route::post('/cobranzas/recuperar/{alquiler}', [CobranzaController::class, 'recuperar'])->name('cobranzas.recuperar');
});

// Control de Caja
Route::middleware('auth')->group(function () {
    Route::get('/caja', [CajaController::class, 'index'])->name('caja.index');
    Route::post('/caja/aperturar', [CajaController::class, 'aperturar'])->name('caja.aperturar');
    Route::post('/caja/{caja}/movimientos', [CajaController::class, 'registrarMovimiento'])->name('caja.movimientos.store');
    Route::post('/caja/{caja}/cerrar', [CajaController::class, 'cerrar'])->name('caja.cerrar');
    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');
    Route::post('/inventario/{producto}/movimientos', [InventarioController::class, 'storeMovement'])->name('inventario.movimientos.store');
    Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/pdf', [ReportesController::class, 'pdf'])->name('reportes.pdf');
});

Route::middleware(['auth', 'check.rol:admin,almacenero'])->group(function () {
    Route::get('/compras', [CompraController::class, 'index'])->name('compras.index');
    Route::post('/compras', [CompraController::class, 'store'])->name('compras.store');
    Route::post('/compras/{compra}/pagos', [CompraController::class, 'storePayment'])->name('compras.pagos.store');
});

// Módulos CRUD de Administración
Route::middleware('auth')->get('/productos/{producto}/imagen', [ProductoController::class, 'image'])->name('productos.image');
Route::middleware('auth')->resource('productos', ProductoController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
Route::resource('categorias', CategoriaController::class)->only(['index', 'store', 'destroy']);
Route::resource('marcas', MarcaController::class)->only(['index', 'store', 'destroy']);
Route::middleware('auth')->resource('clientes', ClienteController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
Route::resource('proveedores', ProveedorController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
Route::middleware('auth')->resource('usuarios', UsuarioController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
Route::middleware('auth')->group(function () {
    Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
    Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');
});
