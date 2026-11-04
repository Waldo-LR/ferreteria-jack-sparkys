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
        Schema::create('tnotificaciones', function (Blueprint $table) {
            $table->id('id_notificacion');
            $table->foreignId('id_producto')->constrained('tproductos', 'id_producto')->cascadeOnDelete();
            $table->foreignId('id_cliente')->nullable()->constrained('tclientes', 'id_cliente')->nullOnDelete();
            $table->foreignId('id_usuario')->constrained('tusuarios', 'id_usuario')->restrictOnDelete();
            $table->string('tipo', 50);
            $table->boolean('atendida')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tnotificaciones');
    }
};
