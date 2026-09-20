<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorOffDay;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search ? str_replace(['%', '_'], ['\%', '\_'], $request->search) : null;
        $doctors = Doctor::when($search, function ($query) use ($search) {
            $query->where('name', 'like', '%'.$search.'%')
                ->orWhere('department', 'like', '%'.$search.'%')
                ->orWhere('specialist', 'like', '%'.$search.'%');
        })->paginate(10);
        $departments = Department::where('status', 1)->get();

        return view('backend.doctors.index', compact('doctors', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::where('status', 1)->get();

        return view('backend.doctors.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Create User first
        DB::transaction(function () use ($request) {
            $user = new User([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $user->role = 'doctor';
            $user->save();

            // Doctor data - whitelist safe fields only
            $data = $request->only(['name', 'degree', 'department', 'specialist', 'services', 'availability', 'phone']);
            if (isset($data['services'])) {
                $data['services'] = strip_tags($data['services']);
            }
            $data['slug'] = Str::slug($request->name).'-'.uniqid();
            $data['user_id'] = $user->id;
            $data['status'] = $request->has('status') ? (int) $request->status : 1;

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('doctors', 'public');
            }

            Doctor::create($data);
        });

        return back()->with('success', 'Doctor added successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $doctor = Doctor::findOrFail($id);
        $departments = Department::where('status', 1)->get();

        return view('backend.doctors.edit', compact('doctor', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $doctor = Doctor::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$doctor->user_id,
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'password' => 'nullable|string|min:8',
            'degree' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'specialist' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'status' => 'nullable|in:0,1',
        ]);

        // Update User
        DB::transaction(function () use ($request, $doctor) {
            if ($doctor->user_id) {
                $userData = [
                    'name' => $request->name,
                    'email' => $request->email,
                ];

                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($request->password);
                }

                User::where('id', $doctor->user_id)->update($userData);
            }

            $data = $request->only(['name', 'degree', 'department', 'specialist', 'services', 'availability', 'phone', 'status']);
            if (isset($data['services'])) {
                $data['services'] = strip_tags($data['services']);
            }
            $data['slug'] = Str::slug($request->name);

            if ($request->hasFile('image')) {
                if ($doctor->image) {
                    Storage::disk('public')->delete($doctor->image);
                }
                $data['image'] = $request->file('image')->store('doctors', 'public');
            }

            $doctor->update($data);
        });

        return back()->with('success', 'Doctor updated successfully!');
    }

    /**
     * Show the weekly availability editor for a doctor.
     */
    public function availability(Doctor $doctor)
    {
        $weekdays = collect([
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ]);

        $slots = TimeSlot::where('status', 1)->orderBy('time')->get();
        $openByWeekday = $doctor->schedules
            ->whereIn('time_slot_id', $slots->pluck('id'))
            ->groupBy('weekday')
            ->map(fn ($rows) => $rows->pluck('time_slot_id')->all());
        $offDays = $doctor->offDays()->orderBy('date')->get();

        return view('backend.doctors.availability', compact('doctor', 'weekdays', 'slots', 'openByWeekday', 'offDays'));
    }

    /**
     * Persist the weekly schedule matrix for a doctor.
     */
    public function updateAvailability(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'schedules' => 'nullable|array',
            'schedules.*' => 'array',
            'schedules.*.*' => 'exists:time_slots,id',
        ]);

        // Array keys are weekdays (0=Sunday..6=Saturday) — reject anything else.
        foreach (array_keys($validated['schedules'] ?? []) as $weekday) {
            abort_unless(
                is_numeric($weekday) && (int) $weekday >= 0 && (int) $weekday <= 6,
                422,
                'Invalid weekday in schedule.'
            );
        }

        DB::transaction(function () use ($doctor, $validated) {
            $doctor->schedules()->delete();

            foreach ($validated['schedules'] ?? [] as $weekday => $slotIds) {
                foreach (array_values($slotIds) as $slotId) {
                    $doctor->schedules()->create([
                        'weekday' => (int) $weekday,
                        'time_slot_id' => $slotId,
                    ]);
                }
            }
        });

        return back()->with('success', 'Weekly availability saved for '.$doctor->name.'.');
    }

    /**
     * Add an off-day for a doctor.
     */
    public function storeOffDay(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'reason' => 'nullable|string|max:255',
        ]);

        $doctor->offDays()->firstOrCreate([
            'date' => $validated['date'],
        ], [
            'reason' => $validated['reason'] ?? null,
        ]);

        return back()->with('success', 'Off-day added for '.$doctor->name.'.');
    }

    /**
     * Remove an off-day.
     */
    public function destroyOffDay(DoctorOffDay $offDay)
    {
        $offDay->delete();

        return back()->with('success', 'Off-day removed.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $doctor = Doctor::findOrFail($id);

        // NEVER hard-delete a doctor who still owns patient history: the
        // appointments and lab-orders FKs cascade and would wipe that
        // patient record in one shot. Deactivate (status=0) instead.
        $hasHistory = $doctor->appointments()->exists() || $doctor->labOrders()->exists();
        if ($hasHistory) {
            return redirect()
                ->route('admin.doctors.index')
                ->with('error', 'This doctor has patient appointments or lab orders and cannot be deleted. Set the doctor to inactive instead.');
        }

        if ($doctor->image) {
            Storage::disk('public')->delete($doctor->image);
        }

        $doctor->delete();

        return redirect()
            ->route('admin.doctors.index')
            ->with('success', 'Doctor deleted successfully!');
    }
}
