<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompraRequest;
use App\Http\Requests\StorePagoCompraRequest;
use App\Models\Caja;
use App\Models\Compra;
use App\Models\MovimientoCaja;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Usuario;
use App\Services\CajaService;
use App\Services\CompraService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompraController extends Controller
{
    public function index(Request $request, CajaService $cajaService): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'id_proveedor' => ['nullable', 'integer', Rule::exists('tproveedores', 'id_proveedor')],
            'fecha_desde' => ['nullable', 'date_format:Y-m-d', 'required_with:fecha_hasta'],
            'fecha_hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:fecha_desde', 'required_with:fecha_desde'],
        ]);

        $compras = Compra::query()
            ->with(['proveedor', 'usuario', 'detalles.producto', 'pagos.usuario'])
            ->withSum('pagos as monto_pagado', 'monto')
            ->when($filters['q'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('numero_comprobante', 'like', '%'.$search.'%')
                        ->orWhereHas('proveedor', fn (Builder $providerQuery) => $providerQuery
                            ->where('razon_social', 'like', '%'.$search.'%'));
                });
            })
            ->when($filters['id_proveedor'] ?? null, fn (Builder $query, int $providerId) => $query
                ->where('id_proveedor', $providerId))
            ->when($filters['fecha_desde'] ?? null, fn (Builder $query, string $date) => $query
                ->whereDate('fecha_compra', '>=', $date))
            ->when($filters['fecha_hasta'] ?? null, fn (Builder $query, string $date) => $query
                ->whereDate('fecha_compra', '<=', $date))
            ->latest('fecha_compra')
            ->latest('id_compra')
            ->paginate(15)
            ->withQueryString();

        $totalComprado = (float) Compra::query()->where('estado', 'recibida')->sum('total');
        $totalPagado = (float) MovimientoCaja::query()
            ->whereNotNull('id_compra')
            ->whereHas('compra', fn (Builder $query) => $query->where('estado', 'recibida'))
            ->sum('monto');

        return view('compras.index', [
            'compras' => $compras,
            'proveedores' => Proveedor::query()->where('activo', true)->orderBy('razon_social')->get(),
            'productos' => Producto::query()->where('activo', true)->orderBy('nombre')->get(),
            'lineasFormulario' => $request->old('detalles', [['id_producto' => '', 'cantidad' => 1, 'precio_unitario' => '']]),
            'filters' => $filters,
            'cajaVentasAbierta' => $cajaService->cajaAbierta(Caja::TIPO_VENTAS) !== null,
            'resumen' => [
                'total' => $totalComprado,
                'pagado' => $totalPagado,
                'pendiente' => max(round($totalComprado - $totalPagado, 2), 0),
            ],
        ]);
    }

    public function store(StoreCompraRequest $request, CompraService $compraService): RedirectResponse
    {
        $usuario = Auth::user();

        if (! ($usuario instanceof Usuario)) {
            abort(401);
        }

        $compra = $compraService->registrarCompra($request->validated(), $usuario);

        return redirect()->route('compras.index')
            ->with('success', "Compra #{$compra->id_compra} recibida y registrada en Inventario.");
    }

    public function storePayment(
        StorePagoCompraRequest $request,
        Compra $compra,
        CompraService $compraService
    ): RedirectResponse {
        $usuario = Auth::user();

        if (! ($usuario instanceof Usuario)) {
            abort(401);
        }

        $compraService->registrarPago(
            $compra,
            $request->validated('monto'),
            $request->validated('medio_pago'),
            $usuario
        );

        return redirect()->route('compras.index')
            ->with('success', "Pago registrado correctamente para la compra #{$compra->id_compra}.");
    }
}
