<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\IngredientMovement;
use App\Models\MilkDelivery;
use App\Models\Producer;
use App\Models\Product;
use App\Models\QualityReport;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductionCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_production_create_page_renders(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

        $response = $this->actingAs($admin)->get(route('admin.production-create'));

        $response->assertOk();
        $response->assertSee('milk_ids[]', false);
        $response->assertSee('Supervisor');
    }

    public function test_admin_can_create_production_batch_with_real_milk_deliveries(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $product = Product::create([
            'name' => 'Queso Admin Test', 'slug' => 'queso-admin-test', 'sku' => 'QAT-1',
            'category' => 'queso', 'description' => 'Test', 'unit_price' => 20, 'unit' => 'kg',
            'is_active' => true,
        ]);
        $sal = Ingredient::create(['name' => 'Sal Admin Test', 'unit' => 'g', 'is_milk' => false, 'active' => true]);
        IngredientMovement::create(['ingredient_id' => $sal->id, 'movement_type' => 'entrada', 'quantity' => 200]);
        $recipe = Recipe::create(['product_id' => $product->id, 'name' => 'Receta Admin Test', 'milk_liters_per_unit' => 5, 'active' => true]);
        RecipeIngredient::create(['recipe_id' => $recipe->id, 'ingredient_id' => $sal->id, 'quantity_per_unit' => 50]);

        $producer = Producer::factory()->create();
        $delivery = MilkDelivery::factory()->create(['producer_id' => $producer->id, 'liters' => 10]);
        QualityReport::factory()->create(['milk_delivery_id' => $delivery->id, 'producer_id' => $producer->id, 'result' => 'aprobado']);

        $response = $this->actingAs($admin)->post(route('admin.production-store'), [
            'items' => [
                ['product_id' => $product->id, 'output_units' => 2],
            ],
            'production_date' => now()->toDateString(),
            'status' => 'en_proceso',
            'milk_ids' => [$delivery->id],
        ]);

        $response->assertRedirect(route('admin.production'));
        $this->assertDatabaseHas('production_batches', ['product_id' => $product->id, 'input_milk_liters' => 10]);
        $this->assertEquals(100, $sal->fresh()->currentStock());
    }
}
