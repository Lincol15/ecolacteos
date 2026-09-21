<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMilkDeliveryRequest;
use App\Http\Requests\StoreProductionCartRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateProducerRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\CollectionRoute;
use App\Models\Complaint;
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
use App\Services\MilkDeliveryService;
use App\Services\PaymentService;
use App\Services\ProductionBatchService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
            $query->whereHas('user', fn ($q) => $q
                ->where(DB::raw('LOWER(name)'), 'like', '%'.strtolower($search).'%')
                ->orWhere('dni', 'like', "%{$search}%"));
        }
        if ($status = $request->status) {
            $query->where('status', $status);
        }
        if ($zone = $request->zone) {
            $query->where('zone', $zone);
        }
        $producers = $query->latest()->paginate(15)->withQueryString();
        $zones = Producer::distinct()->pluck('zone')->filter()->sort()->values();

        return view('admin.producers', compact('producers', 'zones'));
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
        $routes = CollectionRoute::where('collector_id', $collector->id)
            ->with('stops.producer.user')
            ->latest()->get();
        $assignedProducers = RouteStop::whereHas('collectionRoute', fn ($q) => $q->where('collector_id', $collector->id))
            ->with('producer.user')
            ->get()
            ->pluck('producer')
            ->filter()
            ->unique('id');

        return view('admin.collectors-show', compact('collector', 'todayDeliveries', 'routes', 'assignedProducers'));
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
                $data['milk_ids'],
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

    public function ingredients()
    {
        $ingredients = Ingredient::where('active', true)->orderBy('is_milk', 'desc')->orderBy('name')->get();
        $ingredientMovements = IngredientMovement::with('ingredient', 'processedBy', 'productionBatch')
            ->whereHas('ingredient', fn ($q) => $q->where('is_milk', false))
            ->latest()->limit(30)->get();
        $milkReceivedToday = MilkDelivery::where('delivery_date', Carbon::today()->toDateString())
            ->where('recibido', true)
            ->where('status', '!=', 'rechazado')
            ->sum('liters');

        return view('admin.ingredients', compact('ingredients', 'ingredientMovements', 'milkReceivedToday'));
    }

    public function ingredientStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:ingredients,name',
            'unit' => 'required|in:L,ml,kg,g,und',
            'min_stock' => 'nullable|numeric|min:0',
        ]);
        Ingredient::create([...$data, 'active' => true, 'is_milk' => false]);

        return back()->with('success', 'Insumo registrado correctamente.');
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
        $query = Sale::with('items.product', 'servedBy');
        if ($search = $request->search) {
            $query->where('client_name', 'like', "%{$search}%")->orWhere('invoice_number', 'like', "%{$search}%");
        }
        if ($from = $request->from) {
            $query->where('sale_date', '>=', $from);
        }
        if ($to = $request->to) {
            $query->where('sale_date', '<=', $to);
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

    public function payments(Request $request)
    {
        $query = Payment::with('producer.user', 'processedBy');
        if ($status = $request->status) {
            $query->where('status', $status);
        }
        if ($method = $request->method) {
            $query->where('payment_method', $method);
        }
        if ($period = $request->period) {
            $month = Carbon::parse($period.'-01');
            $query->whereBetween('period_start', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);
        }
        $payments = $query->latest('period_end')->paginate(15)->withQueryString();
        $summary = [
            'total' => $payments->total() > 0 ? (clone $query)->sum('total_amount') : 0,
            'pagado' => $payments->total() > 0 ? (clone $query)->where('status', 'pagado')->sum('total_amount') : 0,
            'pendiente' => $payments->total() > 0 ? (clone $query)->where('status', 'pendiente')->sum('total_amount') : 0,
            'liters' => $payments->total() > 0 ? (clone $query)->sum('total_liters') : 0,
        ];

        $deliveryQuery = MilkDelivery::whereNotNull('collector_id')->where('status', '!=', 'rechazado');
        if ($period = $request->period) {
            $month = Carbon::parse($period.'-01');
            $deliveryQuery->whereBetween('delivery_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);
        }
        $collectorSummary = $deliveryQuery
            ->selectRaw('collector_id, SUM(liters) as liters, SUM(total_amount) as amount, COUNT(*) as deliveries')
            ->groupBy('collector_id')
            ->with('collector')
            ->orderByDesc('liters')
            ->get();

        return view('admin.payments', compact('payments', 'summary', 'collectorSummary'));
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
        $configs = PlantConfig::latest()->get();

        return view('admin.config', compact('configs'));
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
