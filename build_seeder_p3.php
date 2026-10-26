<?php

$body8 = "        // 8. CONFIGURACION\n        DB::table('tconfiguracion')->truncate();\n        DB::table('tconfiguracion')->insert([\n";
$body8 .= "            'id_configuracion' => 1, 'razon_social' => 'FERRETERÍA JACK SPARKY\'S S.A.C.', 'ruc' => '20609876543', 'direccion' => 'Av. Los Héroes 123, Lima', 'telefono' => '987654321', 'email' => 'ventas@jacksparkys.com', 'slogan' => 'Construyendo juntos tus mejores proyectos con garantía y calidad', 'igv_porcentaje' => 18.00, 'mora_porcentaje' => 5.00, 'dias_recuperacion' => 30, 'dias_alerta_vencimiento' => 5, 'cierre_automatico' => 0, 'created_at' => \$now, 'updated_at' => \$now\n";
$body8 .= "        ]);\n\n";

$body9 = "        // 9. SERIES\n        DB::table('tseries')->truncate();\n        DB::table('tseries')->insert([\n";
$body9 .= "            ['id_serie' => 1, 'tipo_comprobante' => 'nota_venta', 'serie' => 'NV01', 'ultimo_numero' => 100, 'activo' => 1, 'created_at' => \$now, 'updated_at' => \$now],\n";
$body9 .= "            ['id_serie' => 2, 'tipo_comprobante' => 'boleta', 'serie' => 'B001', 'ultimo_numero' => 50, 'activo' => 1, 'created_at' => \$now, 'updated_at' => \$now],\n";
$body9 .= "            ['id_serie' => 3, 'tipo_comprobante' => 'factura', 'serie' => 'F001', 'ultimo_numero' => 20, 'activo' => 1, 'created_at' => \$now, 'updated_at' => \$now]\n";
$body9 .= "        ]);\n\n";

$body10 = "        // 10. CAJA\n        DB::table('tcaja')->truncate();\n        DB::table('tcaja')->insert([\n";
$body10 .= "            'id_caja' => 1, 'id_usuario' => 3, 'fecha_apertura' => \$now->copy()->subHours(6), 'fecha_cierre' => null, 'monto_inicial' => 200.00, 'total_ingresos' => 649.50, 'ingresos_efectivo' => 649.50, 'total_egresos' => 30.00, 'monto_final' => 819.50, 'tipo_cierre' => null, 'estado' => 'abierta', 'observaciones' => 'Apertura de caja turno mañana', 'created_at' => \$now, 'updated_at' => \$now\n";
$body10 .= "        ]);\n\n";

$body11 = "        // 11. GASTOS CAJA\n        DB::table('tgastoscaja')->truncate();\n        DB::table('tgastoscaja')->insert([\n";
$body11 .= "            'id_gasto' => 1, 'id_caja' => 1, 'id_usuario' => 3, 'categoria' => 'transporte', 'monto' => 30.00, 'detalle' => 'Pago de flete de transporte de muestras de cemento', 'created_at' => \$now, 'updated_at' => \$now\n";
$body11 .= "        ]);\n\n";

$body12 = "        // 12. VENTAS\n        DB::table('tventas')->truncate();\n        DB::table('tventas')->insert([\n";
$body12 .= "            'id_venta' => 1, 'id_caja' => 1, 'id_cliente' => 1, 'id_usuario' => 2, 'tipo_comprobante' => 'boleta', 'serie' => 'B001', 'numero' => 51, 'fecha_emision' => \$now->toDateString(), 'medio_pago' => 'efectivo', 'valor_venta' => 109.75, 'igv' => 19.75, 'total' => 129.50, 'monto_pagado' => 130.00, 'vuelto' => 0.50, 'estado' => 'emitida', 'estado_sunat' => 'simulado', 'motivo_anulacion' => null, 'created_at' => \$now, 'updated_at' => \$now\n";
$body12 .= "        ]);\n\n";

$body13 = "        // 13. DETALLE VENTAS\n        DB::table('tdetalleventas')->truncate();\n        DB::table('tdetalleventas')->insert([\n";
$body13 .= "            ['id_detalle_venta' => 1, 'id_venta' => 1, 'id_producto' => 1, 'cantidad' => 1, 'precio_unitario' => 38.00, 'costo_unitario' => 25.00, 'subtotal' => 38.00, 'created_at' => \$now, 'updated_at' => \$now],\n";
$body13 .= "            ['id_detalle_venta' => 2, 'id_venta' => 1, 'id_producto' => 3, 'cantidad' => 3, 'precio_unitario' => 29.50, 'costo_unitario' => 24.50, 'subtotal' => 88.50, 'created_at' => \$now, 'updated_at' => \$now]\n";
$body13 .= "        ]);\n\n";

$body14 = "        // 14. ALQUILERES\n        DB::table('talquileres')->truncate();\n        DB::table('talquileres')->insert([\n";
$body14 .= "            'id_alquiler' => 1, 'id_caja' => 1, 'id_cliente' => 3, 'id_producto' => 2, 'id_usuario' => 2, 'cantidad' => 1, 'precio_dia' => 35.00, 'dias_pactados' => 3, 'fecha_entrega' => \$now->toDateString(), 'fecha_pactada_devolucion' => \$now->copy()->addDays(3)->toDateString(), 'fecha_devolucion_real' => null, 'total_alquiler' => 105.00, 'monto_pagado' => 105.00, 'medio_pago' => 'yape', 'ubicacion_herramienta' => 'Obra Av. Las Casuarinas 450', 'estado' => 'vigente', 'observaciones' => 'Se entregó taladro DeWalt en maletín con 2 baterías y cargador.', 'observaciones_devolucion' => null, 'id_usuario_devolucion' => null, 'created_at' => \$now, 'updated_at' => \$now\n";
$body14 .= "        ]);\n\n";

$body15 = "        // 15. AUDITORIA\n        DB::table('tauditoria')->truncate();\n        DB::table('tauditoria')->insert([\n";
$body15 .= "            'id_auditoria' => 1, 'id_usuario' => 1, 'accion' => 'MIGRACION_INICIAL', 'tabla_afectada' => 'sistema', 'id_registro' => 1, 'detalle' => 'Carga inicial de datos de ejemplo en idioma español para Ferretería Jack Sparkys S.A.C.', 'ip' => '127.0.0.1', 'created_at' => \$now, 'updated_at' => \$now\n";
$body15 .= "        ]);\n";
$body15 .= "        Schema::enableForeignKeyConstraints();\n";
$body15 .= "    }\n}\n";

file_put_contents(__DIR__ . '/database/seeders/DatabaseSeeder.php', $body8 . $body9 . $body10 . $body11 . $body12 . $body13 . $body14 . $body15, FILE_APPEND);
echo "Seeder part 3 appended.\n";
