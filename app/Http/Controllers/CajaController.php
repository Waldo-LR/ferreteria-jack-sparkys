<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\PagoCobranza;
use App\Models\Usuario;
use App\Models\Venta;
use App\Services\CajaService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CajaController extends Controller
{
    private const TIPOS_CAJA = [
        Caja::TIPO_VENTAS => 'Caja de Venta de Productos',
        Caja::TIPO_ALQUILERES => 'Caja de Alquileres/Cobranzas',
    ];

    private const CATEGORIAS_MOVIMIENTO = [
        'Aporte de fondos',
        'Reintegro',
        'Otros ingresos',
        'Compras menores',
        'Pago a proveedor',
        'Servicios',
        'Transporte',
        'Mantenimiento',
        'Otros egresos',
    ];

    public function index(): View
    {
        $this->authorizeCajaAccess();

        $cajasAbiertas = Caja::query()
            ->with('usuario')
            ->where('estado', 'abierta')
            ->get()
            ->keyBy('tipo_caja');

        $actividadPorCaja = [];

        foreach ($cajasAbiertas as $tipoCaja => $caja) {
            $actividadPorCaja[$tipoCaja] = $this->actividadReciente($caja);
        }

        $cierres = Caja::query()
            ->with(['usuario', 'usuarioCierre'])
            ->where('estado', 'cerrada')
            ->latest('fecha_cierre')
            ->take(12)
            ->get();

        return view('caja.index', [
            'cajasAbiertas' => $cajasAbiertas,
            'actividadPorCaja' => $actividadPorCaja,
            'cierres' => $cierres,
            'tiposCaja' => self::TIPOS_CAJA,
            'categoriasMovimiento' => self::CATEGORIAS_MOVIMIENTO,
        ]);
    }

    public function aperturar(Request $request, CajaService $cajaService): RedirectResponse
    {
        $this->authorizeCajaAccess();

        $datos = $request->validate([
            'tipo_caja' => ['required', Rule::in(array_keys(self::TIPOS_CAJA))],
            'monto_inicial' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            DB::transaction(function () use ($datos, $cajaService): void {
                if ($cajaService->cajaAbierta($datos['tipo_caja'], true)) {
                    throw ValidationException::withMessages([
                        'tipo_caja' => 'Ya existe una caja abierta para esta operación.',
                    ]);
                }

                $montoInicial = round((float) $datos['monto_inicial'], 2);

                Caja::create([
                    'id_usuario' => Auth::id(),
                    'tipo_caja' => $datos['tipo_caja'],
                    'tipo_caja_abierta' => $datos['tipo_caja'],
                    'fecha_apertura' => now(),
                    'monto_inicial' => $montoInicial,
                    'monto_final' => $montoInicial,
                    'estado' => 'abierta',
                    'observaciones' => $datos['observaciones'] ?? null,
                ]);
            });
        } catch (QueryException $exception) {
            if ($cajaService->cajaAbierta($datos['tipo_caja'])) {
                return back()->withInput()->with('error', 'Ya existe una caja abierta para esta operación.');
            }

            throw $exception;
        }

        return redirect()->route('caja.index')
            ->with('success', 'Caja abierta correctamente.');
    }

    public function registrarMovimiento(
        Request $request,
        Caja $caja,
        CajaService $cajaService
    ): RedirectResponse {
        $this->authorizeCajaAccess();

        $datos = $request->validate([
            'tipo_movimiento' => ['required', Rule::in(['ingreso', 'egreso'])],
            'categoria' => ['required', 'string', 'max:50', Rule::in(self::CATEGORIAS_MOVIMIENTO)],
            'medio_pago' => ['required', Rule::in(Caja::MEDIOS_PAGO)],
            'monto' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'detalle' => ['required', 'string', 'min:3', 'max:2000'],
        ]);

        DB::transaction(function () use ($caja, $cajaService, $datos): void {
            $cajaBloqueada = $cajaService->cajaAbierta($caja->tipo_caja, true);

            if (! $cajaBloqueada || $cajaBloqueada->id_caja !== $caja->id_caja) {
                throw ValidationException::withMessages([
                    'caja' => 'La caja está cerrada o ya no está disponible para registrar movimientos.',
                ]);
            }

            $monto = round((float) $datos['monto'], 2);

            $cajaBloqueada->movimientos()->create([
                'id_usuario' => Auth::id(),
                'tipo_movimiento' => $datos['tipo_movimiento'],
                'categoria' => $datos['categoria'],
                'monto' => $monto,
                'medio_pago' => $datos['medio_pago'],
                'detalle' => $datos['detalle'],
            ]);

            if ($datos['tipo_movimiento'] === 'ingreso') {
                $cajaService->registrarIngreso($cajaBloqueada, $monto, $datos['medio_pago']);
            } else {
                $cajaService->registrarEgreso($cajaBloqueada, $monto, $datos['medio_pago']);
            }
        });

        return redirect()->route('caja.index')
            ->with('success', 'Movimiento registrado correctamente.');
    }

    public function cerrar(Request $request, Caja $caja, CajaService $cajaService): RedirectResponse
    {
        $this->authorizeCajaAccess();

        $datos = $request->validate([
            'monto_contado' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($caja, $cajaService, $datos): void {
            $cajaBloqueada = $cajaService->cajaAbierta($caja->tipo_caja, true);

            if (! $cajaBloqueada || $cajaBloqueada->id_caja !== $caja->id_caja) {
                throw ValidationException::withMessages([
                    'caja' => 'La caja ya fue cerrada o no está disponible.',
                ]);
            }

            $montoContado = round((float) $datos['monto_contado'], 2);
            $diferencia = round($montoContado - (float) $cajaBloqueada->monto_final, 2);

            if ($diferencia !== 0.0 && empty($datos['observaciones'])) {
                throw ValidationException::withMessages([
                    'observaciones' => 'Indica el motivo del sobrante o faltante de efectivo.',
                ]);
            }

            $cajaBloqueada->update([
                'id_usuario_cierre' => Auth::id(),
                'fecha_cierre' => now(),
                'monto_contado' => $montoContado,
                'diferencia_cierre' => $diferencia,
                'tipo_cierre' => 'manual',
                'tipo_caja_abierta' => null,
                'estado' => 'cerrada',
                'observaciones' => $datos['observaciones'] ?? $cajaBloqueada->observaciones,
            ]);
        });

        return redirect()->route('caja.index')
            ->with('success', 'Caja cerrada y arqueo registrado correctamente.');
    }

    private function actividadReciente(Caja $caja): array
    {
        $actividad = MovimientoCaja::query()
            ->with('usuario')
            ->where('id_caja', $caja->id_caja)
            ->latest()
            ->take(20)
            ->get()
            ->map(fn (MovimientoCaja $movimiento): array => [
                'fecha' => $movimiento->created_at,
                'tipo' => $movimiento->tipo_movimiento,
                'concepto' => $movimiento->categoria.' · '.$movimiento->detalle,
                'medio_pago' => $movimiento->medio_pago,
                'monto' => (float) $movimiento->monto,
                'usuario' => $movimiento->usuario?->nombre ?? 'Usuario',
            ]);

        if ($caja->tipo_caja === Caja::TIPO_VENTAS) {
            $actividad = $actividad->concat(
                Venta::query()
                    ->with(['usuario', 'cliente'])
                    ->where('id_caja', $caja->id_caja)
                    ->latest()
                    ->take(20)
                    ->get()
                    ->map(fn (Venta $venta): array => [
                        'fecha' => $venta->created_at,
                        'tipo' => 'ingreso',
                        'concepto' => "Venta {$venta->serie}-{$venta->numero} · ".$venta->cliente->nombres,
                        'medio_pago' => $venta->medio_pago,
                        'monto' => (float) $venta->total,
                        'usuario' => $venta->usuario?->nombre ?? 'Usuario',
                    ])
            );
        } else {
            $actividad = $actividad->concat(
                PagoCobranza::query()
                    ->with(['usuario', 'cobranza.alquiler.cliente'])
                    ->where('id_caja', $caja->id_caja)
                    ->latest()
                    ->take(20)
                    ->get()
                    ->map(function (PagoCobranza $pago): array {
                        $concepto = match ($pago->cobranza?->tipo) {
                            'alquiler' => 'Pago de alquiler',
                            'dano' => 'Cobro por daños',
                            default => 'Cobro de mora',
                        };

                        return [
                            'fecha' => $pago->created_at,
                            'tipo' => 'ingreso',
                            'concepto' => $concepto.' · '.$pago->cobranza?->alquiler?->cliente?->nombres,
                            'medio_pago' => $pago->medio_pago,
                            'monto' => (float) $pago->monto,
                            'usuario' => $pago->usuario?->nombre ?? 'Usuario',
                        ];
                    })
            );
        }

        return $actividad
            ->sortByDesc('fecha')
            ->take(20)
            ->values()
            ->all();
    }

    private function authorizeCajaAccess(): void
    {
        $user = Auth::user();

        abort_unless(
            $user instanceof Usuario
                && ($user->esAdmin() || $user->esVendedor() || $user->esCajero() || $user->esCobrador()),
            403
        );
    }
}
