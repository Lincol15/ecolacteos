<?php

namespace Tests\Feature\Api;

use App\Models\MilkDelivery;
use App\Models\Producer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Sesión con access + refresh token y sincronización de recolecciones hechas sin conexión.
 */
class MobileApiSessionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{access_token: string, refresh_token: string}
     */
    private function loginAs(User $user): array
    {
        $response = $this->postJson(route('api.v1.auth.login'), [
            'login' => $user->email,
            'password' => 'password',
            'device_name' => 'test',
        ])->assertOk();

        $this->app['auth']->forgetGuards();

        return $response->json('data');
    }

    private function collectorFor(Producer $producer): User
    {
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => true]);
        $collector->assignedProducers()->attach($producer->id);

        return $collector;
    }

    public function test_login_returns_short_lived_access_token_and_refresh_token(): void
    {
        $user = User::factory()->create(['role' => 'acopiador', 'active' => true]);

        $tokens = $this->loginAs($user);

        $this->assertSame(60 * 60, $tokens['expires_in']);
        $this->assertSame(60 * 24 * 30 * 60, $tokens['refresh_expires_in']);
        $this->assertEqualsWithDelta(now()->addHour()->timestamp, $user->tokens()->whereJsonContains('abilities', 'access')->first()->expires_at->timestamp, 5);
        $this->withToken($tokens['access_token'])->getJson(route('api.v1.auth.me'))->assertOk();
    }

    public function test_refresh_token_cannot_be_used_to_call_the_api(): void
    {
        $tokens = $this->loginAs(User::factory()->create(['role' => 'acopiador', 'active' => true]));

        $this->withToken($tokens['refresh_token'])->getJson(route('api.v1.auth.me'))->assertUnauthorized();
    }

    public function test_access_token_cannot_be_used_to_refresh(): void
    {
        $tokens = $this->loginAs(User::factory()->create(['role' => 'acopiador', 'active' => true]));

        $this->withToken($tokens['access_token'])->postJson(route('api.v1.auth.refresh'))->assertUnauthorized();
    }

    public function test_refresh_rotates_tokens_and_old_ones_stop_working(): void
    {
        $user = User::factory()->create(['role' => 'acopiador', 'active' => true]);
        $tokens = $this->loginAs($user);

        $newTokens = $this->withToken($tokens['refresh_token'])
            ->postJson(route('api.v1.auth.refresh'))
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->json('data');
        $this->app['auth']->forgetGuards();

        $this->assertNotSame($tokens['access_token'], $newTokens['access_token']);
        $this->withToken($newTokens['access_token'])->getJson(route('api.v1.auth.me'))->assertOk();
        $this->app['auth']->forgetGuards();
        $this->withToken($tokens['access_token'])->getJson(route('api.v1.auth.me'))->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->withToken($tokens['refresh_token'])->postJson(route('api.v1.auth.refresh'))->assertUnauthorized();
        $this->assertSame(2, $user->tokens()->count());
    }

    public function test_expired_access_token_is_rejected_but_refresh_still_works(): void
    {
        $tokens = $this->loginAs(User::factory()->create(['role' => 'acopiador', 'active' => true]));

        $this->travel(61)->minutes();

        $this->withToken($tokens['access_token'])->getJson(route('api.v1.auth.me'))->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->withToken($tokens['refresh_token'])->postJson(route('api.v1.auth.refresh'))->assertOk();
    }

    public function test_inactive_user_cannot_refresh(): void
    {
        $user = User::factory()->create(['role' => 'acopiador', 'active' => true]);
        $tokens = $this->loginAs($user);
        $user->update(['active' => false]);

        $this->withToken($tokens['refresh_token'])->postJson(route('api.v1.auth.refresh'))->assertForbidden();
    }

    public function test_logout_revokes_access_and_refresh_tokens_of_the_session(): void
    {
        $user = User::factory()->create(['role' => 'acopiador', 'active' => true]);
        $tokens = $this->loginAs($user);
        $otherDevice = $this->loginAs($user);

        $this->withToken($tokens['access_token'])->postJson(route('api.v1.auth.logout'))->assertNoContent();
        $this->app['auth']->forgetGuards();

        $this->withToken($tokens['refresh_token'])->postJson(route('api.v1.auth.refresh'))->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->withToken($otherDevice['access_token'])->getJson(route('api.v1.auth.me'))->assertOk();
    }

    public function test_resending_an_offline_delivery_with_the_same_client_uuid_does_not_duplicate_it(): void
    {
        $producer = Producer::factory()->create();
        Sanctum::actingAs($this->collectorFor($producer));
        $payload = ['producer_id' => $producer->id, 'liters' => 40, 'client_uuid' => (string) Str::uuid()];

        $firstId = $this->postJson(route('api.v1.deliveries.store'), $payload)->assertCreated()->json('data.id');
        $this->postJson(route('api.v1.deliveries.store'), $payload)
            ->assertOk()
            ->assertJsonPath('data.id', $firstId)
            ->assertJsonPath('data.client_uuid', $payload['client_uuid']);

        $this->assertSame(1, MilkDelivery::count());
    }

    public function test_client_uuid_of_another_collector_is_rejected(): void
    {
        $producer = Producer::factory()->create();
        $uuid = (string) Str::uuid();
        Sanctum::actingAs($this->collectorFor($producer));
        $this->postJson(route('api.v1.deliveries.store'), ['producer_id' => $producer->id, 'liters' => 40, 'client_uuid' => $uuid])
            ->assertCreated();

        Sanctum::actingAs($this->collectorFor($producer));
        $this->postJson(route('api.v1.deliveries.store'), ['producer_id' => $producer->id, 'liters' => 15, 'client_uuid' => $uuid])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('client_uuid');
    }

    public function test_offline_delivery_keeps_the_time_it_was_collected(): void
    {
        $this->travelTo(now()->setTime(18, 0));
        $producer = Producer::factory()->create();
        Sanctum::actingAs($this->collectorFor($producer));
        $collectedAt = now()->subDay()->setTime(6, 30);

        $this->postJson(route('api.v1.deliveries.store'), [
            'producer_id' => $producer->id,
            'liters' => 25,
            'client_uuid' => (string) Str::uuid(),
            'collected_at' => $collectedAt->copy()->utc()->toIso8601String(),
        ])
            ->assertCreated()
            ->assertJsonPath('data.delivery_date', $collectedAt->toDateString())
            ->assertJsonPath('data.delivery_time', '06:30');
    }

    public function test_offline_delivery_older_than_seven_days_or_in_the_future_is_rejected(): void
    {
        $producer = Producer::factory()->create();
        Sanctum::actingAs($this->collectorFor($producer));

        foreach ([now()->subDays(8), now()->addHour()] as $collectedAt) {
            $this->postJson(route('api.v1.deliveries.store'), [
                'producer_id' => $producer->id,
                'liters' => 25,
                'collected_at' => $collectedAt->toIso8601String(),
            ])->assertUnprocessable()->assertJsonValidationErrors('collected_at');
        }
    }
}
