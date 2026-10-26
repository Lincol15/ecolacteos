<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductionCartRequest;
use App\Http\Resources\ProductionBatchResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\RecipeResource;
use App\Http\Resources\SaleResource;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\Recipe;
use App\Models\Sale;
use App\Services\ProductionBatchService;
use App\Services\RecipeService;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

/**
 * Planta: catálogo, recetas, producción por lotes y ventas. Reutiliza los mismos servicios que la web.
 */
class PlantController extends Controller
{
    public function products(): AnonymousResourceCollection
    {
        return ProductResource::collection(Product::where('is_active', true)->orderBy('name')->get());
    }

    public function ingredients(): JsonResponse
    {
        $ingredients = Ingredient::where('active', true)->orderBy('is_milk', 'desc')->orderBy('name')->get()
            ->map(fn (Ingredient $i) => [
                'id' => $i->id,
                'name' => $i->name,
                'unit' => $i->unit,
                'is_milk' => (bool) $i->is_milk,
                'stock' => $i->currentStock(),
            ]);

        return response()->json(['data' => $ingredients]);
    }

    public function recipes(): AnonymousResourceCollection
    {
        return RecipeResource::collection(
            Recipe::with('product', 'recipeIngredients.ingredient')->latest()->get()
        );
    }

    public function storeRecipe(Request $request, RecipeService $recipeService)
    {
        $data = $request->validate(RecipeService::RULES, RecipeService::MESSAGES);
        $recipe = $recipeService->create($data, $request->user()->id);

        return (new RecipeResource($recipe->load('product', 'recipeIngredients.ingredient')))->response()->setStatusCode(201);
    }

    public function batches(Request $request): AnonymousResourceCollection
    {
        $query = ProductionBatch::with('product');
        if ($status = $request->status) {
            $query->where('status', $status);
        }

        return ProductionBatchResource::collection($query->latest('production_date')->latest('id')->paginate(100));
    }

    /**
     * Registra un carrito de producción. La leche se toma sola de las entregas disponibles
     * más antiguas y se descuentan los insumos de cada receta (ProductionBatchService).
     */
    public function storeProduction(StoreProductionCartRequest $request, ProductionBatchService $service)
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
                $request->user()->id
            );
        } catch (\RuntimeException $e) {
            throw ValidationException::withMessages(['items' => $e->getMessage()]);
        }

        return ProductionBatchResource::collection($batches->each->load('product'))->response()->setStatusCode(201);
    }

    public function sales(Request $request): AnonymousResourceCollection
    {
        $query = Sale::with('items.product');
        if ($from = $request->from) {
            $query->where('sale_date', '>=', $from);
        }

        return SaleResource::collection($query->latest('sale_date')->latest('id')->paginate(100));
    }

    public function storeSale(Request $request, SaleService $saleService)
    {
        $data = $request->validate(SaleService::RULES);
        $items = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_id' => 'nullable|exists:production_batches,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ])['items'];

        $sale = $saleService->create($data, collect($items), $request->user()->id);

        return (new SaleResource($sale->load('items.product')))->response()->setStatusCode(201);
    }
}
