<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQualityReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole(['control_calidad', 'admin', 'gerente']);
    }

    public function rules(): array
    {
        return [
            'milk_delivery_id' => 'required|exists:milk_deliveries,id|unique:quality_reports,milk_delivery_id',
            'sample_code' => 'nullable|string|max:50',
            'grasa_pct' => 'nullable|numeric|min:0|max:10',
            'proteina_pct' => 'nullable|numeric|min:0|max:10',
            'lactosa_pct' => 'nullable|numeric|min:0|max:10',
            'solidos_no_grasos_pct' => 'nullable|numeric|min:0|max:15',
            'total_solidos_pct' => 'nullable|numeric|min:0|max:20',
            'agua_aniadida_pct' => 'nullable|numeric|min:0|max:100',
            'ph' => 'nullable|numeric|min:6|max:7.5',
            'punto_congelacion' => 'nullable|numeric|min:-1|max:0',
            'acidez_dornic' => 'nullable|integer|min:10|max:30',
            'densidad' => 'nullable|numeric|min:1.020|max:1.040',
            'observations' => 'nullable|string|max:2000',
            'result' => 'required|in:aprobado,rechazado,aceptable,observado',
            'rejection_reason' => 'required|in:ninguno,baja_grasa,baja_proteina,exceso_agua,acidez_alta,ph_anormal,congelacion_anormal,contaminacion',
        ];
    }

    public function messages(): array
    {
        return [
            'milk_delivery_id.required' => 'Debe seleccionar una entrega de leche',
            'milk_delivery_id.unique' => 'Esta entrega ya tiene un análisis de calidad registrado',
            'result.required' => 'Debe indicar el resultado del análisis',
            'rejection_reason.required' => 'Debe indicar la razón de rechazo',
            'ph.min' => 'El pH no puede ser menor a 6',
            'ph.max' => 'El pH no puede ser mayor a 7.5',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Si el resultado es aprobado, forzar rejection_reason a 'ninguno'
        if ($this->result === 'aprobado' && !$this->has('rejection_reason')) {
            $this->merge(['rejection_reason' => 'ninguno']);
        }
    }
}
