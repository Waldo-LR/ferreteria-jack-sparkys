<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\MovimientoStock;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\MovimientoStockService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventarioController extends Controller
{
    private const STOCK_MAXIMO = 4294967295;

    public function index(Request $request): View
    {
        $user = $this->authorizeView();
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'stock' => ['nullable', Rule::in(['bajo', 'agotado'])],
            'producto_historial' => ['nullable', 'string', 'max:100'],
            'tipo_movimiento' => ['nullable', Rule::in(['entrada', 'salida', 'ajuste'])],
            'fecha_desde' => ['nullable', 'date_format:Y-m-d', 'required_with:fecha_hasta'],
            'fecha_hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:fecha_desde', 'required_with:fecha_desde'],
        ]);

        $productsQuery = Producto::query()
            ->with(['categoria:id_categoria,nombre', 'marca:id_marca,nombre'])
            ->where('activo', true)
            ->when($filters['q'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('nombre', 'like', '%'.$search.'%')
                        ->orWhere('codigo', 'like', '%'.$search.'%');
                });
            })
            ->when(($filters['stock'] ?? null) === 'bajo', function (Builder $query): void {
                $query->whereColumn('stock', '<=', 'stock_minimo');
            })
            ->when(($filters['stock'] ?? null) === 'agotado', function (Builder $query): void {
                $query->where('stock', 0);
            })
            ->orderBy('nombre')
            ->orderBy('id_producto');

        $movementQuery = MovimientoStock::query()
            ->with([
                'producto:id_producto,nombre,codigo',
                'usuario:id_usuario,nombre',
            ])
            ->when($filters['producto_historial'] ?? null, function (Builder $query, string $search): void {
                $query->whereHas('producto', function (Builder $query) use ($search): void {
                    $query->where('nombre', 'like', '%'.$search.'%')
                        ->orWhere('codigo', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['tipo_movimiento'] ?? null, function (Builder $query, string $type): void {
                $query->where('tipo', $type);
            })
            ->when($filters['fecha_desde'] ?? null, function (Builder $query, string $date): void {
                $query->where('created_at', '>=', Carbon::parse($date)->startOfDay());
            })
            ->when($filters['fecha_hasta'] ?? null, function (Builder $query, string $date): void {
                $query->where('created_at', '<=', Carbon::parse($date)->endOfDay());
            })
            ->latest('created_at')
            ->latest('id_movimiento');

        $activeProducts = Producto::query()->where('activo', true);

        return view('inventario.index', [
            'products' => $productsQuery->paginate(15, ['*'], 'productos_pagina')->withQueryString(),
            'movements' => $movementQuery->paginate(15, ['*'], 'movimientos_pagina')->withQueryString(),
            'categories' => Categoria::query()->where('activo', true)->orderBy('nombre')->get(),
            'brands' => Marca::query()->where('activo', true)->orderBy('nombre')->get(),
            'filters' => $filters,
            'canManageInventory' => $user->esAdmin() || $user->esAlmacenero(),
            'summary' => [
                'products' => (clone $activeProducts)->count(),
                'low_stock' => (clone $activeProducts)->whereColumn('stock', '<=', 'stock_minimo')->count(),
                'stock_value' => (clone $activeProducts)
                    ->selectRaw('COALESCE(SUM(stock * precio_compra), 0) as total')
                    ->value('total'),
                'today_movements' => MovimientoStock::query()->whereDate('created_at', today())->count(),
            ],
        ]);
    }

    public function storeMovement(
        Request $request,
        Producto $producto,
        MovimientoStockService $movimientoStockService
    ): RedirectResponse {
        $this->authorizeManage();

        $validated = $request->validate([
            'tipo' => ['required', Rule::in(['entrada', 'salida', 'ajuste'])],
            'cantidad' => [
                'required',
                'integer',
                Rule::when($request->input('tipo') === 'ajuste', ['min:0', 'max:'.self::STOCK_MAXIMO], ['min:1', 'max:'.self::STOCK_MAXIMO]),
            ],
            'motivo' => ['required', 'string', 'max:150'],
            'referencia' => ['nullable', 'string', 'max:100'],
        ]);

        $usuario = Auth::user();
        abort_unless($usuario instanceof Usuario, 401);

        $movimientoStockService->registrar(
            $producto,
            $usuario,
            $validated['tipo'],
            (int) $validated['cantidad'],
            $validated['motivo'],
            $validated['referencia'] ?? null
        );

        return back()->with('success', 'Movimiento de inventario registrado correctamente.');
    }

    private function authorizeView(): Usuario
    {
        $user = Auth::user();

        abort_unless(
            $user instanceof Usuario && ($user->esAdmin() || $user->esAlmacenero() || $user->esVendedor()),
            403
        );

        return $user;
    }

    private function authorizeManage(): void
    {
        $user = Auth::user();

        abort_unless($user instanceof Usuario && ($user->esAdmin() || $user->esAlmacenero()), 403);
    }
}
