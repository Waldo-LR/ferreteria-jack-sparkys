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
        Schema::create('tventas', function (Blueprint $table) {
            $table->id('id_venta');
            $table->foreignId('id_caja')->constrained('tcaja', 'id_caja')->restrictOnDelete();
            $table->foreignId('id_cliente')->constrained('tclientes', 'id_cliente')->restrictOnDelete();
            $table->foreignId('id_usuario')->constrained('tusuarios', 'id_usuario')->restrictOnDelete();
            $table->string('tipo_comprobante', 30);
            $table->string('serie', 20);
            $table->unsignedInteger('numero');
            $table->date('fecha_emision');
            $table->string('medio_pago', 30);
            $table->decimal('valor_venta', 12, 2);
            $table->decimal('igv', 12, 2);
            $table->decimal('total', 12, 2);
            $table->decimal('monto_pagado', 12, 2)->default(0);
            $table->decimal('vuelto', 12, 2)->default(0);
            $table->string('estado', 30)->default('emitida');
            $table->string('estado_sunat', 30)->nullable();
            $table->text('motivo_anulacion')->nullable();
            $table->timestamps();
            $table->unique(['tipo_comprobante', 'serie', 'numero']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tventas');
    }
};
