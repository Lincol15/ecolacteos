<?php

namespace App\Services;

use App\Models\MilkDelivery;
use App\Models\QualityReport;
use Illuminate\Support\Facades\DB;

class QualityReportService
{
    /**
     * Crear un nuevo reporte de calidad
     */
    public function create(array $data, int $analystId): QualityReport
    {
        return DB::transaction(function () use ($data, $analystId) {
            // Generar código de muestra si no se proporciona
            if (!isset($data['sample_code']) || empty($data['sample_code'])) {
                $data['sample_code'] = $this->generateSampleCode($data['milk_delivery_id']);
            }

            // Crear reporte
            $report = QualityReport::create([
                ...$data,
                'analyst_id' => $analystId,
                'analyzed_at' => now(),
            ]);

            // Actualizar estado de la entrega
            $delivery = MilkDelivery::find($data['milk_delivery_id']);
            $delivery->update([
                'has_quality_analysis' => true,
                'status' => $data['result'] === 'rechazado' ? 'rechazado' : 'analizado',
            ]);

            return $report->fresh(['milkDelivery.producer.user', 'analyst']);
        });
    }

    /**
     * Actualizar un reporte de calidad
     */
    public function update(QualityReport $report, array $data): QualityReport
    {
        return DB::transaction(function () use ($report, $data) {
            $report->update($data);

            // Si cambió el resultado, actualizar la entrega
            if (isset($data['result'])) {
                $report->milkDelivery->update([
                    'status' => $data['result'] === 'rechazado' ? 'rechazado' : 'analizado',
                ]);
            }

            return $report->fresh();
        });
    }

    /**
     * Evaluar automáticamente un reporte basándose en parámetros
     */
    public function autoEvaluate(array $params): array
    {
        $issues = [];
        $result = 'aprobado';
        $rejectionReason = 'ninguno';

        // Verificar grasa
        if (isset($params['grasa_pct']) && $params['grasa_pct'] < 3.2) {
            $issues[] = 'Grasa baja';
            $result = 'rechazado';
            $rejectionReason = 'baja_grasa';
        }

        // Verificar proteína
        if (isset($params['proteina_pct']) && $params['proteina_pct'] < 2.9) {
            $issues[] = 'Proteína baja';
            if ($result !== 'rechazado') {
                $result = 'rechazado';
                $rejectionReason = 'baja_proteina';
            }
        }

        // Verificar agua añadida
        if (isset($params['agua_aniadida_pct']) && $params['agua_aniadida_pct'] > 5) {
            $issues[] = 'Exceso de agua añadida';
            $result = 'rechazado';
            $rejectionReason = 'exceso_agua';
        }

        // Verificar pH
        if (isset($params['ph']) && ($params['ph'] < 6.4 || $params['ph'] > 6.8)) {
            $issues[] = 'pH fuera de rango';
            if ($result !== 'rechazado') {
                $result = 'rechazado';
                $rejectionReason = 'ph_anormal';
            }
        }

        // Verificar punto de congelación
        if (isset($params['punto_congelacion']) && ($params['punto_congelacion'] < -0.550 || $params['punto_congelacion'] > -0.510)) {
            $issues[] = 'Punto de congelación anormal';
            if ($result !== 'rechazado') {
                $result = 'observado';
                $rejectionReason = 'congelacion_anormal';
            }
        }

        // Si hay issues menores pero no es rechazado, marcar como observado
        if (!empty($issues) && $result === 'aprobado') {
            $result = 'observado';
        }

        return [
            'result' => $result,
            'rejection_reason' => $rejectionReason,
            'auto_observations' => !empty($issues)
                ? 'Evaluación automática: ' . implode(', ', $issues)
                : 'Todos los parámetros dentro del rango normal',
        ];
    }

    /**
     * Obtener estadísticas de calidad para un periodo
     */
    public function getQualityStats(string $startDate, string $endDate): array
    {
        $reports = QualityReport::whereBetween('analyzed_at', [$startDate, $endDate])->get();

        if ($reports->isEmpty()) {
            return [
                'total' => 0,
                'approved' => 0,
                'rejected' => 0,
                'observed' => 0,
                'approval_rate' => 0,
            ];
        }

        return [
            'total' => $reports->count(),
            'approved' => $reports->where('result', 'aprobado')->count(),
            'rejected' => $reports->where('result', 'rechazado')->count(),
            'observed' => $reports->where('result', 'observado')->count(),
            'acceptable' => $reports->where('result', 'aceptable')->count(),
            'approval_rate' => round(($reports->where('result', 'aprobado')->count() / $reports->count()) * 100, 1),
            'avg_grasa' => $reports->avg('grasa_pct'),
            'avg_proteina' => $reports->avg('proteina_pct'),
            'avg_ph' => $reports->avg('ph'),
        ];
    }

    /**
     * Generar código de muestra
     */
    protected function generateSampleCode(int $deliveryId): string
    {
        return 'MUE-' . $deliveryId . '-' . strtoupper(substr(md5((string)$deliveryId . now()), 0, 4));
    }
}
