import re

file_path = 'resources/views/layouts/adminlte.blade.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace any route('something.index') with url('/something') for sidebar links
replacements = {
    "route('productos.index')": "url('/productos')",
    "route('categorias.index')": "url('/categorias')",
    "route('marcas.index')": "url('/marcas')",
    "route('clientes.index')": "url('/clientes')",
    "route('proveedores.index')": "url('/proveedores')",
    "route('usuarios.index')": "url('/usuarios')",
    "route('ventas.pos')": "url('/ventas/pos')",
    "route('ventas.index')": "url('/ventas')",
    "route('alquileres.index')": "url('/alquileres')",
    "route('cobranzas.index')": "url('/cobranzas')",
    "route('caja.index')": "url('/caja')",
    "route('auditoria.index')": "url('/auditoria')",
    "route('configuracion.index')": "url('/configuracion')",
}

for old_val, new_val in replacements.items():
    content = content.replace(old_val, new_val)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("adminlte.blade.php sidebar links updated to explicit WEB URLs successfully!")
