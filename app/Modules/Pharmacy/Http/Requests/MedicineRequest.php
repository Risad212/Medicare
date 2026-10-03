<?php

namespace App\Modules\Pharmacy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MedicineRequest extends FormRequest
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
        $medicineId = $this->route('medicine')?->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('medicines')->ignore($medicineId)],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:30'],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'low_stock_threshold' => ['required', 'integer', 'min:0', 'max:1000000'],
            'expiry_date' => ['nullable', 'date'],
        ];
    }
}
