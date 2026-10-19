<?php

namespace App\Modules\Publishing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GalleryImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isUpdate = $this->route('gallery') !== null;

        return [
            'image' => [$isUpdate ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'caption' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'status' => ['required', 'in:0,1'],
        ];
    }
}
