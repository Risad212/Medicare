<?php

namespace App\Modules\Vaccination\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use App\Modules\Vaccination\Http\Requests\VaccinationRequest;
use App\Modules\Vaccination\Models\Vaccination;
use App\Modules\Vaccination\Services\VaccinationService;
use App\Support\AdminNavigation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VaccinationController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->search ? str_replace(['%', '_'], ['\%', '\_'], $request->search) : null;

        $vaccinations = Vaccination::with(['user', 'patient'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('vaccine_name', 'like', '%'.$search.'%')
                        ->orWhere('child_name', 'like', '%'.$search.'%')
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('patient', fn ($p) => $p->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $vaccinations->through(fn (Vaccination $vaccination) => [
            'id' => $vaccination->id,
            'subjectName' => $vaccination->subject_name,
            'vaccineName' => $vaccination->vaccine_name,
            'doseNumber' => $vaccination->dose_number,
            'status' => $vaccination->status,
            'statusLabel' => $vaccination->status_label,
            'isOverdue' => $vaccination->is_overdue,
            'nextDueDate' => $vaccination->next_due_date?->format('Y-m-d'),
        ]);

        return Inertia::render('Admin/Vaccinations/Index', [
            'vaccinations' => [
                'data' => $vaccinations->items(),
                'currentPage' => $vaccinations->currentPage(),
                'lastPage' => $vaccinations->lastPage(),
                'firstItem' => $vaccinations->firstItem(),
                'lastItem' => $vaccinations->lastItem(),
                'total' => $vaccinations->total(),
                'previousPageUrl' => $vaccinations->previousPageUrl(),
                'nextPageUrl' => $vaccinations->nextPageUrl(),
            ],
            'filters' => ['search' => (string) $request->query('search', '')],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.vaccinations.index'),
                'create' => route('admin.vaccinations.create'),
                'showBase' => url('/admin/vaccinations'),
                'editBase' => url('/admin/vaccinations'),
                'deleteBase' => url('/admin/vaccinations'),
            ],
        ]);
    }

    public function create(): Response
    {
        $users = User::where('role', 'patient')->orderBy('name')->limit(200)->get(['id', 'name']);
        $patients = Patient::orderBy('name')->limit(200)->get(['id', 'name']);

        return Inertia::render('Admin/Vaccinations/Form', [
            'mode' => 'create',
            'users' => $users,
            'patients' => $patients,
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.vaccinations.index'),
                'store' => route('admin.vaccinations.store'),
            ],
        ]);
    }

    public function store(VaccinationRequest $request, VaccinationService $service)
    {
        $vaccination = $service->create($request->validated() + ['created_by' => auth()->id()]);

        return redirect()->route('admin.vaccinations.show', $vaccination)
            ->with('success', 'Vaccination record created successfully.');
    }

    public function show(Vaccination $vaccination): Response
    {
        $vaccination->load(['user', 'patient', 'creator']);

        return Inertia::render('Admin/Vaccinations/Show', [
            'vaccination' => [
                'id' => $vaccination->id,
                'subjectName' => $vaccination->subject_name,
                'userName' => $vaccination->user?->name,
                'patientName' => $vaccination->patient?->name,
                'vaccineName' => $vaccination->vaccine_name,
                'doseNumber' => $vaccination->dose_number,
                'status' => $vaccination->status_label,
                'isOverdue' => $vaccination->is_overdue,
                'dateGiven' => $vaccination->date_given?->format('d M Y'),
                'nextDueDate' => $vaccination->next_due_date?->format('d M Y'),
                'nextDueDateLong' => $vaccination->next_due_date?->format('d M Y'),
                'administeredBy' => $vaccination->administered_by,
                'notes' => $vaccination->notes,
                'creatorName' => $vaccination->creator?->name,
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.vaccinations.index'),
                'edit' => route('admin.vaccinations.edit', $vaccination),
                'delete' => route('admin.vaccinations.destroy', $vaccination),
            ],
        ]);
    }

    public function edit(Vaccination $vaccination): Response
    {
        $users = User::where('role', 'patient')->orderBy('name')->limit(200)->get(['id', 'name']);
        $patients = Patient::orderBy('name')->limit(200)->get(['id', 'name']);

        return Inertia::render('Admin/Vaccinations/Form', [
            'mode' => 'edit',
            'vaccination' => [
                'id' => $vaccination->id,
                'userId' => $vaccination->user_id,
                'patientId' => $vaccination->patient_id,
                'childName' => $vaccination->child_name,
                'vaccineName' => $vaccination->vaccine_name,
                'doseNumber' => $vaccination->dose_number,
                'dateGiven' => $vaccination->date_given?->format('Y-m-d'),
                'nextDueDate' => $vaccination->next_due_date?->format('Y-m-d'),
                'administeredBy' => $vaccination->administered_by,
                'notes' => $vaccination->notes,
                'status' => $vaccination->status,
            ],
            'users' => $users,
            'patients' => $patients,
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.vaccinations.index'),
                'show' => route('admin.vaccinations.show', $vaccination),
                'update' => route('admin.vaccinations.update', $vaccination),
            ],
        ]);
    }

    public function update(VaccinationRequest $request, Vaccination $vaccination, VaccinationService $service)
    {
        $service->update($vaccination, $request->validated());

        return redirect()->route('admin.vaccinations.show', $vaccination)
            ->with('success', 'Vaccination record updated successfully.');
    }

    public function destroy(Vaccination $vaccination, VaccinationService $service)
    {
        $service->delete($vaccination);

        return redirect()->route('admin.vaccinations.index')
            ->with('success', 'Vaccination record deleted successfully.');
    }
}
