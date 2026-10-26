<?php

$file = __DIR__ . '/resources/views/layouts/adminlte.blade.php';
$content = file_get_contents($file);

$replacements = [
    "route('productos.index')" => "url('/productos')",
    "route('categorias.index')" => "url('/categorias')",
    "route('marcas.index')" => "url('/marcas')",
    "route('clientes.index')" => "url('/clientes')",
    "route('proveedores.index')" => "url('/proveedores')",
    "route('usuarios.index')" => "url('/usuarios')",
    "route('ventas.pos')" => "url('/ventas/pos')",
    "route('ventas.index')" => "url('/ventas')",
    "route('alquileres.index')" => "url('/alquileres')",
    "route('cobranzas.index')" => "url('/cobranzas')",
    "route('caja.index')" => "url('/caja')",
    "route('auditoria.index')" => "url('/auditoria')",
    "route('configuracion.index')" => "url('/configuracion')",
];

foreach ($replacements as $old => $new) {
    $content = str_replace($old, $new, $content);
}

file_put_contents($file, $content);
echo "adminlte.blade.php updated with clean web URLs!\n";
