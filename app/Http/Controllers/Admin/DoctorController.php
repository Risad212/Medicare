<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDoctorRequest;
use App\Http\Requests\Admin\UpdateDoctorRequest;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorOffDay;
use App\Models\TimeSlot;
use App\Models\User;
use App\Support\AdminNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = $request->search ? str_replace(['%', '_'], ['\%', '\_'], $request->search) : null;
        $doctors = Doctor::when($search, function ($query) use ($search) {
            $query->where('name', 'like', '%'.$search.'%')
                ->orWhere('department', 'like', '%'.$search.'%')
                ->orWhere('specialist', 'like', '%'.$search.'%');
        })->paginate(10)->withQueryString();
        $departments = Department::where('status', 1)->get();

        $doctors->through(fn (Doctor $doctor) => [
            'id' => $doctor->id,
            'name' => $doctor->name,
            'image' => $doctor->image,
            'department' => $doctor->department,
            'specialist' => $doctor->specialist,
            'phone' => $doctor->phone,
            'status' => (int) $doctor->status,
        ]);

        return Inertia::render('Admin/Doctors/Index', [
            'doctors' => [
                'data' => $doctors->items(),
                'currentPage' => $doctors->currentPage(),
                'lastPage' => $doctors->lastPage(),
                'firstItem' => $doctors->firstItem(),
                'lastItem' => $doctors->lastItem(),
                'total' => $doctors->total(),
                'previousPageUrl' => $doctors->previousPageUrl(),
                'nextPageUrl' => $doctors->nextPageUrl(),
            ],
            'filters' => ['search' => (string) $request->query('search', '')],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.doctors.index'),
                'create' => route('admin.doctors.create'),
                'showBase' => url('/admin/doctors'),
                'availabilityBase' => url('/admin/doctors'),
                'editBase' => url('/admin/doctors'),
                'deleteBase' => url('/admin/doctors'),
            ],
            'storageUrl' => asset('storage'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $departments = Department::where('status', 1)->get();

        return Inertia::render('Admin/Doctors/Create', [
            'departments' => $departments->map(fn (Department $department) => [
                'id' => $department->id,
                'name' => $department->name,
            ])->values(),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.doctors.index'),
                'store' => route('admin.doctors.store'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDoctorRequest $request)
    {
        $validated = $request->validated();

        // Create User first
        DB::transaction(function () use ($validated, $request) {
            $user = new User([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);
            $user->role = 'doctor';
            $user->save();

            // Doctor data - whitelist safe fields only
            $data = array_intersect_key($validated, array_flip(['name', 'degree', 'department', 'specialist', 'services', 'availability', 'phone']));
            if (isset($data['services'])) {
                $data['services'] = strip_tags($data['services']);
            }
            $data['slug'] = Str::slug($validated['name']).'-'.uniqid();
            $data['user_id'] = $user->id;
            $data['status'] = isset($validated['status']) ? (int) $validated['status'] : 1;

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
    public function edit(string $id): Response
    {
        $doctor = Doctor::findOrFail($id);
        $departments = Department::where('status', 1)->get();

        return Inertia::render('Admin/Doctors/Edit', [
            'doctor' => [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'image' => $doctor->image,
                'degree' => $doctor->degree,
                'department' => $doctor->department,
                'specialist' => $doctor->specialist,
                'services' => $doctor->services,
                'availability' => $doctor->availability,
                'phone' => $doctor->phone,
                'status' => (int) $doctor->status,
                'email' => $doctor->user?->email,
            ],
            'departments' => $departments->map(fn (Department $department) => [
                'id' => $department->id,
                'name' => $department->name,
            ])->values(),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.doctors.index'),
                'update' => route('admin.doctors.update', $doctor),
            ],
            'storageUrl' => asset('storage'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDoctorRequest $request, string $id)
    {
        $doctor = Doctor::findOrFail($id);

        $validated = $request->validated();

        // Update User
        DB::transaction(function () use ($validated, $request, $doctor) {
            if ($doctor->user_id) {
                $userData = [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                ];

                if (! empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                User::where('id', $doctor->user_id)->update($userData);
            }

            $data = array_intersect_key($validated, array_flip(['name', 'degree', 'department', 'specialist', 'services', 'availability', 'phone', 'status']));
            if (isset($data['services'])) {
                $data['services'] = strip_tags($data['services']);
            }
            $data['slug'] = Str::slug($validated['name']);

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
    public function availability(Doctor $doctor): Response
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

        return Inertia::render('Admin/Doctors/Availability', [
            'doctor' => ['id' => $doctor->id, 'name' => $doctor->name],
            'weekdays' => $weekdays->map(fn (string $label, int $day) => [
                'day' => $day,
                'label' => $label,
            ])->values(),
            'slots' => $slots->map(fn (TimeSlot $slot) => [
                'id' => $slot->id,
                'time' => $slot->time,
            ])->values(),
            'openByWeekday' => $openByWeekday->map(fn (array $slotIds) => array_map('intval', $slotIds)),
            'offDays' => $offDays->map(fn (DoctorOffDay $offDay) => [
                'id' => $offDay->id,
                'date' => $offDay->date->format('Y-m-d'),
                'displayDate' => $offDay->date->format('d M Y'),
                'reason' => $offDay->reason,
            ])->values(),
            'minDate' => now()->toDateString(),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.doctors.index'),
                'availabilityUpdate' => route('admin.doctors.availability.update', $doctor),
                'offDayStore' => route('admin.doctors.off-days.store', $doctor),
                'offDayDeleteBase' => url('/admin/doctor-off-days'),
            ],
        ]);
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

        // Never strand booked patients: an off-day must not silently leave
        // active appointments on that date without a doctor. Check + create
        // inside a transaction with a row lock so a concurrent booking
        // can't slip in between.
        return DB::transaction(function () use ($doctor, $validated) {
            $activeCount = Appointment::where('doctor_id', $doctor->id)
                ->whereDate('appointment_date', $validated['date'])
                ->where('status', '!=', 3)
                ->lockForUpdate()
                ->count();

            if ($activeCount > 0) {
                return back()->with('error', "Cannot add leave: {$activeCount} active appointment(s) already booked on this date. Cancel or reschedule them first.");
            }

            $doctor->offDays()->firstOrCreate([
                'date' => $validated['date'],
            ], [
                'reason' => $validated['reason'] ?? null,
            ]);

            return back()->with('success', 'Off-day added for '.$doctor->name.'.');
        });
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
