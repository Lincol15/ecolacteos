<?php

namespace Database\Factories;

use App\Models\Producer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MilkDelivery>
 */
class MilkDeliveryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $liters = fake()->randomFloat(2, 20, 200);
        $pricePerLiter = 1.70;

        return [
            'producer_id' => Producer::factory(),
            'collector_id' => User::factory()->create(['role' => 'acopiador']),
            'liters' => $liters,
            'temperature' => fake()->randomFloat(2, 3, 8),
            'price_per_liter' => $pricePerLiter,
            'total_amount' => round($liters * $pricePerLiter, 2),
            'delivery_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'delivery_time' => fake()->dateTimeBetween('-30 days', 'now'),
            'container_type' => fake()->randomElement(['caneca', 'bidon', 'cisterna']),
            'containers_count' => fake()->numberBetween(1, 5),
            'vehicle_plate' => fake()->regexify('[A-Z]{2}-[0-9]{4}'),
            'observations' => fake()->optional()->sentence(),
            'has_quality_analysis' => false,
            'status' => fake()->randomElement(['registrado', 'aceptado', 'analizado']),
        ];
    }
}
