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
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // Relación con products
            $table->string('codigo_inventario')->nullable(); // Código de inventario
            $table->string('serie')->nullable(); // Número de serie
            $table->enum('estado', ['prestado', 'dañado', 'disponible'])->default('disponible');
            $table->string('descripcion_lugar')->nullable(); // Ubicación específica
            $table->string('funcionario_responsable')->nullable(); // Responsable del activo
            $table->date('fecha_asignacion')->nullable(); // Fecha de asignación
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
