<?php

$c1b1 = <<<'HTML'

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
                            </td>
HTML;

file_put_contents(__DIR__ . '/resources/views/cobranzas/index.blade.php', $c1b1, FILE_APPEND);
echo "cv_b1 done\n";
