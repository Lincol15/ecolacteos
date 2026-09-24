<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Planilla mensual de acopiadores: un pago por acopiador y mes.
     */
    public function up(): void
    {
        Schema::create('collector_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collector_id')->constrained('users')->cascadeOnDelete();
            $table->string('period_code', 30);
            $table->date('period_month');
            $table->decimal('base_salary', 10, 2);
            $table->decimal('bonus', 10, 2)->default(0);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('liters_collected', 12, 2)->default(0);
            $table->unsignedInteger('deliveries_count')->default(0);
            $table->string('status', 20)->default('pendiente');
            $table->string('payment_method', 20)->nullable();
            $table->string('transaction_number', 50)->nullable();
            $table->date('payment_date')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['collector_id', 'period_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collector_payments');
    }
};
