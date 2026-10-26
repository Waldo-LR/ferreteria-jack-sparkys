<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        $now = Carbon::now();

        // 1. ROLES
        DB::table("troles")->truncate();
        DB::table("troles")->insert([
            ["id_rol" => 1, "nombre" => "Administrador", "descripcion" => "Acceso total al sistema", "activo" => 1, "created_at" => $now, "updated_at" => $now],
            ["id_rol" => 2, "nombre" => "Vendedor", "descripcion" => "Ventas, alquileres y clientes", "activo" => 1, "created_at" => $now, "updated_at" => $now],
            ["id_rol" => 3, "nombre" => "Almacenero", "descripcion" => "Inventario, stock y compras", "activo" => 1, "created_at" => $now, "updated_at" => $now],
            ["id_rol" => 4, "nombre" => "Gestor de Cobranzas", "descripcion" => "Cobranzas, moras y recupero", "activo" => 1, "created_at" => $now, "updated_at" => $now],
            ["id_rol" => 5, "nombre" => "Cajero", "descripcion" => "Caja chica y movimientos", "activo" => 1, "created_at" => $now, "updated_at" => $now],
        ]);

        // 2. USUARIOS
        DB::table("tusuarios")->truncate();
        $pwd = Hash::make("123456");
        DB::table("tusuarios")->insert([
            ["id_usuario" => 1, "id_rol" => 1, "nombre" => "Carlos Mendoza (Administrador)", "usuario" => "admin", "email" => "admin@jacksparkys.com", "password" => $pwd, "sexo" => "M", "telefono" => "987654321", "direccion" => "Av. Javier Prado Este 1234, Lima", "sobre_mi" => "Administrador General del Sistema.", "foto" => null, "activo" => 1, "fecha_baja" => null, "ultimo_login" => $now, "created_at" => $now, "updated_at" => $now],
            ["id_usuario" => 2, "id_rol" => 2, "nombre" => "Juan Pérez (Vendedor)", "usuario" => "vendedor", "email" => "vendedor@jacksparkys.com", "password" => $pwd, "sexo" => "M", "telefono" => "987123456", "direccion" => "Calle Los Olivos 456, Lima", "sobre_mi" => "Vendedor especialista en ventas y alquileres.", "foto" => null, "activo" => 1, "fecha_baja" => null, "ultimo_login" => $now, "created_at" => $now, "updated_at" => $now],
            ["id_usuario" => 3, "id_rol" => 3, "nombre" => "Roberto Sánchez (Almacenero)", "usuario" => "almacen", "email" => "almacen@jacksparkys.com", "password" => $pwd, "sexo" => "M", "telefono" => "976543210", "direccion" => "Av. Los Héroes 789, Lima", "sobre_mi" => "Encargado de inventario y recepción.", "foto" => null, "activo" => 1, "fecha_baja" => null, "ultimo_login" => $now, "created_at" => $now, "updated_at" => $now],
            ["id_usuario" => 4, "id_rol" => 4, "nombre" => "Luis Torres (Gestor Cobranzas)", "usuario" => "cobranzas", "email" => "cobranzas@jacksparkys.com", "password" => $pwd, "sexo" => "M", "telefono" => "965432109", "direccion" => "Jr. Pachacutec 321, Lima", "sobre_mi" => "Gestor de cobranzas y moras.", "foto" => null, "activo" => 1, "fecha_baja" => null, "ultimo_login" => $now, "created_at" => $now, "updated_at" => $now],
            ["id_usuario" => 5, "id_rol" => 5, "nombre" => "María Gutierrez (Cajera)", "usuario" => "cajero", "email" => "caja@jacksparkys.com", "password" => $pwd, "sexo" => "F", "telefono" => "912345678", "direccion" => "Av. Arequipa 789, Lima", "sobre_mi" => "Cajera principal.", "foto" => null, "activo" => 1, "fecha_baja" => null, "ultimo_login" => $now, "created_at" => $now, "updated_at" => $now]
        ]);

        // 3. CATEGORÍAS
        DB::table("tcategorias")->truncate();
        DB::table("tcategorias")->insert([
            ["id_categoria" => 1, "nombre" => "Herramientas Manuales", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now],
            ["id_categoria" => 2, "nombre" => "Herramientas Eléctricas y Maquinaria", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now],
            ["id_categoria" => 3, "nombre" => "Materiales de Construcción", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now],
            ["id_categoria" => 4, "nombre" => "Gasfitería y Tuberías", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now],
            ["id_categoria" => 5, "nombre" => "Electricidad e Iluminación", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now],
            ["id_categoria" => 6, "nombre" => "Pinturas, Químicos y Acabados", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now],
            ["id_categoria" => 7, "nombre" => "Cerrajería y Fijaciones", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now],
            ["id_categoria" => 8, "nombre" => "Seguridad y Protección (EPP)", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now]
        ]);

        // 4. MARCAS
        DB::table("tmarcas")->truncate();
        DB::table("tmarcas")->insert([
            ["id_marca" => 1, "nombre" => "Truper", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now],
            ["id_marca" => 2, "nombre" => "Stanley", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now],
            ["id_marca" => 3, "nombre" => "Bosch", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now],
            ["id_marca" => 4, "nombre" => "DeWalt", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now],
            ["id_marca" => 5, "nombre" => "Makita", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now],
            ["id_marca" => 6, "nombre" => "Pavco", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now],
            ["id_marca" => 7, "nombre" => "Yura", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now],
            ["id_marca" => 8, "nombre" => "Anypsa", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now],
            ["id_marca" => 9, "nombre" => "Sika", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now],
            ["id_marca" => 10, "nombre" => "Indeco", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now]
        ]);

        // 5. PROVEEDORES
        DB::table("tproveedores")->truncate();
        DB::table("tproveedores")->insert([
            ["id_proveedor" => 1, "razon_social" => "Distribuidora Ferretera del Sur S.A.C.", "ruc" => "20601234567", "telefono" => "014567890", "email" => "ventas@ferreteradelsur.pe", "direccion" => "Av. Argentina 1450, Lima", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now],
            ["id_proveedor" => 2, "razon_social" => "Importaciones Truper Perú E.I.R.L.", "ruc" => "20512345678", "telefono" => "019876543", "email" => "contacto@truper.com.pe", "direccion" => "Av. Colonial 2300, Lima", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now],
            ["id_proveedor" => 3, "razon_social" => "Comercializadora Stanley Black & Decker Perú S.A.", "ruc" => "20489123456", "telefono" => "013456789", "email" => "ventas@stanleyperu.com", "direccion" => "Av. Industrial 567, Ate", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now]
        ]);

        // 6. CLIENTES (INCLUYE CLIENTE CON MORA PARA DEMOSTRAR REGLA DE BLOQUEO)
        DB::table("tclientes")->truncate();
        DB::table("tclientes")->insert([
            ["id_cliente" => 1, "tipo_documento" => "DNI", "dni_ruc" => "45891234", "nombres" => "Juan Carlos Quispe Morales", "email" => "jquispe@gmail.com", "telefono" => "954123890", "direccion" => "Jr. Huánuco 321, Lima", "fecha_nacimiento" => "1988-06-15", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now],
            ["id_cliente" => 2, "tipo_documento" => "RUC", "dni_ruc" => "20501928374", "nombres" => "Constructora San Martín S.A.C.", "email" => "compras@sanmartin.pe", "telefono" => "014829102", "direccion" => "Av. El Sol 890, San Borja", "fecha_nacimiento" => null, "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now],
            ["id_cliente" => 3, "tipo_documento" => "DNI", "dni_ruc" => "71238495", "nombres" => "María Elena Flores Benítez", "email" => "mflores@hotmail.com", "telefono" => "981237465", "direccion" => "Av. Los Próceres 123, Surco", "fecha_nacimiento" => "1992-03-22", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now],
            ["id_cliente" => 4, "tipo_documento" => "DNI", "dni_ruc" => "10293847", "nombres" => "Pedro Alvarado Ruiz (CLIENTE CON MORA)", "email" => "palvarado@gmail.com", "telefono" => "978912345", "direccion" => "Calle Las Casuarinas 450, Chorrillos", "fecha_nacimiento" => "1984-11-10", "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now]
        ]);

        // 7. PRODUCTOS (INCLUYE PRECIOS POR MAYOR/MENOR, FECHAS VENCIMIENTO, ES_ALQUILABLE, Y STOCK 0)
        DB::table("tproductos")->truncate();
        DB::table("tproductos")->insert([
            [
                "id_producto" => 1, "codigo" => "HER-001", "id_categoria" => 1, "id_marca" => 2,
                "nombre" => "Martillo de Uña 16oz Stanley Mango Fibra", "descripcion" => "Martillo ergonómico con mango antideslizante",
                "stock" => 45, "stock_minimo" => 10, "precio_compra" => 25.00, "precio_venta" => 38.00,
                "precio_venta_mayor" => 33.00, "cantidad_mayorista" => 6, "es_alquilable" => 0, "precio_alquiler_dia" => 0.00,
                "fecha_vencimiento" => null, "imagen" => null, "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now
            ],
            [
                "id_producto" => 2, "codigo" => "MAQ-002", "id_categoria" => 2, "id_marca" => 4,
                "nombre" => "Taladro Percutor Inalámbrico 20V MAX DeWalt", "descripcion" => "Taladro percutor batería ion litio 20V con maletín y 2 baterías",
                "stock" => 12, "stock_minimo" => 3, "precio_compra" => 390.00, "precio_venta" => 540.00,
                "precio_venta_mayor" => 495.00, "cantidad_mayorista" => 2, "es_alquilable" => 1, "precio_alquiler_dia" => 35.00,
                "fecha_vencimiento" => null, "imagen" => null, "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now
            ],
            [
                "id_producto" => 3, "codigo" => "MAT-003", "id_categoria" => 3, "id_marca" => 7,
                "nombre" => "Cemento Yura Tipo IP 42.5kg", "descripcion" => "Bolsa de Cemento Tipo IP para albañilería y estructuras",
                "stock" => 180, "stock_minimo" => 30, "precio_compra" => 24.50, "precio_venta" => 29.50,
                "precio_venta_mayor" => 27.80, "cantidad_mayorista" => 20, "es_alquilable" => 0, "precio_alquiler_dia" => 0.00,
                "fecha_vencimiento" => "2027-02-15", "imagen" => null, "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now
            ],
            [
                "id_producto" => 4, "codigo" => "MAQ-004", "id_categoria" => 2, "id_marca" => 5,
                "nombre" => "Amoladora Angular 4.5\" 840W Makita", "descripcion" => "Esmeriladora angular compacta profesional",
                "stock" => 8, "stock_minimo" => 2, "precio_compra" => 185.00, "precio_venta" => 265.00,
                "precio_venta_mayor" => 245.00, "cantidad_mayorista" => 3, "es_alquilable" => 1, "precio_alquiler_dia" => 25.00,
                "fecha_vencimiento" => null, "imagen" => null, "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now
            ],
            [
                "id_producto" => 5, "codigo" => "PIN-005", "id_categoria" => 6, "id_marca" => 8,
                "nombre" => "Pintura Esmalte Sintético Blanco Anypsa 1 Galón", "descripcion" => "Esmalte sintético brillante de máxima cobertura",
                "stock" => 28, "stock_minimo" => 5, "precio_compra" => 43.00, "precio_venta" => 62.00,
                "precio_venta_mayor" => 56.50, "cantidad_mayorista" => 4, "es_alquilable" => 0, "precio_alquiler_dia" => 0.00,
                "fecha_vencimiento" => "2026-12-30", "imagen" => null, "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now
            ],
            [
                "id_producto" => 6, "codigo" => "MAQ-006", "id_categoria" => 2, "id_marca" => 3,
                "nombre" => "Rotomartillo SDS Plus 800W Bosch GBH 2-24 D", "descripcion" => "Rotomartillo para demolición y perforación",
                "stock" => 5, "stock_minimo" => 1, "precio_compra" => 520.00, "precio_venta" => 690.00,
                "precio_venta_mayor" => 650.00, "cantidad_mayorista" => 2, "es_alquilable" => 1, "precio_alquiler_dia" => 45.00,
                "fecha_vencimiento" => null, "imagen" => null, "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now
            ],
            [
                // PRODUCTO CON STOCK 0 PARA PROBAR ALERTA A ALMACEN Y NOTIFICAR AL CLIENTE
                "id_producto" => 7, "codigo" => "ELE-007", "id_categoria" => 5, "id_marca" => 10,
                "nombre" => "Cable Mellizo 2x14 AWG Indeco Rollo 100m (AGOTADO)", "descripcion" => "Cable de cobre flexible para instalaciones eléctricas",
                "stock" => 0, "stock_minimo" => 5, "precio_compra" => 110.00, "precio_venta" => 155.00,
                "precio_venta_mayor" => 142.00, "cantidad_mayorista" => 3, "es_alquilable" => 0, "precio_alquiler_dia" => 0.00,
                "fecha_vencimiento" => null, "imagen" => null, "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now
            ],
            [
                // PRODUCTO CON CADUCIDAD CERCANA PARA PROBAR ALERTA DE VENCIMIENTO
                "id_producto" => 8, "codigo" => "QUI-008", "id_categoria" => 6, "id_marca" => 9,
                "nombre" => "Aditivo Acelerante Sika-3 Galón (POR VENCER)", "descripcion" => "Acelerante de fraguado para concreto",
                "stock" => 14, "stock_minimo" => 4, "precio_compra" => 35.00, "precio_venta" => 52.00,
                "precio_venta_mayor" => 48.00, "cantidad_mayorista" => 4, "es_alquilable" => 0, "precio_alquiler_dia" => 0.00,
                "fecha_vencimiento" => Carbon::now()->addDays(10)->toDateString(), "imagen" => null, "activo" => 1, "fecha_baja" => null, "created_at" => $now, "updated_at" => $now
            ],
        ]);

        // 8. CONFIGURACIÓN
        DB::table("tconfiguracion")->truncate();
        DB::table("tconfiguracion")->insert([
            "id_configuracion" => 1,
            "razon_social" => "FERRETERÍA JACK SPARKY'S S.A.C.",
            "ruc" => "20609876543",
            "direccion" => "Av. Los Héroes 123, San Juan de Miraflores, Lima - Perú",
            "telefono" => "987654321",
            "email" => "ventas@jacksparkys.com",
            "slogan" => "Tu mejor aliado, en tus proyectos",
            "igv_porcentaje" => 18.00,
            "mora_porcentaje" => 5.00,
            "dias_recuperacion" => 30,
            "dias_alerta_vencimiento" => 5,
            "cierre_automatico" => 0,
            "created_at" => $now,
            "updated_at" => $now
        ]);

        // 9. SERIES DE COMPROBANTES
        DB::table("tseries")->truncate();
        DB::table("tseries")->insert([
            ["id_serie" => 1, "tipo_comprobante" => "nota_venta", "serie" => "NV01", "ultimo_numero" => 105, "activo" => 1, "created_at" => $now, "updated_at" => $now],
            ["id_serie" => 2, "tipo_comprobante" => "boleta", "serie" => "B001", "ultimo_numero" => 58, "activo" => 1, "created_at" => $now, "updated_at" => $now],
            ["id_serie" => 3, "tipo_comprobante" => "factura", "serie" => "F001", "ultimo_numero" => 24, "activo" => 1, "created_at" => $now, "updated_at" => $now],
        ]);

        // 10. CAJA CHICA
        DB::table("tcaja")->truncate();
        DB::table("tcaja")->insert([
            [
                "id_caja" => 1, "id_usuario" => 5, "fecha_apertura" => $now->copy()->subHours(8),
                "fecha_cierre" => null, "monto_inicial" => 350.00, "total_ingresos" => 1240.50,
                "ingresos_efectivo" => 980.50, "total_egresos" => 65.00, "monto_final" => 1525.50,
                "tipo_cierre" => null, "estado" => "abierta", "observaciones" => "Apertura de turno mañana - Cajera María Gutierrez",
                "created_at" => $now, "updated_at" => $now
            ]
        ]);

        // 11. GASTOS DE CAJA / LOGÍSTICA
        DB::table("tgastoscaja")->truncate();
        DB::table("tgastoscaja")->insert([
            ["id_gasto" => 1, "id_caja" => 1, "id_usuario" => 5, "categoria" => "logistica", "monto" => 35.00, "detalle" => "Flete de transporte de cemento a obra", "created_at" => $now, "updated_at" => $now],
            ["id_gasto" => 2, "id_caja" => 1, "id_usuario" => 5, "categoria" => "servicios", "monto" => 30.00, "detalle" => "Cinta de embalaje y artículos de limpieza", "created_at" => $now, "updated_at" => $now],
        ]);

        // 12. VENTAS
        DB::table("tventas")->truncate();
        DB::table("tventas")->insert([
            [
                "id_venta" => 1, "id_caja" => 1, "id_cliente" => 1, "id_usuario" => 2,
                "tipo_comprobante" => "boleta", "serie" => "B001", "numero" => 57,
                "fecha_emision" => $now->toDateString(), "medio_pago" => "efectivo",
                "valor_venta" => 110.17, "igv" => 19.83, "total" => 130.00,
                "monto_pagado" => 150.00, "vuelto" => 20.00, "estado" => "emitida",
                "estado_sunat" => "simulado", "motivo_anulacion" => null,
                "created_at" => $now, "updated_at" => $now
            ],
            [
                "id_venta" => 2, "id_caja" => 1, "id_cliente" => 2, "id_usuario" => 2,
                "tipo_comprobante" => "factura", "serie" => "F001", "numero" => 24,
                "fecha_emision" => $now->toDateString(), "medio_pago" => "yape",
                "valor_venta" => 690.68, "igv" => 124.32, "total" => 815.00,
                "monto_pagado" => 815.00, "vuelto" => 0.00, "estado" => "emitida",
                "estado_sunat" => "simulado", "motivo_anulacion" => null,
                "created_at" => $now, "updated_at" => $now
            ]
        ]);

        // 13. DETALLE DE VENTAS
        DB::table("tdetalleventas")->truncate();
        DB::table("tdetalleventas")->insert([
            ["id_detalle_venta" => 1, "id_venta" => 1, "id_producto" => 1, "cantidad" => 1, "precio_unitario" => 38.00, "costo_unitario" => 25.00, "subtotal" => 38.00, "created_at" => $now, "updated_at" => $now],
            ["id_detalle_venta" => 2, "id_venta" => 1, "id_producto" => 5, "cantidad" => 1, "precio_unitario" => 62.00, "costo_unitario" => 43.00, "subtotal" => 62.00, "created_at" => $now, "updated_at" => $now],
            ["id_detalle_venta" => 3, "id_venta" => 2, "id_producto" => 3, "cantidad" => 20, "precio_unitario" => 27.80, "costo_unitario" => 24.50, "subtotal" => 556.00, "created_at" => $now, "updated_at" => $now],
            ["id_detalle_venta" => 4, "id_venta" => 2, "id_producto" => 4, "cantidad" => 1, "precio_unitario" => 259.00, "costo_unitario" => 185.00, "subtotal" => 259.00, "created_at" => $now, "updated_at" => $now],
        ]);

        // 14. ALQUILERES (INCLUYE ALQUILER VENCIDO PARA PROBAR BLOQUEO DE ALQUILER)
        DB::table("talquileres")->truncate();
        DB::table("talquileres")->insert([
            [
                "id_alquiler" => 1, "id_caja" => 1, "id_cliente" => 3, "id_producto" => 2, "id_usuario" => 2,
                "cantidad" => 1, "precio_dia" => 35.00, "dias_pactados" => 4,
                "fecha_entrega" => $now->copy()->subDays(1)->toDateString(),
                "fecha_pactada_devolucion" => $now->copy()->addDays(3)->toDateString(),
                "fecha_devolucion_real" => null, "total_alquiler" => 140.00, "monto_pagado" => 140.00,
                "medio_pago" => "yape", "ubicacion_herramienta" => "Obra Residencial Surco, Calle Las Violetas 230",
                "estado" => "vigente", "observaciones" => "Taladro DeWalt en maletín con 2 baterías.",
                "observaciones_devolucion" => null, "id_usuario_devolucion" => null,
                "created_at" => $now, "updated_at" => $now
            ],
            [
                // ALQUILER VENCIDO PARA CLIENTE MOROSO (id_cliente 4: Pedro Alvarado)
                "id_alquiler" => 2, "id_caja" => 1, "id_cliente" => 4, "id_producto" => 6, "id_usuario" => 2,
                "cantidad" => 1, "precio_dia" => 45.00, "dias_pactados" => 2,
                "fecha_entrega" => $now->copy()->subDays(6)->toDateString(),
                "fecha_pactada_devolucion" => $now->copy()->subDays(4)->toDateString(),
                "fecha_devolucion_real" => null, "total_alquiler" => 90.00, "monto_pagado" => 90.00,
                "medio_pago" => "efectivo", "ubicacion_herramienta" => "Calle Las Casuarinas 450, Chorrillos",
                "estado" => "vencido", "observaciones" => "Rotomartillo Bosch con cincel de punta.",
                "observaciones_devolucion" => null, "id_usuario_devolucion" => null,
                "created_at" => $now->copy()->subDays(6), "updated_at" => $now
            ]
        ]);

        // 15. COBRANZAS (MORA ASOCIADA AL ALQUILER VENCIDO)
        DB::table("tcobranzas")->truncate();
        DB::table("tcobranzas")->insert([
            [
                "id_cobranza" => 1, "id_alquiler" => 2, "id_usuario" => 4,
                "dias_atraso" => 4, "monto_mora" => 180.00,
                "monto_pagado" => 0.00, "estado" => "pendiente",
                "observaciones" => "Cliente no devuelve rotomartillo hace 4 días. Contactado telefónicamente.",
                "created_at" => $now, "updated_at" => $now
            ]
        ]);

        // 16. MOVIMIENTOS DE STOCK
        DB::table("tmovimientosstock")->truncate();
        DB::table("tmovimientosstock")->insert([
            ["id_movimiento" => 1, "id_producto" => 1, "id_usuario" => 1, "tipo" => "entrada", "cantidad" => 50, "stock_resultante" => 50, "motivo" => "Inventario Inicial", "referencia" => "INV-2026", "created_at" => $now, "updated_at" => $now],
            ["id_movimiento" => 2, "id_producto" => 1, "id_usuario" => 2, "tipo" => "salida", "cantidad" => 1, "stock_resultante" => 49, "motivo" => "Venta Mostrador B001-57", "referencia" => "B001-57", "created_at" => $now, "updated_at" => $now],
            ["id_movimiento" => 3, "id_producto" => 7, "id_usuario" => 3, "tipo" => "salida", "cantidad" => 0, "stock_resultante" => 0, "motivo" => "Stock Agotado", "referencia" => "MER-001", "created_at" => $now, "updated_at" => $now],
        ]);

        // 17. NOTIFICACIONES
        DB::table("tnotificaciones")->truncate();
        DB::table("tnotificaciones")->insert([
            ["id_notificacion" => 1, "id_producto" => 7, "id_cliente" => null, "id_usuario" => 3, "tipo" => "alerta_almacen", "atendida" => 0, "created_at" => $now, "updated_at" => $now],
            ["id_notificacion" => 2, "id_producto" => 7, "id_cliente" => 1, "id_usuario" => 2, "tipo" => "aviso_cliente", "atendida" => 0, "created_at" => $now, "updated_at" => $now],
        ]);

        // 18. AUDITORÍA BÁSICA
        DB::table("tauditoria")->truncate();
        DB::table("tauditoria")->insert([
            ["id_auditoria" => 1, "id_usuario" => 1, "accion" => "INICIO_SISTEMA", "tabla_afectada" => "sistema", "id_registro" => 1, "detalle" => "Puesta en marcha del Sistema MVC Ferretería Jack Sparkys S.A.C.", "ip" => "127.0.0.1", "created_at" => $now, "updated_at" => $now],
            ["id_auditoria" => 2, "id_usuario" => 2, "accion" => "VENTA_EMITIDA", "tabla_afectada" => "tventas", "id_registro" => 1, "detalle" => "Emisión Boleta B001-57 por S/ 130.00", "ip" => "127.0.0.1", "created_at" => $now, "updated_at" => $now],
            ["id_auditoria" => 3, "id_usuario" => 4, "accion" => "MORA_GENERADA", "tabla_afectada" => "tcobranzas", "id_registro" => 1, "detalle" => "Generación de mora S/ 180.00 por rotomartillo no devuelto", "ip" => "127.0.0.1", "created_at" => $now, "updated_at" => $now],
        ]);

        Schema::enableForeignKeyConstraints();
    }
}
