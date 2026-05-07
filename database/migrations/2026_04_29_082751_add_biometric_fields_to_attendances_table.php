<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * definimos qué columnas AGREGAR.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Coordenadas GPS: Usamos decimal para máxima precisión en mapas.
          
            $table->decimal('latitude', 10, 8)->nullable()->after('check_out');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');

            // Ruta de la foto: Guardamos el nombre del archivo, no la imagen real.
            $table->string('photo_path')->nullable()->after('longitude');

            // IP: Para saber desde qué red se conectaron.
            $table->string('ip_address')->nullable()->after('photo_path');
        });
    }

    /**
     * Reverse the migrations.
     *  definimos qué hacer si queremos "deshacer" el cambio.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Si echamos para atrás la migración, borramos las columnas.
            $table->dropColumn(['latitude', 'longitude', 'photo_path', 'ip_address']);
        });
    }
};