<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Mismos indicadores que el panel web de administración y gerencia.
     */
    public function __invoke(DashboardService $dashboardService): JsonResponse
    {
        return response()->json([
            // Las sumas de MySQL llegan como texto: se entregan como número.
            'data' => collect($dashboardService->stats(Carbon::today()))
                ->map(fn ($value) => (float) $value)
                ->put('users_count', User::count())
                ->all(),
        ]);
    }
}
