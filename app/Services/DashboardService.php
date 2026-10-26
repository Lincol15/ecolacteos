<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\MilkDelivery;
use App\Models\PlantConfig;
use App\Models\Producer;
use App\Models\ProductionBatch;
use App\Models\Sale;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Indicadores del panel de gestión (web y app móvil).
     *
     * @return array<string, float|int>
     */
    public function stats(Carbon $today): array
    {
        $startMonth = $today->copy()->startOfMonth();

        return [
            'liters_today' => MilkDelivery::where('delivery_date', $today->toDateString())->sum('liters'),
            'liters_month' => MilkDelivery::whereBetween('delivery_date', [$startMonth, $today])->sum('liters'),
            'producers_active' => Producer::where('status', 'activo')->count(),
            'batches_active' => ProductionBatch::whereIn('status', ['en_proceso', 'curando'])->count(),
            'sales_today' => Sale::where('sale_date', $today->toDateString())->sum('total_amount'),
            'sales_month' => Sale::whereBetween('sale_date', [$startMonth, $today])->sum('total_amount'),
            'complaints_open' => Complaint::whereIn('status', ['abierto', 'en_revision'])->count(),
            'milk_price' => PlantConfig::getValue('precio_litro_leche', 1.70),
            'deliveries_count' => MilkDelivery::where('delivery_date', $today->toDateString())->count(),
            'deliveries_received' => MilkDelivery::where('delivery_date', $today->toDateString())->where('recibido', true)->count(),
            'deliveries_in_transit' => MilkDelivery::where('delivery_date', $today->toDateString())->where('recibido', false)->count(),
        ];
    }
}
