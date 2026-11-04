@extends('layouts.app')

@section('title', 'Reportes - Ferretería Jack Sparkys S.A.C.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-2xl font-black text-slate-100">
                <i class="fa-solid fa-chart-column text-amber-400"></i>
                Reportes
            </h1>
            <p class="mt-1 text-sm text-slate-400">Gestiona la información de tu negocio de forma sencilla.</p>
        </div>
        <div class="flex items-center gap-2 self-start rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 text-xs font-semibold text-slate-300 sm:self-auto">
            <i class="fa-regular fa-clock text-amber-400"></i>
            Generado {{ $generatedAt->format('d/m/Y H:i') }}
        </div>
    </div>

    <form action="{{ route('reportes.index') }}" method="GET" class="grid grid-cols-1 gap-3 rounded-2xl border border-slate-700 bg-slate-800 p-4 md:grid-cols-2 xl:grid-cols-[minmax(220px,1.3fr)_minmax(170px,1fr)_minmax(170px,1fr)_auto] xl:items-end">
        <label class="space-y-1.5 text-xs font-semibold text-slate-300">
            <span>Tipo de reporte</span>
            <select name="tipo" required onchange="this.form.requestSubmit()" class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/20">
                @foreach($reportTypes as $type => $label)
                    <option value="{{ $type }}" @selected($filters['tipo'] === $type)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        @unless($filters['tipo'] === 'inventario')
            <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                <span>Desde</span>
                <input type="date" name="fecha_desde" value="{{ $filters['fecha_desde'] }}" required class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/20">
            </label>
            <label class="space-y-1.5 text-xs font-semibold text-slate-300">
                <span>Hasta</span>
                <input type="date" name="fecha_hasta" value="{{ $filters['fecha_hasta'] }}" required class="w-full rounded-xl border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/20">
            </label>
        @endunless

        <div class="flex flex-wrap gap-2 md:col-span-2 xl:col-span-1">
            <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-slate-950 transition hover:bg-amber-400">
                <i class="fa-solid fa-filter"></i>
                Consultar
            </button>
            <a href="{{ route('reportes.pdf', $filters) }}" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-blue-500">
                <i class="fa-solid fa-file-pdf"></i>
                Exportar PDF
            </a>
            <button type="button" onclick="window.print()" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-slate-600 bg-slate-700 px-4 py-2.5 text-sm font-bold text-slate-100 transition hover:bg-slate-600">
                <i class="fa-solid fa-print"></i>
                Imprimir
            </button>
        </div>
    </form>

    <section id="report-print-area" class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800 shadow-xl">
        <div class="flex flex-col gap-3 border-b border-slate-700 p-5 sm:flex-row sm:items-center sm:justify-between md:px-6">
            <div>
                <h2 class="text-lg font-bold text-slate-100">{{ $report['titulo'] }}</h2>
                <p class="mt-1 text-xs text-slate-400">
                    @if($filters['tipo'] === 'inventario')
                        Inventario vigente · Generado {{ $generatedAt->format('d/m/Y H:i') }}
                    @else
                        Periodo {{ \Carbon\Carbon::parse($filters['fecha_desde'])->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($filters['fecha_hasta'])->format('d/m/Y') }}
                        · Generado {{ $generatedAt->format('d/m/Y H:i') }}
                    @endif
                </p>
            </div>
            <span class="w-fit rounded-full border border-slate-600 bg-slate-900 px-3 py-1.5 text-xs font-semibold text-slate-300">
                {{ number_format($report['filas']->total()) }} registros
            </span>
        </div>

        <div class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2 lg:grid-cols-3 md:px-6">
            @foreach($report['resumen'] as $item)
                <div class="rounded-xl border border-slate-700 bg-slate-900/70 p-4">
                    <p class="text-xs font-semibold text-slate-400">{{ $item['etiqueta'] }}</p>
                    <p class="mt-1 text-xl font-extrabold text-slate-100">{{ $item['valor'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="overflow-x-auto px-5 pb-2 md:px-6">
            <table class="w-full min-w-[760px] text-left text-xs">
                <thead class="border-y border-slate-700 bg-slate-900/70 text-[10px] uppercase tracking-wider text-slate-400">
                    <tr>
                        @foreach($report['columnas'] as $column)
                            <th class="whitespace-nowrap px-4 py-3 font-bold {{ $loop->last ? 'text-right' : '' }}">{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/80">
                    @forelse($report['filas'] as $row)
                        <tr class="transition hover:bg-slate-700/30">
                            @foreach($row as $cell)
                                <td class="max-w-sm px-4 py-3.5 text-slate-300 {{ $loop->last ? 'whitespace-nowrap text-right font-bold text-slate-100' : '' }}">
                                    {{ $cell }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($report['columnas']) }}" class="px-4 py-12 text-center">
                                <i class="fa-solid fa-folder-open text-3xl text-slate-600"></i>
                                <p class="mt-3 font-semibold text-slate-300">No hay registros para los filtros seleccionados.</p>
                                <p class="mt-1 text-xs text-slate-500">Prueba con otro periodo o selecciona un tipo de reporte diferente.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 p-5 md:px-6">
            <p class="text-xs text-slate-500">{{ $report['nota'] }}</p>
            <div class="report-pagination">
                {{ $report['filas']->links() }}
            </div>
        </div>
    </section>
</div>

<style>
    @media print {
        body * {
            visibility: hidden !important;
        }

        #report-print-area,
        #report-print-area * {
            visibility: visible !important;
        }

        #report-print-area {
            position: absolute;
            inset: 0;
            width: 100%;
            overflow: visible;
            border: 0;
            background: #fff;
            color: #111827;
            box-shadow: none;
        }

        #report-print-area thead {
            background: #f1f5f9;
        }

        #report-print-area th,
        #report-print-area td,
        #report-print-area h2,
        #report-print-area p,
        #report-print-area span {
            color: #111827 !important;
        }

        #report-print-area table {
            min-width: 0;
            font-size: 9px;
        }

        .report-pagination {
            display: none;
        }
    }
</style>
@endsection
