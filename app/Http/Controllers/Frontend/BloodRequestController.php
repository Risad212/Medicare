<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BloodIssue;
use App\Models\BloodRequest;

class BloodRequestController extends Controller
{
    /**
     * The authenticated patient's own blood requests.
     * Never exposes other patients' records.
     */
    public function index()
    {
        $requests = BloodRequest::with(['bloodGroup', 'doctor'])
            ->where('patient_id', auth()->id())
            ->latest()
            ->paginate(10);

        $issues = BloodIssue::with('bloodGroup')
            ->where('patient_id', auth()->id())
            ->latest('issue_date')
            ->paginate(10);

        return view('frontend.blood-requests.index', [
            'requests' => $requests,
            'issues' => $issues,
        ]);
    }
}
