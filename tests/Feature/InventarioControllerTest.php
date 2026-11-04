<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\MovimientoStock;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InventarioControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_administrator_can_view_stock_and_register_an_audited_entry(): void
    {
        [$administrator] = $this->createUsers();
        $product = $this->createProduct('INV-001', 4, 2);

        $this->actingAs($administrator)
            ->get(route('inventario.index'))
            ->assertOk()
            ->assertSee('Control de Inventario')
            ->assertSee('INV-001')
            ->assertSee('Existencias')
            ->assertSee('Historial de movimientos');

        $this->actingAs($administrator)
            ->post(route('inventario.movimientos.store', $product), [
                'tipo' => 'entrada',
                'cantidad' => 6,
                'motivo' => 'Reposición de mercadería',
                'referencia' => 'GUIA-101',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Movimiento de inventario registrado correctamente.');

        $this->assertSame(10, $product->refresh()->stock);
        $this->assertDatabaseHas('tmovimientosstock', [
            'id_producto' => $product->id_producto,
            'id_usuario' => $administrator->id_usuario,
            'tipo' => 'entrada',
            'cantidad' => 6,
            'stock_resultante' => 10,
            'motivo' => 'Reposición de mercadería',
            'referencia' => 'GUIA-101',
        ]);
    }

    public function test_warehouse_user_can_register_an_adjustment_to_the_counted_stock(): void
    {
        [, , $warehouseUser] = $this->createUsers();
        $product = $this->createProduct('INV-002', 8, 2);

        $this->actingAs($warehouseUser)
            ->post(route('inventario.movimientos.store', $product), [
                'tipo' => 'ajuste',
                'cantidad' => 5,
                'motivo' => 'Conteo físico de estante',
                'referencia' => 'CONTEO-2026-11',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(5, $product->refresh()->stock);
        $this->assertDatabaseHas('tmovimientosstock', [
            'id_producto' => $product->id_producto,
            'id_usuario' => $warehouseUser->id_usuario,
            'tipo' => 'ajuste',
            'cantidad' => 3,
            'stock_resultante' => 5,
            'motivo' => 'Ajuste de 8 a 5: Conteo físico de estante',
        ]);
    }

    public function test_rejects_an_exit_greater_than_available_stock_without_mutating_inventory(): void
    {
        [$administrator] = $this->createUsers();
        $product = $this->createProduct('INV-003', 3, 1);

        $this->actingAs($administrator)
            ->from(route('inventario.index'))
            ->post(route('inventario.movimientos.store', $product), [
                'tipo' => 'salida',
                'cantidad' => 4,
                'motivo' => 'Salida de almacén',
            ])
            ->assertRedirect(route('inventario.index'))
            ->assertSessionHasErrors([
                'cantidad' => 'Stock insuficiente. Disponible: 3 unidades.',
            ]);

        $this->assertSame(3, $product->refresh()->stock);
        $this->assertDatabaseCount('tmovimientosstock', 0);
    }

    public function test_rejects_an_adjustment_that_matches_current_stock(): void
    {
        [$administrator] = $this->createUsers();
        $product = $this->createProduct('INV-004', 3, 1);

        $this->actingAs($administrator)
            ->from(route('inventario.index'))
            ->post(route('inventario.movimientos.store', $product), [
                'tipo' => 'ajuste',
                'cantidad' => 3,
                'motivo' => 'Conteo físico',
            ])
            ->assertRedirect(route('inventario.index'))
            ->assertSessionHasErrors([
                'cantidad' => 'El conteo ingresado coincide con el stock actual; no se registró ningún ajuste.',
            ]);

        $this->assertSame(3, $product->refresh()->stock);
        $this->assertDatabaseCount('tmovimientosstock', 0);
    }

    public function test_filters_products_and_movement_history(): void
    {
        [$administrator] = $this->createUsers();
        $lowStockProduct = $this->createProduct('INV-008', 1, 2);
        $this->createProduct('INV-009', 8, 2);
        MovimientoStock::create([
            'id_producto' => $lowStockProduct->id_producto,
            'id_usuario' => $administrator->id_usuario,
            'tipo' => 'entrada',
            'cantidad' => 1,
            'stock_resultante' => 1,
            'motivo' => 'Reposición de prueba',
        ]);

        $this->actingAs($administrator)
            ->get(route('inventario.index', [
                'q' => 'INV-008',
                'stock' => 'bajo',
                'producto_historial' => 'INV-008',
                'tipo_movimiento' => 'entrada',
            ]))
            ->assertOk()
            ->assertSee('INV-008')
            ->assertSee('Reposición de prueba')
            ->assertViewHas('products', fn ($products): bool => $products->count() === 1
                && $products->first()->codigo === 'INV-008');
    }

    public function test_seller_can_view_inventory_but_cannot_change_stock(): void
    {
        [, $seller] = $this->createUsers();
        $product = $this->createProduct('INV-005', 3, 1);

        $this->actingAs($seller)
            ->get(route('inventario.index'))
            ->assertOk()
            ->assertSee('INV-005')
            ->assertDontSee('Registrar producto');

        $this->actingAs($seller)
            ->post(route('inventario.movimientos.store', $product), [
                'tipo' => 'entrada',
                'cantidad' => 5,
                'motivo' => 'Intento no autorizado',
            ])
            ->assertForbidden();

        $this->assertSame(3, $product->refresh()->stock);
        $this->assertDatabaseCount('tmovimientosstock', 0);
    }

    public function test_guest_is_redirected_to_login_from_inventory(): void
    {
        $this->get(route('inventario.index'))
            ->assertRedirect(route('login'));
    }

    public function test_product_creation_records_initial_stock_as_an_inventory_entry(): void
    {
        [$administrator] = $this->createUsers();
        $category = Categoria::create(['nombre' => 'Herramientas', 'activo' => true]);
        $brand = Marca::create(['nombre' => 'Marca de prueba', 'activo' => true]);

        $this->actingAs($administrator)
            ->from(route('inventario.index'))
            ->post(route('productos.store'), [
                'codigo' => 'INV-006',
                'nombre' => 'Llave ajustable',
                'id_categoria' => $category->id_categoria,
                'id_marca' => $brand->id_marca,
                'stock' => 7,
                'stock_minimo' => 2,
                'precio_compra' => 10,
                'precio_venta' => 15,
                'precio_alquiler_dia' => 0,
            ])
            ->assertRedirect(route('inventario.index'))
            ->assertSessionHas('success');

        $product = Producto::query()->where('codigo', 'INV-006')->firstOrFail();

        $this->assertSame(7, $product->stock);
        $this->assertSame(15.0, (float) $product->precio_venta_mayor);
        $this->assertSame(2, $product->cantidad_mayorista);
        $this->assertDatabaseHas('tmovimientosstock', [
            'id_producto' => $product->id_producto,
            'id_usuario' => $administrator->id_usuario,
            'tipo' => 'entrada',
            'cantidad' => 7,
            'stock_resultante' => 7,
            'motivo' => 'Stock inicial por creación de producto',
        ]);
    }

    public function test_seller_cannot_create_products_through_the_web_route(): void
    {
        [, $seller] = $this->createUsers();

        $this->actingAs($seller)
            ->post(route('productos.store'), [])
            ->assertForbidden();

        $this->assertDatabaseCount('tproductos', 0);
    }

    public function test_product_with_remaining_stock_cannot_be_deactivated(): void
    {
        [$administrator] = $this->createUsers();
        $product = $this->createProduct('INV-007', 3, 1);

        $this->actingAs($administrator)
            ->from(route('productos.index'))
            ->delete(route('productos.destroy', $product))
            ->assertRedirect(route('productos.index'))
            ->assertSessionHasErrors([
                'producto' => 'No se puede dar de baja un producto con existencias. Registra primero su salida o ajuste.',
            ]);

        $this->assertDatabaseHas('tproductos', [
            'id_producto' => $product->id_producto,
            'activo' => true,
        ]);
    }

    /**
     * @return array{Usuario, Usuario, Usuario}
     */
    private function createUsers(): array
    {
        $administratorRole = Rol::create(['nombre' => 'Administrador', 'activo' => true]);
        $sellerRole = Rol::create(['nombre' => 'Vendedor', 'activo' => true]);
        $warehouseRole = Rol::create(['nombre' => 'Almacenero', 'activo' => true]);

        return [
            $this->createUser($administratorRole->id_rol, 'admin'),
            $this->createUser($sellerRole->id_rol, 'vendedor'),
            $this->createUser($warehouseRole->id_rol, 'almacen'),
        ];
    }

    private function createUser(int $roleId, string $username): Usuario
    {
        return Usuario::create([
            'id_rol' => $roleId,
            'nombre' => ucfirst($username).' de prueba',
            'usuario' => $username.'-inventario',
            'email' => $username.'-inventario@example.test',
            'password' => Hash::make('secreto123'),
            'activo' => true,
        ]);
    }

    private function createProduct(string $code, int $stock, int $minimumStock): Producto
    {
        $category = Categoria::create(['nombre' => 'Categoría '.$code, 'activo' => true]);
        $brand = Marca::create(['nombre' => 'Marca '.$code, 'activo' => true]);

        return Producto::create([
            'codigo' => $code,
            'id_categoria' => $category->id_categoria,
            'id_marca' => $brand->id_marca,
            'nombre' => 'Producto '.$code,
            'stock' => $stock,
            'stock_minimo' => $minimumStock,
            'precio_compra' => 10,
            'precio_venta' => 15,
            'precio_venta_mayor' => 14,
            'cantidad_mayorista' => 2,
            'es_alquilable' => false,
            'precio_alquiler_dia' => 0,
            'activo' => true,
        ]);
    }
}
