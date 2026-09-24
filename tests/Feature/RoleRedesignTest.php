<?php

namespace Tests\Feature;

use App\Models\MilkDelivery;
use App\Models\Notification;
use App\Models\Producer;
use App\Models\Product;
use App\Models\QualityReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RoleRedesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_gerente_is_forbidden_from_admin_only_routes(): void
    {
        $gerente = User::factory()->create(['role' => 'gerente', 'active' => true]);
        $producer = Producer::factory()->create();

        $this->actingAs($gerente)->get(route('admin.users'))->assertStatus(403);
        $this->actingAs($gerente)->get(route('admin.producers-edit', $producer))->assertStatus(403);
        $this->actingAs($gerente)->get(route('admin.collectors'))->assertStatus(403);
        $this->actingAs($gerente)->get(route('admin.price'))->assertStatus(403);
    }

    public function test_admin_can_view_new_admin_only_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => true]);

        $this->actingAs($admin)->get(route('admin.collectors'))->assertOk();
        $this->actingAs($admin)->get(route('admin.collectors-show', $collector))->assertOk();
        $this->actingAs($admin)->get(route('admin.price'))->assertOk();
        $this->actingAs($admin)->get(route('admin.payments', ['tab' => 'acopiador']))->assertOk();
    }

    public function test_gerente_can_view_shared_read_only_routes(): void
    {
        $gerente = User::factory()->create(['role' => 'gerente', 'active' => true]);

        $this->actingAs($gerente)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($gerente)->get(route('admin.producers'))->assertOk();
        $this->actingAs($gerente)->get(route('admin.deliveries'))->assertOk();
    }

    public function test_admin_and_gerente_can_register_manual_delivery(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $gerente = User::factory()->create(['role' => 'gerente', 'active' => true]);
        $producer = Producer::factory()->create();

        $response = $this->actingAs($gerente)->post(route('admin.deliveries-store'), [
            'producer_id' => $producer->id,
            'liters' => 80,
            'delivery_date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('admin.deliveries'));
        $this->assertDatabaseHas('milk_deliveries', [
            'producer_id' => $producer->id,
            'collector_id' => $gerente->id,
            'liters' => 80,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.deliveries-store'), [
            'producer_id' => $producer->id,
            'liters' => 40,
            'delivery_date' => now()->toDateString(),
        ]);
        $response->assertRedirect(route('admin.deliveries'));
    }

    public function test_collector_sees_only_their_assigned_producers(): void
    {
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => true]);
        $otherCollector = User::factory()->create(['role' => 'acopiador', 'active' => true]);

        $myProducer = Producer::factory()->create();
        $otherProducer = Producer::factory()->create();

        // Asignación hecha por el admin en "Configurar Asignación".
        $collector->assignedProducers()->attach($myProducer->id);
        $otherCollector->assignedProducers()->attach($otherProducer->id);

        $response = $this->actingAs($collector)->get(route('collector.producers'));

        $response->assertOk();
        $response->assertSee($myProducer->code);
        $response->assertDontSee($otherProducer->code);

        // El formulario de registrar entrega debe respetar el mismo alcance.
        $createResponse = $this->actingAs($collector)->get(route('collector.delivery-create'));
        $createResponse->assertOk();
        $createResponse->assertSee($myProducer->code);
        $createResponse->assertDontSee($otherProducer->code);

        $this->actingAs($collector)->get(route('collector.journal'))->assertOk();
    }

    public function test_collector_without_routes_falls_back_to_same_community_producers(): void
    {
        $collector = User::factory()->create(['role' => 'acopiador', 'active' => true, 'comunidad' => 'Huata Centro']);

        $sameCommunity = Producer::factory()->create(['comunidad' => 'Huata Centro', 'status' => 'activo']);
        $otherCommunity = Producer::factory()->create(['comunidad' => 'Uco', 'status' => 'activo']);

        // El acopiador no tiene ninguna ruta asignada.
        $response = $this->actingAs($collector)->get(route('collector.producers'));

        $response->assertOk();
        $response->assertSee($sameCommunity->code);
        $response->assertDontSee($otherCommunity->code);
    }

    public function test_producer_can_mark_notification_as_read(): void
    {
        $producerUser = User::factory()->create(['role' => 'productor', 'active' => true]);
        Producer::factory()->create(['user_id' => $producerUser->id]);
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

        $notification = Notification::create([
            'target' => 'productores',
            'title' => 'Aviso de prueba',
            'message' => 'Mensaje de prueba',
            'priority' => 'normal',
            'created_by' => $admin->id,
            'is_active' => true,
        ]);

        $this->actingAs($producerUser)->get(route('producer.notifications'))->assertOk();

        $response = $this->actingAs($producerUser)->put(route('producer.notification-read', $notification));

        $response->assertRedirect();
        $this->assertDatabaseHas('notification_reads', [
            'notification_id' => $notification->id,
            'user_id' => $producerUser->id,
        ]);
        $this->assertTrue($notification->isReadBy($producerUser->fresh()));
    }

    public function test_plant_worker_can_create_production_batch(): void
    {
        $plantWorker = User::factory()->create(['role' => 'trabajador_planta', 'active' => true]);
        $product = Product::create([
            'name' => 'Queso Test', 'slug' => 'queso-test', 'sku' => 'QT-001',
            'category' => 'queso', 'description' => 'Test', 'unit_price' => 20, 'unit' => 'kg',
            'is_active' => true,
        ]);
        $producer = Producer::factory()->create();
        $delivery = MilkDelivery::factory()->create(['producer_id' => $producer->id, 'liters' => 100]);
        QualityReport::factory()->create(['milk_delivery_id' => $delivery->id, 'producer_id' => $producer->id, 'result' => 'aprobado']);

        $response = $this->actingAs($plantWorker)->post(route('plant.production-store'), [
            'items' => [
                ['product_id' => $product->id, 'output_units' => 10],
            ],
            'production_date' => now()->toDateString(),
            'status' => 'en_proceso',
            'milk_ids' => [$delivery->id],
        ]);

        $response->assertRedirect(route('plant.production', ['tab' => 'historial']));
        $this->assertDatabaseHas('production_batches', [
            'product_id' => $product->id,
            'input_milk_liters' => 100,
        ]);

        $this->actingAs($plantWorker)->get(route('plant.production', ['tab' => 'recetas']))->assertOk();
        $this->actingAs($plantWorker)->get(route('plant.sales', ['tab' => 'stock']))->assertOk();
        $this->actingAs($plantWorker)->get(route('plant.recipe-create'))->assertOk();
        $this->actingAs($plantWorker)->get(route('plant.sales-create'))->assertOk();
    }

    public function test_plant_worker_cannot_access_admin_only_routes(): void
    {
        $plantWorker = User::factory()->create(['role' => 'trabajador_planta', 'active' => true]);

        $this->actingAs($plantWorker)->get(route('admin.dashboard'))->assertStatus(403);
    }

    public function test_quality_ocr_scan_endpoint_returns_json_and_never_crashes_without_tesseract(): void
    {
        Storage::fake('public');
        $qualityUser = User::factory()->create(['role' => 'control_calidad', 'active' => true]);
        $photo = UploadedFile::fake()->image('ticket.jpg');

        $response = $this->actingAs($qualityUser)->post(route('quality.report-ocr-scan'), [
            'ticket_photo' => $photo,
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['ticket_photo_path', 'fields', 'ocr_available', 'message']);
    }
}
