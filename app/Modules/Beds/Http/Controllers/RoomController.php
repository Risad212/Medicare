<?php

namespace App\Modules\Beds\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Beds\Http\Requests\RoomRequest;
use App\Modules\Beds\Models\Room;
use App\Modules\Beds\Models\Ward;
use App\Support\AdminNavigation;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    public function index(): Response
    {
        $rooms = Room::with(['ward'])->withCount('beds')->latest()->paginate(10);

        $rooms->through(fn (Room $room) => [
            'id' => $room->id,
            'roomNumber' => $room->room_number,
            'roomType' => $room->room_type,
            'wardName' => $room->ward?->name,
            'bedsCount' => $room->beds_count,
        ]);

        return Inertia::render('Admin/Rooms/Index', [
            'rooms' => [
                'data' => $rooms->items(),
                'currentPage' => $rooms->currentPage(),
                'lastPage' => $rooms->lastPage(),
                'firstItem' => $rooms->firstItem(),
                'lastItem' => $rooms->lastItem(),
                'total' => $rooms->total(),
                'previousPageUrl' => $rooms->previousPageUrl(),
                'nextPageUrl' => $rooms->nextPageUrl(),
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.rooms.index'),
                'create' => route('admin.rooms.create'),
                'bedDashboard' => route('admin.beds.index'),
                'showBase' => url('/admin/rooms'),
                'editBase' => url('/admin/rooms'),
                'deleteBase' => url('/admin/rooms'),
            ],
        ]);
    }

    public function create(): Response
    {
        $wards = Ward::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Rooms/Form', [
            'mode' => 'create',
            'wards' => $wards,
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.rooms.index'),
                'store' => route('admin.rooms.store'),
            ],
        ]);
    }

    public function store(RoomRequest $request)
    {
        $room = Room::create($request->validated());

        return redirect()->route('admin.rooms.show', $room)
            ->with('success', 'Room created successfully.');
    }

    public function show(Room $room): Response
    {
        $room->load(['ward', 'beds.currentPatient']);

        return Inertia::render('Admin/Rooms/Show', [
            'room' => [
                'id' => $room->id,
                'roomNumber' => $room->room_number,
                'roomType' => $room->room_type,
                'wardName' => $room->ward?->name,
                'beds' => $room->beds->map(fn ($bed) => [
                    'id' => $bed->id,
                    'bedNumber' => $bed->bed_number,
                    'statusLabel' => $bed->status_label,
                    'patientName' => $bed->currentPatient?->name,
                    'admittedAt' => $bed->admitted_at?->format('d M Y'),
                ]),
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.rooms.index'),
                'edit' => route('admin.rooms.edit', $room),
            ],
        ]);
    }

    public function edit(Room $room): Response
    {
        $wards = Ward::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Rooms/Form', [
            'mode' => 'edit',
            'room' => [
                'id' => $room->id,
                'wardId' => $room->ward_id,
                'roomNumber' => $room->room_number,
                'roomType' => $room->room_type,
            ],
            'wards' => $wards,
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.rooms.index'),
                'update' => route('admin.rooms.update', $room),
            ],
        ]);
    }

    public function update(RoomRequest $request, Room $room)
    {
        $room->update($request->validated());

        return redirect()->route('admin.rooms.show', $room)
            ->with('success', 'Room updated successfully.');
    }

    public function destroy(Room $room)
    {
        if ($room->beds()->exists()) {
            return back()->withErrors(['room' => 'This room still has beds. Move or delete them first.']);
        }

        $room->delete();

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Room deleted successfully.');
    }
}
