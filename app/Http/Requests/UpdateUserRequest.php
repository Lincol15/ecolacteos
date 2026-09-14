<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole(['admin', 'gerente']);
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id ?? $this->route('user');

        return [
            'name' => 'required|string|max:100',
            'lastname' => 'nullable|string|max:100',
            'dni' => 'required|string|max:12|unique:users,dni,' . $userId,
            'email' => 'required|email|max:150|unique:users,email,' . $userId,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'role' => 'required|in:admin,gerente,acopiador,control_calidad,trabajador_planta,productor',
            'active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'dni.required' => 'El DNI es obligatorio',
            'dni.unique' => 'Este DNI ya está registrado',
            'email.required' => 'El email es obligatorio',
            'email.unique' => 'Este email ya está registrado',
            'role.required' => 'Debe seleccionar un rol',
        ];
    }
}
