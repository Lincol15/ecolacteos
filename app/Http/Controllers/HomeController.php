<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Notification;
use App\Models\PlantConfig;
use App\Models\Producer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $products = Product::where('show_in_catalog', true)->where('is_active', true)
            ->orderBy('sort_order')->orderBy('name')->get();
        $notifications = auth()->check()
            ? Notification::visibleForUser(auth()->user())->limit(3)->get()
            : collect([]);
        $stats = [
            'producers' => Producer::where('status', 'activo')->count(),
            'products' => $products->count(),
            'price' => PlantConfig::getValue('precio_litro_leche', 1.70),
        ];
        $featuredProducts = $products->take(3);

        return view('welcome', compact('products', 'featuredProducts', 'stats', 'notifications'));
    }

    public function about()
    {
        return view('public.about');
    }

    public function catalog()
    {
        $products = Product::where('show_in_catalog', true)->where('is_active', true)
            ->orderBy('sort_order')->orderBy('name')->paginate(12);

        return view('public.catalog', compact('products'));
    }

    public function productShow(string $slug)
    {
        $product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $related = Product::where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->limit(4)->get();

        return view('public.product', compact('product', 'related'));
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function contactStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:200',
            'message' => 'required|string|max:2000',
        ]);
        ContactMessage::create($data);

        return redirect()->route('contact')->with('success', 'Gracias por su mensaje. Nos pondremos en contacto a la brevedad.');
    }

    public function login()
    {
        if (auth()->check()) {
            return redirect()->intended($this->dashboardByRole(auth()->user()));
        }

        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        $loginField = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'dni';
        if (Auth::attempt([$loginField => $request->email, 'password' => $request->password], $request->filled('remember'))) {
            $request->session()->regenerate();
            $user = auth()->user();
            if (! $user->active) {
                Auth::logout();

                return back()->withErrors(['email' => 'Su cuenta se encuentra inactiva.'])->onlyInput('email');
            }

            return redirect()->intended($this->dashboardByRole($user));
        }

        return back()->withErrors(['email' => 'Credenciales inválidas.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function dashboardByRole($user): string
    {
        return match ($user->role) {
            'admin', 'gerente' => route('admin.dashboard'),
            'acopiador' => route('collector.dashboard'),
            'control_calidad' => route('quality.dashboard'),
            'trabajador_planta' => route('plant.dashboard'),
            'productor' => route('producer.dashboard'),
            default => route('home'),
        };
    }
}
