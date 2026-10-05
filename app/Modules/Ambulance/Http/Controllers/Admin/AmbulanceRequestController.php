<?php

namespace App\Modules\Ambulance\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Ambulance\Http\Requests\UpdateAmbulanceRequest;
use App\Modules\Ambulance\Models\AmbulanceRequest;
use App\Modules\Ambulance\Services\AmbulanceService;
use App\Support\AdminNavigation;
use Inertia\Inertia;
use Inertia\Response;

class AmbulanceRequestController extends Controller
{
    /**
     * Newest requests first so fresh emergencies sit on top.
     */
    public function index(): Response
    {
        $requests = AmbulanceRequest::with('user')->latest()->paginate(10);

        $requests->through(fn (AmbulanceRequest $request) => [
            'id' => $request->id,
            'requesterName' => $request->requester_name,
            'requesterPhone' => $request->requester_phone,
            'pickupAddress' => $request->pickup_address,
            'destination' => $request->destination,
            'emergencyType' => $request->emergency_type,
            'status' => $request->status,
            'statusLabel' => $request->status_label,
            'receivedAt' => $request->created_at->diffForHumans(),
            'transitions' => array_map(
                fn (int $status) => ['value' => $status, 'label' => AmbulanceRequest::STATUSES[$status]],
                AmbulanceRequest::TRANSITIONS[$request->status] ?? []
            ),
        ]);

        return Inertia::render('Admin/Ambulance/Index', [
            'requests' => [
                'data' => $requests->items(),
                'currentPage' => $requests->currentPage(),
                'lastPage' => $requests->lastPage(),
                'firstItem' => $requests->firstItem(),
                'lastItem' => $requests->lastItem(),
                'total' => $requests->total(),
                'previousPageUrl' => $requests->previousPageUrl(),
                'nextPageUrl' => $requests->nextPageUrl(),
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.ambulance-requests.index'),
                'updateBase' => url('/admin/ambulance-requests'),
            ],
        ]);
    }

    public function update(UpdateAmbulanceRequest $request, AmbulanceRequest $ambulanceRequest, AmbulanceService $service)
    {
        $service->updateStatus($ambulanceRequest, (int) $request->validated()['status']);

        return back()->with('success', 'Ambulance request updated successfully.');
    }
}
