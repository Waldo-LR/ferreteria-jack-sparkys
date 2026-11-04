<?php

namespace Tests\Feature;

use App\Models\Caja;
use App\Models\Categoria;
use App\Models\Compra;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_resources_require_a_bearer_token_and_inactive_accounts_cannot_log_in(): void
    {
        $administrator = $this->createUser(1, 'admin-api');
        $inactiveUser = $this->createUser(2, 'inactivo-api', false);

        $this->getJson('/api/productos')->assertUnauthorized();
        $this->getJson('/api/compras')->assertUnauthorized();
        $this->getJson('/api/movimientosstock')->assertUnauthorized();
        $this->getJson('/api/usuarios')->assertUnauthorized();
        $this->postJson('/api/login', [
            'usuario' => $inactiveUser->usuario,
            'password' => 'secreto123',
        ])->assertUnauthorized();

        $this->assertDatabaseCount('personal_access_tokens', 0);

        $response = $this->postJson('/api/login', [
            'usuario' => $administrator->usuario,
            'password' => 'secreto123',
            'nombre_token' => 'Postman local',
        ])->assertCreated();

        $token = $response->json('data.access_token');
        $this->assertNotEmpty($token);
        $this->assertNotNull($response->json('data.expires_at'));

        $this->withToken($token)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.usuario', $administrator->usuario);

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->app['auth']->forgetGuards();

        $this->withToken($token)
            ->getJson('/api/user')
            ->assertUnauthorized();
    }

    public function test_invalid_credentials_do_not_issue_a_token(): void
    {
        $this->createUser(1, 'admin-credenciales');

        $this->postJson('/api/login', [
            'usuario' => 'admin-credenciales',
            'password' => 'incorrecta',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Las credenciales no son válidas o la cuenta está inactiva.');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_product_write_routes_are_not_exposed_to_sellers(): void
    {
        $seller = $this->createUser(2, 'vendedor-api');
        $token = $this->postJson('/api/login', [
            'usuario' => $seller->usuario,
            'password' => 'secreto123',
        ])->assertCreated()->json('data.access_token');

        $this->withToken($token)
            ->postJson('/api/productos', [])
            ->assertForbidden();
    }

    public function test_sellers_receive_forbidden_when_accessing_purchase_endpoints(): void
    {
        $seller = $this->createUser(2, 'vendedor-compras-api');
        $token = $this->postJson('/api/login', [
            'usuario' => $seller->usuario,
            'password' => 'secreto123',
        ])->assertCreated()->json('data.access_token');

        $this->withToken($token)
            ->getJson('/api/compras')
            ->assertForbidden();
    }

    public function test_postman_can_record_a_purchase_and_stock_movements_through_validated_api_routes(): void
    {
        $warehouseUser = $this->createUser(3, 'almacen-api');
        $token = $this->loginToken($warehouseUser);
        $provider = Proveedor::create([
            'razon_social' => 'Proveedor API',
            'ruc' => '20123456789',
            'activo' => true,
        ]);
        $category = Categoria::create(['nombre' => 'Herramientas API', 'activo' => true]);
        $brand = Marca::create(['nombre' => 'Marca API', 'activo' => true]);
        $product = Producto::create([
            'codigo' => 'API-001',
            'id_categoria' => $category->id_categoria,
            'id_marca' => $brand->id_marca,
            'nombre' => 'Producto API',
            'stock' => 3,
            'stock_minimo' => 1,
            'precio_compra' => '10.00',
            'precio_venta' => '20.00',
            'activo' => true,
        ]);

        $purchase = $this->withToken($token)
            ->postJson('/api/compras', [
                'id_proveedor' => $provider->id_proveedor,
                'tipo_comprobante' => 'Factura',
                'numero_comprobante' => 'F002-000001',
                'fecha_compra' => today()->toDateString(),
                'detalles' => [[
                    'id_producto' => $product->id_producto,
                    'cantidad' => 2,
                    'precio_unitario' => '15.00',
                ]],
                'monto_pagado' => '0.00',
            ])
            ->assertCreated()
            ->assertJsonPath('data.total', '30.00')
            ->assertJsonPath('data.saldo_pendiente', 30);

        $this->assertSame(5, $product->refresh()->stock);

        $compra = Compra::query()->firstOrFail();
        $caja = Caja::create([
            'id_usuario' => $warehouseUser->id_usuario,
            'tipo_caja' => Caja::TIPO_VENTAS,
            'tipo_caja_abierta' => Caja::TIPO_VENTAS,
            'fecha_apertura' => now(),
            'monto_inicial' => '100.00',
            'monto_final' => '100.00',
            'estado' => 'abierta',
        ]);

        $this->withToken($token)
            ->postJson("/api/compras/{$compra->id_compra}/pagos", [
                'monto' => '10.00',
                'medio_pago' => 'deposito',
            ])
            ->assertCreated()
            ->assertJsonPath('data.compra.estado_pago', 'parcial')
            ->assertJsonPath('data.compra.saldo_pendiente', 20);

        $this->assertSame('10.00', $caja->refresh()->total_egresos);
        $this->assertSame('100.00', $caja->monto_final);
        $this->assertDatabaseHas('tgastoscaja', [
            'id_caja' => $caja->id_caja,
            'id_compra' => $compra->id_compra,
            'tipo_movimiento' => 'egreso',
            'categoria' => 'Pago a proveedor',
            'monto' => '10.00',
            'medio_pago' => 'deposito',
        ]);

        $this->withToken($token)
            ->postJson("/api/productos/{$product->id_producto}/movimientos", [
                'tipo' => 'salida',
                'cantidad' => 6,
                'motivo' => 'Prueba de stock insuficiente',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cantidad');

        $this->assertSame(5, $product->refresh()->stock);

        $this->withToken($token)
            ->postJson("/api/productos/{$product->id_producto}/movimientos", [
                'tipo' => 'entrada',
                'cantidad' => 2,
                'motivo' => 'Entrada por API validada',
                'referencia' => 'POSTMAN-01',
            ])
            ->assertCreated()
            ->assertJsonPath('data.stock_resultante', 7);

        $this->assertSame(7, $product->refresh()->stock);
        $this->assertDatabaseHas('tmovimientosstock', [
            'id_producto' => $product->id_producto,
            'id_usuario' => $warehouseUser->id_usuario,
            'referencia' => 'POSTMAN-01',
            'stock_resultante' => 7,
        ]);
    }

    private function loginToken(Usuario $usuario): string
    {
        return $this->postJson('/api/login', [
            'usuario' => $usuario->usuario,
            'password' => 'secreto123',
        ])->assertCreated()->json('data.access_token');
    }

    private function createUser(int $roleId, string $username, bool $active = true): Usuario
    {
        while (Rol::query()->count() < $roleId) {
            $nextRoleId = Rol::query()->count() + 1;
            Rol::create(['nombre' => 'Rol '.$nextRoleId, 'activo' => true]);
        }

        return Usuario::create([
            'id_rol' => $roleId,
            'nombre' => ucfirst($username),
            'usuario' => $username,
            'email' => $username.'@example.test',
            'password' => Hash::make('secreto123'),
            'activo' => $active,
        ]);
    }
}
