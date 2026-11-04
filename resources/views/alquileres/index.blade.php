@extends('layouts.app')

@section('title', $modulo === 'registro' ? 'Registrar Alquiler - Ferretería Jack Sparkys S.A.C.' : 'Historial de Alquileres - Ferretería Jack Sparkys S.A.C.')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-bold text-slate-100">
                <i class="fa-solid {{ $modulo === 'registro' ? 'fa-screwdriver-wrench' : 'fa-clock-rotate-left' }} text-amber-400"></i>
                {{ $modulo === 'registro' ? 'Registrar alquiler' : 'Historial de alquileres' }}
            </h1>
            <p class="mt-1 text-sm text-slate-400">{{ $modulo === 'registro' ? 'Registra la entrega del equipo, el plazo, la garantía y el pago inicial.' : 'Consulta pagos, saldos, garantías, fechas y devoluciones de los equipos.' }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            @if($modulo === 'registro')
                <a href="{{ route('alquileres.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-amber-300 hover:text-amber-200">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Historial de alquileres
                </a>
            @else
                <a href="{{ route('alquileres.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 px-3 py-2 text-sm font-bold text-slate-950 hover:bg-amber-400">
                    <i class="fa-solid fa-file-circle-plus"></i>
                    Registrar alquiler
                </a>
                <a href="{{ route('cobranzas.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-amber-300 hover:text-amber-200">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                    Ver cobranzas e historial de pagos
                </a>
            @endif
        </div>
    </div>

    @if($modulo === 'registro' && $cajaActual)
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-3 text-sm text-emerald-300">
            Caja de Alquileres abierta · Efectivo disponible: S/ {{ number_format($cajaActual->monto_final, 2) }}
        </div>
    @elseif($modulo === 'registro')
        <div class="flex flex-col gap-2 rounded-xl border border-amber-500/30 bg-amber-500/10 p-3 text-sm text-amber-200 sm:flex-row sm:items-center sm:justify-between">
            <span>Abre la caja de Alquileres/Cobranzas para registrar alquileres, garantías o devoluciones de garantía.</span>
            <a href="{{ route('caja.index') }}" class="font-bold underline underline-offset-2">Ir a Control de Caja</a>
        </div>
    @endif

    @if($modulo === 'historial')
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-700 bg-slate-800 p-4">
            <p class="text-xs font-medium text-slate-400">Equipos fuera de almacén</p>
            <p class="mt-1 text-2xl font-bold text-blue-300">{{ $resumen['activos'] }}</p>
        </div>
        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4">
            <p class="text-xs font-medium text-rose-200">Devoluciones vencidas</p>
            <p class="mt-1 text-2xl font-bold text-rose-300">{{ $resumen['vencidos'] }}</p>
        </div>
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 p-4">
            <p class="text-xs font-medium text-amber-200">Garantías en custodia</p>
            <p class="mt-1 text-2xl font-bold text-amber-300">S/ {{ number_format($resumen['garantias'], 2) }}</p>
        </div>
    </div>
    @endif

    @if($modulo === 'registro')
    <section id="registro-alquiler" class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800">
        <div class="border-b border-slate-700 p-5">
            <h2 class="font-bold text-slate-100">Registrar alquiler</h2>
            <p class="mt-1 text-xs text-slate-400">Un equipo por operación. Se requiere garantía; el alquiler puede pagarse en abonos.</p>
        </div>
        <form action="{{ route('alquileres.store') }}" method="POST" class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">
            @csrf
            <label class="space-y-1 text-xs text-slate-300">
                <span>Cliente</span>
                <select name="id_cliente" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100">
                    <option value="">Selecciona un cliente</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id_cliente }}" @selected(old('id_cliente') == $cliente->id_cliente) @disabled(in_array($cliente->id_cliente, $clientesBloqueados, true))>
                            {{ $cliente->nombres }} · {{ $cliente->dni_ruc }}{{ in_array($cliente->id_cliente, $clientesBloqueados, true) ? ' · Tiene pendiente' : '' }}
                        </option>
                    @endforeach
                </select>
                <span class="block text-[11px] text-slate-500">Clientes con equipo sin devolver o deuda no pueden iniciar otro alquiler.</span>
            </label>
            <label class="space-y-1 text-xs text-slate-300">
                <span>Equipo / herramienta</span>
                <select name="id_producto" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100">
                    <option value="">Selecciona un equipo</option>
                    @foreach($herramientas as $herramienta)
                        <option value="{{ $herramienta->id_producto }}" @selected(old('id_producto') == $herramienta->id_producto)>
                            {{ $herramienta->codigo }} · {{ $herramienta->nombre }} · {{ $herramienta->stock }} disp. · S/ {{ number_format($herramienta->precio_alquiler_dia, 2) }}/día
                        </option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-1 text-xs text-slate-300">
                <span>Fecha de entrega</span>
                <input type="date" name="fecha_entrega" value="{{ old('fecha_entrega', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100">
            </label>
            <label class="space-y-1 text-xs text-slate-300">
                <span>Días pactados</span>
                <input type="number" name="dias_pactados" value="{{ old('dias_pactados', 1) }}" min="1" max="3650" step="1" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100">
            </label>
            <label class="space-y-1 text-xs text-slate-300">
                <span>Pago inicial del alquiler (S/)</span>
                <input type="number" name="monto_pagado" value="{{ old('monto_pagado', '0.00') }}" min="0" step="0.01" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100">
            </label>
            <label class="space-y-1 text-xs text-slate-300">
                <span>Medio del pago inicial</span>
                <select name="medio_pago_alquiler" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100">
                    <option value="">Sin pago inicial</option>
                    @foreach(\App\Models\Caja::MEDIOS_PAGO as $medio)
                        <option value="{{ $medio }}" @selected(old('medio_pago_alquiler') === $medio)>{{ ucfirst($medio) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-1 text-xs text-slate-300">
                <span>Garantía del equipo (S/)</span>
                <input type="number" name="monto_garantia" value="{{ old('monto_garantia') }}" min="0.01" step="0.01" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100">
            </label>
            <label class="space-y-1 text-xs text-slate-300">
                <span>Medio de la garantía</span>
                <select name="medio_pago_garantia" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100">
                    @foreach(\App\Models\Caja::MEDIOS_PAGO as $medio)
                        <option value="{{ $medio }}" @selected(old('medio_pago_garantia', 'efectivo') === $medio)>{{ ucfirst($medio) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="space-y-1 text-xs text-slate-300 md:col-span-2">
                <span>Ubicación del equipo / obra</span>
                <input name="ubicacion_herramienta" value="{{ old('ubicacion_herramienta') }}" maxlength="200" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100" placeholder="Dirección o referencia">
            </label>
            <label class="space-y-1 text-xs text-slate-300 md:col-span-2">
                <span>Observaciones</span>
                <input name="observaciones" value="{{ old('observaciones') }}" maxlength="2000" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2.5 text-sm text-slate-100" placeholder="Condición del equipo al entregar, accesorios, etc.">
            </label>
            <div class="flex flex-col justify-end gap-2 md:col-span-2 xl:col-span-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-400">El saldo del alquiler se registra en Cobranzas. La garantía se contabiliza aparte y no se considera ingreso por alquiler.</p>
                <button type="submit" @disabled(!$cajaActual) class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-bold text-slate-950 hover:bg-amber-400 disabled:cursor-not-allowed disabled:opacity-50">
                    <i class="fa-solid fa-file-circle-plus"></i> Registrar y entregar equipo
                </button>
            </div>
        </form>
    </section>
    @endif

    @if($modulo === 'historial')
    <section id="historial-alquileres" class="overflow-hidden rounded-2xl border border-slate-700 bg-slate-800">
        <div class="flex flex-col gap-3 border-b border-slate-700 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-slate-100">Historial de alquileres</h2>
                <p class="mt-1 text-xs text-slate-400">Pagos, saldos, garantías, fechas y estado de devolución.</p>
            </div>
            <form action="{{ route('alquileres.index') }}" method="GET" class="grid grid-cols-1 gap-2 sm:grid-cols-[minmax(12rem,1fr)_auto_auto]">
                <input name="buscar" value="{{ $filters['buscar'] ?? '' }}" maxlength="100" placeholder="Buscar cliente, DNI o equipo" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-xs text-slate-100">
                <select name="estado" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-xs text-slate-100">
                    <option value="">Todos los estados</option>
                    <option value="vigente" @selected(($filters['estado'] ?? '') === 'vigente')>Vigentes</option>
                    <option value="vencido" @selected(($filters['estado'] ?? '') === 'vencido')>Vencidos</option>
                    <option value="devuelto" @selected(($filters['estado'] ?? '') === 'devuelto')>Devueltos</option>
                </select>
                <button class="rounded-lg bg-slate-700 px-3 py-2 text-xs font-bold text-slate-100 hover:bg-slate-600">Filtrar</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px] text-left text-xs">
                <thead class="bg-slate-900/80 uppercase text-slate-400">
                    <tr>
                        <th class="p-4">Alquiler / equipo</th>
                        <th class="p-4">Cliente</th>
                        <th class="p-4">Fechas / ubicación</th>
                        <th class="p-4 text-right">Alquiler</th>
                        <th class="p-4 text-right">Garantía</th>
                        <th class="p-4 text-center">Estado / acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($alquileres as $alq)
                        @php
                            $alquilerVencido = $alq->estado !== 'devuelto' && $alq->fecha_pactada_devolucion->lt(today());
                            $estadoVisual = $alq->estado === 'devuelto' ? 'devuelto' : ($alquilerVencido ? 'vencido' : 'vigente');
                            $saldoAlquiler = max(0, (float) $alq->total_alquiler - (float) $alq->monto_pagado);
                        @endphp
                        <tr class="align-top text-slate-300">
                            <td class="p-4">
                                <div class="font-bold text-amber-300">#ALQ-{{ $alq->id_alquiler }}</div>
                                <div class="mt-1 font-semibold text-slate-100">{{ $alq->producto?->nombre ?? 'Equipo no disponible' }}</div>
                                <div class="text-slate-500">{{ $alq->producto?->codigo ?? '' }}</div>
                            </td>
                            <td class="p-4">
                                <div class="font-semibold text-slate-100">{{ $alq->cliente?->nombres ?? 'Cliente no disponible' }}</div>
                                <div class="mt-1 text-slate-400">{{ $alq->cliente?->dni_ruc ?? '—' }} · {{ $alq->cliente?->telefono ?? 'Sin teléfono' }}</div>
                                <div class="mt-1 text-slate-500">Atendió: {{ $alq->usuario?->nombre ?? '—' }}</div>
                            </td>
                            <td class="p-4">
                                <div>Entrega: {{ $alq->fecha_entrega->format('d/m/Y') }}</div>
                                <div class="mt-1">Devolución pactada: {{ $alq->fecha_pactada_devolucion->format('d/m/Y') }}</div>
                                @if($alq->fecha_devolucion_real)
                                    <div class="mt-1 text-emerald-300">Devuelto: {{ $alq->fecha_devolucion_real->format('d/m/Y') }}</div>
                                @endif
                                <div class="mt-1 text-slate-500">{{ $alq->ubicacion_herramienta ?: 'Ubicación no registrada' }}</div>
                            </td>
                            <td class="p-4 text-right">
                                <div class="font-bold text-slate-100">S/ {{ number_format($alq->total_alquiler, 2) }}</div>
                                <div class="mt-1 text-emerald-300">Pagado S/ {{ number_format($alq->monto_pagado, 2) }}</div>
                                @if($saldoAlquiler > 0)
                                    <div class="mt-1 text-rose-300">Saldo S/ {{ number_format($saldoAlquiler, 2) }}</div>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                <div class="font-semibold text-amber-300">S/ {{ number_format($alq->monto_garantia, 2) }}</div>
                                @if($alq->estado === 'devuelto')
                                    <div class="mt-1 text-xs text-slate-400">Aplicado: S/ {{ number_format($alq->monto_garantia_aplicado, 2) }}</div>
                                    <div class="text-xs text-slate-400">Devuelto: S/ {{ number_format($alq->monto_garantia_devuelto, 2) }}</div>
                                @else
                                    <div class="mt-1 text-xs text-amber-200">En custodia</div>
                                @endif
                            </td>
                            <td class="space-y-2 p-4 text-center">
                                <span class="inline-flex rounded-full border px-2.5 py-1 font-bold uppercase {{ $estadoVisual === 'devuelto' ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300' : ($estadoVisual === 'vencido' ? 'border-rose-500/30 bg-rose-500/10 text-rose-300' : 'border-blue-500/30 bg-blue-500/10 text-blue-300') }}">
                                    {{ ucfirst($estadoVisual) }}
                                </span>
                                @if($alq->estado !== 'devuelto')
                                    <details class="text-left">
                                        <summary class="cursor-pointer rounded-lg border border-amber-500/40 px-3 py-2 text-center font-bold text-amber-200 hover:bg-amber-500/10">Registrar devolución</summary>
                                        <form action="{{ route('alquileres.devolver', $alq) }}" method="POST" class="mt-2 space-y-2 rounded-lg border border-slate-700 bg-slate-900 p-3">
                                            @csrf
                                            <label class="block space-y-1">
                                                <span class="text-slate-300">Cargo por daño (S/)</span>
                                                <input type="number" name="monto_dano" min="0" step="0.01" value="0.00" required class="w-full rounded border border-slate-600 bg-slate-800 px-2 py-1.5 text-slate-100">
                                            </label>
                                            <label class="block space-y-1">
                                                <span class="text-slate-300">Inspección / observaciones</span>
                                                <textarea name="observaciones_devolucion" rows="2" required maxlength="2000" class="w-full rounded border border-slate-600 bg-slate-800 px-2 py-1.5 text-slate-100" placeholder="Condición del equipo y accesorios">{{ old('observaciones_devolucion') }}</textarea>
                                            </label>
                                            <p class="text-slate-400">El cargo por daño se descuenta primero de la garantía; el excedente queda como deuda.</p>
                                            <button type="submit" class="w-full rounded bg-emerald-500 px-2 py-2 font-bold text-slate-950 hover:bg-emerald-400">Confirmar devolución</button>
                                        </form>
                                    </details>
                                @else
                                    <div class="text-left">
                                        @foreach($alq->cobranzas->where('tipo', 'mora') as $mora)
                                            <div class="text-rose-300">Mora: {{ $mora->dias_atraso }} día(s) · S/ {{ number_format($mora->monto_mora, 2) }}</div>
                                        @endforeach
                                        @if($alq->observaciones_devolucion)
                                            <details class="mt-1 text-slate-400">
                                                <summary class="cursor-pointer">Inspección</summary>
                                                <p class="mt-1">{{ $alq->observaciones_devolucion }}</p>
                                            </details>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center text-slate-400">No hay alquileres que coincidan con el filtro.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-700 p-4">{{ $alquileres->links() }}</div>
    </section>
    @endif
</div>
@endsection
