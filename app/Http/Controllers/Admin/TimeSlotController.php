<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use App\Support\AdminNavigation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TimeSlotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $timeSlots = TimeSlot::orderBy('time')->get();

        return Inertia::render('Admin/TimeSlots/Index', [
            'timeSlots' => $timeSlots->map(fn (TimeSlot $timeSlot) => [
                'id' => $timeSlot->id,
                'time' => $timeSlot->time,
                'status' => (int) $timeSlot->status,
            ])->values(),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.time-slots.index'),
                'create' => route('admin.time-slots.create'),
                'editBase' => url('/admin/time-slots'),
                'deleteBase' => url('/admin/time-slots'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/TimeSlots/Create', [
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.time-slots.index'),
                'store' => route('admin.time-slots.store'),
            ],
        ]);
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
            'time' => $request->time,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return back()->with('success', 'Time slot added successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TimeSlot $timeSlot): Response
    {
        return Inertia::render('Admin/TimeSlots/Edit', [
            'timeSlot' => [
                'id' => $timeSlot->id,
                'time' => $timeSlot->time,
                'status' => (bool) $timeSlot->status,
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.time-slots.index'),
                'update' => route('admin.time-slots.update', $timeSlot),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TimeSlot $timeSlot)
    {
        $request->validate([
            'time' => 'required|string|unique:time_slots,time,'.$timeSlot->id,
        ]);

        $timeSlot->update([
            'time' => $request->time,
            'status' => $request->has('status') ? 1 : 0,
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
