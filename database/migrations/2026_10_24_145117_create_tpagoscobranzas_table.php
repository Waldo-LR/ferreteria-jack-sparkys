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
        Schema::create('tpagoscobranza', function (Blueprint $table) {
            $table->id('id_pago');
            $table->foreignId('id_cobranza')->constrained('tcobranzas', 'id_cobranza')->cascadeOnDelete();
            $table->foreignId('id_usuario')->constrained('tusuarios', 'id_usuario')->restrictOnDelete();
            $table->foreignId('id_caja')->constrained('tcaja', 'id_caja')->restrictOnDelete();
            $table->decimal('monto', 12, 2);
            $table->string('medio_pago', 30);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tpagoscobranza');
    }
};
