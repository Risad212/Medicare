<?php

namespace App\Http\Requests\Admin;

use App\Models\Doctor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDoctorRequest extends FormRequest
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
        $userId = $this->route('doctor') instanceof Doctor
            ? $this->route('doctor')->user_id
            : null;

        // Route may be /admin/doctors/{id} (string) — resolve via model when needed.
        if ($userId === null && is_numeric($this->route('id'))) {
            $userId = Doctor::find($this->route('id'))?->user_id;
        } elseif ($userId === null && is_numeric($this->route('doctor'))) {
            $userId = Doctor::find($this->route('doctor'))?->user_id;
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:8'],
            'degree' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'specialist' => ['nullable', 'string', 'max:255'],
            'services' => ['nullable', 'string'],
            'availability' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'in:0,1'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
