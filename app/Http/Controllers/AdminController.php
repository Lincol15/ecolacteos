<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductionBatchRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateProducerRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\CollectionRoute;
use App\Models\MilkDelivery;
use App\Models\Notification;
use App\Models\PlantConfig;
use App\Models\Producer;
use App\Models\ProductionBatch;
use App\Models\Product;
use App\Models\QualityReport;
use App\Models\RouteStop;
use App\Models\Sale;
use App\Models\User;
use App\Models\Complaint;
use App\Models\Payment;
use App\Models\Inventory;
use App\Models\SaleItem;
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
                ->where(DB::raw('LOWER(name)'), 'like', '%' . strtolower($search) . '%')
                ->orWhere(DB::raw('LOWER(lastname)'), 'like', '%' . strtolower($search) . '%')
                ->orWhere(DB::raw('LOWER(email)'), 'like', '%' . strtolower($search) . '%')
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
                'code' => 'PROD-' . str_pad((string)$user->id, 5, '0', STR_PAD_LEFT),
                'farm_name' => $data['name'],
                'zone' => 'Huata', 
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
                ->where(DB::raw('LOWER(name)'), 'like', '%' . strtolower($search) . '%')
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
        $deliveries = $query->latest('delivery_date')->paginate(25)->withQueryString();
        $totals = [
            'liters' => $deliveries->total() > 0 ? (clone $query)->sum('liters') : 0,
            'amount' => $deliveries->total() > 0 ? (clone $query)->sum('total_amount') : 0,
        ];
        $collectors = User::where('role', 'acopiador')->get();
        return view('admin.deliveries', compact('deliveries', 'totals', 'collectors'));
    }

    public function quality(Request $request)
    {
        $query = QualityReport::with('milkDelivery.producer.user', 'analyst');
        if ($from = $request->from) {
            $query->where('analyzed_at', '>=', $from);
        }
        if ($to = $request->to) {
            $query->where('analyzed_at', '<=', $to);
        }
        if ($result = $request->result) {
            $query->where('result', $result);
        }
        $reports = $query->latest()->paginate(20)->withQueryString();
        return view('admin.quality', compact('reports'));
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

    public function productionCreate()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $supervisors = User::whereIn('role', ['trabajador_planta', 'gerente', 'admin'])->get();
        $deliveries = MilkDelivery::where('status', '!=', 'rechazado')
            ->whereDoesntHave('batches')
            ->with('producer.user')
            ->latest()->limit(50)->get();
        return view('admin.production-create', compact('products', 'supervisors', 'deliveries'));
    }

    public function productionStore(StoreProductionBatchRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            $yield = $data['input_milk_liters'] > 0 
                ? round(($data['output_units'] / $data['input_milk_liters']) * 100, 2) 
                : 0;
                
            $batch = ProductionBatch::create([
                ...$data,
                'batch_number' => 'LOTE-' . Carbon::parse($data['production_date'])->format('Ymd') . '-' . str_pad((string)(ProductionBatch::count() + 1), 3, '0', STR_PAD_LEFT),
                'yield_percentage' => $yield,
                'started_at' => $data['status'] !== 'planeado' ? now() : null,
                'finished_at' => $data['status'] === 'terminado' ? now() : null,
            ]);
            
            if ($milk_ids = $request->milk_ids) {
                $per = $data['input_milk_liters'] / count($milk_ids);
                foreach ($milk_ids as $id) {
                    $batch->milkDeliveries()->attach($id, ['liters_used' => round($per, 2)]);
                }
            }
            
            $product = Product::find($data['product_id']);
            Inventory::create([
                'production_batch_id' => $batch->id,
                'product_id' => $batch->product_id,
                'quantity' => $data['output_units'],
                'unit' => $product->unit,
                'movement_type' => 'entrada',
                'unit_cost' => round(($product->unit_price * 0.65), 2),
                'total_value' => round($data['output_units'] * ($product->unit_price * 0.65), 2),
                'location' => 'Cámara Principal',
                'expiration_date' => $data['expiration_date'] ?? null,
                'reference_document' => $batch->batch_number,
                'processed_by' => Auth::id(),
                'notes' => 'Ingreso lote producción',
            ]);
            
            return redirect()->route('admin.production')->with('success', 'Lote de producción creado correctamente.');
        });
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
        $products = Product::with('productionBatches', fn ($q) => $q->where('status', 'terminado'))
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
                $subtotal += (float)$i['quantity'] * (float)$i['unit_price'];
            }
            
            $tax = round($subtotal * 0.18, 2);
            $discount = (float)($data['discount'] ?? 0);
            
            $sale = Sale::create([
                ...$data,
                'invoice_number' => 'FV-' . Carbon::parse($data['sale_date'])->format('Ymd') . '-' . str_pad((string)(Sale::count() + 1), 4, '0', STR_PAD_LEFT),
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
                    'subtotal' => round((float)$i['quantity'] * (float)$i['unit_price'], 2),
                ]);
                
                $product = Product::find($i['product_id']);
                Inventory::create([
                    'production_batch_id' => $i['batch_id'] ?? null,
                    'product_id' => $i['product_id'],
                    'quantity' => $i['quantity'],
                    'unit' => $product?->unit,
                    'movement_type' => 'salida',
                    'unit_cost' => $product?->unit_price ? round(($product->unit_price * 0.65), 2) : 0,
                    'total_value' => $product?->unit_price ? round((float)$i['quantity'] * ($product->unit_price * 0.65), 2) : 0,
                    'location' => 'Punto de Venta',
                    'reference_document' => $sale->invoice_number,
                    'related_sale_id' => $sale->id,
                    'processed_by' => Auth::id(),
                    'notes' => 'Venta #' . $sale->invoice_number,
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
        $payments = $query->latest('period_end')->paginate(15)->withQueryString();
        $summary = [
            'total' => $payments->total() > 0 ? (clone $query)->sum('total_amount') : 0,
            'pagado' => $payments->total() > 0 ? (clone $query)->where('status', 'pagado')->sum('total_amount') : 0,
            'pendiente' => $payments->total() > 0 ? (clone $query)->where('status', 'pendiente')->sum('total_amount') : 0,
            'liters' => $payments->total() > 0 ? (clone $query)->sum('total_liters') : 0,
        ];
        return view('admin.payments', compact('payments', 'summary'));
    }

    public function paymentsProcess()
    {
        $start = Carbon::now()->startOfWeek()->subWeek();
        $end = $start->copy()->endOfWeek();
        $producersWithDeliveries = Producer::where('status', 'activo')
            ->whereHas('milkDeliveries', fn ($q) => $q->whereBetween('delivery_date', [$start, $end])->where('status', '!=', 'rechazado'))
            ->with(['milkDeliveries' => fn ($q) => $q->whereBetween('delivery_date', [$start, $end])->where('status', '!=', 'rechazado')])
            ->get();
        return view('admin.payments-process', compact('producersWithDeliveries', 'start', 'end'));
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
                if (!empty($stop['producer_id'])) {
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
        return redirect()->route('admin.routes')->with('success', 'Ruta creada correctamente con ' . count($stops ?? []) . ' paradas.');
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
            'code' => 'required|string|max:20|unique:collection_routes,code,' . $route->id,
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

    public function notifications()
    {
        $notifications = Notification::with('creator')->latest()->paginate(15);
        return view('admin.notifications', compact('notifications'));
    }

    public function notificationsCreate()
    {
        return view('admin.notifications-create');
    }

    public function notificationsStore(Request $request)
    {
        $data = $request->validate([
            'target' => 'required|in:todos,productores,acopiadores,gerencia,admin',
            'title' => 'required|string|max:200',
            'message' => 'required|string|max:2000',
            'priority' => 'required|in:baja,normal,alta,urgente',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
        ]);
        Notification::create(['created_by' => Auth::id(), 'is_active' => true, ...$data]);
        return redirect()->route('admin.notifications')->with('success', 'Aviso publicado.');
    }
}
