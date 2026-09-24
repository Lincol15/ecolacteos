<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\IngredientMovement;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminIngredientDeletionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'active' => true]);
    }

    public function test_admin_can_delete_an_unused_ingredient(): void
    {
        $ingredient = Ingredient::create(['name' => 'Colorante', 'unit' => 'g', 'is_milk' => false, 'active' => true]);

        $this->actingAs($this->admin())->delete(route('admin.ingredient-destroy', $ingredient))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('ingredients', ['id' => $ingredient->id]);
    }

    public function test_ingredient_with_movements_is_hidden_but_keeps_its_history(): void
    {
        $ingredient = Ingredient::create(['name' => 'Sal', 'unit' => 'g', 'is_milk' => false, 'active' => true]);
        IngredientMovement::create(['ingredient_id' => $ingredient->id, 'movement_type' => 'entrada', 'quantity' => 100]);
        $admin = $this->admin();

        $this->actingAs($admin)->delete(route('admin.ingredient-destroy', $ingredient))->assertSessionHas('success');

        $this->assertFalse($ingredient->fresh()->active);
        $this->assertDatabaseHas('ingredient_movements', ['ingredient_id' => $ingredient->id]);

        // Volver a registrarlo con el mismo nombre lo reactiva.
        $this->actingAs($admin)->post(route('admin.ingredient-store'), ['name' => 'Sal', 'unit' => 'kg'])->assertSessionHas('success');
        $this->assertTrue($ingredient->fresh()->active);
        $this->assertSame(1, Ingredient::where('name', 'Sal')->count());
    }

    public function test_ingredient_used_in_a_recipe_cannot_be_deleted(): void
    {
        $ingredient = Ingredient::create(['name' => 'Cuajo', 'unit' => 'g', 'is_milk' => false, 'active' => true]);
        $recipe = Recipe::create(['product_id' => Product::factory()->create()->id, 'name' => 'Receta Queso', 'milk_liters_per_unit' => 5, 'active' => true]);
        RecipeIngredient::create(['recipe_id' => $recipe->id, 'ingredient_id' => $ingredient->id, 'quantity_per_unit' => 10]);

        $this->actingAs($this->admin())->delete(route('admin.ingredient-destroy', $ingredient))
            ->assertSessionHas('error', fn (string $message) => str_contains($message, 'Receta Queso'));

        $this->assertTrue($ingredient->fresh()->active);
    }

    public function test_milk_cannot_be_deleted_and_gerente_cannot_delete(): void
    {
        $milk = Ingredient::create(['name' => 'Leche', 'unit' => 'L', 'is_milk' => true, 'active' => true]);
        $salt = Ingredient::create(['name' => 'Sal', 'unit' => 'g', 'is_milk' => false, 'active' => true]);
        $gerente = User::factory()->create(['role' => 'gerente', 'active' => true]);

        $this->actingAs($this->admin())->delete(route('admin.ingredient-destroy', $milk))->assertSessionHas('error');
        $this->assertDatabaseHas('ingredients', ['id' => $milk->id, 'active' => true]);

        $this->actingAs($gerente)->delete(route('admin.ingredient-destroy', $salt))->assertForbidden();
    }
}
