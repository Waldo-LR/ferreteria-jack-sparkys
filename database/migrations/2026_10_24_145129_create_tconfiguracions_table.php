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
        Schema::create('tconfiguracion', function (Blueprint $table) {
            $table->id('id_configuracion');
            $table->string('razon_social', 150);
            $table->string('ruc', 11)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('slogan', 200)->nullable();
            $table->decimal('igv_porcentaje', 5, 2)->default(18);
            $table->decimal('mora_porcentaje', 5, 2)->default(0);
            $table->unsignedInteger('dias_recuperacion')->default(0);
            $table->unsignedInteger('dias_alerta_vencimiento')->default(0);
            $table->boolean('cierre_automatico')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tconfiguracion');
    }
};
