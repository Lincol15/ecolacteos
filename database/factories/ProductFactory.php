<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->word()).' '.fake()->randomElement(['Andino', 'Huata', 'Tradicional']);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'sku' => strtoupper(Str::random(4)).'-'.fake()->unique()->numberBetween(100, 999),
            'category' => fake()->randomElement(array_keys(Product::CATEGORIES)),
            'description' => fake()->sentence(10),
            'long_description' => fake()->paragraph(),
            'unit_price' => fake()->randomFloat(2, 5, 60),
            'unit' => fake()->randomElement(['kg', 'L', 'und']),
            'image_url' => null,
            'emoji' => '🧀',
            'specifications' => null,
            'is_active' => true,
            'show_in_catalog' => true,
            'sort_order' => 0,
        ];
    }
}
