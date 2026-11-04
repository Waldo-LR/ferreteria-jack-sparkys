<?php

namespace App\Http\Controllers;

use App\Models\Alquiler;
use App\Models\Caja;
use App\Models\Cobranza;
use App\Models\Configuracion;
use App\Models\PagoCobranza;
use App\Models\Usuario;
use App\Services\AlquilerService;
use App\Services\CajaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CobranzaController extends Controller
{
    public function index(Request $request, CajaService $cajaService): View
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', Rule::in(['pendiente', 'pagado'])],
            'tipo' => ['nullable', Rule::in([
                Cobranza::TIPO_ALQUILER,
                Cobranza::TIPO_MORA,
                Cobranza::TIPO_DANO,
            ])],
        ]);

        $query = Cobranza::query()
            ->with(['alquiler.cliente', 'alquiler.producto', 'usuario', 'pagos.usuario'])
            ->when($filters['buscar'] ?? null, function (Builder $query, string $search): void {
                $query->whereHas('alquiler', function (Builder $alquilerQuery) use ($search): void {
                    $alquilerQuery->where('ubicacion_herramienta', 'like', '%'.$search.'%')
                        ->orWhereHas('cliente', fn (Builder $clientQuery) => $clientQuery
                            ->where('nombres', 'like', '%'.$search.'%')
                            ->orWhere('dni_ruc', 'like', '%'.$search.'%')
                            ->orWhere('direccion', 'like', '%'.$search.'%'))
                        ->orWhereHas('producto', fn (Builder $productQuery) => $productQuery
                            ->where('nombre', 'like', '%'.$search.'%')
                            ->orWhere('codigo', 'like', '%'.$search.'%'));
                });
            })
            ->when($filters['estado'] ?? null, fn (Builder $query, string $status) => $query
                ->where('estado', $status))
            ->when($filters['tipo'] ?? null, fn (Builder $query, string $type) => $query
                ->where('tipo', $type))
            ->latest('id_cobranza');

        return view('cobranzas.index', [
            'cobranzas' => $query->paginate(15)->withQueryString(),
            'totalPendiente' => (float) Cobranza::query()
                ->where('estado', 'pendiente')
                ->selectRaw('COALESCE(SUM(monto_mora - monto_pagado), 0) as saldo_pendiente')
                ->value('saldo_pendiente'),
            'moraPorcentaje' => (float) (Configuracion::query()->value('mora_porcentaje') ?? 15),
            'cajaAlquileresAbierta' => $cajaService->cajaAbierta(Caja::TIPO_ALQUILERES) !== null,
            'filters' => $filters,
        ]);
    }

    public function pagar(
        Request $request,
        Cobranza $cobranza,
        CajaService $cajaService
    ): RedirectResponse {
        $datos = $request->validate([
            'monto' => ['required', 'numeric', 'gt:0', 'max:9999999999.99', 'decimal:0,2'],
            'medio_pago' => ['required', Rule::in(Caja::MEDIOS_PAGO)],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);
        $usuario = Auth::user();

        if (! ($usuario instanceof Usuario)) {
            abort(401);
        }

        DB::transaction(function () use ($cobranza, $cajaService, $datos, $usuario): void {
            $cobranzaBloqueada = Cobranza::query()
                ->lockForUpdate()
                ->findOrFail($cobranza->id_cobranza);
            $caja = $cajaService->cajaAbierta(Caja::TIPO_ALQUILERES, true);

            if (! $caja) {
                throw ValidationException::withMessages([
                    'caja' => 'Abre la Caja de Alquileres/Cobranzas para registrar este pago.',
                ]);
            }

            $montoCentimos = (int) round((float) $datos['monto'] * 100);
            $saldoCentimos = (int) round(
                ((float) $cobranzaBloqueada->monto_mora - (float) $cobranzaBloqueada->monto_pagado) * 100
            );

            if ($cobranzaBloqueada->estado !== 'pendiente' || $montoCentimos > $saldoCentimos) {
                throw ValidationException::withMessages([
                    'monto' => 'El monto supera el saldo pendiente de esta cobranza o ya fue cancelada.',
                ]);
            }

            PagoCobranza::create([
                'id_cobranza' => $cobranzaBloqueada->id_cobranza,
                'id_usuario' => $usuario->id_usuario,
                'id_caja' => $caja->id_caja,
                'monto' => number_format($montoCentimos / 100, 2, '.', ''),
                'medio_pago' => $datos['medio_pago'],
                'observaciones' => $datos['observaciones'] ?? 'Abono registrado en caja.',
            ]);

            $nuevoPagadoCentimos = (int) round((float) $cobranzaBloqueada->monto_pagado * 100) + $montoCentimos;
            $cobranzaBloqueada->update([
                'monto_pagado' => number_format($nuevoPagadoCentimos / 100, 2, '.', ''),
                'estado' => $nuevoPagadoCentimos >= (int) round((float) $cobranzaBloqueada->monto_mora * 100)
                    ? 'pagado'
                    : 'pendiente',
            ]);

            if ($cobranzaBloqueada->tipo === Cobranza::TIPO_ALQUILER) {
                $alquiler = Alquiler::query()
                    ->lockForUpdate()
                    ->findOrFail($cobranzaBloqueada->id_alquiler);
                $alquiler->increment('monto_pagado', $montoCentimos / 100);
            }

            $cajaService->registrarIngreso($caja, $montoCentimos / 100, $datos['medio_pago']);
        });

        return redirect()->route('cobranzas.index', $request->only(['buscar', 'estado', 'tipo']))
            ->with('success', 'Pago registrado en la Caja de Alquileres/Cobranzas.');
    }

    public function recuperar(
        Request $request,
        Alquiler $alquiler,
        AlquilerService $alquilerService
    ): RedirectResponse {
        $datos = $request->validate([
            'monto_dano' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'observaciones_devolucion' => ['required', 'string', 'min:3', 'max:2000'],
        ]);
        $usuario = Auth::user();

        if (! ($usuario instanceof Usuario)) {
            abort(401);
        }

        $alquilerService->devolver($alquiler, $datos, $usuario, $request->ip());

        return redirect()->route('cobranzas.index')
            ->with('success', 'Recuperación y devolución registradas; se calcularon los cargos pendientes.');
    }
}
