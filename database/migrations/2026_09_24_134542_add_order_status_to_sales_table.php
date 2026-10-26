<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Estado de entrega de los pedidos web (nuevo → confirmado → en camino → entregado / cancelado).
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('order_status', 20)->nullable()->after('payment_status');
        });

        DB::table('sales')->where('sale_type', 'pedido_web')->update([
            'order_status' => DB::raw("CASE payment_status WHEN 'pagado' THEN 'entregado' WHEN 'anulado' THEN 'cancelado' ELSE 'nuevo' END"),
        ]);
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('order_status');
        });
    }
};
