<?php

namespace App\Http\Requests;

use App\Models\PlantConfig;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMilkDeliveryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole(['acopiador', 'admin', 'gerente']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'producer_id' => 'required|exists:producers,id',
            'route_stop_id' => 'nullable|exists:route_stops,id',
            'collection_route_id' => 'nullable|exists:collection_routes,id',
            'liters' => 'required|numeric|min:0.1|max:'.$this->maxLiters(),
            'temperature' => 'nullable|numeric|min:2|max:10',
            'price_per_liter' => 'nullable|numeric|min:0|max:100',
            'delivery_date' => 'required|date|before_or_equal:today',
            'container_type' => 'nullable|in:caneca,bidon,cisterna',
            'containers_count' => 'nullable|integer|min:1|max:100',
            'vehicle_plate' => 'nullable|string|max:10',
            'observations' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'producer_id.required' => 'Debe seleccionar un productor',
            'producer_id.exists' => 'El productor seleccionado no existe',
            'liters.required' => 'Debe ingresar la cantidad de litros',
            'liters.min' => 'La cantidad mínima es 0.1 litros',
            'liters.max' => 'La cantidad máxima permitida es '.number_format($this->maxLiters(), 0).' litros',
            'temperature.min' => 'La temperatura no puede ser menor a 2°C',
            'temperature.max' => 'La temperatura no puede exceder 10°C',
            'delivery_date.required' => 'La fecha de entrega es obligatoria',
            'delivery_date.before_or_equal' => 'La fecha de entrega no puede ser futura',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalizar datos si es necesario
        if ($this->has('liters')) {
            $this->merge([
                'liters' => (float) str_replace(',', '.', (string) $this->liters),
            ]);
        }
    }

    protected function maxLiters(): float
    {
        return (float) PlantConfig::getValue('litros_maximos_entrega', 10000);
    }
}
