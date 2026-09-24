<?php

namespace Tests\Feature;

use App\Models\MilkDelivery;
use App\Models\PlantConfig;
use App\Models\Producer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MilkDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_collector_can_create_milk_delivery(): void
    {
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => true]);
        $producer = Producer::factory()->create();

        $response = $this->actingAs($collector)->post(route('collector.delivery-store'), [
            'producer_id' => $producer->id,
            'liters' => 100.5,
            'temperature' => 4.5,
            'delivery_date' => now()->toDateString(),
            'container_type' => 'caneca',
            'containers_count' => 2,
        ]);

        $response->assertRedirect(route('collector.deliveries'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('milk_deliveries', [
            'producer_id' => $producer->id,
            'collector_id' => $collector->id,
            'liters' => 100.5,
            'status' => 'registrado',
        ]);
    }

    public function test_delivery_zona_is_autofilled_from_producer_comunidad(): void
    {
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => true]);
        $producer = Producer::factory()->create(['comunidad' => 'Huata Centro']);

        $this->actingAs($collector)->post(route('collector.delivery-store'), [
            'producer_id' => $producer->id,
            'liters' => 20,
            'delivery_date' => now()->toDateString(),
        ]);

        $this->assertDatabaseHas('milk_deliveries', [
            'producer_id' => $producer->id,
            'zona' => 'Huata Centro',
        ]);
    }

    public function test_delivery_calculates_total_amount_correctly(): void
    {
        $delivery = MilkDelivery::factory()->create([
            'liters' => 100,
            'price_per_liter' => 1.70,
        ]);

        $this->assertEquals(170.00, $delivery->total_amount);
    }

    public function test_producer_cannot_create_delivery(): void
    {
        $producer = User::factory()->create(['role' => 'productor', 'active' => true]);
        $producerModel = Producer::factory()->create(['user_id' => $producer->id]);

        $response = $this->actingAs($producer)->post(route('collector.delivery-store'), [
            'producer_id' => $producerModel->id,
            'liters' => 100,
            'temperature' => 4.5,
            'delivery_date' => now()->toDateString(),
        ]);

        $response->assertStatus(403); // Forbidden
    }

    public function test_delivery_requires_minimum_liters(): void
    {
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => true]);
        $producer = Producer::factory()->create();

        $response = $this->actingAs($collector)->post(route('collector.delivery-store'), [
            'producer_id' => $producer->id,
            'liters' => 0.05, // Menor al mínimo
            'delivery_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('liters');
    }

    public function test_delivery_respects_configurable_max_liters(): void
    {
        PlantConfig::updateOrCreate(
            ['key' => 'litros_maximos_entrega'],
            ['label' => 'Litros Máximos por Entrega', 'value' => '50', 'value_type' => 'number']
        );
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => true]);
        $producer = Producer::factory()->create();

        $tooMuch = $this->actingAs($collector)->post(route('collector.delivery-store'), [
            'producer_id' => $producer->id,
            'liters' => 51,
            'delivery_date' => now()->toDateString(),
        ]);
        $tooMuch->assertSessionHasErrors('liters');

        $withinLimit = $this->actingAs($collector)->post(route('collector.delivery-store'), [
            'producer_id' => $producer->id,
            'liters' => 50,
            'delivery_date' => now()->toDateString(),
        ]);
        $withinLimit->assertSessionDoesntHaveErrors('liters');
    }

    public function test_collector_only_sends_liters_and_observations_the_rest_is_automatic(): void
    {
        PlantConfig::updateOrCreate(
            ['key' => 'precio_litro_leche'],
            ['label' => 'Precio por Litro de Leche', 'value' => '1.70', 'value_type' => 'number']
        );
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => true, 'vehiculo' => 'ABC-123']);
        $producer = Producer::factory()->create();

        $this->actingAs($collector)->post(route('collector.delivery-store'), [
            'producer_id' => $producer->id,
            'liters' => 100,
            'observations' => 'Leche en buen estado',
            // Campos que el acopiador ya no controla: se ignoran.
            'price_per_liter' => 9.99,
            'temperature' => 1,
            'vehicle_plate' => 'OTRO-999',
            'delivery_date' => now()->subDays(5)->toDateString(),
        ])->assertRedirect(route('collector.deliveries'));

        $delivery = MilkDelivery::where('producer_id', $producer->id)->firstOrFail();
        $this->assertEquals(1.70, (float) $delivery->price_per_liter);
        $this->assertEquals(170.00, (float) $delivery->total_amount);
        $this->assertNull($delivery->temperature);
        $this->assertSame('ABC-123', $delivery->vehicle_plate);
        $this->assertSame(now()->toDateString(), $delivery->delivery_date->toDateString());
        $this->assertSame('Leche en buen estado', $delivery->observations);
    }

    public function test_admin_delivery_validates_temperature_range(): void
    {
        $collector = User::factory()->create(['role' => 'admin', 'active' => true]);
        $producer = Producer::factory()->create();

        // Temperatura muy baja
        $response = $this->actingAs($collector)->post(route('admin.deliveries-store'), [
            'producer_id' => $producer->id,
            'liters' => 100,
            'temperature' => 1.5, // Menor a 2°C
            'delivery_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('temperature');

        // Temperatura muy alta
        $response = $this->actingAs($collector)->post(route('admin.deliveries-store'), [
            'producer_id' => $producer->id,
            'liters' => 100,
            'temperature' => 11, // Mayor a 10°C
            'delivery_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('temperature');
    }

    public function test_admin_delivery_cannot_be_in_future(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $producer = Producer::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.deliveries-store'), [
            'producer_id' => $producer->id,
            'liters' => 100,
            'delivery_date' => now()->addDay()->toDateString(),
        ]);

        $response->assertSessionHasErrors('delivery_date');
    }

    public function test_collector_can_view_their_deliveries(): void
    {
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => true]);
        $otherCollector = User::factory()->create(['role' => 'acopiador', 'active' => true]);

        $myDelivery = MilkDelivery::factory()->create(['collector_id' => $collector->id]);
        $otherDelivery = MilkDelivery::factory()->create(['collector_id' => $otherCollector->id]);

        $response = $this->actingAs($collector)->get(route('collector.deliveries'));

        $response->assertStatus(200);
        $response->assertSee($myDelivery->liters);
        $response->assertDontSee($otherDelivery->liters);
    }

    public function test_producer_can_view_their_deliveries(): void
    {
        $producer = User::factory()->create(['role' => 'productor', 'active' => true]);
        $producerModel = Producer::factory()->create(['user_id' => $producer->id]);

        $myDelivery = MilkDelivery::factory()->create(['producer_id' => $producerModel->id]);
        $otherDelivery = MilkDelivery::factory()->create();

        $response = $this->actingAs($producer)->get(route('producer.deliveries'));

        $response->assertStatus(200);
    }
}
