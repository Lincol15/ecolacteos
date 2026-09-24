<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('id')->constrained('customers')->onDelete('set null');
        });

        DB::statement("ALTER TABLE sales MODIFY COLUMN sale_type ENUM('mostrador', 'delivery', 'mayorista', 'exportacion', 'pedido_web') NOT NULL DEFAULT 'mostrador'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE sales MODIFY COLUMN sale_type ENUM('mostrador', 'delivery', 'mayorista', 'exportacion') NOT NULL DEFAULT 'mostrador'");

        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
        });
    }
};
