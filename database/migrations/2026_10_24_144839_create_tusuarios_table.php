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
        Schema::create('tusuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->foreignId('id_rol')->constrained('troles', 'id_rol')->restrictOnDelete();
            $table->string('nombre', 100);
            $table->string('usuario', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->char('sexo', 1)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('direccion', 150)->nullable();
            $table->text('sobre_mi')->nullable();
            $table->string('foto')->nullable();
            $table->boolean('activo')->default(true);
            $table->dateTime('fecha_baja')->nullable();
            $table->dateTime('ultimo_login')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tusuarios');
    }
};
