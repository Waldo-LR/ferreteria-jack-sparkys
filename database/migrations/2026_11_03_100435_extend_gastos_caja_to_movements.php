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
        Schema::table('tgastoscaja', function (Blueprint $table) {
            $table->string('tipo_movimiento', 20)->default('egreso')->after('id_usuario');
            $table->string('medio_pago', 30)->default('efectivo')->after('monto');
            $table->index(['id_caja', 'created_at'], 'tgastoscaja_caja_fecha_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tgastoscaja', function (Blueprint $table) {
            $table->dropIndex('tgastoscaja_caja_fecha_index');
            $table->dropColumn(['tipo_movimiento', 'medio_pago']);
        });
    }
};
