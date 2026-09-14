<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectByRole
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($request->routeIs('login') || $request->routeIs('register') || $request->is('/')) {
                return redirect($this->getDashboardRoute($user));
            }
        }
        return $next($request);
    }

    private function getDashboardRoute($user): string
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
