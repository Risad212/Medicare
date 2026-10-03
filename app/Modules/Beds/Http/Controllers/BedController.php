<?php

namespace App\Modules\Beds\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Beds\Http\Requests\AssignBedRequest;
use App\Modules\Beds\Http\Requests\BedRequest;
use App\Modules\Beds\Models\Bed;
use App\Modules\Beds\Models\Room;
use App\Modules\Beds\Models\Ward;
use App\Modules\Beds\Services\BedService;

class BedController extends Controller
{
    /**
     * Bed availability dashboard: per-ward counts + color-coded grid.
     */
    public function index()
    {
        $wards = Ward::with(['rooms.beds.currentPatient'])
            ->withCount([
                'beds',
                'beds as available_beds_count' => fn ($q) => $q->where('status', Bed::STATUS_AVAILABLE),
                'beds as occupied_beds_count' => fn ($q) => $q->where('status', Bed::STATUS_OCCUPIED),
            ])
            ->orderBy('name')
            ->get();

        $patients = User::where('role', 'patient')->orderBy('name')->limit(200)->get(['id', 'name']);

        return view('beds.dashboard', compact('wards', 'patients'));
    }

    public function create()
    {
        $rooms = Room::with('ward')->orderBy('room_number')->get();

        return view('beds.create', compact('rooms'));
    }

    public function store(BedRequest $request)
    {
        $bed = Bed::create($request->validated());

        return redirect()->route('admin.beds.index')
            ->with('success', 'Bed created successfully.');
    }

    public function edit(Bed $bed)
    {
        $rooms = Room::with('ward')->orderBy('room_number')->get();

        return view('beds.edit', compact('bed', 'rooms'));
    }

    public function update(BedRequest $request, Bed $bed)
    {
        if ($bed->is_occupied) {
            return back()->withErrors(['bed' => 'Discharge the patient before editing this bed.']);
        }

        $bed->update($request->validated());

        return redirect()->route('admin.beds.index')
            ->with('success', 'Bed updated successfully.');
    }

    public function destroy(Bed $bed)
    {
        if ($bed->is_occupied) {
            return back()->withErrors(['bed' => 'Discharge the patient before deleting this bed.']);
        }

        $bed->delete();

        return redirect()->route('admin.beds.index')
            ->with('success', 'Bed deleted successfully.');
    }

    public function assign(AssignBedRequest $request, Bed $bed, BedService $service)
    {
        $service->assign($bed, (int) $request->validated()['patient_user_id']);

        return back()->with('success', 'Patient assigned to bed successfully.');
    }

    public function discharge(Bed $bed, BedService $service)
    {
        $service->discharge($bed);

        return back()->with('success', 'Patient discharged successfully.');
    }
}
