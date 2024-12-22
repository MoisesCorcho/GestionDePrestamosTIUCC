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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Nombre del producto
            $table->text('descripcion')->nullable(); // Descripción general del producto
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->integer('cantidad')->default(0); // Cantidad total disponible
            // $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps(); // created_at y updated_at
        });

        // Schema::create('inventory', function (Blueprint $table) {
        //     $table->id(); // ID principal
        //     $table->string('sede')->nullable(); // Sede del equipo
        //     $table->string('dependencia')->nullable(); // Dependencia o departamento
        //     $table->string('direccion')->nullable(); // Dirección
        //     $table->string('piso')->nullable(); // Piso donde se encuentra el equipo
        //     $table->string('bloque')->nullable(); // Bloque donde se encuentra el equipo
        //     $table->string('funcionario_responsable')->nullable(); // Nombre del responsable
        //     $table->string('cargo')->nullable(); // Cargo del responsable
        //     $table->string('descripcion_activo')->nullable(); // Descripción general del activo
        //     $table->integer('cantidad')->default(1); // Cantidad disponible
        //     $table->string('marca')->nullable(); // Marca del activo
        //     $table->string('modelo')->nullable(); // Modelo del activo
        //     $table->string('serie')->nullable(); // Número de serie
        //     $table->date('fecha_adquisicion')->nullable(); // Fecha de adquisición
        //     $table->string('proveedor')->nullable(); // Proveedor del activo
        //     $table->string('numero_factura')->nullable(); // Número de factura
        //     $table->decimal('valor', 15, 2)->nullable(); // Valor del activo
        //     $table->boolean('asegurado')->default(false); // Indica si está asegurado
        //     $table->string('estado')->nullable(); // Estado del activo (e.g., "Dañado", "En uso")
        //     $table->date('fecha_asignacion')->nullable(); // Fecha de asignación
        //     $table->boolean('activo_compartido')->default(false); // Indica si es compartido
        //     $table->string('codigo_inventario')->unique(); // Código único de inventario
        //     $table->string('nombre_equipo')->nullable(); // Nombre del equipo
        //     $table->string('dominio')->nullable(); // Dominio (e.g., "Académico", "Administrativo")
        //     $table->text('descripcion_lugar')->nullable(); // Descripción del lugar
        //     $table->text('falla')->nullable(); // Descripción de fallas
        //     $table->string('contrato')->nullable(); // Número de contrato relacionado
        //     $table->boolean('absolute')->default(false); // Indicador de Absolute
        //     $table->boolean('hp_techpulse')->default(false); // Indicador de HP TechPulse
        //     $table->boolean('puc')->default(false); // Indicador de PUC
        //     $table->boolean('sophos')->default(false); // Indicador de Sophos
        //     $table->timestamps(); // Timestamps para created_at y updated_at
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
