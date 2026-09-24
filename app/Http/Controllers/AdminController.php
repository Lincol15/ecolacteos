<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMilkDeliveryRequest;
use App\Http\Requests\StoreProducerRequest;
use App\Http\Requests\StoreProductionCartRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateProducerRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\CollectionRoute;
use App\Models\CollectorPayment;
use App\Models\Complaint;
use App\Models\Customer;
use App\Models\Ingredient;
use App\Models\IngredientMovement;
use App\Models\Inventory;
use App\Models\MilkDelivery;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\PlantConfig;
use App\Models\Producer;
use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\QualityReport;
use App\Models\Recipe;
use App\Models\RouteStop;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Sanction;
use App\Models\User;
use App\Services\CollectorPayrollService;
use App\Services\MilkDeliveryService;
use App\Services\PaymentService;
use App\Services\ProductionBatchService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();
        $startMonth = $today->copy()->startOfMonth();
        $startWeek = $today->copy()->startOfWeek();

        $stats = [
            'liters_today' => MilkDelivery::where('delivery_date', $today->toDateString())->sum('liters'),
            'liters_month' => MilkDelivery::whereBetween('delivery_date', [$startMonth, $today])->sum('liters'),
            'producers_active' => Producer::where('status', 'activo')->count(),
            'batches_active' => ProductionBatch::whereIn('status', ['en_proceso', 'curando'])->count(),
            'sales_today' => Sale::where('sale_date', $today->toDateString())->sum('total_amount'),
            'sales_month' => Sale::whereBetween('sale_date', [$startMonth, $today])->sum('total_amount'),
            'complaints_open' => Complaint::whereIn('status', ['abierto', 'en_revision'])->count(),
            'milk_price' => PlantConfig::getValue('precio_litro_leche', 1.70),
            'deliveries_count' => MilkDelivery::where('delivery_date', $today->toDateString())->count(),
            'deliveries_received' => MilkDelivery::where('delivery_date', $today->toDateString())->where('recibido', true)->count(),
            'deliveries_in_transit' => MilkDelivery::where('delivery_date', $today->toDateString())->where('recibido', false)->count(),
        ];

        $collectionLabels = [];
        $collectionLiters = [];
        $salesMonthly = [];
        $salesAmounts = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = $today->copy()->subDays($i);
            $collectionLabels[] = $day->format('d');
            $collectionLiters[] = MilkDelivery::where('delivery_date', $day->toDateString())->sum('liters');
        }
        for ($i = 5; $i >= 0; $i--) {
            $month = $today->copy()->subMonths($i)->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            $salesMonthly[] = $month->shortLocaleMonth;
            $salesAmounts[] = Sale::whereBetween('sale_date', [$month, $monthEnd])->sum('total_amount');
        }

        $recentDeliveries = MilkDelivery::with('producer.user', 'collector', 'qualityReport')
            ->latest()->limit(8)->get();
        $recentSales = Sale::with('items.product')->latest()->limit(6)->get();
        $notifications = Notification::visibleForUser(Auth::user())->limit(5)->get();
        $openComplaints = Complaint::with('producer.user')
            ->whereIn('status', ['abierto', 'en_revision'])->limit(5)->latest()->get();

        $milkByProductor = Producer::where('status', 'activo')
            ->withSum(['milkDeliveries as month_liters' => fn ($q) => $q->whereBetween('delivery_date', [$startMonth, $today])], 'liters')
            ->orderByDesc('month_liters')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'collectionLabels', 'collectionLiters',
            'salesMonthly', 'salesAmounts', 'recentDeliveries',
            'recentSales', 'notifications', 'openComplaints', 'milkByProductor'
        ));
    }

    public function users(Request $request)
    {
        $query = User::with('producer');
        if ($role = $request->role) {
            $query->where('role', $role);
        }
        if ($search = $request->search) {
            $query->where(fn ($q) => $q
                ->where(DB::raw('LOWER(name)'), 'like', '%'.strtolower($search).'%')
                ->orWhere(DB::raw('LOWER(lastname)'), 'like', '%'.strtolower($search).'%')
                ->orWhere(DB::raw('LOWER(email)'), 'like', '%'.strtolower($search).'%')
                ->orWhere('dni', 'like', "%{$search}%"));
        }
        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function userCreate()
    {
        return view('admin.users-create');
    }

    public function userStore(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['active'] = $request->boolean('active', true);

        $user = User::create($data);

        if ($user->isProducer()) {
            Producer::create([
                'user_id' => $user->id,
                'code' => 'PROD-'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT),
                'farm_name' => $data['name'],
                'zone' => $data['comunidad'] ?? 'Huata',
                'comunidad' => $data['comunidad'] ?? null,
                'district' => 'Huata',
                'province' => 'Huata',
                'region' => 'Ancash',
                'status' => 'activo',
                'registration_date' => now(),
            ]);
        }

        return redirect()->route('admin.users')->with('success', 'Usuario creado correctamente.');
    }

    public function userEdit(User $user)
    {
        return view('admin.users-edit', compact('user'));
    }

    public function userUpdate(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active', true);
        $user->update($data);

        return back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function producers(Request $request)
    {
        $query = Producer::with('user');
        if ($search = $request->search) {
            $like = '%'.strtolower($search).'%';
            $query->where(function ($q) use ($search, $like) {
                $q->whereHas('user', fn ($uq) => $uq
                    ->where(DB::raw('LOWER(name)'), 'like', $like)
                    ->orWhere('dni', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%"))
                    ->orWhere('comunidad', 'like', "%{$search}%");
            });
        }
        if ($zone = $request->zone) {
            $query->where('zone', $zone);
        }
        if ($comunidad = $request->comunidad) {
            $query->where('comunidad', $comunidad);
        }

        $counts = [
            'total' => (clone $query)->count(),
            'activos' => (clone $query)->where('status', 'activo')->count(),
            'inactivos' => (clone $query)->where('status', 'inactivo')->count(),
        ];

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        $producers = $query->latest()->paginate(15)->withQueryString();
        $zones = Producer::distinct()->pluck('zone')->filter()->sort()->values();
        $comunidades = Producer::distinct()->pluck('comunidad')->filter()->sort()->values();

        return view('admin.producers', compact('producers', 'zones', 'comunidades', 'counts'));
    }

    public function producerCreate()
    {
        return view('admin.producers-create');
    }

    public function producerStore(StoreProducerRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'lastname' => $data['lastname'] ?? null,
                'dni' => $data['dni'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'comunidad' => $data['comunidad'],
                'password' => Hash::make($data['password']),
                'role' => 'productor',
                'active' => true,
            ]);

            Producer::create([
                'user_id' => $user->id,
                'code' => 'PROD-'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT),
                'farm_name' => $data['farm_name'] ?? $data['name'],
                'zone' => $data['comunidad'],
                'comunidad' => $data['comunidad'],
                'district' => 'Huata',
                'province' => 'Huata',
                'region' => 'Ancash',
                'cows_count' => $data['cows_count'] ?? 0,
                'daily_avg_liters' => $data['daily_avg_liters'] ?? 0,
                'status' => 'activo',
                'registration_date' => now(),
            ]);
        });

        return redirect()->route('admin.producers')->with('success', 'Productor creado correctamente.');
    }

    public function producerEdit(Producer $producer)
    {
        return view('admin.producers-edit', compact('producer'));
    }

    public function producerUpdate(UpdateProducerRequest $request, Producer $producer)
    {
        $producer->update($request->validated());

        return back()->with('success', 'Productor actualizado correctamente.');
    }

    public function producerToggleStatus(Producer $producer)
    {
        $producer->update([
            'status' => $producer->status === 'activo' ? 'inactivo' : 'activo',
        ]);

        return back()->with('success', 'Estado del productor actualizado correctamente.');
    }

    public function deliveries(Request $request)
    {
        $query = MilkDelivery::with('producer.user', 'collector', 'qualityReport', 'collectionRoute');
        if ($search = $request->search) {
            $query->whereHas('producer.user', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('dni', 'like', "%{$search}%"));
        }
        if ($producer_id = $request->producer_id) {
            $query->where('producer_id', $producer_id);
        }
        if ($from = $request->from) {
            $query->where('delivery_date', '>=', $from);
        }
        if ($to = $request->to) {
            $query->where('delivery_date', '<=', $to);
        }
        if ($status = $request->status) {
            $query->where('status', $status);
        }
        if ($collector_id = $request->collector_id) {
            $query->where('collector_id', $collector_id);
        }
        if ($zona = $request->zona) {
            $query->where('zona', $zona);
        }
        if ($request->filled('recibido')) {
            $query->where('recibido', $request->boolean('recibido'));
        }
        $deliveries = $query->latest('delivery_date')->paginate(25)->withQueryString();
        $totals = [
            'liters' => $deliveries->total() > 0 ? (clone $query)->sum('liters') : 0,
            'amount' => $deliveries->total() > 0 ? (clone $query)->sum('total_amount') : 0,
        ];
        $collectors = User::where('role', 'acopiador')->get();
        $producers = Producer::where('status', 'activo')->with('user')->orderBy('code')->get();
        $zones = MilkDelivery::distinct()->pluck('zona')->filter()->sort()->values();

        return view('admin.deliveries', compact('deliveries', 'totals', 'collectors', 'producers', 'zones'));
    }

    public function deliveryCreate()
    {
        $producers = Producer::where('status', 'activo')->with('user')->orderBy('code')->get();
        $collectors = User::where('role', 'acopiador')->where('active', true)->get();

        return view('admin.deliveries-create', compact('producers', 'collectors'));
    }

    public function deliveryStore(StoreMilkDeliveryRequest $request, MilkDeliveryService $deliveryService)
    {
        $collectorId = $request->integer('collector_id') ?: Auth::id();

        try {
            $delivery = $deliveryService->create($request->validated(), $collectorId);

            return redirect()
                ->route('admin.deliveries')
                ->with('success', "Entrega de {$delivery->liters} L registrada correctamente.");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al registrar entrega: '.$e->getMessage());
        }
    }

    public function collectors()
    {
        $today = Carbon::today()->toDateString();
        $collectors = User::where('role', 'acopiador')
            ->withSum(['milkDeliveriesAsCollector as today_liters' => fn ($q) => $q->where('delivery_date', $today)], 'liters')
            ->withCount(['milkDeliveriesAsCollector as today_deliveries' => fn ($q) => $q->where('delivery_date', $today)])
            ->orderBy('name')
            ->get();

        return view('admin.collectors', compact('collectors'));
    }

    public function collectorShow(User $collector)
    {
        if ($collector->role !== 'acopiador') {
            abort(404);
        }
        $today = Carbon::today()->toDateString();
        $todayDeliveries = MilkDelivery::where('collector_id', $collector->id)
            ->where('delivery_date', $today)
            ->with('producer.user')
            ->latest()->get();
        $assignedProducers = $collector->resolveAssignedProducers();
        $explicitAssignedIds = $collector->assignedProducers()->pluck('producers.id');
        $assignedIds = $assignedProducers->pluck('id');
        $allActiveProducers = Producer::where('status', 'activo')->with('user')->orderBy('comunidad')->get();
        $allComunidades = Producer::distinct()->pluck('comunidad')->filter()->sort()->values();

        return view('admin.collectors-show', compact(
            'collector', 'todayDeliveries', 'assignedProducers', 'explicitAssignedIds', 'assignedIds', 'allActiveProducers', 'allComunidades'
        ));
    }

    public function collectorAssignmentUpdate(Request $request, User $collector)
    {
        if ($collector->role !== 'acopiador') {
            abort(404);
        }
        $data = $request->validate([
            'comunidad' => 'nullable|string|max:150',
            'producer_ids' => 'nullable|array',
            'producer_ids.*' => 'integer|exists:producers,id',
        ]);
        $comunidad = $data['comunidad'] ?? null;
        $selectedIds = collect($data['producer_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->sort()->values();
        $comunidadIds = $comunidad
            ? Producer::where('status', 'activo')->where('comunidad', $comunidad)->pluck('id')->sort()->values()
            : collect();

        $collector->update(['comunidad' => $comunidad]);

        // Si lo marcado es exactamente la comunidad, no se guarda lista individual:
        // así los productores nuevos de esa comunidad se asignan solos.
        $collector->assignedProducers()->sync(
            $comunidad && $selectedIds->all() === $comunidadIds->all() ? [] : $selectedIds->all()
        );

        $total = $collector->resolveAssignedProducers()->count();

        return back()->with('success', "Asignación guardada: {$total} productor(es) asignado(s) a {$collector->fullname}.");
    }

    public function price()
    {
        $config = PlantConfig::firstOrCreate(
            ['key' => 'precio_litro_leche'],
            ['label' => 'Precio por Litro de Leche', 'value' => '1.70', 'value_type' => 'number']
        );

        return view('admin.price', compact('config'));
    }

    public function priceUpdate(Request $request)
    {
        $data = $request->validate([
            'value' => 'required|numeric|min:0.1|max:100',
        ]);
        $config = PlantConfig::where('key', 'precio_litro_leche')->first();
        $config->update(['value' => $data['value'], 'updated_by' => Auth::id()]);

        return back()->with('success', 'Precio por litro actualizado a S/ '.number_format($data['value'], 2).'.');
    }

    public function quality(Request $request)
    {
        $query = QualityReport::with('milkDelivery.producer.user', 'producer.user', 'analyst');
        if ($from = $request->from) {
            $query->where('analyzed_at', '>=', $from);
        }
        if ($to = $request->to) {
            $query->where('analyzed_at', '<=', $to);
        }
        if ($result = $request->result) {
            $query->where('result', $result);
        }
        if ($producer_id = $request->producer_id) {
            $query->where('producer_id', $producer_id);
        }
        $reports = $query->latest()->paginate(20)->withQueryString();
        $producers = Producer::where('status', 'activo')->with('user')->orderBy('code')->get();

        return view('admin.quality', compact('reports', 'producers'));
    }

    public function production()
    {
        $batches = ProductionBatch::with('product', 'supervisor')
            ->latest('production_date')->paginate(15);
        $stats = [
            'total_batches' => ProductionBatch::count(),
            'in_process' => ProductionBatch::where('status', 'en_proceso')->count(),
            'curing' => ProductionBatch::where('status', 'curando')->count(),
            'finished' => ProductionBatch::where('status', 'terminado')->count(),
        ];

        return view('admin.production', compact('batches', 'stats'));
    }

    public function productionCreate(ProductionBatchService $service)
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $recipesForJs = $service->recipesForJs(Recipe::where('active', true)->with('nonMilkIngredients.ingredient')->get());
        $supervisors = User::whereIn('role', ['trabajador_planta', 'gerente', 'admin'])->get();
        // La leche registrada por el acopiador queda disponible de inmediato; solo se descarta la rechazada.
        $deliveries = MilkDelivery::where('status', '!=', 'rechazado')
            ->whereDoesntHave('batches')
            ->with('producer.user')
            ->latest()->limit(50)->get();
        $ingredientStocks = Ingredient::where('active', true)->where('is_milk', false)->get()
            ->map(fn ($i) => ['id' => $i->id, 'name' => $i->name, 'unit' => $i->unit, 'stock' => $i->currentStock()])->values();

        return view('admin.production-create', compact('products', 'recipesForJs', 'supervisors', 'deliveries', 'ingredientStocks'));
    }

    public function productionStore(StoreProductionCartRequest $request, ProductionBatchService $service)
    {
        $data = $request->validated();

        try {
            $batches = $service->createCart(
                $data['items'],
                $data['milk_ids'] ?? null,
                [
                    'production_date' => $data['production_date'],
                    'status' => $data['status'],
                    'supervised_by' => $data['supervised_by'] ?? null,
                    'recipe_notes' => $data['recipe_notes'] ?? null,
                    'quality_notes' => $data['quality_notes'] ?? null,
                ],
                Auth::id()
            );
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $message = $batches->count() > 1
            ? "Se crearon {$batches->count()} lotes de producción correctamente."
            : 'Lote de producción creado correctamente.';

        return redirect()->route('admin.production')->with('success', $message);
    }

    public function ingredients(Request $request)
    {
        $ingredients = Ingredient::where('active', true)->withCount('recipeIngredients')->orderBy('is_milk', 'desc')->orderBy('name')->get();
        $ingredientMovements = IngredientMovement::with('ingredient', 'processedBy', 'productionBatch')
            ->whereHas('ingredient', fn ($q) => $q->where('is_milk', false))
            ->latest()->limit(30)->get();
        $milkReport = MilkDelivery::receivedByDay($request->milk_from, $request->milk_to);

        return view('admin.ingredients', compact('ingredients', 'ingredientMovements', 'milkReport'));
    }

    public function ingredientStore(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('ingredients', 'name')->where('active', true)],
            'unit' => 'required|in:L,ml,kg,g,und',
            'min_stock' => 'nullable|numeric|min:0',
        ]);

        // Si existía un insumo eliminado (desactivado) con el mismo nombre, se reactiva con su historial.
        $removed = Ingredient::where('name', $data['name'])->where('active', false)->first();
        if ($removed) {
            $removed->update([...$data, 'active' => true]);
        } else {
            Ingredient::create([...$data, 'active' => true, 'is_milk' => false]);
        }

        return back()->with('success', 'Insumo registrado correctamente.');
    }

    /**
     * Elimina un insumo. La leche no se puede eliminar y un insumo usado en recetas
     * debe quitarse primero de ellas. Si tiene movimientos se desactiva para conservar
     * el historial; si no, se borra definitivamente.
     */
    public function ingredientDestroy(Ingredient $ingredient)
    {
        if ($ingredient->is_milk) {
            return back()->with('error', 'La leche es un insumo del sistema y no se puede eliminar.');
        }

        $recipeNames = $ingredient->recipeIngredients()->with('recipe')->get()->pluck('recipe.name')->filter()->unique();
        if ($recipeNames->isNotEmpty()) {
            return back()->with('error', "No se puede eliminar \"{$ingredient->name}\": se usa en la(s) receta(s) {$recipeNames->implode(', ')}. Quítalo primero de esas recetas.");
        }

        if ($ingredient->movements()->exists()) {
            $ingredient->update(['active' => false]);
        } else {
            $ingredient->delete();
        }

        return back()->with('success', "Insumo \"{$ingredient->name}\" eliminado correctamente.");
    }

    public function ingredientAdjust(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|exists:ingredients,id',
            'items.*.movement_type' => 'required|in:entrada,salida,ajuste,merma',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.notes' => 'nullable|required_if:items.*.movement_type,ajuste,merma|string|max:500',
        ], [
            'items.required' => 'Debe agregar al menos un insumo al carrito de compra.',
            'items.*.notes.required_if' => 'Debe indicar una observación para movimientos de ajuste o merma.',
        ]);

        $milkIngredientIds = Ingredient::where('is_milk', true)->pluck('id');
        if (collect($data['items'])->pluck('ingredient_id')->intersect($milkIngredientIds)->isNotEmpty()) {
            return back()->withInput()->with('error', 'El stock de leche se actualiza automáticamente cuando Control de Calidad analiza cada entrega.');
        }

        DB::transaction(function () use ($data) {
            foreach ($data['items'] as $item) {
                IngredientMovement::create([...$item, 'processed_by' => Auth::id()]);
            }
        });

        $count = count($data['items']);
        $message = $count > 1 ? "Se registraron {$count} movimientos de insumos correctamente. Ya están disponibles en Producción." : 'Compra de insumo registrada. Ya está disponible en Producción.';

        return back()->with('success', $message);
    }

    public function products(Request $request)
    {
        $query = Product::query();
        if ($category = $request->category) {
            $query->where('category', $category);
        }
        if ($search = $request->search) {
            $query->where('name', 'like', "%{$search}%");
        }
        $products = $query->orderBy('sort_order')->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.products', compact('products'));
    }

    public function productsCreate()
    {
        return view('admin.products-create');
    }

    public function productStore(Request $request)
    {
        $data = $this->validateProduct($request);
        $data['slug'] = $this->uniqueProductSlug($data['name']);
        $data['sku'] = $this->uniqueProductSku($data['category']);
        $data['specifications'] = $this->parseProductSpecifications($request);
        if ($request->hasFile('image')) {
            $data['image_url'] = Storage::url($request->file('image')->store('products', 'public'));
        }

        Product::create($data);

        return redirect()->route('admin.products')->with('success', 'Producto creado correctamente.');
    }

    public function productsEdit(Product $product)
    {
        return view('admin.products-edit', compact('product'));
    }

    public function productUpdate(Request $request, Product $product)
    {
        $data = $this->validateProduct($request, $product);
        $data['specifications'] = $this->parseProductSpecifications($request);
        if ($request->hasFile('image')) {
            $data['image_url'] = Storage::url($request->file('image')->store('products', 'public'));
        }

        $product->update($data);

        return redirect()->route('admin.products')->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateProduct(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'category' => 'required|in:'.implode(',', array_keys(Product::CATEGORIES)),
            'description' => 'nullable|string|max:500',
            'long_description' => 'nullable|string',
            'unit_price' => 'required|numeric|min:0',
            'unit' => 'required|string|max:20',
            'emoji' => 'nullable|string|max:20',
            'image' => 'nullable|image|max:4096',
            'is_active' => 'required|in:0,1',
            'show_in_catalog' => 'required|in:0,1',
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }

    private function uniqueProductSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 2;
        while (Product::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$original}-{$i}";
            $i++;
        }

        return $slug;
    }

    private function uniqueProductSku(string $category): string
    {
        do {
            $sku = strtoupper(Str::substr($category, 0, 4)).'-'.strtoupper(Str::random(5));
        } while (Product::withTrashed()->where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * @return array<string, string>|null
     */
    private function parseProductSpecifications(Request $request): ?array
    {
        $keys = $request->input('spec_key', []);
        $values = $request->input('spec_value', []);
        $specs = [];
        foreach ($keys as $i => $key) {
            $key = trim((string) $key);
            $value = trim((string) ($values[$i] ?? ''));
            if ($key !== '' && $value !== '') {
                $specs[$key] = $value;
            }
        }

        return $specs ?: null;
    }

    public function inventory()
    {
        $products = Product::with(['inventories' => fn ($q) => $q->latest()])
            ->where('is_active', true)->paginate(20);
        $movements = Inventory::with('product', 'productionBatch', 'processedBy')
            ->latest()->limit(20)->get();
        $stock = [];
        foreach ($products as $p) {
            $in = $p->inventories->whereIn('movement_type', ['entrada', 'devolucion'])->sum('quantity');
            $out = $p->inventories->whereIn('movement_type', ['salida', 'merma'])->sum('quantity');
            $stock[$p->id] = round($in - $out, 2);
        }

        return view('admin.inventory', compact('products', 'stock', 'movements'));
    }

    public function sales(Request $request)
    {
        $query = Sale::with('items.product', 'servedBy', 'customer');
        if ($search = $request->search) {
            $query->where('client_name', 'like', "%{$search}%")->orWhere('invoice_number', 'like', "%{$search}%");
        }
        if ($from = $request->from) {
            $query->where('sale_date', '>=', $from);
        }
        if ($to = $request->to) {
            $query->where('sale_date', '<=', $to);
        }
        if ($type = $request->type) {
            $query->where('sale_type', $type);
        }
        if ($payment_status = $request->payment_status) {
            $query->where('payment_status', $payment_status);
        }
        $sales = $query->latest('sale_date')->paginate(15)->withQueryString();
        $summary = [
            'total' => $sales->total() > 0 ? (clone $query)->sum('total_amount') : 0,
            'paid' => $sales->total() > 0 ? (clone $query)->where('payment_status', 'pagado')->sum('total_amount') : 0,
            'pending' => $sales->total() > 0 ? (clone $query)->where('payment_status', 'pendiente')->sum('total_amount') : 0,
        ];

        return view('admin.sales', compact('sales', 'summary'));
    }

    public function salesShow(Sale $sale)
    {
        $sale->load('items.product', 'servedBy', 'customer');

        return view('admin.sales-show', compact('sale'));
    }

    public function salesUpdateStatus(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'payment_status' => 'required|in:pendiente,pagado,parcial,anulado',
        ]);
        $sale->update($data);

        return back()->with('success', 'Estado del pedido actualizado.');
    }

    public function salesCreate()
    {
        $products = Product::with(['productionBatches' => fn ($q) => $q->where('status', 'terminado')])
            ->where('is_active', true)->get();

        return view('admin.sales-create', compact('products'));
    }

    public function salesStore(Request $request)
    {
        $data = $request->validate([
            'client_name' => 'required|string|max:150',
            'client_dni_ruc' => 'nullable|string|max:20',
            'client_email' => 'nullable|email',
            'client_phone' => 'nullable|string|max:20',
            'client_address' => 'nullable|string',
            'sale_date' => 'required|date',
            'payment_status' => 'required|in:pendiente,pagado,parcial,anulado',
            'payment_method' => 'required|in:efectivo,transferencia,yape,plin,cheque,tarjeta',
            'sale_type' => 'required|in:mostrador,delivery,mayorista,exportacion',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $items = collect($request->items ?? [])->filter(fn ($i) => ($i['quantity'] ?? 0) > 0);

        if ($items->isEmpty()) {
            return back()->withErrors('Debe agregar al menos un producto.');
        }

        return DB::transaction(function () use ($data, $items) {
            $subtotal = 0;
            foreach ($items as $i) {
                $subtotal += (float) $i['quantity'] * (float) $i['unit_price'];
            }

            $tax = round($subtotal * 0.18, 2);
            $discount = (float) ($data['discount'] ?? 0);

            $sale = Sale::create([
                ...$data,
                'invoice_number' => 'FV-'.Carbon::parse($data['sale_date'])->format('Ymd').'-'.str_pad((string) (Sale::count() + 1), 4, '0', STR_PAD_LEFT),
                'subtotal' => round($subtotal, 2),
                'tax' => $tax,
                'discount' => $discount,
                'total_amount' => round($subtotal + $tax - $discount, 2),
                'served_by' => Auth::id(),
            ]);

            foreach ($items as $i) {
                $saleItem = SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $i['product_id'],
                    'production_batch_id' => $i['batch_id'] ?? null,
                    'quantity' => $i['quantity'],
                    'unit_price' => $i['unit_price'],
                    'subtotal' => round((float) $i['quantity'] * (float) $i['unit_price'], 2),
                ]);

                $product = Product::find($i['product_id']);
                Inventory::create([
                    'production_batch_id' => $i['batch_id'] ?? null,
                    'product_id' => $i['product_id'],
                    'quantity' => $i['quantity'],
                    'unit' => $product?->unit,
                    'movement_type' => 'salida',
                    'unit_cost' => $product?->unit_price ? round(($product->unit_price * 0.65), 2) : 0,
                    'total_value' => $product?->unit_price ? round((float) $i['quantity'] * ($product->unit_price * 0.65), 2) : 0,
                    'location' => 'Punto de Venta',
                    'reference_document' => $sale->invoice_number,
                    'related_sale_id' => $sale->id,
                    'processed_by' => Auth::id(),
                    'notes' => 'Venta #'.$sale->invoice_number,
                ]);
            }

            return redirect()->route('admin.sales')->with('success', 'Venta registrada correctamente.');
        });
    }

    /**
     * Centro de pagos: productores (liquidación por litro) y acopiadores (sueldo mensual).
     */
    /**
     * Clientes de la tienda web: los que crearon su cuenta y los que pidieron como invitados.
     */
    public function customers(Request $request)
    {
        $tab = $request->tab === 'invitados' ? 'invitados' : 'cuentas';
        $search = trim((string) $request->search);

        $customers = Customer::query()
            ->withCount('sales')
            ->withSum('sales', 'total_amount')
            ->withMax('sales', 'sale_date')
            ->when($search, fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")))
            ->latest()
            ->paginate(15, ['*'], 'page')->withQueryString();

        $guestOrders = Sale::whereNull('customer_id')->where('sale_type', 'pedido_web');
        $guests = (clone $guestOrders)
            ->selectRaw("COALESCE(NULLIF(client_email, ''), client_name) as guest_key, MAX(client_name) as name, MAX(client_email) as email, MAX(client_phone) as phone, COUNT(*) as orders_count, SUM(total_amount) as total_spent, MAX(sale_date) as last_order")
            ->when($search, fn ($q) => $q->where(fn ($w) => $w
                ->where('client_name', 'like', "%{$search}%")
                ->orWhere('client_email', 'like', "%{$search}%")
                ->orWhere('client_phone', 'like', "%{$search}%")))
            ->groupBy('guest_key')
            ->orderByDesc('last_order')
            ->paginate(15, ['*'], 'guests_page')->withQueryString();

        $stats = [
            'accounts' => Customer::count(),
            'new_this_month' => Customer::where('created_at', '>=', now()->startOfMonth())->count(),
            'guests' => (int) (clone $guestOrders)->selectRaw("COUNT(DISTINCT COALESCE(NULLIF(client_email, ''), client_name)) as total")->value('total'),
            'web_sales' => Sale::where('sale_type', 'pedido_web')->sum('total_amount'),
        ];

        return view('admin.customers', compact('tab', 'customers', 'guests', 'stats'));
    }

    public function customerShow(Customer $customer)
    {
        $orders = $customer->sales()->with('items.product')->latest('sale_date')->latest('id')->paginate(10);
        $summary = [
            'orders' => $customer->sales()->count(),
            'total' => $customer->sales()->sum('total_amount'),
            'pending' => $customer->sales()->where('payment_status', '!=', 'pagado')->count(),
        ];

        return view('admin.customers-show', compact('customer', 'orders', 'summary'));
    }

    public function payments(Request $request)
    {
        $tab = $request->tab === 'acopiadores' ? 'acopiadores' : 'productores';
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();

        $kpis = [
            'pending_producers' => Payment::whereNotIn('status', ['pagado', 'rechazado'])->sum('total_amount'),
            'pending_collectors' => CollectorPayment::where('status', '!=', 'pagado')->sum('total_amount'),
            'paid_this_month' => Payment::where('status', 'pagado')->whereBetween('payment_date', [$currentMonthStart, $currentMonthEnd])->sum('total_amount')
                + CollectorPayment::where('status', 'pagado')->whereBetween('payment_date', [$currentMonthStart, $currentMonthEnd])->sum('total_amount'),
            'liters_this_month' => Payment::whereBetween('period_start', [$currentMonthStart, $currentMonthEnd])->sum('total_liters'),
        ];

        $query = Payment::with('producer.user');
        if ($status = $request->status) {
            $query->where('status', $status);
        }
        if ($method = $request->input('method')) {
            $query->where('payment_method', $method);
        }
        if ($period = $request->period) {
            $month = Carbon::parse($period.'-01');
            $query->whereBetween('period_start', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);
        }
        if ($search = $request->search) {
            $query->whereHas('producer', fn ($q) => $q->where('code', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('lastname', 'like', "%{$search}%")));
        }
        $payments = $query->latest('period_end')->latest('id')->paginate(15, ['*'], 'page')->withQueryString();

        $payrollMonth = $request->month ? Carbon::parse($request->month.'-01') : $currentMonthStart->copy();
        $payrollStart = $payrollMonth->copy()->startOfMonth();
        $payrollEnd = $payrollMonth->copy()->endOfMonth();
        $collectors = User::where('role', 'acopiador')
            ->withSum(['milkDeliveriesAsCollector as month_liters' => fn ($q) => $q
                ->whereBetween('delivery_date', [$payrollStart->toDateString(), $payrollEnd->toDateString()])
                ->where('status', '!=', 'rechazado')], 'liters')
            ->with(['collectorPayments' => fn ($q) => $q->whereDate('period_month', $payrollStart)])
            ->orderBy('name')
            ->get();
        $collectorHistory = CollectorPayment::with('collector')
            ->latest('period_month')->latest('id')
            ->paginate(10, ['*'], 'history_page')->withQueryString();
        $monthPayments = $collectors->flatMap->collectorPayments;
        $payroll = [
            'total' => $monthPayments->sum('total_amount'),
            'paid' => $monthPayments->where('status', 'pagado')->sum('total_amount'),
            'pending' => $monthPayments->where('status', '!=', 'pagado')->sum('total_amount'),
            'missing' => $collectors->where('active', true)->filter(fn ($c) => $c->collectorPayments->isEmpty())->count(),
        ];

        return view('admin.payments', compact(
            'tab', 'kpis', 'payments', 'collectors', 'collectorHistory', 'payroll', 'payrollMonth'
        ));
    }

    public function paymentsProcess(Request $request, PaymentService $paymentService)
    {
        $start = $request->period_start
            ? Carbon::parse($request->period_start)
            : Carbon::now()->startOfWeek()->subWeek();
        $end = $request->period_end
            ? Carbon::parse($request->period_end)
            : $start->copy()->endOfWeek();

        $producersWithDeliveries = Producer::where('status', 'activo')
            ->whereHas('milkDeliveries', fn ($q) => $q->whereBetween('delivery_date', [$start, $end])->where('status', '!=', 'rechazado'))
            ->whereDoesntHave('payments', fn ($q) => $q->where('period_start', $start->toDateString())->where('period_end', $end->toDateString()))
            ->with(['milkDeliveries' => fn ($q) => $q->whereBetween('delivery_date', [$start, $end])->where('status', '!=', 'rechazado')])
            ->get();

        $previews = $producersWithDeliveries->map(
            fn (Producer $p) => $paymentService->calculatePayment($p, $start, $end)
        )->filter(fn ($preview) => $preview['can_generate']);

        return view('admin.payments-process', compact('previews', 'start', 'end'));
    }

    public function paymentsGenerate(Request $request, PaymentService $paymentService)
    {
        $data = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'producer_ids' => 'required|array|min:1',
            'producer_ids.*' => 'exists:producers,id',
        ]);

        $start = Carbon::parse($data['period_start']);
        $end = Carbon::parse($data['period_end']);
        $generated = 0;

        foreach ($data['producer_ids'] as $producerId) {
            $producer = Producer::find($producerId);
            $preview = $paymentService->calculatePayment($producer, $start, $end);
            if ($preview['can_generate']) {
                $paymentService->generatePayment($preview, Auth::id());
                $generated++;
            }
        }

        return redirect()->route('admin.payments')->with('success', "Se generaron {$generated} liquidaciones de pago.");
    }

    public function paymentMarkPaid(Request $request, Payment $payment, PaymentService $paymentService)
    {
        $data = $request->validate([
            'payment_method' => 'nullable|in:transferencia,efectivo,cheque',
            'transaction_number' => 'nullable|string|max:50',
        ]);
        $paymentService->processPayment($payment, $data);

        return back()->with('success', 'Pago marcado como pagado.');
    }

    public function paymentReceipt(Payment $payment)
    {
        $payment->load('producer.user', 'items.milkDelivery', 'processedBy');

        return view('payments.producer-receipt', [
            'payment' => $payment,
            'backUrl' => route('admin.payments'),
        ]);
    }

    public function collectorPaymentReceipt(CollectorPayment $collectorPayment)
    {
        $collectorPayment->load('collector', 'processedBy');

        return view('payments.collector-receipt', [
            'payment' => $collectorPayment,
            'backUrl' => route('admin.payments', ['tab' => 'acopiadores']),
        ]);
    }

    public function collectorPayrollGenerate(Request $request, CollectorPayrollService $payrollService)
    {
        $data = $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);
        $month = Carbon::parse($data['month'].'-01');
        $result = $payrollService->generateForMonth($month, Auth::id());

        $message = "Planilla de {$month->locale('es')->translatedFormat('F Y')}: {$result['generated']} pago(s) generado(s).";
        if ($result['skipped_without_salary'] > 0) {
            $message .= " {$result['skipped_without_salary']} acopiador(es) sin sueldo definido.";
        }

        return redirect()->route('admin.payments', ['tab' => 'acopiadores', 'month' => $data['month']])
            ->with('success', $message);
    }

    public function collectorPaymentMarkPaid(Request $request, CollectorPayment $collectorPayment, CollectorPayrollService $payrollService)
    {
        $data = $request->validate([
            'payment_method' => 'nullable|in:transferencia,efectivo,cheque',
            'transaction_number' => 'nullable|string|max:50',
        ]);
        $payrollService->markAsPaid($collectorPayment, $data);

        return back()->with('success', "Sueldo de {$collectorPayment->collector->fullname} marcado como pagado.");
    }

    public function collectorSalaryUpdate(Request $request, User $collector)
    {
        if ($collector->role !== 'acopiador') {
            abort(404);
        }
        $data = $request->validate([
            'monthly_salary' => 'required|numeric|min:0|max:100000',
        ]);
        $collector->update(['monthly_salary' => $data['monthly_salary']]);

        return back()->with('success', "Sueldo mensual de {$collector->fullname} actualizado a S/ ".number_format($data['monthly_salary'], 2).'.');
    }

    public function routes(Request $request)
    {
        $query = CollectionRoute::with('collector', 'stops.producer.user');
        if ($status = $request->status) {
            $query->where('status', $status);
        }
        if ($day = $request->day) {
            $query->where('day', $day);
        }
        $routes = $query->latest()->paginate(12)->withQueryString();
        $days = CollectionRoute::DAYS;

        return view('admin.routes', compact('routes', 'days'));
    }

    public function routesCreate()
    {
        $collectors = User::where('role', 'acopiador')->where('active', true)->get();
        $producers = Producer::where('status', 'activo')->with('user')->get();

        return view('admin.routes-create', compact('collectors', 'producers'));
    }

    public function routesStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:collection_routes,code',
            'description' => 'nullable|string',
            'day' => 'required|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo',
            'start_time' => 'required',
            'end_time' => 'required',
            'vehicle_plate' => 'nullable|string|max:10',
            'estimated_distance_km' => 'nullable|numeric|min:0',
            'collector_id' => 'nullable|exists:users,id',
            'status' => 'required|in:planeada,en_curso,completada,cancelada',
        ]);
        $route = CollectionRoute::create($data);
        if ($stops = $request->stops) {
            foreach ($stops as $order => $stop) {
                if (! empty($stop['producer_id'])) {
                    RouteStop::create([
                        'collection_route_id' => $route->id,
                        'producer_id' => $stop['producer_id'],
                        'stop_order' => $order + 1,
                        'estimated_arrival' => $stop['estimated_arrival'] ?? null,
                        'estimated_liters' => $stop['estimated_liters'] ?? 0,
                        'special_instructions' => $stop['special_instructions'] ?? null,
                        'status' => 'pendiente',
                    ]);
                }
            }
        }

        return redirect()->route('admin.routes')->with('success', 'Ruta creada correctamente con '.count($stops ?? []).' paradas.');
    }

    public function routesEdit(CollectionRoute $route)
    {
        $route->load('stops.producer.user');
        $collectors = User::where('role', 'acopiador')->where('active', true)->get();
        $producers = Producer::where('status', 'activo')->with('user')->get();

        return view('admin.routes-edit', compact('route', 'collectors', 'producers'));
    }

    public function routesUpdate(Request $request, CollectionRoute $route)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:collection_routes,code,'.$route->id,
            'description' => 'nullable|string',
            'day' => 'required|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo',
            'start_time' => 'required',
            'end_time' => 'required',
            'vehicle_plate' => 'nullable|string|max:10',
            'estimated_distance_km' => 'nullable|numeric|min:0',
            'collector_id' => 'nullable|exists:users,id',
            'status' => 'required|in:planeada,en_curso,completada,cancelada',
        ]);
        $route->update($data);

        return redirect()->route('admin.routes')->with('success', 'Ruta actualizada.');
    }

    public function config()
    {
        $knownKeys = collect(PlantConfig::SETTINGS)->flatMap(fn ($group) => array_keys($group['settings']));
        $saved = PlantConfig::with('updatedBy')->get()->keyBy('key');
        $groups = collect(PlantConfig::SETTINGS)->map(function (array $group) use ($saved) {
            $group['settings'] = collect($group['settings'])->map(fn (array $setting, string $key) => [
                ...$setting,
                'value' => $saved->get($key)?->value ?? $setting['default'],
            ])->all();
            $group['updated_at'] = $saved->only(array_keys($group['settings']))->max('updated_at');

            return $group;
        });
        $customConfigs = $saved->except($knownKeys->all())->sortBy('label')->values();

        return view('admin.config', compact('groups', 'customConfigs'));
    }

    /**
     * Guarda de una vez todos los ajustes de un grupo (Empresa, Acopio, Calidad).
     */
    public function configSaveGroup(Request $request, string $group)
    {
        $definition = PlantConfig::SETTINGS[$group] ?? abort(404);

        $rules = [];
        foreach ($definition['settings'] as $key => $setting) {
            $rules["settings.{$key}"] = match (true) {
                $setting['type'] === 'number' => ['required', 'numeric', 'min:0', 'max:1000000'],
                $setting['type'] === 'select' => ['required', Rule::in($setting['options'])],
                $key === 'email_planta' => ['nullable', 'email', 'max:150'],
                in_array($key, ['facebook_url', 'instagram_url'], true) => ['nullable', 'url', 'max:255'],
                $setting['type'] === 'text' => ['nullable', 'string', 'max:1000'],
                default => ['nullable', 'string', 'max:255'],
            };
        }
        $data = $request->validate($rules, [], collect($definition['settings'])->mapWithKeys(fn ($s, $k) => ["settings.{$k}" => mb_strtolower($s['label'])])->all());

        foreach ($definition['settings'] as $key => $setting) {
            PlantConfig::updateOrCreate(['key' => $key], [
                'label' => $setting['label'],
                'value' => (string) ($data['settings'][$key] ?? ''),
                'value_type' => $setting['type'] === 'select' ? 'string' : $setting['type'],
                'description' => $setting['help'],
                'updated_by' => Auth::id(),
            ]);
        }

        return redirect()->to(route('admin.config').'#'.$group)->with('success', "Se guardaron los cambios de \"{$definition['title']}\".");
    }

    public function configStore(Request $request)
    {
        $data = $request->validate([
            'key' => 'required|string|max:50|unique:plant_configs,key',
            'label' => 'required|string|max:100',
            'value' => 'required|string',
            'value_type' => 'required|in:string,number,boolean,date,text',
            'description' => 'nullable|string',
        ]);
        PlantConfig::create(['updated_by' => Auth::id(), ...$data]);

        return back()->with('success', 'Configuración agregada.');
    }

    public function configUpdate(Request $request, PlantConfig $config)
    {
        $data = $request->validate([
            'value' => 'required',
            'description' => 'nullable|string',
        ]);
        $config->update(['updated_by' => Auth::id(), ...$data]);

        return back()->with('success', 'Configuración actualizada.');
    }

    public function complaints()
    {
        $complaints = Complaint::with('producer.user', 'assignedTo')
            ->latest()->paginate(15);

        return view('admin.complaints', compact('complaints'));
    }

    public function complaintShow(Complaint $complaint)
    {
        $complaint->load('producer.user', 'assignedTo');
        $staff = User::whereIn('role', ['admin', 'gerente'])->where('active', true)->get();

        return view('admin.complaints-show', compact('complaint', 'staff'));
    }

    public function complaintUpdate(Request $request, Complaint $complaint)
    {
        $data = $request->validate([
            'status' => 'required|in:abierto,en_revision,respondido,cerrado,rechazado',
            'staff_response' => 'nullable|string|max:2000',
            'assigned_to' => 'nullable|exists:users,id',
        ]);
        if ($data['status'] === 'respondido' && $complaint->status !== 'respondido') {
            $data['responded_at'] = now();
        }
        if ($data['status'] === 'cerrado' && $complaint->status !== 'cerrado') {
            $data['closed_at'] = now();
        }
        $complaint->update($data);

        return back()->with('success', 'Reclamo actualizado.');
    }

    public function sanctions(Request $request)
    {
        $query = Sanction::with('producer.user', 'issuedBy');
        if ($status = $request->status) {
            $query->where('status', $status);
        }
        if ($producer_id = $request->producer_id) {
            $query->where('producer_id', $producer_id);
        }
        $sanctions = $query->latest('sanction_date')->paginate(15)->withQueryString();

        return view('admin.sanctions', compact('sanctions'));
    }

    public function sanctionCreate()
    {
        $producers = Producer::where('status', 'activo')->with('user')->orderBy('code')->get();

        return view('admin.sanctions-create', compact('producers'));
    }

    public function sanctionStore(Request $request)
    {
        $data = $request->validate([
            'producer_id' => 'required|exists:producers,id',
            'type' => 'required|in:calidad,incumplimiento,fraude,pesaje,otro',
            'motivo' => 'required|string|max:150',
            'description' => 'nullable|string|max:2000',
            'amount' => 'nullable|numeric|min:0',
            'sanction_date' => 'required|date',
        ]);
        Sanction::create([...$data, 'issued_by' => Auth::id(), 'status' => 'activa']);

        return redirect()->route('admin.sanctions')->with('success', 'Sanción registrada correctamente.');
    }

    public function sanctionUpdate(Request $request, Sanction $sanction)
    {
        $data = $request->validate([
            'status' => 'required|in:activa,cumplida,anulada',
            'notes' => 'nullable|string|max:2000',
        ]);
        if ($data['status'] !== 'activa' && $sanction->status === 'activa') {
            $data['resolved_date'] = now();
        }
        $sanction->update($data);

        return back()->with('success', 'Sanción actualizada.');
    }

    public function notifications()
    {
        $notifications = Notification::with('creator', 'recipient')->latest()->paginate(15);

        return view('admin.notifications', compact('notifications'));
    }

    public function notificationsCreate()
    {
        $users = User::where('active', true)->orderBy('name')->get();

        return view('admin.notifications-create', compact('users'));
    }

    public function notificationsStore(Request $request)
    {
        $data = $request->validate([
            'target' => 'required|in:todos,productores,acopiadores,gerencia,admin',
            'user_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:200',
            'message' => 'required|string|max:2000',
            'priority' => 'required|in:baja,normal,alta,urgente',
            'is_active' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
        ]);
        Notification::create(['created_by' => Auth::id(), ...$data, 'is_active' => $data['is_active'] ?? true]);

        return redirect()->route('admin.notifications')->with('success', 'Aviso publicado.');
    }
}
