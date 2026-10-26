<?php

$p2a = <<<'HTML'
            <!-- PANEL 2 & 3: CLIENTE & PAGO Y COMPROBANTE -->
            <div class="space-y-6">

                <!-- PANEL 2: DATOS DEL CLIENTE -->
                <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5 shadow-xl space-y-3">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-700">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 font-black text-xs flex items-center justify-center">2</span>
                            <h2 class="font-bold text-slate-200 text-sm">Datos del Cliente</h2>
                        </div>
                        <button type="button" onclick="abrirModalBuscarCliente()" class="px-2.5 py-1 bg-slate-700 hover:bg-slate-600 text-slate-200 font-semibold rounded-lg text-[11px] transition flex items-center gap-1">
                            <i class="fa-solid fa-user-check"></i>
                            <span>Buscar / Seleccionar</span>
                        </button>
                    </div>

                    <input type="hidden" name="id_cliente" id="input_id_cliente" value="{{ $clientes->first()->id_cliente ?? 1 }}">

                    <div id="cardCliente" class="p-3 rounded-xl bg-slate-900/80 border border-slate-700 space-y-1 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-amber-400" id="cli_nombre">{{ $clientes->first()->nombres ?? 'Cliente General' }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300 border border-slate-700" id="cli_doc">DNI: {{ $clientes->first()->dni_ruc ?? '00000000' }}</span>
                        </div>
                        <div class="text-slate-400 text-[11px]" id="cli_dir"><i class="fa-solid fa-location-dot mr-1"></i> {{ $clientes->first()->direccion ?? 'Atención en mostrador' }}</div>
                        <div class="text-slate-400 text-[11px]" id="cli_tel"><i class="fa-solid fa-phone mr-1"></i> {{ $clientes->first()->telefono ?? '-' }}</div>
                    </div>
                </div>
HTML;

file_put_contents(__DIR__ . '/resources/views/ventas/pos.blade.php', $p2a, FILE_APPEND);
echo "pos p2a done\n";
