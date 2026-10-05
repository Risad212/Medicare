<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\AboutSetting;
use App\Models\Doctor;
use App\Models\SeoSetting;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function index(): Response
    {
        $about = AboutSetting::first() ?? new AboutSetting;
        $doctors = Doctor::where('status', 1)->latest()->take(3)->get();
        $seo = SeoSetting::where('page', 'about')->first();

        return Inertia::render('Public/About', [
            'about' => $about->toArray(),
            'doctors' => $doctors->map(fn (Doctor $doctor) => [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'department' => $doctor->department,
                'specialist' => $doctor->specialist,
                'degree' => $doctor->degree,
                'image' => $doctor->image,
            ])->values(),
            'seo' => [
                'title' => $seo?->meta_title,
                'description' => $seo?->meta_description,
                'keywords' => $seo?->meta_keywords,
            ],
        ]);
    }
}
