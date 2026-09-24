<?php

namespace Tests\Feature;

use App\Models\MilkDelivery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MilkReceivedByDayTest extends TestCase
{
    use RefreshDatabase;

    private function delivery(float $liters, string $date, string $status = 'registrado'): void
    {
        $delivery = MilkDelivery::factory()->create(['liters' => $liters]);
        $delivery->forceFill(['delivery_date' => $date, 'status' => $status])->saveQuietly();
    }

    public function test_insumos_shows_milk_received_today_by_default_without_rejected_deliveries(): void
    {
        $this->delivery(40, now()->toDateString());
        $this->delivery(10.5, now()->toDateString());
        $this->delivery(99, now()->toDateString(), 'rechazado');
        $this->delivery(30, now()->subDay()->toDateString());
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

        $response = $this->actingAs($admin)->get(route('admin.ingredients'))
            ->assertOk()
            ->assertSee('Leche recibida por día')
            ->assertSee('Recibido hoy')
            ->assertDontSee('Leche Recibida Hoy');

        $this->assertEquals(50.5, $response->viewData('milkReport')['total_liters']);
        $this->assertSame(2, $response->viewData('milkReport')['total_deliveries']);
    }

    public function test_plant_worker_can_search_milk_received_over_several_days(): void
    {
        $this->delivery(40, now()->toDateString());
        $this->delivery(30, now()->subDays(2)->toDateString());
        $this->delivery(25, now()->subDays(10)->toDateString());
        $worker = User::factory()->create(['role' => 'trabajador_planta', 'active' => true]);

        $response = $this->actingAs($worker)->get(route('plant.production', [
            'tab' => 'insumos',
            'milk_from' => now()->subDays(6)->toDateString(),
            'milk_to' => now()->toDateString(),
        ]))->assertOk();

        $report = $response->viewData('milkReport');
        $this->assertEquals(70.0, $report['total_liters']);
        $this->assertCount(7, $report['days']);
    }

    public function test_logout_uses_a_centered_confirmation_dialog(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('id="logoutDialog"', false)
            ->assertSee('Sí, cerrar sesión')
            ->assertDontSee("confirm('¿Cerrar sesión?')", false);
    }
}
