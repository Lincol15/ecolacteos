<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('admin');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'target' => 'required|in:todos,productores,acopiadores,gerencia,admin',
            'user_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:200',
            'message' => 'required|string|max:2000',
            'priority' => 'required|in:baja,normal,alta,urgente',
            'is_active' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
        ];
    }
}
