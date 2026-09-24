<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Producer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = now()->startOfWeek()->subWeek();
        $liters = fake()->randomFloat(2, 100, 600);
        $price = 1.70;
        $base = round($liters * $price, 2);

        return [
            'producer_id' => Producer::factory(),
            'period_code' => 'SEM-'.$start->format('Y-W'),
            'period_start' => $start->toDateString(),
            'period_end' => $start->copy()->endOfWeek()->toDateString(),
            'total_liters' => $liters,
            'avg_price_per_liter' => $price,
            'precio_por_litro' => $price,
            'base_amount' => $base,
            'quality_bonus' => 0,
            'production_bonus' => 0,
            'deductions' => 0,
            'llevado_a_planta' => 0,
            'total_amount' => $base,
            'payment_method' => 'transferencia',
            'status' => 'pendiente',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => 'pagado',
            'transaction_number' => 'TXN-'.fake()->numerify('######'),
            'payment_date' => now()->toDateString(),
        ]);
    }
}
