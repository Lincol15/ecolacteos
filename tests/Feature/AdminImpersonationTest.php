<?php

namespace Tests\Feature;

use App\Http\Controllers\ImpersonationController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminImpersonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_system_as_another_user_and_return(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => true]);

        $this->actingAs($admin)
            ->post(route('admin.users-impersonate', $collector))
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($collector);
        $this->assertSame($admin->id, session(ImpersonationController::SESSION_KEY));

        $this->get(route('collector.dashboard'))
            ->assertOk()
            ->assertSee('Volver a mi cuenta de administrador');

        $this->post(route('impersonation.stop'))
            ->assertRedirect(route('admin.users'));

        $this->assertAuthenticatedAs($admin);
        $this->assertFalse(session()->has(ImpersonationController::SESSION_KEY));
    }

    public function test_admin_cannot_impersonate_another_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $otherAdmin = User::factory()->create(['role' => 'admin', 'active' => true]);

        $this->actingAs($admin)->post(route('admin.users-impersonate', $otherAdmin));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_cannot_impersonate_an_inactive_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $inactive = User::factory()->create(['role' => 'acopiador', 'active' => false]);

        $this->actingAs($admin)->post(route('admin.users-impersonate', $inactive));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_gerente_cannot_impersonate_users(): void
    {
        $gerente = User::factory()->create(['role' => 'gerente', 'active' => true]);
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => true]);

        $this->actingAs($gerente)
            ->post(route('admin.users-impersonate', $collector))
            ->assertForbidden();

        $this->assertAuthenticatedAs($gerente);
    }

    public function test_stopping_without_an_impersonation_session_logs_the_user_out(): void
    {
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => true]);

        $this->actingAs($collector)
            ->post(route('impersonation.stop'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
