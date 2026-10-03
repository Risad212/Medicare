<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PrescriptionRequest extends FormRequest
{
    /**
     * Authorize if a doctor is logged in.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'appointment_id' => ['nullable', 'integer', Rule::exists('appointments', 'id')],
            'patient_name' => ['required_if:appointment_id,null', 'string', 'max:255'],
            'age' => ['nullable', 'integer', 'min:0', 'max:130'],
            'gender' => ['nullable', 'in:1,2,3'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'symptoms' => ['nullable', 'string', 'max:5000'],
            'diagnosis' => ['required', 'string', 'max:5000'],
            'advice' => ['nullable', 'string', 'max:5000'],
            'follow_up_date' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.medicine_name' => ['required', 'string', 'max:255'],
            'items.*.dosage' => ['nullable', 'string', 'max:255'],
            'items.*.frequency' => ['nullable', 'string', 'max:255'],
            'items.*.duration' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'string', 'max:255'],
            'items.*.instructions' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Add at least one medicine to the prescription.',
            'items.min' => 'Add at least one medicine to the prescription.',
            'items.*.medicine_name.required' => 'Each medicine row needs a medicine name.',
        ];
    }
}
