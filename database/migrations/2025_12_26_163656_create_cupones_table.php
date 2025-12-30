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
        Schema::create('cupones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique()->comment('Código del cupón (ej: VERANO2025)');
            $table->string('nombre')->comment('Nombre descriptivo del cupón');
            $table->text('descripcion')->nullable()->comment('Descripción del cupón');
            $table->enum('tipo', ['porcentaje', 'fijo'])->default('porcentaje')->comment('Tipo de descuento: porcentaje o fijo');
            $table->decimal('valor', 10, 2)->comment('Valor del descuento (porcentaje o cantidad fija)');
            $table->decimal('descuento_maximo', 10, 2)->nullable()->comment('Descuento máximo si es porcentaje');
            $table->decimal('importe_minimo', 10, 2)->nullable()->comment('Importe mínimo de reserva para aplicar el cupón');
            $table->date('fecha_inicio')->comment('Fecha de inicio de validez');
            $table->date('fecha_fin')->comment('Fecha de fin de validez');
            $table->integer('usos_maximos')->nullable()->comment('Número máximo de usos (null = ilimitado)');
            $table->integer('usos_actuales')->default(0)->comment('Número de usos actuales');
            $table->integer('usos_por_cliente')->default(1)->comment('Número de usos permitidos por cliente');
            $table->boolean('activo')->default(true)->comment('Si el cupón está activo');
            $table->json('apartamentos_ids')->nullable()->comment('IDs de apartamentos donde aplica (null = todos)');
            $table->json('restricciones')->nullable()->comment('Restricciones adicionales (JSON)');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('codigo');
            $table->index('activo');
            $table->index(['fecha_inicio', 'fecha_fin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cupones');
    }
};
