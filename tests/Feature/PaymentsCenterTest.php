<?php

namespace Tests\Feature;

use App\Models\CollectorPayment;
use App\Models\Payment;
use App\Models\Producer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentsCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_producer_and_collector_payment_tabs(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $payment = Payment::factory()->create();
        $collectorPayment = CollectorPayment::factory()->create();

        $this->actingAs($admin)->get(route('admin.payments'))
            ->assertOk()
            ->assertSee($payment->producer->user->fullname)
            ->assertSee($payment->receipt_number);

        $this->actingAs($admin)->get(route('admin.payments', ['tab' => 'acopiadores']))
            ->assertOk()
            ->assertSee($collectorPayment->collector->fullname)
            ->assertSee($collectorPayment->receipt_number);
    }

    public function test_admin_generates_monthly_payroll_only_for_collectors_with_salary_and_once_per_month(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $withSalary = User::factory()->create(['role' => 'acopiador', 'active' => true, 'monthly_salary' => 1500]);
        $withoutSalary = User::factory()->create(['role' => 'acopiador', 'active' => true, 'monthly_salary' => null]);

        $this->actingAs($admin)->post(route('admin.collector-payments-generate'), ['month' => '2026-08'])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.collector-payments-generate'), ['month' => '2026-08'])->assertRedirect();

        $this->assertSame(1, CollectorPayment::count());
        $payment = CollectorPayment::first();
        $this->assertSame($withSalary->id, $payment->collector_id);
        $this->assertSame('1500.00', $payment->total_amount);
        $this->assertSame('2026-08-01', $payment->period_month->toDateString());
        $this->assertSame('pendiente', $payment->status);
        $this->assertDatabaseMissing('collector_payments', ['collector_id' => $withoutSalary->id]);
    }

    public function test_admin_can_set_salary_and_mark_collector_payment_as_paid(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $collectorPayment = CollectorPayment::factory()->create();

        $this->actingAs($admin)->put(route('admin.collector-salary-update', $collectorPayment->collector), ['monthly_salary' => 1800])
            ->assertRedirect();
        $this->assertSame('1800.00', $collectorPayment->collector->fresh()->monthly_salary);

        $this->actingAs($admin)->put(route('admin.collector-payment-mark-paid', $collectorPayment), [
            'payment_method' => 'efectivo',
            'transaction_number' => 'OP-123',
        ])->assertRedirect();

        $collectorPayment->refresh();
        $this->assertSame('pagado', $collectorPayment->status);
        $this->assertSame('efectivo', $collectorPayment->payment_method);
        $this->assertSame('OP-123', $collectorPayment->transaction_number);
    }

    public function test_gerente_can_view_payments_but_cannot_generate_payroll(): void
    {
        $gerente = User::factory()->create(['role' => 'gerente', 'active' => true]);

        $this->actingAs($gerente)->get(route('admin.payments', ['tab' => 'acopiadores']))->assertOk();
        $this->actingAs($gerente)->post(route('admin.collector-payments-generate'), ['month' => '2026-08'])->assertForbidden();
    }

    public function test_producer_can_view_and_download_only_their_own_receipts(): void
    {
        $producer = Producer::factory()->create();
        $ownPayment = Payment::factory()->create(['producer_id' => $producer->id]);
        $otherPayment = Payment::factory()->create();

        $this->actingAs($producer->user)->get(route('producer.payments'))
            ->assertOk()
            ->assertSee($ownPayment->receipt_number)
            ->assertDontSee($otherPayment->receipt_number);

        $this->actingAs($producer->user)->get(route('producer.payment-show', $ownPayment))
            ->assertOk()
            ->assertSee('Cómo se calculó tu pago');

        $this->actingAs($producer->user)->get(route('producer.payment-receipt', ['payment' => $ownPayment, 'download' => 1]))
            ->assertOk()
            ->assertSee($ownPayment->receipt_number)
            ->assertSee('Descargar PDF');

        $this->actingAs($producer->user)->get(route('producer.payment-receipt', $otherPayment))->assertForbidden();
    }

    public function test_collector_sees_their_monthly_salary_slips_only(): void
    {
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => true, 'monthly_salary' => 1300]);
        $ownPayment = CollectorPayment::factory()->create(['collector_id' => $collector->id, 'base_salary' => 1300, 'total_amount' => 1300]);
        $otherPayment = CollectorPayment::factory()->create();

        $this->actingAs($collector)->get(route('collector.payments'))
            ->assertOk()
            ->assertSee($ownPayment->receipt_number)
            ->assertSee('S/ 1,300.00')
            ->assertDontSee($otherPayment->receipt_number);

        $this->actingAs($collector)->get(route('collector.payment-receipt', $ownPayment))
            ->assertOk()
            ->assertSee('Boleta de pago mensual');

        $this->actingAs($collector)->get(route('collector.payment-receipt', $otherPayment))->assertForbidden();
    }
}
