<?php

use App\Http\Controllers\Api\AlquilerController;
use App\Http\Controllers\Api\AuditoriaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CajaController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\CobranzaController;
use App\Http\Controllers\Api\CompraController;
use App\Http\Controllers\Api\ConfiguracionController;
use App\Http\Controllers\Api\DetalleCompraController;
use App\Http\Controllers\Api\DetalleVentaController;
use App\Http\Controllers\Api\GastoCajaController;
use App\Http\Controllers\Api\MarcaController;
use App\Http\Controllers\Api\MovimientoStockController;
use App\Http\Controllers\Api\NotificacionController;
use App\Http\Controllers\Api\PagoCobranzaController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\ProveedorController;
use App\Http\Controllers\Api\RolController;
use App\Http\Controllers\Api\SerieController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\VentaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Ferretería Jack Sparkys
|--------------------------------------------------------------------------
| Todas las rutas de la API están prefijadas con el nombre 'api.' para
| evitar cualquier colisión con las rutas web de la interfaz de usuario.
|
*/

Route::name('api.')->group(function () {
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('user', [AuthController::class, 'user'])->name('user');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::middleware('check.rol:admin,almacenero')->group(function (): void {
            Route::get('compras', [CompraController::class, 'index'])->name('compras.index');
            Route::post('compras', [CompraController::class, 'store'])->name('compras.store');
            Route::get('compras/{compra}', [CompraController::class, 'show'])->name('compras.show');
            Route::post('compras/{compra}/pagos', [CompraController::class, 'storePayment'])
                ->name('compras.pagos.store');
            Route::apiResource('detallecompras', DetalleCompraController::class)->only(['index', 'show']);
        });

        Route::middleware('check.rol:admin')->group(function (): void {
            Route::apiResource('roles', RolController::class);
            Route::apiResource('usuarios', UsuarioController::class);
            Route::apiResource('auditoria', AuditoriaController::class);
            Route::apiResource('configuracion', ConfiguracionController::class);
            Route::apiResource('series', SerieController::class);
            Route::apiResource('notificaciones', NotificacionController::class);
        });

        Route::middleware('check.rol:admin,almacenero,vendedor,cajero')->group(function (): void {
            Route::apiResource('productos', ProductoController::class)->only(['index', 'show']);
        });

        Route::middleware('check.rol:admin,almacenero')->group(function (): void {
            Route::apiResource('productos', ProductoController::class)->only(['store', 'update', 'destroy']);
            Route::post('productos/{producto}/movimientos', [MovimientoStockController::class, 'storeForProduct'])
                ->name('productos.movimientos.store');
            Route::apiResource('proveedores', ProveedorController::class);
        });

        Route::middleware('check.rol:admin,almacenero,vendedor')->group(function (): void {
            Route::apiResource('categorias', CategoriaController::class)->only(['index', 'show']);
            Route::apiResource('marcas', MarcaController::class)->only(['index', 'show']);
            Route::apiResource('movimientosstock', MovimientoStockController::class)->only(['index', 'show']);
        });

        Route::middleware('check.rol:admin')->group(function (): void {
            Route::apiResource('categorias', CategoriaController::class)->only(['store', 'update', 'destroy']);
            Route::apiResource('marcas', MarcaController::class)->only(['store', 'update', 'destroy']);
        });

        Route::middleware('check.rol:admin,vendedor,cajero,cobrador')->group(function (): void {
            Route::apiResource('clientes', ClienteController::class);
            Route::apiResource('caja', CajaController::class)->only(['index', 'show']);
            Route::apiResource('gastoscaja', GastoCajaController::class)->only(['index', 'show']);
        });

        Route::middleware('check.rol:admin,vendedor')->group(function (): void {
            Route::apiResource('ventas', VentaController::class);
            Route::apiResource('detalleventas', DetalleVentaController::class);
            Route::apiResource('alquileres', AlquilerController::class);
        });

        Route::middleware('check.rol:admin,vendedor,cobrador')->group(function (): void {
            Route::apiResource('cobranzas', CobranzaController::class);
            Route::apiResource('pagoscobranza', PagoCobranzaController::class);
        });
    });
});
