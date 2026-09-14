<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProducerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole(['admin', 'gerente']);
    }

    public function rules(): array
    {
        $producerId = $this->route('producer')->id ?? $this->route('producer');

        return [
            'code' => 'required|string|max:20|unique:producers,code,' . $producerId,
            'farm_name' => 'nullable|string|max:150',
            'zone' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'cows_count' => 'nullable|integer|min:0|max:1000',
            'daily_avg_liters' => 'nullable|numeric|min:0|max:10000',
            'status' => 'required|in:activo,inactivo,suspendido',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'El código del productor es obligatorio',
            'code.unique' => 'Este código ya está en uso',
            'status.required' => 'Debe seleccionar un estado',
            'cows_count.max' => 'La cantidad máxima de vacas es 1000',
            'latitude.between' => 'La latitud debe estar entre -90 y 90',
            'longitude.between' => 'La longitud debe estar entre -180 y 180',
        ];
    }
}
