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
            $table->foreignId('id_compra')
                ->nullable()
                ->after('id_usuario')
                ->constrained('tcompras', 'id_compra')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tgastoscaja', function (Blueprint $table) {
            $table->dropForeign(['id_compra']);
            $table->dropColumn('id_compra');
        });
    }
};
