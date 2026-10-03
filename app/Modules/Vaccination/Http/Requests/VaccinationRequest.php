<?php

namespace App\Modules\Vaccination\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VaccinationRequest extends FormRequest
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
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id'), 'required_without:patient_id'],
            'patient_id' => ['nullable', 'integer', Rule::exists('patients', 'id'), 'required_without:user_id'],
            'child_name' => ['nullable', 'string', 'max:255'],
            'vaccine_name' => ['required', 'string', 'max:255'],
            'dose_number' => ['required', 'integer', 'min:1', 'max:10'],
            'date_given' => ['nullable', 'date', 'required_if:status,1'],
            'next_due_date' => ['nullable', 'date'],
            'administered_by' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:0,1,2'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required_without' => 'Select a patient account or a clinic register patient.',
            'patient_id.required_without' => 'Select a patient account or a clinic register patient.',
            'date_given.required_if' => 'Completed vaccinations need the date given.',
        ];
    }
}
