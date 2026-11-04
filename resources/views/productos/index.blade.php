@extends('layouts.app')

@section('title', 'Catálogo de Productos - Ferretería Jack Sparkys S.A.C.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-100">Catálogo de Productos</h1>
            <p class="mt-1 text-sm text-slate-400">Consulta productos, precios y disponibilidad de stock.</p>
        </div>
        <div class="flex items-center gap-2 self-start rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 text-xs font-semibold text-slate-300 sm:self-auto">
            <i class="fa-regular fa-clock text-amber-400"></i>
            {{ now()->format('d/m/Y') }}
        </div>
    </div>

    <form action="{{ route('productos.index') }}" method="GET" class="grid grid-cols-1 gap-3 rounded-2xl border border-slate-700 bg-slate-800 p-4 md:grid-cols-[minmax(0,1fr)_auto_auto] md:items-end">
        <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
        <input type="hidden" name="direction" value="{{ $filters['direction'] }}">
        <label class="space-y-1.5 text-xs font-semibold text-slate-300">
            <span>Buscar por producto, código, categoría o marca</span>
            <span class="relative block">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                <input type="search" name="q" value="{{ $filters['q'] }}" maxlength="100" placeholder="Ej. taladro, código o marca" class="w-full rounded-xl border border-slate-600 bg-slate-900 py-2.5 pl-9 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/20">
            </span>
        </label>
        <label class="flex items-center gap-2 text-xs font-semibold text-slate-300">
            <span>Mostrar</span>
            <select name="per_page" class="rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none" onchange="this.form.requestSubmit()">
                @foreach([10, 25, 50] as $pageSize)
                    <option value="{{ $pageSize }}" @selected($filters['per_page'] === $pageSize)>{{ $pageSize }}</option>
                @endforeach
            </select>
            <span>registros</span>
        </label>
        <div class="flex gap-2">
            <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-slate-700 px-4 py-2.5 text-sm font-bold text-slate-100 transition hover:bg-slate-600">
                <i class="fa-solid fa-filter"></i>
                Buscar
            </button>
            @if($filters['q'] !== '')
                <a href="{{ route('productos.index', ['per_page' => $filters['per_page'], 'sort' => $filters['sort'], 'direction' => $filters['direction']]) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-600 px-3 py-2.5 text-sm text-slate-300 transition hover:border-slate-500 hover:text-white" aria-label="Limpiar búsqueda" title="Limpiar búsqueda">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </div>
    </form>

    <div class="flex flex-col gap-3 rounded-2xl border border-slate-700 bg-slate-800 p-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-400">
            @if($productos->total() > 0)
                Mostrando <span class="font-semibold text-slate-200">{{ $productos->firstItem() }}–{{ $productos->lastItem() }}</span>
                de <span class="font-semibold text-slate-200">{{ number_format($productos->total()) }}</span> productos
            @else
                No hay productos para mostrar
            @endif
        </p>
        <div class="flex flex-wrap items-center gap-2">
            @if($productos->hasPages())
                <nav aria-label="Paginación del catálogo" class="inline-flex items-center overflow-hidden rounded-xl border border-slate-600 text-sm">
                    @if($productos->onFirstPage())
                        <span class="px-3 py-2.5 text-slate-500" aria-disabled="true">Anterior</span>
                    @else
                        <a href="{{ $productos->previousPageUrl() }}" class="px-3 py-2.5 text-slate-300 transition hover:bg-slate-700">Anterior</a>
                    @endif
                    <span class="border-x border-slate-600 bg-blue-500/20 px-3 py-2.5 font-bold text-blue-200">{{ $productos->currentPage() }} / {{ $productos->lastPage() }}</span>
                    @if($productos->hasMorePages())
                        <a href="{{ $productos->nextPageUrl() }}" class="px-3 py-2.5 text-slate-300 transition hover:bg-slate-700">Siguiente</a>
                    @else
                        <span class="px-3 py-2.5 text-slate-500" aria-disabled="true">Siguiente</span>
                    @endif
                </nav>
            @endif
            @if($canManageProducts)
                <a href="{{ route('inventario.index') }}#registrar-producto" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-amber-400">
                    <i class="fa-solid fa-plus"></i>
                    Añadir producto
                </a>
            @endif
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px] text-left text-sm">
                <thead class="border-b border-slate-700 bg-slate-900/80 text-xs uppercase tracking-wide text-slate-400">
                    <tr>
                        <th scope="col" class="px-4 py-3">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'nombre', 'direction' => $filters['sort'] === 'nombre' && $filters['direction'] === 'asc' ? 'desc' : 'asc', 'page' => null]) }}" class="inline-flex items-center gap-2 hover:text-slate-100">
                                Producto <i class="fa-solid {{ $filters['sort'] === 'nombre' ? ($filters['direction'] === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-sort' }}"></i>
                            </a>
                        </th>
                        <th scope="col" class="px-4 py-3">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'categoria', 'direction' => $filters['sort'] === 'categoria' && $filters['direction'] === 'asc' ? 'desc' : 'asc', 'page' => null]) }}" class="inline-flex items-center gap-2 hover:text-slate-100">
                                Categoría / Marca <i class="fa-solid {{ $filters['sort'] === 'categoria' ? ($filters['direction'] === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-sort' }}"></i>
                            </a>
                        </th>
                        <th scope="col" class="px-4 py-3 text-center">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'stock', 'direction' => $filters['sort'] === 'stock' && $filters['direction'] === 'asc' ? 'desc' : 'asc', 'page' => null]) }}" class="inline-flex items-center gap-2 hover:text-slate-100">
                                Stock <i class="fa-solid {{ $filters['sort'] === 'stock' ? ($filters['direction'] === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-sort' }}"></i>
                            </a>
                        </th>
                        <th scope="col" class="px-4 py-3 text-right">P. Compra</th>
                        <th scope="col" class="px-4 py-3 text-right">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'precio_venta', 'direction' => $filters['sort'] === 'precio_venta' && $filters['direction'] === 'asc' ? 'desc' : 'asc', 'page' => null]) }}" class="inline-flex items-center gap-2 hover:text-slate-100">
                                Precio unitario <i class="fa-solid {{ $filters['sort'] === 'precio_venta' ? ($filters['direction'] === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-sort' }}"></i>
                            </a>
                        </th>
                        <th scope="col" class="px-4 py-3 text-right">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'precio_venta_mayor', 'direction' => $filters['sort'] === 'precio_venta_mayor' && $filters['direction'] === 'asc' ? 'desc' : 'asc', 'page' => null]) }}" class="inline-flex items-center gap-2 hover:text-slate-100">
                                Mayorista <i class="fa-solid {{ $filters['sort'] === 'precio_venta_mayor' ? ($filters['direction'] === 'asc' ? 'fa-arrow-up' : 'fa-arrow-down') : 'fa-sort' }}"></i>
                            </a>
                        </th>
                        <th scope="col" class="px-4 py-3 text-right">Alquiler / día</th>
                        <th scope="col" class="px-4 py-3 text-center">Estado de stock</th>
                        <th scope="col" class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($productos as $producto)
                        <tr class="transition hover:bg-slate-700/30">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-600 bg-slate-900 text-lg text-amber-400">
                                        <i class="fa-solid fa-box-open"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate font-bold text-slate-100">{{ $producto->nombre }}</p>
                                        <p class="mt-0.5 text-xs text-slate-400">{{ $producto->codigo }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-slate-200">{{ $producto->categoria?->nombre ?? 'Sin categoría' }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $producto->marca?->nombre ?? 'Sin marca' }}</p>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex rounded-full bg-blue-500/10 px-2.5 py-1 text-xs font-bold text-blue-300">
                                    {{ number_format($producto->stock) }} und.
                                </span>
                                <p class="mt-1 text-[11px] text-slate-500">Mínimo: {{ number_format($producto->stock_minimo) }}</p>
                            </td>
                            <td class="px-4 py-4 text-right font-medium text-slate-300">S/ {{ number_format((float) $producto->precio_compra, 2) }}</td>
                            <td class="px-4 py-4 text-right font-bold text-emerald-400">S/ {{ number_format((float) $producto->precio_venta, 2) }}</td>
                            <td class="px-4 py-4 text-right">
                                <p class="font-semibold text-slate-200">S/ {{ number_format((float) ($producto->precio_venta_mayor ?? $producto->precio_venta), 2) }}</p>
                                <p class="mt-0.5 text-[11px] text-slate-400">Desde {{ number_format($producto->cantidad_mayorista) }} und.</p>
                            </td>
                            <td class="px-4 py-4 text-right">
                                @if($producto->es_alquilable)
                                    <span class="font-semibold text-blue-300">S/ {{ number_format((float) $producto->precio_alquiler_dia, 2) }}</span>
                                @else
                                    <span class="text-xs text-slate-500">No alquilable</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($producto->stock === 0)
                                    <span class="inline-flex rounded-full border border-rose-500/30 bg-rose-500/10 px-2.5 py-1 text-xs font-bold text-rose-300">Agotado</span>
                                @elseif($producto->stock <= $producto->stock_minimo)
                                    <span class="inline-flex rounded-full border border-amber-500/30 bg-amber-500/10 px-2.5 py-1 text-xs font-bold text-amber-300">Por reponer</span>
                                @else
                                    <span class="inline-flex rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-1 text-xs font-bold text-emerald-300">Disponible</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @if($canManageProducts)
                                        <a href="{{ route('productos.edit', $producto) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-amber-500/30 text-amber-300 transition hover:bg-amber-500 hover:text-slate-950" aria-label="Editar {{ $producto->nombre }}" title="Editar producto">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('inventario.index', ['q' => $producto->codigo]) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-600 text-slate-300 transition hover:border-blue-400 hover:text-blue-300" aria-label="Ver inventario de {{ $producto->nombre }}" title="Ver inventario">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @if($canManageProducts && $producto->stock === 0)
                                        <form action="{{ route('productos.destroy', $producto->id_producto) }}" method="POST" onsubmit="return confirm('¿Confirma dar de baja este producto?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-rose-500/30 text-rose-300 transition hover:bg-rose-500 hover:text-white" aria-label="Dar de baja {{ $producto->nombre }}" title="Dar de baja">
                                                <i class="fa-solid fa-box-archive"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center">
                                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-700 text-slate-400">
                                    <i class="fa-solid fa-box-open"></i>
                                </span>
                                <p class="mt-3 font-semibold text-slate-200">No se encontraron productos</p>
                                <p class="mt-1 text-sm text-slate-400">
                                    {{ $filters['q'] !== '' ? 'Prueba con otro nombre, código, categoría o marca.' : 'Los productos activos aparecerán aquí.' }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($productos->hasPages())
            <div class="border-t border-slate-700 p-4">{{ $productos->links() }}</div>
        @endif
    </div>
</div>
@endsection
