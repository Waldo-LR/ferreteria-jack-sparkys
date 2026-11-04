<?php

namespace Tests\Feature;

use App\Models\Alquiler;
use App\Models\Caja;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_does_not_count_historical_unreconciled_purchases_as_supplier_debt(): void
    {
        $role = Rol::create(['nombre' => 'Administrador', 'activo' => true]);
        $user = Usuario::create([
            'id_rol' => $role->id_rol,
            'nombre' => 'Administradora de compras',
            'usuario' => 'admin-compras-dashboard',
            'email' => 'admin-compras-dashboard@example.test',
            'password' => Hash::make('secreto123'),
            'activo' => true,
        ]);
        $provider = Proveedor::create([
            'razon_social' => 'Proveedor histórico',
            'ruc' => '20123456789',
            'activo' => true,
        ]);
        Compra::create([
            'id_proveedor' => $provider->id_proveedor,
            'id_usuario' => $user->id_usuario,
            'tipo_comprobante' => 'Factura',
            'numero_comprobante' => 'F001-HISTORICA',
            'fecha_compra' => today()->toDateString(),
            'total' => '100.00',
            'estado' => 'registrada',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('cuentasPorPagar', 0.0);
    }

    public function test_dashboard_shows_emitted_sales_for_each_of_the_last_seven_days(): void
    {
        $this->travelTo('2026-10-30 12:00:00');

        $role = Rol::create([
            'nombre' => 'Administrador',
            'activo' => true,
        ]);
        $user = Usuario::create([
            'id_rol' => $role->id_rol,
            'nombre' => 'Administradora de prueba',
            'usuario' => 'admin-dashboard',
            'email' => 'admin-dashboard@example.test',
            'password' => Hash::make('secreto123'),
            'activo' => true,
        ]);
        $this->actingAs($user);

        $cliente = Cliente::create([
            'tipo_documento' => 'DNI',
            'dni_ruc' => '45891234',
            'nombres' => 'Cliente de prueba',
            'activo' => true,
        ]);
        $caja = Caja::create([
            'id_usuario' => $user->id_usuario,
            'fecha_apertura' => now(),
            'monto_inicial' => 100,
            'monto_final' => 100,
            'estado' => 'abierta',
        ]);

        $categoria = Categoria::create([
            'nombre' => 'Herramientas',
            'activo' => true,
        ]);
        $marca = Marca::create([
            'nombre' => 'Marca de prueba',
            'activo' => true,
        ]);
        $alquilerProducto = $this->createProduct($categoria->id_categoria, $marca->id_marca, 'REP-001', true, 1, 2);
        $this->createProduct($categoria->id_categoria, $marca->id_marca, 'REP-002', true, 8, 2);
        $this->createProduct($categoria->id_categoria, $marca->id_marca, 'REP-003', false, 0, 2);

        foreach ([
            ['total' => 100, 'created_at' => '2026-10-24 10:00:00'],
            ['total' => 55, 'created_at' => '2026-10-30 11:00:00'],
            ['total' => 1000, 'created_at' => '2026-10-23 11:00:00'],
        ] as $alquiler) {
            $registro = Alquiler::create([
                'id_caja' => $caja->id_caja,
                'id_cliente' => $cliente->id_cliente,
                'id_producto' => $alquilerProducto->id_producto,
                'id_usuario' => $user->id_usuario,
                'cantidad' => 1,
                'precio_dia' => 50,
                'dias_pactados' => 2,
                'fecha_entrega' => substr($alquiler['created_at'], 0, 10),
                'fecha_pactada_devolucion' => substr($alquiler['created_at'], 0, 10),
                'total_alquiler' => $alquiler['total'],
                'medio_pago' => 'pendiente',
                'estado' => 'devuelto',
            ]);
            $registro->timestamps = false;
            $registro->created_at = $alquiler['created_at'];
            $registro->updated_at = $alquiler['created_at'];
            $registro->save();
        }

        foreach ([
            ['numero' => 1, 'fecha_emision' => '2026-10-24', 'total' => 120, 'estado' => 'emitida'],
            ['numero' => 2, 'fecha_emision' => '2026-10-30', 'total' => 85, 'estado' => 'emitida'],
            ['numero' => 3, 'fecha_emision' => '2026-10-30', 'total' => 500, 'estado' => 'anulada'],
            ['numero' => 4, 'fecha_emision' => '2026-10-23', 'total' => 250, 'estado' => 'emitida'],
        ] as $venta) {
            Venta::create([
                'id_caja' => $caja->id_caja,
                'id_cliente' => $cliente->id_cliente,
                'id_usuario' => $user->id_usuario,
                'tipo_comprobante' => 'boleta',
                'serie' => 'B001',
                'numero' => $venta['numero'],
                'fecha_emision' => $venta['fecha_emision'],
                'medio_pago' => 'efectivo',
                'valor_venta' => $venta['total'],
                'igv' => 0,
                'total' => $venta['total'],
                'estado' => $venta['estado'],
            ]);
        }

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ventas de hoy')
            ->assertSee('Operaciones hoy')
            ->assertSee('Productos activos')
            ->assertSee('Por reponer')
            ->assertSee('Alquileres vigentes')
            ->assertSee('Estado de caja')
            ->assertSee('Saldos de alquiler y moras')
            ->assertSee('S/ 85.00')
            ->assertSee('Resumen de ventas')
            ->assertSee('Resumen de alquileres')
            ->assertSee('window.dashboardChartData', false)
            ->assertSee('205.00')
            ->assertSee('155.00')
            ->assertDontSee('cdn.jsdelivr.net', false)
            ->assertDontSee('cdnjs.cloudflare.com', false)
            ->assertSee('id="salesChart"', false)
            ->assertSee('id="rentalsChart"', false)
            ->assertSee('href="'.route('alquileres.index', ['estado' => 'vigente']).'"', false)
            ->assertSee('href="'.route('inventario.index').'"', false)
            ->assertSeeInOrder(['id="salesChart"', 'id="rentalsChart"', 'Ventas de hoy'])
            ->assertViewHas('ventasHoy', 85.0)
            ->assertViewHas('operacionesHoy', 1)
            ->assertViewHas('totalProductos', 2)
            ->assertViewHas('productosPorReponer', 1)
            ->assertViewHas('totalMorasPendientes', 0.0)
            ->assertViewHas('alquileresVencidos', 0)
            ->assertViewHas('ventasChartLabels', ['24/10', '25/10', '26/10', '27/10', '28/10', '29/10', '30/10'])
            ->assertViewHas('ventasChartData', [120.0, 0.0, 0.0, 0.0, 0.0, 0.0, 85.0])
            ->assertViewHas('alquileresChartData', [100.0, 0.0, 0.0, 0.0, 0.0, 0.0, 55.0]);
    }

    private function createProduct(
        int $categoryId,
        int $brandId,
        string $code,
        bool $active,
        int $stock,
        int $minimumStock
    ): Producto {
        return Producto::create([
            'codigo' => $code,
            'id_categoria' => $categoryId,
            'id_marca' => $brandId,
            'nombre' => "Producto {$code}",
            'stock' => $stock,
            'stock_minimo' => $minimumStock,
            'precio_compra' => 5,
            'precio_venta' => 10,
            'precio_venta_mayor' => 9,
            'cantidad_mayorista' => 2,
            'es_alquilable' => false,
            'precio_alquiler_dia' => 0,
            'activo' => $active,
        ]);
    }
}
