<?php

namespace Tests\Feature;

use App\Models\Caja;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Marca;
use App\Models\MovimientoCaja;
use App\Models\MovimientoStock;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReportesControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public static function supportedReportTypes(): array
    {
        return [
            'sales' => ['ventas', 'Reporte de Ventas'],
            'rentals' => ['alquileres', 'Reporte de Alquileres'],
            'collections' => ['cobranzas', 'Reporte de Cobranzas'],
            'cash movements' => ['caja', 'Reporte de Movimientos de Caja'],
            'inventory' => ['inventario', 'Reporte de Inventario'],
            'purchases' => ['compras', 'Reporte de Compras y Proveedores'],
            'stock movements' => ['movimientos_inventario', 'Reporte de Movimientos de Inventario'],
        ];
    }

    public static function additionalReportTypes(): array
    {
        return [
            'purchases' => ['compras'],
            'stock movements' => ['movimientos_inventario'],
        ];
    }

    #[DataProvider('supportedReportTypes')]
    public function test_administrator_can_render_each_supported_report_type(string $type, string $title): void
    {
        $administrator = $this->createAdministrator();

        $response = $this->actingAs($administrator)->get(route('reportes.index', [
            'tipo' => $type,
            'fecha_desde' => '2026-03-01',
            'fecha_hasta' => '2026-03-31',
        ]));

        $response->assertOk()
            ->assertSee($title)
            ->assertSee('Exportar PDF')
            ->assertSee('onchange="this.form.requestSubmit()"', false);
    }

    public function test_sales_report_filters_by_period_and_excludes_cancelled_sales_from_total(): void
    {
        $administrator = $this->createAdministrator();
        $client = Cliente::create([
            'tipo_documento' => 'DNI',
            'dni_ruc' => '45891234',
            'nombres' => 'Cliente del reporte',
            'activo' => true,
        ]);
        $cashRegister = $this->createCashRegister($administrator);
        $this->createSale($administrator, $client, $cashRegister, 1, '2026-03-10', 118, 'emitida');
        $this->createSale($administrator, $client, $cashRegister, 2, '2026-03-11', 50, 'anulada');
        $this->createSale($administrator, $client, $cashRegister, 3, '2026-02-28', 75, 'emitida');

        $response = $this->actingAs($administrator)->get(route('reportes.index', [
            'tipo' => 'ventas',
            'fecha_desde' => '2026-03-01',
            'fecha_hasta' => '2026-03-31',
        ]));

        $response->assertOk()
            ->assertSee('S/ 118.00')
            ->assertSee('Anuladas')
            ->assertSee('B001-000001')
            ->assertSee('B001-000002')
            ->assertDontSee('B001-000003');
    }

    public function test_purchases_report_shows_supplier_payments_and_remaining_balance(): void
    {
        $administrator = $this->createAdministrator();
        $provider = Proveedor::create([
            'razon_social' => 'Proveedor del reporte',
            'ruc' => '20123456789',
            'activo' => true,
        ]);
        $cashRegister = $this->createCashRegister($administrator);
        $purchase = Compra::create([
            'id_proveedor' => $provider->id_proveedor,
            'id_usuario' => $administrator->id_usuario,
            'tipo_comprobante' => 'Factura',
            'numero_comprobante' => 'F001-000123',
            'fecha_compra' => '2026-03-10',
            'total' => '100.00',
            'estado' => 'recibida',
        ]);
        Compra::create([
            'id_proveedor' => $provider->id_proveedor,
            'id_usuario' => $administrator->id_usuario,
            'tipo_comprobante' => 'Factura',
            'numero_comprobante' => 'F001-000124',
            'fecha_compra' => '2026-03-11',
            'total' => '20.00',
            'estado' => 'registrada',
        ]);
        MovimientoCaja::create([
            'id_caja' => $cashRegister->id_caja,
            'id_usuario' => $administrator->id_usuario,
            'id_compra' => $purchase->id_compra,
            'tipo_movimiento' => 'egreso',
            'categoria' => 'Pago a proveedor',
            'monto' => '40.00',
            'medio_pago' => 'efectivo',
            'detalle' => 'Abono de compra',
        ]);

        $this->actingAs($administrator)
            ->get(route('reportes.index', [
                'tipo' => 'compras',
                'fecha_desde' => '2026-03-01',
                'fecha_hasta' => '2026-03-31',
            ]))
            ->assertOk()
            ->assertSee('Proveedor del reporte')
            ->assertSee('F001-000123')
            ->assertSee('F001-000124')
            ->assertSee('Registrada')
            ->assertSee('S/ 100.00')
            ->assertSee('S/ 20.00')
            ->assertSee('S/ 40.00')
            ->assertSee('S/ 60.00')
            ->assertSee('Saldo pendiente');
    }

    public function test_stock_movements_report_filters_by_period_and_shows_kardex_details(): void
    {
        $this->travelTo('2026-03-15 12:00:00');
        $administrator = $this->createAdministrator();
        $category = Categoria::create(['nombre' => 'Herramientas', 'activo' => true]);
        $brand = Marca::create(['nombre' => 'Marca del reporte', 'activo' => true]);
        $product = Producto::create([
            'codigo' => 'REP-001',
            'id_categoria' => $category->id_categoria,
            'id_marca' => $brand->id_marca,
            'nombre' => 'Taladro del reporte',
            'stock' => 8,
            'stock_minimo' => 2,
            'precio_compra' => 20,
            'precio_venta' => 40,
            'precio_venta_mayor' => 35,
            'cantidad_mayorista' => 3,
            'es_alquilable' => false,
            'precio_alquiler_dia' => 0,
            'activo' => true,
        ]);
        MovimientoStock::create([
            'id_producto' => $product->id_producto,
            'id_usuario' => $administrator->id_usuario,
            'tipo' => 'entrada',
            'cantidad' => 5,
            'stock_resultante' => 8,
            'motivo' => 'Compra recibida',
            'referencia' => 'COM-10',
        ]);
        $outOfPeriodMovement = MovimientoStock::create([
            'id_producto' => $product->id_producto,
            'id_usuario' => $administrator->id_usuario,
            'tipo' => 'salida',
            'cantidad' => 1,
            'stock_resultante' => 7,
            'motivo' => 'Venta de producto',
            'referencia' => 'VEN-10',
        ]);
        $outOfPeriodMovement->timestamps = false;
        $outOfPeriodMovement->created_at = '2026-02-28 12:00:00';
        $outOfPeriodMovement->updated_at = '2026-02-28 12:00:00';
        $outOfPeriodMovement->save();

        $this->actingAs($administrator)
            ->get(route('reportes.index', [
                'tipo' => 'movimientos_inventario',
                'fecha_desde' => '2026-03-01',
                'fecha_hasta' => '2026-03-31',
            ]))
            ->assertOk()
            ->assertSee('Taladro del reporte')
            ->assertSee('Compra recibida')
            ->assertSee('COM-10')
            ->assertSee('Entradas')
            ->assertSee('1')
            ->assertDontSee('Venta de producto')
            ->assertDontSee('VEN-10');
    }

    public function test_pdf_export_returns_a_downloadable_pdf(): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('reportes.pdf', [
                'tipo' => 'ventas',
                'fecha_desde' => '2026-03-01',
                'fecha_hasta' => '2026-03-31',
            ]))
            ->assertOk()
            ->assertDownload('reporte-ventas-2026-03-01-a-2026-03-31.pdf')
            ->assertHeader('content-type', 'application/pdf');
    }

    #[DataProvider('additionalReportTypes')]
    public function test_pdf_export_supports_additional_report_types(string $type): void
    {
        $administrator = $this->createAdministrator();

        $this->actingAs($administrator)
            ->get(route('reportes.pdf', [
                'tipo' => $type,
                'fecha_desde' => '2026-03-01',
                'fecha_hasta' => '2026-03-31',
            ]))
            ->assertOk()
            ->assertDownload('reporte-'.$type.'-2026-03-01-a-2026-03-31.pdf')
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_pdf_export_rejects_reports_with_more_than_2500_rows(): void
    {
        $administrator = $this->createAdministrator();
        $client = Cliente::create([
            'tipo_documento' => 'DNI',
            'dni_ruc' => '45891234',
            'nombres' => 'Cliente del reporte',
            'activo' => true,
        ]);
        $cashRegister = $this->createCashRegister($administrator);
        $sales = [];

        for ($number = 1; $number <= 2501; $number++) {
            $sales[] = [
                'id_caja' => $cashRegister->id_caja,
                'id_cliente' => $client->id_cliente,
                'id_usuario' => $administrator->id_usuario,
                'tipo_comprobante' => 'boleta',
                'serie' => 'B001',
                'numero' => $number,
                'fecha_emision' => '2026-03-10',
                'medio_pago' => 'efectivo',
                'valor_venta' => 10,
                'igv' => 0,
                'total' => 10,
                'monto_pagado' => 10,
                'vuelto' => 0,
                'estado' => 'emitida',
            ];

            if (count($sales) === 50) {
                DB::table('tventas')->insert($sales);
                $sales = [];
            }
        }

        if ($sales !== []) {
            DB::table('tventas')->insert($sales);
        }

        $this->actingAs($administrator)
            ->from(route('reportes.pdf'))
            ->get(route('reportes.pdf', [
                'tipo' => 'ventas',
                'fecha_desde' => '2026-03-01',
                'fecha_hasta' => '2026-03-31',
            ]))
            ->assertRedirect(route('reportes.pdf'))
            ->assertSessionHasErrors([
                'tipo' => 'El reporte supera los 2,500 registros. Reduce el periodo y vuelve a exportar.',
            ]);
    }

    public function test_non_administrator_cannot_access_reports(): void
    {
        $this->createAdministrator();
        $sellerRole = Rol::create([
            'nombre' => 'Vendedor',
            'activo' => true,
        ]);
        $seller = $this->createUser($sellerRole->id_rol, 'vendedor');

        $this->actingAs($seller)
            ->get(route('reportes.index'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_from_reports(): void
    {
        $this->get(route('reportes.index'))
            ->assertRedirect(route('login'));
    }

    public function test_report_rejects_an_unknown_report_type(): void
    {
        $this->actingAs($this->createAdministrator())
            ->from(route('reportes.index'))
            ->get(route('reportes.index', ['tipo' => 'desconocido']))
            ->assertRedirect(route('reportes.index'))
            ->assertSessionHasErrors('tipo');
    }

    public function test_report_rejects_periods_longer_than_366_days(): void
    {
        $this->actingAs($this->createAdministrator())
            ->from(route('reportes.index'))
            ->get(route('reportes.index', [
                'tipo' => 'ventas',
                'fecha_desde' => '2025-01-01',
                'fecha_hasta' => '2026-01-02',
            ]))
            ->assertRedirect(route('reportes.index'))
            ->assertSessionHasErrors([
                'fecha_desde' => 'El periodo máximo de consulta es de 366 días.',
            ]);
    }

    private function createAdministrator(): Usuario
    {
        $role = Rol::create([
            'nombre' => 'Administrador',
            'activo' => true,
        ]);

        return $this->createUser($role->id_rol, 'administrador');
    }

    private function createUser(int $roleId, string $username): Usuario
    {
        return Usuario::create([
            'id_rol' => $roleId,
            'nombre' => ucfirst($username).' de prueba',
            'usuario' => $username.'-prueba',
            'email' => $username.'@example.test',
            'password' => Hash::make('secreto123'),
            'activo' => true,
        ]);
    }

    private function createCashRegister(Usuario $user): Caja
    {
        return Caja::create([
            'id_usuario' => $user->id_usuario,
            'tipo_caja' => Caja::TIPO_VENTAS,
            'tipo_caja_abierta' => Caja::TIPO_VENTAS,
            'fecha_apertura' => '2026-03-10 08:00:00',
            'monto_inicial' => 0,
            'total_ingresos' => 0,
            'ingresos_efectivo' => 0,
            'total_egresos' => 0,
            'egresos_efectivo' => 0,
            'monto_final' => 0,
            'estado' => 'abierta',
        ]);
    }

    private function createSale(
        Usuario $user,
        Cliente $client,
        Caja $cashRegister,
        int $number,
        string $date,
        float $total,
        string $status
    ): Venta {
        return Venta::create([
            'id_caja' => $cashRegister->id_caja,
            'id_cliente' => $client->id_cliente,
            'id_usuario' => $user->id_usuario,
            'tipo_comprobante' => 'boleta',
            'serie' => 'B001',
            'numero' => $number,
            'fecha_emision' => $date,
            'medio_pago' => 'efectivo',
            'valor_venta' => $total,
            'igv' => 0,
            'total' => $total,
            'monto_pagado' => $total,
            'vuelto' => 0,
            'estado' => $status,
        ]);
    }
}
