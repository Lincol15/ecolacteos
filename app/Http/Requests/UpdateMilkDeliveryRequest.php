<?php

namespace App\Http\Requests;

use App\Models\MilkDelivery;
use App\Models\PlantConfig;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMilkDeliveryRequest extends FormRequest
{
    /**
     * Admin y gerente pueden corregir cualquier entrega. El acopiador solo las suyas,
     * mientras sigan "registradas" y no hayan sido recibidas en planta.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var MilkDelivery $delivery */
        $delivery = $this->route('delivery');

        if (! $user) {
            return false;
        }

        if ($user->isManager()) {
            return true;
        }

        return $user->isCollector()
            && $delivery->collector_id === $user->id
            && $delivery->status === 'registrado'
            && ! $delivery->recibido;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'liters' => 'sometimes|required|numeric|min:0.1|max:'.$this->maxLiters(),
            'temperature' => 'sometimes|nullable|numeric|min:2|max:10',
            'observations' => 'sometimes|nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'liters.min' => 'La cantidad mínima es 0.1 litros',
            'liters.max' => 'La cantidad máxima permitida es '.number_format($this->maxLiters(), 0).' litros',
            'temperature.min' => 'La temperatura no puede ser menor a 2°C',
            'temperature.max' => 'La temperatura no puede exceder 10°C',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('liters')) {
            $this->merge([
                'liters' => (float) str_replace(',', '.', (string) $this->liters),
            ]);
        }

        // Igual que en el registro: el acopiador no fija temperatura.
        if ($this->user()?->role === 'acopiador') {
            $this->replace($this->except(['temperature']));
        }
    }

    protected function maxLiters(): float
    {
        return (float) PlantConfig::getValue('litros_maximos_entrega', 10000);
    }
}
