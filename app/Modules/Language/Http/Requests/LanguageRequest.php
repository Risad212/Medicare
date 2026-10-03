<?php

namespace App\Modules\Language\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $id = $this->route('language')?->id;

        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:10', 'regex:/^[a-z]{2}(-[A-Z]{2})?$/', "unique:languages,code,{$id}"],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (is_array($data)) {
            $data['is_active'] = (bool) ($data['is_active'] ?? false);
        }

        return $data;
    }
}
