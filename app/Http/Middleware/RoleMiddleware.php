<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Debe iniciar sesión para acceder a esta sección.');
        }

        $user = auth()->user();

        if (!$user->active) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Su cuenta se encuentra inactiva. Contacte con administración.');
        }

        if (!empty($roles) && !$user->hasRole($roles)) {
            abort(403, 'No tiene permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
