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
        Schema::create('request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->onDelete('cascade'); // Relación con la tabla requests
            $table->enum('estado', ['pendiente', 'aceptado', 'rechazado', 'completado']); // Estado actual
            $table->timestamp('fecha_cambio'); // Fecha y hora del cambio
            $table->timestamps(); // Para mantener created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_logs');
    }
};
