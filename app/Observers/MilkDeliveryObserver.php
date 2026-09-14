<?php

namespace App\Observers;

use App\Models\MilkDelivery;
use App\Models\PlantConfig;

class MilkDeliveryObserver
{
    /**
     * Handle the MilkDelivery "creating" event.
     */
    public function creating(MilkDelivery $delivery): void
    {
        // Calcular total_amount automáticamente si no está establecido
        if (!isset($delivery->total_amount) || $delivery->total_amount === 0) {
            $price = $delivery->price_per_liter ?? PlantConfig::getValue('precio_litro_leche', 1.70);
            $delivery->total_amount = round($delivery->liters * $price, 2);
        }

        // Validar temperatura si está presente
        if (isset($delivery->temperature)) {
            if ($delivery->temperature < 2 || $delivery->temperature > 10) {
                throw new \Exception('Temperatura fuera de rango permitido (2-10°C). Actual: ' . $delivery->temperature . '°C');
            }
        }

        // Validar que los litros sean positivos
        if ($delivery->liters <= 0) {
            throw new \Exception('La cantidad de litros debe ser mayor a 0');
        }

        // Establecer delivery_time si no está presente
        if (!isset($delivery->delivery_time)) {
            $delivery->delivery_time = now();
        }
    }

    /**
     * Handle the MilkDelivery "updating" event.
     */
    public function updating(MilkDelivery $delivery): void
    {
        // Recalcular total_amount si cambian litros o precio
        if ($delivery->isDirty(['liters', 'price_per_liter'])) {
            $delivery->total_amount = round($delivery->liters * $delivery->price_per_liter, 2);
        }

        // Validar temperatura si cambia
        if ($delivery->isDirty('temperature') && isset($delivery->temperature)) {
            if ($delivery->temperature < 2 || $delivery->temperature > 10) {
                throw new \Exception('Temperatura fuera de rango permitido (2-10°C)');
            }
        }
    }

    /**
     * Handle the MilkDelivery "created" event.
     */
    public function created(MilkDelivery $delivery): void
    {
        // Aquí se pueden disparar eventos, notificaciones, etc.
        // Por ejemplo: event(new MilkDeliveryCreated($delivery));
    }

    /**
     * Handle the MilkDelivery "deleting" event.
     */
    public function deleting(MilkDelivery $delivery): void
    {
        // Verificar que no tenga un reporte de calidad antes de eliminar
        if ($delivery->qualityReport()->exists()) {
            throw new \Exception('No se puede eliminar una entrega que ya tiene análisis de calidad');
        }

        // Verificar que no esté incluida en un pago
        if ($delivery->paymentItems()->exists()) {
            throw new \Exception('No se puede eliminar una entrega que ya está incluida en un pago');
        }
    }
}
