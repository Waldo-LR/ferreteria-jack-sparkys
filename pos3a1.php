<?php

$p3a1 = <<<'HTML'
<!-- MODAL: BUSCAR PRODUCTOS -->
<div id="modalBuscarProd" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-800 rounded-2xl max-w-3xl w-full border border-slate-700 shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between bg-slate-850">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-amber-500 text-lg"></i>
                <h3 class="font-bold text-slate-100 text-sm">Catálogo de Productos de Ferretería</h3>
            </div>
            <button type="button" onclick="cerrarModalBuscarProductos()" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="p-4 border-b border-slate-700 bg-slate-900/50">
            <div class="relative">
                <input type="text" id="inputFiltroProd" onkeyup="filtrarProductosModal()" placeholder="Buscar por código, nombre, categoría o marca..." class="w-full bg-slate-900 border border-slate-700 rounded-xl py-2.5 pl-9 pr-4 text-xs text-slate-200 focus:outline-none focus:border-amber-500">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
            </div>
        </div>

        <div class="p-4 overflow-y-auto flex-1 space-y-2" id="listaProdsModal">
            @foreach($productos as $p)
                <div class="item-prod-modal p-3 rounded-xl bg-slate-900/80 border border-slate-700/80 flex items-center justify-between text-xs hover:border-amber-500/50 transition">
                    <div class="space-y-0.5">
                        <div class="font-bold text-slate-200 flex items-center gap-2">
                            <span>{{ $p->nombre }}</span>
                            <span class="text-[10px] px-2 py-0.2 rounded bg-slate-800 text-amber-400 font-bold border border-slate-700">{{ $p->codigo }}</span>
                        </div>
                        <div class="text-[11px] text-slate-400">
                            Cat: <strong class="text-slate-300">{{ $p->categoria->nombre ?? '-' }}</strong> | Marca: <strong class="text-slate-300">{{ $p->marca->nombre ?? '-' }}</strong>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <span class="block font-extrabold text-emerald-400 text-sm">S/ {{ number_format($p->precio_venta, 2) }}</span>
                            <span class="text-[10px] text-slate-400 block">Mayor: S/ {{ number_format($p->precio_venta_mayor ?? $p->precio_venta, 2) }}</span>
                        </div>

                        @if($p->stock > 0)
                            <div class="text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 block mb-1">
                                    Stock: {{ $p->stock }}
                                </span>
                                <button type="button" onclick="agregarProductoPOS({{ json_encode($p) }})" class="px-3 py-1 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-lg text-xs transition shadow">
                                    + Agregar
                                </button>
                            </div>
                        @else
                            <div class="flex flex-col items-end gap-1">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                    AGOTADO (0)
                                </span>
                                <div class="flex gap-1">
                                    <button type="button" onclick="alertarAlmacen({{ $p->id_producto }})" class="px-2 py-0.5 bg-rose-500/20 hover:bg-rose-500 hover:text-white text-rose-300 text-[10px] font-bold rounded border border-rose-500/30 transition">
                                        🔔 Alertar Almacén
                                    </button>
                                    <button type="button" onclick="notificarCliente({{ $p->id_producto }})" class="px-2 py-0.5 bg-blue-500/20 hover:bg-blue-500 hover:text-white text-blue-300 text-[10px] font-bold rounded border border-blue-500/30 transition">
                                        📧 Notificar Cliente
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
HTML;

file_put_contents(__DIR__ . '/resources/views/ventas/pos.blade.php', $p3a1, FILE_APPEND);
echo "pos p3a1 done\n";
