<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\PlantConfig;
use App\Models\Producer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Calcular pago para un productor en un periodo
     */
    public function calculatePayment(Producer $producer, Carbon $start, Carbon $end): array
    {
        $deliveries = $producer->milkDeliveries()
            ->whereBetween('delivery_date', [$start, $end])
            ->where('status', '!=', 'rechazado')
            ->get();

        if ($deliveries->isEmpty()) {
            return [
                'can_generate' => false,
                'reason' => 'No hay entregas en el periodo',
            ];
        }

        $liters = $deliveries->sum('liters');
        $base = $deliveries->sum('total_amount');

        // Calcular bonos
        $qualityBonus = $this->calculateQualityBonus($producer, $start, $end, $base);
        $volumeBonus = $this->calculateVolumeBonus($liters, $base);

        // Calcular deducciones
        $deductions = $this->calculateDeductions($base);
        $llevadoAPlanta = $this->calculateLlevadoAPlanta($deliveries);

        $total = round($base + $qualityBonus + $volumeBonus - $deductions - $llevadoAPlanta, 2);

        return [
            'can_generate' => true,
            'producer_id' => $producer->id,
            'period_start' => $start,
            'period_end' => $end,
            'total_liters' => $liters,
            'detalle_diario' => $this->buildDetalleDiario($deliveries),
            'avg_price_per_liter' => $liters > 0 ? round($base / $liters, 2) : 0,
            'precio_por_litro' => (float) PlantConfig::getValue('precio_litro_leche', 1.70),
            'base_amount' => $base,
            'quality_bonus' => $qualityBonus,
            'production_bonus' => $volumeBonus,
            'deductions' => $deductions,
            'llevado_a_planta' => $llevadoAPlanta,
            'total_amount' => $total,
            'deliveries' => $deliveries,
        ];
    }

    /**
     * Generar pago para un productor
     */
    public function generatePayment(array $paymentData, int $processedBy): Payment
    {
        return DB::transaction(function () use ($paymentData, $processedBy) {
            // Generar código de periodo
            $periodCode = 'SEM-'.Carbon::parse($paymentData['period_start'])->format('Y-W');

            // Crear pago
            $payment = Payment::create([
                'producer_id' => $paymentData['producer_id'],
                'period_code' => $periodCode,
                'period_start' => $paymentData['period_start'],
                'period_end' => $paymentData['period_end'],
                'payment_date' => Carbon::parse($paymentData['period_end'])->addDay(),
                'total_liters' => $paymentData['total_liters'],
                'detalle_diario' => $paymentData['detalle_diario'] ?? null,
                'avg_price_per_liter' => $paymentData['avg_price_per_liter'],
                'precio_por_litro' => $paymentData['precio_por_litro'] ?? $paymentData['avg_price_per_liter'],
                'base_amount' => $paymentData['base_amount'],
                'quality_bonus' => $paymentData['quality_bonus'],
                'production_bonus' => $paymentData['production_bonus'],
                'deductions' => $paymentData['deductions'],
                'llevado_a_planta' => $paymentData['llevado_a_planta'] ?? 0,
                'total_amount' => $paymentData['total_amount'],
                'deductions_detail' => $this->getDeductionsDetail($paymentData['deductions']),
                'bonus_detail' => $this->getBonusDetail($paymentData['quality_bonus'], $paymentData['production_bonus']),
                'payment_method' => 'transferencia',
                'status' => 'pendiente',
                'processed_by' => $processedBy,
                'notes' => 'Liquidación generada automáticamente',
            ]);

            // Crear items de pago
            foreach ($paymentData['deliveries'] as $delivery) {
                PaymentItem::create([
                    'payment_id' => $payment->id,
                    'milk_delivery_id' => $delivery->id,
                    'liters' => $delivery->liters,
                    'price_per_liter' => $delivery->price_per_liter,
                    'line_amount' => $delivery->total_amount,
                ]);
            }

            return $payment->fresh(['items', 'producer.user']);
        });
    }

    /**
     * Procesar pago (marcar como pagado)
     */
    public function processPayment(Payment $payment, array $data): Payment
    {
        return DB::transaction(function () use ($payment, $data) {
            $payment->update([
                'status' => 'pagado',
                'payment_method' => $data['payment_method'] ?? $payment->payment_method,
                'transaction_number' => $data['transaction_number'] ?? 'TXN-'.strtoupper(uniqid()),
                'payment_date' => $data['payment_date'] ?? now(),
                'notes' => $data['notes'] ?? $payment->notes,
            ]);

            return $payment->fresh();
        });
    }

    /**
     * Calcular bono de calidad
     */
    protected function calculateQualityBonus(Producer $producer, Carbon $start, Carbon $end, float $base): float
    {
        $qualityReportsCount = $producer->qualityReports()
            ->whereHas('milkDelivery', fn ($q) => $q->whereBetween('delivery_date', [$start, $end]))
            ->where('result', 'aprobado')
            ->count();

        $totalDeliveries = $producer->milkDeliveries()
            ->whereBetween('delivery_date', [$start, $end])
            ->where('status', '!=', 'rechazado')
            ->count();

        // Bono del 5% si el porcentaje de entregas aprobadas alcanza el mínimo configurado.
        $minApprovedRatio = (float) PlantConfig::getValue('bono_calidad_minimo_score', 90) / 100;
        if ($totalDeliveries > 0 && ($qualityReportsCount / $totalDeliveries) >= $minApprovedRatio) {
            return round($base * 0.05, 2);
        }

        return 0;
    }

    /**
     * Calcular bono de volumen
     */
    protected function calculateVolumeBonus(float $liters, float $base): float
    {
        // Bono del 3% si supera los litros mínimos configurados en el período.
        if ($liters > (float) PlantConfig::getValue('bono_volumen_minimo_litros', 500)) {
            return round($base * 0.03, 2);
        }

        return 0;
    }

    /**
     * Calcular deducciones
     */
    protected function calculateDeductions(float $base): float
    {
        // Deducción del 1% para fondo solidario
        return round($base * 0.01, 2);
    }

    /**
     * Litros que el productor llevó directamente a planta (sin pasar por acopiador),
     * y que por tanto se descuentan del monto a pagar en la liquidación.
     */
    protected function calculateLlevadoAPlanta($deliveries): float
    {
        return round(
            $deliveries->whereNull('collector_id')->sum('total_amount'),
            2
        );
    }

    /**
     * Desglose de litros entregados por día de la semana dentro del periodo.
     */
    protected function buildDetalleDiario($deliveries): array
    {
        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        $detalle = array_fill_keys($dias, 0.0);

        foreach ($deliveries as $delivery) {
            $isoWeekday = Carbon::parse($delivery->delivery_date)->dayOfWeekIso; // 1=Lunes ... 7=Domingo
            $detalle[$dias[$isoWeekday - 1]] += (float) $delivery->liters;
        }

        return array_map(fn ($v) => round($v, 2), $detalle);
    }

    /**
     * Obtener detalle de deducciones
     */
    protected function getDeductionsDetail(float $deductions): ?string
    {
        if ($deductions > 0) {
            return "Aporte fondo solidario 1% (S/ {$deductions})";
        }

        return null;
    }

    /**
     * Obtener detalle de bonos
     */
    protected function getBonusDetail(float $qualityBonus, float $volumeBonus): ?string
    {
        $details = [];

        if ($qualityBonus > 0) {
            $details[] = "Bono calidad S/ {$qualityBonus}";
        }

        if ($volumeBonus > 0) {
            $details[] = "Bono volumen S/ {$volumeBonus}";
        }

        return ! empty($details) ? implode(' | ', $details) : null;
    }
}
