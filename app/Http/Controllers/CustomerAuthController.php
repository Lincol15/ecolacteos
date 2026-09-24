<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    public function showRegister(): View
    {
        return view('public.customer.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.unique' => 'Ya existe una cuenta con ese correo.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $customer = Customer::create($data);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->intended(route('cart.index'))->with('success', 'Cuenta creada. ¡Bienvenido/a!');
    }

    /**
     * El acceso de clientes y personal es único en la página de login general.
     */
    public function showLogin(): RedirectResponse
    {
        session()->reflash();

        return redirect()->route('login');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function account(): View
    {
        $customer = Auth::guard('customer')->user();
        $sales = $customer->sales()->with('items.product')->latest('sale_date')->paginate(10);

        return view('public.customer.account', compact('customer', 'sales'));
    }
}
