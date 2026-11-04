<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tcaja', function (Blueprint $table) {
            $table->string('tipo_caja', 20)->default('ventas')->after('id_caja');
            $table->string('tipo_caja_abierta', 20)->nullable()->unique('tcaja_tipo_caja_abierta_unique')->after('tipo_caja');
            $table->decimal('egresos_efectivo', 12, 2)->default(0)->after('total_egresos');
            $table->decimal('monto_contado', 12, 2)->nullable()->after('monto_final');
            $table->decimal('diferencia_cierre', 12, 2)->nullable()->after('monto_contado');
            $table->foreignId('id_usuario_cierre')->nullable()->after('id_usuario')
                ->constrained('tusuarios', 'id_usuario')->nullOnDelete();
            $table->index(['tipo_caja', 'estado']);
        });

        DB::table('tcaja')
            ->where('estado', 'abierta')
            ->update(['tipo_caja_abierta' => 'ventas']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tcaja', function (Blueprint $table) {
            $table->dropForeign(['id_usuario_cierre']);
            $table->dropIndex(['tipo_caja', 'estado']);
            $table->dropUnique('tcaja_tipo_caja_abierta_unique');
            $table->dropColumn([
                'tipo_caja',
                'tipo_caja_abierta',
                'egresos_efectivo',
                'monto_contado',
                'diferencia_cierre',
                'id_usuario_cierre',
            ]);
        });
    }
};
