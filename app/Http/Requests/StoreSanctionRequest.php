<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSanctionRequest extends FormRequest
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
            'producer_id' => 'required|exists:producers,id',
            'type' => 'required|in:calidad,incumplimiento,fraude,pesaje,otro',
            'motivo' => 'required|string|max:150',
            'description' => 'nullable|string|max:2000',
            'amount' => 'nullable|numeric|min:0',
            'sanction_date' => 'required|date',
        ];
    }
}
