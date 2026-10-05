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
use App\Support\AdminNavigation;
use Inertia\Inertia;
use Inertia\Response;

class BedController extends Controller
{
    /**
     * Bed availability dashboard: per-ward counts + color-coded grid.
     */
    public function index(): Response
    {
        $wards = Ward::with(['rooms.beds.currentPatient'])
            ->withCount([
                'beds',
                'beds as available_beds_count' => fn ($q) => $q->where('status', Bed::STATUS_AVAILABLE),
                'beds as occupied_beds_count' => fn ($q) => $q->where('status', Bed::STATUS_OCCUPIED),
            ])
            ->orderBy('name')
            ->get();

        $wards = $wards->map(fn (Ward $ward) => [
            'id' => $ward->id,
            'name' => $ward->name,
            'availableBedsCount' => $ward->available_beds_count,
            'bedsCount' => $ward->beds_count,
            'rooms' => $ward->rooms->map(fn (Room $room) => [
                'id' => $room->id,
                'roomNumber' => $room->room_number,
                'roomType' => $room->room_type,
                'beds' => $room->beds->map(fn (Bed $bed) => [
                    'id' => $bed->id,
                    'bedNumber' => $bed->bed_number,
                    'status' => $bed->status,
                    'statusLabel' => $bed->status_label,
                    'patientName' => $bed->currentPatient?->name,
                    'routes' => [
                        'edit' => route('admin.beds.edit', $bed),
                        'assign' => route('admin.beds.assign', $bed),
                        'discharge' => route('admin.beds.discharge', $bed),
                        'delete' => route('admin.beds.destroy', $bed),
                    ],
                ]),
            ]),
        ]);

        $patients = User::where('role', 'patient')->orderBy('name')->limit(200)
            ->get(['id', 'name'])
            ->map(fn (User $patient) => ['id' => $patient->id, 'name' => $patient->name]);

        return Inertia::render('Admin/Beds/Index', [
            'wards' => $wards,
            'patients' => $patients,
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.beds.index'),
                'create' => route('admin.beds.create'),
                'wards' => route('admin.wards.index'),
                'rooms' => route('admin.rooms.index'),
            ],
        ]);
    }

    public function create(): Response
    {
        $rooms = Room::with('ward')->orderBy('room_number')->get();

        return Inertia::render('Admin/Beds/Form', [
            'mode' => 'create',
            'rooms' => $rooms->map(fn (Room $room) => [
                'id' => $room->id,
                'roomNumber' => $room->room_number,
                'wardName' => $room->ward?->name,
            ]),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.beds.index'),
                'store' => route('admin.beds.store'),
            ],
        ]);
    }

    public function store(BedRequest $request)
    {
        $bed = Bed::create($request->validated());

        return redirect()->route('admin.beds.index')
            ->with('success', 'Bed created successfully.');
    }

    public function edit(Bed $bed): Response
    {
        $rooms = Room::with('ward')->orderBy('room_number')->get();

        return Inertia::render('Admin/Beds/Form', [
            'mode' => 'edit',
            'bed' => [
                'id' => $bed->id,
                'roomId' => $bed->room_id,
                'bedNumber' => $bed->bed_number,
                'status' => $bed->status,
            ],
            'rooms' => $rooms->map(fn (Room $room) => [
                'id' => $room->id,
                'roomNumber' => $room->room_number,
                'wardName' => $room->ward?->name,
            ]),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.beds.index'),
                'update' => route('admin.beds.update', $bed),
            ],
        ]);
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
