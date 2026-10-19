<?php

namespace App\Modules\Reviews\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorReview;
use App\Modules\Reviews\Http\Requests\ModerateReviewRequest;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = DoctorReview::with(['doctor', 'user'])->latest()->get();

        return view('reviews.admin.index', compact('reviews'));
    }

    public function update(ModerateReviewRequest $request, DoctorReview $doctor_review): RedirectResponse
    {
        $doctor_review->update(['status' => (int) $request->validated()['status']]);

        return redirect()->route('admin.doctor-reviews.index')->with('success', 'Review status updated.');
    }

    public function destroy(DoctorReview $doctor_review): RedirectResponse
    {
        $doctor_review->delete();

        return back()->with('success', 'Review deleted.');
    }
}
