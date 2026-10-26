<?php

namespace App\Http\Requests;

use App\Services\QualityReportService;

/**
 * Mismas reglas y permisos que el registro web de análisis LACTOMAT. Si la app no
 * indica resultado, se calcula con la evaluación automática del backend.
 */
class StoreQualityReportApiRequest extends StoreQualityReportRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('result')) {
            $evaluation = app(QualityReportService::class)->autoEvaluate($this->all());
            $this->merge([
                'result' => $evaluation['result'],
                'rejection_reason' => $evaluation['rejection_reason'],
                'observations' => $this->filled('observations')
                    ? $this->input('observations')
                    : $evaluation['auto_observations'],
            ]);
        }

        parent::prepareForValidation();
    }
}
