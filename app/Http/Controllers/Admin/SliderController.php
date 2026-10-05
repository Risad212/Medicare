<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SliderRequest;
use App\Models\Slider;
use App\Support\AdminNavigation;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SliderController extends Controller
{
    public function index(): Response
    {
        $sliders = Slider::latest()->get();

        return Inertia::render('Admin/Sliders/Index', [
            'sliders' => $sliders->map(fn (Slider $slider) => [
                'id' => $slider->id,
                'title' => $slider->title,
                'description' => $slider->description,
                'buttonText' => $slider->button_text,
                'backgroundImage' => $slider->bg_image,
            ])->values(),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.sliders.index'),
                'create' => route('admin.sliders.create'),
                'editBase' => url('/admin/sliders'),
                'deleteBase' => url('/admin/sliders'),
            ],
            'storageUrl' => asset('storage'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Sliders/Create', [
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.sliders.index'),
                'store' => route('admin.sliders.store'),
            ],
        ]);
    }

    public function store(SliderRequest $request)
    {
        $validated = $request->validated();

        $data = array_intersect_key($validated, array_flip(['title', 'description', 'button_text']));

        if ($request->hasFile('bg_image')) {
            $data['bg_image'] = $request->file('bg_image')->store('sliders', 'public');
        }

        Slider::create($data);

        return back()->with('success', 'Slider added successfully!');
    }

    public function edit(Slider $slider): Response
    {
        return Inertia::render('Admin/Sliders/Edit', [
            'slider' => [
                'id' => $slider->id,
                'title' => $slider->title,
                'description' => $slider->description,
                'buttonText' => $slider->button_text,
                'backgroundImage' => $slider->bg_image,
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.sliders.index'),
                'update' => route('admin.sliders.update', $slider),
            ],
            'storageUrl' => asset('storage'),
        ]);
    }

    public function update(SliderRequest $request, Slider $slider)
    {
        $validated = $request->validated();

        $data = array_intersect_key($validated, array_flip(['title', 'description', 'button_text']));

        if ($request->hasFile('bg_image')) {
            if ($slider->bg_image) {
                Storage::delete('public/'.$slider->bg_image);
            }
            $data['bg_image'] = $request->file('bg_image')->store('sliders', 'public');
        }

        $slider->update($data);

        return redirect()->route('admin.sliders.index')
            ->with('success', 'Slider updated successfully!');
    }

    public function destroy(Slider $slider)
    {
        if ($slider->bg_image) {
            Storage::delete('public/'.$slider->bg_image);
        }
        $slider->delete();

        return redirect()->route('admin.sliders.index')
            ->with('success', 'Slider deleted successfully!');
    }
}
