<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_batch_id')->nullable()->constrained('production_batches')->onDelete('set null');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('quantity', 12, 2);
            $table->string('unit', 20);
            $table->enum('movement_type', ['entrada', 'salida', 'ajuste', 'devolucion', 'merma']);
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->decimal('total_value', 12, 2)->default(0);
            $table->string('location', 100)->default('planta_principal');
            $table->date('expiration_date')->nullable();
            $table->string('reference_document', 50)->nullable();
            $table->foreignId('related_sale_id')->nullable()->constrained('sales')->onDelete('set null');
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 30)->unique()->nullable();
            $table->string('client_name');
            $table->string('client_dni_ruc', 20)->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_phone', 20)->nullable();
            $table->text('client_address')->nullable();
            $table->date('sale_date');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->enum('payment_status', ['pendiente', 'pagado', 'parcial', 'anulado'])->default('pendiente');
            $table->enum('payment_method', ['efectivo', 'transferencia', 'yape', 'plin', 'cheque', 'tarjeta'])->default('efectivo');
            $table->enum('sale_type', ['mostrador', 'delivery', 'mayorista', 'exportacion'])->default('mostrador');
            $table->foreignId('served_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('production_batch_id')->nullable()->constrained('production_batches')->onDelete('set null');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('sales');
    }
};
