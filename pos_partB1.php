<?php

$p_B1 = <<<'HTML'

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
}
HTML;

file_put_contents(__DIR__ . '/resources/views/ventas/pos.blade.php', $p_B1, FILE_APPEND);
echo "pos_B1 done\n";
