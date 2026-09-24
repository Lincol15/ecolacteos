<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Permite al administrador "ver como" otro usuario sin conocer su contraseña.
 * El id del administrador original se guarda en sesión para poder volver.
 */
class ImpersonationController extends Controller
{
    public const SESSION_KEY = 'impersonator_id';

    public function start(User $user): RedirectResponse
    {
        $admin = Auth::user();

        if ($user->is($admin) || $user->isAdmin()) {
            return back()->with('error', 'No puede ver el sistema como otro administrador.');
        }

        if (! $user->active) {
            return back()->with('error', 'No puede ver el sistema como un usuario inactivo.');
        }

        Auth::login($user);
        session()->put(self::SESSION_KEY, $admin->id);

        return redirect()->route('home');
    }

    public function stop(): RedirectResponse
    {
        $impersonator = User::find(session()->pull(self::SESSION_KEY));

        if (! $impersonator || ! $impersonator->isAdmin() || ! $impersonator->active) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            return redirect()->route('login')->with('error', 'La sesión de administrador ya no es válida.');
        }

        Auth::login($impersonator);

        return redirect()->route('admin.users')->with('success', 'Volviste a tu cuenta de administrador.');
    }
}
