<?php

namespace Database\Factories;

use App\Models\CollectorPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CollectorPayment>
 */
class CollectorPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $month = now()->startOfMonth();
        $salary = fake()->randomElement([1200, 1300, 1500]);

        return [
            'collector_id' => User::factory()->state(['role' => 'acopiador', 'monthly_salary' => $salary]),
            'period_code' => 'MES-'.$month->format('Y-m'),
            'period_month' => $month->toDateString(),
            'base_salary' => $salary,
            'bonus' => 0,
            'deductions' => 0,
            'total_amount' => $salary,
            'liters_collected' => fake()->randomFloat(2, 500, 5000),
            'deliveries_count' => fake()->numberBetween(20, 120),
            'status' => 'pendiente',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => 'pagado',
            'payment_method' => 'transferencia',
            'transaction_number' => 'TXN-'.fake()->numerify('######'),
            'payment_date' => now()->toDateString(),
        ]);
    }
}
