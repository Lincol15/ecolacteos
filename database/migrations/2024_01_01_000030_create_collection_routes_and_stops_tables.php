<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->enum('day', ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'])->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('vehicle_plate', 10)->nullable();
            $table->decimal('estimated_distance_km', 8, 2)->nullable();
            $table->foreignId('collector_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['planeada', 'en_curso', 'completada', 'cancelada'])->default('planeada');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('route_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_route_id')->constrained('collection_routes')->onDelete('cascade');
            $table->foreignId('producer_id')->constrained('producers')->onDelete('cascade');
            $table->integer('stop_order')->default(1);
            $table->time('estimated_arrival')->nullable();
            $table->decimal('estimated_liters', 8, 2)->default(0);
            $table->text('special_instructions')->nullable();
            $table->enum('status', ['pendiente', 'en_camino', 'visitado', 'ausente', 'rechazado'])->default('pendiente');
            $table->timestamp('visited_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_stops');
        Schema::dropIfExists('collection_routes');
    }
};
