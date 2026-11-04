<?php

namespace App\Services;

use App\Models\Alquiler;
use App\Models\Auditoria;
use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Cobranza;
use App\Models\Configuracion;
use App\Models\PagoCobranza;
use App\Models\Producto;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AlquilerService
{
    private const MAXIMO_IMPORTE = 9999999999.99;

    public function __construct(
        private CajaService $cajaService,
        private MovimientoStockService $movimientoStockService
    ) {}

    /**
     * @param  array{
     *     id_cliente: int,
     *     id_producto: int,
     *     dias_pactados: int,
     *     fecha_entrega: string,
     *     monto_pagado: string|float|int,
     *     medio_pago_alquiler?: string|null,
     *     monto_garantia: string|float|int,
     *     medio_pago_garantia: string,
     *     ubicacion_herramienta?: string|null,
     *     observaciones?: string|null
     * }  $datos
     */
    public function registrar(array $datos, Usuario $usuario, ?string $ip): Alquiler
    {
        return DB::transaction(function () use ($datos, $usuario, $ip): Alquiler {
            $caja = $this->cajaService->cajaAbierta(Caja::TIPO_ALQUILERES, true);

            if (! $caja) {
                throw ValidationException::withMessages([
                    'caja' => 'Abre la Caja de Alquileres/Cobranzas antes de registrar un alquiler.',
                ]);
            }

            $cliente = Cliente::query()->lockForUpdate()->findOrFail($datos['id_cliente']);

            if (! $cliente->activo) {
                throw ValidationException::withMessages([
                    'id_cliente' => 'El cliente seleccionado está inactivo.',
                ]);
            }

            $tieneDevolucionPendiente = Alquiler::query()
                ->where('id_cliente', $cliente->id_cliente)
                ->where('estado', '!=', 'devuelto')
                ->lockForUpdate()
                ->exists();
            $tieneSaldoPendiente = Cobranza::query()
                ->whereHas('alquiler', fn ($query) => $query->where('id_cliente', $cliente->id_cliente))
                ->where('estado', 'pendiente')
                ->whereColumn('monto_pagado', '<', 'monto_mora')
                ->exists();

            if ($tieneDevolucionPendiente || $tieneSaldoPendiente) {
                throw ValidationException::withMessages([
                    'id_cliente' => 'No se puede registrar otro alquiler: el cliente tiene una devolución pendiente o una deuda con la empresa.',
                ]);
            }

            $producto = Producto::query()
                ->lockForUpdate()
                ->findOrFail($datos['id_producto']);

            if (! $producto->activo || ! $producto->es_alquilable) {
                throw ValidationException::withMessages([
                    'id_producto' => 'El producto seleccionado no está activo o no está habilitado para alquiler.',
                ]);
            }

            if ($producto->stock < 1) {
                throw ValidationException::withMessages([
                    'id_producto' => 'No hay unidades disponibles de este equipo para alquilar.',
                ]);
            }

            $precioDiarioCentimos = $this->aCentimos($producto->precio_alquiler_dia);
            $totalCentimos = $precioDiarioCentimos * (int) $datos['dias_pactados'];
            $pagoInicialCentimos = $this->aCentimos($datos['monto_pagado']);
            $garantiaCentimos = $this->aCentimos($datos['monto_garantia']);

            if (
                $precioDiarioCentimos <= 0
                || $totalCentimos > $this->aCentimos(self::MAXIMO_IMPORTE)
                || $pagoInicialCentimos > $totalCentimos
            ) {
                throw ValidationException::withMessages([
                    'monto_pagado' => 'Verifica la tarifa diaria y que el pago inicial no supere el total del alquiler.',
                ]);
            }

            $fechaEntrega = Carbon::parse($datos['fecha_entrega']);
            $fechaPactada = $fechaEntrega->copy()->addDays((int) $datos['dias_pactados']);

            $alquiler = Alquiler::create([
                'id_caja' => $caja->id_caja,
                'id_cliente' => $cliente->id_cliente,
                'id_producto' => $producto->id_producto,
                'id_usuario' => $usuario->id_usuario,
                'cantidad' => 1,
                'precio_dia' => $this->formatearCentimos($precioDiarioCentimos),
                'dias_pactados' => (int) $datos['dias_pactados'],
                'fecha_entrega' => $fechaEntrega->toDateString(),
                'fecha_pactada_devolucion' => $fechaPactada->toDateString(),
                'total_alquiler' => $this->formatearCentimos($totalCentimos),
                'monto_pagado' => $this->formatearCentimos($pagoInicialCentimos),
                'monto_garantia' => $this->formatearCentimos($garantiaCentimos),
                'medio_pago' => $datos['medio_pago_alquiler'] ?? 'pendiente',
                'medio_pago_garantia' => $datos['medio_pago_garantia'],
                'ubicacion_herramienta' => $datos['ubicacion_herramienta'] ?? 'En obra del cliente',
                'estado' => 'vigente',
                'observaciones' => $datos['observaciones'] ?? null,
            ]);

            $this->movimientoStockService->registrar(
                $producto,
                $usuario,
                'salida',
                1,
                "Salida por alquiler #{$alquiler->id_alquiler}",
                "ALQ-{$alquiler->id_alquiler}"
            );

            $saldoAlquilerCentimos = $totalCentimos - $pagoInicialCentimos;
            $cobranzaAlquiler = Cobranza::create([
                'id_alquiler' => $alquiler->id_alquiler,
                'id_usuario' => $usuario->id_usuario,
                'tipo' => Cobranza::TIPO_ALQUILER,
                'monto_mora' => $this->formatearCentimos($totalCentimos),
                'monto_pagado' => $this->formatearCentimos($pagoInicialCentimos),
                'estado' => $saldoAlquilerCentimos > 0 ? 'pendiente' : 'pagado',
                'observaciones' => 'Cobro del servicio de alquiler.',
            ]);

            if ($pagoInicialCentimos > 0) {
                PagoCobranza::create([
                    'id_cobranza' => $cobranzaAlquiler->id_cobranza,
                    'id_usuario' => $usuario->id_usuario,
                    'id_caja' => $caja->id_caja,
                    'monto' => $this->formatearCentimos($pagoInicialCentimos),
                    'medio_pago' => $datos['medio_pago_alquiler'],
                    'observaciones' => "Pago inicial del alquiler #{$alquiler->id_alquiler}",
                ]);

                $this->cajaService->registrarIngreso(
                    $caja,
                    $pagoInicialCentimos / 100,
                    $datos['medio_pago_alquiler']
                );
            }

            $montoGarantia = $this->formatearCentimos($garantiaCentimos);
            $caja->movimientos()->create([
                'id_usuario' => $usuario->id_usuario,
                'id_alquiler' => $alquiler->id_alquiler,
                'tipo_movimiento' => 'ingreso',
                'categoria' => 'Garantía de alquiler',
                'monto' => $montoGarantia,
                'medio_pago' => $datos['medio_pago_garantia'],
                'detalle' => "Garantía en custodia del alquiler #{$alquiler->id_alquiler}.",
            ]);
            $this->cajaService->registrarIngreso(
                $caja,
                $garantiaCentimos / 100,
                $datos['medio_pago_garantia']
            );

            Auditoria::create([
                'id_usuario' => $usuario->id_usuario,
                'accion' => 'ALQUILER_CREADO',
                'tabla_afectada' => 'talquileres',
                'id_registro' => $alquiler->id_alquiler,
                'detalle' => "Alquiler #{$alquiler->id_alquiler}; equipo {$producto->nombre}; total S/ {$alquiler->total_alquiler}; garantía S/ {$alquiler->monto_garantia}.",
                'ip' => $ip,
            ]);

            return $alquiler->load(['cliente', 'producto', 'usuario', 'cobranzas.pagos']);
        }, 3);
    }

    /**
     * @param  array{monto_dano: string|float|int, observaciones_devolucion: string}  $datos
     */
    public function devolver(
        Alquiler $alquiler,
        array $datos,
        Usuario $usuario,
        ?string $ip
    ): Alquiler {
        return DB::transaction(function () use ($alquiler, $datos, $usuario, $ip): Alquiler {
            $alquiler = Alquiler::query()
                ->with(['producto', 'cliente'])
                ->lockForUpdate()
                ->findOrFail($alquiler->id_alquiler);

            if ($alquiler->estado === 'devuelto') {
                throw ValidationException::withMessages([
                    'alquiler' => 'Este equipo ya fue devuelto.',
                ]);
            }

            $montoDanoCentimos = $this->aCentimos($datos['monto_dano']);
            $garantiaCentimos = $this->aCentimos($alquiler->monto_garantia);
            $garantiaAplicadaCentimos = min($montoDanoCentimos, $garantiaCentimos);
            $garantiaADevolverCentimos = $garantiaCentimos - $garantiaAplicadaCentimos;
            $caja = null;

            if ($garantiaADevolverCentimos > 0) {
                $caja = $this->cajaService->cajaAbierta(Caja::TIPO_ALQUILERES, true);

                if (! $caja) {
                    throw ValidationException::withMessages([
                        'caja' => 'Abre la Caja de Alquileres/Cobranzas para devolver el saldo de la garantía.',
                    ]);
                }
            }

            $producto = Producto::query()
                ->lockForUpdate()
                ->findOrFail($alquiler->id_producto);
            $this->movimientoStockService->registrar(
                $producto,
                $usuario,
                'entrada',
                1,
                "Reingreso por devolución del alquiler #{$alquiler->id_alquiler}",
                "DEV-{$alquiler->id_alquiler}",
                true
            );

            $fechaDevolucion = Carbon::today();
            $fechaPactada = Carbon::parse($alquiler->fecha_pactada_devolucion);
            $diasAtraso = $fechaDevolucion->greaterThan($fechaPactada)
                ? (int) $fechaPactada->diffInDays($fechaDevolucion)
                : 0;
            $porcentajeMora = (float) (Configuracion::query()->value('mora_porcentaje') ?? 15);
            $moraCentimos = (int) round(
                $this->aCentimos($alquiler->precio_dia) * $porcentajeMora * $diasAtraso / 100
            );

            if ($moraCentimos > 0) {
                Cobranza::create([
                    'id_alquiler' => $alquiler->id_alquiler,
                    'id_usuario' => $usuario->id_usuario,
                    'tipo' => Cobranza::TIPO_MORA,
                    'dias_atraso' => $diasAtraso,
                    'porcentaje_aplicado' => $porcentajeMora,
                    'monto_mora' => $this->formatearCentimos($moraCentimos),
                    'monto_pagado' => '0.00',
                    'estado' => 'pendiente',
                    'observaciones' => "{$porcentajeMora}% de la tarifa diaria por {$diasAtraso} día(s) de atraso.",
                ]);
            }

            $montoDanoPendienteCentimos = $montoDanoCentimos - $garantiaAplicadaCentimos;

            if ($montoDanoPendienteCentimos > 0) {
                Cobranza::create([
                    'id_alquiler' => $alquiler->id_alquiler,
                    'id_usuario' => $usuario->id_usuario,
                    'tipo' => Cobranza::TIPO_DANO,
                    'monto_mora' => $this->formatearCentimos($montoDanoPendienteCentimos),
                    'monto_pagado' => '0.00',
                    'estado' => 'pendiente',
                    'observaciones' => $datos['observaciones_devolucion'],
                ]);
            }

            if ($garantiaADevolverCentimos > 0 && $caja) {
                $caja->movimientos()->create([
                    'id_usuario' => $usuario->id_usuario,
                    'id_alquiler' => $alquiler->id_alquiler,
                    'tipo_movimiento' => 'egreso',
                    'categoria' => 'Devolución de garantía',
                    'monto' => $this->formatearCentimos($garantiaADevolverCentimos),
                    'medio_pago' => $alquiler->medio_pago_garantia,
                    'detalle' => "Devolución de garantía del alquiler #{$alquiler->id_alquiler}.",
                ]);
                $this->cajaService->registrarEgreso(
                    $caja,
                    $garantiaADevolverCentimos / 100,
                    $alquiler->medio_pago_garantia
                );
            }

            $alquiler->update([
                'fecha_devolucion_real' => $fechaDevolucion->toDateString(),
                'monto_garantia_aplicado' => $this->formatearCentimos($garantiaAplicadaCentimos),
                'monto_garantia_devuelto' => $this->formatearCentimos($garantiaADevolverCentimos),
                'estado' => 'devuelto',
                'observaciones_devolucion' => $datos['observaciones_devolucion'],
                'id_usuario_devolucion' => $usuario->id_usuario,
            ]);

            Auditoria::create([
                'id_usuario' => $usuario->id_usuario,
                'accion' => 'ALQUILER_DEVUELTO',
                'tabla_afectada' => 'talquileres',
                'id_registro' => $alquiler->id_alquiler,
                'detalle' => "Devolución del alquiler #{$alquiler->id_alquiler}; atraso {$diasAtraso} día(s); daño S/ ".number_format($montoDanoCentimos / 100, 2).'; garantía aplicada S/ '.number_format($garantiaAplicadaCentimos / 100, 2).'.',
                'ip' => $ip,
            ]);

            return $alquiler->refresh()->load(['cliente', 'producto', 'usuario']);
        }, 3);
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
