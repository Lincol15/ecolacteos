<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Índices para milk_deliveries (búsquedas muy frecuentes)
        Schema::table('milk_deliveries', function (Blueprint $table) {
            $table->index('delivery_date'); // Búsquedas por fecha
            $table->index('status'); // Filtros por estado
            $table->index(['producer_id', 'delivery_date']); // Consultas por productor y fecha
            $table->index(['collector_id', 'delivery_date']); // Consultas por acopiador y fecha
        });

        // Índices para quality_reports
        Schema::table('quality_reports', function (Blueprint $table) {
            $table->index('analyzed_at'); // Búsquedas por fecha de análisis
            $table->index('result'); // Filtros por resultado
            $table->index(['result', 'analyzed_at']); // Combinación frecuente
        });

        // Índices para payments
        Schema::table('payments', function (Blueprint $table) {
            $table->index('period_end'); // Ordenamiento por periodo
            $table->index('status'); // Filtros por estado
            $table->index(['producer_id', 'status']); // Consultas combinadas
            $table->index('payment_date'); // Búsquedas por fecha de pago
        });

        // Índices para producers
        Schema::table('producers', function (Blueprint $table) {
            $table->index('status'); // Filtros por estado
            $table->index('zone'); // Búsquedas por zona
            $table->index(['status', 'zone']); // Combinación frecuente
        });

        // Índices para users
        Schema::table('users', function (Blueprint $table) {
            $table->index('role'); // Filtros por rol
            $table->index('active'); // Filtros por estado activo
            $table->index(['role', 'active']); // Combinación frecuente
        });

        // Índices para production_batches
        Schema::table('production_batches', function (Blueprint $table) {
            $table->index('production_date'); // Búsquedas por fecha
            $table->index('status'); // Filtros por estado
            $table->index(['product_id', 'status']); // Consultas por producto y estado
        });

        // Índices para inventories
        Schema::table('inventories', function (Blueprint $table) {
            $table->index('movement_type'); // Filtros por tipo de movimiento
            $table->index(['product_id', 'movement_type']); // Consultas de stock
            $table->index('created_at'); // Ordenamiento por fecha de movimiento
        });

        // Índices para sales
        Schema::table('sales', function (Blueprint $table) {
            $table->index('sale_date'); // Búsquedas por fecha
            $table->index('payment_status'); // Filtros por estado de pago
            $table->index(['sale_date', 'payment_status']); // Combinación frecuente
        });

        // Índices para complaints
        Schema::table('complaints', function (Blueprint $table) {
            $table->index('status'); // Filtros por estado
            $table->index('priority'); // Filtros por prioridad
            $table->index('created_at'); // Ordenamiento por fecha
        });

        // Índices para collection_routes
        Schema::table('collection_routes', function (Blueprint $table) {
            $table->index('day'); // Filtros por día
            $table->index('status'); // Filtros por estado
            $table->index(['collector_id', 'status']); // Consultas por acopiador
        });

        // Índices para route_stops
        Schema::table('route_stops', function (Blueprint $table) {
            $table->index('status'); // Filtros por estado
            $table->index(['collection_route_id', 'stop_order']); // Ordenamiento de paradas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar índices en orden inverso
        Schema::table('route_stops', function (Blueprint $table) {
            $table->dropIndex(['route_stops_status_index']);
            $table->dropIndex(['route_stops_collection_route_id_stop_order_index']);
        });

        Schema::table('collection_routes', function (Blueprint $table) {
            $table->dropIndex(['collection_routes_day_index']);
            $table->dropIndex(['collection_routes_status_index']);
            $table->dropIndex(['collection_routes_collector_id_status_index']);
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->dropIndex(['complaints_status_index']);
            $table->dropIndex(['complaints_priority_index']);
            $table->dropIndex(['complaints_created_at_index']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['sales_sale_date_index']);
            $table->dropIndex(['sales_payment_status_index']);
            $table->dropIndex(['sales_sale_date_payment_status_index']);
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropIndex(['inventories_movement_type_index']);
            $table->dropIndex(['inventories_product_id_movement_type_index']);
            $table->dropIndex(['inventories_created_at_index']);
        });

        Schema::table('production_batches', function (Blueprint $table) {
            $table->dropIndex(['production_batches_production_date_index']);
            $table->dropIndex(['production_batches_status_index']);
            $table->dropIndex(['production_batches_product_id_status_index']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['users_role_index']);
            $table->dropIndex(['users_active_index']);
            $table->dropIndex(['users_role_active_index']);
        });

        Schema::table('producers', function (Blueprint $table) {
            $table->dropIndex(['producers_status_index']);
            $table->dropIndex(['producers_zone_index']);
            $table->dropIndex(['producers_status_zone_index']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['payments_period_end_index']);
            $table->dropIndex(['payments_status_index']);
            $table->dropIndex(['payments_producer_id_status_index']);
            $table->dropIndex(['payments_payment_date_index']);
        });

        Schema::table('quality_reports', function (Blueprint $table) {
            $table->dropIndex(['quality_reports_analyzed_at_index']);
            $table->dropIndex(['quality_reports_result_index']);
            $table->dropIndex(['quality_reports_result_analyzed_at_index']);
        });

        Schema::table('milk_deliveries', function (Blueprint $table) {
            $table->dropIndex(['milk_deliveries_delivery_date_index']);
            $table->dropIndex(['milk_deliveries_status_index']);
            $table->dropIndex(['milk_deliveries_producer_id_delivery_date_index']);
            $table->dropIndex(['milk_deliveries_collector_id_delivery_date_index']);
        });
    }
};
