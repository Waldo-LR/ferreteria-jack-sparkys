<?php

namespace Tests\Feature;

use App\Models\Alquiler;
use App\Models\Caja;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Cobranza;
use App\Models\Configuracion;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AlquilerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_registers_a_rental_with_partial_payment_and_a_separate_guarantee(): void
    {
        $usuario = $this->createUser(1, 'admin-alquiler');
        $this->actingAs($usuario);
        $caja = $this->openRentalCash($usuario);
        [$cliente, $producto] = $this->createCustomerAndProduct();

        $this->post(route('alquileres.store'), $this->rentalData($cliente, $producto, [
            'monto_pagado' => '10.00',
            'medio_pago_alquiler' => 'yape',
            'monto_garantia' => '50.00',
            'medio_pago_garantia' => 'efectivo',
        ]))
            ->assertRedirect(route('alquileres.index'))
            ->assertSessionHas('success');

        $alquiler = Alquiler::query()->firstOrFail();
        $cobranza = Cobranza::query()->where('tipo', Cobranza::TIPO_ALQUILER)->firstOrFail();

        $this->assertSame(1, $producto->refresh()->stock);
        $this->assertSame('40.00', $alquiler->total_alquiler);
        $this->assertSame('10.00', $alquiler->monto_pagado);
        $this->assertSame('50.00', $alquiler->monto_garantia);
        $this->assertSame('40.00', $cobranza->monto_mora);
        $this->assertSame('10.00', $cobranza->monto_pagado);
        $this->assertDatabaseHas('tpagoscobranza', [
            'id_cobranza' => $cobranza->id_cobranza,
            'id_caja' => $caja->id_caja,
            'monto' => '10.00',
            'medio_pago' => 'yape',
        ]);
        $this->assertDatabaseHas('tgastoscaja', [
            'id_caja' => $caja->id_caja,
            'id_alquiler' => $alquiler->id_alquiler,
            'tipo_movimiento' => 'ingreso',
            'categoria' => 'Garantía de alquiler',
            'monto' => '50.00',
            'medio_pago' => 'efectivo',
        ]);
        $this->assertSame('60.00', $caja->refresh()->total_ingresos);
        $this->assertSame('150.00', $caja->monto_final);
        $this->assertDatabaseHas('tmovimientosstock', [
            'id_producto' => $producto->id_producto,
            'tipo' => 'salida',
            'referencia' => 'ALQ-'.$alquiler->id_alquiler,
            'stock_resultante' => 1,
        ]);
        $this->assertDatabaseHas('tauditoria', [
            'id_usuario' => $usuario->id_usuario,
            'accion' => 'ALQUILER_CREADO',
            'id_registro' => $alquiler->id_alquiler,
        ]);
    }

    public function test_blocks_a_second_rental_until_the_equipment_is_returned_and_all_debt_is_paid(): void
    {
        $usuario = $this->createUser(1, 'admin-bloqueo-alquiler');
        $this->actingAs($usuario);
        $this->openRentalCash($usuario);
        [$cliente, $producto] = $this->createCustomerAndProduct();
        $this->post(route('alquileres.store'), $this->rentalData($cliente, $producto))
            ->assertRedirect(route('alquileres.index'));
        $alquiler = Alquiler::query()->firstOrFail();

        $this->from(route('alquileres.index'))
            ->post(route('alquileres.store'), $this->rentalData($cliente, $producto))
            ->assertRedirect(route('alquileres.index'))
            ->assertSessionHasErrors([
                'id_cliente' => 'No se puede registrar otro alquiler: el cliente tiene una devolución pendiente o una deuda con la empresa.',
            ]);

        $alquiler->update(['estado' => 'devuelto']);

        $this->from(route('alquileres.index'))
            ->post(route('alquileres.store'), $this->rentalData($cliente, $producto))
            ->assertRedirect(route('alquileres.index'))
            ->assertSessionHasErrors('id_cliente');
        $this->assertDatabaseCount('talquileres', 1);
    }

    public function test_filters_rental_history_by_current_overdue_and_returned_state(): void
    {
        $this->travelTo('2026-11-03 10:00:00');
        $usuario = $this->createUser(1, 'admin-filtro-alquiler');
        $this->actingAs($usuario);
        $this->openRentalCash($usuario);
        [$cliente, $producto] = $this->createCustomerAndProduct();

        $this->post(route('alquileres.store'), $this->rentalData($cliente, $producto, [
            'monto_pagado' => '40.00',
            'medio_pago_alquiler' => 'efectivo',
        ]))->assertRedirect(route('alquileres.index'));
        $vigente = Alquiler::query()->firstOrFail();

        $vencido = $vigente->replicate();
        $vencido->fecha_pactada_devolucion = '2026-11-02';
        $vencido->save();

        $devuelto = $vigente->replicate();
        $devuelto->estado = 'devuelto';
        $devuelto->fecha_devolucion_real = '2026-11-02';
        $devuelto->save();

        $this->get(route('alquileres.index', ['estado' => 'devuelto']))
            ->assertOk()
            ->assertSee('#ALQ-'.$devuelto->id_alquiler)
            ->assertDontSee('#ALQ-'.$vigente->id_alquiler)
            ->assertDontSee('#ALQ-'.$vencido->id_alquiler);

        $this->get(route('alquileres.index', ['estado' => 'vencido']))
            ->assertOk()
            ->assertSee('#ALQ-'.$vencido->id_alquiler)
            ->assertDontSee('#ALQ-'.$vigente->id_alquiler)
            ->assertDontSee('#ALQ-'.$devuelto->id_alquiler);

        $this->get(route('alquileres.index', ['estado' => 'vigente']))
            ->assertOk()
            ->assertSee('#ALQ-'.$vigente->id_alquiler)
            ->assertDontSee('#ALQ-'.$vencido->id_alquiler)
            ->assertDontSee('#ALQ-'.$devuelto->id_alquiler);
    }

    public function test_rental_registration_and_history_are_separate_pages(): void
    {
        $usuario = $this->createUser(1, 'admin-modulos-alquiler');
        $this->actingAs($usuario);

        $this->get(route('alquileres.create'))
            ->assertOk()
            ->assertSee('Registrar alquiler')
            ->assertSee('name="monto_garantia"', false)
            ->assertDontSee('Pagos, saldos, garantías, fechas y estado de devolución.');

        $this->get(route('alquileres.index'))
            ->assertOk()
            ->assertSee('Historial de alquileres')
            ->assertSee('Pagos, saldos, garantías, fechas y estado de devolución.')
            ->assertDontSee('name="monto_garantia"', false);

        $this->get('/alquileres')
            ->assertRedirect(route('alquileres.index'));
    }

    public function test_return_calculates_late_fee_and_applies_guarantee_to_damage(): void
    {
        $this->travelTo('2026-11-03 10:00:00');
        $usuario = $this->createUser(1, 'admin-devolucion');
        $this->actingAs($usuario);
        $caja = $this->openRentalCash($usuario);
        [$cliente, $producto] = $this->createCustomerAndProduct();
        Configuracion::create([
            'razon_social' => 'Ferretería de prueba',
            'ruc' => '20123456789',
            'direccion' => 'Dirección de prueba',
            'telefono' => '999999999',
            'email' => 'prueba@example.test',
            'igv_porcentaje' => 18,
            'mora_porcentaje' => 15,
            'dias_recuperacion' => 30,
            'dias_alerta_vencimiento' => 5,
            'cierre_automatico' => false,
        ]);
        $this->post(route('alquileres.store'), $this->rentalData($cliente, $producto, [
            'monto_pagado' => '20.00',
            'medio_pago_alquiler' => 'efectivo',
            'monto_garantia' => '50.00',
            'medio_pago_garantia' => 'efectivo',
            'dias_pactados' => 1,
        ]))->assertRedirect(route('alquileres.index'));
        $alquiler = Alquiler::query()->firstOrFail();
        $this->travelTo('2026-11-06 10:00:00');

        $this->post(route('alquileres.devolver', $alquiler), [
            'monto_dano' => '60.00',
            'observaciones_devolucion' => 'Carcasa rota; requiere reparación.',
        ])->assertRedirect(route('alquileres.index'))
            ->assertSessionHas('success');

        $mora = Cobranza::query()->where('tipo', Cobranza::TIPO_MORA)->firstOrFail();
        $dano = Cobranza::query()->where('tipo', Cobranza::TIPO_DANO)->firstOrFail();
        $alquiler->refresh();

        $this->assertSame('devuelto', $alquiler->estado);
        $this->assertSame('2026-11-06', $alquiler->fecha_devolucion_real->toDateString());
        $this->assertSame('50.00', $alquiler->monto_garantia_aplicado);
        $this->assertSame('0.00', $alquiler->monto_garantia_devuelto);
        $this->assertSame(2, $mora->dias_atraso);
        $this->assertSame('15.00', $mora->porcentaje_aplicado);
        $this->assertSame('6.00', $mora->monto_mora);
        $this->assertSame('10.00', $dano->monto_mora);
        $this->assertSame(2, $producto->refresh()->stock);
        $this->assertSame('70.00', $caja->refresh()->total_ingresos);
        $this->assertDatabaseHas('tmovimientosstock', [
            'id_producto' => $producto->id_producto,
            'tipo' => 'entrada',
            'referencia' => 'DEV-'.$alquiler->id_alquiler,
            'stock_resultante' => 2,
        ]);
    }

    public function test_returns_unused_guarantee_as_a_cash_expense(): void
    {
        $usuario = $this->createUser(1, 'admin-garantia');
        $this->actingAs($usuario);
        $caja = $this->openRentalCash($usuario);
        [$cliente, $producto] = $this->createCustomerAndProduct();
        $this->post(route('alquileres.store'), $this->rentalData($cliente, $producto, [
            'monto_pagado' => '40.00',
            'medio_pago_alquiler' => 'efectivo',
            'monto_garantia' => '50.00',
            'medio_pago_garantia' => 'efectivo',
        ]))->assertRedirect(route('alquileres.index'));
        $alquiler = Alquiler::query()->firstOrFail();

        $this->post(route('alquileres.devolver', $alquiler), [
            'monto_dano' => '20.00',
            'observaciones_devolucion' => 'Equipo revisado; desgaste menor.',
        ])->assertRedirect(route('alquileres.index'));

        $this->assertSame('20.00', $alquiler->refresh()->monto_garantia_aplicado);
        $this->assertSame('30.00', $alquiler->monto_garantia_devuelto);
        $this->assertSame('160.00', $caja->refresh()->monto_final);
        $this->assertDatabaseHas('tgastoscaja', [
            'id_alquiler' => $alquiler->id_alquiler,
            'tipo_movimiento' => 'egreso',
            'categoria' => 'Devolución de garantía',
            'monto' => '30.00',
            'medio_pago' => 'efectivo',
        ]);
        $this->assertDatabaseCount('tcobranzas', 1);
    }

    public function test_records_partial_rental_payments_in_cash_and_the_collection_history(): void
    {
        $usuario = $this->createUser(1, 'admin-abono-alquiler');
        $this->actingAs($usuario);
        $caja = $this->openRentalCash($usuario);
        [$cliente, $producto] = $this->createCustomerAndProduct();
        $this->post(route('alquileres.store'), $this->rentalData($cliente, $producto))
            ->assertRedirect(route('alquileres.index'));
        $cobranza = Cobranza::query()->where('tipo', Cobranza::TIPO_ALQUILER)->firstOrFail();

        $this->post(route('cobranzas.pagar', $cobranza), [
            'monto' => '15.00',
            'medio_pago' => 'deposito',
            'observaciones' => 'Abono por transferencia.',
        ])->assertRedirect(route('cobranzas.index'))
            ->assertSessionHas('success');

        $this->assertSame('15.00', $cobranza->refresh()->monto_pagado);
        $this->assertSame('15.00', Alquiler::query()->firstOrFail()->monto_pagado);
        $this->assertSame('65.00', $caja->refresh()->total_ingresos);
        $this->assertSame('150.00', $caja->monto_final);
        $this->assertDatabaseHas('tpagoscobranza', [
            'id_cobranza' => $cobranza->id_cobranza,
            'monto' => '15.00',
            'medio_pago' => 'deposito',
            'observaciones' => 'Abono por transferencia.',
        ]);
        $this->get(route('cobranzas.index', ['tipo' => 'alquiler', 'estado' => 'pendiente']))
            ->assertOk()
            ->assertSee('Historial de moras, saldos y cobranzas')
            ->assertSee('Abono por transferencia.')
            ->assertSee('Saldo: S/ 25.00');
    }

    public function test_alquiler_and_cobranzas_routes_require_authentication_and_role(): void
    {
        $this->get(route('alquileres.index'))->assertRedirect(route('login'));
        $this->get(route('cobranzas.index'))->assertRedirect(route('login'));

        $warehouseUser = $this->createUser(3, 'almacen-alquiler');
        $this->actingAs($warehouseUser);
        $this->get(route('alquileres.index'))->assertForbidden();
        $this->get(route('cobranzas.index'))->assertForbidden();
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

    private function openRentalCash(Usuario $usuario): Caja
    {
        return Caja::create([
            'id_usuario' => $usuario->id_usuario,
            'tipo_caja' => Caja::TIPO_ALQUILERES,
            'tipo_caja_abierta' => Caja::TIPO_ALQUILERES,
            'fecha_apertura' => now(),
            'monto_inicial' => '100.00',
            'monto_final' => '100.00',
            'estado' => 'abierta',
        ]);
    }

    /**
     * @return array{Cliente, Producto}
     */
    private function createCustomerAndProduct(): array
    {
        $cliente = Cliente::create([
            'tipo_documento' => 'DNI',
            'dni_ruc' => '45891234',
            'nombres' => 'Cliente de alquiler',
            'telefono' => '987654321',
            'activo' => true,
        ]);
        $categoria = Categoria::create(['nombre' => 'Herramientas', 'activo' => true]);
        $marca = Marca::create(['nombre' => 'Marca de prueba', 'activo' => true]);
        $producto = Producto::create([
            'codigo' => 'ALQ-001',
            'id_categoria' => $categoria->id_categoria,
            'id_marca' => $marca->id_marca,
            'nombre' => 'Taladro de alquiler',
            'stock' => 2,
            'stock_minimo' => 1,
            'precio_compra' => '20.00',
            'precio_venta' => '40.00',
            'precio_venta_mayor' => '35.00',
            'cantidad_mayorista' => 2,
            'es_alquilable' => true,
            'precio_alquiler_dia' => '20.00',
            'activo' => true,
        ]);

        return [$cliente, $producto];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function rentalData(Cliente $cliente, Producto $producto, array $overrides = []): array
    {
        return array_merge([
            'id_cliente' => $cliente->id_cliente,
            'id_producto' => $producto->id_producto,
            'dias_pactados' => 2,
            'fecha_entrega' => today()->toDateString(),
            'monto_pagado' => '0.00',
            'medio_pago_alquiler' => null,
            'monto_garantia' => '50.00',
            'medio_pago_garantia' => 'efectivo',
            'ubicacion_herramienta' => 'Obra de prueba',
            'observaciones' => 'Entregado en buenas condiciones.',
        ], $overrides);
    }
}
