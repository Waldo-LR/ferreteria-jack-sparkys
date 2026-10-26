<?php

$c1 = <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\GastoCaja;
use App\Models\Venta;
use App\Models\DetalleVenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CajaController extends Controller
{
    public function index()
    {
        $cajaActual = Caja::with('usuario')->where('estado', 'abierta')->first();
        $gastos = $cajaActual ? GastoCaja::where('id_caja', $cajaActual->id_caja)->get() : collect();
        $ventasTurno = $cajaActual ? Venta::with(['cliente', 'usuario'])->where('id_caja', $cajaActual->id_caja)->latest('id_venta')->get() : collect();
        $historialCajas = Caja::with('usuario')->latest('id_caja')->take(10)->get();

        // Gráfico de productos más vendidos
        $topProductos = DetalleVenta::join('tproductos', 'tdetalleventas.id_producto', '=', 'tproductos.id_producto')
            ->select('tproductos.nombre', DB::raw('SUM(tdetalleventas.cantidad) as total_vendido'))
            ->groupBy('tproductos.id_producto', 'tproductos.nombre')
            ->orderByDesc('total_vendido')
            ->take(6)
            ->get();

        return view('caja.index', compact(
            'cajaActual', 'gastos', 'ventasTurno', 'historialCajas', 'topProductos'
        ));
    }

    public function aperturar(Request $request)
    {
        $request->validate([
            'monto_inicial' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string'
        ]);

        $cajaAbierta = Caja::where('estado', 'abierta')->first();
        if ($cajaAbierta) {
            return back()->with('error', 'Ya existe una caja abierta en el sistema. Debe cerrarla antes de abrir una nueva.');
        }

        $now = Carbon::now();
        $montoInicial = floatval($request->monto_inicial);

        $caja = Caja::create([
            'id_usuario' => Auth::id() ?? 1,
            'fecha_apertura' => $now,
            'fecha_cierre' => null,
            'monto_inicial' => $montoInicial,
            'total_ingresos' => 0.00,
            'ingresos_efectivo' => 0.00,
            'total_egresos' => 0.00,
            'monto_final' => $montoInicial,
            'tipo_cierre' => null,
            'estado' => 'abierta',
            'observaciones' => $request->observaciones ?? 'Apertura de turno',
        ]);

        DB::table('tauditoria')->insert([
            'id_usuario' => Auth::id() ?? 1,
            'accion' => 'CAJA_APERTURADA',
            'tabla_afectada' => 'tcaja',
            'id_registro' => $caja->id_caja,
            'detalle' => "Apertura de caja chica con saldo inicial de S/ {$montoInicial}",
            'ip' => $request->ip(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return back()->with('success', "¡Caja chica aperturada con éxito! Saldo inicial: S/ {$montoInicial}");
    }
PHP;

file_put_contents(__DIR__ . '/app/Http/Controllers/CajaController.php', $c1);
echo "caja1 written\n";
