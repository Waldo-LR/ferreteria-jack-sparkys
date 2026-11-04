<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('talquileres', function (Blueprint $table) {
            $table->decimal('monto_garantia', 12, 2)->default(0)->after('monto_pagado');
            $table->decimal('monto_garantia_aplicado', 12, 2)->default(0)->after('monto_garantia');
            $table->decimal('monto_garantia_devuelto', 12, 2)->default(0)->after('monto_garantia_aplicado');
            $table->string('medio_pago_garantia', 30)->nullable()->after('medio_pago');
        });

        Schema::table('tcobranzas', function (Blueprint $table) {
            $table->string('tipo', 30)->default('mora')->after('id_usuario');
            $table->decimal('porcentaje_aplicado', 5, 2)->default(0)->after('dias_atraso');
        });

        Schema::table('tgastoscaja', function (Blueprint $table) {
            $table->foreignId('id_alquiler')
                ->nullable()
                ->after('id_compra')
                ->constrained('talquileres', 'id_alquiler')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tgastoscaja', function (Blueprint $table) {
            $table->dropForeign(['id_alquiler']);
            $table->dropColumn('id_alquiler');
        });

        Schema::table('tcobranzas', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'porcentaje_aplicado']);
        });

        Schema::table('talquileres', function (Blueprint $table) {
            $table->dropColumn([
                'monto_garantia',
                'monto_garantia_aplicado',
                'monto_garantia_devuelto',
                'medio_pago_garantia',
            ]);
        });
    }
};
