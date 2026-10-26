<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Models\Producer;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends Controller
{
    /**
     * Pagos de productores. El productor ve solo los suyos.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $query = Payment::with('producer.user');

        if ($user->isProducer()) {
            $query->where('producer_id', $user->producer?->id ?? 0);
        } elseif ($producerId = $request->producer_id) {
            $query->where('producer_id', $producerId);
        }
        if ($status = $request->status) {
            $query->where('status', $status);
        }

        $perPage = min(max($request->integer('per_page', 50), 1), 100);

        return PaymentResource::collection($query->latest('period_end')->paginate($perPage)->withQueryString());
    }

    /**
     * Vista previa de la liquidación de cada productor activo en el período, calculada por PaymentService
     * (litros, bonos, descuentos), sin guardar nada.
     */
    public function preview(Request $request, PaymentService $paymentService): JsonResponse
    {
        $data = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);
        $start = Carbon::parse($data['period_start']);
        $end = Carbon::parse($data['period_end']);

        $rows = Producer::where('status', 'activo')->with('user')->orderBy('code')->get()
            ->map(function (Producer $producer) use ($paymentService, $start, $end) {
                $preview = $paymentService->calculatePayment($producer, $start, $end);

                return [
                    'producer_id' => $producer->id,
                    'producer_name' => $producer->user?->fullname,
                    'comunidad' => $producer->comunidad,
                    'can_generate' => $preview['can_generate'],
                    'total_liters' => (float) ($preview['total_liters'] ?? 0),
                    'price_per_liter' => (float) ($preview['precio_por_litro'] ?? 0),
                    'total_amount' => (float) ($preview['total_amount'] ?? 0),
                    'already_paid_period' => Payment::where('producer_id', $producer->id)
                        ->whereDate('period_start', $start)->whereDate('period_end', $end)->exists(),
                ];
            });

        return response()->json(['data' => $rows]);
    }

    public function generate(Request $request, PaymentService $paymentService): JsonResponse
    {
        $data = $request->validate(PaymentService::GENERATE_RULES);
        $generated = $paymentService->generateForProducers(
            Carbon::parse($data['period_start']), Carbon::parse($data['period_end']), $data['producer_ids'], $request->user()->id
        );

        return response()->json(['data' => ['generated' => $generated]], 201);
    }

    public function markPaid(Request $request, Payment $payment, PaymentService $paymentService): PaymentResource
    {
        $data = $request->validate([
            'payment_method' => 'nullable|in:transferencia,efectivo,cheque',
            'transaction_number' => 'nullable|string|max:50',
        ]);

        return new PaymentResource($paymentService->processPayment($payment, $data)->load('producer.user'));
    }
}
