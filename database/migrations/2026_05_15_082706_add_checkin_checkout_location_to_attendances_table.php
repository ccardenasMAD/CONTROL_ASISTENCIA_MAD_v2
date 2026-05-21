use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1 Si existen latitude / longitude, los eliminamos en bloques separados
        if (Schema::hasColumn('attendances', 'latitude')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropColumn('latitude');
            });
        }

        if (Schema::hasColumn('attendances', 'longitude')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropColumn('longitude');
            });
        }

        // 2 Agregamos los nuevos campos (solo si aún no existen)
        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'check_in_lat')) {
                $table->decimal('check_in_lat', 10, 7)->nullable();
            }
            if (! Schema::hasColumn('attendances', 'check_in_lng')) {
                $table->decimal('check_in_lng', 10, 7)->nullable();
            }
            if (! Schema::hasColumn('attendances', 'check_out_lat')) {
                $table->decimal('check_out_lat', 10, 7)->nullable();
            }
            if (! Schema::hasColumn('attendances', 'check_out_lng')) {
                $table->decimal('check_out_lng', 10, 7)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'check_in_lat',
                'check_in_lng',
                'check_out_lat',
                'check_out_lng',
            ]);

            // Restauramos los antiguos si haces rollback
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
        });
    }
};
