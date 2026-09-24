<?php

namespace Tests\Feature;

use App\Models\Producer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProducerAndCollectorAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_producer_status_counts_on_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        Producer::factory()->create(['status' => 'activo']);
        Producer::factory()->create(['status' => 'activo']);
        Producer::factory()->create(['status' => 'inactivo']);

        $response = $this->actingAs($admin)->get(route('admin.producers'));

        $response->assertOk();
        $response->assertViewHas('counts', [
            'total' => 3,
            'activos' => 2,
            'inactivos' => 1,
        ]);
    }

    public function test_admin_can_create_a_producer(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

        $response = $this->actingAs($admin)->post(route('admin.producer-store'), [
            'name' => 'María',
            'lastname' => 'Quispe',
            'dni' => '12345678',
            'email' => 'maria@correo.com',
            'phone' => '987654321',
            'comunidad' => 'Huata Centro',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.producers'));
        $this->assertDatabaseHas('users', [
            'dni' => '12345678',
            'email' => 'maria@correo.com',
            'role' => 'productor',
        ]);
        $this->assertDatabaseHas('producers', [
            'comunidad' => 'Huata Centro',
            'status' => 'activo',
        ]);
    }

    public function test_producer_creation_validates_dni_and_phone_length(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

        $response = $this->actingAs($admin)->post(route('admin.producer-store'), [
            'name' => 'María',
            'dni' => '123',
            'email' => 'maria2@correo.com',
            'phone' => '99',
            'comunidad' => 'Huata Centro',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertSessionHasErrors(['dni', 'phone']);
        $this->assertDatabaseMissing('users', ['email' => 'maria2@correo.com']);
    }

    public function test_admin_can_toggle_producer_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $producer = Producer::factory()->create(['status' => 'activo']);

        $response = $this->actingAs($admin)->put(route('admin.producer-toggle-status', $producer));

        $response->assertRedirect();
        $this->assertSame('inactivo', $producer->fresh()->status);
    }

    public function test_admin_can_save_direct_collector_producer_assignment(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => true]);
        $producer = Producer::factory()->create(['status' => 'activo']);

        $response = $this->actingAs($admin)->put(route('admin.collectors-assign', $collector), [
            'producer_ids' => [$producer->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('collector_producer_assignments', [
            'collector_id' => $collector->id,
            'producer_id' => $producer->id,
        ]);
    }

    public function test_resolve_assigned_producers_prioritizes_explicit_assignment_over_comunidad_fallback(): void
    {
        $collector = User::factory()->create(['role' => 'acopiador', 'comunidad' => 'Huata Centro', 'active' => true]);
        $sameComunidadProducer = Producer::factory()->create(['status' => 'activo', 'comunidad' => 'Huata Centro']);
        $explicitProducer = Producer::factory()->create(['status' => 'activo', 'comunidad' => 'Otra Comunidad']);

        $collector->assignedProducers()->attach($explicitProducer->id);

        $resolved = $collector->resolveAssignedProducers();

        $this->assertCount(1, $resolved);
        $this->assertTrue($resolved->contains('id', $explicitProducer->id));
        $this->assertFalse($resolved->contains('id', $sameComunidadProducer->id));
    }

    public function test_resolve_assigned_producers_falls_back_to_assigned_comunidad_when_no_explicit_or_routes(): void
    {
        $collector = User::factory()->create(['role' => 'acopiador', 'comunidad' => 'Comunidad A', 'active' => true]);
        $comunidadAProducer1 = Producer::factory()->create(['status' => 'activo', 'comunidad' => 'Comunidad A']);
        $comunidadAProducer2 = Producer::factory()->create(['status' => 'activo', 'comunidad' => 'Comunidad A']);
        $comunidadBProducer = Producer::factory()->create(['status' => 'activo', 'comunidad' => 'Comunidad B']);

        $resolved = $collector->resolveAssignedProducers();

        $this->assertCount(2, $resolved);
        $this->assertTrue($resolved->contains('id', $comunidadAProducer1->id));
        $this->assertTrue($resolved->contains('id', $comunidadAProducer2->id));
        $this->assertFalse($resolved->contains('id', $comunidadBProducer->id));
    }

    public function test_admin_can_change_the_collectors_assigned_comunidad(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $collector = User::factory()->create(['role' => 'acopiador', 'comunidad' => 'Comunidad A', 'active' => true]);

        $response = $this->actingAs($admin)->put(route('admin.collectors-assign', $collector), [
            'comunidad' => 'Comunidad B',
        ]);

        $response->assertRedirect();
        $this->assertSame('Comunidad B', $collector->fresh()->comunidad);
    }

    public function test_admin_can_assign_several_producers_from_different_comunidades(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $collector = User::factory()->create(['role' => 'acopiador', 'comunidad' => 'Comunidad A', 'active' => true]);
        $producers = collect(['Comunidad A', 'Comunidad B', 'Comunidad C'])
            ->map(fn ($comunidad) => Producer::factory()->create(['status' => 'activo', 'comunidad' => $comunidad]));

        $this->actingAs($admin)->put(route('admin.collectors-assign', $collector), [
            'comunidad' => 'Comunidad A',
            'producer_ids' => $producers->pluck('id')->all(),
        ])->assertRedirect();

        $this->assertEqualsCanonicalizing(
            $producers->pluck('id')->all(),
            $collector->fresh()->resolveAssignedProducers()->pluck('id')->all()
        );
    }

    public function test_checking_exactly_the_comunidad_producers_keeps_the_comunidad_assignment(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => true]);
        $comunidadProducers = Producer::factory()->count(2)->create(['status' => 'activo', 'comunidad' => 'Comunidad A']);

        $this->actingAs($admin)->put(route('admin.collectors-assign', $collector), [
            'comunidad' => 'Comunidad A',
            'producer_ids' => $comunidadProducers->pluck('id')->all(),
        ]);

        $this->assertDatabaseCount('collector_producer_assignments', 0);
        $newProducer = Producer::factory()->create(['status' => 'activo', 'comunidad' => 'Comunidad A']);
        $this->assertTrue($collector->fresh()->resolveAssignedProducers()->contains('id', $newProducer->id));
    }

    public function test_admin_assignment_is_reflected_in_the_collectors_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $collector = User::factory()->create(['role' => 'acopiador', 'comunidad' => 'Comunidad A', 'active' => true]);
        $producerUser = User::factory()->create(['role' => 'productor', 'name' => 'Rosa', 'lastname' => 'Mamani']);
        Producer::factory()->create(['user_id' => $producerUser->id, 'status' => 'activo', 'comunidad' => 'Comunidad B']);

        $this->actingAs($admin)->put(route('admin.collectors-assign', $collector), [
            'comunidad' => 'Comunidad B',
        ]);

        $this->actingAs($collector->fresh())
            ->get(route('collector.profile'))
            ->assertOk()
            ->assertSee('Comunidad B')
            ->assertSee('Rosa Mamani');
    }

    public function test_non_admin_cannot_create_or_assign_producers(): void
    {
        $gerente = User::factory()->create(['role' => 'gerente', 'active' => true]);
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => true]);

        $this->actingAs($gerente)->get(route('admin.producers-create'))->assertStatus(403);
        $this->actingAs($gerente)->put(route('admin.collectors-assign', $collector), ['producer_ids' => []])->assertStatus(403);
    }
}
