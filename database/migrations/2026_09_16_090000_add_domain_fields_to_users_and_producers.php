<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('comunidad')->nullable()->after('address');
            $table->string('vehiculo', 20)->nullable()->after('comunidad');
        });

        Schema::table('producers', function (Blueprint $table) {
            $table->string('comunidad')->nullable()->after('zone');
        });

        DB::statement('UPDATE producers SET comunidad = zone WHERE comunidad IS NULL');
        DB::statement("UPDATE producers SET status = 'inactivo' WHERE status = 'suspendido'");
        DB::statement("ALTER TABLE producers MODIFY status ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE producers MODIFY status ENUM('activo', 'inactivo', 'suspendido') NOT NULL DEFAULT 'activo'");

        Schema::table('producers', function (Blueprint $table) {
            $table->dropColumn('comunidad');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['comunidad', 'vehiculo']);
        });
    }
};
