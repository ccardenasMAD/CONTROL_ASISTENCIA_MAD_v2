<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_group', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shift_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('group_id')
                ->constrained()
                ->cascadeOnDelete();

            // Vigencia del turno para el grupo
            $table->date('start_date');
            $table->date('end_date')->nullable();

            // Activo / inactivo sin perder historial
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(
                ['shift_id', 'group_id', 'start_date'],
                'shift_group_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_group');
    }
};


