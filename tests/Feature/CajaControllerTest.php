<?php

namespace Tests\Feature;

use App\Models\Alquiler;
use App\Models\Caja;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Cobranza;
use App\Models\Marca;
use App\Models\MovimientoCaja;
use App\Models\PagoCobranza;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CajaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_registers_open_independently_and_rejects_a_second_opening_of_the_same_type(): void
    {
        $this->actingAs($this->createAdministrator());

        $ventas = $this->openBox(Caja::TIPO_VENTAS, 100);
        $alquileres = $this->openBox(Caja::TIPO_ALQUILERES, 50);

        $this->from(route('caja.index'))
            ->post(route('caja.aperturar'), [
                'tipo_caja' => Caja::TIPO_VENTAS,
                'monto_inicial' => 25,
            ])
            ->assertRedirect(route('caja.index'))
            ->assertSessionHasErrors([
                'tipo_caja' => 'Ya existe una caja abierta para esta operación.',
            ]);

        $this->assertSame(Caja::TIPO_VENTAS, $ventas->refresh()->tipo_caja_abierta);
        $this->assertSame(Caja::TIPO_ALQUILERES, $alquileres->refresh()->tipo_caja_abierta);
        $this->assertDatabaseCount('tcaja', 2);

        $response = $this->get(route('caja.index'))
            ->assertOk()
            ->assertSee('Control de Caja')
            ->assertSee('Caja de Venta de Productos')
            ->assertSee('Caja de Alquileres/Cobranzas')
            ->assertSee('role="tablist"', false);

        $this->assertMatchesRegularExpression(
            '/id="tab-ventas"[^>]*aria-selected="true"/s',
            $response->getContent()
        );
        $this->assertMatchesRegularExpression(
            '/id="panel-alquileres"[^>]* hidden/s',
            $response->getContent()
        );
    }

    public function test_forbids_cash_register_access_to_roles_without_cash_permissions(): void
    {
        Rol::create(['nombre' => 'Administrador', 'activo' => true]);
        Rol::create(['nombre' => 'Vendedor', 'activo' => true]);
        $warehouseRole = Rol::create(['nombre' => 'Almacenero', 'activo' => true]);
        $warehouseUser = Usuario::create([
            'id_rol' => $warehouseRole->id_rol,
            'nombre' => 'Almacenero de prueba',
            'usuario' => 'almacen-prueba',
            'email' => 'almacen@example.test',
            'password' => Hash::make('secreto123'),
            'activo' => true,
        ]);
        $this->actingAs($warehouseUser);

        $this->get(route('caja.index'))->assertForbidden();
    }

    public function test_cash_and_digital_movements_update_the_correct_totals(): void
    {
        $this->actingAs($this->createAdministrator());
        $caja = $this->openBox(Caja::TIPO_VENTAS, 100);

        $this->recordMovement($caja, 'ingreso', 'Aporte de fondos', 'efectivo', 40, 'Fondo adicional');
        $this->recordMovement($caja, 'ingreso', 'Otros ingresos', 'yape', 30, 'Ingreso por Yape');
        $this->recordMovement($caja, 'egreso', 'Servicios', 'efectivo', 20, 'Pago de servicio');
        $this->recordMovement($caja, 'egreso', 'Otros egresos', 'deposito', 10, 'Pago por depósito');

        $caja->refresh();

        $this->assertSame('70.00', $caja->total_ingresos);
        $this->assertSame('40.00', $caja->ingresos_efectivo);
        $this->assertSame('30.00', $caja->total_egresos);
        $this->assertSame('20.00', $caja->egresos_efectivo);
        $this->assertSame('120.00', $caja->monto_final);
        $this->assertDatabaseCount('tgastoscaja', 4);
    }

    public function test_rejects_a_cash_expense_greater_than_the_available_balance(): void
    {
        $this->actingAs($this->createAdministrator());
        $caja = $this->openBox(Caja::TIPO_VENTAS, 20);

        $this->from(route('caja.index'))
            ->post(route('caja.movimientos.store', $caja), [
                'tipo_movimiento' => 'egreso',
                'categoria' => 'Otros egresos',
                'medio_pago' => 'efectivo',
                'monto' => 20.01,
                'detalle' => 'Intento de egreso superior al saldo',
            ])
            ->assertRedirect(route('caja.index'))
            ->assertSessionHasErrors([
                'monto' => 'El egreso en efectivo no puede superar el efectivo disponible en caja.',
            ]);

        $this->assertDatabaseCount('tgastoscaja', 0);
        $this->assertSame('20.00', $caja->refresh()->monto_final);
        $this->assertSame('0.00', $caja->total_egresos);
    }

    public function test_requires_an_observation_for_a_closing_difference_and_records_the_cash_count(): void
    {
        $user = $this->createAdministrator();
        $this->actingAs($user);
        $caja = $this->openBox(Caja::TIPO_VENTAS, 100);

        $this->from(route('caja.index'))
            ->post(route('caja.cerrar', $caja), ['monto_contado' => 90])
            ->assertRedirect(route('caja.index'))
            ->assertSessionHasErrors([
                'observaciones' => 'Indica el motivo del sobrante o faltante de efectivo.',
            ]);

        $this->assertSame('abierta', $caja->refresh()->estado);

        $this->post(route('caja.cerrar', $caja), [
            'monto_contado' => 90,
            'observaciones' => 'Faltante revisado en arqueo',
        ])->assertRedirect(route('caja.index'));

        $caja->refresh();

        $this->assertSame('cerrada', $caja->estado);
        $this->assertSame('90.00', $caja->monto_contado);
        $this->assertSame('-10.00', $caja->diferencia_cierre);
        $this->assertSame($user->id_usuario, $caja->id_usuario_cierre);
        $this->assertNull($caja->tipo_caja_abierta);
    }

    public function test_sales_rentals_and_mora_payments_are_assigned_to_their_own_register(): void
    {
        $user = $this->createAdministrator();
        $this->actingAs($user);
        $cajaVentas = $this->openBox(Caja::TIPO_VENTAS, 100);
        $cajaAlquileres = $this->openBox(Caja::TIPO_ALQUILERES, 50);

        $this->get(route('ventas.pos'))
            ->assertOk()
            ->assertSee('Guardar Venta & Generar Comprobante', false);
        $this->get(route('alquileres.index'))
            ->assertOk()
            ->assertSee('Historial de alquileres');
        $this->get(route('alquileres.create'))
            ->assertOk()
            ->assertSee('Caja de Alquileres abierta');
        $this->get(route('cobranzas.index'))
            ->assertOk();

        [$cliente, $producto] = $this->createCustomerAndRentableProduct();

        $this->post(route('ventas.store'), [
            'id_cliente' => $cliente->id_cliente,
            'tipo_comprobante' => 'boleta',
            'medio_pago' => 'yape',
            'productos' => [[
                'id_producto' => $producto->id_producto,
                'cantidad' => 1,
                'precio_unitario' => 40,
            ]],
        ])->assertRedirect(route('ventas.imprimir', 1));

        $this->post(route('alquileres.store'), [
            'id_cliente' => $cliente->id_cliente,
            'id_producto' => $producto->id_producto,
            'dias_pactados' => 2,
            'fecha_entrega' => now()->toDateString(),
            'monto_pagado' => '50.00',
            'medio_pago_alquiler' => 'efectivo',
            'monto_garantia' => '10.00',
            'medio_pago_garantia' => 'efectivo',
            'ubicacion_herramienta' => 'Obra de prueba',
        ])->assertRedirect(route('alquileres.index'));

        $alquiler = Alquiler::firstOrFail();
        $cobranza = Cobranza::create([
            'id_alquiler' => $alquiler->id_alquiler,
            'id_usuario' => $user->id_usuario,
            'dias_atraso' => 2,
            'monto_mora' => 20,
            'monto_pagado' => 0,
            'estado' => 'pendiente',
        ]);

        $this->post(route('cobranzas.pagar', $cobranza->id_cobranza), [
            'monto' => 5,
            'medio_pago' => 'yape',
        ])->assertRedirect();

        $this->from(route('cobranzas.index'))
            ->post(route('cobranzas.pagar', $cobranza->id_cobranza), [
                'monto' => 16,
                'medio_pago' => 'efectivo',
            ])
            ->assertSessionHasErrors([
                'monto' => 'El monto supera el saldo pendiente de esta cobranza o ya fue cancelada.',
            ]);

        $this->assertSame($cajaVentas->id_caja, Venta::firstOrFail()->id_caja);
        $this->assertSame($cajaAlquileres->id_caja, $alquiler->refresh()->id_caja);
        $this->assertSame($cajaAlquileres->id_caja, PagoCobranza::firstOrFail()->id_caja);
        $this->assertSame('40.00', $cajaVentas->refresh()->total_ingresos);
        $this->assertSame('0.00', $cajaVentas->ingresos_efectivo);
        $this->assertSame('65.00', $cajaAlquileres->refresh()->total_ingresos);
        $this->assertSame('60.00', $cajaAlquileres->ingresos_efectivo);
        $this->assertSame('110.00', $cajaAlquileres->monto_final);
        $this->assertSame('5.00', $cobranza->refresh()->monto_pagado);
        $this->assertDatabaseHas('tgastoscaja', [
            'id_caja' => $cajaAlquileres->id_caja,
            'id_alquiler' => $alquiler->id_alquiler,
            'categoria' => 'Garantía de alquiler',
            'tipo_movimiento' => 'ingreso',
            'monto' => '10.00',
        ]);
    }

    public function test_digital_sale_refund_is_recorded_as_an_expense_without_reducing_physical_cash(): void
    {
        $this->actingAs($this->createAdministrator());
        $cajaVentas = $this->openBox(Caja::TIPO_VENTAS, 100);
        [$cliente, $producto] = $this->createCustomerAndRentableProduct();

        $this->post(route('ventas.store'), [
            'id_cliente' => $cliente->id_cliente,
            'tipo_comprobante' => 'boleta',
            'medio_pago' => 'yape',
            'productos' => [[
                'id_producto' => $producto->id_producto,
                'cantidad' => 1,
                'precio_unitario' => 40,
            ]],
        ])->assertRedirect(route('ventas.imprimir', 1));

        $this->from(route('ventas.index'))
            ->post(route('ventas.anular', 1), ['motivo' => 'Devolución al cliente'])
            ->assertRedirect(route('ventas.index'))
            ->assertSessionHas('success');

        $movimiento = MovimientoCaja::firstOrFail();

        $this->assertSame('anulada', Venta::firstOrFail()->estado);
        $this->assertSame('egreso', $movimiento->tipo_movimiento);
        $this->assertSame('yape', $movimiento->medio_pago);
        $this->assertSame('40.00', $cajaVentas->refresh()->total_egresos);
        $this->assertSame('0.00', $cajaVentas->egresos_efectivo);
        $this->assertSame('100.00', $cajaVentas->monto_final);
        $this->assertSame(5, $producto->refresh()->stock);
    }

    private function createAdministrator(): Usuario
    {
        $role = Rol::create([
            'nombre' => 'Administrador',
            'activo' => true,
        ]);

        return Usuario::create([
            'id_rol' => $role->id_rol,
            'nombre' => 'Administrador de prueba',
            'usuario' => 'admin-prueba',
            'email' => 'admin@example.test',
            'password' => Hash::make('secreto123'),
            'activo' => true,
        ]);
    }

    private function openBox(string $type, float $openingAmount): Caja
    {
        $this->post(route('caja.aperturar'), [
            'tipo_caja' => $type,
            'monto_inicial' => $openingAmount,
        ])->assertRedirect(route('caja.index'));

        return Caja::query()
            ->where('tipo_caja', $type)
            ->where('tipo_caja_abierta', $type)
            ->firstOrFail();
    }

    private function recordMovement(
        Caja $caja,
        string $type,
        string $category,
        string $paymentMethod,
        float $amount,
        string $detail
    ): void {
        $this->post(route('caja.movimientos.store', $caja), [
            'tipo_movimiento' => $type,
            'categoria' => $category,
            'medio_pago' => $paymentMethod,
            'monto' => $amount,
            'detalle' => $detail,
        ])->assertRedirect(route('caja.index'));
    }

    /**
     * @return array{Cliente, Producto}
     */
    private function createCustomerAndRentableProduct(): array
    {
        $cliente = Cliente::create([
            'tipo_documento' => 'DNI',
            'dni_ruc' => '45891234',
            'nombres' => 'Cliente de prueba',
            'activo' => true,
        ]);
        $category = Categoria::create([
            'nombre' => 'Herramientas',
            'activo' => true,
        ]);
        $brand = Marca::create([
            'nombre' => 'Marca de prueba',
            'activo' => true,
        ]);
        $producto = Producto::create([
            'codigo' => 'HER-001',
            'id_categoria' => $category->id_categoria,
            'id_marca' => $brand->id_marca,
            'nombre' => 'Taladro de prueba',
            'stock' => 5,
            'stock_minimo' => 1,
            'precio_compra' => 20,
            'precio_venta' => 40,
            'precio_venta_mayor' => 35,
            'cantidad_mayorista' => 3,
            'es_alquilable' => true,
            'precio_alquiler_dia' => 25,
            'activo' => true,
        ]);

        return [$cliente, $producto];
    }
}
