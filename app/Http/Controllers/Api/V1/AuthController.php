<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Inicia sesión del personal (mismas credenciales que la web: email o DNI) y emite
     * un access token de corta duración y un refresh token para renovarlo.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $loginField = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'dni';
        $user = User::where($loginField, $credentials['login'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => 'Credenciales inválidas.',
            ]);
        }

        if (! $user->active) {
            return response()->json(['message' => 'Su cuenta se encuentra inactiva.'], 403);
        }

        $sessionName = ($credentials['device_name'] ?? 'app-movil').'#'.Str::uuid();

        return response()->json([
            'data' => [
                ...$this->issueTokens($user, $sessionName),
                'user' => new UserResource($user->load('producer')),
            ],
        ]);
    }

    /**
     * Cambia un refresh token válido por un par nuevo. El refresh token usado se revoca
     * (rotación), así que cada uno sirve una sola vez.
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentToken = $user->currentAccessToken();

        if (! in_array('refresh', $currentToken->abilities ?? [], true)) {
            return response()->json(['message' => 'Se requiere un refresh token.'], 401);
        }

        $user->tokens()->where('name', $currentToken->name)->delete();

        return response()->json([
            'data' => [
                ...$this->issueTokens($user, $currentToken->name),
                'user' => new UserResource($user->load('producer')),
            ],
        ]);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user()->load('producer'));
    }

    /**
     * Revoca el access token usado y el refresh token de la misma sesión (cierra sesión en este dispositivo).
     */
    public function logout(Request $request): Response
    {
        $user = $request->user();
        $user->tokens()->where('name', $user->currentAccessToken()->name)->delete();

        return response()->noContent();
    }

    /**
     * Crea el par de tokens de una sesión; ambos comparten el nombre para revocarlos juntos.
     *
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int, refresh_expires_in: int}
     */
    private function issueTokens(User $user, string $sessionName): array
    {
        $accessMinutes = config('sanctum.access_token_expiration');
        $refreshMinutes = config('sanctum.refresh_token_expiration');

        $accessToken = $user->createToken($sessionName, ['access'], now()->addMinutes($accessMinutes));
        $refreshToken = $user->createToken($sessionName, ['refresh'], now()->addMinutes($refreshMinutes));

        return [
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => $accessMinutes * 60,
            'refresh_expires_in' => $refreshMinutes * 60,
        ];
    }
}
