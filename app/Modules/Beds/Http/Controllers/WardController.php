<?php

namespace App\Modules\Beds\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Beds\Http\Requests\WardRequest;
use App\Modules\Beds\Models\Ward;

class WardController extends Controller
{
    public function index()
    {
        $wards = Ward::withCount(['rooms', 'beds'])->latest()->paginate(10);

        return view('wards.index', compact('wards'));
    }

    public function create()
    {
        return view('wards.create');
    }

    public function store(WardRequest $request)
    {
        $ward = Ward::create($request->validated());

        return redirect()->route('admin.wards.show', $ward)
            ->with('success', 'Ward created successfully.');
    }

    public function show(Ward $ward)
    {
        $ward->load(['rooms.beds.currentPatient']);

        return view('wards.show', compact('ward'));
    }

    public function edit(Ward $ward)
    {
        return view('wards.edit', compact('ward'));
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
