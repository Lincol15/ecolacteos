<?php

namespace Tests\Feature\Api;

use App\Models\Ingredient;
use App\Models\IngredientMovement;
use App\Models\MilkDelivery;
use App\Models\Producer;
use App\Models\Product;
use App\Models\QualityReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileApiPlantTest extends TestCase
{
    use RefreshDatabase;

    private function plantWorker(): User
    {
        return User::factory()->create(['role' => 'trabajador_planta', 'active' => true]);
    }

    /**
     * @return array{0: Product, 1: Ingredient, 2: Ingredient}
     */
    private function catalog(): array
    {
        $product = Product::create([
            'name' => 'Queso Fresco', 'slug' => 'queso-fresco', 'sku' => 'QF-1',
            'category' => 'queso', 'description' => 'Test', 'unit_price' => 20, 'unit' => 'kg', 'is_active' => true,
        ]);
        $milk = Ingredient::create(['name' => 'Leche', 'unit' => 'L', 'is_milk' => true, 'active' => true]);
        $salt = Ingredient::create(['name' => 'Sal', 'unit' => 'g', 'is_milk' => false, 'active' => true]);
        IngredientMovement::create(['ingredient_id' => $salt->id, 'movement_type' => 'entrada', 'quantity' => 500]);

        return [$product, $milk, $salt];
    }

    private function approvedDelivery(float $liters): MilkDelivery
    {
        $producer = Producer::factory()->create();
        $delivery = MilkDelivery::factory()->create(['producer_id' => $producer->id, 'liters' => $liters]);
        QualityReport::factory()->create(['milk_delivery_id' => $delivery->id, 'producer_id' => $producer->id, 'result' => 'aprobado']);

        return $delivery;
    }

    public function test_plant_worker_creates_recipe_produces_batch_and_sells_it(): void
    {
        [$product, $milk, $salt] = $this->catalog();
        $this->approvedDelivery(20);
        Sanctum::actingAs($this->plantWorker());

        $this->postJson(route('api.v1.plant.recipes.store'), [
            'product_id' => $product->id,
            'name' => 'Queso 1kg',
            'ingredients' => [
                ['ingredient_id' => $milk->id, 'quantity_per_unit' => 10],
                ['ingredient_id' => $salt->id, 'quantity_per_unit' => 50],
            ],
        ])->assertCreated()
            ->assertJsonPath('data.milk_liters_per_unit', 10)
            ->assertJsonCount(2, 'data.ingredients');

        $batch = $this->postJson(route('api.v1.plant.production.store'), [
            'production_date' => now()->toDateString(),
            'status' => 'terminado',
            'items' => [['product_id' => $product->id, 'output_units' => 2]],
        ])->assertCreated()
            ->assertJsonPath('data.0.input_milk_liters', 20)
            ->assertJsonPath('data.0.stock', 2)
            ->json('data.0');

        $this->assertEquals(400, $salt->fresh()->currentStock());

        $this->postJson(route('api.v1.plant.sales.store'), [
            'client_name' => 'Bodega Huata',
            'sale_date' => now()->toDateString(),
            'payment_status' => 'pendiente',
            'payment_method' => 'efectivo',
            'sale_type' => 'mostrador',
            'items' => [['product_id' => $product->id, 'batch_id' => $batch['id'], 'quantity' => 1.5, 'unit_price' => 22]],
        ])->assertCreated()
            ->assertJsonPath('data.subtotal', 33)
            ->assertJsonPath('data.tax', 5.94)
            ->assertJsonPath('data.total_amount', 38.94);

        $this->getJson(route('api.v1.plant.batches'))->assertJsonPath('data.0.stock', 0.5);
        $this->getJson(route('api.v1.plant.products'))->assertJsonPath('data.0.stock', 0.5);
    }

    public function test_production_without_available_milk_returns_422(): void
    {
        [$product, $milk] = $this->catalog();
        Sanctum::actingAs($this->plantWorker());
        $this->postJson(route('api.v1.plant.recipes.store'), [
            'product_id' => $product->id, 'name' => 'Queso', 'ingredients' => [['ingredient_id' => $milk->id, 'quantity_per_unit' => 10]],
        ])->assertCreated();

        $this->postJson(route('api.v1.plant.production.store'), [
            'production_date' => now()->toDateString(),
            'status' => 'terminado',
            'items' => [['product_id' => $product->id, 'output_units' => 5]],
        ])->assertUnprocessable()->assertJsonValidationErrors('items');
    }

    public function test_sale_requires_items_and_collector_cannot_access_plant(): void
    {
        Sanctum::actingAs($this->plantWorker());
        $this->postJson(route('api.v1.plant.sales.store'), [
            'client_name' => 'X', 'sale_date' => now()->toDateString(),
            'payment_status' => 'pendiente', 'payment_method' => 'efectivo', 'sale_type' => 'mostrador',
        ])->assertUnprocessable()->assertJsonValidationErrors('items');

        Sanctum::actingAs(User::factory()->create(['role' => 'acopiador', 'active' => true]));
        $this->getJson(route('api.v1.plant.batches'))->assertForbidden();
    }
}
