<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quality_reports', function (Blueprint $table) {
            $table->foreignId('producer_id')->nullable()->after('milk_delivery_id')->constrained('producers')->onDelete('set null');
            $table->decimal('temperatura', 5, 2)->nullable()->after('producer_id');
            $table->enum('origen_datos', ['manual', 'ocr', 'ocr_corregido'])->default('manual')->after('analyzer_model');
        });

        DB::statement('
            UPDATE quality_reports qr
            INNER JOIN milk_deliveries md ON md.id = qr.milk_delivery_id
            SET qr.producer_id = md.producer_id, qr.temperatura = md.temperature
            WHERE qr.producer_id IS NULL
        ');

        DB::statement('ALTER TABLE quality_reports MODIFY milk_delivery_id BIGINT UNSIGNED NULL');
        DB::statement("ALTER TABLE quality_reports MODIFY result ENUM('aprobado','rechazado','aceptable','observado','pendiente') NOT NULL DEFAULT 'pendiente'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE quality_reports MODIFY result ENUM('aprobado','rechazado','aceptable','observado') NOT NULL DEFAULT 'aprobado'");
        DB::statement('ALTER TABLE quality_reports MODIFY milk_delivery_id BIGINT UNSIGNED NOT NULL');

        Schema::table('quality_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('producer_id');
            $table->dropColumn(['temperatura', 'origen_datos']);
        });
    }
};
