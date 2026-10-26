<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\AlquilerController;
use App\Http\Controllers\CobranzaController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AuthController;

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
Route::post('/ventas/anular/{id}', [VentaController::class, 'anular'])->name('ventas.anular');
Route::post('/ventas/alertar-almacen', [VentaController::class, 'alertarAlmacen'])->name('ventas.alertarAlmacen');
Route::post('/ventas/notificar-cliente', [VentaController::class, 'notificarCliente'])->name('ventas.notificarCliente');

// Módulo Alquileres de Equipos
Route::get('/alquileres', [AlquilerController::class, 'index'])->name('alquileres.index');
Route::post('/alquileres/store', [AlquilerController::class, 'store'])->name('alquileres.store');
Route::post('/alquileres/devolver/{id}', [AlquilerController::class, 'devolver'])->name('alquileres.devolver');

// Módulo Cobranzas y Moras
Route::get('/cobranzas', [CobranzaController::class, 'index'])->name('cobranzas.index');
Route::post('/cobranzas/pagar/{id}', [CobranzaController::class, 'pagar'])->name('cobranzas.pagar');
Route::post('/cobranzas/recuperar/{id}', [CobranzaController::class, 'recuperar'])->name('cobranzas.recuperar');

// Módulo Caja Chica
Route::get('/caja', [CajaController::class, 'index'])->name('caja.index');
Route::post('/caja/aperturar', [CajaController::class, 'aperturar'])->name('caja.aperturar');

// Módulos CRUD de Administración
Route::resource('productos', ProductoController::class)->only(['index', 'store', 'destroy']);
Route::resource('categorias', CategoriaController::class)->only(['index', 'store', 'destroy']);
Route::resource('marcas', MarcaController::class)->only(['index', 'store', 'destroy']);
Route::resource('clientes', ClienteController::class)->only(['index', 'store', 'destroy']);
Route::resource('proveedores', ProveedorController::class)->only(['index', 'store', 'destroy']);
Route::resource('usuarios', UsuarioController::class)->only(['index', 'store', 'destroy']);


