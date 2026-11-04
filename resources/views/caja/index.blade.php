@extends('layouts.app')

@section('title', 'Control de Caja - Ferretería Jack Sparkys S.A.C.')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-xl font-bold text-slate-100 flex items-center gap-2">
            <i class="fa-solid fa-cash-register text-amber-400"></i>
            Control de Caja
        </h1>
        <p class="text-sm text-slate-400 mt-1">Apertura, ingresos, egresos y arqueo independiente para cada operación.</p>
    </div>

    <div class="rounded-2xl border border-slate-700 bg-slate-800 p-2">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" role="tablist" aria-label="Tipo de caja">
            @foreach($tiposCaja as $tipoCaja => $nombreCaja)
                @php
                    $pestanaActiva = old('tipo_caja', $loop->first ? $tipoCaja : null) === $tipoCaja;
                @endphp
                <button
                    id="tab-{{ $tipoCaja }}"
                    type="button"
                    role="tab"
                    aria-selected="{{ $pestanaActiva ? 'true' : 'false' }}"
                    aria-controls="panel-{{ $tipoCaja }}"
                    tabindex="{{ $pestanaActiva ? '0' : '-1' }}"
                    data-caja-tab="{{ $tipoCaja }}"
                    class="rounded-xl px-4 py-3 text-left text-sm font-bold transition {{ $pestanaActiva ? 'bg-amber-500 text-slate-950 shadow-lg shadow-amber-500/10' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}"
                >
                    <span class="flex items-center gap-2">
                        <i class="fa-solid {{ $tipoCaja === 'ventas' ? 'fa-bag-shopping' : 'fa-screwdriver-wrench' }}"></i>
                        {{ $nombreCaja }}
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    @foreach($tiposCaja as $tipoCaja => $nombreCaja)
        @php
            $caja = $cajasAbiertas->get($tipoCaja);
            $pestanaActiva = old('tipo_caja', $loop->first ? $tipoCaja : null) === $tipoCaja;
        @endphp
        <section
            id="panel-{{ $tipoCaja }}"
            role="tabpanel"
            aria-labelledby="tab-{{ $tipoCaja }}"
            data-caja-panel="{{ $tipoCaja }}"
            @unless($pestanaActiva) hidden @endunless
            class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden"
        >
            <div class="p-5 md:p-6 border-b border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-slate-100">{{ $nombreCaja }}</h2>
                </div>
                @if($caja)
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold w-fit">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        ABIERTA
                    </div>
                @else
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-700 text-slate-300 text-xs font-bold w-fit">
                        <i class="fa-solid fa-lock"></i> CERRADA
                    </span>
                @endif
            </div>

            @if($caja)
                @php
                    $ingresosDigitales = (float) $caja->total_ingresos - (float) $caja->ingresos_efectivo;
                    $egresosDigitales = (float) $caja->total_egresos - (float) $caja->egresos_efectivo;
                @endphp

                <div class="p-5 md:p-6 space-y-6">
                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
                        <div class="bg-slate-900/70 p-4 rounded-xl border border-slate-700">
                            <span class="text-xs text-slate-400 block">Saldo inicial</span>
                            <span class="text-lg font-bold text-slate-100">S/ {{ number_format($caja->monto_inicial, 2) }}</span>
                        </div>
                        <div class="bg-slate-900/70 p-4 rounded-xl border border-slate-700">
                            <span class="text-xs text-slate-400 block">Ingresos totales</span>
                            <span class="text-lg font-bold text-emerald-400">S/ {{ number_format($caja->total_ingresos, 2) }}</span>
                        </div>
                        <div class="bg-slate-900/70 p-4 rounded-xl border border-slate-700">
                            <span class="text-xs text-slate-400 block">Egresos totales</span>
                            <span class="text-lg font-bold text-rose-400">S/ {{ number_format($caja->total_egresos, 2) }}</span>
                        </div>
                        <div class="bg-slate-900/70 p-4 rounded-xl border border-slate-700">
                            <span class="text-xs text-slate-400 block">Ingresos en efectivo</span>
                            <span class="text-lg font-bold text-emerald-300">S/ {{ number_format($caja->ingresos_efectivo, 2) }}</span>
                        </div>
                        <div class="bg-slate-900/70 p-4 rounded-xl border border-slate-700">
                            <span class="text-xs text-slate-400 block">Neto no efectivo</span>
                            <span class="text-lg font-bold text-blue-300">S/ {{ number_format($ingresosDigitales - $egresosDigitales, 2) }}</span>
                        </div>
                        <div class="bg-amber-500/10 p-4 rounded-xl border border-amber-500/30">
                            <span class="text-xs text-amber-200 block">Efectivo esperado</span>
                            <span class="text-lg font-extrabold text-amber-300">S/ {{ number_format($caja->monto_final, 2) }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                        <form action="{{ route('caja.movimientos.store', $caja) }}" method="POST" class="bg-slate-900/60 p-4 md:p-5 rounded-xl border border-slate-700 space-y-4">
                            @csrf
                            <input type="hidden" name="tipo_caja" value="{{ $tipoCaja }}">
                            <div>
                                <h3 class="font-bold text-slate-100">Registrar movimiento</h3>
                                <p class="text-xs text-slate-400 mt-1">Los ingresos y egresos quedan vinculados a esta caja y al usuario.</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="text-xs text-slate-300 space-y-1">
                                    <span>Tipo</span>
                                    <select name="tipo_movimiento" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-100">
                                        <option value="ingreso" {{ old('tipo_movimiento') === 'ingreso' ? 'selected' : '' }}>Ingreso</option>
                                        <option value="egreso" {{ old('tipo_movimiento') === 'egreso' ? 'selected' : '' }}>Egreso</option>
                                    </select>
                                </label>
                                <label class="text-xs text-slate-300 space-y-1">
                                    <span>Categoría</span>
                                    <select name="categoria" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-100">
                                        <option value="">Selecciona una categoría</option>
                                        @foreach($categoriasMovimiento as $categoria)
                                            <option value="{{ $categoria }}" {{ old('categoria') === $categoria ? 'selected' : '' }}>{{ $categoria }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="text-xs text-slate-300 space-y-1">
                                    <span>Monto (S/)</span>
                                    <input type="number" name="monto" min="0.01" step="0.01" value="{{ old('monto') }}" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-100">
                                </label>
                                <label class="text-xs text-slate-300 space-y-1">
                                    <span>Medio de pago</span>
                                    <select name="medio_pago" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-100">
                                        <option value="efectivo" {{ old('medio_pago', 'efectivo') === 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                        <option value="yape" {{ old('medio_pago') === 'yape' ? 'selected' : '' }}>Yape / pago digital</option>
                                        <option value="deposito" {{ old('medio_pago') === 'deposito' ? 'selected' : '' }}>Depósito / transferencia</option>
                                    </select>
                                </label>
                            </div>
                            <label class="text-xs text-slate-300 space-y-1 block">
                                <span>Detalle / sustento</span>
                                <textarea name="detalle" rows="2" minlength="3" maxlength="2000" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-100" placeholder="Describe el motivo del movimiento">{{ old('detalle') }}</textarea>
                            </label>
                            @error('monto')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                            @error('caja')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                            <button type="submit" class="w-full sm:w-auto px-4 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold rounded-lg text-sm transition">
                                <i class="fa-solid fa-plus mr-1"></i> Guardar movimiento
                            </button>
                        </form>

                        <form action="{{ route('caja.cerrar', $caja) }}" method="POST" class="bg-slate-900/60 p-4 md:p-5 rounded-xl border border-rose-500/20 space-y-4">
                            @csrf
                            <input type="hidden" name="tipo_caja" value="{{ $tipoCaja }}">
                            <div>
                                <h3 class="font-bold text-slate-100">Cerrar caja y realizar arqueo</h3>
                                <p class="text-xs text-slate-400 mt-1">Cuenta solo el efectivo físico. Los pagos digitales se concilian por separado.</p>
                            </div>
                            <div class="bg-slate-800 p-3 rounded-lg flex items-center justify-between gap-3">
                                <span class="text-sm text-slate-300">Efectivo esperado</span>
                                <span class="font-extrabold text-amber-300">S/ {{ number_format($caja->monto_final, 2) }}</span>
                            </div>
                            <label class="text-xs text-slate-300 space-y-1 block">
                                <span>Efectivo contado (S/)</span>
                                <input type="number" name="monto_contado" min="0" step="0.01" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-100" placeholder="0.00">
                            </label>
                            <label class="text-xs text-slate-300 space-y-1 block">
                                <span>Observaciones / motivo de diferencia</span>
                                <textarea name="observaciones" rows="2" maxlength="1000" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-100" placeholder="Obligatorio si hay sobrante o faltante"></textarea>
                            </label>
                            @error('monto_contado')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                            @error('observaciones')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                            <button type="submit" class="w-full sm:w-auto px-4 py-2.5 bg-rose-500 hover:bg-rose-400 text-white font-bold rounded-lg text-sm transition" onclick="return confirm('¿Confirmas el cierre y el arqueo de esta caja?');">
                                <i class="fa-solid fa-lock mr-1"></i> Cerrar y guardar arqueo
                            </button>
                        </form>
                    </div>

                    <div>
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <h3 class="font-bold text-slate-100">Actividad reciente</h3>
                            <span class="text-xs text-slate-400">Hasta 20 movimientos</span>
                        </div>
                        <div class="overflow-x-auto rounded-xl border border-slate-700">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-900 text-xs uppercase text-slate-400">
                                    <tr>
                                        <th class="p-3">Fecha</th>
                                        <th class="p-3">Concepto</th>
                                        <th class="p-3">Medio</th>
                                        <th class="p-3">Usuario</th>
                                        <th class="p-3 text-right">Monto</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-700">
                                    @forelse($actividadPorCaja[$tipoCaja] ?? [] as $movimiento)
                                        <tr>
                                            <td class="p-3 whitespace-nowrap text-slate-400">{{ $movimiento['fecha']?->format('d/m/Y H:i') }}</td>
                                            <td class="p-3 text-slate-200">
                                                <span class="font-semibold {{ $movimiento['tipo'] === 'ingreso' ? 'text-emerald-400' : 'text-rose-400' }}">
                                                    {{ ucfirst($movimiento['tipo']) }}
                                                </span>
                                                <span class="block text-xs text-slate-400">{{ $movimiento['concepto'] }}</span>
                                            </td>
                                            <td class="p-3 text-slate-300">{{ ucfirst($movimiento['medio_pago']) }}</td>
                                            <td class="p-3 text-slate-400">{{ $movimiento['usuario'] }}</td>
                                            <td class="p-3 text-right font-bold {{ $movimiento['tipo'] === 'ingreso' ? 'text-emerald-400' : 'text-rose-400' }}">
                                                {{ $movimiento['tipo'] === 'ingreso' ? '+' : '-' }} S/ {{ number_format($movimiento['monto'], 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="p-5 text-center text-slate-400">Aún no hay movimientos en este turno.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="p-5 md:p-6">
                    <form action="{{ route('caja.aperturar') }}" method="POST" class="max-w-2xl bg-slate-900/60 p-4 md:p-5 rounded-xl border border-slate-700 space-y-4">
                        @csrf
                        <input type="hidden" name="tipo_caja" value="{{ $tipoCaja }}">
                        <div>
                            <h3 class="font-bold text-slate-100">Abrir caja de {{ $nombreCaja }}</h3>
                            <p class="text-xs text-slate-400 mt-1">Define el fondo inicial de efectivo para este turno.</p>
                        </div>
                        <label class="text-xs text-slate-300 space-y-1 block">
                            <span>Saldo inicial en efectivo (S/)</span>
                            <input type="number" name="monto_inicial" min="0" step="0.01" value="{{ old('monto_inicial', '0.00') }}" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-100">
                        </label>
                        <label class="text-xs text-slate-300 space-y-1 block">
                            <span>Observaciones de apertura</span>
                            <textarea name="observaciones" rows="2" maxlength="1000" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-3 py-2 text-sm text-slate-100">{{ old('observaciones') }}</textarea>
                        </label>
                        @error('tipo_caja')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                        @error('monto_inicial')<p class="text-xs text-rose-400">{{ $message }}</p>@enderror
                        <button type="submit" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-lg text-sm transition">
                            <i class="fa-solid fa-lock-open mr-1"></i> Abrir caja
                        </button>
                    </form>
                </div>
            @endif
        </section>
    @endforeach

    <script>
        (() => {
            const tabs = Array.from(document.querySelectorAll('[data-caja-tab]'));
            const panels = Array.from(document.querySelectorAll('[data-caja-panel]'));

            const activateTab = (type) => {
                tabs.forEach((tab) => {
                    const isActive = tab.dataset.cajaTab === type;
                    tab.setAttribute('aria-selected', String(isActive));
                    tab.setAttribute('tabindex', isActive ? '0' : '-1');
                    tab.classList.toggle('bg-amber-500', isActive);
                    tab.classList.toggle('text-slate-950', isActive);
                    tab.classList.toggle('shadow-lg', isActive);
                    tab.classList.toggle('shadow-amber-500/10', isActive);
                    tab.classList.toggle('text-slate-300', !isActive);
                    tab.classList.toggle('hover:bg-slate-700', !isActive);
                    tab.classList.toggle('hover:text-white', !isActive);
                });

                panels.forEach((panel) => {
                    panel.hidden = panel.dataset.cajaPanel !== type;
                });
            };

            tabs.forEach((tab, index) => {
                tab.addEventListener('click', () => activateTab(tab.dataset.cajaTab));
                tab.addEventListener('keydown', (event) => {
                    if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) {
                        return;
                    }

                    event.preventDefault();
                    const nextIndex = event.key === 'Home'
                        ? 0
                        : event.key === 'End'
                            ? tabs.length - 1
                            : (index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
                    tabs[nextIndex].focus();
                    activateTab(tabs[nextIndex].dataset.cajaTab);
                });
            });
        })();
    </script>

    <section class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden">
        <div class="p-5 border-b border-slate-700">
            <h2 class="font-bold text-slate-100">Cierres recientes</h2>
            <p class="text-xs text-slate-400 mt-1">Historial de arqueos por tipo de caja.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-900 text-xs uppercase text-slate-400">
                    <tr>
                        <th class="p-3">Caja</th>
                        <th class="p-3">Apertura</th>
                        <th class="p-3">Cierre</th>
                        <th class="p-3">Responsables</th>
                        <th class="p-3 text-right">Esperado</th>
                        <th class="p-3 text-right">Contado</th>
                        <th class="p-3 text-right">Diferencia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($cierres as $cierre)
                        <tr>
                            <td class="p-3 text-slate-200">{{ $tiposCaja[$cierre->tipo_caja] ?? $cierre->tipo_caja }}</td>
                            <td class="p-3 text-slate-400 whitespace-nowrap">{{ $cierre->fecha_apertura?->format('d/m/Y H:i') }}</td>
                            <td class="p-3 text-slate-400 whitespace-nowrap">{{ $cierre->fecha_cierre?->format('d/m/Y H:i') }}</td>
                            <td class="p-3 text-slate-400">{{ $cierre->usuario?->nombre }} / {{ $cierre->usuarioCierre?->nombre }}</td>
                            <td class="p-3 text-right text-slate-300">S/ {{ number_format($cierre->monto_final, 2) }}</td>
                            <td class="p-3 text-right text-slate-300">S/ {{ number_format($cierre->monto_contado, 2) }}</td>
                            <td class="p-3 text-right font-bold {{ (float) $cierre->diferencia_cierre < 0 ? 'text-rose-400' : ((float) $cierre->diferencia_cierre > 0 ? 'text-emerald-400' : 'text-slate-300') }}">
                                S/ {{ number_format($cierre->diferencia_cierre, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-5 text-center text-slate-400">Todavía no hay cierres registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
