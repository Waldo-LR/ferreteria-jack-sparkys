<?php

namespace App\Http\Controllers;

use App\Models\Alquiler;
use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Cobranza;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\AlquilerService;
use App\Services\CajaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AlquilerController extends Controller
{
    public function create(CajaService $cajaService): View
    {
        return view('alquileres.index', [
            'herramientas' => Producto::query()
                ->where('activo', true)
                ->where('es_alquilable', true)
                ->where('stock', '>', 0)
                ->orderBy('nombre')
                ->get(),
            'clientes' => Cliente::query()->where('activo', true)->orderBy('nombres')->get(),
            'cajaActual' => $cajaService->cajaAbierta(Caja::TIPO_ALQUILERES),
            'clientesBloqueados' => $this->clientesBloqueados(),
            'modulo' => 'registro',
        ]);
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', Rule::in(['vigente', 'vencido', 'devuelto'])],
        ]);

        $query = Alquiler::query()
            ->with(['cliente', 'producto', 'usuario', 'cobranzas'])
            ->when($filters['buscar'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereHas('cliente', fn (Builder $clientQuery) => $clientQuery
                        ->where('nombres', 'like', '%'.$search.'%')
                        ->orWhere('dni_ruc', 'like', '%'.$search.'%'))
                        ->orWhereHas('producto', fn (Builder $productQuery) => $productQuery
                            ->where('nombre', 'like', '%'.$search.'%')
                            ->orWhere('codigo', 'like', '%'.$search.'%'));
                });
            })
            ->when($filters['estado'] ?? null, function (Builder $query, string $status): void {
                if ($status === 'vencido') {
                    $query->whereIn('estado', ['vigente', 'vencido'])
                        ->whereDate('fecha_pactada_devolucion', '<', today());

                    return;
                }

                if ($status === 'devuelto') {
                    $query->where('estado', 'devuelto');

                    return;
                }

                $query->where('estado', '!=', 'devuelto')
                    ->whereDate('fecha_pactada_devolucion', '>=', today());
            })
            ->latest('id_alquiler');

        $alquileres = $query->paginate(15)->withQueryString();

        return view('alquileres.index', [
            'alquileres' => $alquileres,
            'modulo' => 'historial',
            'resumen' => [
                'activos' => Alquiler::query()->where('estado', '!=', 'devuelto')->count(),
                'vencidos' => Alquiler::query()
                    ->whereIn('estado', ['vigente', 'vencido'])
                    ->whereDate('fecha_pactada_devolucion', '<', today())
                    ->count(),
                'garantias' => (float) Alquiler::query()
                    ->where('estado', '!=', 'devuelto')
                    ->sum('monto_garantia'),
            ],
            'filters' => $filters,
        ]);
    }

    public function store(Request $request, AlquilerService $alquilerService): RedirectResponse
    {
        $validated = $request->validate([
            'id_cliente' => [
                'required',
                'integer',
                Rule::exists('tclientes', 'id_cliente')->where('activo', true),
            ],
            'id_producto' => [
                'required',
                'integer',
                Rule::exists('tproductos', 'id_producto')
                    ->where('activo', true)
                    ->where('es_alquilable', true),
            ],
            'dias_pactados' => ['required', 'integer', 'min:1', 'max:3650'],
            'fecha_entrega' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'monto_pagado' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'medio_pago_alquiler' => [
                'nullable',
                Rule::requiredIf(fn (): bool => (float) $request->input('monto_pagado', 0) > 0),
                Rule::in(Caja::MEDIOS_PAGO),
            ],
            'monto_garantia' => ['required', 'numeric', 'gt:0', 'max:9999999999.99', 'decimal:0,2'],
            'medio_pago_garantia' => ['required', Rule::in(Caja::MEDIOS_PAGO)],
            'ubicacion_herramienta' => ['nullable', 'string', 'max:200'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);

        $usuario = Auth::user();

        if (! ($usuario instanceof Usuario)) {
            abort(401);
        }

        $alquiler = $alquilerService->registrar($validated, $usuario, $request->ip());

        return redirect()->route('alquileres.index')
            ->with('success', "Alquiler #{$alquiler->id_alquiler} registrado. Devuelve el equipo el {$alquiler->fecha_pactada_devolucion->format('d/m/Y')}.");
    }

    /**
     * @return array<int, int>
     */
    private function clientesBloqueados(): array
    {
        $clientesConDevolucionPendiente = Alquiler::query()
            ->where('estado', '!=', 'devuelto')
            ->pluck('id_cliente');
        $clientesConDeuda = Alquiler::query()
            ->whereIn('id_alquiler', Cobranza::query()
                ->where('estado', 'pendiente')
                ->whereColumn('monto_pagado', '<', 'monto_mora')
                ->select('id_alquiler'))
            ->pluck('id_cliente');

        return $clientesConDevolucionPendiente
            ->merge($clientesConDeuda)
            ->unique()
            ->values()
            ->all();
    }

    public function devolver(
        Request $request,
        Alquiler $alquiler,
        AlquilerService $alquilerService
    ): RedirectResponse {
        $validated = $request->validate([
            'monto_dano' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'observaciones_devolucion' => ['required', 'string', 'min:3', 'max:2000'],
        ]);
        $usuario = Auth::user();

        if (! ($usuario instanceof Usuario)) {
            abort(401);
        }

        $alquilerService->devolver($alquiler, $validated, $usuario, $request->ip());

        return redirect()->route('alquileres.index')
            ->with('success', 'Devolución registrada; la mora y la garantía fueron conciliadas.');
    }
}
