<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\SeoSetting;
use App\Modules\Search\Services\DoctorSearchService;
use App\Support\Module;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DoctorController extends Controller
{
    public function index(Request $request): Response
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

        $doctors->setCollection($doctors->getCollection()->map(fn (Doctor $doctor) => [
            'id' => $doctor->id,
            'name' => $doctor->name,
            'department' => $doctor->department,
            'specialist' => $doctor->specialist,
            'degree' => $doctor->degree,
            'image' => $doctor->image,
        ]));

        return Inertia::render('Public/Doctors/Index', [
            'doctors' => [
                'data' => $doctors->items(),
                'currentPage' => $doctors->currentPage(),
                'lastPage' => $doctors->lastPage(),
                'total' => $doctors->total(),
                'links' => $doctors->linkCollection(),
            ],
            'departments' => $departments,
            'filters' => $filters,
            'searchEnabled' => Module::enabled('search'),
            'pageTitle' => $pageTitle,
            'noDoctorsMessage' => $noDoctorsMessage,
            'seo' => [
                'title' => $seo?->meta_title,
                'description' => $seo?->meta_description,
                'keywords' => $seo?->meta_keywords,
            ],
        ]);
    }

    public function show($id): Response
    {
        $doctor = Doctor::where('id', $id)->where('status', 1)->firstOrFail();

        return Inertia::render('Public/Doctors/Show', [
            'minDate' => now()->toDateString(),
            'doctor' => [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'degree' => $doctor->degree,
                'department' => $doctor->department,
                'specialist' => $doctor->specialist,
                'services' => $doctor->services,
                'availability' => $doctor->availability,
                'phone' => $doctor->phone,
                'image' => $doctor->image,
            ],
        ]);
    }
}
