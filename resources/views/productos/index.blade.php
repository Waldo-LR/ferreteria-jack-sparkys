@extends('layouts.app')

@section('title', 'Inventario de Productos - Ferretería Jack Sparkys S.A.C.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-100">Catálogo de Productos y Herramientas</h1>
            <p class="text-xs text-slate-400">Gestión de stock, precios de venta y precios de alquiler por día</p>
        </div>
    </div>

    <!-- Lista de Productos -->
    <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-900/80 text-slate-400 uppercase text-xs">
                    <tr>
                        <th class="p-4">Código</th>
                        <th class="p-4">Producto</th>
                        <th class="p-4">Categoría / Marca</th>
                        <th class="p-4 text-center">Stock</th>
                        <th class="p-4 text-right">P. Compra</th>
                        <th class="p-4 text-right">P. Venta</th>
                        <th class="p-4 text-right">P. Alquiler/Día</th>
                        <th class="p-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($productos as $p)
                        <tr class="hover:bg-slate-700/40">
                            <td class="p-4 font-bold text-amber-400">{{ $p->codigo }}</td>
                            <td class="p-4">
                                <div class="font-bold text-slate-200">{{ $p->nombre }}</div>
                                <div class="text-xs text-slate-400 line-clamp-1">{{ $p->descripcion }}</div>
                            </td>
                            <td class="p-4 text-xs text-slate-300">
                                <div><span class="text-slate-400">Cat:</span> {{ $p->categoria->nombre ?? 'N/A' }}</div>
                                <div><span class="text-slate-400">Marca:</span> {{ $p->marca->nombre ?? 'N/A' }}</div>
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $p->stock <= $p->stock_minimo ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' }}">
                                    {{ $p->stock }} und.
                                </span>
                            </td>
                            <td class="p-4 text-right font-medium text-slate-300">S/ {{ number_format($p->precio_compra, 2) }}</td>
                            <td class="p-4 text-right font-bold text-emerald-400">S/ {{ number_format($p->precio_venta, 2) }}</td>
                            <td class="p-4 text-right">
                                @if($p->es_alquilable)
                                    <span class="font-bold text-blue-400">S/ {{ number_format($p->precio_alquiler_dia, 2) }}</span>
                                @else
                                    <span class="text-xs text-slate-500">No alquilable</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <form action="{{ route('productos.destroy', $p->id_producto) }}" method="POST" onsubmit="return confirm('¿Confirma dar de baja este producto?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 bg-rose-500/10 text-rose-400 border border-rose-500/30 hover:bg-rose-500 hover:text-white rounded text-xs transition">
                                        Dar Baja
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center text-slate-400">No hay productos disponibles.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
