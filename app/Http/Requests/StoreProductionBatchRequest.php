<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductionBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole(['admin', 'gerente', 'trabajador_planta']);
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'input_milk_liters' => 'required|numeric|min:1|max:100000',
            'output_units' => 'required|numeric|min:0',
            'production_date' => 'required|date|before_or_equal:today',
            'expiration_date' => 'nullable|date|after:production_date',
            'supervised_by' => 'nullable|exists:users,id',
            'recipe_notes' => 'nullable|string|max:2000',
            'quality_notes' => 'nullable|string|max:2000',
            'status' => 'required|in:planeado,en_proceso,curando,terminado',
            'milk_ids' => 'nullable|array',
            'milk_ids.*' => 'exists:milk_deliveries,id',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Debe seleccionar un producto',
            'input_milk_liters.required' => 'Debe ingresar la cantidad de leche utilizada',
            'input_milk_liters.min' => 'La cantidad mínima de leche es 1 litro',
            'output_units.required' => 'Debe ingresar la cantidad producida',
            'production_date.required' => 'La fecha de producción es obligatoria',
            'production_date.before_or_equal' => 'La fecha de producción no puede ser futura',
            'expiration_date.after' => 'La fecha de vencimiento debe ser posterior a la fecha de producción',
            'status.required' => 'Debe seleccionar un estado',
        ];
    }
}
