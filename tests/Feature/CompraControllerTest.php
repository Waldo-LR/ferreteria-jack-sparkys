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

class CompraControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_receiving_a_purchase_updates_stock_average_cost_and_partial_cash_payment(): void
    {
        $administrator = $this->createUser(1, 'admin-compra');
        $provider = $this->createProvider();
        $product = $this->createProduct(10, '10.00');
        $caja = $this->openSalesCash($administrator, '100.00');

        $this->actingAs($administrator)
            ->post(route('compras.store'), $this->purchaseData($provider, $product, 5, '20.00', '30.00'))
            ->assertRedirect(route('compras.index'))
            ->assertSessionHas('success');

        $compra = Compra::query()->withSum('pagos as monto_pagado', 'monto')->firstOrFail();

        $this->assertSame(15, $product->refresh()->stock);
        $this->assertSame(13.33, (float) $product->precio_compra);
        $this->assertSame('100.00', $compra->total);
        $this->assertSame(30.0, $compra->monto_pagado);
        $this->assertSame(70.0, $compra->saldo_pendiente);
        $this->assertSame('parcial', $compra->estado_pago);
        $this->assertDatabaseHas('tmovimientosstock', [
            'id_producto' => $product->id_producto,
            'id_usuario' => $administrator->id_usuario,
            'tipo' => 'entrada',
            'cantidad' => 5,
            'stock_resultante' => 15,
            'referencia' => 'COMPRA-'.$compra->id_compra,
        ]);
        $this->assertDatabaseCount('tmovimientosstock', 1);
        $this->assertDatabaseHas('tgastoscaja', [
            'id_caja' => $caja->id_caja,
            'id_compra' => $compra->id_compra,
            'tipo_movimiento' => 'egreso',
            'categoria' => 'Pago a proveedor',
            'monto' => '30.00',
            'medio_pago' => 'efectivo',
        ]);
        $this->assertSame('70.00', $caja->refresh()->monto_final);

        $this->actingAs($administrator)
            ->get(route('compras.index'))
            ->assertOk()
            ->assertSee('Compras y Proveedores')
            ->assertSee('Saldo pendiente')
            ->assertSee('F001-000001');
    }

    public function test_paid_purchase_without_open_sales_cash_rolls_back_inventory_and_purchase(): void
    {
        $administrator = $this->createUser(1, 'admin-sin-caja');
        $provider = $this->createProvider();
        $product = $this->createProduct(4, '8.00');

        $this->actingAs($administrator)
            ->from(route('compras.index'))
            ->post(route('compras.store'), $this->purchaseData($provider, $product, 2, '12.00', '10.00'))
            ->assertRedirect(route('compras.index'))
            ->assertSessionHasErrors([
                'caja' => 'Abre la Caja de Venta de Productos para registrar pagos a proveedores.',
            ]);

        $this->assertDatabaseCount('tcompras', 0);
        $this->assertDatabaseCount('tdetallecompras', 0);
        $this->assertDatabaseCount('tmovimientosstock', 0);
        $this->assertSame(4, $product->refresh()->stock);
        $this->assertSame(8.0, (float) $product->precio_compra);
    }

    public function test_registers_installments_and_rejects_a_payment_greater_than_the_remaining_balance(): void
    {
        $administrator = $this->createUser(1, 'admin-cuotas');
        $provider = $this->createProvider();
        $product = $this->createProduct(0, '0.00');
        $caja = $this->openSalesCash($administrator, '100.00');

        $this->actingAs($administrator)
            ->post(route('compras.store'), $this->purchaseData($provider, $product, 10, '10.00', '0.00'))
            ->assertRedirect(route('compras.index'))
            ->assertSessionHas('success');

        $compra = Compra::query()->firstOrFail();
        $this->actingAs($administrator)
            ->post(route('compras.pagos.store', $compra), [
                'monto' => '60.00',
                'medio_pago' => 'deposito',
            ])
            ->assertRedirect(route('compras.index'))
            ->assertSessionHas('success');

        $this->actingAs($administrator)
            ->from(route('compras.index'))
            ->post(route('compras.pagos.store', $compra), [
                'monto' => '50.00',
                'medio_pago' => 'efectivo',
            ])
            ->assertRedirect(route('compras.index'))
            ->assertSessionHasErrors([
                'monto' => 'El pago debe ser mayor que cero y no puede superar el saldo pendiente.',
            ]);

        $this->assertSame('60.00', $caja->refresh()->total_egresos);
        $this->assertSame('100.00', $caja->monto_final);
        $this->assertDatabaseCount('tgastoscaja', 1);
        $this->assertSame(40.0, $compra->fresh()->saldo_pendiente);
    }

    public function test_seller_cannot_view_or_register_purchases(): void
    {
        $seller = $this->createUser(2, 'vendedor-compra');

        $this->actingAs($seller)
            ->get(route('compras.index'))
            ->assertForbidden();

        $this->actingAs($seller)
            ->post(route('compras.store'), [])
            ->assertForbidden();

        $this->assertDatabaseCount('tcompras', 0);
    }

    public function test_historical_purchase_is_unreconciled_and_cannot_be_paid_as_a_new_purchase(): void
    {
        $administrator = $this->createUser(1, 'admin-compra-historica');
        $provider = $this->createProvider();
        $compra = Compra::create([
            'id_proveedor' => $provider->id_proveedor,
            'id_usuario' => $administrator->id_usuario,
            'tipo_comprobante' => 'Factura',
            'numero_comprobante' => 'F001-HISTORICA',
            'fecha_compra' => today()->toDateString(),
            'total' => '100.00',
            'estado' => 'registrada',
        ]);

        $this->actingAs($administrator)
            ->get(route('compras.index'))
            ->assertOk()
            ->assertSee('Registro anterior')
            ->assertDontSee('Registrar pago');

        $this->assertSame(0.0, $compra->fresh()->saldo_pendiente);
        $this->assertSame('sin_conciliar', $compra->fresh()->estado_pago);

        $this->actingAs($administrator)
            ->from(route('compras.index'))
            ->post(route('compras.pagos.store', $compra), [
                'monto' => '10.00',
                'medio_pago' => 'efectivo',
            ])
            ->assertRedirect(route('compras.index'))
            ->assertSessionHasErrors([
                'compra' => 'Solo se pueden registrar pagos para compras recibidas por el módulo de Inventario.',
            ]);

        $this->assertDatabaseCount('tgastoscaja', 0);
    }

    private function createUser(int $roleId, string $username): Usuario
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
            'activo' => true,
        ]);
    }

    private function createProvider(): Proveedor
    {
        return Proveedor::create([
            'razon_social' => 'Proveedor de prueba',
            'ruc' => '20123456789',
            'activo' => true,
        ]);
    }

    private function createProduct(int $stock, string $cost): Producto
    {
        $category = Categoria::create(['nombre' => 'Herramientas', 'activo' => true]);
        $brand = Marca::create(['nombre' => 'Marca de prueba', 'activo' => true]);

        return Producto::create([
            'codigo' => 'COMPRA-001',
            'id_categoria' => $category->id_categoria,
            'id_marca' => $brand->id_marca,
            'nombre' => 'Taladro de prueba',
            'stock' => $stock,
            'stock_minimo' => 1,
            'precio_compra' => $cost,
            'precio_venta' => '30.00',
            'precio_venta_mayor' => '28.00',
            'cantidad_mayorista' => 2,
            'activo' => true,
        ]);
    }

    private function openSalesCash(Usuario $usuario, string $balance): Caja
    {
        return Caja::create([
            'id_usuario' => $usuario->id_usuario,
            'tipo_caja' => Caja::TIPO_VENTAS,
            'tipo_caja_abierta' => Caja::TIPO_VENTAS,
            'fecha_apertura' => now(),
            'monto_inicial' => $balance,
            'monto_final' => $balance,
            'estado' => 'abierta',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function purchaseData(
        Proveedor $provider,
        Producto $product,
        int $quantity,
        string $unitCost,
        string $initialPayment
    ): array {
        return [
            'id_proveedor' => $provider->id_proveedor,
            'tipo_comprobante' => 'Factura',
            'numero_comprobante' => 'F001-000001',
            'fecha_compra' => today()->toDateString(),
            'detalles' => [[
                'id_producto' => $product->id_producto,
                'cantidad' => $quantity,
                'precio_unitario' => $unitCost,
            ]],
            'monto_pagado' => $initialPayment,
            'medio_pago' => (float) $initialPayment > 0 ? 'efectivo' : null,
        ];
    }
}
