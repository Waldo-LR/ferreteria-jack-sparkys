@extends('layouts.app')

@section('title', 'Registrar Venta - POS - Ferretería Jack Sparkys')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-cart-shopping text-amber-500"></i> Módulo Registrar Venta (POS)
            </h1>
            <p class="text-xs text-slate-400">Emisión de Boletas, Facturas y Notas de Venta / Proformas en tiempo real (IGV 18%)</p>
        </div>
        <div class="bg-slate-800 border border-slate-700 px-4 py-2 rounded-xl text-right">
            <span class="text-[10px] text-slate-400 uppercase font-bold block">N° Operación Interna</span>
            <span class="text-sm font-extrabold text-amber-400">{{ $nroVenta }}</span>
        </div>
    </div>

    <!-- Formulario POS de 3 Paneles -->
    <form id="formPOS" action="{{ route('ventas.store') }}" method="POST" class="space-y-6">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- PANEL 1: DETALLE DE PRODUCTOS (2 Columnas) -->
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5 shadow-xl space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-700">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 font-black text-xs flex items-center justify-center">1</span>
                            <h2 class="font-bold text-slate-200 text-sm">Detalle de Productos a Vender</h2>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5 text-xs text-slate-300">
                                <i class="fa-solid fa-calendar text-amber-400"></i>
                                <span>Emisión:</span>
                                <input type="date" name="fecha_emision" value="{{ date('Y-m-d') }}" class="bg-slate-900 border border-slate-700 text-slate-200 text-xs rounded-lg px-2 py-1 focus:outline-none focus:border-amber-500">
                            </div>
                            <button type="button" onclick="abrirModalBuscarProductos()" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-lg text-xs transition flex items-center gap-1.5 shadow">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <span>Buscar Productos</span>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs" id="tablaPOS">
                            <thead class="bg-slate-900/80 text-slate-400 uppercase text-[10px]">
                                <tr>
                                    <th class="p-3">N°</th>
                                    <th class="p-3">Producto</th>
                                    <th class="p-3">Detalle / Marca</th>
                                    <th class="p-3 text-center w-24">Cantidad</th>
                                    <th class="p-3 text-right">Precio Unit.</th>
                                    <th class="p-3 text-right">Sub Total</th>
                                    <th class="p-3 text-center w-16">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/60" id="bodyPOS">
                                <tr id="rowVacia">
                                    <td colspan="7" class="p-8 text-center text-slate-500 italic">
                                        <i class="fa-solid fa-basket-shopping text-3xl mb-2 block text-slate-600"></i>
                                        Aún no has agregado productos a la venta. Haz clic en <strong>"Buscar Productos"</strong> para seleccionar artículos del catálogo.
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-slate-900/90 text-slate-200 border-t-2 border-slate-700 font-bold">
                                <tr>
                                    <td colspan="5" class="p-3 text-right text-xs uppercase tracking-wider">Importe Total General:</td>
                                    <td class="p-3 text-right text-base text-emerald-400 font-extrabold" id="totalGeneralDisplay">S/ 0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>            <!-- PANEL 2 & 3: CLIENTE & PAGO Y COMPROBANTE -->
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
                </div>                <!-- PANEL 3: REGISTRAR PAGO Y GENERAR COMPROBANTE -->
                <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5 shadow-xl space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-slate-700">
                        <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 font-black text-xs flex items-center justify-center">3</span>
                        <h2 class="font-bold text-slate-200 text-sm">Pago & Comprobante SUNAT</h2>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block font-bold text-slate-300 mb-1">Comprobante</label>
                            <select name="tipo_comprobante" id="tipo_comprobante" onchange="cambiarTipoComprobante()" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-slate-200 font-semibold focus:outline-none focus:border-amber-500">
                                <option value="boleta">Boleta de Venta</option>
                                <option value="factura">Factura (IGV 18%)</option>
                                <option value="nota_venta">Nota de Venta / Proforma</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-300 mb-1">Serie Asignada</label>
                            <input type="text" readonly id="display_serie" value="{{ $serieBoleta->serie ?? 'B001' }}" class="w-full bg-slate-900/60 border border-slate-700 rounded-xl p-2.5 text-amber-400 font-bold text-center">
                        </div>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-bold text-slate-300 mb-1">Medio de Pago</label>
                            <select name="medio_pago" id="medio_pago" onchange="calcularVuelto()" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-slate-200 focus:outline-none focus:border-amber-500">
                                <option value="efectivo">Efectivo en Caja</option>
                                <option value="yape">Yape / Plin (Billetera Digital)</option>
                                <option value="deposito">Depósito / Transferencia Bancaria</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-300 mb-1">Paga con (S/)</label>
                                <input type="number" step="0.50" min="0" name="paga_con" id="paga_con" oninput="calcularVuelto()" placeholder="0.00" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-slate-100 font-bold text-sm focus:outline-none focus:border-amber-500">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-300 mb-1">Cambio / Vuelto</label>
                                <input type="text" readonly id="display_vuelto" value="S/ 0.00" class="w-full bg-slate-900/80 border border-slate-700 rounded-xl p-2.5 text-emerald-400 font-extrabold text-sm text-center">
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-900 p-3 rounded-xl border border-slate-700/80 space-y-1.5 text-xs">
                        <div class="flex justify-between text-slate-400">
                            <span>Subtotal / Op. Gravada:</span>
                            <span id="displaySubtotal">S/ 0.00</span>
                        </div>
                        <div class="flex justify-between text-slate-400">
                            <span>IGV (18%):</span>
                            <span id="displayIGV">S/ 0.00</span>
                        </div>
                        <div class="flex justify-between text-slate-100 font-extrabold text-sm pt-1 border-t border-slate-800">
                            <span>Total Pagar:</span>
                            <span id="displayTotalPagar" class="text-emerald-400">S/ 0.00</span>
                        </div>
                    </div>

                    <div class="pt-2 space-y-2">
                        <button type="submit" class="w-full py-3.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-black rounded-xl text-sm transition shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Guardar Venta & Generar Comprobante</span>
                        </button>
                    </div>

                </div>

            </div>

        </div>
    </form>
</div><!-- MODAL: BUSCAR PRODUCTOS -->
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
</div><!-- MODAL: BUSCAR CLIENTES -->
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
@section('scripts')
<script>
let carritoPOS = [];

function abrirModalBuscarProductos() { document.getElementById('modalBuscarProd').classList.remove('hidden'); }
function cerrarModalBuscarProductos() { document.getElementById('modalBuscarProd').classList.add('hidden'); }
function abrirModalBuscarCliente() { document.getElementById('modalBuscarCli').classList.remove('hidden'); }
function cerrarModalBuscarCliente() { document.getElementById('modalBuscarCli').classList.add('hidden'); }

function filtrarProductosModal() {
    const q = document.getElementById('inputFiltroProd').value.toLowerCase();
    const items = document.querySelectorAll('.item-prod-modal');
    items.forEach(it => {
        const text = it.innerText.toLowerCase();
        it.style.display = text.includes(q) ? 'flex' : 'none';
    });
}

function seleccionarClientePOS(cli) {
    document.getElementById('input_id_cliente').value = cli.id_cliente;
    document.getElementById('cli_nombre').innerText = cli.nombres;
    document.getElementById('cli_doc').innerText = cli.tipo_documento + ': ' + cli.dni_ruc;
    document.getElementById('cli_dir').innerHTML = '<i class="fa-solid fa-location-dot mr-1"></i> ' + (cli.direccion || '-');
    document.getElementById('cli_tel').innerHTML = '<i class="fa-solid fa-phone mr-1"></i> ' + (cli.telefono || '-');
    cerrarModalBuscarCliente();
}

function agregarProductoPOS(p) {
    const existe = carritoPOS.find(i => i.id_producto === p.id_producto);
    if (existe) {
        if (existe.cantidad + 1 > p.stock) {
            alert('Límite de stock disponible alcanzado (' + p.stock + ' und.)');
            return;
        }
        existe.cantidad += 1;
    } else {
        carritoPOS.push({
            id_producto: p.id_producto,
            nombre: p.nombre,
            codigo: p.codigo,
            marca: p.marca ? p.marca.nombre : 'Gral',
            stock: p.stock,
            precio_venta: parseFloat(p.precio_venta),
            precio_venta_mayor: parseFloat(p.precio_venta_mayor || p.precio_venta),
            cantidad_mayorista: parseInt(p.cantidad_mayorista || 999),
            cantidad: 1,
            precio_unitario: parseFloat(p.precio_venta)
        });
    }
    cerrarModalBuscarProductos();
    renderizarTablaPOS();
}

function cambiarCantidad(idx, nuevaCant) {
    nuevaCant = parseInt(nuevaCant);
    if (isNaN(nuevaCant) || nuevaCant < 1) nuevaCant = 1;
    if (nuevaCant > carritoPOS[idx].stock) {
        alert('Stock máximo disponible: ' + carritoPOS[idx].stock);
        nuevaCant = carritoPOS[idx].stock;
    }
    carritoPOS[idx].cantidad = nuevaCant;

    if (nuevaCant >= carritoPOS[idx].cantidad_mayorista) {
        carritoPOS[idx].precio_unitario = carritoPOS[idx].precio_venta_mayor;
    } else {
        carritoPOS[idx].precio_unitario = carritoPOS[idx].precio_venta;
    }

    renderizarTablaPOS();
}

function eliminarFilaPOS(idx) {
    carritoPOS.splice(idx, 1);
    renderizarTablaPOS();
}function renderizarTablaPOS() {
    const body = document.getElementById('bodyPOS');
    body.innerHTML = '';

    if (carritoPOS.length === 0) {
        body.innerHTML = `
            <tr id="rowVacia">
                <td colspan="7" class="p-8 text-center text-slate-500 italic">
                    <i class="fa-solid fa-basket-shopping text-3xl mb-2 block text-slate-600"></i>
                    Aún no has agregado productos a la venta. Haz clic en <strong>"Buscar Productos"</strong> para seleccionar artículos del catálogo.
                </td>
            </tr>
        `;
        actualizarTotalesPOS(0);
        return;
    }

    let totalGral = 0;

    carritoPOS.forEach((item, idx) => {
        const subtotal = item.cantidad * item.precio_unitario;
        totalGral += subtotal;

        const row = document.createElement('tr');
        row.className = 'hover:bg-slate-700/40 border-b border-slate-700/60';
        row.innerHTML = `
            <td class="p-3 font-bold text-amber-400">${idx + 1}</td>
            <td class="p-3 font-bold text-slate-200">
                ${item.nombre}
                <input type="hidden" name="productos[${idx}][id_producto]" value="${item.id_producto}">
            </td>
            <td class="p-3 text-slate-400">${item.marca} (${item.codigo})</td>
            <td class="p-3 text-center">
                <input type="number" min="1" max="${item.stock}" name="productos[${idx}][cantidad]" value="${item.cantidad}" onchange="cambiarCantidad(${idx}, this.value)" class="w-16 bg-slate-900 border border-slate-700 rounded-lg p-1 text-center font-bold text-slate-100">
            </td>
            <td class="p-3 text-right font-medium text-slate-300">
                S/ ${item.precio_unitario.toFixed(2)}
                <input type="hidden" name="productos[${idx}][precio_unitario]" value="${item.precio_unitario}">
            </td>
            <td class="p-3 text-right font-bold text-emerald-400">S/ ${subtotal.toFixed(2)}</td>
            <td class="p-3 text-center">
                <button type="button" onclick="eliminarFilaPOS(${idx})" class="p-1.5 text-rose-400 hover:text-white hover:bg-rose-500 rounded-lg transition" title="Eliminar del carrito">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        `;
        body.appendChild(row);
    });

    actualizarTotalesPOS(totalGral);
}

function actualizarTotalesPOS(total) {
    document.getElementById('totalGeneralDisplay').innerText = 'S/ ' + total.toFixed(2);
    document.getElementById('displayTotalPagar').innerText = 'S/ ' + total.toFixed(2);

    const tipo = document.getElementById('tipo_comprobante').value;
    if (tipo === 'factura') {
        const opGravada = total / 1.18;
        const igv = total - opGravada;
        document.getElementById('displaySubtotal').innerText = 'S/ ' + opGravada.toFixed(2);
        document.getElementById('displayIGV').innerText = 'S/ ' + igv.toFixed(2);
    } else {
        document.getElementById('displaySubtotal').innerText = 'S/ ' + total.toFixed(2);
        document.getElementById('displayIGV').innerText = 'S/ 0.00';
    }

    calcularVuelto();
}

function cambiarTipoComprobante() {
    const tipo = document.getElementById('tipo_comprobante').value;
    const disp = document.getElementById('display_serie');
    if (tipo === 'boleta') disp.value = '{{ $serieBoleta->serie ?? "B001" }}';
    else if (tipo === 'factura') disp.value = '{{ $serieFactura->serie ?? "F001" }}';
    else disp.value = '{{ $serieNota->serie ?? "NV01" }}';

    let totalGral = carritoPOS.reduce((acc, i) => acc + (i.cantidad * i.precio_unitario), 0);
    actualizarTotalesPOS(totalGral);
}

function calcularVuelto() {
    const totalTxt = document.getElementById('displayTotalPagar').innerText.replace('S/ ', '');
    const total = parseFloat(totalTxt) || 0;
    const pagaCon = parseFloat(document.getElementById('paga_con').value) || 0;
    const vuelto = Math.max(0, pagaCon - total);
    document.getElementById('display_vuelto').value = 'S/ ' + vuelto.toFixed(2);
}

function alertarAlmacen(idProd) {
    fetch("{{ route('ventas.alertarAlmacen') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ id_producto: idProd })
    }).then(res => res.json()).then(data => {
        alert(data.message);
    });
}

function notificarCliente(idProd) {
    const idCli = document.getElementById('input_id_cliente').value;
    fetch("{{ route('ventas.notificarCliente') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ id_producto: idProd, id_cliente: idCli })
    }).then(res => res.json()).then(data => {
        alert(data.message);
    });
}
</script>
@endsection