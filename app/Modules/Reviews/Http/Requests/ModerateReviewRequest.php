<?php

namespace App\Modules\Reviews\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModerateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (string) $this->user()?->role === 'admin';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'integer', Rule::in([0, 1, 2])],
        ];
    }
}
