<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('code', 20)->unique();
            $table->string('farm_name')->nullable();
            $table->string('zone')->nullable();
            $table->string('district')->nullable();
            $table->string('province')->default('Huata');
            $table->string('region')->default('Ancash');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('cows_count')->default(0);
            $table->decimal('daily_avg_liters', 8, 2)->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['activo', 'inactivo', 'suspendido'])->default('activo');
            $table->date('registration_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producers');
    }
};
