<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\MilkDelivery;
use App\Models\Notification;
use App\Models\ProductionBatch;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        if ($data['status'] === 'en_proceso' && !$batch->started_at) {
            $update['started_at'] = now();
        }
        if (in_array($data['status'], ['terminado', 'vendido', 'desperdicio'], true) && !$batch->finished_at) {
            $update['finished_at'] = now();
        }
        $batch->update($update);
        return back()->with('success', 'Estado actualizado.');
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
