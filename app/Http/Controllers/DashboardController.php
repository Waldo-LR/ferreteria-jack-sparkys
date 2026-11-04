<?php

namespace App\Http\Controllers;

use App\Models\Alquiler;
use App\Models\Caja;
use App\Models\Cobranza;
use App\Models\Compra;
use App\Models\MovimientoCaja;
use App\Models\Producto;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (! $user) {
            return redirect('/login');
        }

        $now = Carbon::now();

        // Métricas Generales
        $totalProductos = Producto::where('activo', 1)->count();
        $stockBajo = Producto::where('activo', 1)->whereColumn('stock', '<=', 'stock_minimo')->get();

        // Alerta de productos próximos a vencer (en los siguientes 30 días)
        $porVencer = Producto::where('activo', 1)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<=', $now->copy()->addDays(30)->toDateString())
            ->get();

        $productosPorReponer = $stockBajo->count();
        $ventasDelDia = Venta::where('estado', 'emitida')
            ->whereDate('fecha_emision', $now->toDateString());
        $ventasHoy = (clone $ventasDelDia)->sum('total');
        $operacionesHoy = $ventasDelDia->count();

        $fechaInicioVentas = $now->copy()->startOfDay()->subDays(6);
        $ventasPorDia = Venta::query()
            ->where('estado', 'emitida')
            ->whereBetween('fecha_emision', [$fechaInicioVentas->toDateString(), $now->toDateString()])
            ->selectRaw('fecha_emision, SUM(total) as total')
            ->groupBy('fecha_emision')
            ->pluck('total', 'fecha_emision');

        $resumenVentas = collect(range(0, 6))->map(function (int $dia) use ($fechaInicioVentas, $ventasPorDia): array {
            $fecha = $fechaInicioVentas->copy()->addDays($dia);

            return [
                'fecha' => $fecha->format('d/m'),
                'total' => (float) $ventasPorDia->get($fecha->toDateString(), 0),
            ];
        });

        $ventasChartLabels = $resumenVentas->pluck('fecha')->all();
        $ventasChartData = $resumenVentas->pluck('total')->all();

        $alquileresPorDia = Alquiler::query()
            ->whereBetween('created_at', [
                $fechaInicioVentas->copy()->startOfDay(),
                $now->copy()->endOfDay(),
            ])
            ->selectRaw('DATE(created_at) as fecha, SUM(total_alquiler) as total')
            ->groupByRaw('DATE(created_at)')
            ->pluck('total', 'fecha');

        $alquileresChartData = collect(range(0, 6))->map(function (int $dia) use ($fechaInicioVentas, $alquileresPorDia): float {
            $fecha = $fechaInicioVentas->copy()->addDays($dia);

            return (float) $alquileresPorDia->get($fecha->toDateString(), 0);
        })->all();

        $alquileresVigentes = Alquiler::query()
            ->where('estado', '!=', 'devuelto')
            ->whereDate('fecha_pactada_devolucion', '>=', $now->toDateString())
            ->count();
        $alquileresVencidos = Alquiler::query()
            ->where('estado', '!=', 'devuelto')
            ->whereDate('fecha_pactada_devolucion', '<', $now->toDateString())
            ->count();

        $totalMorasPendientes = (float) Cobranza::query()
            ->where('estado', 'pendiente')
            ->selectRaw('COALESCE(SUM(monto_mora - monto_pagado), 0) as saldo_pendiente')
            ->value('saldo_pendiente');
        $totalMorasPendientes = max($totalMorasPendientes, 0);

        $cajasAbiertas = Caja::where('estado', 'abierta')->get()->keyBy('tipo_caja');

        $ultimasVentas = Venta::with(['cliente', 'usuario'])->latest('id_venta')->take(5)->get();
        $ultimosAlquileres = Alquiler::with(['cliente', 'producto'])->latest('id_alquiler')->take(5)->get();
        $cuentasPorPagar = 0.0;

        if ($user->esAdmin() || $user->esAlmacenero()) {
            $totalCompras = (float) Compra::query()->where('estado', 'recibida')->sum('total');
            $totalPagadoAProveedores = (float) MovimientoCaja::query()
                ->whereNotNull('id_compra')
                ->whereHas('compra', fn (Builder $query) => $query->where('estado', 'recibida'))
                ->sum('monto');
            $cuentasPorPagar = max(round($totalCompras - $totalPagadoAProveedores, 2), 0);
        }

        return view('dashboard', compact(
            'totalProductos', 'stockBajo', 'productosPorReponer', 'porVencer', 'ventasHoy', 'operacionesHoy',
            'ventasChartLabels', 'ventasChartData',
            'alquileresChartData',
            'alquileresVigentes', 'alquileresVencidos', 'totalMorasPendientes',
            'cajasAbiertas', 'ultimasVentas', 'ultimosAlquileres', 'cuentasPorPagar', 'user'
        ));
    }
}
