<?php

namespace App\Observers;

use App\Models\QualityReport;

class QualityReportObserver
{
    /**
     * Handle the QualityReport "creating" event.
     */
    public function creating(QualityReport $report): void
    {
        // Validar rangos de parámetros críticos
        if (isset($report->agua_aniadida_pct) && $report->agua_aniadida_pct > 10) {
            throw new \Exception('El porcentaje de agua añadida excede el límite permitido (10%)');
        }

        if (isset($report->ph)) {
            if ($report->ph < 6.0 || $report->ph > 7.5) {
                throw new \Exception('El pH está fuera del rango permitido (6.0-7.5)');
            }
        }

        // Establecer analyzed_at si no está presente
        if (!isset($report->analyzed_at)) {
            $report->analyzed_at = now();
        }

        // Validar coherencia entre result y rejection_reason
        if ($report->result === 'aprobado' && $report->rejection_reason !== 'ninguno') {
            $report->rejection_reason = 'ninguno';
        }

        if ($report->result === 'rechazado' && $report->rejection_reason === 'ninguno') {
            throw new \Exception('Debe especificar una razón de rechazo cuando el resultado es "rechazado"');
        }
    }

    /**
     * Handle the QualityReport "created" event.
     */
    public function created(QualityReport $report): void
    {
        // Actualizar la entrega asociada
        $report->milkDelivery->update([
            'has_quality_analysis' => true,
            'status' => $report->result === 'rechazado' ? 'rechazado' : 'analizado',
        ]);
    }

    /**
     * Handle the QualityReport "updating" event.
     */
    public function updating(QualityReport $report): void
    {
        // Si cambia el resultado, actualizar la entrega
        if ($report->isDirty('result')) {
            $report->milkDelivery->update([
                'status' => $report->result === 'rechazado' ? 'rechazado' : 'analizado',
            ]);
        }

        // Validar coherencia de datos al actualizar
        if ($report->result === 'aprobado' && $report->rejection_reason !== 'ninguno') {
            $report->rejection_reason = 'ninguno';
        }
    }

    /**
     * Handle the QualityReport "deleting" event.
     */
    public function deleting(QualityReport $report): void
    {
        // Actualizar la entrega asociada al eliminar el reporte
        $report->milkDelivery->update([
            'has_quality_analysis' => false,
            'status' => 'registrado',
        ]);
    }
}
