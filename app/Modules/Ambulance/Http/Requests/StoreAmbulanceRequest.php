<?php

namespace App\Modules\Ambulance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAmbulanceRequest extends FormRequest
{
    /**
     * Emergency flow is public — guests included.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'requester_name' => ['required', 'string', 'max:255'],
            'requester_phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'pickup_address' => ['required', 'string', 'max:2000'],
            'destination' => ['nullable', 'string', 'max:255'],
            'emergency_type' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'requester_phone.regex' => 'The phone number may only contain digits, spaces and + - ( ).',
        ];
    }
}
