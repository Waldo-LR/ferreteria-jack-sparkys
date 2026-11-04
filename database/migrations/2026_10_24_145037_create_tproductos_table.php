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
        Schema::create('tproductos', function (Blueprint $table) {
            $table->id('id_producto');
            $table->string('codigo', 50)->unique();
            $table->foreignId('id_categoria')->constrained('tcategorias', 'id_categoria')->restrictOnDelete();
            $table->foreignId('id_marca')->constrained('tmarcas', 'id_marca')->restrictOnDelete();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('stock_minimo')->default(0);
            $table->decimal('precio_compra', 12, 2)->default(0);
            $table->decimal('precio_venta', 12, 2);
            $table->decimal('precio_venta_mayor', 12, 2)->default(0);
            $table->unsignedInteger('cantidad_mayorista')->default(1);
            $table->boolean('es_alquilable')->default(false);
            $table->decimal('precio_alquiler_dia', 12, 2)->default(0);
            $table->date('fecha_vencimiento')->nullable();
            $table->string('imagen')->nullable();
            $table->boolean('activo')->default(true);
            $table->dateTime('fecha_baja')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tproductos');
    }
};
