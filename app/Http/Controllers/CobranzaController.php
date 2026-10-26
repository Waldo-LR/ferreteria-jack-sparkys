<?php

namespace App\Http\Controllers;

use App\Models\Cobranza;
use App\Models\PagoCobranza;
use App\Models\Alquiler;
use App\Models\Caja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CobranzaController extends Controller
{
    public function index(Request $request)
    {
        $query = Cobranza::with(['alquiler.cliente', 'alquiler.producto', 'usuario'])->latest('id_cobranza');

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->whereHas('alquiler.cliente', function ($q) use ($b) {
                $q->where('nombres', 'like', "%$b%")
                  ->orWhere('dni_ruc', 'like', "%$b%")
                  ->orWhere('direccion', 'like', "%$b%");
            })->orWhereHas('alquiler', function ($q) use ($b) {
                $q->where('ubicacion_herramienta', 'like', "%$b%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $cobranzas = $query->paginate(15);
        $totalPendiente = Cobranza::where('estado', 'pendiente')->sum('monto_mora');
        $alquileresVencidos = Alquiler::with(['cliente', 'producto'])->where('estado', 'vencido')->get();

        return view('cobranzas.index', compact('cobranzas', 'totalPendiente', 'alquileresVencidos'));
    }
    public function pagar(Request $request, $id)
    {
        $cobranza = Cobranza::with(['alquiler.cliente'])->findOrFail($id);

        $request->validate([
            'monto' => 'required|numeric|min:0.5',
            'medio_pago' => 'required|in:efectivo,yape,deposito',
            'observaciones' => 'nullable|string'
        ]);

        $caja = Caja::where('estado', 'abierta')->first();
        if (!$caja) {
            return back()->with('error', 'Debe haber una caja abierta para procesar el pago de mora.');
        }

        DB::transaction(function () use ($cobranza, $request, $caja) {
            $now = Carbon::now();
            $monto = floatval($request->monto);

            PagoCobranza::create([
                'id_cobranza' => $cobranza->id_cobranza,
                'id_usuario' => Auth::id() ?? 1,
                'id_caja' => $caja->id_caja,
                'monto' => $monto,
                'medio_pago' => $request->medio_pago,
                'observaciones' => $request->observaciones ?? 'Pago de saldo por mora',
            ]);

            $nuevoPagado = $cobranza->monto_pagado + $monto;
            $estado = ($nuevoPagado >= $cobranza->monto_mora) ? 'pagado' : 'pendiente';

            $cobranza->update([
                'monto_pagado' => $nuevoPagado,
                'estado' => $estado,
            ]);

            if ($request->medio_pago === 'efectivo') {
                $caja->increment('total_ingresos', $monto);
                $caja->increment('ingresos_efectivo', $monto);
                $caja->increment('monto_final', $monto);
            } else {
                $caja->increment('total_ingresos', $monto);
            }

            DB::table('tauditoria')->insert([
                'id_usuario' => Auth::id() ?? 1,
                'accion' => 'PAGO_MORA_REGISTRADO',
                'tabla_afectada' => 'tpagoscobranza',
                'id_registro' => $cobranza->id_cobranza,
                'detalle' => "Cobro de mora por S/ {$monto} a {$cobranza->alquiler->cliente->nombres}",
                'ip' => $request->ip(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        return back()->with('success', '¡Pago de mora registrado y adicionado a la caja con éxito!');
    }

    public function recuperar(Request $request, $id)
    {
        $alquiler = Alquiler::with(['producto', 'cliente'])->findOrFail($id);

        $request->validate([
            'observaciones' => 'required|string',
            'estado_fisico' => 'required|string'
        ]);

        DB::transaction(function () use ($alquiler, $request) {
            $now = Carbon::now();

            if ($alquiler->producto) {
                $alquiler->producto->increment('stock', 1);

                DB::table('tmovimientosstock')->insert([
                    'id_producto' => $alquiler->id_producto,
                    'id_usuario' => Auth::id() ?? 1,
                    'tipo' => 'entrada',
                    'cantidad' => 1,
                    'stock_resultante' => $alquiler->producto->stock,
                    'motivo' => "Recuperación de herramienta en campo #ALQ-{$alquiler->id_alquiler}",
                    'referencia' => "REC-{$alquiler->id_alquiler}",
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $alquiler->update([
                'fecha_devolucion_real' => Carbon::now()->toDateString(),
                'estado' => 'devuelto',
                'observaciones_devolucion' => "RECUPERADA EN CAMPO. Estado físico: {$request->estado_fisico}. Detalle: {$request->observaciones}",
                'id_usuario_devolucion' => Auth::id() ?? 1,
            ]);

            DB::table('tauditoria')->insert([
                'id_usuario' => Auth::id() ?? 1,
                'accion' => 'HERRAMIENTA_RECUPERADA',
                'tabla_afectada' => 'talquileres',
                'id_registro' => $alquiler->id_alquiler,
                'detalle' => "Recuperación en obra de {$alquiler->producto->nombre}. Obs: {$request->observaciones}",
                'ip' => $request->ip(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        return back()->with('success', 'Herramienta recuperada exitosamente y devuelta al stock del almacén.');
    }
}