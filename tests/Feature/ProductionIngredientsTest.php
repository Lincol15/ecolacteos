<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\IngredientMovement;
use App\Models\MilkDelivery;
use App\Models\Producer;
use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\QualityReport;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ProductionIngredientsTest extends TestCase
{
    use RefreshDatabase;

    private function makeRecipe(): array
    {
        $product = Product::create([
            'name' => 'Queso Test', 'slug' => 'queso-test', 'sku' => 'QT-100',
            'category' => 'queso', 'description' => 'Test', 'unit_price' => 20, 'unit' => 'kg',
            'is_active' => true,
        ]);
        $sal = Ingredient::create(['name' => 'Sal Test', 'unit' => 'g', 'is_milk' => false, 'active' => true]);
        IngredientMovement::create(['ingredient_id' => $sal->id, 'movement_type' => 'entrada', 'quantity' => 200]);
        $recipe = Recipe::create([
            'product_id' => $product->id, 'name' => 'Receta Test',
            'milk_liters_per_unit' => 5, 'active' => true,
        ]);
        RecipeIngredient::create(['recipe_id' => $recipe->id, 'ingredient_id' => $sal->id, 'quantity_per_unit' => 50]);

        return [$product, $sal];
    }

    private function makeApprovedDelivery(float $liters): MilkDelivery
    {
        $producer = Producer::factory()->create();
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => true]);
        $delivery = MilkDelivery::factory()->create([
            'producer_id' => $producer->id, 'collector_id' => $collector->id, 'liters' => $liters,
        ]);
        QualityReport::factory()->create(['milk_delivery_id' => $delivery->id, 'producer_id' => $producer->id, 'result' => 'aprobado']);

        return $delivery;
    }

    public function test_production_deducts_recipe_ingredients_from_stock(): void
    {
        [$product, $sal] = $this->makeRecipe();
        $plantWorker = User::factory()->create(['role' => 'trabajador_planta', 'active' => true]);
        $delivery = $this->makeApprovedDelivery(10);

        $this->assertEquals(200, $sal->currentStock());

        $response = $this->actingAs($plantWorker)->post(route('plant.production-store'), [
            'items' => [
                ['product_id' => $product->id, 'output_units' => 2],
            ],
            'production_date' => now()->toDateString(),
            'status' => 'en_proceso',
            'milk_ids' => [$delivery->id],
        ]);

        $response->assertRedirect(route('plant.production', ['tab' => 'historial']));
        $this->assertEquals(100, $sal->fresh()->currentStock());
        $this->assertDatabaseHas('ingredient_movements', [
            'ingredient_id' => $sal->id,
            'movement_type' => 'salida',
            'quantity' => 100,
        ]);
        $this->assertDatabaseHas('production_batches', ['input_milk_liters' => 10]);
        $this->assertDatabaseHas('batch_milk_usage', ['milk_delivery_id' => $delivery->id, 'liters_used' => 10]);
    }

    public function test_production_cart_creates_multiple_batches_sharing_milk_and_deducting_own_ingredients(): void
    {
        $queso = Product::create([
            'name' => 'Queso Carrito', 'slug' => 'queso-carrito', 'sku' => 'QC-1',
            'category' => 'queso', 'description' => 'Test', 'unit_price' => 20, 'unit' => 'kg',
            'is_active' => true,
        ]);
        $yogurt = Product::create([
            'name' => 'Yogurt Carrito', 'slug' => 'yogurt-carrito', 'sku' => 'YC-1',
            'category' => 'yogur', 'description' => 'Test', 'unit_price' => 10, 'unit' => 'L',
            'is_active' => true,
        ]);
        $cuajo = Ingredient::create(['name' => 'Cuajo', 'unit' => 'g', 'is_milk' => false, 'active' => true]);
        $cultivo = Ingredient::create(['name' => 'Cultivo', 'unit' => 'g', 'is_milk' => false, 'active' => true]);
        IngredientMovement::create(['ingredient_id' => $cuajo->id, 'movement_type' => 'entrada', 'quantity' => 100]);
        IngredientMovement::create(['ingredient_id' => $cultivo->id, 'movement_type' => 'entrada', 'quantity' => 100]);

        $recipeQueso = Recipe::create(['product_id' => $queso->id, 'name' => 'Receta Queso', 'milk_liters_per_unit' => 5, 'active' => true]);
        RecipeIngredient::create(['recipe_id' => $recipeQueso->id, 'ingredient_id' => $cuajo->id, 'quantity_per_unit' => 10]);

        $recipeYogurt = Recipe::create(['product_id' => $yogurt->id, 'name' => 'Receta Yogurt', 'milk_liters_per_unit' => 3, 'active' => true]);
        RecipeIngredient::create(['recipe_id' => $recipeYogurt->id, 'ingredient_id' => $cultivo->id, 'quantity_per_unit' => 5]);

        $plantWorker = User::factory()->create(['role' => 'trabajador_planta', 'active' => true]);
        // Una sola entrega de 80L, que debe repartirse entre los dos lotes (queso necesita 10L, yogurt 30L).
        $delivery = $this->makeApprovedDelivery(80);

        $response = $this->actingAs($plantWorker)->post(route('plant.production-store'), [
            'items' => [
                ['product_id' => $queso->id, 'output_units' => 2],
                ['product_id' => $yogurt->id, 'output_units' => 10],
            ],
            'production_date' => now()->toDateString(),
            'status' => 'en_proceso',
            'milk_ids' => [$delivery->id],
        ]);

        $response->assertRedirect(route('plant.production', ['tab' => 'historial']));
        $this->assertDatabaseCount('production_batches', 2);

        $quesoBatch = ProductionBatch::where('product_id', $queso->id)->first();
        $yogurtBatch = ProductionBatch::where('product_id', $yogurt->id)->first();

        $this->assertNotNull($quesoBatch->group_number);
        $this->assertEquals($quesoBatch->group_number, $yogurtBatch->group_number);
        $this->assertEquals(20, $quesoBatch->input_milk_liters);
        $this->assertEquals(60, $yogurtBatch->input_milk_liters);

        $this->assertDatabaseHas('batch_milk_usage', ['production_batch_id' => $quesoBatch->id, 'milk_delivery_id' => $delivery->id, 'liters_used' => 20]);
        $this->assertDatabaseHas('batch_milk_usage', ['production_batch_id' => $yogurtBatch->id, 'milk_delivery_id' => $delivery->id, 'liters_used' => 60]);

        $this->assertEquals(80, $cuajo->fresh()->currentStock());
        $this->assertEquals(50, $cultivo->fresh()->currentStock());
    }

    public function test_production_blocked_when_ingredient_stock_insufficient(): void
    {
        [$product, $sal] = $this->makeRecipe();
        $plantWorker = User::factory()->create(['role' => 'trabajador_planta', 'active' => true]);
        $delivery = $this->makeApprovedDelivery(100);

        $response = $this->actingAs($plantWorker)->post(route('plant.production-store'), [
            'items' => [
                ['product_id' => $product->id, 'output_units' => 10], // needs 500g of sal, only 200g available
            ],
            'production_date' => now()->toDateString(),
            'status' => 'en_proceso',
            'milk_ids' => [$delivery->id],
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(200, $sal->fresh()->currentStock());
        $this->assertDatabaseCount('production_batches', 0);
    }

    public function test_production_requires_at_least_one_milk_delivery(): void
    {
        [$product] = $this->makeRecipe();
        $plantWorker = User::factory()->create(['role' => 'trabajador_planta', 'active' => true]);

        $response = $this->actingAs($plantWorker)->post(route('plant.production-store'), [
            'items' => [
                ['product_id' => $product->id, 'output_units' => 2],
            ],
            'production_date' => now()->toDateString(),
            'status' => 'en_proceso',
        ]);

        $response->assertSessionHasErrors('milk_ids');
        $this->assertDatabaseCount('production_batches', 0);
    }

    public function test_production_rejects_milk_delivery_already_used_in_another_batch(): void
    {
        [$product] = $this->makeRecipe();
        $plantWorker = User::factory()->create(['role' => 'trabajador_planta', 'active' => true]);
        $delivery = $this->makeApprovedDelivery(10);

        $this->actingAs($plantWorker)->post(route('plant.production-store'), [
            'items' => [
                ['product_id' => $product->id, 'output_units' => 2],
            ],
            'production_date' => now()->toDateString(),
            'status' => 'en_proceso',
            'milk_ids' => [$delivery->id],
        ]);
        $this->assertDatabaseCount('production_batches', 1);

        $response = $this->actingAs($plantWorker)->post(route('plant.production-store'), [
            'items' => [
                ['product_id' => $product->id, 'output_units' => 1],
            ],
            'production_date' => now()->toDateString(),
            'status' => 'en_proceso',
            'milk_ids' => [$delivery->id],
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('production_batches', 1);
    }

    public function test_milk_ingredient_stock_includes_all_non_rejected_deliveries_regardless_of_quality_analysis(): void
    {
        $producer = Producer::factory()->create();
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => true]);

        $approved = MilkDelivery::factory()->create(['producer_id' => $producer->id, 'collector_id' => $collector->id, 'liters' => 80]);
        QualityReport::factory()->create(['milk_delivery_id' => $approved->id, 'producer_id' => $producer->id, 'result' => 'aprobado']);

        // Sin análisis de Calidad todavía: cuenta igual, apenas la registra el acopiador.
        MilkDelivery::factory()->create(['producer_id' => $producer->id, 'collector_id' => $collector->id, 'liters' => 15]);

        $rejected = MilkDelivery::factory()->create(['producer_id' => $producer->id, 'collector_id' => $collector->id, 'liters' => 30]);
        QualityReport::factory()->create(['milk_delivery_id' => $rejected->id, 'producer_id' => $producer->id, 'result' => 'rechazado', 'rejection_reason' => 'baja_grasa']);

        $leche = Ingredient::create(['name' => 'Leche Test', 'unit' => 'L', 'is_milk' => true, 'active' => true]);

        $this->assertEquals(95.0, $leche->currentStock());
    }

    public function test_production_can_use_milk_delivery_not_yet_analyzed_by_quality(): void
    {
        [$product] = $this->makeRecipe();
        $plantWorker = User::factory()->create(['role' => 'trabajador_planta', 'active' => true]);
        $producer = Producer::factory()->create();
        // Entrega recién registrada por el acopiador, sin reporte de calidad todavía.
        $delivery = MilkDelivery::factory()->create(['producer_id' => $producer->id, 'liters' => 10]);

        $response = $this->actingAs($plantWorker)->post(route('plant.production-store'), [
            'items' => [
                ['product_id' => $product->id, 'output_units' => 2],
            ],
            'production_date' => now()->toDateString(),
            'status' => 'en_proceso',
            'milk_ids' => [$delivery->id],
        ]);

        $response->assertRedirect(route('plant.production', ['tab' => 'historial']));
        $this->assertDatabaseHas('production_batches', ['input_milk_liters' => 10]);
    }

    public function test_quality_analysis_marks_delivery_as_received_at_plant(): void
    {
        $producer = Producer::factory()->create();
        $delivery = MilkDelivery::factory()->create(['producer_id' => $producer->id, 'recibido' => false, 'status' => 'registrado']);
        $qualityUser = User::factory()->create(['role' => 'control_calidad', 'active' => true]);

        $this->assertFalse($delivery->fresh()->recibido);

        $response = $this->actingAs($qualityUser)->post(route('quality.report-store'), [
            'milk_delivery_id' => $delivery->id,
            'grasa_pct' => 3.5, 'proteina_pct' => 3.2, 'lactosa_pct' => 4.8,
            'solidos_no_grasos_pct' => 8.7, 'total_solidos_pct' => 12.2, 'agua_aniadida_pct' => 0,
            'ph' => 6.6, 'punto_congelacion' => -0.52, 'acidez_dornic' => 16, 'densidad' => 1.028,
            'result' => 'aprobado', 'rejection_reason' => 'ninguno',
        ]);

        $response->assertRedirect(route('quality.reports'));
        $this->assertTrue($delivery->fresh()->recibido);
        $this->assertEquals('analizado', $delivery->fresh()->status);
    }

    public function test_admin_can_register_ingredient_purchase_and_gerente_cannot(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $gerente = User::factory()->create(['role' => 'gerente', 'active' => true]);
        $sal = Ingredient::create(['name' => 'Sal Compra Test', 'unit' => 'g', 'is_milk' => false, 'active' => true]);

        $this->actingAs($gerente)->post(route('admin.ingredient-adjust'), [
            'items' => [['ingredient_id' => $sal->id, 'movement_type' => 'entrada', 'quantity' => 100]],
        ])->assertStatus(403);

        $response = $this->actingAs($admin)->post(route('admin.ingredient-adjust'), [
            'items' => [['ingredient_id' => $sal->id, 'movement_type' => 'entrada', 'quantity' => 500, 'notes' => 'Compra proveedor']],
        ]);

        $response->assertRedirect();
        $this->assertEquals(500, $sal->fresh()->currentStock());
    }

    public function test_admin_can_register_a_cart_with_several_ingredient_purchases_at_once(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $sal = Ingredient::create(['name' => 'Sal Carrito Test', 'unit' => 'g', 'is_milk' => false, 'active' => true]);
        $cuajo = Ingredient::create(['name' => 'Cuajo Carrito Test', 'unit' => 'g', 'is_milk' => false, 'active' => true]);

        $response = $this->actingAs($admin)->post(route('admin.ingredient-adjust'), [
            'items' => [
                ['ingredient_id' => $sal->id, 'movement_type' => 'entrada', 'quantity' => 500, 'notes' => 'Compra proveedor A'],
                ['ingredient_id' => $cuajo->id, 'movement_type' => 'entrada', 'quantity' => 50, 'notes' => 'Compra proveedor B'],
            ],
        ]);

        $response->assertRedirect();
        $this->assertEquals(500, $sal->fresh()->currentStock());
        $this->assertEquals(50, $cuajo->fresh()->currentStock());
        $this->assertDatabaseCount('ingredient_movements', 2);
    }

    public function test_plant_worker_cannot_register_ingredient_purchases(): void
    {
        $this->assertFalse(Route::has('plant.ingredient-adjust'));
        $this->assertFalse(Route::has('plant.ingredient-store'));
    }

    public function test_ingredient_adjustment_requires_notes_for_ajuste_and_merma(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $sal = Ingredient::create(['name' => 'Sal Ajuste Test', 'unit' => 'g', 'is_milk' => false, 'active' => true]);
        IngredientMovement::create(['ingredient_id' => $sal->id, 'movement_type' => 'entrada', 'quantity' => 100]);

        $withoutNotes = $this->actingAs($admin)->post(route('admin.ingredient-adjust'), [
            'items' => [['ingredient_id' => $sal->id, 'movement_type' => 'merma', 'quantity' => 10]],
        ]);
        $withoutNotes->assertSessionHasErrors('items.0.notes');
        $this->assertEquals(100, $sal->fresh()->currentStock());

        $withNotes = $this->actingAs($admin)->post(route('admin.ingredient-adjust'), [
            'items' => [['ingredient_id' => $sal->id, 'movement_type' => 'merma', 'quantity' => 10, 'notes' => 'Se dañó en almacén']],
        ]);
        $withNotes->assertSessionDoesntHaveErrors();
        $this->assertEquals(90, $sal->fresh()->currentStock());
    }
}
