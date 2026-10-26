@extends('layouts.app')

@section('title', 'Ventas y Facturación - Ferretería Jack Sparkys S.A.C.')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-slate-100">Módulo de Ventas & POS</h1>
            <p class="text-xs text-slate-400">Histórico de comprobantes emitidos (Boletas, Facturas y Notas de Venta)</p>
        </div>
    </div>

    <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-900/80 text-slate-400 uppercase text-xs">
                <tr>
                    <th class="p-4">N° Comprobante</th>
                    <th class="p-4">Tipo</th>
                    <th class="p-4">Cliente</th>
                    <th class="p-4">Fecha</th>
                    <th class="p-4">Medio Pago</th>
                    <th class="p-4 text-right">Total Soles</th>
                    <th class="p-4 text-center">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse($ventas as $v)
                    <tr class="hover:bg-slate-700/40">
                        <td class="p-4 font-bold text-amber-400">{{ $v->serie }}-{{ str_pad($v->numero, 6, '0', STR_PAD_LEFT) }}</td>
                        <td class="p-4 uppercase text-xs text-slate-300 font-semibold">{{ str_replace('_', ' ', $v->tipo_comprobante) }}</td>
                        <td class="p-4 font-medium text-slate-200">{{ $v->cliente->nombres ?? 'Cliente General' }}</td>
                        <td class="p-4 text-slate-400 text-xs">{{ $v->fecha_emision }}</td>
                        <td class="p-4 uppercase text-xs text-slate-300">{{ $v->medio_pago }}</td>
                        <td class="p-4 text-right font-extrabold text-emerald-400">S/ {{ number_format($v->total, 2) }}</td>
                        <td class="p-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                {{ $v->estado }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-4 text-center text-slate-400">No hay ventas registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
