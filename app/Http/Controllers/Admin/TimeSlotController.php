<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use Illuminate\Http\Request;

class TimeSlotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $timeSlots = TimeSlot::orderBy('time')->get();
        return view('backend.timeslots.index', compact('timeSlots'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.timeslots.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'time' => 'required|string|unique:time_slots,time',
        ]);

        TimeSlot::create([
            'time'   => $request->time,
            'status' => $request->has('status') ? 1 : 0,
            'order'  => $request->order ?? 0,
        ]);

        return back()->with('success', 'Time slot added successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TimeSlot $timeSlot)
    {
        return view('backend.timeslots.edit', compact('timeSlot'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TimeSlot $timeSlot)
    {
        $request->validate([
            'time' => 'required|string|unique:time_slots,time,' . $timeSlot->id,
        ]);

        $timeSlot->update([
            'time'   => $request->time,
            'status' => $request->has('status') ? 1 : 0,
            'order'  => $request->order ?? 0,
        ]);

        return back()->with('success', 'Time slot updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimeSlot $timeSlot)
    {
        $timeSlot->delete();
        return back()->with('success', 'Time slot deleted successfully!');
    }
}