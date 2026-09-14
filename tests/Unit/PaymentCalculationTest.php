<?php

namespace Tests\Unit;

use App\Models\MilkDelivery;
use App\Models\Producer;
use App\Models\QualityReport;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = new PaymentService();
    }

    public function test_payment_calculation_for_producer_with_deliveries(): void
    {
        $producer = Producer::factory()->create();
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        // Crear 5 entregas de 100L cada una a S/1.70
        MilkDelivery::factory()->count(5)->create([
            'producer_id' => $producer->id,
            'liters' => 100,
            'price_per_liter' => 1.70,
            'total_amount' => 170,
            'delivery_date' => $start->copy()->addDays(1),
            'status' => 'aceptado',
        ]);

        $result = $this->paymentService->calculatePayment($producer, $start, $end);

        $this->assertTrue($result['can_generate']);
        $this->assertEquals(500, $result['total_liters']);
        $this->assertEquals(850, $result['base_amount']);
    }

    public function test_quality_bonus_is_applied_correctly(): void
    {
        $producer = Producer::factory()->create();
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        // Crear 10 entregas con análisis de calidad aprobado
        for ($i = 0; $i < 10; $i++) {
            $delivery = MilkDelivery::factory()->create([
                'producer_id' => $producer->id,
                'liters' => 50,
                'price_per_liter' => 1.70,
                'total_amount' => 85,
                'delivery_date' => $start->copy()->addDays($i % 7),
                'status' => 'analizado',
                'has_quality_analysis' => true,
            ]);

            QualityReport::factory()->create([
                'milk_delivery_id' => $delivery->id,
                'result' => 'aprobado',
            ]);
        }

        $result = $this->paymentService->calculatePayment($producer, $start, $end);

        // Con >90% aprobado, debe tener bono de calidad (5%)
        $this->assertGreaterThan(0, $result['quality_bonus']);
        $this->assertEquals(round(850 * 0.05, 2), $result['quality_bonus']);
    }

    public function test_volume_bonus_is_applied_for_high_volume(): void
    {
        $producer = Producer::factory()->create();
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        // Crear entregas que sumen más de 500 litros
        MilkDelivery::factory()->create([
            'producer_id' => $producer->id,
            'liters' => 600, // Más de 500L
            'price_per_liter' => 1.70,
            'total_amount' => 1020,
            'delivery_date' => $start->copy()->addDay(),
            'status' => 'aceptado',
        ]);

        $result = $this->paymentService->calculatePayment($producer, $start, $end);

        // Con >500L, debe tener bono de volumen (3%)
        $this->assertGreaterThan(0, $result['production_bonus']);
        $this->assertEquals(round(1020 * 0.03, 2), $result['production_bonus']);
    }

    public function test_deductions_are_applied_correctly(): void
    {
        $producer = Producer::factory()->create();
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        MilkDelivery::factory()->create([
            'producer_id' => $producer->id,
            'liters' => 100,
            'price_per_liter' => 1.70,
            'total_amount' => 170,
            'delivery_date' => $start->copy()->addDay(),
            'status' => 'aceptado',
        ]);

        $result = $this->paymentService->calculatePayment($producer, $start, $end);

        // Debe tener deducción del 1%
        $this->assertEquals(round(170 * 0.01, 2), $result['deductions']);
    }

    public function test_total_amount_calculation_is_correct(): void
    {
        $producer = Producer::factory()->create();
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        MilkDelivery::factory()->create([
            'producer_id' => $producer->id,
            'liters' => 100,
            'price_per_liter' => 2.00,
            'total_amount' => 200,
            'delivery_date' => $start->copy()->addDay(),
            'status' => 'aceptado',
        ]);

        $result = $this->paymentService->calculatePayment($producer, $start, $end);

        $expected = $result['base_amount'] 
            + $result['quality_bonus'] 
            + $result['production_bonus'] 
            - $result['deductions'];

        $this->assertEquals($expected, $result['total_amount']);
    }

    public function test_payment_cannot_be_generated_without_deliveries(): void
    {
        $producer = Producer::factory()->create();
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        $result = $this->paymentService->calculatePayment($producer, $start, $end);

        $this->assertFalse($result['can_generate']);
        $this->assertArrayHasKey('reason', $result);
    }

    public function test_rejected_deliveries_are_excluded_from_payment(): void
    {
        $producer = Producer::factory()->create();
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        // Entrega aceptada
        MilkDelivery::factory()->create([
            'producer_id' => $producer->id,
            'liters' => 100,
            'price_per_liter' => 1.70,
            'total_amount' => 170,
            'delivery_date' => $start->copy()->addDay(),
            'status' => 'aceptado',
        ]);

        // Entrega rechazada (no debe contarse)
        MilkDelivery::factory()->create([
            'producer_id' => $producer->id,
            'liters' => 50,
            'price_per_liter' => 1.70,
            'total_amount' => 85,
            'delivery_date' => $start->copy()->addDays(2),
            'status' => 'rechazado',
        ]);

        $result = $this->paymentService->calculatePayment($producer, $start, $end);

        // Solo debe contar la entrega aceptada
        $this->assertEquals(100, $result['total_liters']);
        $this->assertEquals(170, $result['base_amount']);
    }

    public function test_average_price_per_liter_is_calculated_correctly(): void
    {
        $producer = Producer::factory()->create();
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        MilkDelivery::factory()->create([
            'producer_id' => $producer->id,
            'liters' => 100,
            'price_per_liter' => 1.50,
            'total_amount' => 150,
            'delivery_date' => $start->copy()->addDay(),
            'status' => 'aceptado',
        ]);

        MilkDelivery::factory()->create([
            'producer_id' => $producer->id,
            'liters' => 100,
            'price_per_liter' => 2.00,
            'total_amount' => 200,
            'delivery_date' => $start->copy()->addDays(2),
            'status' => 'aceptado',
        ]);

        $result = $this->paymentService->calculatePayment($producer, $start, $end);

        // Promedio: (150 + 200) / 200L = 1.75
        $this->assertEquals(1.75, $result['avg_price_per_liter']);
    }
}
