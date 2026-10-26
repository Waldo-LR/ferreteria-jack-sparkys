<?php

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
    Route::apiResource('roles', App\Http\Controllers\Api\RolController::class);
    Route::apiResource('troles', App\Http\Controllers\Api\trolesController::class);
    Route::apiResource('usuarios', App\Http\Controllers\Api\UsuarioController::class);
    Route::apiResource('tusuarios', App\Http\Controllers\Api\tusuariosController::class);
    Route::apiResource('categorias', App\Http\Controllers\Api\CategoriaController::class);
    Route::apiResource('tcategorias', App\Http\Controllers\Api\tcategoriasController::class);
    Route::apiResource('marcas', App\Http\Controllers\Api\MarcaController::class);
    Route::apiResource('tmarcas', App\Http\Controllers\Api\tmarcasController::class);
    Route::apiResource('clientes', App\Http\Controllers\Api\ClienteController::class);
    Route::apiResource('tclientes', App\Http\Controllers\Api\tclientesController::class);
    Route::apiResource('proveedores', App\Http\Controllers\Api\ProveedorController::class);
    Route::apiResource('tproveedores', App\Http\Controllers\Api\tproveedoresController::class);
    Route::apiResource('productos', App\Http\Controllers\Api\ProductoController::class);
    Route::apiResource('tproductos', App\Http\Controllers\Api\tproductosController::class);
    Route::apiResource('caja', App\Http\Controllers\Api\CajaController::class);
    Route::apiResource('tcaja', App\Http\Controllers\Api\tcajaController::class);
    Route::apiResource('gastoscaja', App\Http\Controllers\Api\GastoCajaController::class);
    Route::apiResource('tgastoscaja', App\Http\Controllers\Api\tgastoscajaController::class);
    Route::apiResource('compras', App\Http\Controllers\Api\CompraController::class);
    Route::apiResource('tcompras', App\Http\Controllers\Api\tcomprasController::class);
    Route::apiResource('detallecompras', App\Http\Controllers\Api\DetalleCompraController::class);
    Route::apiResource('tdetallecompras', App\Http\Controllers\Api\tdetallecomprasController::class);
    Route::apiResource('ventas', App\Http\Controllers\Api\VentaController::class);
    Route::apiResource('tventas', App\Http\Controllers\Api\tventasController::class);
    Route::apiResource('detalleventas', App\Http\Controllers\Api\DetalleVentaController::class);
    Route::apiResource('tdetalleventas', App\Http\Controllers\Api\tdetalleventasController::class);
    Route::apiResource('alquileres', App\Http\Controllers\Api\AlquilerController::class);
    Route::apiResource('talquileres', App\Http\Controllers\Api\talquileresController::class);
    Route::apiResource('cobranzas', App\Http\Controllers\Api\CobranzaController::class);
    Route::apiResource('tcobranzas', App\Http\Controllers\Api\tcobranzasController::class);
    Route::apiResource('pagoscobranza', App\Http\Controllers\Api\PagoCobranzaController::class);
    Route::apiResource('tpagoscobranza', App\Http\Controllers\Api\tpagoscobranzaController::class);
    Route::apiResource('movimientosstock', App\Http\Controllers\Api\MovimientoStockController::class);
    Route::apiResource('tmovimientosstock', App\Http\Controllers\Api\tmovimientosstockController::class);
    Route::apiResource('notificaciones', App\Http\Controllers\Api\NotificacionController::class);
    Route::apiResource('tnotificaciones', App\Http\Controllers\Api\tnotificacionesController::class);
    Route::apiResource('auditoria', App\Http\Controllers\Api\AuditoriaController::class);
    Route::apiResource('tauditoria', App\Http\Controllers\Api\tauditoriaController::class);
    Route::apiResource('configuracion', App\Http\Controllers\Api\ConfiguracionController::class);
    Route::apiResource('tconfiguracion', App\Http\Controllers\Api\tconfiguracionController::class);
    Route::apiResource('series', App\Http\Controllers\Api\SerieController::class);
    Route::apiResource('tseries', App\Http\Controllers\Api\tseriesController::class);
});

