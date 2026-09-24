<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_can_register_a_customer_account(): void
    {
        $response = $this->post(route('customer.register.store'), [
            'name' => 'Cliente Prueba',
            'email' => 'cliente@example.com',
            'phone' => '999888777',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('cart.index'));
        $this->assertDatabaseHas('customers', ['email' => 'cliente@example.com']);
        $this->assertAuthenticated('customer');
    }

    public function test_a_customer_can_login(): void
    {
        $customer = Customer::factory()->create(['password' => bcrypt('secret123')]);

        $response = $this->post(route('authenticate'), [
            'email' => $customer->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('cart.index'));
        $this->assertAuthenticatedAs($customer, 'customer');
    }

    public function test_staff_can_login_with_dni_from_the_shared_login(): void
    {
        $producer = User::factory()->create(['role' => 'productor', 'active' => true, 'dni' => '12345678', 'password' => bcrypt('secret123')]);

        $this->post(route('authenticate'), [
            'email' => '12345678',
            'password' => 'secret123',
        ])->assertRedirect(route('producer.dashboard'));

        $this->assertAuthenticatedAs($producer, 'web');
        $this->assertGuest('customer');
    }

    public function test_inactive_staff_cannot_login(): void
    {
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => false, 'password' => bcrypt('secret123')]);

        $this->post(route('authenticate'), [
            'email' => $collector->email,
            'password' => 'secret123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('web');
    }

    public function test_a_guest_can_add_a_product_to_the_cart_and_see_it(): void
    {
        $product = Product::factory()->create(['name' => 'Yogur Carrito Test', 'unit_price' => 12]);

        $this->post(route('cart.add', $product), ['quantity' => 2]);
        $response = $this->get(route('cart.index'));

        $response->assertOk();
        $response->assertSee('Yogur Carrito Test');
        $response->assertSee('24.00');
    }

    public function test_a_guest_can_place_an_order_from_the_cart(): void
    {
        $product = Product::factory()->create(['unit_price' => 10]);

        $this->post(route('cart.add', $product), ['quantity' => 3]);

        $response = $this->post(route('cart.place-order'), [
            'client_name' => 'Cliente Invitado',
            'client_email' => 'invitado@example.com',
            'payment_method' => 'efectivo',
        ]);

        $response->assertRedirect(route('catalog'));
        $this->assertDatabaseHas('sales', [
            'client_name' => 'Cliente Invitado',
            'sale_type' => 'pedido_web',
            'payment_status' => 'pendiente',
            'customer_id' => null,
        ]);
        $this->assertDatabaseHas('sale_items', ['product_id' => $product->id, 'quantity' => 3]);
        $this->assertDatabaseHas('inventories', ['product_id' => $product->id, 'movement_type' => 'salida', 'quantity' => 3]);
        $this->assertEmpty(session('cart'));
    }

    public function test_a_logged_in_customer_order_is_linked_to_their_account(): void
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create(['unit_price' => 20]);

        $this->actingAs($customer, 'customer');
        $this->post(route('cart.add', $product), ['quantity' => 1]);
        $this->post(route('cart.place-order'), [
            'client_name' => $customer->name,
            'client_email' => $customer->email,
            'payment_method' => 'efectivo',
        ]);

        $this->assertDatabaseHas('sales', ['customer_id' => $customer->id]);
    }
}
