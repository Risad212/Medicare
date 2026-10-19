<?php

namespace App\Modules\Reviews\Http\Requests;

use App\Models\Appointment;
use App\Models\DoctorReview;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReviewRequest extends FormRequest
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
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Appointment $appointment */
            $appointment = $this->route('appointment');
            $user = $this->user();

            if ((int) $appointment->user_id !== (int) $user?->id) {
                $validator->errors()->add('rating', __('messages.reviews.forbidden'));

                return;
            }

            if ((int) $appointment->status !== 2) {
                $validator->errors()->add('rating', __('messages.reviews.not_completed'));

                return;
            }

            if (DoctorReview::where('appointment_id', $appointment->id)->exists()) {
                $validator->errors()->add('rating', __('messages.reviews.already_reviewed'));
            }
        });
    }
}
