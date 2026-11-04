<?php

namespace Tests\Feature;

use App\Models\Caja;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\DetalleVenta;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VentaHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->actingAs($this->createAdministrator());
    }

    public function test_sales_history_lists_receipt_preview_and_print_actions(): void
    {
        $venta = $this->createSale();

        $this->get(route('ventas.index'))
            ->assertOk()
            ->assertSee($this->receiptNumber($venta))
            ->assertSee('Ver comprobante')
            ->assertSee('Imprimir')
            ->assertSee('Descargar PDF')
            ->assertSee(route('ventas.imprimir', $venta->id_venta));
    }

    public function test_sales_history_filters_by_search_type_status_and_date(): void
    {
        $matchingSale = $this->createSale([
            'cliente' => [
                'nombres' => 'Cliente coincidente',
                'dni_ruc' => '45891234',
            ],
            'fecha_emision' => '2026-10-10',
            'estado' => 'anulada',
        ]);
        $this->createSale([
            'cliente' => [
                'nombres' => 'Otro cliente',
                'dni_ruc' => '71238495',
            ],
            'fecha_emision' => '2026-10-11',
        ]);

        $this->get(route('ventas.index', [
            'buscar' => 'coincidente',
            'tipo_comprobante' => 'boleta',
            'estado' => 'anulada',
            'fecha_desde' => '2026-10-10',
            'fecha_hasta' => '2026-10-10',
        ]))
            ->assertOk()
            ->assertViewHas('ventas', function ($ventas) use ($matchingSale) {
                return $ventas->count() === 1
                    && $ventas->first()->id_venta === $matchingSale->id_venta;
            });
    }

    public function test_sales_history_rejects_an_invalid_filter(): void
    {
        $this->from(route('ventas.index'))
            ->get(route('ventas.index', [
                'tipo_comprobante' => 'no-valido',
                'fecha_desde' => 'fecha-invalida',
            ]))
            ->assertRedirect(route('ventas.index'))
            ->assertSessionHasErrors(['tipo_comprobante', 'fecha_desde']);
    }

    public function test_receipt_displays_company_sale_and_item_details_with_print_options(): void
    {
        $venta = $this->createSale();
        Configuracion::create([
            'razon_social' => 'Ferretería de prueba S.A.C.',
            'ruc' => '20609876543',
            'direccion' => 'Av. Principal 123, Lima',
            'telefono' => '987654321',
            'email' => 'empresa@example.test',
            'slogan' => 'Herramientas para tus proyectos',
            'igv_porcentaje' => 18,
            'mora_porcentaje' => 5,
            'dias_recuperacion' => 30,
            'dias_alerta_vencimiento' => 5,
            'cierre_automatico' => false,
        ]);

        $this->get(route('ventas.imprimir', $venta->id_venta))
            ->assertOk()
            ->assertSee('Ferretería de prueba S.A.C.')
            ->assertSee('Cliente coincidente')
            ->assertSee('Martillo de prueba')
            ->assertSee('IMPRIMIR COMPROBANTE')
            ->assertSee('DESCARGAR PDF')
            ->assertSee('window.print()');
    }

    public function test_receipt_route_returns_not_found_for_a_nonexistent_sale(): void
    {
        $this->get(route('ventas.imprimir', 9999))->assertNotFound();
    }

    public function test_receipt_pdf_download_returns_a_named_pdf_file(): void
    {
        $venta = $this->createSale();

        $response = $this->get(route('ventas.pdf', $venta->id_venta));

        $response
            ->assertOk()
            ->assertDownload('comprobante-b001-000101.pdf');

        $this->assertSame('%PDF-', substr($response->getContent(), 0, 5));
    }

    private function createAdministrator(): Usuario
    {
        $rol = Rol::create([
            'nombre' => 'Administrador',
            'activo' => true,
        ]);

        return Usuario::create([
            'id_rol' => $rol->id_rol,
            'nombre' => 'Administradora de prueba',
            'usuario' => 'admin-prueba',
            'email' => 'admin@example.test',
            'password' => Hash::make('secreto123'),
            'activo' => true,
        ]);
    }

    private function createSale(array $overrides = []): Venta
    {
        $suffix = (string) (Venta::count() + 1);
        $clienteData = array_merge([
            'nombres' => 'Cliente coincidente',
            'dni_ruc' => str_pad((string) (45891233 + (int) $suffix), 8, '0', STR_PAD_LEFT),
        ], $overrides['cliente'] ?? []);

        $cliente = Cliente::create(array_merge($clienteData, [
            'tipo_documento' => 'DNI',
            'email' => "cliente{$suffix}@example.test",
            'telefono' => '987654321',
            'direccion' => 'Lima',
            'activo' => true,
        ]));

        $categoria = Categoria::create([
            'nombre' => "Herramientas {$suffix}",
            'activo' => true,
        ]);
        $marca = Marca::create([
            'nombre' => "Marca {$suffix}",
            'activo' => true,
        ]);
        $producto = Producto::create([
            'codigo' => "HER-{$suffix}",
            'id_categoria' => $categoria->id_categoria,
            'id_marca' => $marca->id_marca,
            'nombre' => 'Martillo de prueba',
            'stock' => 10,
            'stock_minimo' => 1,
            'precio_compra' => 25,
            'precio_venta' => 38,
            'precio_venta_mayor' => 33,
            'cantidad_mayorista' => 6,
            'es_alquilable' => false,
            'precio_alquiler_dia' => 0,
            'activo' => true,
        ]);
        $caja = Caja::create([
            'id_usuario' => $this->actingAsUserId(),
            'fecha_apertura' => now(),
            'monto_inicial' => 100,
            'total_ingresos' => 0,
            'ingresos_efectivo' => 0,
            'total_egresos' => 0,
            'monto_final' => 100,
            'estado' => 'abierta',
        ]);

        $saleNumber = 100 + (int) $suffix;
        $venta = Venta::create(array_merge([
            'id_caja' => $caja->id_caja,
            'id_cliente' => $cliente->id_cliente,
            'id_usuario' => $this->actingAsUserId(),
            'tipo_comprobante' => 'boleta',
            'serie' => 'B001',
            'numero' => $saleNumber,
            'fecha_emision' => '2026-10-10',
            'medio_pago' => 'efectivo',
            'valor_venta' => 38,
            'igv' => 0,
            'total' => 38,
            'monto_pagado' => 40,
            'vuelto' => 2,
            'estado' => 'emitida',
            'estado_sunat' => 'simulado',
        ], array_diff_key($overrides, ['cliente' => true])));

        DetalleVenta::create([
            'id_venta' => $venta->id_venta,
            'id_producto' => $producto->id_producto,
            'cantidad' => 1,
            'precio_unitario' => 38,
            'costo_unitario' => 25,
            'subtotal' => 38,
        ]);

        return $venta;
    }

    private function actingAsUserId(): int
    {
        return (int) auth()->id();
    }

    private function receiptNumber(Venta $venta): string
    {
        return $venta->serie.'-'.str_pad((string) $venta->numero, 6, '0', STR_PAD_LEFT);
    }
}
