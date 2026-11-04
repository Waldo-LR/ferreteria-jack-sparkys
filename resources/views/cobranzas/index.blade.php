@extends('layouts.app')

@section('title', 'Historial de Cobranzas - Ferretería Jack Sparkys')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-bold text-slate-100">
                <i class="fa-solid fa-hand-holding-dollar text-amber-400"></i>
                Cobranzas y Moras
            </h1>
            <p class="mt-1 text-sm text-slate-400">Saldos de alquiler, mora y daños; pagos parciales y su historial.</p>
        </div>
        <a href="{{ route('alquileres.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-amber-300 hover:text-amber-200">
            <i class="fa-solid fa-clock-rotate-left"></i>
            Historial de alquileres
        </a>
    </div>

    @unless($cajaAlquileresAbierta)
        <div class="flex flex-col gap-2 rounded-xl border border-amber-500/30 bg-amber-500/10 p-3 text-sm text-amber-200 sm:flex-row sm:items-center sm:justify-between">
            <span>La caja de Alquileres/Cobranzas está cerrada; no se pueden registrar pagos ni devolver garantías.</span>
            <a href="{{ route('caja.index') }}" class="font-bold underline underline-offset-2">Ir a Control de Caja</a>
        </div>
    @endunless

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4">
            <p class="text-xs font-medium text-rose-200">Saldo total por cobrar</p>
            <p class="mt-1 text-2xl font-extrabold text-rose-300">S/ {{ number_format($totalPendiente, 2) }}</p>
        </div>
        <div class="rounded-xl border border-slate-700 bg-slate-800 p-4">
            <p class="text-xs font-medium text-slate-400">Registros en el historial</p>
            <p class="mt-1 text-2xl font-bold text-slate-100">{{ $cobranzas->total() }}</p>
        </div>
        <div class="rounded-xl border border-blue-500/30 bg-blue-500/10 p-4">
            <p class="text-xs font-medium text-blue-200">Tasa de mora vigente</p>
            <p class="mt-1 text-2xl font-bold text-blue-300">{{ number_format((float) $moraPorcentaje, 2) }}% diario sobre la tarifa</p>
        </div>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800">
        <div class="border-b border-slate-700 p-5">
            <h2 class="font-bold text-slate-100">Historial de moras, saldos y cobranzas</h2>
            <p class="mt-1 text-xs text-slate-400">Cada abono se registra en la Caja de Alquileres/Cobranzas.</p>
            <form action="{{ route('cobranzas.index') }}" method="GET" class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-[minmax(12rem,1fr)_12rem_12rem_auto]">
                <input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" maxlength="100" placeholder="Cliente, DNI, equipo o ubicación" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-xs text-slate-100">
                <select name="tipo" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-xs text-slate-100">
                    <option value="">Todos los conceptos</option>
                    <option value="alquiler" @selected(($filters['tipo'] ?? '') === 'alquiler')>Saldo de alquiler</option>
                    <option value="mora" @selected(($filters['tipo'] ?? '') === 'mora')>Mora por atraso</option>
                    <option value="dano" @selected(($filters['tipo'] ?? '') === 'dano')>Daño del equipo</option>
                </select>
                <select name="estado" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-xs text-slate-100">
                    <option value="">Todos los estados</option>
                    <option value="pendiente" @selected(($filters['estado'] ?? '') === 'pendiente')>Pendientes</option>
                    <option value="pagado" @selected(($filters['estado'] ?? '') === 'pagado')>Canceladas</option>
                </select>
                <button class="rounded-lg bg-amber-500 px-4 py-2.5 text-xs font-bold text-slate-950 hover:bg-amber-400">Filtrar</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1100px] text-left text-xs">
                <thead class="bg-slate-900/80 uppercase text-slate-400">
                    <tr>
                        <th class="p-4">Alquiler / cliente</th>
                        <th class="p-4">Concepto</th>
                        <th class="p-4 text-center">Atraso</th>
                        <th class="p-4 text-right">Monto / pagado / saldo</th>
                        <th class="p-4">Estado e historial de pagos</th>
                        <th class="p-4 text-center">Registrar abono</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($cobranzas as $cob)
                        @php
                            $saldoPendiente = max(0, (float) $cob->monto_mora - (float) $cob->monto_pagado);
                            $tipoLabel = match($cob->tipo) {
                                'alquiler' => 'Saldo de alquiler',
                                'dano' => 'Daño / reparación',
                                default => 'Mora por atraso',
                            };
                        @endphp
                        <tr class="align-top text-slate-300">
                            <td class="p-4">
                                <div class="font-bold text-amber-300">#ALQ-{{ $cob->id_alquiler }}</div>
                                <div class="mt-1 font-semibold text-slate-100">{{ $cob->alquiler?->cliente?->nombres ?? 'Cliente no disponible' }}</div>
                                <div class="mt-1 text-slate-400">{{ $cob->alquiler?->cliente?->dni_ruc ?? '—' }} · {{ $cob->alquiler?->cliente?->telefono ?? 'Sin teléfono' }}</div>
                                <div class="mt-1 text-slate-400">{{ $cob->alquiler?->producto?->nombre ?? 'Equipo no disponible' }}</div>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex rounded-full border px-2.5 py-1 font-bold {{ $cob->tipo === 'mora' ? 'border-rose-500/30 bg-rose-500/10 text-rose-300' : ($cob->tipo === 'dano' ? 'border-amber-500/30 bg-amber-500/10 text-amber-300' : 'border-blue-500/30 bg-blue-500/10 text-blue-300') }}">
                                    {{ $tipoLabel }}
                                </span>
                                <p class="mt-2 max-w-xs whitespace-normal text-slate-400">{{ $cob->observaciones ?: 'Sin observaciones.' }}</p>
                                @if($cob->tipo === 'dano' && (float) $cob->alquiler?->monto_garantia_aplicado > 0)
                                    <p class="mt-1 text-amber-200">Garantía aplicada: S/ {{ number_format($cob->alquiler->monto_garantia_aplicado, 2) }}</p>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                @if($cob->tipo === 'mora')
                                    <span class="font-bold text-rose-300">{{ $cob->dias_atraso }} día(s)</span>
                                    <div class="mt-1 text-slate-500">{{ number_format((float) $cob->porcentaje_aplicado, 2) }}% / día</div>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                <div>Total: <span class="font-bold text-slate-100">S/ {{ number_format($cob->monto_mora, 2) }}</span></div>
                                <div class="mt-1 text-emerald-300">Pagado: S/ {{ number_format($cob->monto_pagado, 2) }}</div>
                                <div class="mt-1 font-bold {{ $saldoPendiente > 0 ? 'text-rose-300' : 'text-slate-400' }}">Saldo: S/ {{ number_format($saldoPendiente, 2) }}</div>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex rounded-full border px-2.5 py-1 font-bold uppercase {{ $cob->estado === 'pendiente' ? 'border-rose-500/30 bg-rose-500/10 text-rose-300' : 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300' }}">
                                    {{ $cob->estado === 'pendiente' ? 'Pendiente' : 'Cancelada' }}
                                </span>
                                <div class="mt-2 space-y-1">
                                    @forelse($cob->pagos as $pago)
                                        <div class="text-slate-400">
                                            {{ $pago->created_at->format('d/m/Y H:i') }} · {{ ucfirst($pago->medio_pago) }} · {{ $pago->usuario?->nombre ?? 'Usuario' }} · <span class="text-emerald-300">S/ {{ number_format($pago->monto, 2) }}</span>
                                            @if($pago->observaciones)
                                                <div class="mt-1 text-slate-500">{{ $pago->observaciones }}</div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="text-slate-500">Sin pagos registrados.</div>
                                    @endforelse
                                </div>
                            </td>
                            <td class="p-4">
                                @if($cob->estado === 'pendiente' && $saldoPendiente > 0)
                                    <form action="{{ route('cobranzas.pagar', $cob) }}" method="POST" class="min-w-40 space-y-2">
                                        @csrf
                                        <input type="number" name="monto" min="0.01" max="{{ number_format($saldoPendiente, 2, '.', '') }}" step="0.01" value="{{ number_format($saldoPendiente, 2, '.', '') }}" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-2 py-2 text-slate-100">
                                        <select name="medio_pago" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-2 py-2 text-slate-100">
                                            @foreach(\App\Models\Caja::MEDIOS_PAGO as $medio)
                                                <option value="{{ $medio }}">{{ ucfirst($medio) }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="observaciones" maxlength="1000" placeholder="Observación (opcional)" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-2 py-2 text-slate-100">
                                        <button type="submit" @disabled(!$cajaAlquileresAbierta) class="w-full rounded-lg bg-emerald-500 px-2 py-2 font-bold text-slate-950 hover:bg-emerald-400 disabled:cursor-not-allowed disabled:opacity-50">
                                            Registrar abono
                                        </button>
                                    </form>
                                @else
                                    <span class="text-slate-500">Sin saldo pendiente</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center text-slate-400">No hay cobranzas que coincidan con el filtro.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-700 p-4">{{ $cobranzas->links() }}</div>
    </section>
</div>
@endsection
