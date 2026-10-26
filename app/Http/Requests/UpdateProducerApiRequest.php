<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Edición de un productor desde la app móvil: datos personales (tabla users) y de la finca.
 * Todos los campos son opcionales; solo se cambian los que se envían.
 */
class UpdateProducerApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole(['admin']);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('producer')->user_id;

        return [
            'name' => 'sometimes|required|string|max:100',
            'lastname' => 'sometimes|nullable|string|max:100',
            'dni' => ['sometimes', 'required', 'digits:8', Rule::unique('users', 'dni')->ignore($userId)],
            'phone' => 'sometimes|required|digits:9',
            'address' => 'sometimes|nullable|string|max:255',
            'comunidad' => 'sometimes|required|string|max:150',
            'farm_name' => 'sometimes|nullable|string|max:150',
            'cows_count' => 'sometimes|nullable|integer|min:0|max:1000',
            'daily_avg_liters' => 'sometimes|nullable|numeric|min:0|max:10000',
            'status' => 'sometimes|required|in:activo,inactivo',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'dni.unique' => 'Ya existe un usuario con ese DNI.',
            'dni.digits' => 'El DNI debe tener 8 dígitos.',
            'phone.digits' => 'El teléfono debe tener 9 dígitos.',
        ];
    }
}
