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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('time');
            $table->enum('type', ['in', 'out']);
            $table->enum('source', ['web','mobile','biometric','system','manual',])->default('system');
            $table->foreignId('edited_by')->nullable()->constrained('users');
            $table->enum('status', ['normal','late','early_exit','incomplete',])->nullable();
            $table->timestamps();
            $table->index(['user_id', 'date']);
            $table->index(['group_id', 'date']);
            $table->index(['type']);
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