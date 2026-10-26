@extends('layouts.app')

@section('title', 'Gestión de Cobranzas y Moras - Ferretería Jack Sparkys')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-hand-holding-dollar text-amber-500"></i> Módulo de Cobranzas & Control de Morosos
            </h1>
            <p class="text-xs text-slate-400">Monitoreo de clientes con alquileres vencidos, recargo por mora y recuperación de herramientas en obra</p>
        </div>
        <div class="bg-rose-500/10 border border-rose-500/30 px-4 py-2 rounded-xl text-right">
            <span class="text-[10px] text-rose-300 font-bold uppercase block">Total Moras Pendientes</span>
            <span class="text-lg font-extrabold text-rose-400">S/ {{ number_format($totalPendiente, 2) }}</span>
        </div>
    </div>

    <!-- Buscador de Clientes Morosos -->
    <div class="bg-slate-800 p-4 rounded-2xl border border-slate-700">
        <form action="{{ route('cobranzas.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por cliente, DNI/RUC, dirección u obra..." class="w-full bg-slate-900 border border-slate-700 rounded-xl py-2.5 pl-9 pr-4 text-xs text-slate-200 focus:outline-none focus:border-amber-500">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
            </div>
            <select name="estado" class="bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-amber-500">
                <option value="">Todos los Estados</option>
                <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Con Mora Pendiente</option>
                <option value="pagado" {{ request('estado') == 'pagado' ? 'selected' : '' }}>Moras Pagadas</option>
            </select>
            <button type="submit" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs transition">
                Filtrar Registros
            </button>
        </form>
    </div>
    <!-- Lista de Cobranzas -->
    <div class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-900/80 text-slate-400 uppercase text-[10px]">
                    <tr>
                        <th class="p-4">N° Alquiler</th>
                        <th class="p-4">Cliente Moroso & Contacto</th>
                        <th class="p-4">Equipo & Ubicación de Obra</th>
                        <th class="p-4 text-center">Días Atraso</th>
                        <th class="p-4 text-right">Monto Mora</th>
                        <th class="p-4 text-center">Estado Mora</th>
                        <th class="p-4 text-center w-36">Acciones de Cobro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($cobranzas as $cob)
                        <tr class="hover:bg-slate-700/40">
                            <td class="p-4 font-bold text-amber-400">#ALQ-{{ $cob->id_alquiler }}</td>
                            <td class="p-4">
                                <div class="font-bold text-slate-200 text-sm">{{ $cob->alquiler->cliente->nombres ?? 'Cliente' }}</div>
                                <div class="text-slate-400 text-[11px]"><i class="fa-solid fa-id-card text-amber-500/80 mr-1"></i> {{ $cob->alquiler->cliente->tipo_documento ?? 'DNI' }}: {{ $cob->alquiler->cliente->dni_ruc ?? '-' }}</div>
                                <div class="text-slate-400 text-[11px]"><i class="fa-solid fa-phone text-emerald-400 mr-1"></i> {{ $cob->alquiler->cliente->telefono ?? '-' }}</div>
                                <div class="text-slate-400 text-[11px]"><i class="fa-solid fa-location-dot text-rose-400 mr-1"></i> {{ $cob->alquiler->cliente->direccion ?? '-' }}</div>
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-slate-200">{{ $cob->alquiler->producto->nombre ?? 'Equipo' }}</div>
                                <div class="text-amber-400 text-[11px] font-semibold mt-1">
                                    <i class="fa-solid fa-building-flag mr-1"></i> Ubicación en Obra: {{ $cob->alquiler->ubicacion_herramienta ?? 'En obra del cliente' }}
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-black bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                    {{ $cob->dias_atraso }} días
                                </span>
                            </td>
                            <td class="p-4 text-right font-extrabold text-rose-400 text-sm">S/ {{ number_format($cob->monto_mora, 2) }}</td>
                            <td class="p-4 text-center">
                                @if($cob->estado === 'pendiente')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                        ⚠️ Mora Pendiente
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                        ✅ Mora Cancelada
                                    </span>
                                @endif
                            </td>                            <td class="p-4 text-center space-y-1">
                                @if($cob->estado === 'pendiente')
                                    <form action="{{ route('cobranzas.pagar', $cob->id_cobranza) }}" method="POST" onsubmit="return confirm('¿Confirma registrar el cobro de la mora por S/ {{ $cob->monto_mora }}?');">
                                        @csrf
                                        <input type="hidden" name="monto" value="{{ $cob->monto_mora }}">
                                        <input type="hidden" name="medio_pago" value="efectivo">
                                        <button type="submit" class="w-full py-1.5 px-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold rounded-lg text-[11px] transition shadow">
                                            💵 Cobrar Mora
                                        </button>
                                    </form>
                                @endif

                                @if($cob->alquiler->estado !== 'devuelto')
                                    <form action="{{ route('cobranzas.recuperar', $cob->id_alquiler) }}" method="POST" onsubmit="return confirm('¿Confirma registrar la recuperación de la herramienta en campo?');">
                                        @csrf
                                        <input type="hidden" name="estado_fisico" value="Bueno - Recuperado por cobranzas">
                                        <input type="hidden" name="observaciones" value="Herramienta recuperada de obra por el gestor de cobranzas.">
                                        <button type="submit" class="w-full py-1.5 px-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-lg text-[11px] transition shadow">
                                            🛠️ Recuperar Equipo
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400 italic">
                                <i class="fa-solid fa-circle-check text-emerald-400 text-3xl mb-2 block"></i>
                                ¡Excelente! No hay registros de clientes morosos o cobro de mora pendientes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection