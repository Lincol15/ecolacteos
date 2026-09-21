<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductionCartRequest;
use App\Models\Ingredient;
use App\Models\IngredientMovement;
use App\Models\Inventory;
use App\Models\MilkDelivery;
use App\Models\Notification;
use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\ProductionBatchService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlantController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();
        $pendingStock = [];
        foreach (Product::where('is_active', true)->get() as $p) {
            $in = Inventory::where('product_id', $p->id)
                ->whereIn('movement_type', ['entrada', 'devolucion'])->sum('quantity');
            $out = Inventory::where('product_id', $p->id)
                ->whereIn('movement_type', ['salida', 'merma'])->sum('quantity');
            $pendingStock[] = ['product' => $p, 'stock' => round($in - $out, 2)];
        }

        $activeBatches = ProductionBatch::with('product', 'supervisor')
            ->whereIn('status', ['en_proceso', 'curando'])->latest()->get();
        $recentBatches = ProductionBatch::with('product')
            ->latest()->limit(8)->get();
        $todayMilk = MilkDelivery::where('delivery_date', $today->toDateString())
            ->where('status', '!=', 'rechazado')
            ->sum('liters');

        $labels = [];
        $batchCount = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = $today->copy()->subDays($i);
            $labels[] = $d->format('d/m');
            $batchCount[] = ProductionBatch::whereDate('production_date', $d)->count();
        }

        $notifications = Notification::visibleForUser(Auth::user())->limit(4)->get();

        return view('plant.dashboard', compact(
            'pendingStock', 'activeBatches', 'recentBatches',
            'todayMilk', 'labels', 'batchCount', 'notifications'
        ));
    }

    public function batches(Request $request)
    {
        $query = ProductionBatch::with('product', 'supervisor');
        if ($status = $request->status) {
            $query->where('status', $status);
        }
        if ($product_id = $request->product_id) {
            $query->where('product_id', $product_id);
        }
        $batches = $query->latest('production_date')->paginate(15);
        $products = Product::orderBy('name')->get();

        return view('plant.batches', compact('batches', 'products'));
    }

    public function batchShow(ProductionBatch $batch)
    {
        $batch->load('product', 'supervisor', 'milkDeliveries.producer.user', 'inventories');

        return view('plant.batch-show', compact('batch'));
    }

    public function batchUpdateStatus(Request $request, ProductionBatch $batch)
    {
        $data = $request->validate([
            'status' => 'required|in:planeado,en_proceso,curando,terminado,vendido,desperdicio',
            'quality_notes' => 'nullable|string',
        ]);
        $update = $data;
        if ($data['status'] === 'en_proceso' && ! $batch->started_at) {
            $update['started_at'] = now();
        }
        if (in_array($data['status'], ['terminado', 'vendido', 'desperdicio'], true) && ! $batch->finished_at) {
            $update['finished_at'] = now();
        }
        $batch->update($update);

        return back()->with('success', 'Estado actualizado.');
    }

    public function production(Request $request)
    {
        $tab = $request->get('tab', 'historial');
        $batches = ProductionBatch::with('product', 'supervisor')
            ->latest('production_date')->paginate(15)->withQueryString();
        $recipes = Recipe::with('product', 'recipeIngredients.ingredient')->latest()->get();
        $stats = [
            'total_batches' => ProductionBatch::count(),
            'in_process' => ProductionBatch::where('status', 'en_proceso')->count(),
            'curing' => ProductionBatch::where('status', 'curando')->count(),
            'finished' => ProductionBatch::where('status', 'terminado')->count(),
        ];
        $ingredients = Ingredient::where('active', true)->orderBy('is_milk', 'desc')->orderBy('name')->get();
        $ingredientMovements = IngredientMovement::with('ingredient', 'processedBy', 'productionBatch')
            ->whereHas('ingredient', fn ($q) => $q->where('is_milk', false))
            ->latest()->limit(30)->get();
        $milkReceivedToday = MilkDelivery::where('delivery_date', Carbon::today()->toDateString())
            ->where('recibido', true)
            ->where('status', '!=', 'rechazado')
            ->sum('liters');

        return view('plant.production', compact('tab', 'batches', 'recipes', 'stats', 'ingredients', 'ingredientMovements', 'milkReceivedToday'));
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

        return view('plant.production-create', compact('products', 'recipesForJs', 'supervisors', 'deliveries', 'ingredientStocks'));
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

        return redirect()->route('plant.production', ['tab' => 'historial'])->with('success', $message);
    }

    public function recipeCreate()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $ingredients = Ingredient::where('active', true)->orderBy('is_milk', 'desc')->orderBy('name')->get();

        return view('plant.recipe-create', compact('products', 'ingredients'));
    }

    public function recipeStore(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:150',
            'instructions' => 'nullable|string|max:2000',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.ingredient_id' => 'required|exists:ingredients,id',
            'ingredients.*.quantity_per_unit' => 'required|numeric|min:0.001|max:100000',
        ]);

        return DB::transaction(function () use ($data) {
            $milkQty = collect($data['ingredients'])->first(function ($row) {
                return Ingredient::where('id', $row['ingredient_id'])->value('is_milk');
            });

            $recipe = Recipe::create([
                'product_id' => $data['product_id'],
                'name' => $data['name'],
                'instructions' => $data['instructions'] ?? null,
                'milk_liters_per_unit' => $milkQty['quantity_per_unit'] ?? 0,
                'active' => true,
                'created_by' => Auth::id(),
            ]);

            foreach ($data['ingredients'] as $row) {
                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'ingredient_id' => $row['ingredient_id'],
                    'quantity_per_unit' => $row['quantity_per_unit'],
                ]);
            }

            return redirect()->route('plant.production', ['tab' => 'recetas'])->with('success', 'Receta creada correctamente.');
        });
    }

    public function sales(Request $request)
    {
        $tab = $request->get('tab', 'historial');
        $query = Sale::with('items.product', 'servedBy');
        if ($from = $request->from) {
            $query->where('sale_date', '>=', $from);
        }
        if ($to = $request->to) {
            $query->where('sale_date', '<=', $to);
        }
        $sales = $query->latest('sale_date')->paginate(15)->withQueryString();

        $products = Product::where('is_active', true)->get();
        $stocks = [];
        foreach ($products as $p) {
            $in = Inventory::where('product_id', $p->id)
                ->whereIn('movement_type', ['entrada', 'devolucion'])->sum('quantity');
            $out = Inventory::where('product_id', $p->id)
                ->whereIn('movement_type', ['salida', 'merma'])->sum('quantity');
            $stocks[$p->id] = round($in - $out, 2);
        }

        return view('plant.sales', compact('tab', 'sales', 'products', 'stocks'));
    }

    public function salesCreate()
    {
        $products = Product::with(['productionBatches' => fn ($q) => $q->where('status', 'terminado')])
            ->where('is_active', true)->get();

        return view('plant.sales-create', compact('products'));
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
                SaleItem::create([
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

            return redirect()->route('plant.sales', ['tab' => 'historial'])->with('success', 'Venta registrada correctamente.');
        });
    }

    public function inventory()
    {
        $products = Product::with(['productionBatches' => fn ($q) => $q->where('status', 'terminado')])
            ->where('is_active', true)->get();
        $stocks = [];
        foreach ($products as $p) {
            $in = Inventory::where('product_id', $p->id)
                ->whereIn('movement_type', ['entrada', 'devolucion'])->sum('quantity');
            $out = Inventory::where('product_id', $p->id)
                ->whereIn('movement_type', ['salida', 'merma'])->sum('quantity');
            $stocks[$p->id] = round($in - $out, 2);
        }
        $movements = Inventory::with('product', 'productionBatch', 'processedBy')->latest()->paginate(20);

        return view('plant.inventory', compact('products', 'stocks', 'movements'));
    }

    public function inventoryAdjust(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0',
            'movement_type' => 'required|in:entrada,salida,ajuste,merma',
            'unit_cost' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);
        $product = Product::find($data['product_id']);
        Inventory::create([
            ...$data,
            'unit' => $product->unit,
            'unit_cost' => $data['unit_cost'] ?? 0,
            'total_value' => round(($data['unit_cost'] ?? 0) * $data['quantity'], 2),
            'location' => $data['location'] ?? 'Planta Principal',
            'processed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Movimiento de inventario registrado.');
    }

    public function profile()
    {
        $user = Auth::user();

        return view('plant.profile', compact('user'));
    }

    public function profileUpdate(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'lastname' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
        ]);
        $user->update($data);

        return back()->with('success', 'Datos actualizados.');
    }
}
