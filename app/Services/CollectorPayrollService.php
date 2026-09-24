<?php

namespace App\Services;

use App\Models\CollectorPayment;
use App\Models\MilkDelivery;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Planilla mensual de acopiadores: reciben un sueldo fijo al mes, no un pago por litro.
 */
class CollectorPayrollService
{
    /**
     * Genera el pago del mes para cada acopiador activo con sueldo definido que aún no lo tenga.
     *
     * @return array{generated: int, skipped_without_salary: int}
     */
    public function generateForMonth(Carbon $month, int $processedBy): array
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $collectors = User::where('role', 'acopiador')->where('active', true)->get();
        $generated = 0;
        $skippedWithoutSalary = 0;

        DB::transaction(function () use ($collectors, $start, $end, $processedBy, &$generated, &$skippedWithoutSalary) {
            foreach ($collectors as $collector) {
                if ((float) $collector->monthly_salary <= 0) {
                    $skippedWithoutSalary++;

                    continue;
                }

                $alreadyExists = CollectorPayment::where('collector_id', $collector->id)
                    ->whereDate('period_month', $start)
                    ->exists();
                if ($alreadyExists) {
                    continue;
                }

                $deliveries = MilkDelivery::where('collector_id', $collector->id)
                    ->whereBetween('delivery_date', [$start->toDateString(), $end->toDateString()])
                    ->where('status', '!=', 'rechazado');

                CollectorPayment::create([
                    'collector_id' => $collector->id,
                    'period_code' => 'MES-'.$start->format('Y-m'),
                    'period_month' => $start->toDateString(),
                    'base_salary' => $collector->monthly_salary,
                    'bonus' => 0,
                    'deductions' => 0,
                    'total_amount' => $collector->monthly_salary,
                    'liters_collected' => (clone $deliveries)->sum('liters'),
                    'deliveries_count' => (clone $deliveries)->count(),
                    'status' => 'pendiente',
                    'processed_by' => $processedBy,
                    'notes' => 'Sueldo mensual generado desde planilla',
                ]);
                $generated++;
            }
        });

        return ['generated' => $generated, 'skipped_without_salary' => $skippedWithoutSalary];
    }

    /**
     * @param  array{payment_method?: string|null, transaction_number?: string|null}  $data
     */
    public function markAsPaid(CollectorPayment $payment, array $data): CollectorPayment
    {
        $payment->update([
            'status' => 'pagado',
            'payment_method' => ($data['payment_method'] ?? null) ?: 'transferencia',
            'transaction_number' => ($data['transaction_number'] ?? null) ?: 'TXN-'.strtoupper(uniqid()),
            'payment_date' => now()->toDateString(),
        ]);

        return $payment->fresh();
    }
}
