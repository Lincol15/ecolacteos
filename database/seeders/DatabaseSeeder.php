<?php

namespace Database\Seeders;

use App\Models\CollectionRoute;
use App\Models\Complaint;
use App\Models\ContactMessage;
use App\Models\Ingredient;
use App\Models\IngredientMovement;
use App\Models\Inventory;
use App\Models\MilkDelivery;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\PlantConfig;
use App\Models\Producer;
use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\QualityReport;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RouteStop;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Sanction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $driver = DB::connection()->getDriverName();
        DB::statement($driver === 'sqlite' ? 'PRAGMA foreign_keys = OFF' : 'SET FOREIGN_KEY_CHECKS=0');

        $this->createUsers();
        $this->createProducers();
        $this->createPlantConfigs();
        $this->createNotifications();
        $this->createProducts();
        $this->createIngredientsAndRecipes();
        $this->createCollectionRoutes();
        $this->createMilkDeliveries();
        $this->createQualityReports();
        $this->createPayments();
        $this->createProductionBatches();
        $this->createSales();
        $this->createSanctions();
        $this->createComplaints();
        $this->createContactMessages();

        DB::statement($driver === 'sqlite' ? 'PRAGMA foreign_keys = ON' : 'SET FOREIGN_KEY_CHECKS=1');
    }

    private function createUsers(): void
    {
        $users = [
            [
                'name' => 'Carlos', 'lastname' => 'Admin',
                'email' => 'admin@ecolacteos.com', 'dni' => '00000001',
                'password' => Hash::make('admin123'), 'role' => 'admin',
                'phone' => '987654321', 'address' => 'Huata, Ancash',
            ],
            [
                'name' => 'María', 'lastname' => 'Gómez',
                'email' => 'gerente@ecolacteos.com', 'dni' => '00000002',
                'password' => Hash::make('gerente123'), 'role' => 'gerente',
                'phone' => '987654322', 'address' => 'Huata, Ancash',
            ],
            [
                'name' => 'Juan', 'lastname' => 'Pérez',
                'email' => 'acopiador@ecolacteos.com', 'dni' => '00000003',
                'password' => Hash::make('acopiador123'), 'role' => 'acopiador',
                'phone' => '987654323', 'address' => 'Huata, Ancash',
                'comunidad' => 'Huata Centro', 'vehiculo' => 'AB-1234',
            ],
            [
                'name' => 'Lucía', 'lastname' => 'Torres',
                'email' => 'calidad@ecolacteos.com', 'dni' => '00000004',
                'password' => Hash::make('calidad123'), 'role' => 'control_calidad',
                'phone' => '987654324', 'address' => 'Huata, Ancash',
            ],
            [
                'name' => 'Pedro', 'lastname' => 'Ramírez',
                'email' => 'planta@ecolacteos.com', 'dni' => '00000005',
                'password' => Hash::make('planta123'), 'role' => 'trabajador_planta',
                'phone' => '987654325', 'address' => 'Huata, Ancash',
            ],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(['email' => $u['email']], $u);
        }

        $producersNames = [
            ['name' => 'Don Alberto', 'lastname' => 'Quispe', 'dni' => '40123001', 'farm' => 'Finca El Paraíso'],
            ['name' => 'Doña Rosa', 'lastname' => 'Mamani', 'dni' => '40123002', 'farm' => 'Finca La Esperanza'],
            ['name' => 'Mario', 'lastname' => 'Castro', 'dni' => '40123003', 'farm' => 'Finca San José'],
            ['name' => 'Elena', 'lastname' => 'Vargas', 'dni' => '40123004', 'farm' => 'Finca Los Andes'],
            ['name' => 'Faustino', 'lastname' => 'López', 'dni' => '40123005', 'farm' => 'Finca Santa Rosa'],
            ['name' => 'Dionisia', 'lastname' => 'Chávez', 'dni' => '40123006', 'farm' => 'Finca El Olivar'],
            ['name' => 'Gregorio', 'lastname' => 'Sánchez', 'dni' => '40123007', 'farm' => 'Finca La Pradera'],
            ['name' => 'Natividad', 'lastname' => 'Rojas', 'dni' => '40123008', 'farm' => 'Finca El Horizonte'],
        ];

        $zones = ['Pueblo Libre', 'San Miguel', 'El Arenal', 'Ticapampa', 'Yanama', 'Uco', 'Caraz Bajo', 'Huata Centro'];

        foreach ($producersNames as $i => $p) {
            $email = 'productor'.($i + 1).'@ecolacteos.com';
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $p['name'],
                    'lastname' => $p['lastname'],
                    'dni' => $p['dni'],
                    'password' => Hash::make('productor123'),
                    'role' => 'productor',
                    'phone' => '98700'.str_pad((string) ($i + 10), 3, '0', STR_PAD_LEFT),
                    'address' => $p['farm'].', Huata',
                    'comunidad' => $zones[$i % count($zones)],
                ]
            );
        }
    }

    private function createProducers(): void
    {
        $zones = ['Pueblo Libre', 'San Miguel', 'El Arenal', 'Ticapampa', 'Yanama', 'Uco', 'Caraz Bajo', 'Huata Centro'];
        $userIndex = 0;
        User::where('role', 'productor')->get()->each(function (User $user) use ($zones, &$userIndex) {
            Producer::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'code' => 'PROD-'.str_pad((string) ($userIndex + 1), 3, '0', STR_PAD_LEFT),
                    'farm_name' => $user->address ?? 'Finca',
                    'zone' => $zones[$userIndex % count($zones)],
                    'comunidad' => $user->comunidad ?? $zones[$userIndex % count($zones)],
                    'district' => 'Huata',
                    'province' => 'Huata',
                    'region' => 'Ancash',
                    'latitude' => -9.5500 + ($userIndex * 0.01),
                    'longitude' => -77.2500 + ($userIndex * 0.01),
                    'cows_count' => rand(8, 35),
                    'daily_avg_liters' => rand(40, 180),
                    'status' => 'activo',
                    'registration_date' => now()->subMonths(rand(6, 36)),
                ]
            );
            $userIndex++;
        });
    }

    private function createPlantConfigs(): void
    {
        $configs = [
            ['key' => 'precio_litro_leche', 'label' => 'Precio por Litro de Leche (S/)', 'value' => '1.70', 'value_type' => 'number',
                'description' => 'Precio vigente por litro de leche pagado al productor'],
            ['key' => 'nombre_planta', 'label' => 'Nombre Planta Procesadora', 'value' => 'Ecolácteos Huata S.A.C.', 'value_type' => 'string'],
            ['key' => 'ruc_planta', 'label' => 'RUC Planta', 'value' => '20123456789', 'value_type' => 'string'],
            ['key' => 'direccion_planta', 'label' => 'Dirección Planta', 'value' => 'Av. Principal S/N, Huata, Ancash, Perú', 'value_type' => 'text'],
            ['key' => 'telefono_planta', 'label' => 'Teléfono Planta', 'value' => '+51 43 123456', 'value_type' => 'string'],
            ['key' => 'email_planta', 'label' => 'Email Contacto', 'value' => 'info@ecolacteoshuata.com', 'value_type' => 'string'],
            ['key' => 'bono_volumen_minimo_litros', 'label' => 'Mínimo Litros para Bono Volumen', 'value' => '500', 'value_type' => 'number'],
            ['key' => 'bono_calidad_minimo_score', 'label' => 'Score Mínimo para Bono Calidad (%)', 'value' => '90', 'value_type' => 'number'],
            ['key' => 'tolerancia_agua_pct', 'label' => 'Tolerancia Agua Añadida (%)', 'value' => '5', 'value_type' => 'number'],
            ['key' => 'dias_pago_semanal', 'label' => 'Día Pago Semanal', 'value' => 'Viernes', 'value_type' => 'string'],
            ['key' => 'litros_maximos_entrega', 'label' => 'Litros Máximos por Entrega', 'value' => '10000', 'value_type' => 'number',
                'description' => 'Cantidad máxima de litros permitida en un solo registro de acopio'],
        ];
        $admin = User::where('role', 'admin')->first();
        foreach ($configs as $c) {
            PlantConfig::updateOrCreate(
                ['key' => $c['key']],
                [...$c, 'updated_by' => $admin?->id]
            );
        }
    }

    private function createNotifications(): void
    {
        $admin = User::where('role', 'admin')->first();
        $notifications = [
            [
                'target' => 'productores', 'title' => 'Actualización Precio del Litro',
                'message' => 'Desde la semana del 15 de septiembre, el precio por litro de leche se actualiza a S/ 1.75 por el incremento en demanda nacional.',
                'priority' => 'alta',
            ],
            [
                'target' => 'todos', 'title' => 'Mantenimiento Planta 20 Septiembre',
                'message' => 'Se programó mantenimiento general de la planta el sábado 20 de septiembre de 8:00 a.m. a 2:00 p.m. No habrá recepción de leche en ese horario.',
                'priority' => 'normal',
            ],
            [
                'target' => 'acopiadores', 'title' => 'Nueva Ruta Sector Ticapampa',
                'message' => 'Se ha incorporado la ruta "Ticapampa Bajo" al itinerario. Revisar panel para asignación de paradas.',
                'priority' => 'urgente',
            ],
            [
                'target' => 'gerencia', 'title' => 'Reporte Quincenal Disponible',
                'message' => 'El reporte quincenal de producción y ventas ya se encuentra disponible en el panel de gerencia.',
                'priority' => 'normal',
            ],
        ];
        foreach ($notifications as $i => $n) {
            Notification::create([
                ...$n,
                'created_by' => $admin?->id,
                'published_at' => now()->subDays($i * 2),
            ]);
        }
    }

    private function createProducts(): void
    {
        $products = [
            [
                'name' => 'Queso Andino Huata',
                'slug' => 'queso-andino-huata',
                'sku' => 'QUES-AND-001',
                'category' => 'queso',
                'description' => 'Queso fresco artesanal elaborado con leche cruda de la mejor calidad de nuestros productores locales.',
                'long_description' => textQueso(),
                'unit_price' => 28.00, 'unit' => 'kg', 'emoji' => '🧀',
                'image_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=artesanal%20peruvian%20andes%20fresh%20cheese%20wheel%20on%20rustic%20wood%20table%20natural%20light%20professional%20food%20photography&image_size=square',
                'specifications' => ['Peso' => '1kg / 500g', 'Origen' => 'Huata, Ancash', 'Conservación' => 'Refrigeración 2-6°C', 'Vencimiento' => '21 días'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Yogur Natural de la Sierra',
                'slug' => 'yogur-natural-sierra',
                'sku' => 'YOG-NAT-002',
                'category' => 'yogur',
                'description' => 'Yogur cremoso 100% natural sin azúcares añadidos, elaborado diariamente en planta.',
                'long_description' => textYogur(),
                'unit_price' => 12.00, 'unit' => 'L', 'emoji' => '🥛',
                'image_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=natural%20peruvian%20yogurt%20glass%20jar%20with%20milk%20cows%20mountain%20background%20premium%20dairy%20photography&image_size=square',
                'specifications' => ['Presentación' => '1L / 500ml', 'Probióticos' => 'Sí', 'Azúcar' => '0g adicionada', 'Vencimiento' => '15 días'],
                'sort_order' => 2,
            ],
            [
                'name' => 'Mantequilla de Leche de Vaca',
                'slug' => 'mantequilla-artesanal',
                'sku' => 'MANT-ART-003',
                'category' => 'mantequilla',
                'description' => 'Mantequilla artesanal batida tradicionalmente, color dorado intenso y sabor característico de leche de la sierra.',
                'long_description' => textMantequilla(),
                'unit_price' => 22.00, 'unit' => 'kg', 'emoji' => '🧈',
                'image_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=artisanal%20golden%20butter%20pat%20on%20ceramic%20plate%20with%20wooden%20butter%20knife%20rustic%20kitchen%20professional%20photo&image_size=square',
                'specifications' => ['Grasa' => '82%', 'Sal' => 'Sin sal / con sal', 'Conservación' => 'Refrigeración', 'Vencimiento' => '60 días'],
                'sort_order' => 3,
            ],
            [
                'name' => 'Queso Mantecoso Semi Curado',
                'slug' => 'queso-mantecoso',
                'sku' => 'QUES-MAN-004',
                'category' => 'queso',
                'description' => 'Queso semi curado de textura cremosa y sabor suave, ideal para acompañar pan y empanadas.',
                'long_description' => 'Queso mantecoso con 30 días de curado, humedad media 45%.',
                'unit_price' => 35.00, 'unit' => 'kg', 'emoji' => '🧀',
                'image_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=half%20wheel%20of%20semi%20cured%20creamy%20artisan%20cheese%20on%20rustic%20wood%20with%20grapes%20and%20herbs&image_size=square',
                'specifications' => ['Curado' => '30 días', 'Textura' => 'Cremosa', 'Peso' => '1kg / 2kg', 'Vencimiento' => '90 días'],
                'sort_order' => 4,
            ],
            [
                'name' => 'Yogur con Frutas de la Zona',
                'slug' => 'yogur-con-frutas',
                'sku' => 'YOG-FRU-005',
                'category' => 'yogur',
                'description' => 'Delicioso yogur cremoso con trozos de frutas naturales de la región: lúcuma, guayaba y tumbo.',
                'long_description' => 'Elaborado con frutas 100% peruanas.',
                'unit_price' => 15.00, 'unit' => 'L', 'emoji' => '🥛',
                'image_url' => 'https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt=yogurt%20bowl%20with%20tropical%20lucuma%20and%20guava%20fruits%20healthy%20breakfast%20top%20view%20food%20photography&image_size=square',
                'specifications' => ['Frutas' => 'Lúcuma, Guayaba, Tumbo', 'Presentación' => '1L / 250ml', 'Azúcar' => 'Natural'],
                'sort_order' => 5,
            ],
        ];
        foreach ($products as $p) {
            Product::updateOrCreate(['sku' => $p['sku']], $p);
        }
    }

    private function createIngredientsAndRecipes(): void
    {
        $admin = User::where('role', 'admin')->first();

        $ingredients = [
            ['name' => 'Leche', 'unit' => 'L', 'is_milk' => true],
            ['name' => 'Sal', 'unit' => 'g', 'is_milk' => false, 'min_stock' => 500],
            ['name' => 'Cuajo', 'unit' => 'und', 'is_milk' => false, 'min_stock' => 100],
            ['name' => 'Azúcar', 'unit' => 'g', 'is_milk' => false, 'min_stock' => 1000],
        ];
        foreach ($ingredients as $i) {
            Ingredient::updateOrCreate(['name' => $i['name']], [...$i, 'active' => true]);
        }

        $stock = ['Azúcar' => 2000, 'Sal' => 1000, 'Cuajo' => 1000];
        foreach ($stock as $name => $quantity) {
            $ingredient = Ingredient::where('name', $name)->first();
            if ($ingredient && $ingredient->movements()->count() === 0) {
                IngredientMovement::create([
                    'ingredient_id' => $ingredient->id,
                    'movement_type' => 'entrada',
                    'quantity' => $quantity,
                    'processed_by' => $admin?->id,
                    'notes' => 'Stock inicial',
                ]);
            }
        }

        $leche = Ingredient::where('name', 'Leche')->first();
        $sal = Ingredient::where('name', 'Sal')->first();
        $cuajo = Ingredient::where('name', 'Cuajo')->first();
        $azucar = Ingredient::where('name', 'Azúcar')->first();

        $queso = Product::where('sku', 'QUES-AND-001')->first();
        if ($queso && ! Recipe::where('product_id', $queso->id)->exists()) {
            $recipe = Recipe::create([
                'product_id' => $queso->id,
                'name' => 'Queso Fresco 1kg',
                'milk_liters_per_unit' => 5,
                'instructions' => 'Cuajar la leche, cortar la cuajada, prensar y salar.',
                'active' => true,
                'created_by' => $admin?->id,
            ]);
            RecipeIngredient::create(['recipe_id' => $recipe->id, 'ingredient_id' => $leche->id, 'quantity_per_unit' => 5]);
            RecipeIngredient::create(['recipe_id' => $recipe->id, 'ingredient_id' => $sal->id, 'quantity_per_unit' => 50]);
            RecipeIngredient::create(['recipe_id' => $recipe->id, 'ingredient_id' => $cuajo->id, 'quantity_per_unit' => 1]);
        }

        $yogur = Product::where('sku', 'YOG-NAT-002')->first();
        if ($yogur && ! Recipe::where('product_id', $yogur->id)->exists()) {
            $recipe = Recipe::create([
                'product_id' => $yogur->id,
                'name' => 'Yogur Natural 1L',
                'milk_liters_per_unit' => 1,
                'instructions' => 'Pasteurizar la leche, inocular cultivos, incubar y enfriar.',
                'active' => true,
                'created_by' => $admin?->id,
            ]);
            RecipeIngredient::create(['recipe_id' => $recipe->id, 'ingredient_id' => $leche->id, 'quantity_per_unit' => 1]);
            RecipeIngredient::create(['recipe_id' => $recipe->id, 'ingredient_id' => $azucar->id, 'quantity_per_unit' => 100]);
        }
    }

    private function createCollectionRoutes(): void
    {
        $collector = User::where('role', 'acopiador')->first();
        $producers = Producer::all();

        $routes = [
            ['name' => 'Ruta Norte - Huata Centro', 'code' => 'RUT-N-01', 'day' => 'Lunes', 'start_time' => '06:00', 'end_time' => '12:00',
                'vehicle_plate' => 'AB-1234', 'estimated_distance_km' => 32.5],
            ['name' => 'Ruta Sur - Pueblo Libre', 'code' => 'RUT-S-02', 'day' => 'Martes', 'start_time' => '06:30', 'end_time' => '12:30',
                'vehicle_plate' => 'AB-1234', 'estimated_distance_km' => 45.0],
            ['name' => 'Ruta Este - Ticapampa', 'code' => 'RUT-E-03', 'day' => 'Miércoles', 'start_time' => '05:30', 'end_time' => '13:00',
                'vehicle_plate' => 'CD-5678', 'estimated_distance_km' => 58.2],
            ['name' => 'Ruta Oeste - San Miguel', 'code' => 'RUT-O-04', 'day' => 'Jueves', 'start_time' => '06:00', 'end_time' => '11:30',
                'vehicle_plate' => 'CD-5678', 'estimated_distance_km' => 38.7],
            ['name' => 'Ruta Especial Viernes', 'code' => 'RUT-V-05', 'day' => 'Viernes', 'start_time' => '05:00', 'end_time' => '14:00',
                'vehicle_plate' => 'EF-9012', 'estimated_distance_km' => 72.0],
        ];

        foreach ($routes as $idx => $r) {
            $route = CollectionRoute::create([
                ...$r,
                'collector_id' => $collector?->id,
                'status' => $idx < 2 ? 'completada' : ($idx === 2 ? 'en_curso' : 'planeada'),
                'description' => "Ruta programada para recolección sector {$r['day']}",
            ]);

            $stops = $producers->shuffle()->take(4 + $idx);
            $stopOrder = 1;
            $hour = (int) explode(':', $r['start_time'])[0];
            foreach ($stops as $p) {
                RouteStop::create([
                    'collection_route_id' => $route->id,
                    'producer_id' => $p->id,
                    'stop_order' => $stopOrder++,
                    'estimated_arrival' => sprintf('%02d:%02d', $hour + (int) ($stopOrder / 2), 15 * ($stopOrder % 4)),
                    'estimated_liters' => max(30, $p->daily_avg_liters * (0.9 + (lcg_value() * 0.3))),
                    'special_instructions' => $stopOrder === 1 ? 'Ingresar por portón lateral de la finca' : null,
                    'status' => $route->status === 'completada' ? 'visitado'
                        : ($route->status === 'en_curso' && $stopOrder < 3 ? 'visitado' : 'pendiente'),
                    'visited_at' => in_array($route->status, ['completada', 'en_curso'], true) && $stopOrder < 3
                        ? now()->subDays($idx)->addMinutes($stopOrder * 45)
                        : null,
                ]);
            }
        }
    }

    private function createMilkDeliveries(): void
    {
        $collector = User::where('role', 'acopiador')->first();
        $qualityUser = User::where('role', 'control_calidad')->first();
        $producers = Producer::with('user')->get();
        $pricePerLiter = 1.70;

        foreach (range(-30, 0) as $dayOffset) {
            if (in_array(date('w', strtotime("{$dayOffset} day")), [0], true)) {
                continue;
            }
            $producers->random(rand(5, 8))->each(function (Producer $p) use ($dayOffset, $collector, $qualityUser, $pricePerLiter) {
                $liters = round(max(25, $p->daily_avg_liters * (0.85 + (lcg_value() * 0.35))), 2);
                $routeStop = RouteStop::where('producer_id', $p->id)
                    ->inRandomOrder()
                    ->first();
                $status = lcg_value() > 0.1 ? (lcg_value() > 0.4 ? 'analizado' : 'aceptado') : 'rechazado';
                $delivery = MilkDelivery::create([
                    'producer_id' => $p->id,
                    'collector_id' => $collector?->id,
                    'route_stop_id' => $routeStop?->id,
                    'collection_route_id' => $routeStop?->collection_route_id,
                    'zona' => $p->comunidad ?? $p->zone,
                    'recibido' => $status !== 'registrado',
                    'liters' => $liters,
                    'temperature' => round(4.5 + (lcg_value() * 4), 2),
                    'price_per_liter' => $pricePerLiter,
                    'total_amount' => round($liters * $pricePerLiter, 2),
                    'delivery_date' => Carbon::now()->addDays($dayOffset)->toDateString(),
                    'delivery_time' => Carbon::now()->addDays($dayOffset)->setTime(rand(6, 10), rand(0, 59)),
                    'container_type' => ['caneca', 'bidon', 'cisterna'][array_rand(['caneca', 'bidon', 'cisterna'])],
                    'containers_count' => rand(1, 5),
                    'vehicle_plate' => ['AB-1234', 'CD-5678', 'EF-9012'][array_rand(['AB-1234', 'CD-5678', 'EF-9012'])],
                    'observations' => lcg_value() > 0.8 ? 'Leche con buena temperatura al momento de recojo.' : null,
                    'has_quality_analysis' => lcg_value() > 0.2,
                    'status' => $status,
                    'approved_by' => $qualityUser?->id,
                    'approved_at' => Carbon::now()->addDays($dayOffset)->addHours(rand(1, 4)),
                ]);
            });
        }
    }

    private function createQualityReports(): void
    {
        $analyst = User::where('role', 'control_calidad')->first();
        MilkDelivery::where('has_quality_analysis', true)->whereDoesntHave('qualityReport')
            ->chunk(100, function ($deliveries) use ($analyst) {
                foreach ($deliveries as $d) {
                    $grasa = round(3.0 + (lcg_value() * 2.2), 2);
                    $proteina = round(2.8 + (lcg_value() * 1.3), 2);
                    $lactosa = round(4.3 + (lcg_value() * 0.9), 2);
                    $sng = round(8.0 + (lcg_value() * 1.5), 2);
                    $total_solidos = round($grasa + $sng, 2);
                    $agua = lcg_value() > 0.9 ? round(2 + lcg_value() * 8, 2) : round(lcg_value() * 3, 2);
                    $ph = round(6.2 + (lcg_value() * 0.7), 2);
                    $congelacion = round(-0.56 + (lcg_value() * 0.06), 3);
                    $rejected = $agua > 5 || $grasa < 2.9 || $congelacion > -0.50;
                    QualityReport::create([
                        'milk_delivery_id' => $d->id,
                        'producer_id' => $d->producer_id,
                        'analyst_id' => $analyst?->id,
                        'sample_code' => 'MUE-'.$d->id.'-'.strtoupper(substr(md5((string) $d->id), 0, 4)),
                        'temperatura' => $d->temperature,
                        'origen_datos' => lcg_value() > 0.85 ? 'ocr_corregido' : (lcg_value() > 0.5 ? 'ocr' : 'manual'),
                        'grasa_pct' => $grasa,
                        'proteina_pct' => $proteina,
                        'lactosa_pct' => $lactosa,
                        'solidos_no_grasos_pct' => $sng,
                        'total_solidos_pct' => $total_solidos,
                        'agua_aniadida_pct' => $agua,
                        'ph' => $ph,
                        'punto_congelacion' => $congelacion,
                        'acidez_dornic' => rand(14, 20),
                        'densidad' => round(1.028 + (lcg_value() * 0.010), 3),
                        'result' => $rejected ? 'rechazado' : (lcg_value() > 0.7 ? 'observado' : 'aprobado'),
                        'rejection_reason' => $rejected
                            ? match (true) {
                                $agua > 5 => 'exceso_agua',
                                $grasa < 2.9 => 'baja_grasa',
                                default => 'ph_anormal',
                            }
                            : 'ninguno',
                        'analyzed_at' => $d->approved_at?->addHour() ?? $d->created_at->addHour(),
                        'observations' => $rejected ? 'Muestra fuera de especificación.' : 'Parámetros dentro de rango normal.',
                    ]);
                }
            });
    }

    private function createPayments(): void
    {
        $producers = Producer::all();
        $gerente = User::where('role', 'gerente')->first();

        foreach (range(1, 4) as $week) {
            $end = Carbon::now()->startOfWeek()->subWeeks($week - 1)->endOfWeek();
            $start = $end->copy()->subDays(6);
            foreach ($producers as $p) {
                $deliveries = $p->milkDeliveries()
                    ->whereBetween('delivery_date', [$start->toDateString(), $end->toDateString()])
                    ->get();
                if ($deliveries->isEmpty()) {
                    continue;
                }
                $liters = $deliveries->sum('liters');
                $base = $deliveries->sum('total_amount');
                $qualityBonus = $liters > 200 ? round($base * 0.05, 2) : 0;
                $volumeBonus = $liters > 500 ? round($base * 0.03, 2) : 0;
                $deductions = lcg_value() > 0.7 ? round($base * 0.01, 2) : 0;
                $llevadoAPlanta = round($deliveries->whereNull('collector_id')->sum('total_amount'), 2);
                $total = round($base + $qualityBonus + $volumeBonus - $deductions - $llevadoAPlanta, 2);

                $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                $detalleDiario = array_fill_keys($dias, 0.0);
                foreach ($deliveries as $d) {
                    $detalleDiario[$dias[Carbon::parse($d->delivery_date)->dayOfWeekIso - 1]] += (float) $d->liters;
                }

                $payment = Payment::create([
                    'producer_id' => $p->id,
                    'period_code' => 'SEM-'.$start->format('Y').'-W'.$start->weekOfYear,
                    'period_start' => $start,
                    'period_end' => $end,
                    'payment_date' => $end->copy()->addDay(),
                    'total_liters' => $liters,
                    'detalle_diario' => array_map(fn ($v) => round($v, 2), $detalleDiario),
                    'avg_price_per_liter' => $liters > 0 ? round($base / $liters, 2) : 1.70,
                    'precio_por_litro' => 1.70,
                    'base_amount' => $base,
                    'quality_bonus' => $qualityBonus,
                    'production_bonus' => $volumeBonus,
                    'deductions' => $deductions,
                    'llevado_a_planta' => $llevadoAPlanta,
                    'total_amount' => $total,
                    'deductions_detail' => $deductions > 0 ? 'Aporte fondo solidario 1%' : null,
                    'bonus_detail' => ($qualityBonus > 0 ? "Bono calidad S/{$qualityBonus} " : '')
                        .($volumeBonus > 0 ? "Bono volumen S/{$volumeBonus}" : '') ?: null,
                    'payment_method' => 'transferencia',
                    'transaction_number' => 'TXN'.strtoupper(substr(md5((string) $p->id.$week), 0, 10)),
                    'status' => lcg_value() > 0.3 ? 'pagado' : (lcg_value() > 0.5 ? 'procesando' : (lcg_value() > 0.5 ? 'parcial' : 'pendiente')),
                    'processed_by' => $gerente?->id,
                    'notes' => 'Liquidación semanal automática.',
                ]);

                foreach ($deliveries as $d) {
                    PaymentItem::create([
                        'payment_id' => $payment->id,
                        'milk_delivery_id' => $d->id,
                        'liters' => $d->liters,
                        'price_per_liter' => $d->price_per_liter,
                        'line_amount' => $d->total_amount,
                    ]);
                }
            }
        }
    }

    private function createProductionBatches(): void
    {
        $products = Product::where('category', '!=', 'leche')->get();
        $plant = User::where('role', 'trabajador_planta')->first();
        $deliveries = MilkDelivery::where('status', '!=', 'rechazado')->limit(50)->get();
        $batchIndex = 1;

        foreach (range(1, 12) as $idx) {
            $product = $products->random();
            $input = round(200 + lcg_value() * 800, 2);
            $yieldFactor = match ($product->category) {
                'queso' => 0.11,
                'yogur' => 0.95,
                'mantequilla' => 0.05,
                default => 0.5,
            };
            $output = round($input * $yieldFactor, 2);
            $prodDate = Carbon::now()->subDays($idx * 2);
            $batch = ProductionBatch::create([
                'batch_number' => 'LOTE-'.$prodDate->format('Ymd').'-'.str_pad((string) $batchIndex, 3, '0', STR_PAD_LEFT),
                'product_id' => $product->id,
                'input_milk_liters' => $input,
                'output_units' => $output,
                'yield_percentage' => round(($output / $input) * 100, 2),
                'production_date' => $prodDate,
                'expiration_date' => $prodDate->copy()->addDays(match ($product->category) {
                    'queso' => 60, 'yogur' => 15, 'mantequilla' => 45, default => 30,
                }),
                'supervised_by' => $plant?->id,
                'recipe_notes' => 'Producción estándar, turno mañana.',
                'quality_notes' => 'Control sensorial aprobado.',
                'status' => $idx <= 2 ? 'en_proceso' : ($idx <= 5 ? 'curando' : ($idx <= 10 ? 'terminado' : 'vendido')),
                'started_at' => $prodDate->copy()->setTime(7, 30),
                'finished_at' => $idx > 2 ? $prodDate->copy()->addHours(rand(4, 8))->setTime(15, 0) : null,
            ]);
            $remaining = $input;
            $deliveries->random(rand(3, 7))->each(function ($d) use ($batch, &$remaining) {
                if ($remaining <= 0) {
                    return;
                }
                $used = min(round(50 + lcg_value() * 150, 2), $remaining);
                $batch->milkDeliveries()->attach($d->id, ['liters_used' => $used]);
                $remaining -= $used;
            });

            Inventory::create([
                'production_batch_id' => $batch->id,
                'product_id' => $product->id,
                'quantity' => $output,
                'unit' => $product->unit,
                'movement_type' => 'entrada',
                'unit_cost' => round(($product->unit_price ?? 20) * 0.65, 2),
                'total_value' => round($output * (($product->unit_price ?? 20) * 0.65), 2),
                'location' => 'Cámara Principal',
                'expiration_date' => $batch->expiration_date,
                'reference_document' => $batch->batch_number,
                'processed_by' => $plant?->id,
                'notes' => 'Ingreso de lote de producción.',
            ]);

            if ($batch->status === 'vendido') {
                Inventory::create([
                    'production_batch_id' => $batch->id,
                    'product_id' => $product->id,
                    'quantity' => $output,
                    'unit' => $product->unit,
                    'movement_type' => 'salida',
                    'unit_cost' => round(($product->unit_price ?? 20) * 0.65, 2),
                    'total_value' => round($output * (($product->unit_price ?? 20) * 0.65), 2),
                    'location' => 'Cámara Principal',
                    'reference_document' => 'Salida por venta',
                    'processed_by' => $plant?->id,
                    'notes' => 'Salida a punto de venta.',
                ]);
            }
            $batchIndex++;
        }
    }

    private function createSales(): void
    {
        $products = Product::all();
        $gerente = User::where('role', 'gerente')->first();
        $clientes = [
            ['client_name' => 'Corporación Alimentos del Perú S.A.', 'dni' => '20501234561', 'type' => 'mayorista'],
            ['client_name' => 'Supermercados El Hogar', 'dni' => '20409876543', 'type' => 'mayorista'],
            ['client_name' => 'Ana María Córdova', 'dni' => '40234567', 'type' => 'mostrador'],
            ['client_name' => 'Luis Alberto Díaz', 'dni' => '40345678', 'type' => 'mostrador'],
            ['client_name' => 'Restaurante La Choza Huatana', 'dni' => '20601122334', 'type' => 'delivery'],
            ['client_name' => 'Rosa Elena Gutiérrez', 'dni' => '40456789', 'type' => 'mostrador'],
        ];

        foreach (range(1, 20) as $i) {
            $cliente = $clientes[array_rand($clientes)];
            $itemsCount = rand(1, 4);
            $items = [];
            $subtotal = 0;
            $date = Carbon::now()->subDays(rand(0, 25));
            for ($k = 0; $k < $itemsCount; $k++) {
                $product = $products->random();
                $qty = round(rand(1, 12) + lcg_value(), 2);
                $price = $product->unit_price;
                $line = round($qty * $price, 2);
                $subtotal += $line;
                $batch = $product->productionBatches()->where('status', 'terminado')->inRandomOrder()->first();
                $items[] = [
                    'product_id' => $product->id,
                    'production_batch_id' => $batch?->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $line,
                ];
            }
            $tax = round($subtotal * 0.18, 2);
            $discount = lcg_value() > 0.8 ? round($subtotal * 0.05, 2) : 0;
            $sale = Sale::create([
                'invoice_number' => 'FV-'.$date->format('Ymd').'-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'client_name' => $cliente['client_name'],
                'client_dni_ruc' => $cliente['dni'],
                'client_phone' => '9'.str_pad((string) rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'client_address' => 'Huata, Ancash',
                'sale_date' => $date,
                'subtotal' => round($subtotal, 2),
                'tax' => $tax,
                'discount' => $discount,
                'total_amount' => round($subtotal + $tax - $discount, 2),
                'payment_status' => lcg_value() > 0.3 ? 'pagado' : (lcg_value() > 0.5 ? 'pendiente' : 'parcial'),
                'payment_method' => ['efectivo', 'transferencia', 'yape', 'plin'][array_rand(['efectivo', 'transferencia', 'yape', 'plin'])],
                'sale_type' => $cliente['type'],
                'served_by' => $gerente?->id,
                'notes' => 'Venta generada automáticamente.',
            ]);
            foreach ($items as $it) {
                SaleItem::create(['sale_id' => $sale->id, ...$it]);
            }
        }
    }

    private function createSanctions(): void
    {
        $producers = Producer::limit(3)->get();
        $gerente = User::where('role', 'gerente')->first();
        $sanctions = [
            ['type' => 'calidad', 'motivo' => 'Adulteración con agua detectada', 'status' => 'activa', 'amount' => 50.00],
            ['type' => 'pesaje', 'motivo' => 'Diferencia reiterada en pesaje de caneca', 'status' => 'cumplida', 'amount' => 20.00],
            ['type' => 'incumplimiento', 'motivo' => 'Ausencia sin aviso en 3 recojos consecutivos', 'status' => 'anulada', 'amount' => null],
        ];
        foreach ($sanctions as $idx => $s) {
            $p = $producers[$idx % $producers->count()];
            Sanction::create([
                'producer_id' => $p->id,
                'issued_by' => $gerente?->id,
                'type' => $s['type'],
                'motivo' => $s['motivo'],
                'description' => 'Sanción aplicada según reglamento interno de acopio.',
                'amount' => $s['amount'],
                'status' => $s['status'],
                'sanction_date' => now()->subDays(($idx + 1) * 5),
                'resolved_date' => $s['status'] !== 'activa' ? now()->subDays($idx) : null,
            ]);
        }
    }

    private function createComplaints(): void
    {
        $producers = Producer::limit(4)->get();
        $gerente = User::where('role', 'gerente')->first();
        $ticket = 1;
        $complaints = [
            ['category' => 'precio', 'subject' => 'Desacuerdo con precio del litro',
                'description' => 'Considero que el precio actual de S/ 1.70 no refleja el costo de alimentación del ganado en esta temporada.',
                'priority' => 'alta', 'status' => 'en_revision'],
            ['category' => 'pesaje', 'subject' => 'Diferencia en pesaje de caneca',
                'description' => 'El día martes la lectura del pesaje fue menor a lo estimado en 8 litros. Solicito verificación.',
                'priority' => 'normal', 'status' => 'respondido'],
            ['category' => 'pago', 'subject' => 'Pago semanal no abonado',
                'description' => 'Hasta la fecha no he recibido la liquidación de la semana del 25 de agosto.',
                'priority' => 'urgente', 'status' => 'abierto'],
            ['category' => 'ruta', 'subject' => 'Horario de recojo muy temprano',
                'description' => 'Solicito que la ruta P-02 pase 30 minutos más tarde para completar el ordeño.',
                'priority' => 'baja', 'status' => 'cerrado'],
        ];
        foreach ($complaints as $idx => $c) {
            $p = $producers[$idx % $producers->count()];
            Complaint::create([
                'ticket_number' => 'TK-'.Carbon::now()->format('Y').'-'.str_pad((string) $ticket++, 5, '0', STR_PAD_LEFT),
                'producer_id' => $p->id,
                'user_id' => $p->user_id,
                'category' => $c['category'],
                'subject' => $c['subject'],
                'description' => $c['description'],
                'priority' => $c['priority'],
                'status' => $c['status'],
                'assigned_to' => $gerente?->id,
                'staff_response' => in_array($c['status'], ['respondido', 'cerrado'], true)
                    ? 'Estimado productor, hemos recibido su reclamo y estamos gestionando una respuesta oficial en un plazo de 48 horas hábiles.'
                    : null,
                'responded_at' => in_array($c['status'], ['respondido', 'cerrado'], true) ? now()->subDays($idx + 1) : null,
                'closed_at' => $c['status'] === 'cerrado' ? now()->subDays($idx) : null,
            ]);
        }
    }

    private function createContactMessages(): void
    {
        ContactMessage::create([
            'name' => 'José Luis Mendoza',
            'email' => 'jlmendoza@correo.pe',
            'phone' => '999888777',
            'subject' => 'Proveedor Institucional',
            'message' => 'Somos un hospital de la región y queremos comprar lácteos a largo plazo. Favor enviar catálogo y precios de mayorista.',
        ]);
        ContactMessage::create([
            'name' => 'Mariana Quispe',
            'email' => 'mquispe@gmail.com',
            'phone' => '966555444',
            'subject' => 'Punto de Venta en Lima',
            'message' => '¿Cuándo abrirán un local en Lima? Sus productos son deliciosos y sería genial contar con ellos en la capital.',
        ]);
    }
}

function textQueso(): string
{
    return 'El Queso Andino Huata es elaborado artesanalmente a partir de leche cruda fresca recolectada diariamente de fincas familiares de la zona alta de Huata. Con un proceso de cuajado tradicional y prensado manual, mantiene todas las propiedades organolépticas de la leche de nuestros productores locales. Perfecto para consumo fresco, sandwiches, platos típicos y la tradicional picada peruana.';
}

function textYogur(): string
{
    return 'El Yogur Natural de la Sierra Ecolácteos Huata es pasteurizado y fermentado con cultivos lácticos vivos. Sin azúcares, colorantes ni conservantes añadidos. Cremosidad característica y sabor suave, ideal para desayunos nutritivos, postres saludables y dietas balanceadas. Apto para toda la familia.';
}

function textMantequilla(): string
{
    return 'Elaborada con nata de la más alta calidad, batida lentamente siguiendo métodos tradicionales en nuestra planta procesadora. La Mantequilla Ecolácteos Huata destaca por su intenso color dorado y aroma a mantequilla fresca. Versátil en la cocina: untar en pan, repostería fina, sauces y preparaciones gourmet.';
}
