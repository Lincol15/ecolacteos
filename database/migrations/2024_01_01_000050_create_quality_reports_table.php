<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('milk_delivery_id')->unique()->constrained('milk_deliveries')->onDelete('cascade');
            $table->foreignId('analyst_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('analyzer_model')->default('LACTOMAT');
            $table->string('sample_code', 50)->nullable();
            $table->decimal('grasa_pct', 5, 2)->nullable();
            $table->decimal('proteina_pct', 5, 2)->nullable();
            $table->decimal('lactosa_pct', 5, 2)->nullable();
            $table->decimal('solidos_no_grasos_pct', 5, 2)->nullable();
            $table->decimal('total_solidos_pct', 5, 2)->nullable();
            $table->decimal('agua_aniadida_pct', 5, 2)->default(0);
            $table->decimal('ph', 4, 2)->nullable();
            $table->decimal('punto_congelacion', 5, 3)->nullable();
            $table->integer('acidez_dornic')->nullable();
            $table->decimal('densidad', 6, 3)->nullable();
            $table->text('observations')->nullable();
            $table->enum('result', ['aprobado', 'rechazado', 'aceptable', 'observado'])->default('aprobado');
            $table->enum('rejection_reason', [
                'ninguno',
                'baja_grasa',
                'baja_proteina',
                'exceso_agua',
                'acidez_alta',
                'ph_anormal',
                'congelacion_anormal',
                'contaminacion'
            ])->default('ninguno');
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_reports');
    }
};
