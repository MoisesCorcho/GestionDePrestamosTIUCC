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
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade'); // Usuario que realiza la solicitud
            $table->foreignId('product_id')->constrained('products')->onUpdate('cascade')->onDelete('cascade'); // Producto solicitado
            $table->integer('cantidad_solicitada'); // Cantidad solicitada
            $table->enum('estado', ['pendiente', 'aceptado', 'rechazado', 'completado'])->default('pendiente');
            $table->text('motivo_rechazo')->nullable(); // Motivo del rechazo (si aplica)
            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
