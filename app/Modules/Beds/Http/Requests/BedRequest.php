<?php

namespace App\Modules\Beds\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Occupied (1) is never set via CRUD — only via assign/discharge.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $bedId = $this->route('bed')?->id;

        return [
            'room_id' => ['required', 'integer', Rule::exists('rooms', 'id')],
            'bed_number' => [
                'required', 'string', 'max:50',
                Rule::unique('beds')->where(fn ($q) => $q->where('room_id', $this->input('room_id')))->ignore($bedId),
            ],
            'status' => ['required', 'in:0,2'],
        ];
    }
}
