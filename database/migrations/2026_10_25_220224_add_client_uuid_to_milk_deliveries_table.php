<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Identificador que genera la app móvil al registrar una recolección sin conexión:
     * si la sincronización se reintenta, el servidor reconoce la entrega y no la duplica.
     */
    public function up(): void
    {
        Schema::table('milk_deliveries', function (Blueprint $table) {
            $table->uuid('client_uuid')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('milk_deliveries', function (Blueprint $table) {
            $table->dropUnique(['client_uuid']);
            $table->dropColumn('client_uuid');
        });
    }
};
