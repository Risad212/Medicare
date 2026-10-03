<?php

namespace App\Modules\Beds\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignBedRequest extends FormRequest
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
            'patient_user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'patient')],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_user_id.exists' => 'Select a patient account.',
        ];
    }
}
