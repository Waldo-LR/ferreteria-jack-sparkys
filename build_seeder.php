<?php

$header = "<?php\n\nnamespace Database\\Seeders;\n\nuse Illuminate\\Database\\Seeder;\nuse Illuminate\\Support\\Facades\\DB;\nuse Illuminate\\Support\\Facades\\Hash;\nuse Illuminate\\Support\\Facades\\Schema;\nuse Carbon\\Carbon;\n\nclass DatabaseSeeder extends Seeder\n{\n    public function run(): void\n    {\n        Schema::disableForeignKeyConstraints();\n        \$now = Carbon::now();\n\n";

$body1 = "        // 1. ROLES\n        DB::table('troles')->truncate();\n        DB::table('troles')->insert([\n";
$body1 .= "            ['id_rol' => 1, 'nombre' => 'Administrador', 'descripcion' => 'Acceso total al sistema', 'activo' => 1, 'created_at' => \$now, 'updated_at' => \$now],\n";
$body1 .= "            ['id_rol' => 2, 'nombre' => 'Vendedor', 'descripcion' => 'Ventas y atención al cliente', 'activo' => 1, 'created_at' => \$now, 'updated_at' => \$now],\n";
$body1 .= "            ['id_rol' => 3, 'nombre' => 'Cajero', 'descripcion' => 'Control de caja e ingresos/egresos', 'activo' => 1, 'created_at' => \$now, 'updated_at' => \$now],\n";
$body1 .= "            ['id_rol' => 4, 'nombre' => 'Almacenero', 'descripcion' => 'Gestión de inventario y stock', 'activo' => 1, 'created_at' => \$now, 'updated_at' => \$now],\n";
$body1 .= "            ['id_rol' => 5, 'nombre' => 'Cobrador', 'descripcion' => 'Gestión de cobranzas y moras', 'activo' => 1, 'created_at' => \$now, 'updated_at' => \$now],\n";
$body1 .= "        ]);\n\n";

$body2 = "        // 2. USUARIOS\n        DB::table('tusuarios')->truncate();\n        DB::table('tusuarios')->insert([\n";
$body2 .= "            ['id_usuario' => 1, 'id_rol' => 1, 'nombre' => 'Administrador Principal', 'usuario' => 'admin', 'email' => 'admin@jacksparkys.com', 'password' => Hash::make('admin123'), 'sexo' => 'M', 'telefono' => '987654321', 'direccion' => 'Av. Javier Prado 123, Lima', 'sobre_mi' => 'Administrador del sistema Ferretería Jack Sparkys S.A.C.', 'foto' => null, 'activo' => 1, 'fecha_baja' => null, 'ultimo_login' => \$now, 'created_at' => \$now, 'updated_at' => \$now],\n";
$body2 .= "            ['id_usuario' => 2, 'id_rol' => 2, 'nombre' => 'Juan Carlos Perez', 'usuario' => 'jperez', 'email' => 'jperez@jacksparkys.com', 'password' => Hash::make('vendedor123'), 'sexo' => 'M', 'telefono' => '987123456', 'direccion' => 'Calle Los Olivos 456, Lima', 'sobre_mi' => 'Vendedor senior de herramientas y materiales.', 'foto' => null, 'activo' => 1, 'fecha_baja' => null, 'ultimo_login' => \$now, 'created_at' => \$now, 'updated_at' => \$now],\n";
$body2 .= "            ['id_usuario' => 3, 'id_rol' => 3, 'nombre' => 'Maria Elena Gutierrez', 'usuario' => 'mgutierrez', 'email' => 'mgutierrez@jacksparkys.com', 'password' => Hash::make('caja123'), 'sexo' => 'F', 'telefono' => '912345678', 'direccion' => 'Av. Arequipa 789, Lima', 'sobre_mi' => 'Encargada de caja principal.', 'foto' => null, 'activo' => 1, 'fecha_baja' => null, 'ultimo_login' => \$now, 'created_at' => \$now, 'updated_at' => \$now]\n";
$body2 .= "        ]);\n\n";

$body3 = "        // 3. CATEGORIAS\n        DB::table('tcategorias')->truncate();\n        DB::table('tcategorias')->insert([\n";
$body3 .= "            ['id_categoria' => 1, 'nombre' => 'Herramientas Manuales', 'activo' => 1, 'fecha_baja' => null, 'created_at' => \$now, 'updated_at' => \$now],\n";
$body3 .= "            ['id_categoria' => 2, 'nombre' => 'Herramientas Eléctricas', 'activo' => 1, 'fecha_baja' => null, 'created_at' => \$now, 'updated_at' => \$now],\n";
$body3 .= "            ['id_categoria' => 3, 'nombre' => 'Materiales de Construcción', 'activo' => 1, 'fecha_baja' => null, 'created_at' => \$now, 'updated_at' => \$now],\n";
$body3 .= "            ['id_categoria' => 4, 'nombre' => 'Gasfitería y Tuberías', 'activo' => 1, 'fecha_baja' => null, 'created_at' => \$now, 'updated_at' => \$now],\n";
$body3 .= "            ['id_categoria' => 5, 'nombre' => 'Electricidad e Iluminación', 'activo' => 1, 'fecha_baja' => null, 'created_at' => \$now, 'updated_at' => \$now],\n";
$body3 .= "            ['id_categoria' => 6, 'nombre' => 'Pinturas y Acabados', 'activo' => 1, 'fecha_baja' => null, 'created_at' => \$now, 'updated_at' => \$now]\n";
$body3 .= "        ]);\n\n";

$body4 = "        // 4. MARCAS\n        DB::table('tmarcas')->truncate();\n        DB::table('tmarcas')->insert([\n";
$body4 .= "            ['id_marca' => 1, 'nombre' => 'Truper', 'activo' => 1, 'fecha_baja' => null, 'created_at' => \$now, 'updated_at' => \$now],\n";
$body4 .= "            ['id_marca' => 2, 'nombre' => 'Stanley', 'activo' => 1, 'fecha_baja' => null, 'created_at' => \$now, 'updated_at' => \$now],\n";
$body4 .= "            ['id_marca' => 3, 'nombre' => 'Bosch', 'activo' => 1, 'fecha_baja' => null, 'created_at' => \$now, 'updated_at' => \$now],\n";
$body4 .= "            ['id_marca' => 4, 'nombre' => 'DeWalt', 'activo' => 1, 'fecha_baja' => null, 'created_at' => \$now, 'updated_at' => \$now],\n";
$body4 .= "            ['id_marca' => 5, 'nombre' => 'Makita', 'activo' => 1, 'fecha_baja' => null, 'created_at' => \$now, 'updated_at' => \$now],\n";
$body4 .= "            ['id_marca' => 6, 'nombre' => 'Pavco', 'activo' => 1, 'fecha_baja' => null, 'created_at' => \$now, 'updated_at' => \$now],\n";
$body4 .= "            ['id_marca' => 7, 'nombre' => 'Yura', 'activo' => 1, 'fecha_baja' => null, 'created_at' => \$now, 'updated_at' => \$now],\n";
$body4 .= "            ['id_marca' => 8, 'nombre' => 'Anypsa', 'activo' => 1, 'fecha_baja' => null, 'created_at' => \$now, 'updated_at' => \$now]\n";
$body4 .= "        ]);\n\n";

file_put_contents(__DIR__ . '/database/seeders/DatabaseSeeder.php', $header . $body1 . $body2 . $body3 . $body4);
echo "Seeder header and part 1 rebuilt.\n";

