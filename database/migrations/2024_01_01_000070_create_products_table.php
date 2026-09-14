<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug')->unique();
            $table->string('sku', 30)->unique();
            $table->enum('category', ['queso', 'yogur', 'mantequilla', 'leche', 'otro']);
            $table->text('description')->nullable();
            $table->text('long_description')->nullable();
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->string('unit', 20)->default('kg');
            $table->string('image_url')->nullable();
            $table->string('emoji', 20)->nullable();
            $table->json('specifications')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('show_in_catalog')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
