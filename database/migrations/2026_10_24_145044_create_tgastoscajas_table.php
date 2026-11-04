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
        Schema::create('tgastoscaja', function (Blueprint $table) {
            $table->id('id_gasto');
            $table->foreignId('id_caja')->constrained('tcaja', 'id_caja')->cascadeOnDelete();
            $table->foreignId('id_usuario')->constrained('tusuarios', 'id_usuario')->restrictOnDelete();
            $table->string('categoria', 50);
            $table->decimal('monto', 12, 2);
            $table->text('detalle');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tgastoscaja');
    }
};
