<?php

namespace App\Modules\Publishing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlogCommentRequest extends FormRequest
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
        if ($this->has('name') || $this->has('email') || $this->has('comment')) {
            return [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'comment' => ['required', 'string', 'max:2000'],
                'status' => ['nullable', 'boolean'],
            ];
        }

        return [];
    }
}
