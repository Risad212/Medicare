<?php

namespace App\Modules\Beds\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Beds\Http\Requests\RoomRequest;
use App\Modules\Beds\Models\Room;
use App\Modules\Beds\Models\Ward;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with(['ward'])->withCount('beds')->latest()->paginate(10);

        return view('rooms.index', compact('rooms'));
    }

    public function create()
    {
        $wards = Ward::orderBy('name')->get(['id', 'name']);

        return view('rooms.create', compact('wards'));
    }

    public function store(RoomRequest $request)
    {
        $room = Room::create($request->validated());

        return redirect()->route('admin.rooms.show', $room)
            ->with('success', 'Room created successfully.');
    }

    public function show(Room $room)
    {
        $room->load(['ward', 'beds.currentPatient']);

        return view('rooms.show', compact('room'));
    }

    public function edit(Room $room)
    {
        $wards = Ward::orderBy('name')->get(['id', 'name']);

        return view('rooms.edit', compact('room', 'wards'));
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
