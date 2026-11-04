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
        Schema::create('tseries', function (Blueprint $table) {
            $table->id('id_serie');
            $table->string('tipo_comprobante', 30);
            $table->string('serie', 20);
            $table->unsignedInteger('ultimo_numero')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['tipo_comprobante', 'serie']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tseries');
    }
};
