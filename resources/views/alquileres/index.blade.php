@extends('layouts.app')

@section('title', 'Alquiler de Herramientas y Equipos - Ferretería Jack Sparkys S.A.C.')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-slate-100">Alquiler de Maquinaria y Herramientas</h1>
            <p class="text-xs text-slate-400">Control de entregas, plazos y devoluciones de equipos</p>
        </div>
    </div>

    <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-900/80 text-slate-400 uppercase text-xs">
                <tr>
                    <th class="p-4">ID</th>
                    <th class="p-4">Equipo / Herramienta</th>
                    <th class="p-4">Cliente</th>
                    <th class="p-4">Plazo Entrega - Devolución</th>
                    <th class="p-4 text-right">Precio/Día</th>
                    <th class="p-4 text-right">Total Alquiler</th>
                    <th class="p-4 text-center">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse($alquileres as $alq)
                    <tr class="hover:bg-slate-700/40">
                        <td class="p-4 font-bold text-amber-400">#ALQ-{{ $alq->id_alquiler }}</td>
                        <td class="p-4 font-bold text-slate-200">
                            {{ $alq->producto->nombre ?? 'Equipo' }}
                            <div class="text-xs text-slate-400 font-normal">Cant: {{ $alq->cantidad }} und.</div>
                        </td>
                        <td class="p-4 text-slate-300">{{ $alq->cliente->nombres ?? '-' }}</td>
                        <td class="p-4 text-xs text-slate-300">
                            <div><span class="text-slate-500">Entrega:</span> {{ $alq->fecha_entrega }}</div>
                            <div><span class="text-slate-500">Pactada:</span> {{ $alq->fecha_pactada_devolucion }}</div>
                        </td>
                        <td class="p-4 text-right font-medium text-slate-300">S/ {{ number_format($alq->precio_dia, 2) }}</td>
                        <td class="p-4 text-right font-bold text-emerald-400">S/ {{ number_format($alq->total_alquiler, 2) }}</td>
                        <td class="p-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase bg-blue-500/20 text-blue-400 border border-blue-500/30">
                                {{ $alq->estado }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-4 text-center text-slate-400">No hay alquileres activos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
