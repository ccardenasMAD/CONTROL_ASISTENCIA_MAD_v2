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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            // Usuario que registra la asistencia
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Grupo al momento del marcaje (MUY importante)
            $table->foreignId('group_id')
                ->constrained()
                ->cascadeOnDelete();

            // Fecha lógica de la asistencia
            $table->date('attendance_date');

            // Marcajes
            $table->timestamp('check_in')->nullable();
            $table->timestamp('check_out')->nullable();

            // Origen del registro
            $table->enum('source', [
                'manual',
                'mobile',
                'biometric',
                'system',
            ])->default('system');

            // Estado calculado (se llenará luego)
            $table->enum('status', [
                'present',
                'absent',
                'late',
                'early_exit',
                'incomplete',
            ])->nullable();

            $table->timestamps();

            $table->unique(
                ['user_id', 'attendance_date'],
                'user_attendance_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
