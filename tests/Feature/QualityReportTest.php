<?php

namespace Tests\Feature;

use App\Models\MilkDelivery;
use App\Models\QualityReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QualityReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_quality_analyst_can_create_report(): void
    {
        $analyst = User::factory()->create(['role' => 'control_calidad', 'active' => true]);
        $delivery = MilkDelivery::factory()->create();

        $response = $this->actingAs($analyst)->post(route('quality.report-store'), [
            'milk_delivery_id' => $delivery->id,
            'sample_code' => 'TEST-001',
            'grasa_pct' => 3.5,
            'proteina_pct' => 3.2,
            'lactosa_pct' => 4.8,
            'ph' => 6.6,
            'result' => 'aprobado',
            'rejection_reason' => 'ninguno',
        ]);

        $response->assertRedirect(route('quality.reports'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('quality_reports', [
            'milk_delivery_id' => $delivery->id,
            'analyst_id' => $analyst->id,
            'result' => 'aprobado',
        ]);
    }

    public function test_delivery_status_updates_after_quality_analysis(): void
    {
        $analyst = User::factory()->create(['role' => 'control_calidad', 'active' => true]);
        $delivery = MilkDelivery::factory()->create(['status' => 'registrado']);

        $this->actingAs($analyst)->post(route('quality.report-store'), [
            'milk_delivery_id' => $delivery->id,
            'grasa_pct' => 3.5,
            'result' => 'aprobado',
            'rejection_reason' => 'ninguno',
        ]);

        $delivery->refresh();
        
        $this->assertTrue($delivery->has_quality_analysis);
        $this->assertEquals('analizado', $delivery->status);
    }

    public function test_rejected_delivery_updates_status_to_rejected(): void
    {
        $analyst = User::factory()->create(['role' => 'control_calidad', 'active' => true]);
        $delivery = MilkDelivery::factory()->create();

        $this->actingAs($analyst)->post(route('quality.report-store'), [
            'milk_delivery_id' => $delivery->id,
            'grasa_pct' => 2.5, // Baja
            'result' => 'rechazado',
            'rejection_reason' => 'baja_grasa',
        ]);

        $delivery->refresh();
        
        $this->assertEquals('rechazado', $delivery->status);
    }

    public function test_cannot_create_duplicate_quality_report(): void
    {
        $analyst = User::factory()->create(['role' => 'control_calidad', 'active' => true]);
        $delivery = MilkDelivery::factory()->create();
        
        QualityReport::factory()->create(['milk_delivery_id' => $delivery->id]);

        $response = $this->actingAs($analyst)->post(route('quality.report-store'), [
            'milk_delivery_id' => $delivery->id,
            'result' => 'aprobado',
            'rejection_reason' => 'ninguno',
        ]);

        $response->assertSessionHasErrors('milk_delivery_id');
    }

    public function test_quality_score_is_calculated_correctly(): void
    {
        $report = QualityReport::factory()->create([
            'grasa_pct' => 3.5,      // En rango (3.2-5.5)
            'proteina_pct' => 3.2,   // En rango (2.9-4.2)
            'lactosa_pct' => 4.8,    // En rango (4.5-5.2)
            'ph' => 6.6,             // En rango (6.4-6.8)
        ]);

        $score = $report->qualityScore();

        $this->assertGreaterThan(0, $score);
        $this->assertLessThanOrEqual(100, $score);
        $this->assertEquals(100.0, $score); // Todos los parámetros en rango
    }

    public function test_quality_score_with_out_of_range_parameters(): void
    {
        $report = QualityReport::factory()->create([
            'grasa_pct' => 2.8,      // Fuera de rango (bajo)
            'proteina_pct' => 3.2,   // En rango
            'lactosa_pct' => 4.8,    // En rango
            'ph' => 7.0,             // Fuera de rango (alto)
        ]);

        $score = $report->qualityScore();

        $this->assertLessThan(100, $score);
        $this->assertEquals(50.0, $score); // 2 de 4 en rango
    }

    public function test_producer_cannot_create_quality_report(): void
    {
        $producer = User::factory()->create(['role' => 'productor', 'active' => true]);
        $delivery = MilkDelivery::factory()->create();

        $response = $this->actingAs($producer)->post(route('quality.report-store'), [
            'milk_delivery_id' => $delivery->id,
            'result' => 'aprobado',
            'rejection_reason' => 'ninguno',
        ]);

        $response->assertStatus(403);
    }

    public function test_quality_analyst_can_view_all_reports(): void
    {
        $analyst = User::factory()->create(['role' => 'control_calidad', 'active' => true]);
        
        $report1 = QualityReport::factory()->create();
        $report2 = QualityReport::factory()->create();

        $response = $this->actingAs($analyst)->get(route('quality.reports'));

        $response->assertStatus(200);
    }
}
