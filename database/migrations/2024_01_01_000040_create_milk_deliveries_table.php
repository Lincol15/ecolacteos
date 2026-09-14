<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('milk_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producer_id')->constrained('producers')->onDelete('cascade');
            $table->foreignId('collector_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('route_stop_id')->nullable()->constrained('route_stops')->onDelete('set null');
            $table->foreignId('collection_route_id')->nullable()->constrained('collection_routes')->onDelete('set null');
            $table->decimal('liters', 8, 2);
            $table->decimal('temperature', 5, 2)->nullable();
            $table->decimal('price_per_liter', 6, 2)->default(1.70);
            $table->decimal('total_amount', 10, 2);
            $table->date('delivery_date');
            $table->time('delivery_time')->nullable();
            $table->enum('container_type', ['caneca', 'bidon', 'cisterna'])->default('caneca');
            $table->integer('containers_count')->default(1);
            $table->string('vehicle_plate', 10)->nullable();
            $table->text('observations')->nullable();
            $table->boolean('has_quality_analysis')->default(false);
            $table->enum('status', ['registrado', 'aceptado', 'rechazado', 'analizado'])->default('registrado');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milk_deliveries');
    }
};
