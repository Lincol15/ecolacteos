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
use App\Services\ProductionBatchService;
use App\Services\RecipeService;
use App\Services\SaleService;
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
        $milkReport = MilkDelivery::receivedByDay($request->milk_from, $request->milk_to);

        return view('plant.production', compact('tab', 'batches', 'recipes', 'stats', 'ingredients', 'ingredientMovements', 'milkReport'));
    }

    public function productionCreate(ProductionBatchService $service)
    {
        $activeRecipes = Recipe::where('active', true)->with('nonMilkIngredients.ingredient')->get();
        $recipesForJs = $service->recipesForJs($activeRecipes);
        // Solo se puede producir lo que tiene receta: de ella salen la leche y los insumos.
        $products = Product::where('is_active', true)->whereIn('id', $activeRecipes->pluck('product_id'))->orderBy('name')->get();
        // La leche se toma sola de las entregas más antiguas disponibles (ver ProductionBatchService).
        $availableMilk = $service->availableMilkQuery()->with('producer.user')->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'liters' => (float) $d->liters,
                'date' => $d->delivery_date?->format('d/m/Y'),
                'producer' => $d->producer?->user?->fullname ?? 'Productor',
            ])->values();
        $ingredientStocks = Ingredient::where('active', true)->where('is_milk', false)->get()
            ->map(fn ($i) => ['id' => $i->id, 'name' => $i->name, 'unit' => $i->unit, 'stock' => $i->currentStock()])->values();

        return view('plant.production-create', compact('products', 'recipesForJs', 'availableMilk', 'ingredientStocks'));
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

        return redirect()->route('plant.production', ['tab' => 'historial'])->with('success', $message);
    }

    public function recipeCreate()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $ingredients = Ingredient::where('active', true)->orderBy('is_milk', 'desc')->orderBy('name')->get();

        return view('plant.recipe-create', compact('products', 'ingredients'));
    }

    public function recipeStore(Request $request, RecipeService $recipeService)
    {
        $data = $request->validate(RecipeService::RULES, RecipeService::MESSAGES);
        $recipeService->create($data, Auth::id());

        return redirect()->route('plant.production', ['tab' => 'recetas'])->with('success', 'Receta creada correctamente.');
    }

    /**
     * Elimina la receta y sus ingredientes. Los lotes ya producidos no dependen de la receta.
     */
    public function recipeDestroy(Recipe $recipe)
    {
        $name = $recipe->name;
        $recipe->delete();

        return redirect()->route('plant.production', ['tab' => 'recetas'])->with('success', "Receta \"{$name}\" eliminada.");
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

    public function salesStore(Request $request, SaleService $saleService)
    {
        $data = $request->validate(SaleService::RULES);

        $items = collect($request->items ?? [])->filter(fn ($i) => ($i['quantity'] ?? 0) > 0);

        if ($items->isEmpty()) {
            return back()->withErrors('Debe agregar al menos un producto.');
        }

        $saleService->create($data, $items, Auth::id());

        return redirect()->route('plant.sales', ['tab' => 'historial'])->with('success', 'Venta registrada correctamente.');
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
