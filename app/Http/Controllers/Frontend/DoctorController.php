<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorReview;
use App\Models\SeoSetting;
use App\Models\Service;
use App\Modules\Search\Services\DoctorSearchService;
use App\Support\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        // Search module (app/Modules/Search/): only touched when the flag is
        // on. ::class references are plain strings until app() resolves them,
        // so a deleted module folder is never autoloaded while off.
        if (Module::enabled('search')) {
            $search = app(DoctorSearchService::class);
            $filters = $search->validatedFilters($request);
            $doctors = $search->search($filters);
            $departments = $search->departments();
        } else {
            $filters = [];
            $departments = [];
            $doctors = Doctor::where('status', 1)->latest()->paginate(12);
        }

        $seo = SeoSetting::where('page', 'doctor')->first();
        $pageTitle = __('messages.public.our_doctors');
        $noDoctorsMessage = __('messages.public.no_doctors');

        return view('frontend.doctor', compact('doctors', 'departments', 'filters', 'seo', 'pageTitle', 'noDoctorsMessage'));
    }

    public function show($id)
    {
        $doctor = Doctor::where('id', $id)->where('status', 1)->firstOrFail();

        $reviews = collect();
        $ratingSummary = ['avg' => 0.0, 'count' => 0];

        if (Module::enabled('reviews')) {
            $reviews = $doctor->approvedReviews()->with('user')->latest()->limit(20)->get();
            $ratingSummary = DoctorReview::summaryFor($doctor);
        }

        // Bidirectional department link: services whose department matches
        // the doctor's department. The section stays hidden for every
        // empty/unknown match — no invented service data is ever rendered.
        $department = trim((string) $doctor->department);
        $relatedServices = $department !== ''
            ? Service::where('status', 1)->where('department', $department)->orderBy('order')->get()
            : collect();

        return view('frontend.doctors.show', compact('doctor', 'reviews', 'ratingSummary', 'relatedServices'));
    }

    public function department(string $name)
    {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > 100) {
            abort(404);
        }

        $doctors = Doctor::where('status', 1)
            ->where('department', $name)
            ->orderBy('name')
            ->get();

        $services = Service::where('status', 1)
            ->where('department', $name)
            ->orderBy('order')
            ->get();

        if ($doctors->isEmpty() && $services->isEmpty()) {
            abort(404);
        }

        $department = Department::where('name', $name)->where('status', 1)->first();

        $metaDescription = Str::limit(strip_tags((string) ($department->description ?? '')), 150);

        return view('frontend.department', compact('name', 'doctors', 'services', 'metaDescription'));
    }
}
