<?php

namespace App\Modules\Ambulance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAmbulanceRequest extends FormRequest
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
            'status' => ['required', 'integer', 'in:0,1,2,3'],
        ];
    }
}
