<?php

namespace App\Services;

use App\Models\Caja;
use Illuminate\Validation\ValidationException;

class CajaService
{
    public function cajaAbierta(string $tipoCaja, bool $lockForUpdate = false): ?Caja
    {
        $query = Caja::query()
            ->where('tipo_caja', $tipoCaja)
            ->where('tipo_caja_abierta', $tipoCaja)
            ->where('estado', 'abierta');

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function registrarIngreso(Caja $caja, float $monto, string $medioPago): void
    {
        $caja->increment('total_ingresos', $monto);

        if ($medioPago === 'efectivo') {
            $caja->increment('ingresos_efectivo', $monto);
            $caja->increment('monto_final', $monto);
        }
    }

    public function registrarEgreso(Caja $caja, float $monto, string $medioPago): void
    {
        if ($medioPago === 'efectivo') {
            $saldoDisponibleEnCentimos = (int) round((float) $caja->monto_final * 100);
            $montoEnCentimos = (int) round($monto * 100);

            if ($montoEnCentimos > $saldoDisponibleEnCentimos) {
                throw ValidationException::withMessages([
                    'monto' => 'El egreso en efectivo no puede superar el efectivo disponible en caja.',
                ]);
            }

            $caja->increment('egresos_efectivo', $monto);
            $caja->decrement('monto_final', $monto);
        }

        $caja->increment('total_egresos', $monto);
    }
}
