<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\MovimientoStockService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductoController extends Controller
{
    public function index(Request $request): View
    {
        $user = $this->authorizeView();
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50])],
            'sort' => ['nullable', Rule::in(['nombre', 'categoria', 'stock', 'precio_venta', 'precio_venta_mayor'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
        $sort = $filters['sort'] ?? 'nombre';
        $direction = $filters['direction'] ?? 'asc';
        $perPage = (int) ($filters['per_page'] ?? 10);

        $productosQuery = Producto::query()
            ->with(['categoria', 'marca'])
            ->where('activo', true)
            ->when($filters['q'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('codigo', 'like', '%'.$search.'%')
                        ->orWhere('nombre', 'like', '%'.$search.'%')
                        ->orWhereHas('categoria', fn (Builder $categoryQuery) => $categoryQuery->where('nombre', 'like', '%'.$search.'%'))
                        ->orWhereHas('marca', fn (Builder $brandQuery) => $brandQuery->where('nombre', 'like', '%'.$search.'%'));
                });
            });

        if ($sort === 'categoria') {
            $productosQuery->orderBy(
                Categoria::query()
                    ->select('nombre')
                    ->whereColumn('tcategorias.id_categoria', 'tproductos.id_categoria'),
                $direction
            );
        } else {
            $productosQuery->orderBy($sort, $direction);
        }

        $productos = $productosQuery
            ->orderBy('id_producto')
            ->paginate($perPage)
            ->withQueryString();

        return view('productos.index', [
            'productos' => $productos,
            'filters' => [
                'q' => $filters['q'] ?? '',
                'per_page' => $perPage,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'canManageProducts' => $user->esAdmin() || $user->esAlmacenero(),
        ]);
    }

    public function edit(Producto $producto): View
    {
        $this->authorizeManage();
        abort_unless($producto->activo, 404);

        return view('productos.edit', [
            'producto' => $producto,
            'categorias' => Categoria::query()->where('activo', true)->orderBy('nombre')->get(),
            'marcas' => Marca::query()->where('activo', true)->orderBy('nombre')->get(),
        ]);
    }

    public function image(Producto $producto): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        abort_unless($producto->imagen, 404);

        return response()->file(Storage::disk('public')->path($producto->imagen));
    }

    public function create(): View
    {
        $this->authorizeManage();

        return view('productos.create', [
            'categorias' => Categoria::query()->where('activo', true)->orderBy('nombre')->get(),
            'marcas' => Marca::query()->where('activo', true)->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request, MovimientoStockService $movimientoStockService): RedirectResponse
    {
        $this->authorizeManage();

        $validated = $request->validate([
            ...$this->validationRules(),
            'stock' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ]);

        if ($request->hasFile('imagen')) {
            $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
        }
        $initialStock = (int) $validated['stock'];
        $validated['precio_venta_mayor'] ??= $validated['precio_venta'];
        $validated['cantidad_mayorista'] ??= 2;
        $validated['es_alquilable'] = $request->boolean('es_alquilable');
        $validated['precio_alquiler_dia'] ??= 0;
        $validated['activo'] = true;
        $validated['stock'] = 0;

        DB::transaction(function () use ($validated, $initialStock, $movimientoStockService): void {
            $product = Producto::create($validated);

            if ($initialStock > 0) {
                $user = Auth::user();
                abort_unless($user instanceof Usuario, 401);

                $movimientoStockService->registrar(
                    $product,
                    $user,
                    'entrada',
                    $initialStock,
                    'Stock inicial por creación de producto'
                );
            }
        }, 3);

        return back()->with('success', 'Producto registrado correctamente en el inventario.');
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $this->authorizeManage();
        abort_unless($producto->activo, 404);

        $validated = $request->validate($this->validationRules($producto, true));
        if ($request->hasFile('imagen')) {
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
        }
        $validated['precio_venta_mayor'] ??= $validated['precio_venta'];
        $validated['cantidad_mayorista'] ??= 2;
        $validated['es_alquilable'] = $request->boolean('es_alquilable');
        $validated['precio_alquiler_dia'] ??= 0;

        $producto->update($validated);

        return redirect()->route('productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        $this->authorizeManage();

        if ($producto->stock > 0) {
            throw ValidationException::withMessages([
                'producto' => 'No se puede dar de baja un producto con existencias. Registra primero su salida o ajuste.',
            ]);
        }

        $producto->update(['activo' => false, 'fecha_baja' => now()]);

        return back()->with('success', 'Producto dado de baja correctamente.');
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

    /**
     * @return array<string, array<int, string|Exists|Unique>>
     */
    private function validationRules(?Producto $producto = null, bool $updating = false): array
    {
        $rules = [
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tproductos', 'codigo')->ignore($producto?->id_producto, 'id_producto'),
            ],
            'id_categoria' => ['required', 'integer', Rule::exists('tcategorias', 'id_categoria')->where('activo', true)],
            'id_marca' => ['required', 'integer', Rule::exists('tmarcas', 'id_marca')->where('activo', true)],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'stock_minimo' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'precio_compra' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'precio_venta' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'precio_venta_mayor' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'cantidad_mayorista' => ['nullable', 'integer', 'min:1', 'max:4294967295'],
            'es_alquilable' => ['nullable', 'boolean'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'precio_alquiler_dia' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
        ];

        if ($updating) {
            $rules['stock'] = ['prohibited'];
        }

        return $rules;
    }
}
