<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use App\Models\Alquiler;
use App\Models\Cobranza;
use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
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

        $totalVentas = Venta::where('estado', 'emitida')->sum('total');
        $ventasHoy = Venta::where('estado', 'emitida')->whereDate('fecha_emision', $now->toDateString())->sum('total');

        $alquileresVigentes = Alquiler::where('estado', 'vigente')->count();
        $alquileresVencidos = Alquiler::with(['cliente', 'producto'])->where('estado', 'vencido')->get();

        $totalMorasPendientes = Cobranza::where('estado', 'pendiente')->sum('monto_mora');

        $cajaActual = Caja::where('estado', 'abierta')->first();

        $ultimasVentas = Venta::with(['cliente', 'usuario'])->latest('id_venta')->take(5)->get();
        $ultimosAlquileres = Alquiler::with(['cliente', 'producto'])->latest('id_alquiler')->take(5)->get();

        return view('dashboard', compact(
            'totalProductos', 'stockBajo', 'porVencer', 'totalVentas', 'ventasHoy',
            'alquileresVigentes', 'alquileresVencidos', 'totalMorasPendientes',
            'cajaActual', 'ultimasVentas', 'ultimosAlquileres', 'user'
        ));
    }
}

