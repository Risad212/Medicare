<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnlinkGoogleRequest extends FormRequest
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
            // Re-authenticate before unlinking. A Google-only account has an
            // unknown random password, so this fails until the user sets one
            // (profile form or the password-reset flow) — preventing lockout.
            'current_password' => ['required', 'current_password'],
        ];
    }
}
