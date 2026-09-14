<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Producer>
 */
class ProducerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create(['role' => 'productor']),
            'code' => 'PROD-' . fake()->unique()->numberBetween(1000, 9999),
            'farm_name' => fake()->company(),
            'zone' => fake()->randomElement(['Huata Centro', 'Pueblo Libre', 'San Miguel', 'Ticapampa']),
            'district' => 'Huata',
            'province' => 'Huata',
            'region' => 'Ancash',
            'latitude' => fake()->latitude(-10, -9),
            'longitude' => fake()->longitude(-78, -77),
            'cows_count' => fake()->numberBetween(5, 50),
            'daily_avg_liters' => fake()->randomFloat(2, 30, 200),
            'status' => 'activo',
            'registration_date' => fake()->dateTimeBetween('-2 years', 'now'),
        ];
    }
}
