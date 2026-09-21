<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\IngredientMovement;
use App\Models\Inventory;
use App\Models\MilkDelivery;
use App\Models\Product;
use App\Models\ProductionBatch;
use App\Models\Recipe;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductionBatchService
{
    /**
     * Da forma a las recetas activas para el carrito de producción en el front-end (JS).
     *
     * @param  Collection<int, Recipe>  $recipes
     * @return array<int, array{product_id:int, name:string, milk_liters_per_unit:float, ingredients:array<int, array{id:int, name:?string, unit:?string, qty:float}>}>
     */
    public function recipesForJs(Collection $recipes): array
    {
        return $recipes->map(fn (Recipe $r) => [
            'product_id' => $r->product_id,
            'name' => $r->name,
            'milk_liters_per_unit' => (float) $r->milk_liters_per_unit,
            'ingredients' => $r->nonMilkIngredients->map(fn ($ri) => [
                'id' => $ri->ingredient_id,
                'name' => $ri->ingredient?->name,
                'unit' => $ri->ingredient?->unit,
                'qty' => (float) $ri->quantity_per_unit,
            ])->values()->all(),
        ])->values()->all();
    }

    /**
     * Arma las líneas del carrito con su receta activa, leche e insumos necesarios.
     *
     * @param  array<int, array{product_id:int, output_units:float, expiration_date?:?string}>  $items
     * @return array{lines: Collection, ingredientNeeds: array<int, array{ingredient: Ingredient, needed: float}>}
     */
    public function planCart(array $items): array
    {
        $productIds = collect($items)->pluck('product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $recipes = Recipe::where('active', true)
            ->whereIn('product_id', $productIds)
            ->with('nonMilkIngredients.ingredient')
            ->get()->keyBy('product_id');

        $ingredientNeeds = [];
        $lines = collect();

        foreach ($items as $item) {
            $product = $products->get($item['product_id']);
            $recipe = $recipes->get($item['product_id']);
            $outputUnits = (float) $item['output_units'];
            $milkNeeded = $recipe ? round((float) $recipe->milk_liters_per_unit * $outputUnits, 2) : 0.0;

            $lineIngredients = [];
            if ($recipe) {
                foreach ($recipe->nonMilkIngredients as $ri) {
                    $needed = round((float) $ri->quantity_per_unit * $outputUnits, 2);
                    $lineIngredients[] = ['ingredient' => $ri->ingredient, 'needed' => $needed];
                    $ingredientNeeds[$ri->ingredient_id] ??= ['ingredient' => $ri->ingredient, 'needed' => 0.0];
                    $ingredientNeeds[$ri->ingredient_id]['needed'] += $needed;
                }
            }

            $lines->push([
                'product' => $product,
                'recipe' => $recipe,
                'output_units' => $outputUnits,
                'milk_needed' => $milkNeeded,
                'ingredients' => $lineIngredients,
                'expiration_date' => $item['expiration_date'] ?? null,
            ]);
        }

        return ['lines' => $lines, 'ingredientNeeds' => $ingredientNeeds];
    }

    /**
     * @param  array<int, array{ingredient: Ingredient, needed: float}>  $ingredientNeeds
     * @return string[]
     */
    public function ingredientShortages(array $ingredientNeeds): array
    {
        $shortages = [];
        foreach ($ingredientNeeds as $need) {
            $available = $need['ingredient']->currentStock();
            if ($need['needed'] > $available) {
                $shortages[] = "{$need['ingredient']->name}: se necesitan {$need['needed']} {$need['ingredient']->unit} en total, disponible {$available} {$need['ingredient']->unit}";
            }
        }

        return $shortages;
    }

    /**
     * Reparte las entregas de leche seleccionadas entre las líneas del carrito, según
     * cuánta leche necesita cada una (partiendo una misma entrega entre lotes si hace falta).
     *
     * @param  Collection<int, array>  $lines
     * @param  Collection<int, MilkDelivery>  $deliveries
     * @return array<int, array<int, array{delivery: MilkDelivery, liters: float}>>
     */
    public function allocateMilk(Collection $lines, Collection $deliveries): array
    {
        $totalSelected = round((float) $deliveries->sum('liters'), 2);
        $count = $lines->count();

        $weights = $lines->map(fn ($l) => (float) $l['milk_needed']);
        if ($weights->sum() <= 0) {
            // Ninguna línea tiene receta con proporción de leche: repartir según unidades producidas.
            $weights = $lines->map(fn ($l) => (float) $l['output_units']);
        }
        $totalWeight = $weights->sum();

        $allocatedPerLine = [];
        $runningTotal = 0.0;
        foreach ($weights->values() as $i => $weight) {
            if ($i === $count - 1) {
                $allocatedPerLine[$i] = round($totalSelected - $runningTotal, 2);

                continue;
            }
            $share = $totalWeight > 0 ? round($totalSelected * ($weight / $totalWeight), 2) : 0.0;
            $allocatedPerLine[$i] = $share;
            $runningTotal += $share;
        }

        $pool = $deliveries->values()->map(fn ($d) => ['delivery' => $d, 'remaining' => (float) $d->liters])->all();
        $pointer = 0;
        $result = [];
        foreach ($allocatedPerLine as $i => $need) {
            $result[$i] = [];
            while ($need > 0.005 && $pointer < count($pool)) {
                $take = min($pool[$pointer]['remaining'], $need);
                if ($take > 0.005) {
                    $result[$i][] = ['delivery' => $pool[$pointer]['delivery'], 'liters' => round($take, 2)];
                }
                $pool[$pointer]['remaining'] -= $take;
                $need -= $take;
                if ($pool[$pointer]['remaining'] <= 0.005) {
                    $pointer++;
                }
            }
        }

        return $result;
    }

    /**
     * Crea todos los lotes del carrito en una sola transacción: valida leche e insumos
     * disponibles para el conjunto y solo confirma si alcanza para todas las líneas.
     *
     * @param  array<int, array{product_id:int, output_units:float, expiration_date?:?string}>  $items
     * @param  array<int, int>  $milkDeliveryIds
     * @param  array{production_date:string, status:string, supervised_by:?int, recipe_notes:?string, quality_notes:?string}  $shared
     * @return Collection<int, ProductionBatch>
     *
     * @throws \RuntimeException cuando la leche seleccionada ya no está disponible o faltan insumos
     */
    public function createCart(array $items, array $milkDeliveryIds, array $shared, int $processedBy): Collection
    {
        $deliveries = MilkDelivery::whereIn('id', $milkDeliveryIds)
            ->where('status', '!=', 'rechazado')
            ->whereDoesntHave('batches')
            ->get();

        if ($deliveries->count() !== count($milkDeliveryIds)) {
            throw new \RuntimeException('Una o más entregas de leche seleccionadas ya no están disponibles (fueron usadas en otro lote o fueron rechazadas).');
        }

        $plan = $this->planCart($items);

        $shortages = $this->ingredientShortages($plan['ingredientNeeds']);
        if (! empty($shortages)) {
            throw new \RuntimeException('Insumos insuficientes para este carrito de producción: '.implode('; ', $shortages));
        }

        $milkAllocation = $this->allocateMilk($plan['lines'], $deliveries);
        $groupNumber = $plan['lines']->count() > 1
            ? 'CARRITO-'.Carbon::parse($shared['production_date'])->format('Ymd').'-'.str_pad((string) (ProductionBatch::whereNotNull('group_number')->count() + 1), 3, '0', STR_PAD_LEFT)
            : null;

        return DB::transaction(function () use ($plan, $milkAllocation, $shared, $processedBy, $groupNumber) {
            $sequence = ProductionBatch::count();
            $batches = collect();

            foreach ($plan['lines']->values() as $i => $line) {
                $sequence++;
                $inputMilkLiters = round(collect($milkAllocation[$i] ?? [])->sum('liters'), 2);
                $yield = $inputMilkLiters > 0 ? round(($line['output_units'] / $inputMilkLiters) * 100, 2) : 0;

                $batch = ProductionBatch::create([
                    'batch_number' => 'LOTE-'.Carbon::parse($shared['production_date'])->format('Ymd').'-'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
                    'group_number' => $groupNumber,
                    'product_id' => $line['product']->id,
                    'input_milk_liters' => $inputMilkLiters,
                    'output_units' => $line['output_units'],
                    'yield_percentage' => $yield,
                    'production_date' => $shared['production_date'],
                    'expiration_date' => $line['expiration_date'],
                    'supervised_by' => $shared['supervised_by'] ?? null,
                    'recipe_notes' => $shared['recipe_notes'] ?? null,
                    'quality_notes' => $shared['quality_notes'] ?? null,
                    'status' => $shared['status'],
                    'started_at' => $shared['status'] !== 'planeado' ? now() : null,
                    'finished_at' => $shared['status'] === 'terminado' ? now() : null,
                ]);

                foreach ($milkAllocation[$i] ?? [] as $alloc) {
                    $batch->milkDeliveries()->attach($alloc['delivery']->id, ['liters_used' => $alloc['liters']]);
                }

                foreach ($line['ingredients'] as $ing) {
                    IngredientMovement::create([
                        'ingredient_id' => $ing['ingredient']->id,
                        'movement_type' => 'salida',
                        'quantity' => $ing['needed'],
                        'production_batch_id' => $batch->id,
                        'processed_by' => $processedBy,
                        'notes' => "Uso en lote {$batch->batch_number} ({$line['recipe']->name})",
                    ]);
                }

                Inventory::create([
                    'production_batch_id' => $batch->id,
                    'product_id' => $line['product']->id,
                    'quantity' => $line['output_units'],
                    'unit' => $line['product']->unit,
                    'movement_type' => 'entrada',
                    'unit_cost' => round(($line['product']->unit_price * 0.65), 2),
                    'total_value' => round($line['output_units'] * ($line['product']->unit_price * 0.65), 2),
                    'location' => 'Cámara Principal',
                    'expiration_date' => $line['expiration_date'],
                    'reference_document' => $batch->batch_number,
                    'processed_by' => $processedBy,
                    'notes' => 'Ingreso lote producción',
                ]);

                $batches->push($batch);
            }

            return $batches;
        });
    }
}
