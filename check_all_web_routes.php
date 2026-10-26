<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = App\Models\Usuario::where('usuario', 'admin')->first();
Illuminate\Support\Facades\Auth::login($user);

$webRoutes = [
    '/cobranzas' => 'Gestión de Cobranzas',
    '/caja' => 'Control de Caja Chica',
    '/productos' => 'Catálogo de Productos',
    '/categorias' => 'Categorías',
    '/marcas' => 'Marcas',
    '/proveedores' => 'Proveedores',
    '/clientes' => 'Directorio de Clientes',
    '/usuarios' => 'Gestión de Usuarios',
    '/auditoria' => 'Registro de Auditoría',
    '/configuracion' => 'Configuración'
];

foreach ($webRoutes as $uri => $label) {
    try {
        $request = Illuminate\Http\Request::create($uri, 'GET');
        $response = $kernel->handle($request);
        $status = $response->getStatusCode();
        echo sprintf("%-30s | URI: %-20s | STATUS: %d\n", $label, $uri, $status);
        if ($status !== 200) {
            if ($response->exception) {
                echo "   --> EXCEPTION: " . get_class($response->exception) . ": " . $response->exception->getMessage() . " in " . basename($response->exception->getFile()) . ":" . $response->exception->getLine() . "\n";
            }
        }
    } catch (\Throwable $e) {
        echo sprintf("%-30s | URI: %-20s | THROWABLE: %s\n", $label, $uri, $e->getMessage());
    }
}



