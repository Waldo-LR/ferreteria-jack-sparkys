@extends('layouts.app')

@section('title', 'Control de Inventario - Ferretería Jack Sparkys S.A.C.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-2xl font-black text-slate-100">
                <i class="fa-solid fa-warehouse text-amber-400"></i>
                Control de Inventario
            </h1>
            <p class="mt-1 text-sm text-slate-400">Consulta existencias y mantén trazabilidad de cada movimiento.</p>
        </div>
        @if($canManageInventory)
            <a href="{{ route('compras.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-amber-400">
                <i class="fa-solid fa-plus"></i>
                Registrar producto
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('inventario.index') }}" class="rounded-2xl border border-slate-700 bg-slate-800 p-5 transition hover:border-slate-500">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-400">Productos activos</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/10 text-blue-400"><i class="fa-solid fa-boxes-stacked"></i></span>
            </div>
            <p class="mt-3 text-3xl font-black text-slate-100">{{ number_format($summary['products']) }}</p>
            <p class="mt-1 text-xs text-slate-500">En el catálogo</p>
        </a>
        <a href="{{ route('inventario.index', ['stock' => 'bajo']) }}" class="rounded-2xl border border-slate-700 bg-slate-800 p-5 transition hover:border-rose-500/50">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-400">Por reponer</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-500/10 text-rose-400"><i class="fa-solid fa-triangle-exclamation"></i></span>
            </div>
            <p class="mt-3 text-3xl font-black text-slate-100">{{ number_format($summary['low_stock']) }}</p>
            <p class="mt-1 text-xs text-slate-500">En o bajo el mínimo</p>
        </a>
        <div class="rounded-2xl border border-slate-700 bg-slate-800 p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-400">Valor al costo</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400"><i class="fa-solid fa-sack-dollar"></i></span>
            </div>
            <p class="mt-3 text-3xl font-black text-slate-100">S/ {{ number_format((float) $summary['stock_value'], 2) }}</p>
            <p class="mt-1 text-xs text-slate-500">Existencias por costo registrado</p>
        </div>
        <a href="#historial" class="rounded-2xl border border-slate-700 bg-slate-800 p-5 transition hover:border-amber-500/50">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-400">Movimientos de hoy</p>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400"><i class="fa-solid fa-arrow-right-arrow-left"></i></span>
            </div>
            <p class="mt-3 text-3xl font-black text-slate-100">{{ number_format($summary['today_movements']) }}</p>
            <p class="mt-1 text-xs text-slate-500">Ventas, alquileres y ajustes</p>
        </a>
    </div>

    @if($canManageInventory)
        <details id="registrar-producto" class="group overflow-hidden rounded-2xl border border-slate-700 bg-slate-800" @if($errors->hasAny(['codigo', 'nombre', 'id_categoria', 'id_marca', 'stock', 'stock_minimo', 'precio_compra', 'precio_venta', 'precio_venta_mayor', 'cantidad_mayorista']) && old('_inventario_producto') === null) open @endif>
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-5">
                <div>
                    <h2 class="font-bold text-slate-100">Registrar producto</h2>
                    <p class="mt-1 text-xs text-slate-400">El stock inicial también quedará registrado en el historial.</p>
                </div>
                <i class="fa-solid fa-chevron-down text-slate-400 transition group-open:rotate-180"></i>
            </summary>
            <form action="{{ route('productos.store') }}" method="POST" class="grid grid-cols-1 gap-4 border-t border-slate-700 p-5 sm:grid-cols-2 xl:grid-cols-4">
                @csrf
                <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                    <span>Código *</span>
                    <input type="text" name="codigo" value="{{ old('codigo') }}" required maxlength="50" autocomplete="off" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
                </label>
                <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                    <span>Nombre *</span>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required maxlength="150" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
                </label>
                <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                    <span>Categoría *</span>
                    <select name="id_categoria" required class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
                        <option value="">Selecciona una categoría</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id_categoria }}" @selected(old('id_categoria') == $category->id_categoria)>{{ $category->nombre }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                    <span>Marca *</span>
                    <select name="id_marca" required class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
                        <option value="">Selecciona una marca</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id_marca }}" @selected(old('id_marca') == $brand->id_marca)>{{ $brand->nombre }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                    <span>Stock inicial</span>
                    <input type="number" name="stock" value="{{ old('stock', 0) }}" required min="0" step="1" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
                </label>
                <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                    <span>Stock mínimo *</span>
                    <input type="number" name="stock_minimo" value="{{ old('stock_minimo', 0) }}" required min="0" step="1" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
                </label>
                <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                    <span>Costo unitario (S/) *</span>
                    <input type="number" name="precio_compra" value="{{ old('precio_compra') }}" required min="0" step="0.01" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
                </label>
                <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                    <span>Precio de venta (S/) *</span>
                    <input type="number" name="precio_venta" value="{{ old('precio_venta') }}" required min="0" step="0.01" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
                </label>
                <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                    <span>Precio mayorista (S/)</span>
                    <input type="number" name="precio_venta_mayor" value="{{ old('precio_venta_mayor') }}" min="0" step="0.01" placeholder="Igual al precio de venta" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
                </label>
                <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                    <span>Cantidad mínima mayorista</span>
                    <input type="number" name="cantidad_mayorista" value="{{ old('cantidad_mayorista', 2) }}" min="1" step="1" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
                </label>
                <label class="space-y-1.5 text-xs font-semibold text-slate-300 sm:col-span-2 xl:col-span-4">
                    <span>Descripción</span>
                    <textarea name="descripcion" rows="2" maxlength="5000" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">{{ old('descripcion') }}</textarea>
                </label>
                <div class="flex flex-wrap items-center gap-3 sm:col-span-2 xl:col-span-4">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="es_alquilable" value="1" @checked(old('es_alquilable')) class="rounded border-slate-600 bg-slate-900 text-amber-500 focus:ring-amber-500">
                        También se alquila
                    </label>
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-300">
                        <span>Precio alquiler/día (S/)</span>
                        <input type="number" name="precio_alquiler_dia" value="{{ old('precio_alquiler_dia', 0) }}" min="0" step="0.01" class="w-36 rounded-xl border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
                    </label>
                    <button type="submit" class="ml-auto inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-amber-400">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Guardar producto
                    </button>
                </div>
            </form>
        </details>
    @endif

    <section class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800">
        <div class="border-b border-slate-700 p-5">
            <div class="mb-4">
                <h2 class="font-bold text-slate-100">Existencias</h2>
                <p class="mt-1 text-xs text-slate-400">Busca por nombre o código y revisa productos bajo su nivel mínimo.</p>
            </div>
            <form action="{{ route('inventario.index') }}" method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_200px_auto]">
                <label class="relative">
                    <span class="sr-only">Buscar producto</span>
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Buscar por producto o código..." class="w-full rounded-xl border border-slate-600 bg-slate-900 py-2.5 pl-9 pr-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none">
                </label>
                <label>
                    <span class="sr-only">Filtrar por disponibilidad</span>
                    <select name="stock" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
                        <option value="">Todos los niveles</option>
                        <option value="bajo" @selected(($filters['stock'] ?? '') === 'bajo')>Por reponer</option>
                        <option value="agotado" @selected(($filters['stock'] ?? '') === 'agotado')>Agotados</option>
                    </select>
                </label>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-slate-700 px-4 py-2.5 text-sm font-bold text-slate-100 transition hover:bg-slate-600">
                        <i class="fa-solid fa-filter"></i>
                        Filtrar
                    </button>
                    <a href="{{ route('inventario.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-600 px-3 py-2.5 text-sm text-slate-300 transition hover:bg-slate-700" aria-label="Limpiar filtros">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] text-left text-sm">
                <thead class="bg-slate-900/70 text-[10px] uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-3">Producto</th>
                        <th class="px-5 py-3">Categoría / Marca</th>
                        <th class="px-5 py-3 text-center">Existencia</th>
                        <th class="px-5 py-3 text-center">Mínimo</th>
                        <th class="px-5 py-3 text-right">Costo</th>
                        @if($canManageInventory)
                            <th class="px-5 py-3 text-right">Acción</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/80">
                    @forelse($products as $product)
                        <tr class="align-top transition hover:bg-slate-700/20">
                            <td class="px-5 py-4">
                                <span class="font-bold text-amber-400">{{ $product->codigo }}</span>
                                <span class="mt-1 block font-semibold text-slate-100">{{ $product->nombre }}</span>
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-400">
                                <span class="block">{{ $product->categoria?->nombre ?? 'Sin categoría' }}</span>
                                <span class="mt-1 block">{{ $product->marca?->nombre ?? 'Sin marca' }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex min-w-20 items-center justify-center rounded-full border px-3 py-1 text-xs font-bold {{ $product->stock <= $product->stock_minimo ? 'border-rose-500/30 bg-rose-500/10 text-rose-300' : 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300' }}">
                                    {{ number_format($product->stock) }} und.
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center text-slate-300">{{ number_format($product->stock_minimo) }} und.</td>
                            <td class="px-5 py-4 text-right font-semibold text-slate-200">S/ {{ number_format((float) $product->precio_compra, 2) }}</td>
                            @if($canManageInventory)
                                <td class="px-5 py-4 text-right">
                                    <details class="relative inline-block text-left" @if((string) old('_inventario_producto') === (string) $product->id_producto) open @endif>
                                        <summary class="inline-flex cursor-pointer list-none items-center gap-2 rounded-lg border border-slate-600 px-3 py-2 text-xs font-bold text-slate-200 transition hover:border-amber-400 hover:text-amber-300">
                                            <i class="fa-solid fa-arrow-right-arrow-left"></i>
                                            Movimiento
                                        </summary>
                                        <div class="absolute right-0 z-20 mt-2 w-72 rounded-xl border border-slate-600 bg-slate-900 p-4 text-left shadow-2xl">
                                            <p class="mb-3 text-xs font-bold text-slate-100">{{ $product->nombre }}</p>
                                            <form action="{{ route('inventario.movimientos.store', $product) }}" method="POST" class="space-y-3">
                                                @csrf
                                                <input type="hidden" name="_inventario_producto" value="{{ $product->id_producto }}">
                                                <label class="block space-y-1 text-xs font-semibold text-slate-300">
                                                    <span>Operación</span>
                                                    <select name="tipo" required class="w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-slate-100">
                                                        <option value="entrada" @selected(old('tipo') === 'entrada')>Entrada</option>
                                                        <option value="salida" @selected(old('tipo') === 'salida')>Salida</option>
                                                        <option value="ajuste" @selected(old('tipo') === 'ajuste')>Ajuste por conteo físico</option>
                                                    </select>
                                                </label>
                                                <label class="block space-y-1 text-xs font-semibold text-slate-300">
                                                    <span>Cantidad o stock contado</span>
                                                    <input type="number" name="cantidad" value="{{ old('cantidad', 1) }}" required min="0" step="1" class="w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-slate-100">
                                                    <span class="block font-normal text-slate-500">En un ajuste ingresa el total contado, no la diferencia.</span>
                                                </label>
                                                <label class="block space-y-1 text-xs font-semibold text-slate-300">
                                                    <span>Motivo *</span>
                                                    <input type="text" name="motivo" value="{{ old('motivo') }}" required maxlength="180" class="w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-slate-100">
                                                </label>
                                                <label class="block space-y-1 text-xs font-semibold text-slate-300">
                                                    <span>Documento / referencia</span>
                                                    <input type="text" name="referencia" value="{{ old('referencia') }}" maxlength="100" class="w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-slate-100">
                                                </label>
                                                <button type="submit" class="w-full rounded-lg bg-amber-500 px-3 py-2 text-xs font-bold text-slate-950 transition hover:bg-amber-400">Registrar movimiento</button>
                                            </form>
                                        </div>
                                    </details>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canManageInventory ? 6 : 5 }}" class="px-5 py-12 text-center">
                                <i class="fa-solid fa-box-open text-3xl text-slate-600"></i>
                                <p class="mt-3 font-semibold text-slate-300">No se encontraron productos.</p>
                                <p class="mt-1 text-xs text-slate-500">Prueba con otros filtros o registra un producto nuevo.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-700 p-4">{{ $products->links() }}</div>
    </section>

    <section id="historial" class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800">
        <div class="border-b border-slate-700 p-5">
            <h2 class="font-bold text-slate-100">Historial de movimientos</h2>
            <p class="mt-1 text-xs text-slate-400">Incluye ventas, alquileres, devoluciones y ajustes manuales.</p>
            <form action="{{ route('inventario.index') }}#historial" method="GET" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_180px_170px_170px_auto]">
                <label>
                    <span class="sr-only">Buscar producto en movimientos</span>
                    <input type="search" name="producto_historial" value="{{ $filters['producto_historial'] ?? '' }}" placeholder="Producto o código..." class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:border-amber-400 focus:outline-none">
                </label>
                <label>
                    <span class="sr-only">Tipo de movimiento</span>
                    <select name="tipo_movimiento" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
                        <option value="">Todos los movimientos</option>
                        <option value="entrada" @selected(($filters['tipo_movimiento'] ?? '') === 'entrada')>Entradas</option>
                        <option value="salida" @selected(($filters['tipo_movimiento'] ?? '') === 'salida')>Salidas</option>
                        <option value="ajuste" @selected(($filters['tipo_movimiento'] ?? '') === 'ajuste')>Ajustes</option>
                    </select>
                </label>
                <label>
                    <span class="sr-only">Desde</span>
                    <input type="date" name="fecha_desde" value="{{ $filters['fecha_desde'] ?? '' }}" aria-label="Desde" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
                </label>
                <label>
                    <span class="sr-only">Hasta</span>
                    <input type="date" name="fecha_hasta" value="{{ $filters['fecha_hasta'] ?? '' }}" aria-label="Hasta" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none">
                </label>
                <button type="submit" class="rounded-xl bg-slate-700 px-4 py-2.5 text-sm font-bold text-slate-100 transition hover:bg-slate-600">
                    <i class="fa-solid fa-filter mr-1"></i>
                    Filtrar
                </button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[850px] text-left text-sm">
                <thead class="bg-slate-900/70 text-[10px] uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-3">Fecha</th>
                        <th class="px-5 py-3">Producto</th>
                        <th class="px-5 py-3">Tipo</th>
                        <th class="px-5 py-3 text-right">Cantidad</th>
                        <th class="px-5 py-3 text-right">Stock resultante</th>
                        <th class="px-5 py-3">Motivo / referencia</th>
                        <th class="px-5 py-3">Responsable</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/80">
                    @forelse($movements as $movement)
                        <tr class="hover:bg-slate-700/20">
                            <td class="whitespace-nowrap px-5 py-3.5 text-xs text-slate-400">{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-3.5">
                                <span class="block font-semibold text-slate-100">{{ $movement->producto?->nombre ?? 'Producto eliminado' }}</span>
                                <span class="mt-0.5 block text-xs text-slate-500">{{ $movement->producto?->codigo }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                @php
                                    $movementLabel = match ($movement->tipo) {
                                        'entrada' => 'Entrada',
                                        'salida' => 'Salida',
                                        'ajuste' => 'Ajuste',
                                        default => ucfirst($movement->tipo),
                                    };
                                @endphp
                                <span class="rounded-full border px-2.5 py-1 text-xs font-bold {{ $movement->tipo === 'entrada' ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300' : ($movement->tipo === 'salida' ? 'border-rose-500/30 bg-rose-500/10 text-rose-300' : 'border-amber-500/30 bg-amber-500/10 text-amber-300') }}">
                                    {{ $movementLabel }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right font-bold text-slate-200">{{ number_format($movement->cantidad) }}</td>
                            <td class="px-5 py-3.5 text-right font-bold text-slate-100">{{ number_format($movement->stock_resultante) }}</td>
                            <td class="max-w-xs px-5 py-3.5 text-xs text-slate-300">
                                {{ $movement->motivo }}
                                @if($movement->referencia)
                                    <span class="mt-1 block text-slate-500">Ref: {{ $movement->referencia }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-300">{{ $movement->usuario?->nombre ?? 'Usuario no disponible' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">No hay movimientos que coincidan con los filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-700 p-4">{{ $movements->links() }}</div>
    </section>
</div>
@endsection
