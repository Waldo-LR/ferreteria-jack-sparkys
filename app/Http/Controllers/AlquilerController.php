<?php

namespace App\Http\Controllers;

use App\Models\Alquiler;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Caja;
use App\Models\Cobranza;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlquilerController extends Controller
{
    public function index(Request $request)
    {
        $query = Alquiler::with(['cliente', 'producto', 'usuario'])->latest('id_alquiler');

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function ($q) use ($b) {
                $q->whereHas('cliente', function ($qc) use ($b) {
                    $qc->where('nombres', 'like', "%$b%")
                       ->orWhere('dni_ruc', 'like', "%$b%");
                })->orWhereHas('producto', function ($qp) use ($b) {
                    $qp->where('nombre', 'like', "%$b%")
                       ->orWhere('codigo', 'like', "%$b%");
                });
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $alquileres = $query->paginate(15);
        $herramientas = Producto::where('activo', 1)->where('es_alquilable', 1)->where('stock', '>', 0)->get();
        $clientes = Cliente::where('activo', 1)->orderBy('nombres')->get();
        $cajaActual = Caja::where('estado', 'abierta')->first();

        $clientesBloqueados = Alquiler::where('estado', 'vencido')
            ->pluck('id_cliente')
            ->unique()
            ->toArray();

        return view('alquileres.index', compact('alquileres', 'herramientas', 'clientes', 'cajaActual', 'clientesBloqueados'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'id_cliente' => 'required|integer',
            'id_producto' => 'required|integer',
            'dias_pactados' => 'required|integer|min:1',
            'fecha_entrega' => 'required|date',
            'medio_pago' => 'required|in:efectivo,yape,deposito',
            'ubicacion_herramienta' => 'nullable|string|max:200',
            'observaciones' => 'nullable|string',
        ]);

        $caja = Caja::where('estado', 'abierta')->first();
        if (!$caja) {
            return back()->with('error', 'Debe haber una caja abierta para procesar el pago del alquiler.');
        }

        // REGLA DE NEGOCIO CRÍTICA: BLOQUEO DE CLIENTE CON MORA
        $alquilerVencido = Alquiler::with(['producto', 'cliente'])
            ->where('id_cliente', $request->id_cliente)
            ->where('estado', 'vencido')
            ->first();

        if ($alquilerVencido) {
            $mora = Cobranza::where('id_alquiler', $alquilerVencido->id_alquiler)->first();
            $montoMora = $mora ? $mora->monto_mora : 0;
            $msg = "⚠️ ALQUILER BLOQUEADO: El cliente '{$alquilerVencido->cliente->nombres}' registra el alquiler vencido #ALQ-{$alquilerVencido->id_alquiler} ({$alquilerVencido->producto->nombre}) pendiente desde el {$alquilerVencido->fecha_pactada_devolucion} con mora de S/ " . number_format($montoMora, 2) . ". El sistema NO permite nuevos alquileres hasta devolver el equipo y cancelar la mora.";
            return back()->with('error', $msg)->withInput();
        }

        $producto = Producto::findOrFail($request->id_producto);
        if (!$producto->es_alquilable) {
            return back()->with('error', 'El producto seleccionado no está configurado para alquiler.');
        }

        if ($producto->stock < 1) {
            return back()->with('error', "No hay unidades disponibles de {$producto->nombre} en almacén.");
        }

        return DB::transaction(function () use ($request, $producto, $caja) {
            $now = Carbon::now();
            $dias = intval($request->dias_pactados);
            $precioDia = $producto->precio_alquiler_dia > 0 ? $producto->precio_alquiler_dia : 25.00;
            $totalAlquiler = round($dias * $precioDia, 2);

            $fechaEntrega = Carbon::parse($request->fecha_entrega);
            $fechaPactada = $fechaEntrega->copy()->addDays($dias);

            $alquiler = Alquiler::create([
                'id_caja' => $caja->id_caja,
                'id_cliente' => $request->id_cliente,
                'id_producto' => $producto->id_producto,
                'id_usuario' => Auth::id() ?? 1,
                'cantidad' => 1,
                'precio_dia' => $precioDia,
                'dias_pactados' => $dias,
                'fecha_entrega' => $fechaEntrega->toDateString(),
                'fecha_pactada_devolucion' => $fechaPactada->toDateString(),
                'fecha_devolucion_real' => null,
                'total_alquiler' => $totalAlquiler,
                'monto_pagado' => $totalAlquiler,
                'medio_pago' => $request->medio_pago,
                'ubicacion_herramienta' => $request->ubicacion_herramienta ?? 'En obra del cliente',
                'estado' => 'vigente',
                'observaciones' => $request->observaciones,
            ]);

            $producto->decrement('stock', 1);

            DB::table('tmovimientosstock')->insert([
                'id_producto' => $producto->id_producto,
                'id_usuario' => Auth::id() ?? 1,
                'tipo' => 'salida',
                'cantidad' => 1,
                'stock_resultante' => $producto->stock,
                'motivo' => "Salida por Alquiler #ALQ-{$alquiler->id_alquiler}",
                'referencia' => "ALQ-{$alquiler->id_alquiler}",
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($request->medio_pago === 'efectivo') {
                $caja->increment('total_ingresos', $totalAlquiler);
                $caja->increment('ingresos_efectivo', $totalAlquiler);
                $caja->increment('monto_final', $totalAlquiler);
            } else {
                $caja->increment('total_ingresos', $totalAlquiler);
            }

            DB::table('tauditoria')->insert([
                'id_usuario' => Auth::id() ?? 1,
                'accion' => 'ALQUILER_CREADO',
                'tabla_afectada' => 'talquileres',
                'id_registro' => $alquiler->id_alquiler,
                'detalle' => "Alquiler #ALQ-{$alquiler->id_alquiler} de {$producto->nombre} por {$dias} días (S/ {$totalAlquiler})",
                'ip' => $request->ip(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return redirect()->route('alquileres.index')
                ->with('success', "¡Alquiler registrado exitosamente! Código: #ALQ-{$alquiler->id_alquiler}. Devolución pactada: {$fechaPactada->toDateString()}");
        });
    }
    public function devolver(Request $request, $id)
    {
        $alquiler = Alquiler::with(['producto', 'cliente'])->findOrFail($id);

        if ($alquiler->estado === 'devuelto') {
            return back()->with('error', 'Este equipo ya fue devuelto con anterioridad.');
        }

        DB::transaction(function () use ($alquiler, $request) {
            $now = Carbon::now();
            $hoy = Carbon::today();

            if ($alquiler->producto) {
                $alquiler->producto->increment('stock', 1);

                DB::table('tmovimientosstock')->insert([
                    'id_producto' => $alquiler->id_producto,
                    'id_usuario' => Auth::id() ?? 1,
                    'tipo' => 'entrada',
                    'cantidad' => 1,
                    'stock_resultante' => $alquiler->producto->stock,
                    'motivo' => "Reingreso por Devolución de Alquiler #ALQ-{$alquiler->id_alquiler}",
                    'referencia' => "DEV-{$alquiler->id_alquiler}",
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $cobranza = Cobranza::where('id_alquiler', $alquiler->id_alquiler)->first();
            if ($cobranza && $request->has('pagar_mora')) {
                $cobranza->update([
                    'estado' => 'pagado',
                    'monto_pagado' => $cobranza->monto_mora,
                ]);
            }

            $alquiler->update([
                'fecha_devolucion_real' => $hoy->toDateString(),
                'estado' => 'devuelto',
                'observaciones_devolucion' => $request->observaciones_devolucion ?? 'Herramienta devuelta e inspeccionada en buen estado.',
                'id_usuario_devolucion' => Auth::id() ?? 1,
            ]);

            DB::table('tauditoria')->insert([
                'id_usuario' => Auth::id() ?? 1,
                'accion' => 'ALQUILER_DEVUELTO',
                'tabla_afectada' => 'talquileres',
                'id_registro' => $alquiler->id_alquiler,
                'detalle' => "Devolución de equipo #ALQ-{$alquiler->id_alquiler} ({$alquiler->producto->nombre}). Obs: {$request->observaciones_devolucion}",
                'ip' => $request->ip(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        return back()->with('success', '¡Herramienta reincorporada al inventario y devolución registrada correctamente!');
    }
}