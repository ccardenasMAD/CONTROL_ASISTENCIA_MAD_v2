<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            // Minutos de tolerancia (llegada tarde / salida anticipada)
            $table->unsignedInteger('grace_minutes')->default(0);
            // Horas mínimas requeridas para marcar asistencia válida
            $table->decimal('min_work_hours', 5, 2)->nullable();
            // Método de cálculo de asistencia
            $table->enum('calculation_type', [
                'daily',
                'shift'
            ])->default('shift');
            // Marca turnos nocturnos (cruce de día)
            $table->boolean('is_night_shift')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
