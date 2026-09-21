<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('precio_por_litro', 6, 2)->nullable()->after('avg_price_per_liter');
            $table->json('detalle_diario')->nullable()->after('total_liters');
            $table->decimal('llevado_a_planta', 10, 2)->default(0)->after('deductions');
        });

        DB::statement('UPDATE payments SET precio_por_litro = avg_price_per_liter WHERE precio_por_litro IS NULL');
        DB::statement("ALTER TABLE payments MODIFY status ENUM('pendiente','procesando','pagado','parcial','rechazado') NOT NULL DEFAULT 'pendiente'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY status ENUM('pendiente','procesando','pagado','rechazado') NOT NULL DEFAULT 'pendiente'");

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['precio_por_litro', 'detalle_diario', 'llevado_a_planta']);
        });
    }
};
