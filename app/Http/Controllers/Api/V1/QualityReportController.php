<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQualityReportApiRequest;
use App\Http\Resources\QualityReportResource;
use App\Models\QualityReport;
use App\Services\QualityReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class QualityReportController extends Controller
{
    /**
     * El productor ve solo sus análisis (ligados directamente o por su entrega); calidad y gestión ven todos.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $query = QualityReport::with('producer.user', 'milkDelivery.producer.user');

        if ($user->isProducer()) {
            $producerId = $user->producer?->id ?? 0;
            $query->where(fn ($q) => $q->where('producer_id', $producerId)
                ->orWhereHas('milkDelivery', fn ($dq) => $dq->where('producer_id', $producerId)));
        } elseif ($producerId = $request->producer_id) {
            $query->where('producer_id', $producerId);
        }
        if ($result = $request->result) {
            $query->where('result', $result);
        }

        $perPage = min(max($request->integer('per_page', 50), 1), 100);

        return QualityReportResource::collection($query->latest()->paginate($perPage)->withQueryString());
    }

    /**
     * Corregir el resultado de un análisis (gestión). QualityReportService actualiza también la entrega.
     */
    public function update(Request $request, QualityReport $qualityReport, QualityReportService $reportService): QualityReportResource
    {
        $data = $request->validate([
            'result' => 'required|in:aprobado,rechazado,aceptable,observado,pendiente',
            'rejection_reason' => 'required_if:result,rechazado|nullable|in:ninguno,baja_grasa,baja_proteina,exceso_agua,acidez_alta,ph_anormal,congelacion_anormal,contaminacion',
            'observations' => 'nullable|string|max:2000',
        ]);
        $data['rejection_reason'] ??= 'ninguno';

        $report = $reportService->update($qualityReport, $data);

        return new QualityReportResource($report->load('producer.user', 'milkDelivery.producer.user'));
    }

    public function store(StoreQualityReportApiRequest $request, QualityReportService $reportService)
    {
        $data = $request->validated();
        unset($data['ticket_photo']);

        $report = $reportService->create($data, $request->user()->id);

        return (new QualityReportResource($report->load('producer.user', 'milkDelivery.producer.user')))
            ->response()
            ->setStatusCode(201);
    }
}
