@extends('layouts.app')

@section('title', 'Panel Principal - Ferretería Jack Sparkys S.A.C.')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <section class="min-w-0 rounded-xl border border-slate-700 bg-slate-800 p-5 shadow-sm md:p-6">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-100">Resumen de ventas</h2>
                    <p class="mt-1 text-sm text-slate-400">Ventas emitidas por día</p>
                </div>
                <div class="sm:text-right">
                    <p class="text-xs text-slate-400">Últimos 7 días · S/</p>
                    <p class="text-lg font-bold text-emerald-400">{{ number_format(array_sum($ventasChartData), 2) }}</p>
                </div>
            </div>
            <div class="relative h-64 sm:h-72">
                <canvas id="salesChart" role="img" aria-label="Resumen diario de ventas emitidas durante los últimos siete días"></canvas>
            </div>
        </section>

        <section class="min-w-0 rounded-xl border border-slate-700 bg-slate-800 p-5 shadow-sm md:p-6">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-100">Resumen de alquileres</h2>
                    <p class="mt-1 text-sm text-slate-400">Monto de contratos registrados por día · sin garantías</p>
                </div>
                <div class="sm:text-right">
                    <p class="text-xs text-slate-400">Últimos 7 días · S/</p>
                    <p class="text-lg font-bold text-sky-400">{{ number_format(array_sum($alquileresChartData), 2) }}</p>
                </div>
            </div>
            <div class="relative h-64 sm:h-72">
                <canvas id="rentalsChart" role="img" aria-label="Resumen diario del monto de contratos de alquiler registrados durante los últimos siete días, sin incluir garantías"></canvas>
            </div>
        </section>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <a href="{{ route('ventas.index') }}" class="bg-slate-800 p-5 rounded-xl border border-slate-700 shadow-sm transition hover:border-emerald-500/50 hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-400 font-medium">Ventas de hoy</span>
                <span aria-hidden="true" class="w-10 h-10 flex items-center justify-center bg-emerald-500/10 text-emerald-400 rounded-xl">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                </span>
            </div>
            <div class="text-3xl font-extrabold text-slate-100 mt-2">S/ {{ number_format($ventasHoy, 2) }}</div>
            <div class="text-xs text-slate-400 mt-1">Ingresos por ventas emitidas</div>
        </a>

        <a href="{{ route('ventas.index') }}" class="bg-slate-800 p-5 rounded-xl border border-slate-700 shadow-sm transition hover:border-blue-500/50 hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-400 font-medium">Operaciones hoy</span>
                <span aria-hidden="true" class="w-10 h-10 flex items-center justify-center bg-blue-500/10 text-blue-400 rounded-xl">
                    <i class="fa-solid fa-receipt"></i>
                </span>
            </div>
            <div class="text-3xl font-extrabold text-slate-100 mt-2">{{ $operacionesHoy }}</div>
            <div class="text-xs text-slate-400 mt-1">Ventas completadas</div>
        </a>

        <a href="{{ route('productos.index') }}" class="bg-slate-800 p-5 rounded-xl border border-slate-700 shadow-sm transition hover:border-amber-500/50 hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-400 font-medium">Productos activos</span>
                <span aria-hidden="true" class="w-10 h-10 flex items-center justify-center bg-amber-500/10 text-amber-400 rounded-xl">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </span>
            </div>
            <div class="text-3xl font-extrabold text-slate-100 mt-2">{{ $totalProductos }}</div>
            <div class="text-xs text-slate-400 mt-1">Productos disponibles en catálogo</div>
        </a>

        <a href="{{ route('productos.index') }}" class="bg-slate-800 p-5 rounded-xl border border-slate-700 shadow-sm transition hover:border-rose-500/50 hover:-translate-y-0.5">
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-400 font-medium">Por reponer</span>
                <span aria-hidden="true" class="w-10 h-10 flex items-center justify-center bg-rose-500/10 text-rose-400 rounded-xl">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>
            </div>
            <div class="text-3xl font-extrabold {{ $productosPorReponer > 0 ? 'text-rose-400' : 'text-slate-100' }} mt-2">{{ $productosPorReponer }}</div>
            <div class="text-xs text-slate-400 mt-1">Igual o bajo el stock mínimo</div>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 {{ $user->esAdmin() || $user->esAlmacenero() ? 'lg:grid-cols-3' : '' }} gap-6">
        @if($user->esAdmin() || $user->esVendedor() || $user->esCobrador())
        <a href="{{ route('alquileres.index', ['estado' => 'vigente']) }}" class="bg-slate-800 p-5 rounded-xl border border-slate-700 shadow-sm flex items-center justify-between gap-4 transition hover:border-blue-500/50 hover:-translate-y-0.5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-400">
            <div>
                <p class="text-sm text-slate-400 font-medium">Alquileres vigentes</p>
                <p class="text-2xl font-extrabold text-blue-400 mt-1">{{ $alquileresVigentes }}</p>
                <p class="text-xs text-slate-400 mt-1">Herramientas actualmente en campo · Ver historial</p>
            </div>
            <span aria-hidden="true" class="w-11 h-11 flex items-center justify-center bg-blue-500/10 text-blue-400 rounded-xl">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </span>
        </a>
        @else
        <div class="bg-slate-800 p-5 rounded-xl border border-slate-700 shadow-sm flex items-center justify-between gap-4">
            <div>
                <p class="text-sm text-slate-400 font-medium">Alquileres vigentes</p>
                <p class="text-2xl font-extrabold text-blue-400 mt-1">{{ $alquileresVigentes }}</p>
                <p class="text-xs text-slate-400 mt-1">Herramientas actualmente en campo</p>
            </div>
            <span aria-hidden="true" class="w-11 h-11 flex items-center justify-center bg-blue-500/10 text-blue-400 rounded-xl">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </span>
        </div>
        @endif

        <a href="{{ route('caja.index') }}" class="bg-slate-800 p-5 rounded-xl border border-slate-700 shadow-sm flex items-center justify-between gap-4 transition hover:border-amber-500/50">
            <div>
                <p class="text-sm text-slate-400 font-medium">Estado de cajas</p>
                @foreach([
                    'ventas' => 'Ventas',
                    'alquileres' => 'Alquileres',
                ] as $tipoCaja => $nombreCaja)
                    @php($cajaAbierta = $cajasAbiertas->get($tipoCaja))
                    <p class="text-sm font-bold {{ $cajaAbierta ? 'text-emerald-400' : 'text-rose-400' }} mt-2">
                        {{ $nombreCaja }} · {{ $cajaAbierta ? 'Abierta · S/ '.number_format($cajaAbierta->monto_final, 2) : 'Cerrada' }}
                    </p>
                @endforeach
            </div>
            <span aria-hidden="true" class="w-11 h-11 flex items-center justify-center bg-amber-500/10 text-amber-400 rounded-xl">
                <i class="fa-solid fa-cash-register"></i>
            </span>
        </a>

        @if($user->esAdmin() || $user->esVendedor() || $user->esCobrador())
            <a href="{{ route('cobranzas.index') }}" class="bg-slate-800 p-5 rounded-xl border border-slate-700 shadow-sm flex items-center justify-between gap-4 transition hover:border-rose-500/50">
                <div>
                    <p class="text-sm text-slate-400 font-medium">Saldos de alquiler y moras</p>
                    <p class="text-2xl font-extrabold {{ $totalMorasPendientes > 0 ? 'text-rose-400' : 'text-emerald-400' }} mt-1">
                        S/ {{ number_format($totalMorasPendientes, 2) }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">Pendiente de cobro en Alquileres/Cobranzas</p>
                </div>
                <span aria-hidden="true" class="w-11 h-11 flex items-center justify-center bg-rose-500/10 text-rose-400 rounded-xl">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </span>
            </a>
        @endif

        @if($user->esAdmin() || $user->esAlmacenero())
            <a href="{{ route('compras.index') }}" class="bg-slate-800 p-5 rounded-xl border border-slate-700 shadow-sm flex items-center justify-between gap-4 transition hover:border-rose-500/50">
                <div>
                    <p class="text-sm text-slate-400 font-medium">Saldo pendiente a proveedores</p>
                    <p class="text-2xl font-extrabold {{ $cuentasPorPagar > 0 ? 'text-rose-400' : 'text-emerald-400' }} mt-1">
                        S/ {{ number_format($cuentasPorPagar, 2) }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">Compras recibidas menos pagos registrados</p>
                </div>
                <span aria-hidden="true" class="w-11 h-11 flex items-center justify-center bg-rose-500/10 text-rose-400 rounded-xl">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </span>
            </a>
        @endif
    </div>

    @if($alquileresVencidos > 0)
        <section class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-bold text-rose-300">Devoluciones de alquiler vencidas</h2>
                    <p class="mt-1 text-xs text-slate-300">{{ $alquileresVencidos }} equipo(s) siguen fuera después de su fecha pactada.</p>
                </div>
                <a href="{{ route('alquileres.index', ['estado' => 'vencido']) }}" class="rounded-lg border border-rose-400/40 px-3 py-2 text-center text-xs font-bold text-rose-200 hover:bg-rose-500/10">
                    Revisar alquileres vencidos
                </a>
            </div>
        </section>
    @endif

    @if(count($stockBajo) > 0)
    <a href="{{ route('inventario.index') }}" aria-label="Abrir control de inventario y revisar productos con stock mínimo" class="block rounded-xl border border-rose-500/30 bg-rose-900/20 p-5 transition hover:border-rose-400/60 hover:bg-rose-900/30 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-400">
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
        <p class="mt-4 text-xs font-semibold text-rose-200">Abrir control de inventario <i class="fa-solid fa-arrow-right ml-1"></i></p>
    </a>
    @endif
</div>
@endsection

@section('scripts')
<script>
    window.dashboardChartData = {
        labels: {{ Illuminate\Support\Js::from($ventasChartLabels) }},
        sales: {{ Illuminate\Support\Js::from($ventasChartData) }},
        rentals: {{ Illuminate\Support\Js::from($alquileresChartData) }}
    };
</script>
@endsection
