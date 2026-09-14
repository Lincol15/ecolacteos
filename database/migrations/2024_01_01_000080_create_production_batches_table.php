<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number', 30)->unique();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('input_milk_liters', 10, 2)->default(0);
            $table->decimal('output_units', 10, 2)->default(0);
            $table->decimal('yield_percentage', 5, 2)->default(0);
            $table->date('production_date');
            $table->date('expiration_date')->nullable();
            $table->foreignId('supervised_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('recipe_notes')->nullable();
            $table->text('quality_notes')->nullable();
            $table->enum('status', ['planeado', 'en_proceso', 'curando', 'terminado', 'vendido', 'desperdicio'])->default('planeado');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('batch_milk_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_batch_id')->constrained('production_batches')->onDelete('cascade');
            $table->foreignId('milk_delivery_id')->constrained('milk_deliveries')->onDelete('cascade');
            $table->decimal('liters_used', 8, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_milk_usage');
        Schema::dropIfExists('production_batches');
    }
};
