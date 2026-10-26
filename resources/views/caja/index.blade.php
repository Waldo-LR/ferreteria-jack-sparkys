@extends('layouts.app')

@section('title', 'Control de Caja Chica - Ferretería Jack Sparkys S.A.C.')

@section('content')
<div class="space-y-6 max-w-4xl">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-slate-100">Control y Movimientos de Caja Chica</h1>
            <p class="text-xs text-slate-400">Arqueo de caja diaria e ingresos/egresos</p>
        </div>
    </div>

    @if($cajaActual)
        <div class="bg-slate-800 p-6 rounded-xl border border-slate-700 space-y-4">
            <div class="flex items-center justify-between pb-4 border-b border-slate-700">
                <div>
                    <span class="text-xs uppercase text-slate-400 font-bold">Estado de Caja</span>
                    <h2 class="text-lg font-bold text-emerald-400 flex items-center gap-2 mt-1">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span> CAJA ABIERTA
                    </h2>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-400">Fecha de Apertura</span>
                    <div class="text-sm font-semibold text-slate-200">{{ $cajaActual->fecha_apertura }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
                <div class="bg-slate-900/60 p-4 rounded-lg border border-slate-700">
                    <span class="text-xs text-slate-400 block">Monto Inicial</span>
                    <span class="text-xl font-bold text-slate-200">S/ {{ number_format($cajaActual->monto_inicial, 2) }}</span>
                </div>
                <div class="bg-slate-900/60 p-4 rounded-lg border border-slate-700">
                    <span class="text-xs text-slate-400 block">Total Ingresos</span>
                    <span class="text-xl font-bold text-emerald-400">S/ {{ number_format($cajaActual->total_ingresos, 2) }}</span>
                </div>
                <div class="bg-slate-900/60 p-4 rounded-lg border border-slate-700">
                    <span class="text-xs text-slate-400 block">Monto en Caja</span>
                    <span class="text-xl font-extrabold text-amber-400">S/ {{ number_format($cajaActual->monto_final, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Gastos de Caja -->
        <div class="bg-slate-800 rounded-xl border border-slate-700 p-5 space-y-3">
            <h3 class="font-bold text-slate-200 text-sm">Egresos y Gastos de Caja</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-900/80 text-slate-400 uppercase text-xs">
                        <tr>
                            <th class="p-3">Categoría</th>
                            <th class="p-3">Detalle</th>
                            <th class="p-3 text-right">Monto Soles</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @forelse($gastos as $g)
                            <tr>
                                <td class="p-3 uppercase text-xs font-bold text-amber-400">{{ $g->categoria }}</td>
                                <td class="p-3 text-slate-300">{{ $g->detalle }}</td>
                                <td class="p-3 text-right font-bold text-rose-400">S/ {{ number_format($g->monto, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="p-3 text-center text-slate-400">Sin egresos registrados en este turno.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-slate-800 p-8 rounded-xl border border-slate-700 text-center">
            <span class="text-4xl mb-3 block">🔒</span>
            <h3 class="text-lg font-bold text-slate-200">La caja se encuentra cerrada</h3>
            <p class="text-xs text-slate-400 mt-1">Abre un nuevo turno de caja para procesar ventas y cobros en efectivo.</p>
        </div>
    @endif
</div>
@endsection
