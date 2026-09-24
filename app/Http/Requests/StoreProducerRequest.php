<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProducerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'lastname' => 'nullable|string|max:100',
            'dni' => 'required|digits:8|unique:users,dni',
            'email' => 'required|email|max:150|unique:users,email',
            'phone' => 'required|digits:9',
            'comunidad' => 'required|string|max:150',
            'farm_name' => 'nullable|string|max:150',
            'cows_count' => 'nullable|integer|min:0|max:1000',
            'daily_avg_liters' => 'nullable|numeric|min:0|max:10000',
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'dni.required' => 'El DNI es obligatorio',
            'dni.digits' => 'El DNI debe tener exactamente 8 dígitos',
            'dni.unique' => 'Este DNI ya está registrado',
            'email.required' => 'El email es obligatorio',
            'email.unique' => 'Este email ya está registrado',
            'phone.required' => 'El teléfono es obligatorio',
            'phone.digits' => 'El teléfono debe tener exactamente 9 dígitos',
            'comunidad.required' => 'La comunidad es obligatoria',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
        ];
    }
}
