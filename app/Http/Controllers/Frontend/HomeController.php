<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Doctor;
use App\Models\HomeSetting;
use App\Models\SeoSetting;
use App\Models\Service;
use App\Models\Slider;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $homeSetting = HomeSetting::first();
        $sliders = Slider::all();
        $doctors = Doctor::where('status', 1)->latest()->take(3)->get();
        $recentBlogs = Blog::where('status', 1)->latest()->take(4)->get();
        $serviceItems = Service::where('status', 1)->orderBy('order')->get();
        $seo = SeoSetting::where('page', 'home')->first();

        return Inertia::render('Public/Home', [
            'home' => $homeSetting?->toArray() ?? [],
            'sliders' => $sliders->map(fn (Slider $slider) => [
                'title' => $slider->title,
                'description' => $slider->description,
                'buttonText' => $slider->button_text,
                'backgroundImage' => $slider->bg_image,
            ])->values(),
            'doctors' => $doctors->map(fn (Doctor $doctor) => [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'department' => $doctor->department,
                'specialist' => $doctor->specialist,
                'degree' => $doctor->degree,
                'image' => $doctor->image,
            ])->values(),
            'recentBlogs' => $recentBlogs->map(fn (Blog $blog) => [
                'id' => $blog->id,
                'title' => $blog->title,
                'slug' => $blog->slug,
                'excerpt' => $blog->excerpt,
                'image' => $blog->image,
                'author' => $blog->author,
                'date' => $blog->created_at?->format('M d, Y'),
            ])->values(),
            'services' => $serviceItems->map(fn (Service $service) => [
                'id' => $service->id,
                'title' => $service->title,
                'slug' => $service->slug,
                'description' => $service->description,
                'icon' => $service->icon,
                'buttonText' => $service->button_text,
                'buttonUrl' => $service->button_url,
            ])->values(),
            'seo' => [
                'title' => $seo?->meta_title,
                'description' => $seo?->meta_description,
                'keywords' => $seo?->meta_keywords,
            ],
        ]);
    }
}
