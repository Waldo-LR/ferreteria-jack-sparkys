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
        Schema::create('tcobranzas', function (Blueprint $table) {
            $table->id('id_cobranza');
            $table->foreignId('id_alquiler')->constrained('talquileres', 'id_alquiler')->cascadeOnDelete();
            $table->foreignId('id_usuario')->constrained('tusuarios', 'id_usuario')->restrictOnDelete();
            $table->unsignedInteger('dias_atraso')->default(0);
            $table->decimal('monto_mora', 12, 2);
            $table->decimal('monto_pagado', 12, 2)->default(0);
            $table->string('estado', 30)->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tcobranzas');
    }
};
