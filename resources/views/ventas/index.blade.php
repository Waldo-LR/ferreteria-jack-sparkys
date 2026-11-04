@extends('layouts.app')

@section('title', 'Historial de Ventas - Ferretería Jack Sparkys S.A.C.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-100">Historial de Ventas</h1>
            <p class="text-xs text-slate-400">Consulta comprobantes emitidos y accede a su vista previa, impresión o PDF.</p>
        </div>
        <a href="{{ route('ventas.pos') }}" class="rounded-lg bg-amber-500 px-4 py-2.5 text-center text-sm font-bold text-slate-950 transition hover:bg-amber-400">
            Nueva venta
        </a>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-300" role="alert">
            <p class="font-bold">Revisa los filtros de búsqueda:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('ventas.index') }}" method="GET" class="grid grid-cols-1 gap-3 rounded-2xl border border-slate-700 bg-slate-800 p-4 lg:grid-cols-[minmax(0,1fr)_170px_150px_160px_160px_auto_auto] lg:items-end">
        <div>
            <label for="buscar" class="mb-1 block text-xs font-semibold text-slate-300">Buscar</label>
            <input id="buscar" type="search" name="buscar" value="{{ request('buscar') }}" maxlength="100"
                placeholder="Comprobante, cliente o documento"
                class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:border-amber-500 focus:outline-none">
        </div>
        <div>
            <label for="tipo_comprobante" class="mb-1 block text-xs font-semibold text-slate-300">Comprobante</label>
            <select id="tipo_comprobante" name="tipo_comprobante" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                <option value="">Todos</option>
                <option value="nota_venta" @selected(request('tipo_comprobante') === 'nota_venta')>Nota de venta</option>
                <option value="boleta" @selected(request('tipo_comprobante') === 'boleta')>Boleta</option>
                <option value="factura" @selected(request('tipo_comprobante') === 'factura')>Factura</option>
            </select>
        </div>
        <div>
            <label for="estado" class="mb-1 block text-xs font-semibold text-slate-300">Estado</label>
            <select id="estado" name="estado" class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
                <option value="">Todos</option>
                <option value="emitida" @selected(request('estado') === 'emitida')>Emitida</option>
                <option value="anulada" @selected(request('estado') === 'anulada')>Anulada</option>
            </select>
        </div>
        <div>
            <label for="fecha_desde" class="mb-1 block text-xs font-semibold text-slate-300">Desde</label>
            <input id="fecha_desde" type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
        </div>
        <div>
            <label for="fecha_hasta" class="mb-1 block text-xs font-semibold text-slate-300">Hasta</label>
            <input id="fecha_hasta" type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 focus:border-amber-500 focus:outline-none">
        </div>
        <button type="submit" class="rounded-lg bg-slate-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-600">
            Filtrar
        </button>
        @if (request()->hasAny(['buscar', 'tipo_comprobante', 'estado', 'fecha_desde', 'fecha_hasta']))
            <a href="{{ route('ventas.index') }}" class="rounded-lg border border-slate-600 px-4 py-2.5 text-center text-sm font-semibold text-slate-300 transition hover:bg-slate-700">
                Limpiar
            </a>
        @endif
    </form>

    <section class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px] text-left text-sm">
                <thead class="bg-slate-900/80 text-xs uppercase text-slate-400">
                    <tr>
                        <th class="p-4">N° Comprobante</th>
                        <th class="p-4">Tipo</th>
                        <th class="p-4">Cliente</th>
                        <th class="p-4">Fecha</th>
                        <th class="p-4">Medio de pago</th>
                        <th class="p-4 text-right">Total</th>
                        <th class="p-4 text-center">Estado</th>
                        <th class="p-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse ($ventas as $venta)
                        <tr class="align-top transition hover:bg-slate-700/40">
                            <td class="whitespace-nowrap p-4 font-bold text-amber-400">
                                {{ $venta->serie }}-{{ str_pad($venta->numero, 6, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="p-4 text-xs font-semibold uppercase text-slate-300">{{ str_replace('_', ' ', $venta->tipo_comprobante) }}</td>
                            <td class="p-4">
                                <div class="font-medium text-slate-200">{{ $venta->cliente?->nombres ?? 'Cliente General' }}</div>
                                @if ($venta->cliente?->dni_ruc)
                                    <div class="mt-1 text-[11px] text-slate-400">{{ $venta->cliente->dni_ruc }}</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap p-4 text-xs text-slate-400">{{ $venta->fecha_emision }}</td>
                            <td class="p-4 text-xs uppercase text-slate-300">{{ $venta->medio_pago }}</td>
                            <td class="whitespace-nowrap p-4 text-right font-extrabold text-emerald-400">S/ {{ number_format($venta->total, 2) }}</td>
                            <td class="p-4 text-center">
                                <span class="rounded-full border px-2.5 py-1 text-xs font-bold uppercase {{ $venta->estado === 'anulada' ? 'border-rose-500/30 bg-rose-500/10 text-rose-300' : 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300' }}">
                                    {{ $venta->estado }}
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('ventas.imprimir', $venta->id_venta) }}" target="_blank" rel="noopener"
                                        class="whitespace-nowrap rounded-lg border border-slate-600 px-3 py-1.5 text-xs font-semibold text-slate-200 transition hover:border-amber-500 hover:text-amber-300">
                                        Ver comprobante
                                    </a>
                                    <a href="{{ route('ventas.imprimir', ['id' => $venta->id_venta, 'auto' => 1]) }}" target="_blank" rel="noopener"
                                        class="whitespace-nowrap rounded-lg border border-slate-600 px-3 py-1.5 text-xs font-semibold text-slate-200 transition hover:bg-slate-700">
                                        Imprimir
                                    </a>
                                    <a href="{{ route('ventas.pdf', $venta->id_venta) }}"
                                        class="whitespace-nowrap rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-bold text-slate-950 transition hover:bg-amber-400">
                                        Descargar PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-10 text-center text-sm text-slate-400">
                                No se encontraron ventas con esos filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($ventas->hasPages())
            <div class="border-t border-slate-700 p-3">{{ $ventas->links() }}</div>
        @endif
    </section>
</div>
@endsection
