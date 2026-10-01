<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SliderRequest;
use App\Models\Slider;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::latest()->get();

        return view('backend.sliders.index', compact('sliders'));
    }

    public function create()
    {
        return view('backend.sliders.create');
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

    public function edit(Slider $slider)
    {
        return view('backend.sliders.edit', compact('slider'));
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
