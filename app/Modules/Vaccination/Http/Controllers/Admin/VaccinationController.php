<?php

namespace App\Modules\Vaccination\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use App\Modules\Vaccination\Http\Requests\VaccinationRequest;
use App\Modules\Vaccination\Models\Vaccination;
use App\Modules\Vaccination\Services\VaccinationService;
use Illuminate\Http\Request;

class VaccinationController extends Controller
{
    public function index(Request $request)
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

        return view('vaccinations.index', compact('vaccinations'));
    }

    public function create()
    {
        $users = User::where('role', 'patient')->orderBy('name')->limit(200)->get(['id', 'name']);
        $patients = Patient::orderBy('name')->limit(200)->get(['id', 'name']);

        return view('vaccinations.create', compact('users', 'patients'));
    }

    public function store(VaccinationRequest $request, VaccinationService $service)
    {
        $vaccination = $service->create($request->validated() + ['created_by' => auth()->id()]);

        return redirect()->route('admin.vaccinations.show', $vaccination)
            ->with('success', 'Vaccination record created successfully.');
    }

    public function show(Vaccination $vaccination)
    {
        $vaccination->load(['user', 'patient', 'creator']);

        return view('vaccinations.show', compact('vaccination'));
    }

    public function edit(Vaccination $vaccination)
    {
        $users = User::where('role', 'patient')->orderBy('name')->limit(200)->get(['id', 'name']);
        $patients = Patient::orderBy('name')->limit(200)->get(['id', 'name']);

        return view('vaccinations.edit', compact('vaccination', 'users', 'patients'));
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
