<?php

$p_A = <<<'HTML'
<!-- MODAL: BUSCAR CLIENTES -->
<div id="modalBuscarCli" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-slate-800 rounded-2xl max-w-lg w-full border border-slate-700 shadow-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between bg-slate-850">
            <h3 class="font-bold text-slate-100 text-sm">Seleccionar Cliente para la Venta</h3>
            <button type="button" onclick="cerrarModalBuscarCliente()" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="p-4 max-h-[60vh] overflow-y-auto space-y-2">
            @foreach($clientes as $cli)
                <div onclick="seleccionarClientePOS({{ json_encode($cli) }})" class="p-3 rounded-xl bg-slate-900 border border-slate-700/80 hover:border-amber-500 cursor-pointer transition flex justify-between items-center text-xs">
                    <div>
                        <div class="font-bold text-slate-200">{{ $cli->nombres }}</div>
                        <div class="text-[11px] text-slate-400">{{ $cli->tipo_documento }}: {{ $cli->dni_ruc }}</div>
                    </div>
                    <span class="text-amber-400 font-bold text-xs"><i class="fa-solid fa-check"></i> Elegir</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

@endsection
HTML;

file_put_contents(__DIR__ . '/resources/views/ventas/pos.blade.php', $p_A, FILE_APPEND);
echo "pos_A done\n";
