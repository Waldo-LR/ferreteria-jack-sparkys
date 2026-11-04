@extends('layouts.app')

@section('title', 'Registro de Auditoría - Ferretería Jack Sparkys S.A.C.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-100">Registro de Auditoría</h1>
            <p class="text-xs text-slate-400">Consulta cronológica de las acciones registradas en el sistema.</p>
        </div>
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-2">
            <span class="block text-[10px] font-bold uppercase text-amber-300">Registros encontrados</span>
            <span class="text-lg font-extrabold text-amber-400">{{ $auditorias->total() }}</span>
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-300" role="alert">
            <p class="font-bold">Revisa los filtros:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('auditoria.index') }}" method="GET" class="grid grid-cols-1 gap-3 rounded-2xl border border-slate-700 bg-slate-800 p-4 lg:grid-cols-[minmax(0,1fr)_180px_180px_auto_auto] lg:items-end">
        <div>
            <label for="buscar" class="mb-1 block text-xs font-semibold text-slate-300">Búsqueda</label>
            <input id="buscar" type="search" name="buscar" value="{{ request('buscar') }}"
                placeholder="Acción, usuario, tabla, detalle o IP"
                maxlength="100"
                class="w-full rounded-lg border border-slate-700 bg-slate-900 p-2.5 text-sm text-slate-100 placeholder:text-slate-500 focus:border-amber-500 focus:outline-none">
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
        <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-amber-400">
            Buscar
        </button>
        @if (request()->hasAny(['buscar', 'fecha_desde', 'fecha_hasta']))
            <a href="{{ route('auditoria.index') }}" class="rounded-lg border border-slate-600 px-4 py-2.5 text-center text-sm font-semibold text-slate-300 transition hover:bg-slate-700">
                Limpiar
            </a>
        @endif
    </form>

    <section class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="bg-slate-900/80 text-xs uppercase text-slate-400">
                    <tr>
                        <th class="p-4">Fecha y hora</th>
                        <th class="p-4">Usuario</th>
                        <th class="p-4">Acción</th>
                        <th class="p-4">Registro afectado</th>
                        <th class="p-4">Detalle</th>
                        <th class="p-4">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse ($auditorias as $auditoria)
                        <tr class="align-top transition hover:bg-slate-700/40">
                            <td class="whitespace-nowrap p-4 text-xs text-slate-300">
                                {{ $auditoria->created_at?->format('d/m/Y H:i:s') ?? 'Sin fecha' }}
                            </td>
                            <td class="p-4">
                                <div class="font-semibold text-slate-100">{{ $auditoria->usuario?->nombre ?? 'Sistema / usuario eliminado' }}</div>
                                @if ($auditoria->usuario?->usuario)
                                    <div class="mt-1 text-[11px] text-slate-400">{{ '@' . $auditoria->usuario->usuario }}</div>
                                @endif
                            </td>
                            <td class="p-4">
                                <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-300">
                                    {{ $auditoria->accion }}
                                </span>
                            </td>
                            <td class="p-4 text-xs text-slate-300">
                                <div>{{ $auditoria->tabla_afectada }}</div>
                                @if ($auditoria->id_registro !== null)
                                    <div class="mt-1 text-slate-500">ID {{ $auditoria->id_registro }}</div>
                                @endif
                            </td>
                            <td class="max-w-md whitespace-normal p-4 text-xs text-slate-300">{{ $auditoria->detalle ?: 'Sin detalle' }}</td>
                            <td class="whitespace-nowrap p-4 text-xs text-slate-400">{{ $auditoria->ip ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center text-sm text-slate-400">
                                No se encontraron registros de auditoría con esos filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($auditorias->hasPages())
            <div class="border-t border-slate-700 p-3">{{ $auditorias->links() }}</div>
        @endif
    </section>
</div>
@endsection
