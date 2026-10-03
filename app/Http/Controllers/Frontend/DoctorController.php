<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\SeoSetting;
use App\Modules\Search\Services\DoctorSearchService;
use App\Support\Module;
use Illuminate\Http\Request;

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
        $pageTitle = 'Our Doctors';
        $noDoctorsMessage = 'No doctors found.';

        return view('frontend.doctor', compact('doctors', 'departments', 'filters', 'seo', 'pageTitle', 'noDoctorsMessage'));
    }

    public function show($id)
    {
        $doctor = Doctor::where('id', $id)->where('status', 1)->firstOrFail();

        return view('frontend.doctors.show', compact('doctor'));
    }
}
