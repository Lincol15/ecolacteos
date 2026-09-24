<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        return view('public.cart', $this->cartSummary());
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        $quantity = max(1, (int) $request->input('quantity', 1));
        $cart = session('cart', []);
        $cart[$product->id] = ($cart[$product->id] ?? 0) + $quantity;
        session(['cart' => $cart]);

        return back()->with('success', "{$product->name} agregado al carrito.");
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $quantity = (int) $request->input('quantity', 1);
        $cart = session('cart', []);

        if ($quantity <= 0) {
            unset($cart[$product->id]);
        } else {
            $cart[$product->id] = $quantity;
        }

        session(['cart' => $cart]);

        return back();
    }

    public function remove(Product $product): RedirectResponse
    {
        $cart = session('cart', []);
        unset($cart[$product->id]);
        session(['cart' => $cart]);

        return back()->with('success', 'Producto quitado del carrito.');
    }

    public function checkout(): View|RedirectResponse
    {
        $summary = $this->cartSummary();
        if ($summary['items']->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Tu carrito está vacío.');
        }

        $customer = Auth::guard('customer')->user();

        return view('public.checkout', [...$summary, 'customer' => $customer]);
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Tu carrito está vacío.');
        }

        $data = $request->validate([
            'client_name' => 'required|string|max:150',
            'client_email' => 'required|email|max:150',
            'client_phone' => 'nullable|string|max:20',
            'client_address' => 'nullable|string',
            'payment_method' => 'required|in:efectivo,transferencia,yape,plin,cheque,tarjeta',
            'notes' => 'nullable|string',
        ]);

        $products = Product::whereIn('id', array_keys($cart))->where('is_active', true)->get()->keyBy('id');
        if ($products->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Los productos de tu carrito ya no están disponibles.');
        }

        $sale = DB::transaction(function () use ($data, $cart, $products) {
            $subtotal = 0;
            foreach ($cart as $productId => $qty) {
                if ($product = $products->get($productId)) {
                    $subtotal += (float) $product->unit_price * $qty;
                }
            }
            $tax = round($subtotal * 0.18, 2);

            $sale = Sale::create([
                ...$data,
                'customer_id' => Auth::guard('customer')->id(),
                'sale_date' => Carbon::today()->toDateString(),
                'invoice_number' => 'PW-'.Carbon::today()->format('Ymd').'-'.str_pad((string) (Sale::count() + 1), 4, '0', STR_PAD_LEFT),
                'subtotal' => round($subtotal, 2),
                'tax' => $tax,
                'discount' => 0,
                'total_amount' => round($subtotal + $tax, 2),
                'payment_status' => 'pendiente',
                'sale_type' => 'pedido_web',
                'served_by' => null,
            ]);

            foreach ($cart as $productId => $qty) {
                $product = $products->get($productId);
                if (! $product) {
                    continue;
                }

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $product->unit_price,
                    'subtotal' => round((float) $product->unit_price * $qty, 2),
                ]);

                Inventory::create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit' => $product->unit,
                    'movement_type' => 'salida',
                    'unit_cost' => round($product->unit_price * 0.65, 2),
                    'total_value' => round($qty * ($product->unit_price * 0.65), 2),
                    'location' => 'Pedido Web',
                    'related_sale_id' => $sale->id,
                ]);
            }

            return $sale;
        });

        session()->forget('cart');

        return redirect()->route('catalog')->with('success', "¡Pedido #{$sale->invoice_number} recibido! Nos pondremos en contacto para confirmar la entrega.");
    }

    /**
     * @return array{items: Collection, total: float}
     */
    private function cartSummary(): array
    {
        $cart = session('cart', []);
        $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');

        $items = collect($cart)->map(function ($qty, $productId) use ($products) {
            $product = $products->get($productId);
            if (! $product) {
                return null;
            }

            return [
                'product' => $product,
                'quantity' => $qty,
                'subtotal' => round((float) $product->unit_price * $qty, 2),
            ];
        })->filter()->values();

        return [
            'items' => $items,
            'total' => round((float) $items->sum('subtotal'), 2),
        ];
    }
}
