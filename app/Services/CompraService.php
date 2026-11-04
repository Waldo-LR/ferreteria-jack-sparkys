<?php

namespace App\Services;

use App\Models\Caja;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\MovimientoCaja;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompraService
{
    private const STOCK_MAXIMO = 4294967295;

    private const IMPORTE_MAXIMO_EN_CENTIMOS = 999999999999;

    public function __construct(
        private CajaService $cajaService,
        private MovimientoStockService $movimientoStockService
    ) {}

    /**
     * @param  array{
     *     id_proveedor: int,
     *     tipo_comprobante: string,
     *     numero_comprobante: string,
     *     fecha_compra: string,
     *     detalles: array<int, array{id_producto: int, cantidad: int, precio_unitario: string|float|int}>,
     *     monto_pagado?: string|float|int|null,
     *     medio_pago?: string|null,
     *     observaciones?: string|null
     * }  $datos
     */
    public function registrarCompra(array $datos, Usuario $usuario): Compra
    {
        return DB::transaction(function () use ($datos, $usuario): Compra {
            $detalles = $datos['detalles'];
            $idsProducto = collect($detalles)->pluck('id_producto')->map(fn ($id): int => (int) $id)->all();
            $productos = Producto::query()
                ->whereIn('id_producto', $idsProducto)
                ->orderBy('id_producto')
                ->lockForUpdate()
                ->get()
                ->keyBy('id_producto');

            if ($productos->count() !== count($idsProducto)) {
                throw ValidationException::withMessages([
                    'detalles' => 'Uno o más productos ya no están disponibles.',
                ]);
            }

            $lineas = [];
            $totalCentimos = 0;

            foreach ($detalles as $detalle) {
                $producto = $productos->get((int) $detalle['id_producto']);

                if (! $producto || ! $producto->activo) {
                    throw ValidationException::withMessages([
                        'detalles' => 'Uno o más productos ya no están disponibles.',
                    ]);
                }

                $cantidad = (int) $detalle['cantidad'];
                $precioCentimos = $this->aCentimos($detalle['precio_unitario']);

                if ($precioCentimos > intdiv(self::IMPORTE_MAXIMO_EN_CENTIMOS, $cantidad)) {
                    throw ValidationException::withMessages([
                        'detalles' => 'El subtotal de una línea supera el importe máximo permitido.',
                    ]);
                }

                $subtotalCentimos = $cantidad * $precioCentimos;
                $totalCentimos += $subtotalCentimos;

                if ($totalCentimos > self::IMPORTE_MAXIMO_EN_CENTIMOS) {
                    throw ValidationException::withMessages([
                        'detalles' => 'El total de la compra supera el importe máximo permitido.',
                    ]);
                }

                $lineas[] = [
                    'producto' => $producto,
                    'cantidad' => $cantidad,
                    'precio_centimos' => $precioCentimos,
                    'subtotal_centimos' => $subtotalCentimos,
                ];
            }

            $montoPagadoCentimos = $this->aCentimos($datos['monto_pagado'] ?? 0);

            if ($montoPagadoCentimos > $totalCentimos) {
                throw ValidationException::withMessages([
                    'monto_pagado' => 'El pago inicial no puede superar el total de la compra.',
                ]);
            }

            $compra = Compra::create([
                'id_proveedor' => $datos['id_proveedor'],
                'id_usuario' => $usuario->id_usuario,
                'tipo_comprobante' => $datos['tipo_comprobante'],
                'numero_comprobante' => $datos['numero_comprobante'],
                'fecha_compra' => $datos['fecha_compra'],
                'total' => $this->formatearCentimos($totalCentimos),
                'estado' => 'recibida',
                'observaciones' => $datos['observaciones'] ?? null,
            ]);

            foreach ($lineas as $linea) {
                /** @var Producto $producto */
                $producto = $linea['producto'];
                $stockAnterior = (int) $producto->stock;
                $cantidad = $linea['cantidad'];

                if ($stockAnterior > self::STOCK_MAXIMO - $cantidad) {
                    throw ValidationException::withMessages([
                        'detalles' => "El stock de {$producto->nombre} excedería el máximo permitido.",
                    ]);
                }

                $stockResultante = $stockAnterior + $cantidad;
                $costoAnteriorCentimos = $this->aCentimos($producto->precio_compra);
                $costoPromedioCentimos = (int) round(
                    $costoAnteriorCentimos
                        + ($cantidad / $stockResultante)
                        * ($linea['precio_centimos'] - $costoAnteriorCentimos)
                );

                DetalleCompra::create([
                    'id_compra' => $compra->id_compra,
                    'id_producto' => $producto->id_producto,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $this->formatearCentimos($linea['precio_centimos']),
                    'subtotal' => $this->formatearCentimos($linea['subtotal_centimos']),
                ]);

                $this->movimientoStockService->registrar(
                    $producto,
                    $usuario,
                    'entrada',
                    $cantidad,
                    "Ingreso por compra #{$compra->id_compra}",
                    "COMPRA-{$compra->id_compra}"
                );
                $producto->update([
                    'precio_compra' => $this->formatearCentimos($costoPromedioCentimos),
                ]);
            }

            if ($montoPagadoCentimos > 0) {
                $medioPago = $datos['medio_pago'] ?? null;

                if (! $medioPago) {
                    throw ValidationException::withMessages([
                        'medio_pago' => 'Selecciona el medio de pago para registrar el pago inicial.',
                    ]);
                }

                $this->registrarPagoDentroDeTransaccion(
                    $compra,
                    $montoPagadoCentimos,
                    $medioPago,
                    $usuario
                );
            }

            return $this->cargarCompra($compra);
        }, 3);
    }

    public function registrarPago(
        Compra $compra,
        string|float|int $monto,
        string $medioPago,
        Usuario $usuario
    ): MovimientoCaja {
        return DB::transaction(function () use ($compra, $monto, $medioPago, $usuario): MovimientoCaja {
            $compraBloqueada = Compra::query()
                ->lockForUpdate()
                ->findOrFail($compra->id_compra);

            return $this->registrarPagoDentroDeTransaccion(
                $compraBloqueada,
                $this->aCentimos($monto),
                $medioPago,
                $usuario
            );
        }, 3);
    }

    private function registrarPagoDentroDeTransaccion(
        Compra $compra,
        int $montoCentimos,
        string $medioPago,
        Usuario $usuario
    ): MovimientoCaja {
        if ($compra->estado !== 'recibida') {
            throw ValidationException::withMessages([
                'compra' => 'Solo se pueden registrar pagos para compras recibidas por el módulo de Inventario.',
            ]);
        }

        $montoPagadoCentimos = $this->aCentimos($compra->pagos()->sum('monto'));
        $saldoPendienteCentimos = $this->aCentimos($compra->total) - $montoPagadoCentimos;

        if ($montoCentimos <= 0 || $montoCentimos > $saldoPendienteCentimos) {
            throw ValidationException::withMessages([
                'monto' => 'El pago debe ser mayor que cero y no puede superar el saldo pendiente.',
            ]);
        }

        if (! in_array($medioPago, Caja::MEDIOS_PAGO, true)) {
            throw ValidationException::withMessages([
                'medio_pago' => 'El medio de pago seleccionado no es válido.',
            ]);
        }

        $caja = $this->cajaService->cajaAbierta(Caja::TIPO_VENTAS, true);

        if (! $caja) {
            throw ValidationException::withMessages([
                'caja' => 'Abre la Caja de Venta de Productos para registrar pagos a proveedores.',
            ]);
        }

        $monto = $montoCentimos / 100;
        $movimiento = $compra->pagos()->create([
            'id_caja' => $caja->id_caja,
            'id_usuario' => $usuario->id_usuario,
            'tipo_movimiento' => 'egreso',
            'categoria' => 'Pago a proveedor',
            'monto' => $this->formatearCentimos($montoCentimos),
            'medio_pago' => $medioPago,
            'detalle' => "Pago de compra #{$compra->id_compra} · {$compra->numero_comprobante}",
        ]);

        $this->cajaService->registrarEgreso($caja, $monto, $medioPago);

        return $movimiento;
    }

    private function cargarCompra(Compra $compra): Compra
    {
        return Compra::query()
            ->with(['proveedor', 'usuario', 'detalles.producto', 'pagos.usuario', 'pagos.caja'])
            ->withSum('pagos as monto_pagado', 'monto')
            ->findOrFail($compra->id_compra);
    }

    private function aCentimos(string|float|int $monto): int
    {
        return (int) round((float) $monto * 100);
    }

    private function formatearCentimos(int $monto): string
    {
        return sprintf('%d.%02d', intdiv($monto, 100), $monto % 100);
    }
}
