<?php

namespace Tests\Feature\Api;

use App\Models\Complaint;
use App\Models\MilkDelivery;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Producer;
use App\Models\QualityReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileApiProducerAndQualityTest extends TestCase
{
    use RefreshDatabase;

    public function test_quality_analyst_registers_report_and_backend_evaluates_result(): void
    {
        $delivery = MilkDelivery::factory()->create(['status' => 'registrado', 'recibido' => false]);
        Sanctum::actingAs(User::factory()->create(['role' => 'control_calidad', 'active' => true]));

        $this->postJson(route('api.v1.quality-reports.store'), [
            'milk_delivery_id' => $delivery->id,
            'grasa_pct' => 2.8,
            'ph' => 6.6,
            'origen_datos' => 'manual',
        ])->assertCreated()
            ->assertJsonPath('data.result', 'rechazado')
            ->assertJsonPath('data.rejection_reason', 'baja_grasa')
            ->assertJsonPath('data.producer_id', $delivery->producer_id);

        $delivery->refresh();
        $this->assertSame('rechazado', $delivery->status);
        $this->assertTrue($delivery->recibido);
    }

    public function test_quality_report_out_of_range_values_return_422(): void
    {
        $producer = Producer::factory()->create();
        Sanctum::actingAs(User::factory()->create(['role' => 'control_calidad', 'active' => true]));

        $this->postJson(route('api.v1.quality-reports.store'), ['producer_id' => $producer->id, 'densidad' => 1028])
            ->assertUnprocessable()
            ->assertJsonPath('errors.densidad.0', 'El campo densidad no puede ser mayor a 1.040');
    }

    public function test_producer_only_sees_own_quality_reports_and_payments(): void
    {
        $producer = Producer::factory()->create();
        $mine = MilkDelivery::factory()->create(['producer_id' => $producer->id]);
        QualityReport::factory()->create(['milk_delivery_id' => $mine->id]);
        QualityReport::factory()->create();
        Payment::factory()->create(['producer_id' => $producer->id]);
        Payment::factory()->create();
        Sanctum::actingAs($producer->user);

        $this->getJson(route('api.v1.quality-reports.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.producer_id', $producer->id);

        $this->getJson(route('api.v1.payments.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.producer_id', $producer->id);
    }

    public function test_producer_sees_targeted_notifications_and_marks_them_read(): void
    {
        $producer = Producer::factory()->create();
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $visible = Notification::create([
            'target' => 'productores', 'title' => 'Cambio de precio', 'message' => 'Nuevo precio',
            'created_by' => $admin->id, 'published_at' => now(), 'is_active' => true,
        ]);
        Notification::create([
            'target' => 'acopiadores', 'title' => 'Solo acopio', 'message' => 'x',
            'created_by' => $admin->id, 'published_at' => now(), 'is_active' => true,
        ]);
        Sanctum::actingAs($producer->user);

        $this->getJson(route('api.v1.notifications.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.read', false);

        $this->patchJson(route('api.v1.notifications.read', $visible))
            ->assertOk()
            ->assertJsonPath('data.read', true);

        $this->getJson(route('api.v1.notifications.index'))->assertJsonPath('data.0.read', true);
    }

    public function test_producer_sends_complaint_and_admin_answers_it(): void
    {
        $producer = Producer::factory()->create();
        Sanctum::actingAs($producer->user);

        $id = $this->postJson(route('api.v1.complaints.store'), [
            'category' => 'pago',
            'priority' => 'normal',
            'subject' => 'Pago incompleto',
            'description' => 'Faltan litros del martes',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'abierto')
            ->json('data.id');

        Sanctum::actingAs(User::factory()->create(['role' => 'admin', 'active' => true]));
        $this->patchJson(route('api.v1.complaints.update', $id), [
            'status' => 'respondido',
            'staff_response' => 'Se corrigió el pago',
        ])->assertOk()
            ->assertJsonPath('data.staff_response', 'Se corrigió el pago');

        $this->assertNotNull(Complaint::find($id)->responded_at);
    }

    public function test_collector_cannot_send_complaints_or_read_payments(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'acopiador', 'active' => true]));

        $this->postJson(route('api.v1.complaints.store'), [])->assertForbidden();
        $this->getJson(route('api.v1.payments.index'))->assertForbidden();
    }
}
