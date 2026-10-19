<?php

namespace App\Modules\Reviews\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DoctorReview;
use App\Modules\Reviews\Http\Requests\StoreReviewRequest;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validated();

        DoctorReview::create([
            'doctor_id' => $appointment->doctor_id,
            'appointment_id' => $appointment->id,
            'user_id' => $request->user()->id,
            'rating' => (int) $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'status' => DoctorReview::STATUS_PENDING,
        ]);

        return back()->with('success', __('messages.reviews.submitted'));
    }
}
