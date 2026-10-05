<?php

namespace App\Modules\Beds\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Beds\Http\Requests\WardRequest;
use App\Modules\Beds\Models\Ward;
use App\Support\AdminNavigation;
use Inertia\Inertia;
use Inertia\Response;

class WardController extends Controller
{
    public function index(): Response
    {
        $wards = Ward::withCount(['rooms', 'beds'])->latest()->paginate(10);

        $wards->through(fn (Ward $ward) => [
            'id' => $ward->id,
            'name' => $ward->name,
            'description' => $ward->description,
            'roomsCount' => $ward->rooms_count,
            'bedsCount' => $ward->beds_count,
        ]);

        return Inertia::render('Admin/Wards/Index', [
            'wards' => [
                'data' => $wards->items(),
                'currentPage' => $wards->currentPage(),
                'lastPage' => $wards->lastPage(),
                'firstItem' => $wards->firstItem(),
                'lastItem' => $wards->lastItem(),
                'total' => $wards->total(),
                'previousPageUrl' => $wards->previousPageUrl(),
                'nextPageUrl' => $wards->nextPageUrl(),
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.wards.index'),
                'create' => route('admin.wards.create'),
                'bedDashboard' => route('admin.beds.index'),
                'showBase' => url('/admin/wards'),
                'editBase' => url('/admin/wards'),
                'deleteBase' => url('/admin/wards'),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Wards/Form', [
            'mode' => 'create',
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.wards.index'),
                'store' => route('admin.wards.store'),
            ],
        ]);
    }

    public function store(WardRequest $request)
    {
        $ward = Ward::create($request->validated());

        return redirect()->route('admin.wards.show', $ward)
            ->with('success', 'Ward created successfully.');
    }

    public function show(Ward $ward): Response
    {
        $ward->load(['rooms.beds.currentPatient']);

        return Inertia::render('Admin/Wards/Show', [
            'ward' => [
                'id' => $ward->id,
                'name' => $ward->name,
                'description' => $ward->description,
                'rooms' => $ward->rooms->map(fn ($room) => [
                    'id' => $room->id,
                    'roomNumber' => $room->room_number,
                    'roomType' => $room->room_type,
                    'bedsCount' => $room->beds->count(),
                    'occupiedCount' => $room->beds->where('status', 1)->count(),
                ]),
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.wards.index'),
                'edit' => route('admin.wards.edit', $ward),
                'roomShowBase' => url('/admin/rooms'),
            ],
        ]);
    }

    public function edit(Ward $ward): Response
    {
        return Inertia::render('Admin/Wards/Form', [
            'mode' => 'edit',
            'ward' => [
                'id' => $ward->id,
                'name' => $ward->name,
                'description' => $ward->description,
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.wards.index'),
                'update' => route('admin.wards.update', $ward),
            ],
        ]);
    }

    public function update(WardRequest $request, Ward $ward)
    {
        $ward->update($request->validated());

        return redirect()->route('admin.wards.show', $ward)
            ->with('success', 'Ward updated successfully.');
    }

    public function destroy(Ward $ward)
    {
        if ($ward->rooms()->exists()) {
            return back()->withErrors(['ward' => 'This ward still has rooms. Move or delete them first.']);
        }

        $ward->delete();

        return redirect()->route('admin.wards.index')
            ->with('success', 'Ward deleted successfully.');
    }
}
