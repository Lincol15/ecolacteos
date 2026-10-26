<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CollectorPaymentResource;
use App\Models\CollectorPayment;
use App\Services\CollectorPayrollService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CollectorPaymentController extends Controller
{
    /**
     * Sueldos de acopiadores. El acopiador ve solo los suyos.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $query = CollectorPayment::with('collector');
        if ($user->isCollector()) {
            $query->where('collector_id', $user->id);
        }
        if ($month = $request->month) {
            $query->whereDate('period_month', Carbon::parse($month.'-01'));
        }

        return CollectorPaymentResource::collection($query->latest('period_month')->paginate(100)->withQueryString());
    }

    /**
     * Genera la planilla mensual (misma regla que la web).
     */
    public function generate(Request $request, CollectorPayrollService $payrollService): JsonResponse
    {
        $data = $request->validate(['month' => 'required|date_format:Y-m']);
        $result = $payrollService->generateForMonth(Carbon::parse($data['month'].'-01'), $request->user()->id);

        return response()->json(['data' => $result], 201);
    }

    public function markPaid(Request $request, CollectorPayment $collectorPayment, CollectorPayrollService $payrollService): CollectorPaymentResource
    {
        $data = $request->validate([
            'payment_method' => 'nullable|in:transferencia,efectivo,cheque',
            'transaction_number' => 'nullable|string|max:50',
        ]);

        return new CollectorPaymentResource($payrollService->markAsPaid($collectorPayment, $data)->load('collector'));
    }
}
