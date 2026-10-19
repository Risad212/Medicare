<?php

namespace App\Modules\Publishing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FaqRequest extends FormRequest
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
        $faqId = $this->route('faq')?->id;

        return [
            'question' => ['required', 'string', 'max:255', Rule::unique('faqs', 'question')->ignore($faqId)],
            'answer' => ['required', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'status' => ['required', 'in:0,1'],
        ];
    }
}
