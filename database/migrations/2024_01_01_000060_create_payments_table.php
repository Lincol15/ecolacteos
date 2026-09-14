<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producer_id')->constrained('producers')->onDelete('cascade');
            $table->string('period_code', 30);
            $table->date('period_start');
            $table->date('period_end');
            $table->date('payment_date')->nullable();
            $table->decimal('total_liters', 10, 2)->default(0);
            $table->decimal('avg_price_per_liter', 6, 2)->default(1.70);
            $table->decimal('base_amount', 12, 2)->default(0);
            $table->decimal('quality_bonus', 10, 2)->default(0);
            $table->decimal('production_bonus', 10, 2)->default(0);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->text('deductions_detail')->nullable();
            $table->text('bonus_detail')->nullable();
            $table->enum('payment_method', ['transferencia', 'efectivo', 'cheque'])->default('transferencia');
            $table->string('transaction_number', 50)->nullable();
            $table->enum('status', ['pendiente', 'procesando', 'pagado', 'rechazado'])->default('pendiente');
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->foreignId('milk_delivery_id')->constrained('milk_deliveries')->onDelete('cascade');
            $table->decimal('liters', 8, 2);
            $table->decimal('price_per_liter', 6, 2);
            $table->decimal('line_amount', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_items');
        Schema::dropIfExists('payments');
    }
};
