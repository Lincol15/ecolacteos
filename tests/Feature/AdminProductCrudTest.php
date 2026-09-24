<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_products_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        Product::factory()->create(['name' => 'Queso Index Test']);

        $response = $this->actingAs($admin)->get(route('admin.products'));

        $response->assertOk();
        $response->assertSee('Queso Index Test');
    }

    public function test_admin_can_create_a_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

        $response = $this->actingAs($admin)->post(route('admin.product-store'), [
            'name' => 'Yogur Frutilla',
            'category' => 'yogur',
            'description' => 'Yogur natural con frutilla',
            'unit_price' => 15.50,
            'unit' => 'L',
            'emoji' => '🍓',
            'is_active' => '1',
            'show_in_catalog' => '1',
            'sort_order' => 0,
            'spec_key' => ['Presentación'],
            'spec_value' => ['1L'],
        ]);

        $response->assertRedirect(route('admin.products'));
        $this->assertDatabaseHas('products', [
            'name' => 'Yogur Frutilla',
            'slug' => 'yogur-frutilla',
            'unit_price' => 15.50,
        ]);
        $product = Product::where('name', 'Yogur Frutilla')->firstOrFail();
        $this->assertNotEmpty($product->sku);
        $this->assertSame(['Presentación' => '1L'], $product->specifications);
    }

    public function test_admin_can_update_a_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $product = Product::factory()->create(['name' => 'Queso Original', 'unit_price' => 10]);

        $response = $this->actingAs($admin)->put(route('admin.product-update', $product), [
            'name' => 'Queso Original',
            'category' => $product->category,
            'unit_price' => 22.00,
            'unit' => $product->unit,
            'is_active' => '1',
            'show_in_catalog' => '0',
        ]);

        $response->assertRedirect(route('admin.products'));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'unit_price' => 22.00,
            'show_in_catalog' => 0,
        ]);
    }

    public function test_non_admin_cannot_manage_products(): void
    {
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => true]);

        $response = $this->actingAs($collector)->get(route('admin.products'));

        $response->assertStatus(403);
    }
}
