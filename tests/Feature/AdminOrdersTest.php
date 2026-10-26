<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrdersTest extends TestCase
{
    use RefreshDatabase;

    private function placeWebOrder(): Sale
    {
        $product = Product::factory()->create(['unit_price' => 10]);
        $this->post(route('cart.add', $product), ['quantity' => 3]);
        $this->post(route('cart.place-order'), [
            'client_name' => 'María Cliente',
            'client_email' => 'maria@example.com',
            'payment_method' => 'efectivo',
        ]);

        return Sale::where('sale_type', 'pedido_web')->latest('id')->firstOrFail();
    }

    public function test_new_web_order_appears_in_pedidos_and_moves_step_by_step_until_delivered(): void
    {
        $order = $this->placeWebOrder();
        $this->assertSame('nuevo', $order->order_status);
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

        $this->actingAs($admin)->get(route('admin.orders'))
            ->assertOk()
            ->assertSee($order->invoice_number)
            ->assertSee('María Cliente')
            ->assertSee('Confirmar pedido');

        // No se puede saltar pasos.
        $this->actingAs($admin)->put(route('admin.orders-update-status', $order), ['order_status' => 'entregado'])
            ->assertSessionHas('error');
        $this->assertSame('nuevo', $order->fresh()->order_status);

        foreach (['confirmado', 'en_camino', 'entregado'] as $step) {
            $this->actingAs($admin)->put(route('admin.orders-update-status', $order), ['order_status' => $step])
                ->assertSessionHas('success');
            $this->assertSame($step, $order->fresh()->order_status);
        }
    }

    public function test_cancelling_an_order_returns_products_to_stock(): void
    {
        $order = $this->placeWebOrder();
        $item = $order->items()->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

        $this->actingAs($admin)->put(route('admin.orders-update-status', $order), ['order_status' => 'cancelado'])
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('cancelado', $order->order_status);
        $this->assertSame('anulado', $order->payment_status);
        $this->assertDatabaseHas('inventories', [
            'product_id' => $item->product_id,
            'movement_type' => 'devolucion',
            'related_sale_id' => $order->id,
            'quantity' => 3,
        ]);

        // Un pedido cancelado ya no cambia de estado.
        $this->actingAs($admin)->put(route('admin.orders-update-status', $order), ['order_status' => 'cancelado'])
            ->assertSessionHas('error');
    }

    public function test_admin_menu_shows_clientes_pedidos_y_ventas_and_gerente_cannot_change_orders(): void
    {
        $order = $this->placeWebOrder();
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $gerente = User::factory()->create(['role' => 'gerente', 'active' => true]);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertSee('Comercial')
            ->assertSee(route('admin.customers'))
            ->assertSee(route('admin.orders'))
            ->assertSee(route('admin.sales'));

        $this->actingAs($gerente)->get(route('admin.orders'))->assertOk()->assertDontSee('Confirmar pedido');
        $this->actingAs($gerente)->put(route('admin.orders-update-status', $order), ['order_status' => 'confirmado'])->assertForbidden();
    }
}
