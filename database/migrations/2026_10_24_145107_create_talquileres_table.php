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
        Schema::create('talquileres', function (Blueprint $table) {
            $table->id('id_alquiler');
            $table->foreignId('id_caja')->constrained('tcaja', 'id_caja')->restrictOnDelete();
            $table->foreignId('id_cliente')->constrained('tclientes', 'id_cliente')->restrictOnDelete();
            $table->foreignId('id_producto')->constrained('tproductos', 'id_producto')->restrictOnDelete();
            $table->foreignId('id_usuario')->constrained('tusuarios', 'id_usuario')->restrictOnDelete();
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_dia', 12, 2);
            $table->unsignedInteger('dias_pactados');
            $table->date('fecha_entrega');
            $table->date('fecha_pactada_devolucion');
            $table->date('fecha_devolucion_real')->nullable();
            $table->decimal('total_alquiler', 12, 2);
            $table->decimal('monto_pagado', 12, 2)->default(0);
            $table->string('medio_pago', 30);
            $table->string('ubicacion_herramienta', 200)->nullable();
            $table->string('estado', 30)->default('vigente');
            $table->text('observaciones')->nullable();
            $table->text('observaciones_devolucion')->nullable();
            $table->foreignId('id_usuario_devolucion')->nullable()->constrained('tusuarios', 'id_usuario')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('talquileres');
    }
};
