@extends('layouts.app')

@section('title', 'Panel Principal - Ferretería Jack Sparkys S.A.C.')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-slate-800 p-5 rounded-xl border border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-400 font-medium">Productos Activos</span>
                <span class="text-2xl p-2 bg-amber-500/10 rounded-lg">📦</span>
            </div>
            <div class="text-3xl font-extrabold text-slate-100 mt-2">{{ $totalProductos }}</div>
            <div class="text-xs text-slate-400 mt-1">Items registrados en catálogo</div>
        </div>

        <div class="bg-slate-800 p-5 rounded-xl border border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-400 font-medium">Ventas Emitidas</span>
                <span class="text-2xl p-2 bg-emerald-500/10 rounded-lg">💰</span>
            </div>
            <div class="text-3xl font-extrabold text-emerald-400 mt-2">S/ {{ number_format($totalVentas, 2) }}</div>
            <div class="text-xs text-emerald-400/80 mt-1">Total acumulado en soles</div>
        </div>

        <div class="bg-slate-800 p-5 rounded-xl border border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-400 font-medium">Alquileres Vigentes</span>
                <span class="text-2xl p-2 bg-blue-500/10 rounded-lg">🛠️</span>
            </div>
            <div class="text-3xl font-extrabold text-blue-400 mt-2">{{ $alquileresVigentes }}</div>
            <div class="text-xs text-blue-400/80 mt-1">Herramientas en campo</div>
        </div>

        <div class="bg-slate-800 p-5 rounded-xl border border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-400 font-medium">Estado de Caja</span>
                <span class="text-2xl p-2 bg-indigo-500/10 rounded-lg">💵</span>
            </div>
            <div class="text-xl font-bold mt-2 flex items-center gap-2">
                @if($cajaActual)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">ABIERTA</span>
                    <span class="text-slate-100 text-lg">S/ {{ number_format($cajaActual->monto_final, 2) }}</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/30">CERRADA</span>
                @endif
            </div>
            <div class="text-xs text-slate-400 mt-1">Caja Chica / Control Turno</div>
        </div>
    </div>

    @if(count($stockBajo) > 0)
    <div class="bg-rose-900/20 border border-rose-500/30 rounded-xl p-5">
        <h3 class="font-bold text-rose-400 text-base mb-3 flex items-center gap-2">
            <span>⚠️</span> Alerta de Stock Mínimo
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($stockBajo as $item)
                <div class="bg-slate-800/80 p-3 rounded-lg border border-rose-500/20 flex justify-between items-center text-sm">
                    <div>
                        <div class="font-bold text-slate-200">{{ $item->nombre }}</div>
                        <div class="text-xs text-slate-400">Cód: {{ $item->codigo }}</div>
                    </div>
                    <div class="text-right">
                        <span class="text-rose-400 font-extrabold text-base">{{ $item->stock }}</span>
                        <span class="text-xs text-slate-400 block">mín: {{ $item->stock_minimo }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
