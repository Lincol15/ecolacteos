<?php

namespace Tests\Feature\Api;

use App\Models\CollectionRoute;
use App\Models\MilkDelivery;
use App\Models\Producer;
use App\Models\RouteStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    private function collectorFor(Producer ...$producers): User
    {
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => true, 'vehiculo' => 'ABC-123']);
        $collector->assignedProducers()->attach(collect($producers)->pluck('id'));

        return $collector;
    }

    public function test_login_returns_token_that_authenticates_following_requests(): void
    {
        $user = User::factory()->create(['role' => 'acopiador', 'active' => true, 'dni' => '12345678']);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'login' => '12345678',
            'password' => 'password',
            'device_name' => 'test',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.role', 'acopiador');

        $this->withToken($response->json('data.access_token'))
            ->getJson(route('api.v1.auth.me'))
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_login_rejects_wrong_password_and_inactive_users(): void
    {
        $user = User::factory()->create(['role' => 'acopiador', 'active' => true]);
        $inactive = User::factory()->create(['role' => 'acopiador', 'active' => false]);

        $this->postJson(route('api.v1.auth.login'), ['login' => $user->email, 'password' => 'incorrecta'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('login');

        $this->postJson(route('api.v1.auth.login'), ['login' => $inactive->email, 'password' => 'password'])
            ->assertForbidden();
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = User::factory()->create(['role' => 'acopiador', 'active' => true]);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson(route('api.v1.auth.logout'))->assertNoContent();
        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson(route('api.v1.auth.me'))->assertUnauthorized();
    }

    public function test_requests_without_token_get_json_401(): void
    {
        $this->getJson(route('api.v1.deliveries.index'))
            ->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    public function test_collector_only_lists_assigned_producers(): void
    {
        $assigned = Producer::factory()->create();
        Producer::factory()->create();
        Sanctum::actingAs($this->collectorFor($assigned));

        $this->getJson(route('api.v1.producers.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $assigned->id);
    }

    public function test_collector_registers_delivery_and_it_is_visible_to_admin_on_the_web(): void
    {
        $producer = Producer::factory()->create(['comunidad' => 'Huata Centro']);
        $collector = $this->collectorFor($producer);
        Sanctum::actingAs($collector);

        $response = $this->postJson(route('api.v1.deliveries.store'), [
            'producer_id' => $producer->id,
            'liters' => 50,
            'observations' => 'Registro desde la app',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.liters', 50)
            ->assertJsonPath('data.zona', 'Huata Centro')
            ->assertJsonPath('data.status', 'registrado')
            ->assertJsonPath('data.recibido', false)
            ->assertJsonPath('data.vehicle_plate', 'ABC-123');

        $this->assertDatabaseHas('milk_deliveries', [
            'producer_id' => $producer->id,
            'collector_id' => $collector->id,
            'liters' => 50,
            'delivery_date' => now()->toDateString(),
        ]);

        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $this->actingAs($admin, 'web')
            ->get(route('admin.deliveries'))
            ->assertOk()
            ->assertSee($producer->code)
            ->assertSee('50.0 L');
    }

    public function test_collector_cannot_register_delivery_for_unassigned_producer(): void
    {
        $assigned = Producer::factory()->create();
        $other = Producer::factory()->create();
        Sanctum::actingAs($this->collectorFor($assigned));

        $this->postJson(route('api.v1.deliveries.store'), ['producer_id' => $other->id, 'liters' => 10])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('producer_id');

        $this->assertDatabaseCount('milk_deliveries', 0);
    }

    public function test_delivery_validation_errors_are_returned_as_json(): void
    {
        $producer = Producer::factory()->create();
        Sanctum::actingAs($this->collectorFor($producer));

        $this->postJson(route('api.v1.deliveries.store'), ['producer_id' => $producer->id, 'liters' => 0])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('liters');
    }

    public function test_producer_role_cannot_register_deliveries(): void
    {
        $producer = Producer::factory()->create();
        Sanctum::actingAs($producer->user);

        $this->postJson(route('api.v1.deliveries.store'), ['producer_id' => $producer->id, 'liters' => 10])
            ->assertForbidden();
    }

    public function test_collector_lists_only_own_deliveries(): void
    {
        $producer = Producer::factory()->create();
        $collector = $this->collectorFor($producer);
        MilkDelivery::factory()->create(['producer_id' => $producer->id, 'collector_id' => $collector->id]);
        MilkDelivery::factory()->create(['producer_id' => $producer->id]);
        Sanctum::actingAs($collector);

        $this->getJson(route('api.v1.deliveries.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.collector_id', $collector->id);
    }

    public function test_collector_can_correct_liters_only_while_delivery_is_in_transit(): void
    {
        $producer = Producer::factory()->create();
        $collector = $this->collectorFor($producer);
        $delivery = MilkDelivery::factory()->create([
            'producer_id' => $producer->id, 'collector_id' => $collector->id,
            'status' => 'registrado', 'recibido' => false, 'liters' => 40, 'price_per_liter' => 2,
        ]);
        Sanctum::actingAs($collector);

        $this->patchJson(route('api.v1.deliveries.update', $delivery), ['liters' => 45])
            ->assertOk()
            ->assertJsonPath('data.liters', 45)
            ->assertJsonPath('data.total_amount', 90);

        $delivery->update(['recibido' => true]);

        $this->patchJson(route('api.v1.deliveries.update', $delivery), ['liters' => 60])
            ->assertForbidden();
    }

    public function test_plant_worker_registers_reception_and_collector_cannot(): void
    {
        $delivery = MilkDelivery::factory()->create(['recibido' => false]);

        Sanctum::actingAs($delivery->collector);
        $this->patchJson(route('api.v1.deliveries.receive', $delivery))->assertForbidden();

        Sanctum::actingAs(User::factory()->create(['role' => 'trabajador_planta', 'active' => true]));
        $this->patchJson(route('api.v1.deliveries.receive', $delivery))
            ->assertOk()
            ->assertJsonPath('data.recibido', true);

        $this->assertTrue($delivery->fresh()->recibido);
    }

    public function test_collector_sees_own_routes_and_updates_stop_status(): void
    {
        $producer = Producer::factory()->create();
        $collector = $this->collectorFor($producer);
        $route = CollectionRoute::create([
            'name' => 'Ruta Norte', 'code' => 'R-001', 'day' => 'Lunes',
            'collector_id' => $collector->id, 'status' => 'planeada',
        ]);
        $stop = RouteStop::create(['collection_route_id' => $route->id, 'producer_id' => $producer->id, 'stop_order' => 1]);
        CollectionRoute::create(['name' => 'Ajena', 'code' => 'R-002', 'status' => 'planeada']);
        Sanctum::actingAs($collector);

        $this->getJson(route('api.v1.routes.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.stops.0.producer.id', $producer->id);

        $this->patchJson(route('api.v1.route-stops.update', $stop), ['status' => 'visitado'])
            ->assertOk()
            ->assertJsonPath('data.status', 'visitado');

        $this->assertNotNull($stop->fresh()->visited_at);
    }

    public function test_admin_registers_producer_through_api(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'admin', 'active' => true]));

        $this->postJson(route('api.v1.producers.store'), [
            'name' => 'Juana',
            'lastname' => 'Mamani',
            'dni' => '87654321',
            'email' => 'juana@example.com',
            'phone' => '987654321',
            'address' => 'Jr. Lima 100',
            'comunidad' => 'Huata Centro',
            'password' => 'secreto1',
            'password_confirmation' => 'secreto1',
        ])->assertCreated()
            ->assertJsonPath('data.dni', '87654321')
            ->assertJsonPath('data.address', 'Jr. Lima 100')
            ->assertJsonPath('data.status', 'activo');

        $this->assertDatabaseHas('users', ['dni' => '87654321', 'role' => 'productor']);
        $this->assertDatabaseHas('producers', ['comunidad' => 'Huata Centro']);
    }

    public function test_collector_cannot_register_producers(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'acopiador', 'active' => true]));

        $this->postJson(route('api.v1.producers.store'), [])->assertForbidden();
    }

    public function test_admin_edits_producer_personal_and_farm_data_through_api(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'admin', 'active' => true]));
        $producer = Producer::factory()->create();

        $this->putJson(route('api.v1.producers.update', $producer), [
            'name' => 'Juana',
            'phone' => '912345678',
            'address' => 'Jr. Puno 200',
            'comunidad' => 'Huata Alta',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Juana')
            ->assertJsonPath('data.phone', '912345678')
            ->assertJsonPath('data.comunidad', 'Huata Alta');

        $this->assertDatabaseHas('users', ['id' => $producer->user_id, 'name' => 'Juana', 'address' => 'Jr. Puno 200']);
        $this->assertDatabaseHas('producers', ['id' => $producer->id, 'comunidad' => 'Huata Alta', 'zone' => 'Huata Alta']);
    }

    public function test_editing_producer_rejects_dni_of_another_user(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'admin', 'active' => true]));
        User::factory()->create(['dni' => '11112222']);
        $producer = Producer::factory()->create();

        $this->putJson(route('api.v1.producers.update', $producer), ['dni' => '11112222'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('dni');
    }

    public function test_admin_deletes_producer_without_history_and_its_account(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'admin', 'active' => true]));
        $producer = Producer::factory()->create();

        $this->deleteJson(route('api.v1.producers.destroy', $producer))->assertNoContent();

        $this->assertSoftDeleted('producers', ['id' => $producer->id]);
        $this->assertSoftDeleted('users', ['id' => $producer->user_id]);
    }

    public function test_producer_with_deliveries_cannot_be_deleted(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'admin', 'active' => true]));
        $producer = Producer::factory()->create();
        MilkDelivery::factory()->create(['producer_id' => $producer->id]);

        $this->deleteJson(route('api.v1.producers.destroy', $producer))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('producer');

        $this->assertDatabaseHas('producers', ['id' => $producer->id]);
        $this->assertSame(1, MilkDelivery::where('producer_id', $producer->id)->count());
    }

    public function test_only_admin_can_edit_or_delete_producers(): void
    {
        $producer = Producer::factory()->create();
        Sanctum::actingAs(User::factory()->create(['role' => 'gerente', 'active' => true]));

        $this->putJson(route('api.v1.producers.update', $producer), ['name' => 'X'])->assertForbidden();
        $this->deleteJson(route('api.v1.producers.destroy', $producer))->assertForbidden();
    }
}
