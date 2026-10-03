<?php

namespace App\Modules\Ambulance\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Ambulance\Http\Requests\UpdateAmbulanceRequest;
use App\Modules\Ambulance\Models\AmbulanceRequest;
use App\Modules\Ambulance\Services\AmbulanceService;

class AmbulanceRequestController extends Controller
{
    /**
     * Newest requests first so fresh emergencies sit on top.
     */
    public function index()
    {
        $requests = AmbulanceRequest::with('user')->latest()->paginate(10);

        return view('ambulance.index', compact('requests'));
    }

    public function update(UpdateAmbulanceRequest $request, AmbulanceRequest $ambulanceRequest, AmbulanceService $service)
    {
        $service->updateStatus($ambulanceRequest, (int) $request->validated()['status']);

        return back()->with('success', 'Ambulance request updated successfully.');
    }
}
