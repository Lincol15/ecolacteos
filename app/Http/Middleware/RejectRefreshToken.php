<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RejectRefreshToken
{
    /**
     * El refresh token solo sirve para renovar la sesión en /auth/refresh: no autoriza el resto de la API.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()?->currentAccessToken();

        if ($token && $token->can('refresh') && ! $token->can('access')) {
            return response()->json(['message' => 'Token no válido para esta operación.'], 401);
        }

        return $next($request);
    }
}
