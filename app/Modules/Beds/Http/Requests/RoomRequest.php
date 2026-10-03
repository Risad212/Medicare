<?php

namespace App\Modules\Beds\Http\Requests;

use App\Modules\Beds\Models\Room;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoomRequest extends FormRequest
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
        $roomId = $this->route('room')?->id;

        return [
            'ward_id' => ['required', 'integer', Rule::exists('wards', 'id')],
            'room_number' => [
                'required', 'string', 'max:50',
                Rule::unique('rooms')->where(fn ($q) => $q->where('ward_id', $this->input('ward_id')))->ignore($roomId),
            ],
            'room_type' => ['required', 'in:'.implode(',', Room::TYPES)],
        ];
    }
}
