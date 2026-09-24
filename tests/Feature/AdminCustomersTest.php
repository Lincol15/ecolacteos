<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCustomersTest extends TestCase
{
    use RefreshDatabase;

    private function placeWebOrder(string $name, string $email, ?Customer $customer = null): void
    {
        $product = Product::factory()->create(['unit_price' => 10]);
        if ($customer) {
            $this->actingAs($customer, 'customer');
        }
        $this->post(route('cart.add', $product), ['quantity' => 2]);
        $this->post(route('cart.place-order'), [
            'client_name' => $name,
            'client_email' => $email,
            'payment_method' => 'efectivo',
        ]);
    }

    public function test_admin_sees_registered_customers_and_their_orders(): void
    {
        $customer = Customer::factory()->create(['name' => 'Rosa Quispe', 'email' => 'rosa@example.com']);
        $this->placeWebOrder('Rosa Quispe', 'rosa@example.com', $customer);
        Customer::factory()->create(['name' => 'Sin Pedidos']);
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

        $this->actingAs($admin)->get(route('admin.customers'))
            ->assertOk()
            ->assertSee('Rosa Quispe')
            ->assertSee('rosa@example.com')
            ->assertSee('Sin Pedidos');

        $this->actingAs($admin)->get(route('admin.customers', ['search' => 'rosa']))
            ->assertSee('Rosa Quispe')
            ->assertDontSee('Sin Pedidos');

        $this->actingAs($admin)->get(route('admin.customers-show', $customer))
            ->assertOk()
            ->assertSee('Historial de pedidos')
            ->assertSee('PW-');
    }

    public function test_guest_web_orders_are_grouped_by_email_in_the_guests_tab(): void
    {
        $this->placeWebOrder('Juan Invitado', 'juan@example.com');
        $this->placeWebOrder('Juan Invitado', 'juan@example.com');
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

        $response = $this->actingAs($admin)->get(route('admin.customers', ['tab' => 'invitados']));

        $response->assertOk()->assertSee('Juan Invitado')->assertSee('juan@example.com');
        $this->assertSame(1, $response->viewData('stats')['guests']);
        $this->assertSame(2, (int) $response->viewData('guests')->first()->orders_count);
    }

    public function test_gerente_can_view_customers_but_producer_cannot(): void
    {
        $gerente = User::factory()->create(['role' => 'gerente', 'active' => true]);
        $producer = User::factory()->create(['role' => 'productor', 'active' => true]);

        $this->actingAs($gerente)->get(route('admin.customers'))->assertOk();
        $this->actingAs($producer)->get(route('admin.customers'))->assertForbidden();
    }
}
