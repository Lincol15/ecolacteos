<?php

namespace Database\Factories;

use App\Models\MilkDelivery;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QualityReport>
 */
class QualityReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'milk_delivery_id' => MilkDelivery::factory(),
            'analyst_id' => User::factory()->create(['role' => 'control_calidad']),
            'analyzer_model' => 'LACTOMAT',
            'sample_code' => 'MUE-' . fake()->unique()->numberBetween(1000, 9999),
            'grasa_pct' => fake()->randomFloat(2, 3.0, 5.0),
            'proteina_pct' => fake()->randomFloat(2, 2.9, 4.0),
            'lactosa_pct' => fake()->randomFloat(2, 4.5, 5.2),
            'solidos_no_grasos_pct' => fake()->randomFloat(2, 8.0, 9.5),
            'total_solidos_pct' => fake()->randomFloat(2, 11.5, 14.5),
            'agua_aniadida_pct' => fake()->randomFloat(2, 0, 3),
            'ph' => fake()->randomFloat(2, 6.4, 6.8),
            'punto_congelacion' => fake()->randomFloat(3, -0.550, -0.520),
            'acidez_dornic' => fake()->numberBetween(14, 20),
            'densidad' => fake()->randomFloat(3, 1.028, 1.034),
            'observations' => fake()->optional()->sentence(),
            'result' => fake()->randomElement(['aprobado', 'aceptable', 'observado']),
            'rejection_reason' => 'ninguno',
            'analyzed_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
