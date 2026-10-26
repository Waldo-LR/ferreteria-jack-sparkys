<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Caja;
use App\Models\Serie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $query = Venta::with(['cliente', 'usuario', 'caja'])->latest('id_venta');

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function ($q) use ($b) {
                $q->where('serie', 'like', "%$b%")
                  ->orWhere('numero', 'like', "%$b%")
                  ->orWhereHas('cliente', function ($qc) use ($b) {
                      $qc->where('nombres', 'like', "%$b%")
                         ->orWhere('dni_ruc', 'like', "%$b%");
                  });
            });
        }

        if ($request->filled('tipo_comprobante')) {
            $query->where('tipo_comprobante', $request->tipo_comprobante);
        }

        $ventas = $query->paginate(15);
        return view('ventas.index', compact('ventas'));
    }

    public function pos()
    {
        $cajaActual = Caja::where('estado', 'abierta')->first();
        $productos = Producto::with(['categoria', 'marca'])->where('activo', 1)->orderBy('nombre')->get();
        $clientes = Cliente::where('activo', 1)->orderBy('nombres')->get();
        
        $serieBoleta = Serie::where('tipo_comprobante', 'boleta')->where('activo', 1)->first();
        $serieFactura = Serie::where('tipo_comprobante', 'factura')->where('activo', 1)->first();
        $serieNota = Serie::where('tipo_comprobante', 'nota_venta')->where('activo', 1)->first();

        $nroVenta = 'VTA-' . date('Y') . '-' . str_pad((Venta::max('id_venta') ?? 0) + 1, 5, '0', STR_PAD_LEFT);

        return view('ventas.pos', compact(
            'cajaActual', 'productos', 'clientes',
            'serieBoleta', 'serieFactura', 'serieNota', 'nroVenta'
        ));
    }
    public function store(Request $request)
    {
        $request->validate([
            'id_cliente' => 'required|integer',
            'tipo_comprobante' => 'required|in:nota_venta,boleta,factura',
            'medio_pago' => 'required|in:efectivo,yape,deposito',
            'productos' => 'required|array|min:1',
            'productos.*.id_producto' => 'required|integer',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        $caja = Caja::where('estado', 'abierta')->first();
        if (!$caja) {
            return back()->with('error', 'No hay ninguna caja abierta. Debe aperturar caja antes de emitir ventas.');
        }

        return DB::transaction(function () use ($request, $caja) {
            $now = Carbon::now();

            $serieRecord = Serie::where('tipo_comprobante', $request->tipo_comprobante)->where('activo', 1)->first();
            $serie = $serieRecord ? $serieRecord->serie : '001';
            $numero = $serieRecord ? ($serieRecord->ultimo_numero + 1) : 1;
            if ($serieRecord) {
                $serieRecord->update(['ultimo_numero' => $numero]);
            }

            $total = 0;
            $itemsDetalle = [];

            foreach ($request->productos as $item) {
                $prod = Producto::lockForUpdate()->findOrFail($item['id_producto']);
                if ($prod->stock < $item['cantidad']) {
                    throw new \Exception("Stock insuficiente para: {$prod->nombre}. Stock disponible: {$prod->stock}");
                }

                $subtotal = round($item['cantidad'] * $item['precio_unitario'], 2);
                $total += $subtotal;

                $prod->decrement('stock', $item['cantidad']);

                DB::table('tmovimientosstock')->insert([
                    'id_producto' => $prod->id_producto,
                    'id_usuario' => Auth::id() ?? 1,
                    'tipo' => 'salida',
                    'cantidad' => $item['cantidad'],
                    'stock_resultante' => $prod->stock,
                    'motivo' => 'Venta en Mostrador ' . $serie . '-' . $numero,
                    'referencia' => $serie . '-' . $numero,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $itemsDetalle[] = [
                    'id_producto' => $prod->id_producto,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'costo_unitario' => $prod->precio_compra,
                    'subtotal' => $subtotal,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($request->tipo_comprobante === 'factura') {
                $valorVenta = round($total / 1.18, 2);
                $igv = round($total - $valorVenta, 2);
            } else {
                $valorVenta = $total;
                $igv = 0.00;
            }

            $montoPagado = floatval($request->paga_con ?? $total);
            $vuelto = max(0, round($montoPagado - $total, 2));

            $venta = Venta::create([
                'id_caja' => $caja->id_caja,
                'id_cliente' => $request->id_cliente,
                'id_usuario' => Auth::id() ?? 1,
                'tipo_comprobante' => $request->tipo_comprobante,
                'serie' => $serie,
                'numero' => $numero,
                'fecha_emision' => $request->fecha_emision ?? $now->toDateString(),
                'medio_pago' => $request->medio_pago,
                'valor_venta' => $valorVenta,
                'igv' => $igv,
                'total' => $total,
                'monto_pagado' => $montoPagado,
                'vuelto' => $vuelto,
                'estado' => 'emitida',
                'estado_sunat' => 'simulado',
            ]);

            foreach ($itemsDetalle as &$it) {
                $it['id_venta'] = $venta->id_venta;
            }
            DetalleVenta::insert($itemsDetalle);

            if ($request->medio_pago === 'efectivo') {
                $caja->increment('total_ingresos', $total);
                $caja->increment('ingresos_efectivo', $total);
                $caja->increment('monto_final', $total);
            } else {
                $caja->increment('total_ingresos', $total);
            }

            DB::table('tauditoria')->insert([
                'id_usuario' => Auth::id() ?? 1,
                'accion' => 'VENTA_REGISTRADA',
                'tabla_afectada' => 'tventas',
                'id_registro' => $venta->id_venta,
                'detalle' => "Venta {$serie}-{$numero} por S/ {$total} ({$request->tipo_comprobante})",
                'ip' => $request->ip(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return redirect()->route('ventas.imprimir', $venta->id_venta)
                ->with('success', "¡Venta registrada con éxito! Comprobante emitido: {$serie}-" . str_pad($numero, 6, '0', STR_PAD_LEFT));
        });
    }
    public function imprimir($id)
    {
        $venta = Venta::with(['cliente', 'usuario', 'detalles.producto', 'caja'])->findOrFail($id);
        $config = DB::table('tconfiguracion')->first();
        return view('ventas.comprobante', compact('venta', 'config'));
    }

    public function alertarAlmacen(Request $request)
    {
        $request->validate(['id_producto' => 'required|integer']);
        $prod = Producto::findOrFail($request->id_producto);

        DB::table('tnotificaciones')->insert([
            'id_producto' => $prod->id_producto,
            'id_cliente' => null,
            'id_usuario' => Auth::id() ?? 1,
            'tipo' => 'alerta_almacen',
            'atendida' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Se ha enviado una alerta inmediata al área de Almacén para reponer stock de: {$prod->nombre}"
        ]);
    }

    public function notificarCliente(Request $request)
    {
        $request->validate([
            'id_producto' => 'required|integer',
            'id_cliente' => 'required|integer'
        ]);

        $prod = Producto::findOrFail($request->id_producto);
        $cli = Cliente::findOrFail($request->id_cliente);

        DB::table('tnotificaciones')->insert([
            'id_producto' => $prod->id_producto,
            'id_cliente' => $cli->id_cliente,
            'id_usuario' => Auth::id() ?? 1,
            'tipo' => 'aviso_cliente',
            'atendida' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Aviso programado: El cliente {$cli->nombres} recibirá un aviso a {$cli->email} cuando ingrese stock de {$prod->nombre}."
        ]);
    }

    public function anular(Request $request, $id)
    {
        if (!Auth::user()->esAdmin()) {
            return back()->with('error', 'Acción denegada: Únicamente el Administrador puede anular ventas.');
        }

        $venta = Venta::with('detalles.producto')->findOrFail($id);
        if ($venta->estado === 'anulada') {
            return back()->with('error', 'Esta venta ya se encuentra anulada.');
        }

        DB::transaction(function () use ($venta, $request) {
            $now = Carbon::now();
            $venta->update([
                'estado' => 'anulada',
                'motivo_anulacion' => $request->motivo ?? 'Anulación autorizada por Administrador'
            ]);

            foreach ($venta->detalles as $det) {
                if ($det->producto) {
                    $det->producto->increment('stock', $det->cantidad);

                    DB::table('tmovimientosstock')->insert([
                        'id_producto' => $det->id_producto,
                        'id_usuario' => Auth::id(),
                        'tipo' => 'entrada',
                        'cantidad' => $det->cantidad,
                        'stock_resultante' => $det->producto->stock,
                        'motivo' => 'Anulación de Venta ' . $venta->serie . '-' . $venta->numero,
                        'referencia' => $venta->serie . '-' . $venta->numero,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            DB::table('tauditoria')->insert([
                'id_usuario' => Auth::id(),
                'accion' => 'VENTA_ANULADA',
                'tabla_afectada' => 'tventas',
                'id_registro' => $venta->id_venta,
                'detalle' => "Anulación de comprobante {$venta->serie}-{$venta->numero} por S/ {$venta->total}",
                'ip' => $request->ip(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        return back()->with('success', 'Venta anulada correctamente y stock reincorporado al inventario.');
    }
}