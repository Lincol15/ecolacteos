<?php

namespace App\Services;

use App\Models\MilkDelivery;
use App\Models\PlantConfig;
use App\Models\Producer;
use App\Models\RouteStop;
use Illuminate\Support\Facades\DB;

class MilkDeliveryService
{
    /**
     * Crear una nueva entrega de leche
     */
    public function create(array $data, int $collectorId): MilkDelivery
    {
        return DB::transaction(function () use ($data, $collectorId) {
            // Obtener precio configurado si no se proporciona
            $price = $data['price_per_liter'] ?? PlantConfig::getValue('precio_litro_leche', 1.70);

            // La zona de la entrega es siempre la comunidad del productor (no se pide manualmente).
            $zona = Producer::where('id', $data['producer_id'])->value('comunidad');

            // Crear la entrega
            $delivery = MilkDelivery::create([
                ...$data,
                'collector_id' => $collectorId,
                'zona' => $zona,
                'price_per_liter' => $price,
                'total_amount' => round($data['liters'] * $price, 2),
                'delivery_time' => now(),
                'has_quality_analysis' => false,
                'status' => 'registrado',
            ]);

            // Actualizar estado de parada si existe
            if (isset($data['route_stop_id'])) {
                RouteStop::where('id', $data['route_stop_id'])->update([
                    'status' => 'visitado',
                    'visited_at' => now(),
                ]);
            }

            return $delivery;
        });
    }

    /**
     * Actualizar una entrega de leche
     */
    public function update(MilkDelivery $delivery, array $data): MilkDelivery
    {
        return DB::transaction(function () use ($delivery, $data) {
            // Recalcular total_amount si cambian litros o precio
            if (isset($data['liters']) || isset($data['price_per_liter'])) {
                $liters = $data['liters'] ?? $delivery->liters;
                $price = $data['price_per_liter'] ?? $delivery->price_per_liter;
                $data['total_amount'] = round($liters * $price, 2);
            }

            $delivery->update($data);

            return $delivery->fresh();
        });
    }

    /**
     * Aprobar una entrega
     */
    public function approve(MilkDelivery $delivery, int $approverId): MilkDelivery
    {
        return DB::transaction(function () use ($delivery, $approverId) {
            $delivery->update([
                'status' => 'aceptado',
                'approved_by' => $approverId,
                'approved_at' => now(),
            ]);

            return $delivery->fresh();
        });
    }

    /**
     * Rechazar una entrega
     */
    public function reject(MilkDelivery $delivery, string $reason, int $approverId): MilkDelivery
    {
        return DB::transaction(function () use ($delivery, $reason, $approverId) {
            $delivery->update([
                'status' => 'rechazado',
                'observations' => ($delivery->observations ? $delivery->observations.'\n\n' : '')
                    .'RECHAZADO: '.$reason,
                'approved_by' => $approverId,
                'approved_at' => now(),
            ]);

            // Si tiene parada asociada, actualizar su estado
            if ($delivery->route_stop_id) {
                RouteStop::where('id', $delivery->route_stop_id)->update([
                    'status' => 'rechazado',
                ]);
            }

            return $delivery->fresh();
        });
    }

    /**
     * Calcular estadísticas de entregas para un productor en un periodo
     */
    public function getProducerStats(int $producerId, string $startDate, string $endDate): array
    {
        $deliveries = MilkDelivery::where('producer_id', $producerId)
            ->whereBetween('delivery_date', [$startDate, $endDate])
            ->where('status', '!=', 'rechazado')
            ->get();

        return [
            'total_deliveries' => $deliveries->count(),
            'total_liters' => $deliveries->sum('liters'),
            'total_amount' => $deliveries->sum('total_amount'),
            'avg_liters' => $deliveries->avg('liters'),
            'avg_temperature' => $deliveries->avg('temperature'),
            'min_temperature' => $deliveries->min('temperature'),
            'max_temperature' => $deliveries->max('temperature'),
        ];
    }
}
