<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductionCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole(['admin', 'gerente', 'trabajador_planta']);
    }

    public function rules(): array
    {
        return [
            'production_date' => 'required|date|before_or_equal:today',
            'status' => 'required|in:planeado,en_proceso,curando,terminado',
            'supervised_by' => 'nullable|exists:users,id',
            'recipe_notes' => 'nullable|string|max:2000',
            'quality_notes' => 'nullable|string|max:2000',
            'milk_ids' => 'nullable|array|min:1',
            'milk_ids.*' => 'exists:milk_deliveries,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.output_units' => 'required|numeric|min:0.01',
            'items.*.expiration_date' => 'nullable|date|after:production_date',
        ];
    }

    public function messages(): array
    {
        return [
            'production_date.required' => 'La fecha de producción es obligatoria',
            'production_date.before_or_equal' => 'La fecha de producción no puede ser futura',
            'status.required' => 'Debe seleccionar un estado',
            'milk_ids.required' => 'Debe seleccionar al menos una entrega de leche para este carrito de producción',
            'milk_ids.min' => 'Debe seleccionar al menos una entrega de leche para este carrito de producción',
            'items.required' => 'Debe agregar al menos un producto al carrito de producción',
            'items.min' => 'Debe agregar al menos un producto al carrito de producción',
            'items.*.product_id.required' => 'Debe seleccionar un producto para cada línea del carrito',
            'items.*.output_units.required' => 'Debe ingresar la cantidad producida para cada línea del carrito',
            'items.*.expiration_date.after' => 'La fecha de vencimiento debe ser posterior a la fecha de producción',
        ];
    }
}
