<?php

namespace Tests\Feature\Api;

use App\Models\MilkDelivery;
use App\Models\Payment;
use App\Models\PlantConfig;
use App\Models\Producer;
use App\Models\QualityReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileApiAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'active' => true]);
    }

    public function test_admin_creates_collector_and_toggles_status_but_not_his_own(): void
    {
        $admin = $this->admin();
        Sanctum::actingAs($admin);

        $id = $this->postJson(route('api.v1.users.store'), [
            'name' => 'Luis', 'lastname' => 'Quispe', 'dni' => '44556677', 'email' => 'luis@example.com',
            'role' => 'acopiador', 'comunidad' => 'Huata Centro', 'vehiculo' => 'MOT-01',
            'password' => 'secreto1', 'password_confirmation' => 'secreto1',
        ])->assertCreated()->assertJsonPath('data.role', 'acopiador')->json('data.id');

        $this->patchJson(route('api.v1.users.update-status', $id), ['active' => false])
            ->assertOk()->assertJsonPath('data.active', false);

        $this->patchJson(route('api.v1.users.update-status', $admin), ['active' => false])->assertStatus(422);
    }

    public function test_admin_assigns_individual_producers_and_collector_sees_them(): void
    {
        Sanctum::actingAs($this->admin());
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => true]);
        $producers = Producer::factory()->count(2)->create();

        $this->putJson(route('api.v1.collectors.update-assignment', $collector), [
            'producer_ids' => [$producers[0]->id],
        ])->assertOk()
            ->assertJsonPath('data.individual', true)
            ->assertJsonPath('data.producer_ids', [$producers[0]->id]);

        Sanctum::actingAs($collector);
        $this->getJson(route('api.v1.producers.index'))->assertJsonCount(1, 'data');
    }

    public function test_dashboard_reports_backend_stats_for_manager(): void
    {
        MilkDelivery::factory()->create(['delivery_date' => now()->toDateString(), 'liters' => 40]);
        Sanctum::actingAs(User::factory()->create(['role' => 'gerente', 'active' => true]));

        $this->getJson(route('api.v1.dashboard'))
            ->assertOk()
            ->assertJsonPath('data.liters_today', 40)
            ->assertJsonPath('data.deliveries_count', 1);
    }

    public function test_admin_updates_price_through_config_group(): void
    {
        Sanctum::actingAs($this->admin());
        $settings = collect(PlantConfig::groupsWithValues()['acopio']['settings'])->map(fn ($s) => $s['value'])->all();
        $settings['precio_litro_leche'] = '1.85';

        $this->putJson(route('api.v1.config.update-group', 'acopio'), ['settings' => $settings])
            ->assertOk()
            ->assertJsonPath('data.acopio.settings.precio_litro_leche.value', '1.85');

        $this->assertEquals(1.85, PlantConfig::getValue('precio_litro_leche'));
    }

    public function test_manager_cannot_change_config_or_users(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'gerente', 'active' => true]));

        $this->putJson(route('api.v1.config.update-group', 'acopio'), ['settings' => []])->assertForbidden();
        $this->getJson(route('api.v1.users.index'))->assertForbidden();
    }

    public function test_admin_publishes_notification_visible_to_producers(): void
    {
        Sanctum::actingAs($this->admin());
        $this->postJson(route('api.v1.notifications.store'), [
            'target' => 'productores', 'title' => 'Reunión', 'message' => 'Sábado 9am', 'priority' => 'normal',
        ])->assertCreated();

        Sanctum::actingAs(Producer::factory()->create()->user);
        $this->getJson(route('api.v1.notifications.index'))->assertJsonPath('data.0.title', 'Reunión');
    }

    public function test_admin_corrects_quality_result_and_delivery_follows(): void
    {
        $report = QualityReport::factory()->create(['result' => 'aprobado']);
        Sanctum::actingAs($this->admin());

        $this->patchJson(route('api.v1.quality-reports.update', $report), ['result' => 'rechazado'])
            ->assertUnprocessable()->assertJsonValidationErrors('rejection_reason');

        $this->patchJson(route('api.v1.quality-reports.update', $report), ['result' => 'rechazado', 'rejection_reason' => 'exceso_agua'])
            ->assertOk()->assertJsonPath('data.result', 'rechazado');

        $this->assertSame('rechazado', $report->milkDelivery->fresh()->status);
    }

    public function test_admin_registers_and_resolves_sanction(): void
    {
        $producer = Producer::factory()->create();
        Sanctum::actingAs($this->admin());

        $id = $this->postJson(route('api.v1.sanctions.store'), [
            'producer_id' => $producer->id, 'type' => 'calidad', 'motivo' => 'Advertencia: agua añadida',
            'sanction_date' => now()->toDateString(),
        ])->assertCreated()->assertJsonPath('data.status', 'activa')->json('data.id');

        $this->patchJson(route('api.v1.sanctions.update', $id), ['status' => 'cumplida'])
            ->assertOk()->assertJsonPath('data.status', 'cumplida');
    }

    public function test_admin_previews_generates_and_pays_producer_payment(): void
    {
        $producer = Producer::factory()->create();
        MilkDelivery::factory()->create([
            'producer_id' => $producer->id, 'delivery_date' => now()->toDateString(),
            'liters' => 100, 'status' => 'aceptado',
        ]);
        Sanctum::actingAs($this->admin());
        $period = ['period_start' => now()->startOfWeek()->toDateString(), 'period_end' => now()->endOfWeek()->toDateString()];

        $preview = collect($this->getJson(route('api.v1.payments.preview', $period))->assertOk()->json('data'))
            ->firstWhere('producer_id', $producer->id);
        $this->assertTrue($preview['can_generate']);
        $this->assertEquals(100, $preview['total_liters']);

        $this->postJson(route('api.v1.payments.generate'), [...$period, 'producer_ids' => [$producer->id]])
            ->assertCreated()->assertJsonPath('data.generated', 1);

        $payment = Payment::where('producer_id', $producer->id)->firstOrFail();
        $this->patchJson(route('api.v1.payments.mark-paid', $payment), ['payment_method' => 'efectivo'])
            ->assertOk()->assertJsonPath('data.status', 'pagado');
    }

    public function test_admin_generates_collector_payroll(): void
    {
        User::factory()->create(['role' => 'acopiador', 'active' => true, 'monthly_salary' => 1200]);
        Sanctum::actingAs($this->admin());

        $this->postJson(route('api.v1.collector-payments.generate'), ['month' => now()->format('Y-m')])
            ->assertCreated()->assertJsonPath('data.generated', 1);

        $this->getJson(route('api.v1.collector-payments.index'))
            ->assertOk()->assertJsonPath('data.0.total_amount', 1200);
    }
}
