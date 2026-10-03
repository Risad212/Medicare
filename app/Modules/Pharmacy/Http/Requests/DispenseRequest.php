<?php

namespace App\Modules\Pharmacy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DispenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'medicine_id' => ['required', 'integer', Rule::exists('medicines', 'id')],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
        ];
    }
}
