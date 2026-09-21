<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('milk_deliveries', function (Blueprint $table) {
            $table->string('zona')->nullable()->after('producer_id');
            $table->boolean('recibido')->default(false)->after('status');
        });

        DB::statement('
            UPDATE milk_deliveries md
            INNER JOIN producers p ON p.id = md.producer_id
            SET md.zona = p.zone
            WHERE md.zona IS NULL
        ');

        DB::statement("UPDATE milk_deliveries SET recibido = 1 WHERE status <> 'registrado'");
    }

    public function down(): void
    {
        Schema::table('milk_deliveries', function (Blueprint $table) {
            $table->dropColumn(['zona', 'recibido']);
        });
    }
};
