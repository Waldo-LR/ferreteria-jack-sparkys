<?php

$p_B2 = <<<'HTML'
function renderizarTablaPOS() {
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
HTML;

file_put_contents(__DIR__ . '/resources/views/ventas/pos.blade.php', $p_B2, FILE_APPEND);
echo "pos_B2 done\n";
